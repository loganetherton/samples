import React, {Component, PropTypes} from 'react';
import Immutable from 'immutable';
import {Dialog} from 'material-ui';

// Default customer ID
import {DEFAULT_CUSTOMER} from 'redux/modules/jobType';

import {ActionButton, VpPlainDropdown, statesList, VpTextField, JobTypesTableRow, ProgressButton} from 'components';

const style = {
  location: {width: '100%', border: 'none', fontSize: '14px', position: 'relative', background: 'none'},
  noWrap: {whiteSpace: 'nowrap'},
  setFeesTitle: {padding: '24px 24px 0px', margin: '0px', color: 'rgba(0,0,0,0.870588)', fontSize: '24px', lineHeight: '32px', fontWeight: '400'},
  setFeesMargin: {margin: '0px 39px 0px 24px'},
  locationTable: {width: '100%', marginTop: '10px'}
};

const styles = require('./style.scss');

export default class JobTypesTable extends Component {
  static propTypes = {
    // Job type reducer
    jobType: PropTypes.instanceOf(Immutable.Map),
    // Job types list
    jobTypes: PropTypes.instanceOf(Immutable.List),
    // Fees
    fees: PropTypes.instanceOf(Immutable.List),
    // Handle clicking a checkbox
    handleRowSelect: PropTypes.func.isRequired,
    // Update a fee
    setFeeValue: PropTypes.func.isRequired,
    // default job types
    defaultJobTypes: PropTypes.instanceOf(Immutable.List),
    // Selected customer override
    selectedCustomer: PropTypes.number,
    // If appraiser can edit fees
    canEditFees: PropTypes.bool.isRequired,
    // Change search value
    changeSearchValue: PropTypes.func.isRequired,
    // Sort by a column
    sortColumn: PropTypes.func.isRequired,
    // Create map using fees, to prevent list iteration when checking boxes programmatically
    createFeeMap: PropTypes.func.isRequired,
    // AMC view
    isAmc: PropTypes.bool,
    // Change fee location
    changeLocation: PropTypes.func,
    // invitation search
    customerFormSearch: PropTypes.string,
    industryFormSearch: PropTypes.string,
    // Get counties in the currently selected state
    getCounties: PropTypes.func,
    // Get zips in the currently selected state
    getZips: PropTypes.func,
    // Set prop
    setProp: PropTypes.func,
    // Get AMC location fees
    getAmcLocationFees: PropTypes.func,
    // Set AMC location fees
    setAmcLocationFees: PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      showFeesByLocation: false,
      selectedJobTypeId: null,
      // For saving customer specific location values
      selectedJobTypeIdCustomerLocation: null,
      amcLocationView: 'state',
      applyStateToAll: false,
      // County or zips
      applyStateToAllType: null
    };

    this.saveFeesByLocation = ::this.saveFeesByLocation;
    this.toggleFeesByLocation = ::this.toggleFeesByLocation;
    this.applyStateFeesToLocation = ::this.applyStateFeesToLocation;
    this.amcLocationSetJobType = ::this.amcLocationSetJobType;
    this.toggleApplyStateFees = ::this.toggleApplyStateFees;
    this.changeLocationFee = ::this.changeLocationFee;
    this.changeCustomerJobTypeFilter = this.changeSearchInput.bind(this, 'customerFormSearch');
    this.clearCustomerJobTypeFilter = this.clearSearch.bind(this, 'customerFormSearch');
    this.changeIndustryJobTypeFilter = this.changeSearchInput.bind(this, 'industryFormSearch');
    this.clearIndustryJobTypeFilter = this.clearSearch.bind(this, 'industryFormSearch');
    this.switchToStateView = this.changeLocationView.bind(this, 'state');
    this.toggleApplyStateFeesToCounties = {};
    this.toggleApplyStateFeesToZips = {};
    this.switchToCountyView = {};
    this.switchToZipView = {};
    this.changeAmcStateFee = {};
    this.setJobTypeFeeValue = {};
    this.enableJobType = {};
    this.changeCountyFees = {};
    this.changeZipFees = {};
    this.toggleFeesByLocationWithFee = {};
    this.sortColumns = {};

    ['enabled', 'customerForm', 'industryForm', 'fee', 'action'].forEach(column => {
      this.sortColumns[column] = this.sortColumn.bind(this, column);
    });

    statesList.map(state => {
      this.toggleApplyStateFeesToCounties[state.get('value')] = this.toggleApplyStateFees.bind(this, 'counties', state);
      this.toggleApplyStateFeesToZips[state.get('value')] = this.toggleApplyStateFees.bind(this, 'zips', state);
      this.switchToCountyView[state.get('value')] = this.changeLocationView.bind(this, 'county', state.get('value'));
      this.switchToZipView[state.get('value')] = this.changeLocationView.bind(this, 'zip', state.get('value'));
      this.changeAmcStateFee[state.get('value')] = this.changeLocationFee.bind(this, state.get('value'), 'amcState');
    });
  }

  componentWillReceiveProps(nextProps) {
    if (nextProps.jobTypes) {
      this.setJobTypeFeeValue = {};
      this.enableJobType = {};

      nextProps.jobTypes.map(jobType => {
        this.setJobTypeFeeValue[jobType.get('id')] = this.setFeeValue.bind(this, jobType);
        this.enableJobType[jobType.get('id')] = this.enableForm.bind(this, jobType);
      });
    }

    if (nextProps.jobType.get('counties')) {
      this.changeCountyFees = {};

      nextProps.jobType.get('counties').map(county => {
        this.changeCountyFees[county.get('id')] = this.changeLocationFee.bind(this, county.get('id'), 'amcCounty');
      });
    }

    if (nextProps.jobType.get('zips')) {
      this.changeZipFees = {};

      nextProps.jobType.get('zips').map(zip => {
        this.changeZipFees[zip] = this.changeLocationFee.bind(this, zip, 'amcZip');
      });
    }

    if (nextProps.fees) {
      this.toggleFeesByLocationWithFee = {};

      nextProps.fees.forEach(fee => {
        this.toggleFeesByLocationWithFee[fee.getIn(['jobType', 'id'])] = this.toggleFeesByLocation.bind(this, fee);
      });
    }
  }

  /**
   * Set fee value
   * @param jobType
   * @param event
   */
  setFeeValue(jobType, event) {
    const {target: {value}} = event;
    const sanitizedValue = value.replace(/[^\d\.]/g, '');
    this.props.setFeeValue(jobType, sanitizedValue, this.getSelectedCustomer());
  }

  /**
   * Retrieve selected customer
   * @returns {*}
   */
  getSelectedCustomer() {
    const {selectedCustomer, jobType} = this.props;
    // Allow for override
    if (selectedCustomer) {
      return selectedCustomer;
    }
    return jobType.get('selectedCustomer');
  }

  /**
   * Enable or disable a form
   * @param jobType
   */
  enableForm(jobType) {
    // Prevent double click
    if (this.timer) {
      return;
    }
    this.timer = setTimeout(() => {
      const {canEditFees, handleRowSelect} = this.props;
      if (this.getSelectedCustomer() === DEFAULT_CUSTOMER || canEditFees) {
        handleRowSelect(jobType, this.getSelectedCustomer() !== DEFAULT_CUSTOMER);
      }
      this.timer = null;
    }, 50);
  }

  /**
   * Search input
   */
  changeSearchInput(form, event) {
    this.props.changeSearchValue(form, event.target.value, this.getSelectedCustomer() !== DEFAULT_CUSTOMER);
  }

  /**
   * Clear search input
   */
  clearSearch(form) {
    const {changeSearchValue} = this.props;
    changeSearchValue(form, '', this.getSelectedCustomer() !== DEFAULT_CUSTOMER);
  }

  /**
   * Create table column headers
   * @param selectedCustomer
   * @returns {XML}
   */
  createColumns(selectedCustomer) {
    const {jobType, isAmc = false} = this.props;
    const industryFormSearch = jobType.get('industryFormSearch');
    const customerFormSearch = jobType.get('customerFormSearch');
    return (
      <thead>
        <tr key="columns">
          <th>
            <div>
              <label className={`control-label ${styles['header-label']}`}>
                Enabled
              </label>
            </div>
          </th>
          {selectedCustomer !== DEFAULT_CUSTOMER &&
           <th>
             <div className={styles['job-type-header-wrapper']}>
               <label className={`control-label ${styles['job-type-header']} ${styles['header-label']}`}>
                 Customer Form
               </label>
               <div className={styles['job-type-filter']}>
                 <VpTextField
                  placeholder="Search"
                  value={customerFormSearch}
                  onChange={this.changeCustomerJobTypeFilter}
                  parentClass={styles['input-wrapper']}
                  inputClass={styles['filter-input']}
                  hideError
                 />
                 {!!customerFormSearch.length &&
                  <span className={styles['clear-job-type-filter']}>
                    <i className={`material-icons ${styles['clear-filter-icon']}`}
                       onClick={this.clearCustomerJobTypeFilter}>highlight_off</i>
                  </span>
                 }
               </div>
             </div>
           </th>
          }
          <th>
            <div className={styles['job-type-header-wrapper']}>
              <label className={`control-label ${styles['job-type-header']} ${styles['header-label']}`}>
                Industry Form
              </label>
              <div className={styles['job-type-filter']}>
                <VpTextField
                  placeholder="Search"
                  value={industryFormSearch}
                  onChange={this.changeIndustryJobTypeFilter}
                  parentClass={styles['input-wrapper']}
                  inputClass={styles['filter-input']}
                  hideError
                />
                {!!industryFormSearch.length &&
                 <span className={styles['clear-job-type-filter']}>
                    <i className={`material-icons ${styles['clear-filter-icon']}`}
                       onClick={this.clearIndustryJobTypeFilter}>highlight_off</i>
                  </span>
                }
              </div>
            </div>
          </th>
          <th>
            <div>
              <label className={`control-label ${styles['header-label']}`}>
                {isAmc ? 'Default Fee' : 'Fee'}
              </label>
            </div>
          </th>
          {isAmc &&
            <th key={1}>
              <div>
                <label className={`control-label ${styles['header-label']}`}>
                  Action
                </label>
              </div>
            </th>
          }
        </tr>
      </thead>
    );
  }

  /**
   * Sort by a column
   * @param column
   */
  sortColumn(column) {
    this.props.sortColumn(column, this.getSelectedCustomer() !== DEFAULT_CUSTOMER);
  }

  /**
   * Create up/down sort buttons
   * @param sortVal Current sort value this column
   * @param column Current column
   */
  createSortButtons(sortVal, column) {
    const sortButtons = [];
    const upSort = (
      <i className={`material-icons ${styles['sort-buttons']}`} key={0}>keyboard_arrow_up</i>
    );
    const downSort = (
      <i className={`material-icons ${styles['sort-buttons']}`} key={1}>keyboard_arrow_down</i>
    );
    // Don't sort action column
    const noSort = (
      <i className={`material-icons ${styles['sort-buttons']}`} key={2}/>
    );
    if (sortVal === 0) {
      sortButtons.push(upSort);
      sortButtons.push(downSort);
    } else if (sortVal === 1) {
      sortButtons.push(upSort);
    } else if (column === 'action') {
      sortButtons.push(noSort);
    } else {
      sortButtons.push(downSort);
    }
    return (
      <span role="button" className={styles['sort-button-wrapper']} onClick={this.sortColumns[column]}>
        {sortButtons}
      </span>
    );
  }

  /**
   * Create row for sorting
   * @param index
   * @returns {XML}
   */
  createSortRow(index) {
    const isCustomer = this.getSelectedCustomer() !== DEFAULT_CUSTOMER;
    const {jobType, isAmc} = this.props;
    const sortState = jobType.get('sorts');
    let columns = isCustomer ? ['enabled', 'customerForm', 'industryForm', 'fee'] : ['enabled', 'industryForm', 'fee'];
    // AMC view
    if (isAmc) {
      columns = ['enabled', 'industryForm', 'fee', 'action'];
      if (isCustomer) {
        columns.splice(1, 0, 'customerForm');
      }
    }
    const sorts = [];
    for (const column in columns) {
      if (columns.hasOwnProperty(column)) {
        sorts.push(
          <td className={styles['sort-cell']} key={column}>
            {this.createSortButtons(sortState.get(columns[column]), columns[column])}
          </td>
        );
      }
    }
    return (
      <tr key={index} className={styles['sort-row']}>
        {sorts}
      </tr>
    );
  }

  /**
   * Change fee location
   */
  amcChangeLocation(jobTypeId, event) {
    this.props.changeLocation(jobTypeId, event.target.value);
  }

  /**
   * Show set fees by location dialog
   */
  toggleFeesByLocation(jobType) {
    const toggleState = !this.state.showFeesByLocation;
    const {getAmcLocationFees, setProp} = this.props;
    const newState = {
      showFeesByLocation: toggleState
    };
    let jobTypeId;
    // If selecting a job type
    if (jobType) {
      jobTypeId = jobType.getIn(['jobType', 'id']);
      newState.selectedJobTypeId = jobType.getIn(['jobType', 'id']);
      newState.selectedJobTypeIdCustomerLocation = jobType.getIn(['jobType', 'local', 'id']);
    }
    // Get state fees
    if (toggleState) {
      getAmcLocationFees('states', jobTypeId, null, this.getSelectedCustomer());
    } else {
      setProp(Immutable.Map(), 'amcState');
      setProp(Immutable.Map(), 'amcCounty');
      setProp(Immutable.Map(), 'amcZip');
    }
    this.setState(newState);
  }

  /**
   * Create body row for AMC view
   * @param isEnabled Row is enabled
   * @param fee Fee for row
   */
  amcBodyRow(isEnabled, fee) {
    let row;
    if (!isEnabled) {
      row = <td key={0}/>;
    } else {
      row = (
        <td key={0}>
          <a className="link" style={style.noWrap} onClick={this.toggleFeesByLocationWithFee[fee.getIn(['jobType', 'id'])]}>
            Set Fees by Location</a>
        </td>
      );
    }
    return row;
  }

  /**
   * Fee is enabled
   * @param fee
   * @returns {boolean}
   */
  isEnabled(fee) {
    return typeof fee !== 'undefined' && fee.get('removed') !== true;
  }

  /**
   * Create normal body row
   * @param jobType Job type
   * @param selectedCustomer Customer
   * @param localJobTitle Industry title
   * @param canEditFees If can edit fees
   * @param disabled Disabled fee
   * @param fee Current fee
   * @param index
   * @returns {XML}
   */
  createBodyRow(jobType, selectedCustomer, localJobTitle, canEditFees, disabled, fee, index) {
    const {isAmc} = this.props;
    const rowIsEnabled = this.isEnabled(fee);
    let feeValue = '';
    if (fee) {
      feeValue = fee && typeof fee.get('amount') !== 'undefined' && fee.get('removed') !== true ? fee.get('amount').toString() : '';
    }
    return (
      <tr key={index}>
        <td className={`text-center ${styles['enable-job-type-cell']}`}
            width="1%"
            onClick={this.enableJobType[jobType.get('id')]}>
          <label className={styles['enable-job-type-label']}>
            <input
              type="checkbox"
              checked={rowIsEnabled}
              readOnly
              disabled={!canEditFees}
            />
          </label>
        </td>
        <td>
          <div>{jobType.get('title')}</div>
        </td>
        {selectedCustomer !== DEFAULT_CUSTOMER &&
         <td>
           <div>{localJobTitle}</div>
         </td>
        }
        <td width="10%">
          <VpTextField
            value={feeValue}
            placeholder="Fee"
            onChange={this.setJobTypeFeeValue[jobType.get('id')]}
            disabled={!canEditFees || disabled}
            inputClass={styles['fee-input']}
            parentClass={styles['input-wrapper']}
            noTimeout
          />
          {fee && !!fee.get('amount') && fee.get('error') &&
           <div className="has-error">
             <p className="help-block">{fee.get('error') ? 'Fee amount should contain only digits and an optional decimal value, eg, 11.11' : ''}</p>
           </div>
          }
        </td>
        {isAmc &&
          this.amcBodyRow(rowIsEnabled, fee)
        }
      </tr>
    );
  }

  /**
   * Create table body
   * @param jobTypes
   * @param feeMap Map of fees
   * @param jobTypeMap Map of selected job types
   * @param selectedCustomer
   * @returns {any}
   */
  createBody(jobTypes, feeMap, jobTypeMap, selectedCustomer) {
    // If appraiser can edit fees for this customer
    const canEditFees = this.props.canEditFees;
    jobTypes = jobTypes.unshift(Immutable.fromJS({
      sort: true
    }));

    return jobTypes.map((jobType, index) => {
      const fee = feeMap.get(jobType.get('id'));
      const localJobTitle = jobTypeMap.getIn([jobType.getIn(['local', 'id']), 'title'], 'N/A');
      const disabled = (typeof fee === 'undefined') || (fee.get('removed') === true);
      let row;
      if (jobType.get('sort') === true) {
        row = this.createSortRow(index);
      } else {
        row = this.createBodyRow(jobType, selectedCustomer, localJobTitle, canEditFees, disabled, fee, index);
      }
      return row;
    }).toJS();
  }

  /**
   * Create a map of job types
   */
  createJobTypeMap(jobTypes) {
    let jobTypeMap = Immutable.Map();
    jobTypes.forEach((jobType) => {
      jobTypeMap = jobTypeMap.set(jobType.get('id'), jobType);
    });
    return jobTypeMap;
  }

  /**
   * Save fees by location
   */
  saveFeesByLocation() {
    const {setAmcLocationFees} = this.props;
    const {amcLocationView, selectedJobTypeId, selectedState} = this.state;
    const customerId = this.getSelectedCustomer();
    // State
    if (amcLocationView === 'state') {
      return setAmcLocationFees('states', selectedJobTypeId, null, {}, customerId);
    // County
    } else if (amcLocationView === 'county') {
      return setAmcLocationFees('counties', selectedJobTypeId, selectedState, {}, customerId);
    // Zip
    } else if (amcLocationView === 'zip') {
      return setAmcLocationFees('zips', selectedJobTypeId, selectedState, {}, customerId);
    }
  }

  /**
   * Close button for set fee by location
   */
  feeByLocationDialogAction() {
    const {amcLocationView} = this.state;
    const isState = amcLocationView === 'state';
    return [
      <ActionButton
        type="cancel"
        text={isState ? 'Close' : 'Back to state view'}
        onClick={isState ? this.toggleFeesByLocation : this.switchToStateView}
      />,
      <div className="col-md-1 pull-right">
        <ProgressButton onClick={this.saveFeesByLocation} state={this.state.saveButton} durationSuccess={1000}>
          Save
        </ProgressButton>
      </div>
    ];
  }

  /**
   * Apply state fees to all of a location type
   */
  applyStateFeesToLocation() {
    const {applyStateToAllType, selectedJobTypeId, selectedState} = this.state;
    const {setAmcLocationFees} = this.props;
    // Apply to all
    setAmcLocationFees('states', selectedJobTypeId, null, {
      code: selectedState,
      type: applyStateToAllType
    }, this.getSelectedCustomer());
    // Write to backend, revert to state view
    this.setState({
      applyStateToAll: !this.state.applyStateToAll,
      applyStateToAllType: null,
      showFeesByLocation: !this.state.showFeesByLocation
    });
  }

  /**
   * Actions for applying state fees to all of type
   */
  applyStateToAllActions() {
    return [
      <ActionButton
        type="cancel"
        text="Close"
        onClick={this.toggleApplyStateFees}
      />,
      <ActionButton
        type="submit"
        text="Apply"
        onClick={this.applyStateFeesToLocation}
        additionalClasses={styles['apply-state-fees-to-location']}
      />
    ];
  }

  /**
   * Set currently viewed job type (amc set fee)
   * @param event
   */
  amcLocationSetJobType(event) {
    this.setState({
      selectedJobTypeId: parseInt(event.target.value, 10)
    });
  }

  /**
   * Change fee for a state
   * @param id ID of the location
   * @param prop Location type prop
   * @param event
   */
  changeLocationFee(id, prop, event) {
    this.props.setProp(event.target.value, prop, id);
  }

  /**
   * Toggle apply state fees dialog
   * @param type
   * @param state Selected state
   */
  toggleApplyStateFees(type, state) {
    const newState = {
      applyStateToAll: !this.state.applyStateToAll,
      applyStateToAllType: type,
      showFeesByLocation: !this.state.showFeesByLocation
    };
    // Store state
    if (state) {
      newState.selectedState = state.get('value');
    }
    this.setState(newState);
  }

  /**
   * Change state to display a certain view
   * @param type
   */
  setViewState(type) {
    this.setState({
      amcLocationView: type
    });
  }

  /**
   * Change location view type
   * @param type Location type to display
   * @param state State within which location is found
   */
  changeLocationView(type, state) {
    const {getCounties, getZips, setProp, getAmcLocationFees} = this.props;
    const {selectedJobTypeId} = this.state;
    // Store selected state
    if (state) {
      this.setState({
        selectedState: state
      });
    }
    // Reset list of available
    if (type === 'county') {
      setProp(Immutable.Map(), `counties`);
    } else if (type === 'zip') {
      setProp(Immutable.Map(), `zips`);
    }
    const promises = [];
    if (type === 'county') {
      // Get counties
      promises.push(getCounties(state));
      promises.push(getAmcLocationFees('counties', selectedJobTypeId, state, this.getSelectedCustomer()));
    } else if (type === 'zip') {
      // Get zips
      promises.push(getZips(state));
      promises.push(getAmcLocationFees('zips', selectedJobTypeId, state, this.getSelectedCustomer()));
      // State
    } else {
      this.setViewState(type);
    }
    // Wait to retrieve list then transition
    if (promises.length) {
      Promise.all(promises)
      .then(() => {
        this.setViewState(type);
      });
    }
  }

  /**
   * Amc set fees by state
   */
  amcLocationState() {
    // State vals
    const stateVals = this.props.jobType.get('amcState');
    return (
      <table className="data-table" style={style.locationTable}>
        <colgroup>
          <col width="20%" />
          <col width="20%" />
          <col width="20%" />
          <col width="20%" />
          <col width="20%" />
        </colgroup>
        <thead>
        <tr key="columns">
          <th>
            <div>
              <label className="control-label">
                States
              </label>
            </div>
          </th>
          <th>
            <div>
              <label className="control-label">
                AMC Fee
              </label>
            </div>
          </th>
          <th>
            <div>
              <label className="control-label">
                Apply Fees
              </label>
            </div>
          </th>
          <th>
            <div>
              <label className="control-label">
                Set By County
              </label>
            </div>
          </th>
          <th>
            <div>
              <label className="control-label">
                Set By Zip
              </label>
            </div>
          </th>
        </tr>
        </thead>
        <tbody>
        {statesList.map((state, index) => {
          let amount = stateVals.get(state.get('value'));
          if (typeof amount !== 'undefined') {
            amount = amount.toString();
          }
          return (
            <tr key={index}>
              <td>
                <div>{state.get('name')}</div>
              </td>
              <td>
                <div>
                  <VpTextField
                    name="stateFee"
                    value={amount ? amount : ''}
                    onChange={this.changeAmcStateFee[state.get('value')]}
                    parentClass=""
                  />
                </div>
              </td>
              <td>
                {!!amount &&
                 <div className="row">
                   <div className="col-md-12">
                     <a className="link" style={style.noWrap}
                        onClick={this.toggleApplyStateFeesToCounties[state.get('value')]}>
                       Apply state fees to all counties</a>
                   </div>
                   <div className="col-md-12">
                     <a className="link" style={style.noWrap} onClick={this.toggleApplyStateFeesToZips[state.get('value')]}>
                       Apply state fees to all zips</a>
                   </div>
                 </div>
                }
              </td>
              <td>
                <div>
                  <a className="link" style={style.noWrap} onClick={this.switchToCountyView[state.get('value')]}>
                    Set by county</a>
                </div>
              </td>
              <td>
                <div>
                  <a className="link" style={style.noWrap} onClick={this.switchToZipView[state.get('value')]}>
                    Set by zip</a>
                </div>
              </td>
            </tr>
          );
        })}
        </tbody>
      </table>
    );
  }

  /**
   * Amc set fees by county
   */
  amcLocationCounty() {
    const {jobType} = this.props;
    // Counties in the selected state
    const counties = jobType.get('counties');
    const countyFees = jobType.get('amcCounty').map(fee => fee.toString());
    return (
      <table className="data-table" style={style.locationTable}>
        <colgroup>
          <col width="50%" />
          <col width="50%" />
        </colgroup>
        <thead>
        <tr key="columns">
          <th>
            <div>
              <label className="control-label">
                County
              </label>
            </div>
          </th>
          <th>
            <div>
              <label className="control-label">
                Fee
              </label>
            </div>
          </th>
        </tr>
        </thead>
        <tbody>
        {counties.map((county, index) => {
          const countyAmount = countyFees.get(county.get('id'));
          return (
            <JobTypesTableRow
              key={index}
              title={county.get('title')}
              value={countyAmount}
              onChange={this.changeCountyFees[county.get('id')]}
              fieldName="countyFee"
            />
          );
        })}
        </tbody>
      </table>
    );
  }

  /**
   * Amc set fees by zip
   */
  amcLocationZip() {
    const {jobType} = this.props;
    // Zips in the selected state
    const zips = jobType.get('zips');
    const zipFees = jobType.get('amcZip');
    return (
      <table className="data-table" style={style.locationTable}>
        <colgroup>
          <col width="50%" />
          <col width="50%" />
        </colgroup>
        <thead>
        <tr key="columns">
          <th>
            <div>
              <label className="control-label">
                Zip
              </label>
            </div>
          </th>
          <th>
            <div>
              <label className="control-label">
                Fee
              </label>
            </div>
          </th>
        </tr>
        </thead>
        <tbody>
        {zips.map((zip, index) => {
          return (
            <JobTypesTableRow
              key={index}
              title={zip}
              value={zipFees.get(zip)}
              onChange={this.changeZipFees[zip]}
              fieldName="zipFee"
            />
          );
        })}
        </tbody>
      </table>
    );
  }

  /**
   * Set fees by location dialog
   */
  setFeesByLocationDialog() {
    const {showFeesByLocation, selectedJobTypeId, amcLocationView} = this.state;
    const {jobType} = this.props;
    const isDefault = this.getSelectedCustomer() === DEFAULT_CUSTOMER;
    const feeProp = isDefault ? 'fees' : 'customerFees';
    const selectedFee = jobType.get(feeProp).filter(fee => fee.getIn(['jobType', 'id']) === selectedJobTypeId);
    const fees = jobType.get('fees').map(fee => Immutable.fromJS({
      name: fee.getIn(['jobType', 'title']),
      value: fee.getIn(['jobType', 'id'])
    }));
    let dialog;
    if (!selectedFee.count()) {
      dialog = null;
    } else {
      dialog = (
        <Dialog
          title={
            <div>
              <h3 style={style.setFeesTitle}>Set Fees by Location</h3>
              {amcLocationView === 'state' && false &&
                <div style={style.setFeesMargin}>
                  <VpPlainDropdown
                    options={fees}
                    value={selectedJobTypeId}
                    onChange={this.amcLocationSetJobType}
                    name="jobType"
                    label="Job Type"
                  />
                </div>
              }
              {amcLocationView !== 'state' &&
                <div className={styles['back-to-state-view']}>
                  <p style={style.setFeesMargin}>
                    <a className="link" style={style.noWrap} onClick={this.switchToStateView}>
                      Back to state view
                    </a>
                  </p>
                </div>
              }
            </div>
          }
          actions={this.feeByLocationDialogAction()}
          modal
          open={showFeesByLocation}
          contentStyle={{width: '90%', maxWidth: 'none'}} // Thanks MUI for using inline styles that I can't override through external stylesheets without relying on !important
          autoScrollBodyContent
        >
          <div>
            <div>
              {amcLocationView === 'state' &&
                this.amcLocationState()
              }
              {amcLocationView === 'county' &&
               this.amcLocationCounty()
              }
              {amcLocationView === 'zip' &&
               this.amcLocationZip()
              }
            </div>
          </div>
        </Dialog>
      );
    }
    return dialog;
  }

  /**
   * Apply state fees to all counties or zips
   */
  applyStateFeesToAll() {
    const {applyStateToAll, applyStateToAllType} = this.state;
    return (
      <Dialog
        title={`Apply state fees to all ${applyStateToAllType}`}
        actions={this.applyStateToAllActions()}
        modal
        open={applyStateToAll}
      >
        By clicking "Apply," the fee currently set for this state will be applied to all {applyStateToAllType} in this state.
      </Dialog>
    );
  }

  render() {
    const {jobTypes, fees, defaultJobTypes, createFeeMap, isAmc = false} = this.props;
    const feeMap = createFeeMap(fees);
    const selectedCustomer = this.getSelectedCustomer();
    const jobTypeMap = this.createJobTypeMap(defaultJobTypes);
    const defaultSelected = selectedCustomer === DEFAULT_CUSTOMER;

    return (
      <div className="row">

        <div className="col-md-12">
          <table className={`data-table ${styles['job-type-table']}`}>
            {defaultSelected && !isAmc &&
             <colgroup>
               <col width="4%" />
               <col width="75%" />
               <col width="21%" />
             </colgroup>
            }
            {!defaultSelected &&
             <colgroup>
               <col width="4%" />
               <col width="37%" />
               <col width="38%" />
               <col width="21%" />
             </colgroup>
            }
            {isAmc &&
             <colgroup>
               <col width="4%" />
               <col width="54%" />
               <col width="18%" />
               <col width="10%" />
             </colgroup>
            }
            {this.createColumns(selectedCustomer)}
            <tbody>
              {this.createBody(jobTypes, feeMap, jobTypeMap, selectedCustomer)}
            </tbody>
          </table>
        </div>

        {/*AMC set fees by location*/}
        {isAmc &&
          <div>
            {this.setFeesByLocationDialog()}
            {this.applyStateFeesToAll()}
          </div>
        }
      </div>
    );
  }
}
