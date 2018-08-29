import React, {Component, PropTypes} from 'react';
import SyntheticEvent from 'react/lib/SyntheticEvent';
import {
  VpDateRange,
  MyDropzone,
  ActionButton,
  DividerWithIcon,
  AscSearch,
  LicenseType,
  statesList,
  UploadDialog,
  Alias
} from 'components';
import moment from 'moment';
import Immutable from 'immutable';
import {
  Drawer,
  Divider,
  Snackbar
} from 'material-ui';
import {Void} from 'components';
import {capitalizeWords} from 'helpers/string';

// Style
const style = {
  pointer: {
    cursor: 'pointer'
  },
  dim: {
    opacity: 0.5
  },
  marginLeft: {
    marginLeft: 5
  },
  marginRight: {
    marginRight: 5
  },
  noPadLeft: {
    paddingLeft: 0
  }
};

// MIME type regex for license document upload
const licenseDocMimeType = /(image\/(jpe?g?|png|gif))|(application\/pdf)/;

/**
 * Add coverage area modal
 */
export default class CoverageForm extends Component {
  static propTypes = {
    // Show or hide modal value
    showNav: PropTypes.bool.isRequired,
    // coverage from reducer
    coverage: PropTypes.object.isRequired,
    // Search ASC function
    searchAsc: PropTypes.func.isRequired,
    // Close modal function
    closeModal: PropTypes.func.isRequired,
    // File upload
    uploadFile: PropTypes.func.isRequired,
    // Get counties
    getCounties: PropTypes.func.isRequired,
    // Submit coverage
    submitCoverage: PropTypes.func.isRequired,
    // Clear the form after ASC is selected
    clearForm: PropTypes.func.isRequired,
    // Current license form
    form: PropTypes.instanceOf(Immutable.Map),
    // User type
    userType: PropTypes.string.isRequired,
    // Set prop
    setProp: PropTypes.func.isRequired,
    // Form disabled
    isDisabled: PropTypes.bool.isRequired,
    // Select ASC appraiser
    selectFunction: PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);
    // Date picker values
    this.state = {
      dateTime: moment().format('MM-DD-YYYY'),
      format: 'MM-DD-YYYY',
      inputFormat: 'MM/DD/YYYY',
      viewMode: 'date',
      // Show zips
      showZips: false,
      // File type invalid notification
      fileTypeInvalid: false,
      // Document uploading dialog
      documentUploading: false,
      // Classes
      uploads: null,
    };
  }

  /**
   * Retrieve state on load
   */
  componentDidMount() {
    const {form, getCounties} = this.props;
    // Retrieve counties
    const state = form.get('state');
    if (state) {
      getCounties(state);
    }
  }

  /**
   * Get counties when state is selected
   * @param nextProps
   */
  componentWillReceiveProps(nextProps) {
    const {form, getCounties, coverage} = this.props;
    const {coverage: nextCoverage} = nextProps;
    const thisState = form.get('state');
    const nextState = nextProps.form.get('state');
    // Perform county search
    if (nextState && nextState !== thisState && !nextProps.coverage.get('gettingCounties')) {
      getCounties(nextState);
    }
    // Uploading notification
    if (!coverage.get('uploading') && nextCoverage.get('uploading')) {
      this.setState({
        documentUploading: true
      });
    }
    // Doc finished uploading
    if (!coverage.get('fileUploadSuccess') && nextCoverage.get('fileUploadSuccess')) {
      setTimeout(() => {
        this.setState({
          documentUploading: false
        });
      }, 1000);
    }
  }

  /**
   * Get datepicker, set either to today, or to license expiration date, if default license
   */
  getDatePicker() {
    const {form, isDisabled} = this.props;
    const expires = moment(form.get('expiresAt'));
    return (
      <div style={{ marginTop: '-10px' }}>
        <VpDateRange
          label="License expiration date"
          minDate={moment().add(1, 'days')}
          date={expires}
          changeHandler={::this.updateExpirationDate}
          disabled={isDisabled}
        />
      </div>
    );
  }

  /**
   * Retrieve unique zip codes from list
   * @param value
   * @param index
   * @param self
   * @returns {boolean}
   */
  getUniqueZips(value, index, self) {
    return self.indexOf(value) === index;
  }

  /**
   * Find the index of the selected county in the form list of counties
   * @param selectedCounty
   */
  getCountyIndex(selectedCounty) {
    let countyIndex;
    this.props.coverage.getIn(['form', 'coverage']).forEach((county, index) => {
      // Find index of selected county in form county list
      if (county.get('county') === selectedCounty.get('id')) {
        countyIndex = index;
        return false;
      }
    });
    // Obviously, the county should always be found
    if (typeof countyIndex === 'undefined') {
      throw new Error('Could not determine current county');
    }
    return countyIndex;
  }

  /**
   * Clear the form on reset button press
   */
  clearForm() {
    this.setState({
      showZips: false
    });
    this.props.clearForm();
  }

  /**
   * Update expiration date
   */
  updateExpirationDate(date) {
    this.props.setProp(date, 'form', 'expiresAt');
  }

  /**
   * Update a checkbox
   * @param event
   */
  updateCheckbox(event) {
    const {name} = event.target;
    const {form, setProp} = this.props;
    setProp(!form.get(name), 'form', name);
  }

  /**
   * Update license type
   * @param value New value
   */
  updateLicenseType(value) {
    this.props.setProp(value, 'form', 'certifications', 0);
  }

  /**
   * Change ASC state in license search
   */
  changeAscState(path, code) {
    const {setProp} = this.props;
    setProp(code, 'form', 'state');
  }

  /**
   * Select zips in a county
   */
  showZips(countyId, event) {
    // Prevent this county from getting deselected
    event.stopPropagation();
    // this.props.showZips(countyId);
    this.setState({
      showZips: countyId
    });
  }

  /**
   * Return to county view
   */
  backToCounties() {
    this.setState({
      showZips: false
    });
  }

  /**
   * Add in incoming zips to current zipList and return only unique zips
   * @param coverage
   * @param thisCounty
   */
  addZipsAndReturnOnlyUniqueZips(coverage, thisCounty) {
    // Current selected zipList
    const currentZipList = coverage.get('zipList');
    // Combine the two and retrieve unique
    const combinedZipList = currentZipList.toJS().concat(thisCounty.toJS());
    return combinedZipList.filter(this.getUniqueZips);
  }

  /**
   * Remove zips from current zipList
   * @param coverage
   * @param thisCounty
   */
  removeZipsFromZipList(coverage, thisCounty) {
    // Current selected zipList
    const currentZipList = coverage.get('zipList');
    // Return a zipList with all zips in this county removed
    return currentZipList.filter(zip => thisCounty.indexOf(zip) === -1);
  }

  /**
   * Select a county
   */
  selectCounty(event) {
    const {form, setProp, coverage} = this.props;
    let counties = form.get('coverage');
    let countyList = coverage.get('countyList');
    let countyId;
    // Checkbox click
    if (event instanceof SyntheticEvent) {
      countyId = parseInt(event.target.name, 10);
    } else {
      countyId = event;
    }
    // See if county is already selected
    const countyIndex = countyList.indexOf(countyId);
    // Selected
    if (countyIndex !== -1) {
      // Remove from county list
      countyList = countyList.remove(countyIndex);
      // Remove from coverage
      counties = counties.filter(county => {
        return county.get('county') !== countyId;
      });
      // Not selected, so add
    } else {
      // Current state
      const thisState = coverage.getIn(['states', form.get('state')]);
      // Zips in this county
      const thisCounty = thisState.filter(county => {
        return countyId === county.get('id');
      }).getIn([0, 'zips']);
      // Add zips from this county to zip list and return only unique
      const uniqueZips = this.addZipsAndReturnOnlyUniqueZips.call(this, coverage, thisCounty);
      // Set all zips for this county as selected
      counties = counties.push(Immutable.fromJS({
        county: countyId,
        zips: thisCounty
      }));
      countyList = countyList.push(countyId);
      setProp(Immutable.fromJS(uniqueZips), 'zipList');
    }
    // Set county list
    setProp(counties, 'form', 'coverage');
    setProp(countyList, 'countyList');
  }

  /**
   * Select all zips
   */
  selectAllZips() {
    const {coverage, setProp} = this.props;
    const selectedCounty = this.state.showZips;
    const selectedCountyZips = selectedCounty.get('zips');
    // Add zips from this county to zip list and return only unique
    const uniqueZips = this.addZipsAndReturnOnlyUniqueZips.call(this, coverage, selectedCountyZips);
    const countyIndex = this.getCountyIndex.call(this, selectedCounty);
    setProp(selectedCountyZips, 'form', 'coverage', countyIndex, 'zips');
    setProp(Immutable.fromJS(uniqueZips), 'zipList');
  }

  /**
   * Deselect all zips in the currently selected county
   */
  deselectAllZips() {
    const {coverage, setProp} = this.props;
    const selectedCounty = this.state.showZips;
    const selectedCountyZips = selectedCounty.get('zips');
    // Remove zips from zipList
    const updatedZipList = this.removeZipsFromZipList.call(this, coverage, selectedCountyZips);
    // Find county index
    const countyIndex = this.getCountyIndex.call(this, selectedCounty);
    // Update zip list and this county
    setProp(Immutable.List(), 'form', 'coverage', countyIndex, 'zips');
    setProp(updatedZipList, 'zipList');
  }

  /**
   * Select all counties
   */
  selectAllCounties() {
    const {coverage, form, setProp} = this.props;
    const counties = coverage.getIn(['states', form.get('state')]);
    const countyIds = [];
    const coverageCounties = [];
    const zipList = [];

    counties.map((county) => {
      countyIds.push(county.get('id'));
      coverageCounties.push({
        county: county.get('id'),
        zips: county.get('zips'),
      });

      county.get('zips').map((zip) => {
        zipList.push(zip);
      });
    });

    setProp(Immutable.fromJS(coverageCounties), 'form', 'coverage');
    setProp(Immutable.fromJS(countyIds), 'countyList');
    setProp(Immutable.fromJS(zipList), 'zipList');
  }

  /**
   * De-select all counties
   */
  deselectAllCounties() {
    const {setProp} = this.props;
    setProp(Immutable.List(), 'form', 'coverage');
    setProp(Immutable.List(), 'countyList');
    setProp(Immutable.List(), 'zipList');
  }

  /**
   * Select a zip
   * @param event
   */
  selectZip(event) {
    const {form, setProp, coverage} = this.props;
    const countyId = this.state.showZips.get('id');
    let counties = form.get('coverage');
    let zipList = coverage.get('zipList');
    let zipId;
    // Checkbox click
    if (event instanceof SyntheticEvent) {
      zipId = parseInt(event.target.name, 10);
    } else {
      zipId = parseInt(event, 10);
    }
    // See if zip is already selected
    const zipListIndex = zipList.indexOf(zipId.toString());
    // Find the county that houses this zip
    let countyIndex = -1;
    // Zips already in this county in the coverage array
    let countyZips = [];
    counties.forEach((county, index) => {
      if (county.get('county') === countyId) {
        countyIndex = index;
        countyZips = county.get('zips');
        return false;
      }
    });
    // Selected
    if (zipListIndex !== -1) {
      // Remove from county list
      zipList = zipList.remove(zipListIndex);
      // Remove from this county's zips
      countyZips = countyZips.remove(countyZips.indexOf(zipId.toString()));
      // Not selected
    } else {
      // Add to zip list
      zipList = zipList.push(zipId.toString());
      // Add this zip to this county
      countyZips = countyZips.push(zipId.toString());
    }
    // Put zips back in counties
    counties = counties.setIn([countyIndex, 'zips'], countyZips);
    // Set county list
    setProp(counties, 'form', 'coverage');
    setProp(zipList, 'zipList');
  }

  /**
   * Create listing of counties
   */
  countyOrZipsListing(form) {
    const {coverage, isDisabled} = this.props;
    const showZips = this.state.showZips;
    // List of counties
    const displayCounties = coverage.getIn(['statesCountiesSorted', form.get('state')]);
    // List of selected counties
    const selectedCountyList = coverage.get('countyList');
    // List of selected zips
    const selectedZipList = coverage.get('zipList').map(zip => parseInt(zip, 10));
    // Return jsx
    let display;
    // Show zips
    if (showZips) {
      const zips = showZips.get('zips');
      // No zips available
      if (!zips || !zips.count()) {
        display = (
          <div>
            <ActionButton type="submit" icon="arrow_back" onClick={::this.backToCounties} text="Back to County List" />
            <Void pixels={15}/>
            No Zips to Display
          </div>
        );
      } else {
        // Show zips
        display = (
          <div>
            {!isDisabled &&
              <div className="row" style={{ paddingLeft: '15px' }}>
                <a className="link" onClick={::this.selectAllZips}>Select All</a>
                &nbsp;|&nbsp;
                <a className="link" onClick={::this.deselectAllZips}>Deselect All</a>
              </div>
            }
            <Void pixels={15}/>
            <div className="row">
              {
                (zips.map((zip, index) => {
                  // This zip selected
                  const selected = selectedZipList.indexOf(parseInt(zip, 10)) !== -1;
                  return (
                    <div className="col-md-2" key={index} style={style.pointer}>
                      <div className="row">
                        <div className="col-md-4">
                          <input
                            type="checkbox"
                            name={zip}
                            checked={selected}
                            onChange={::this.selectZip}
                            disabled={isDisabled}
                          />
                        </div>
                        <div className="col-md-8" style={style.noPadLeft} onClick={this.selectZip.bind(this, zip)}>
                          {zip}
                        </div>
                      </div>
                    </div>
                  );
                }))
              }
            </div>
          </div>
        );
      }
      // Show counties
    } else {
      // No counties
      if (!displayCounties) {
        display = (<div></div>);
        // Counties to display
      } else {
        display = (
          <div>
            {!isDisabled &&
              <div className="row" style={{ paddingLeft: '15px', paddingBottom: '10px' }}>
                <a className="link" onClick={::this.selectAllCounties}>Select All</a>
                &nbsp;|&nbsp;
                <a className="link" onClick={::this.deselectAllCounties}>Deselect All</a>
              </div>
            }
            <div className="row">
              {displayCounties.map((counties, index) => {
                return (
                  <div className="col-md-4" key={index}>
                    {counties.map((county, index) => {
                      // Determine if this county is selected
                      const selected = selectedCountyList.indexOf(county.get('id')) !== -1;
                      return (
                        <div key={index} className="row" style={style.pointer}>
                          <div className="col-md-2">
                            <input
                              type="checkbox"
                              name={county.get('id').toString()}
                              checked={selected}
                              onChange={::this.selectCounty}
                              disabled={isDisabled}
                            />
                          </div>
                          <div
                            className="col-md-10"
                            onClick={() => this.selectCounty.call(this, county.get('id'))}>
                            {capitalizeWords(county.get('title').toLowerCase())}
                            {selected &&
                             <span>
                               &nbsp;&nbsp;<a onClick={this.showZips.bind(this, county)} className="link">ZIPs</a>
                             </span>
                            }
                          </div>
                        </div>
                      );
                    })}
                  </div>
                );
              })}
            </div>
          </div>
        );
      }
    }
    return display;
  }

  /**
   * Upload license document
   * @param files
   */
  uploadLicenseDoc(files) {
    // Make sure that we're getting the right MIME type at least
    if (!licenseDocMimeType.test(files[0].type)) {
      this.setState({
        fileTypeInvalid: true
      });
      // Upload if we have the right mime type
    } else {
      this.props.uploadFile('document', files[0]);
    }
  }

  /**
   * Close snackbar
   */
  closeSnackbar() {
    this.setState({
      fileTypeInvalid: false
    });
  }

  /**
   * If submit is disabled
   */
  submitDisabled() {
    const {coverage} = this.props;
    return {
      counties: !coverage.get('countyList').count()
    };
  }

  /**
   * Address change for DBA
   */
  dbaAddressChange(event) {
    const {value, name} = event.target;
    this.props.setProp(value, 'form', name);
  }

  /**
   * Change DBA state
   */
  dbaStateChange(state) {
    this.props.setProp(state, 'form', 'dbaState');
  }

  render() {
    const {
      showNav,
      closeModal,
      submitCoverage,
      coverage,
      form,
      searchAsc,
      userType,
      isDisabled,
      setProp,
      selectFunction
    } = this.props;
    // Coverage form
    const editing = coverage.get('editing') || coverage.get('ascSelected');
    // Don't proceed with no form
    if (!form) {
      return <div />;
    }
    // Remove states already selected for AMC
    let statesToRemove = coverage.get('licenses').map(license => license.getIn(['state', 'code']));
    if (editing) {
      // Remove states for AMC
      if (userType === 'amc') {
        // Remove current state
        statesToRemove = statesList
          .filter(state => coverage.getIn(['form', 'state']) !== state.get('value'))
          .map(state => state.get('value'));
        // display current for editing
      } else if (userType === 'appraiser') {
        statesToRemove = Immutable.List();
      }
    }

    return (
      <Drawer
        width={700}
        openSecondary
        open={showNav}>
        <div>
          <div className="col-md-12">
          <span className="order-details-icon-container hover-red" role="button" onClick={closeModal} style={{ display: 'inline-block', marginBottom: '10px' }}>
            <i className="material-icons">highlight_off</i>
            <span>Close</span>
          </span>
          </div>
          <div className="container-fluid" data-coverage-pane>
            <AscSearch
              {...this.props}
              form={form}
              formChange={userType === 'appraiser' ? searchAsc : searchAsc.bind(this, 'amc')}
              stateChange={::this.changeAscState}
              results={coverage.get('ascResults')}
              stateProp="state"
              licenseProp="number"
              ascSelected={editing}
              withDividers
              noSearch={userType === 'amc'}
              statesToRemove={statesToRemove}
              selectFunction={selectFunction}
            />
            {!!(editing || coverage.get('ascSelected') || userType === 'amc') &&
             <div>
               <div className="row">
                 <div className={userType !== 'amc' ? 'col-md-6' : 'col-md-12'}>
                   {this.getDatePicker()}
                 </div>
                 {userType !== 'amc' &&
                  <div className="col-md-6">
                    <label style={{ cursor: 'pointer' }}>
                      <input name="isFhaApproved" type="checkbox" checked={form.get('isFhaApproved')} disabled={isDisabled}
                             onChange={::this.updateCheckbox}/> FHA Approved
                    </label>
                    <br />
                    <label style={{ cursor: 'pointer' }}>
                      <input name="isCommercial" type="checkbox" checked={form.get('isCommercial')} disabled={isDisabled}
                             onChange={::this.updateCheckbox}/> Commercial
                    </label>
                  </div>
                 }
               </div>

               <Void pixels={20} />

                {userType === 'amc' &&
                  <Alias
                    isUsingAlias={coverage.get('isUsingAlias')}
                    setProp={setProp}
                    form={form}
                    errors={coverage.get('aliasErrors')}
                  />
                }

               <Void pixels={20}/>

               <div className="row">
                 <div className="col-md-12">
                   <Divider/>
                 </div>
                 <div className="col-md-12">
                   <DividerWithIcon
                     label="Certifications"
                     icon="star"
                   />
                 </div>
                 <div className="col-md-12">
                   <Divider/>
                 </div>
               </div>

               {userType !== 'amc' &&
                <div className="row">
                  <div className="col-md-12">
                    <LicenseType
                      form={form}
                      propPath={['certifications', 0]}
                      setProp={::this.updateLicenseType}
                      disabled={isDisabled}
                    />
                  </div>
                </div>
               }
               <div className="row">
                 <div className="col-md-12">
                   <Void pixels={10}/>

                   <MyDropzone
                     {...this.props}
                     onDrop={::this.uploadLicenseDoc}
                     uploadedFiles={form.get('document') ? Immutable.List().push(form.get('document')) : Immutable.List()}
                     instructions="Drag your license document here, or click to select it."
                     inline
                     hideButton={isDisabled}
                   />

                 </div>
               </div>

               <Void pixels={20}/>

               <div className="row">
                 <div className="col-md-12">
                   <Divider/>
                 </div>
                 <div className="col-md-12">
                   <DividerWithIcon
                     label="Counties"
                     icon="assistant_photo"
                   />
                 </div>
                 <div className="col-md-12">
                   <Divider/>
                 </div>
               </div>

               <Void pixels={20}/>

               {/*County or zips*/}
               <div className="row">
                 <div className="col-md-12">
                   {this.countyOrZipsListing.call(this, form)}
                 </div>
               </div>
             </div>
            }

            <Void pixels={15}/>
            {this.state.showZips &&
             <ActionButton type="submit" icon="arrow_back" onClick={::this.backToCounties} text="Back to County List" />
            }

            {!this.state.showZips &&
             <div>
               {/*Action buttons*/}
               {!isDisabled && <ActionButton type="submit" onClick={submitCoverage.bind(this, this.submitDisabled())}/>}
               <ActionButton type="cancel" onClick={closeModal} style={style.marginLeft} />
               {!coverage.get('editing') &&
                <ActionButton type="reset" onClick={::this.clearForm} style={style.marginLeft} />
               }
             </div>
            }

            <Void pixels={15}/>
          </div>
        </div>
        {/*MIME type failure notification*/}
        <Snackbar
          open={this.state.fileTypeInvalid || false}
          message="Only file types .jpg, .gif, .png, and .pdf are accepted"
          autoHideDuration={4000}
          onRequestClose={::this.closeSnackbar}
        />
        {/*Upload document dialog*/}
        <UploadDialog
          message="Your coverage document is uploading. When it is finished, this dialog will close automatically."
          documentUploading={this.state.documentUploading}
        />
      </Drawer>
    );
  }
}
