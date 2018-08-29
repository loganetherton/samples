import React, { Component, PropTypes } from 'react';
import ReactDOM from 'react-dom';
import Immutable from 'immutable';
import { connect } from 'react-redux';
import {JobTypesTable, JobTypeAmcSelector} from 'components';
import {
  getJobTypes,
  getCustomerJobTypes,
  getCustomers,
  setProp,
  selectCustomer,
  getFees,
  handleRowSelect,
  setFeeValue,
  saveChanges,
  DEFAULT_CUSTOMER,
  applyDefaultFees,
  createBatchRequests,
  getFeeTotals,
  changeSearchValue,
  sortColumn,
  createFeeMap,
  resetSort,
  changeLocation,
  getCounties,
  getZips,
  saveAmcFees,
  getAmcLocationFees,
  setAmcLocationFees
} from 'redux/modules/jobType';
import {
  NoAppraiserSelected,
  ProgressButton,
  Void
} from 'components';

import {Dialog} from 'material-ui';

const styles = {
  fees: {
    paddingLeft: '0px'
  },
  actionButtonsWrapper: {height: '45px'},
  customerList: {padding: '7px 0 0 0'}
};

// Fluid padding on each side
const fluidPadding = 15;

@connect(
  state => ({
    auth: state.auth,
    jobType: state.jobType,
    browser: state.browser,
    customer: state.customer
  }),
  {
    getJobTypes,
    getCustomers,
    setProp,
    selectCustomer,
    getFees,
    handleRowSelect,
    setFeeValue,
    saveChanges,
    getCustomerJobTypes,
    applyDefaultFees,
    getFeeTotals,
    changeSearchValue,
    sortColumn,
    resetSort,
    changeLocation,
    getCounties,
    getZips,
    saveAmcFees,
    getAmcLocationFees,
    setAmcLocationFees
  })
export default class JobTypes extends Component {
  static propTypes = {
    // Auth
    auth: PropTypes.instanceOf(Immutable.Map),
    // Browser
    browser: PropTypes.object,
    // Job type
    jobType: PropTypes.instanceOf(Immutable.Map),
    // Retrieve all available job tyoes
    getJobTypes: PropTypes.func.isRequired,
    // Get customer job types
    getCustomerJobTypes: PropTypes.func.isRequired,
    // Get customers for the logged in appraiser
    getCustomers: PropTypes.func.isRequired,
    // Set a prop manually
    setProp: PropTypes.func.isRequired,
    // Select a customer
    selectCustomer: PropTypes.func.isRequired,
    // Get fees for selected customer
    getFees: PropTypes.func.isRequired,
    // Handle selecting/deselecting a row
    handleRowSelect: PropTypes.func.isRequired,
    // Set a fee value
    setFeeValue: PropTypes.func.isRequired,
    // Save changes
    saveChanges: PropTypes.func.isRequired,
    // Apply default fees
    applyDefaultFees: PropTypes.func.isRequired,
    // Sign up
    signUp: PropTypes.bool,
    // Get fee totals for displaying next to customer names
    getFeeTotals: PropTypes.func.isRequired,
    // Change search value
    changeSearchValue: PropTypes.func.isRequired,
    // Sort by a column
    sortColumn: PropTypes.func.isRequired,
    // Reset sorting when change customer
    resetSort: PropTypes.func.isRequired,
    // Change fee location value
    changeLocation: PropTypes.func.isRequired,
    // Get counties in a state
    getCounties: PropTypes.func.isRequired,
    // Get zips in a state
    getZips: PropTypes.func.isRequired,
    // Save AMC fees
    saveAmcFees: PropTypes.func.isRequired,
    // Get AMC location fees
    getAmcLocationFees: PropTypes.func.isRequired,
    // Set AMC location fees
    setAmcLocationFees: PropTypes.func.isRequired,
    // Customer reducer
    customer: PropTypes.instanceOf(Immutable.Map).isRequired
  };

  /**
   * Set the initial AMC selector width to 0
   */
  constructor(props) {
    super(props);
    this.state = {
      amcSelectorWidth: 0,
      initialFees: false,
      saveState: '',
      applyFeesState: '',
      saveDisabled: '',
      setDefaultError: false
    };
    this.saveChanges = ::this.saveChanges;
    this.applyDefault = ::this.applyDefault;
    this.closeSetDefaultError = ::this.closeSetDefaultError;
    this.getAmcLocationFees = ::this.getAmcLocationFees;
    this.setAmcLocationFees = ::this.setAmcLocationFees;
  }

  /**
   * Retrieve job types on load
   */
  componentDidMount() {
    const {getJobTypes, auth, getCustomers, setProp, getFeeTotals, customer} = this.props;
    // Retrieve job types
    getJobTypes();
    const user = auth.get('user');
    let customerToSelect = DEFAULT_CUSTOMER;
    // Retrieve customers and fees
    if (user) {
      const userType = user.get('type');
      if (userType === 'appraiser' || userType === 'amc') {
        getFeeTotals(user);
        getCustomers(user);
        // Handle customer
      } else if (user.get('type') === 'customer' && customer.get('selectedAppraiser')) {
        customerToSelect = user.get('id');
        getCustomers(user);
      }
    }
    // Default to default selected
    setProp(customerToSelect, 'selectedCustomer');
  }

  /**
   * Get customers after login, if loading on this screen
   * @param nextProps
   */
  componentWillReceiveProps(nextProps) {
    const {jobType, setProp} = this.props;
    const {auth: nextAuth, jobType: nextJobtype, customer} = nextProps;
    const nextUser = nextAuth.get('user');
    // Once we have a user
    if (nextUser) {
      let selectedCustomer = nextJobtype.get('selectedCustomer');
      if (nextUser.get('type') === 'customer') {
        if (selectedCustomer === DEFAULT_CUSTOMER && customer.get('selectedAppraiser')) {
          setProp(nextUser.get('id'), 'selectedCustomer');
        }
        selectedCustomer = nextUser.get('id');
      }
      // Default forms
      if (!this.state.initialFees) {
        this.setState({
          initialFees: true
        });
        this.getFees.call(this, nextProps, selectedCustomer);
      }
      // Change customer
      if (jobType.get('selectedCustomer') !== selectedCustomer && !nextJobtype.get('gettingFees')) {
        this.getFees.call(this, nextProps, selectedCustomer);
      }
    }

    // Get the AMC selector
    const amcSelectorNode = ReactDOM.findDOMNode(this.refs['jobtype-amc-selector-col']);

    // Set width if we have it
    if (amcSelectorNode && typeof amcSelectorNode.offsetWidth === 'number') {
      this.setState({
        amcSelectorWidth: amcSelectorNode.offsetWidth + fluidPadding
      });
    }

    // Saving disabled
    const saveDisabled = !!jobType.get('rowValues').toList().filter(row => row.get('error')).count();
    if (this.state.saveDisabled !== saveDisabled) {
      this.setState({
        saveState: saveDisabled ? 'disabled' : ''
      });
    }
  }

  /**
   * Retrieve fees when customer is selected
   * @param nextProps
   * @param selectedCustomer Selected customer ID
   */
  getFees(nextProps, selectedCustomer) {
    const user = nextProps.auth.get('user');
    let userId = user.get('id');
    let promise = null;
    const userType = user.get('type');
    // Select appraiser for customer view
    if (userType === 'customer') {
      userId = nextProps.customer.get('selectedAppraiser');
      selectedCustomer = user.get('id');
      if (!userId) {
        return promise;
      }
    }
    const {getFees, getCustomerJobTypes} = this.props;
    // Get only fees for default
    if (selectedCustomer === DEFAULT_CUSTOMER) {
      promise = getFees(userId, null, user.get('type'));
      // Get customer job types and fees
    } else {
      getCustomerJobTypes(user, selectedCustomer);
      promise = getFees(userId, selectedCustomer, userType);
    }
    return promise;
  }

  /**
   * Apply default fees to currently selected customer
   */
  applyDefault() {
    const {applyDefaultFees, jobType, auth, getFeeTotals} = this.props;
    return applyDefaultFees(auth.getIn(['user', 'id']), jobType.get('selectedCustomer'))
    .then(res => {
      if (!res.error) {
        return this.getFees(this.props, jobType.get('selectedCustomer'));
      } else {
        throw new Error('save');
      }
    })
    .then(res => {
      if (!res.error) {
        return getFeeTotals(auth.get('user'));
      } else {
        throw new Error('totals');
      }
    })
    .catch(() => {
      this.setState({
        setDefaultError: true
      });
    });
  }

  /**
   * Save changes to fee table
   */
  saveChanges() {
    const {auth, jobType, saveChanges, saveAmcFees} = this.props;
    const userId = auth.getIn(['user', 'id']);
    const userType = auth.getIn(['user', 'type']);
    const customerId = jobType.get('selectedCustomer');
    const createRequest = createBatchRequests.bind(this, auth.get('user'), customerId);
    const feeProp = customerId === DEFAULT_CUSTOMER ? 'fees' : 'customerFees';
    // Get fee values
    const fees = jobType.get(feeProp);
    const requests = [];
    // Iterate fees and update backend
    fees.forEach(fee => {
      // Cast to float
      fee = fee.set('amount', parseFloat(fee.get('amount')));
      // Set job type to int
      fee = fee.set('jobType', fee.getIn(['jobType', 'id']));
      // Set qualifier (location)
      if (userType === 'amc' && customerId === DEFAULT_CUSTOMER) {
        fee = fee.set('qualifier', fee.get('qualifier', 'default'));
        requests.push(fee.toJS());
      } else if (userType === 'appraiser' || userType === 'amc') {
        // Update
        if (fee.get('id') && !fee.get('removed')) {
          requests.push(fee);
          // Delete
        } else if (fee.get('id') && fee.get('removed')) {
          requests.push(fee);
          // Create
        } else if (!fee.get('id')) {
          requests.push(fee);
        }
      }
    });
    // Appraiser
    if (userType === 'appraiser' || (userType === 'amc' && customerId !== DEFAULT_CUSTOMER)) {
      const deleteRequests = requests.filter(request => request.get('removed'));
      const updateRequests = requests.filter(request => request.get('id') && !request.get('removed'));
      const newRequests = requests.filter(request => !request.get('id'));

      return saveChanges([[deleteRequests, 'delete'], [updateRequests, 'update'], [newRequests, 'new']].map(request => {
        if (request[0].length) {
          return createRequest(request[0], request[1]);
        }
      }).filter(request => request), customerId);
      // AMC
    } else if (userType === 'amc') {
      const filteredRequests = requests.filter(request => !request.removed);
      return saveAmcFees(userId, filteredRequests);
    }
  }

  /**
   * Determine if appraiser can edit fees after registration
   * @param selectedCustomer
   * @param userType
   */
  canAppraiserEditFees(selectedCustomer, userType) {
    if (userType === 'customer') {
      return false;
    }
    const {jobType, signUp} = this.props;
    if (!jobType.get('customers')) {
      return true;
    }
    const thisCustomer = jobType.get('customers')
      .filter(customer => customer.get('id') === selectedCustomer).get(0);
    // Determine if appraiser can edit
    if (signUp || !thisCustomer) {
      return true;
    }
    return thisCustomer.getIn(['settings', 'canAppraiserChangeJobTypeFees'], true);
  }

  /**
   * Get AMC fees for a location type
   * @param type Location type
   * @param jobTypeId
   * @param stateCode Currently selected state
   * @param customerId Customer ID
   */
  getAmcLocationFees(type, jobTypeId, stateCode, customerId) {
    const {getAmcLocationFees, auth, jobType} = this.props;
    let saveFirst = false;
    // Make sure we have the location in the backend
    if (type === 'states') {
      const inBackend = jobType.getIn(['amcFeesInBackend', jobTypeId]);
      if (!inBackend) {
        saveFirst = true;
      }
    }
    // Save changes before opening
    if (saveFirst) {
      return this.saveChanges.call(this)
        .then(() => {
          return getAmcLocationFees(auth.getIn(['user', 'id']), jobTypeId, type, stateCode, customerId);
        });
    } else {
      return getAmcLocationFees(auth.getIn(['user', 'id']), jobTypeId, type, stateCode, customerId);
    }
  }

  /**
   * Set AMC fees for a location type
   * @param type Location type
   * @param jobTypeId
   * @param stateCode Currently selected state for county and zip
   * @param setAll Set all counties or zips by state fee
   * @param customerId Customer ID
   */
  setAmcLocationFees(type, jobTypeId, stateCode, setAll = {}, customerId) {
    const {jobType, auth, setAmcLocationFees} = this.props;
    const requests = [];
    if (type === 'states') {
      jobType.get('amcState').forEach((amount, code) => {
        const thisRequest = {
          state: code,
          amount: parseFloat(amount),
          applyStateAmountToAllCounties: false,
          applyStateAmountToAllZips: false
        };
        // Apply to all zips or counties
        if (setAll.code === code) {
          if (setAll.type === 'counties') {
            thisRequest.applyStateAmountToAllCounties = true;
          } else {
            thisRequest.applyStateAmountToAllZips = true;
          }
        }
        // If fee is removed, then delete from the backend
        if (amount !== '') {
          requests.push(thisRequest);
        }
      });
    } else if (type === 'counties') {
      jobType.get('amcCounty').forEach((amount, code) => {
        // If fee is removed, then delete from the backend
        if (amount !== '') {
          requests.push({
            county: code,
            amount: parseFloat(amount)
          });
        }
      });
    } else if (type === 'zips') {
      jobType.get('amcZip').forEach((amount, code) => {
        // If fee is removed, then delete from the backend
        if (amount !== '') {
          requests.push({
            zip: code,
            amount: parseFloat(amount)
          });
        }
      });
    }
    return setAmcLocationFees(auth.getIn(['user', 'id']), jobTypeId, requests, type, stateCode, customerId);
  }

  /**
   * Close the set default error dialog
   */
  closeSetDefaultError() {
    this.setState({
      setDefaultError: false
    });
  }

  render() {
    const {
      jobType,
      browser,
      changeSearchValue,
      sortColumn,
      resetSort,
      auth,
      changeLocation,
      getCounties,
      getZips,
      signUp,
      customer,
      selectCustomer,
      handleRowSelect,
      setFeeValue,
      setProp
    } = this.props;
    const customers = jobType.get('customers');
    const selectedCustomer = jobType.get('selectedCustomer');
    const jobTypes = jobType.get('jobTypes') || Immutable.List();
    const customerJobTypes = jobType.get('customerJobTypes') || Immutable.List();
    const fees = jobType.get('fees') || Immutable.List();
    const customerFees = jobType.get('customerFees') || Immutable.List();
    const userType = auth.getIn(['user', 'type']);
    const isAppraiser = userType === 'appraiser';
    const isAmc = userType === 'amc';
    // Determine if appraiser can edit fees for this customer
    const canEditFees = this.canAppraiserEditFees.call(this, selectedCustomer, userType);

    // set the initial column sizes
    let colSizes = {
      amc: 3,
      fees: 9
    };

    if (browser.width < 1500) {
      colSizes = {
        amc: 4,
        fees: 8
      };
    }
    // AMC display or appraiser sign up
    if ((!isAppraiser && !isAmc) || signUp) {
      colSizes = {
        fees: 12
      };
      styles.fees = {};
    }

    // No customer selected for current appraiser
    if (userType === 'customer') {
      if (!customer.get('selectedAppraiser')) {
        return <NoAppraiserSelected/>;
      }
    }

    return (
      <div>
        <div className="row">
          {((isAppraiser || isAmc) && !signUp) &&
           <div className={`col-md-${colSizes.amc}`} ref="jobtype-amc-selector-col" style={styles.customerList}>
             <JobTypeAmcSelector
               selectCustomer={selectCustomer}
               customers={customers}
               selectedCustomer={selectedCustomer}
               totals={jobType.get('totals')}
               changeSearchValue={changeSearchValue}
               resetSort={resetSort}
             />
           </div>
          }
          <div className={`col-md-${colSizes.fees}`} style={styles.fees}>
            {canEditFees &&
              <div className="row">
                <div className="text-center" style={styles.actionButtonsWrapper}>
                  <Void pixels={10}/>

                  <ProgressButton onClick={this.saveChanges} state={this.state.saveState} durationSuccess={1000}>
                    Save changes
                  </ProgressButton>
                  &nbsp; &nbsp;
                  {selectedCustomer !== DEFAULT_CUSTOMER && userType === 'amc' &&
                   <ProgressButton onClick={this.applyDefault} state={this.state.applyFeesState} durationSuccess={1000} applyDefaultStyle>
                     Apply Default Fees
                   </ProgressButton>
                  }
                </div>
              </div>
            }
            {!canEditFees &&
              <Void pixels="45"/>
            }
            <JobTypesTable
              jobType={jobType}
              defaultJobTypes={jobTypes}
              jobTypes={selectedCustomer === DEFAULT_CUSTOMER ? jobTypes : customerJobTypes}
              selectedCustomer={selectedCustomer}
              handleRowSelect={handleRowSelect}
              setFeeValue={setFeeValue}
              fees={selectedCustomer === DEFAULT_CUSTOMER ? fees : customerFees}
              amcSelectorWidth={this.state.amcSelectorWidth}
              canEditFees={canEditFees}
              changeSearchValue={changeSearchValue}
              sortColumn={sortColumn}
              createFeeMap={createFeeMap}
              isAmc={userType === 'amc'}
              changeLocation={changeLocation}
              getCounties={getCounties}
              getZips={getZips}
              getAmcLocationFees={this.getAmcLocationFees}
              setAmcLocationFees={this.setAmcLocationFees}
              setProp={setProp}
            />
            {canEditFees &&
              <div className="row">
                <div className="text-center" style={styles.actionButtonsWrapper}>
                  <Void pixels={10}/>

                  <ProgressButton onClick={this.saveChanges} state={this.state.saveState} durationSuccess={1000}>
                    Save changes
                  </ProgressButton>
                  &nbsp; &nbsp;
                  {selectedCustomer !== DEFAULT_CUSTOMER && userType === 'amc' &&
                   <ProgressButton onClick={this.applyDefault} state={this.state.applyFeesState} durationSuccess={1000} applyDefaultStyle>
                     Apply Default Fees
                   </ProgressButton>
                  }
                </div>
              </div>
            }
          </div>
        </div>
        <Dialog
          open={this.state.setDefaultError}
          actions={
            <button className="btn btn-raised btn-info"
                    onClick={this.closeSetDefaultError}>Close</button>
          }
          title="Failed to apply default fees"
        >
          An error occurred when setting default fees. Please try again.
        </Dialog>
      </div>
    );
  }
}
