import React, {Component, PropTypes} from 'react';
import {connect} from 'react-redux';
import moment from 'moment';
import {Confirm, CoverageForm, CoverageCounties, NoAppraiserSelected} from 'components';
import {
  getLicenses,
  setProp,
  getCounties,
  uploadFile,
  submitCoverage,
  searchAsc,
  selectAsc,
  clearForm,
  prepareDelete,
  deleteCoverage,
  closeDeleteModal,
  setSelectedForm,
  setAsPrimary,
  formInterface
} from 'redux/modules/coverage';
import Immutable from 'immutable';
import ReactTooltip from 'react-tooltip';
import {capitalizeWords} from 'helpers/string';

import {Dialog} from 'material-ui';

/**
 * Style for individual components here
 */
const styles = {
  icon: {
    cursor: 'pointer'
  },
  counties: {
    position: 'fixed',
    top: 0,
    bottom: 0,
    left: 0,
    right: 0,
    width: '100%',
    height: '100%'
  },
  tableHeader: {margin: 0},
  tooltip: {top: 18},
  coverageTable: {width: '100%', marginTop: '10px'}
};

/**
 * Update coverage areas for an appraiser
 */
@connect(
  state => ({
    auth: state.auth,
    coverage: state.coverage,
    customer: state.customer
  }),
  {
    getLicenses,
    setProp,
    getCounties,
    uploadFile,
    submitCoverage,
    searchAsc,
    selectAsc,
    clearForm,
    prepareDelete,
    deleteCoverage,
    closeDeleteModal,
    setSelectedForm,
    setAsPrimary,
  })
export default class Coverage extends Component {
  static propTypes = {
    // Coverage
    coverage: PropTypes.instanceOf(Immutable.Map),
    // Auth
    auth: PropTypes.instanceOf(Immutable.Map),
    // Customer reducer
    customer: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Retrieve licenses
    getLicenses: PropTypes.func.isRequired,
    // Set a default value
    setProp: PropTypes.func.isRequired,
    // Get counties
    getCounties: PropTypes.func.isRequired,
    // Upload license document
    uploadFile: PropTypes.func.isRequired,
    // Submit new coverage area
    submitCoverage: PropTypes.func.isRequired,
    // Search appraisers via ASC
    searchAsc: PropTypes.func.isRequired,
    // Select ASC result
    selectAsc: PropTypes.func.isRequired,
    // Clear form
    clearForm: PropTypes.func.isRequired,
    // Prepare for deleting coverage
    prepareDelete: PropTypes.func.isRequired,
    // Delete coverage area
    deleteCoverage: PropTypes.func.isRequired,
    // Close delete modal
    closeDeleteModal: PropTypes.func.isRequired,
    // Select an existing form
    setSelectedForm: PropTypes.func.isRequired,
    // set a license as the primary
    setAsPrimary: PropTypes.func.isRequired,
  };

  constructor(props) {
    super(props);

    this.state = {
      // Default to no county shown
      countyShow: '',
      // Default to sidebar closed
      showNav: false
    };

    this.submitCoverage = ::this.submitCoverage;
    this.searchAsc = ::this.searchAsc;
    this.closeModal = ::this.closeModal;
    this.newCoverage = ::this.newCoverage;
    this.deleteCoverage = ::this.deleteCoverage;
    this.closeDeleteModal = ::this.closeDeleteModal;
    this.closeFormIncomplete = ::this.closeFormIncomplete;
    this.toggleCountyShow = ::this.toggleCountyShow;
    this.createLicenseBindings(props.coverage.get('licenses'));
  }

  createLicenseBindings(licenses) {
    this.editCoverages = {};
    this.selectedCountiesTogglers = {};
    this.setLicenseAsPrimary = {};
    this.prepareDeletes = {};

    licenses.forEach(license => {
      this.editCoverages[license.get('id')] = this.editCoverage.bind(this, license);
      this.selectedCountiesTogglers[license.get('id')] = this.toggleSelectedCounties.bind(this, license);
      this.setLicenseAsPrimary[license.get('id')] = this.setAsPrimary.bind(this, license.get('id'));
      this.prepareDeletes[license.get('id')] = this.props.prepareDelete.bind(null, license);
    });
  }

  /**
   * Retrieve coverage areas and resize table on mount
   */
  componentDidMount() {
    const {auth, getLicenses, customer} = this.props;
    const user = auth.get('user');
    const userId = user.get('id');
    // If we already have a user, init
    if (userId) {
      const selectedAppraiser = customer.get('selectedAppraiser');
      // Customer view, none selected
      if (user.get('type') === 'customer' && !selectedAppraiser) {
        return;
      }
      getLicenses(user, selectedAppraiser);
    }
  }

  /**
   * Set default license when it's received
   * @param nextProps
   */
  componentWillReceiveProps(nextProps) {
    const {setProp, coverage, getLicenses, setSelectedForm, customer} = this.props;
    const {auth: nextAuth, coverage: nextCoverage, customer: nextCustomer} = nextProps;
    const licenses = coverage.get('licenses');
    const nextLicenses = nextCoverage.get('licenses');
    const user = nextAuth.get('user');
    const userType = user.get('type');
    // Select appraiser as customer on this state
    if (userType === 'customer' && !customer.get('selectedAppraiser') && nextCustomer.get('selectedAppraiser')) {
      getLicenses(user, nextCustomer.get('selectedAppraiser'));
    }
    // Initial license load
    if (!licenses.count() && nextLicenses.count() && userType !== 'customer') {
      // If there's only one license, but no coverage, initial license is not complete
      if (nextLicenses.count() === 1 && !nextLicenses.getIn([0, 'coverage']).count()) {
        // Set initial license as selected
        setSelectedForm(nextCoverage.getIn(['licenses', 0]));
        // Set initial license completed to default false
        setProp(false, 'initialLicenseComplete');
      }
    }
    // License has been selected
    if (!coverage.get('licenseSelected') && nextCoverage.get('licenseSelected')) {
      // Go ahead and open the modal
      this.setState({
        showNav: true
      });
    }
    // After submit is finished, refresh the table so we can get counties by name
    if (!coverage.get('submitSuccess') && nextCoverage.get('submitSuccess') &&
        !nextCoverage.get('gettingCoverage')) {
      getLicenses(nextAuth.get('user'));
    }
    // Submit fail
    if (typeof coverage.get('submitSuccess') === 'undefined' && nextCoverage.get('submitSuccess') === false) {
      this.setState({
        showFormIncomplete: true
      });
    }

    if (nextCoverage && nextCoverage.get('licenses').count()) {
      this.createLicenseBindings(nextCoverage.get('licenses'));
    }
  }

  setAsPrimary(licenseId) {
    const {setAsPrimary, getLicenses, auth} = this.props;
    const user = auth.get('user');
    setAsPrimary(user, licenseId).then(() => {
      getLicenses(user);
    });
  }

  /**
   * Update form
   * @param event
   */
  searchAsc(event) {
    let isAmc = false;
    // AMC search
    if (typeof event === 'string') {
      event = arguments[1];
      isAmc = true;
    }
    const {name, value} = event.target;
    const {searchAsc, coverage, setProp, selectAsc, clearForm} = this.props;
    setProp(value, 'form', name);
    if (isAmc) {
      if (typeof value === 'string' && value.length) {
        const form = coverage.get('form');
        selectAsc(Immutable.fromJS({
          licenseNumber: value,
          licenseState: {
            code: form.get('state')
          },
          licenseExpiresAt: form.get('expiresAt', moment().add(1, 'days').toDate()),
          certifications: null,
          document: form.get('document', null),
          id: form.get('id', null),
        }));
      } else {
        clearForm();
      }
      // setProp(typeof value === 'string' && value.length, 'ascSelected');
    // If searching for license
    } else if (name === 'number' && !isAmc) {
      searchAsc({licenseNumber: value, licenseState: coverage.getIn(['form', 'state'])});
    }
  }

  /**
   * Prepare to submit a new coverage
   */
  submitCoverage(disabled) {
    // Show form incomplete dialog
    if (disabled.counties || disabled.document) {
      this.setState({
        showFormIncomplete: true,
        incompleteCounties: disabled.counties ? 'At least one county must be selected' : false,
        incompleteDocument: disabled.document ? 'A license document must be uploaded' : false
      });
      return;
    }
    const {auth, coverage, submitCoverage} = this.props;
    // Get form
    const form = coverage.get('form');
    const user = auth.get('user');
    // Format format to send to the backend
    let formFormatted = form
      .set('expiresAt', moment(form.get('expiresAt')).format());
    // Editing
    if (coverage.get('editing') && user.get('type') === 'appraiser') {
      formFormatted = formFormatted
        .remove('state')
        .remove('number');
    }
    // Remove expiration date is no license
    if (!formFormatted.get('document') && !formFormatted.get('number')) {
      formFormatted = formFormatted.set('expiresAt', moment().add(1, 'days').format());
    }

    if (user.get('type') === 'amc' && coverage.get('isUsingAlias') === false) {
      formFormatted = formFormatted.set('alias', null);
    }

    // Submit
    submitCoverage(user, formFormatted, coverage.get('editing'))
      .then(res => {
        if (!res.error) {
          // Close nav
          this.setState({
            showNav: false
          });
        }
      });
  }

  /**
   * Edit existing coverage
   * @param row Current row
   */
  editCoverage(row) {
    // Set selected license
    this.props.setSelectedForm(row);
  }

  /**
   * Delete coverage
   */
  deleteCoverage() {
    const {auth, coverage} = this.props;
    this.props.deleteCoverage(auth.get('user'), coverage.get('deleteCoverage'));
  }

  /**
   * Callback on modal close for deleting coverage
   */
  closeDeleteModal() {
    this.props.closeDeleteModal();
  }

  /**
   * Display selected counties
   * @param license
   */
  toggleSelectedCounties(license) {
    if (license.get('id') === this.state.countyShow) {
      this.hideSelectedCounties();
    } else {
      this.showSelectedCounties(license.get('id'));
    }
  }

  /**
   * Select counties for a license
   */
  showSelectedCounties(licenseId) {
    this.setState({
      countyShow: licenseId
    });
  }

  /**
   * Hide selected counties
   */
  hideSelectedCounties() {
    this.setState({
      countyShow: ''
    });
  }

  /**
   * Toggle show county
   */
  toggleCountyShow() {
    this.setState({
      countyShow: !this.state.countyShow
    });
  }

  /**
   * Close modal
   */
  closeModal() {
    const {coverage, clearForm, setProp} = this.props;
    // Deselect license if not on the first one
    if (coverage.get('initialLicenseComplete')) {
      setProp(false, 'licenseSelected');

      this.setState({
        showNav: false
      });
      // Clear form
      clearForm();
    }
  }

  /**
   * Start a new coverage item
   */
  newCoverage() {
    const {setProp, coverage} = this.props;
    // Open modal
    this.setState({
      showNav: true
    });
    // Editing false
    setProp(false, 'editing');
    // Revert all props
    setProp(Immutable.fromJS(formInterface), 'form');
    // Set ASC as not selected
    setProp(false, 'ascSelected');
    // Revert county and zip list
    setProp(Immutable.List(), 'countyList');
    setProp(Immutable.List(), 'zipList');
    // Default state to Alabama
    setProp(coverage.get('nextAvailableState'), 'form', 'state');
  }

  /**
   * Actions cell
   * @param license Row from fixed-data-table
   * @param userType
   */
  actionCell(license, userType) {
    let editText = 'Edit';
    if (userType === 'customer') {
      editText = 'View';
    }
    const editCellContent = (
      <div className="pull-left" data-tip data-for="edit" style={ styles.icon } onClick={this.editCoverages[license.get('id')]}>
        <i className="material-icons">mode_edit</i>
        <ReactTooltip id="edit" place="bottom" type="dark" effect="solid" offset={styles.tooltip}>
          <span>{editText}</span>
        </ReactTooltip>
      </div>
    );

    if (license.get('isPrimary') || userType === 'customer') {
      return (
        <span>
          {editCellContent}
        </span>
      );
      // Any other license can be edited or deleted
    } else {
      return (
        <span>
          {editCellContent}
          <div className="pull-left" data-tip data-for="delete" style={ styles.icon }
               onClick={this.prepareDeletes[license.get('id')]}>
            <i className="material-icons">delete</i>
            <ReactTooltip id="delete" place="bottom" type="dark" effect="solid" offset={styles.tooltip}>
              <span>Delete</span>
            </ReactTooltip>
          </div>
        </span>
      );
    }
  }

  /**
   * License doc for table
   */
  licenseDoc(license) {
    let doc;
    if (license && license.getIn(['document', 'url'])) {
      doc = (
        <a style={{ color: '#17A1E5' }}
           href={license.getIn(['document', 'url'])}
           target="_blank">View Document</a>
      );
    } else {
      doc = <div />;
    }
    return doc;
  }

  /**
   * Close coverage form incomplete dialog
   */
  closeFormIncomplete() {
    this.setState({
      showFormIncomplete: false
    });
    const {setProp, coverage} = this.props;
    if (coverage.get('errors')) {
      setProp(false, 'errors');
    }
  }

  /**
   * Submit error messages
   */
  submitErrors(errors) {
    if (!errors) {
      return <div/>;
    }

    const errorMessages = errors.toList();

    return (
      <div>
        {errorMessages.map((error, key) => <p key={key}>{error}</p>)}
      </div>
    );
  }

  /**
   * Appraiser only headers
   */
  appraiserHeaders() {
    return ([
      <th className="text-center" key={0}><label className="control-label" style={styles.tableHeader}>License Type</label></th>,
      <th className="text-center" key={1}><label className="control-label" style={styles.tableHeader}>FHA</label></th>,
      <th className="text-center" key={2}><label className="control-label" style={styles.tableHeader}>Commercial</label></th>
    ]);
  }

  /**
   * Appraiser only fields
   */
  appraiserFields(license) {
    return ([
      <td className="text-center" key={0}>{ capitalizeWords(license.get('certifications').join(',').replace('-', ' ')) }</td>,
      <td className="text-center" key={1}>{ license.get('isFhaApproved') ? 'Yes' : 'No' }</td>,
      <td className="text-center" key={2}>{ license.get('isCommercial') ? 'Yes' : 'No' }</td>
    ]);
  }

  render() {
    const {coverage, auth, customer, uploadFile, getCounties, clearForm, setProp, selectAsc} = this.props;
    // Submission errors
    const errors = this.submitErrors(coverage.get('errors'));

    const {showNav, showFormIncomplete, incompleteCounties, incompleteDocument} = this.state;
    // Confirm delete body
    const deleteModalBody = (<div>Are you sure you want to delete the selected coverage?</div>);
    const showDeleteModal = coverage.get('showDeleteModal') || false;
    const userType = auth.getIn(['user', 'type']);

    // No customer selected for current appraiser
    if (userType === 'customer' && !customer.get('selectedAppraiser')) {
      return <NoAppraiserSelected/>;
    }

    return (
      <div>
        {userType !== 'customer' &&
          <div className="row">
            <div className="col-md-12 text-right">
              <button className="btn btn-raised btn-info" onClick={this.newCoverage}>
                Add State
              </button>
            </div>
          </div>
        }
        <table className="data-table" style={styles.coverageTable}>
          <thead>
          <tr>
            <th className="text-center"><label className="control-label" style={styles.tableHeader}>State</label></th>
            <th className="text-center"><label className="control-label" style={styles.tableHeader}>License</label></th>
            {userType === 'appraiser' &&
              this.appraiserHeaders()
            }
            <th className="text-center"><label className="control-label" style={styles.tableHeader}>Expiration Date</label></th>
            <th className="text-center"><label className="control-label" style={styles.tableHeader}>License Doc</label></th>
            <th className="text-center"><label className="control-label" style={styles.tableHeader}>Counties</label></th>
            {userType !== 'amc' &&
              <th className="text-center"><label className="control-label" style={styles.tableHeader}>Primary</label></th>
            }
            <th className="text-center"><label className="control-label" style={styles.tableHeader}>Actions</label></th>
          </tr>
          </thead>
          <tbody>
          {coverage.get('licenses').map((license, index) => {
            const counties = license.get('coverage').map((coverage) => coverage.getIn(['county', 'title']));

            return (
              <tr key={ index }>
                <td className="text-center">{ license.getIn(['state', 'name']) }</td>
                <td className="text-center">{ license.get('number') }</td>
                {userType === 'appraiser' &&
                  this.appraiserFields(license)
                }
                <td className="text-center">{ license.get('expiresAt') ?
                                              moment(license.get('expiresAt')).format('MM/DD/YYYY') : '' }</td>
                <td className="text-center">{this.licenseDoc(license)}</td>
                <td className="text-center">
                    <span style={ styles.icon } onClick={this.selectedCountiesTogglers[license.get('id')]}>
                      <i className="material-icons">list</i>
                    </span>
                  <CoverageCounties
                    counties={ counties }
                    show={ this.state.countyShow === license.get('id') }
                  />
                  {this.state.countyShow === license.get('id') &&
                   <div
                     style={styles.counties}
                     onClick={this.toggleCountyShow}
                   >
                   </div>
                  }
                </td>
                {userType !== 'amc' &&
                  <td className="text-center">
                    <input name="primary" type="radio" defaultChecked={license.get('isPrimary')}
                           onClick={this.setLicenseAsPrimary[license.get('id')]}
                           disabled={userType === 'customer'}/>
                  </td>
                }
                <td className="text-center">{ this.actionCell(license, userType) }</td>
              </tr>
            );
          })}
          </tbody>
        </table>

        {/*Add state modal*/}
        {showNav &&
         <CoverageForm
           clearForm={clearForm}
           getCounties={getCounties}
           uploadFile={uploadFile}
           coverage={coverage}
           showNav={showNav}
           closeModal={this.closeModal}
           searchAsc={this.searchAsc}
           submitCoverage={this.submitCoverage}
           selectFunction={selectAsc}
           form={coverage.get('form')}
           userType={userType}
           isDisabled={userType === 'customer'}
           setProp={setProp}
         />}
        {/*Confirm delete*/}
        <Confirm
          show={showDeleteModal}
          submit={this.deleteCoverage}
          body={deleteModalBody}
          title="Delete coverage area"
          hide={this.closeDeleteModal}/>
        {/*Incomplete form*/}
        <Dialog
          title="Cannot submit coverage"
          actions={<button
               label="Close"
               className="btn btn-raised btn-info"
               onClick={this.closeFormIncomplete}>
               Close
               </button>}
          modal
          open={showFormIncomplete || false}
        >
          <div>
            <p>Please correct the following:</p>
            {!!incompleteCounties && <p>{incompleteCounties}</p>}
            {!!incompleteDocument && <p>{incompleteDocument}</p>}
            {errors}
          </div>
        </Dialog>
      </div>
    );
  }
}
