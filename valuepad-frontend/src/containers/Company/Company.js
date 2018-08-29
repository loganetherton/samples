import React, {Component, PropTypes} from 'react';
import { connect } from 'react-redux';
import Immutable from 'immutable';
import {Tabs, Tab} from 'material-ui';
import { push } from 'redux-router';

import {
  AchForm,
  ActionButton,
  AppraiserCompanyCreate,
  CompanyUsers,
  MyDropzone,
  VpPlainDropdown,
  JobTypesTable,
  ProgressButton
} from 'components';
import {
  changeSelectedCompany,
  getBranches,
  getCompanies,
  getStaff,
  setProp,
  removeProp,
  patchCompany,
  uploadFile,
  getThisCompany,
  createBranch,
  patchBranch,
  resetBranch,
  updateStaff,
  addManager,
  ascSearch,
  selectAscAppraiser,
  inviteAppraiser,
  getUserPermissions,
  setUserPermissions,
  deleteStaff
} from 'redux/modules/company';
import {
  createFeeMap,
  DEFAULT_CUSTOMER,
  getJobTypes,
  handleRowSelect,
  setFeeValue,
  changeSearchValue,
  sortColumn,
  getFees,
  saveProductsCompany
} from 'redux/modules/jobType';
import {downloadW9Form} from 'helpers/genericFunctions';
import {inputGroupClass} from 'helpers/styleHelpers';

const style = {
  marginTop20: {marginTop: '20px'}
};

@connect(
  state => ({
    auth: state.auth,
    company: state.company,
    jobType: state.jobType
  }),
  {
    changeSelectedCompany,
    getBranches,
    getCompanies,
    getStaff,
    getThisCompany,
    patchCompany,
    pushState: push,
    removeProp,
    setProp,
    uploadFile,
    createBranch,
    patchBranch,
    resetBranch,
    updateStaff,
    addManager,
    ascSearch,
    selectAscAppraiser,
    inviteAppraiser,
    getUserPermissions,
    setUserPermissions,
    deleteStaff,
    getJobTypes,
    handleRowSelect,
    setFeeValue,
    changeSearchValue,
    sortColumn,
    getFees,
    saveProductsCompany
  })
export default class Company extends Component {
  static propTypes = {
    // Auth
    auth: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Job type reduce
    jobType: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Change selected company
    changeSelectedCompany: PropTypes.func.isRequired,
    // Company reducer
    company: PropTypes.instanceOf(Immutable.Map),
    // Get branches
    getBranches: PropTypes.func.isRequired,
    // Get company staff
    getStaff: PropTypes.func.isRequired,
    // Get companies
    getCompanies: PropTypes.func.isRequired,
    // Patch company
    patchCompany: PropTypes.func.isRequired,
    // Change state
    pushState: PropTypes.func.isRequired,
    // Remove prop
    removeProp: PropTypes.func.isRequired,
    // Set a property
    setProp: PropTypes.func.isRequired,
    // Upload file
    uploadFile: PropTypes.func.isRequired,
    // Create branch
    createBranch: PropTypes.func.isRequired,
    // Patch branch
    patchBranch: PropTypes.func.isRequired,
    // Reset branch input
    resetBranch: PropTypes.func.isRequired,
    // Update staff
    updateStaff: PropTypes.func.isRequired,
    // Add company manager
    addManager: PropTypes.func.isRequired,
    // Perform ASC search
    ascSearch: PropTypes.func.isRequired,
    // Select asc
    selectAscAppraiser: PropTypes.func.isRequired,
    // Invite
    inviteAppraiser: PropTypes.func.isRequired,
    // Get user's permissions
    getUserPermissions: PropTypes.func.isRequired,
    // Set permissions
    setUserPermissions: PropTypes.func.isRequired,
    // Delete staff
    deleteStaff: PropTypes.func.isRequired,
    // Get default job types
    getJobTypes: PropTypes.func.isRequired,
    // Select row in products
    handleRowSelect: PropTypes.func.isRequired,
    // Set fee values
    setFeeValue: PropTypes.func.isRequired,
    // Change products search
    changeSearchValue: PropTypes.func.isRequired,
    // Sort column products
    sortColumn: PropTypes.func.isRequired,
    // Get company fees
    getFees: PropTypes.func.isRequired,
    // Save product changes
    saveProductsCompany: PropTypes.func.isRequired,
  };

  constructor(props) {
    super(props);
    this.state = {
      administering: Immutable.List(),
      companiesLoaded: false,
      selectedCompany: 0,
      tabValue: 1,
      saveProductState: '',
    };
    this.achFormChange = ::this.achFormChange;
    this.patchCompany = ::this.patchCompany;
    this.patchDetails = this.patchCompany.bind(this,
      ['address1', 'address2', 'assignmentZip', 'city', 'email', 'eo', 'fax', 'firstName', 'lastName', 'name', 'phone', 'state',
       'taxId', 'type', 'zip']);
    this.patchFinancial = this.patchCompany.bind(this, ['ach', 'w9']);
    this.uploadW9 = ::this.uploadW9;
    this.checkCompanies = ::this.checkCompanies;
    this.changeSelectedCompany = ::this.changeSelectedCompany;
    this.selectCompany = ::this.selectCompany;
    this.updateStaff = ::this.updateStaff;
    this.addManager = ::this.addManager;
    this.deleteStaff = ::this.deleteStaff;
    this.saveProductsCompany = ::this.saveProductsCompany;
  }

  /**
   * Check how many companies administered on load
   */
  componentDidMount() {
    const {company, getCompanies, auth, getJobTypes, getFees} = this.props;
    const companies = company.get('companies');
    const user = auth.get('user');
    const selectedCompany = company.get('selectedCompany');
    // Check companies if we have them
    if (company.get('companiesRetrieved')) {
      this.checkCompanies(companies);
    // This probably isn't necessary, but hey, better safe than sorry
    } else if (!company.get('retrievingCompanies') && auth.get('user') && auth.getIn(['user', 'type']) !== 'manager') {
      getCompanies(auth.get('user'));
    }
    if (user && selectedCompany.get('id')) {
      getFees(selectedCompany, null, 'companies');
    }
    // Get default job types
    getJobTypes();
  }

  /**
   * If loading on this state, check how many companies administered
   */
  componentWillReceiveProps(nextProps) {
    const {company, jobType} = this.props;
    const {company: nextCompany, jobType: nextJobType} = nextProps;
    // Handle companies once loaded
    if (!company.get('companiesRetrieved') && nextCompany.get('companiesRetrieved')) {
      this.checkCompanies(nextCompany.get('companies'));
    }
    if (typeof jobType.get('saving') === 'undefined' && nextJobType.get('saving') !== 'undefined') {
      this.setState({
        saveProductState: 'disabled'
      });
    }
    if (typeof jobType.get('saveSuccess') === 'undefined' && nextJobType.get('saveSuccess') !== 'undefined') {
      this.setState({
        saveProductState: ''
      });
    }
  }

  /**
   * Check how many companies administered, show dropdown if necessary
   * @param companies List of companies
   *
   * @todo Update this with however the backend is going to show we're an admin or manager
   */
  checkCompanies(companies) {
    const auth = this.props.auth;
    const userId = auth.getIn(['user', 'id']);
    // Filter out companies this user is an admin or manager of
    companies = companies.filter(company => {
      const staff = company.get('staff');
      if (staff.get('isAdmin') || staff.get('isManager')) {
        if (staff.getIn(['user', 'id']) === userId) {
          return company;
        }
      }
      return null;
    });
    const nextState = {
      companiesLoaded: true
    };
    // Show dropdown
    if (companies.count()) {
      nextState.administering = companies;
    }
    this.setState(nextState);
    // Set this company
    setTimeout(() => this.changeSelectedCompany(null, companies.getIn([0, 'id'])), 0);
  }

  /**
   * Set ACH form value
   * @param event SyntheticEvent
   */
  achFormChange(event) {
    const {target: {value, name}} = event;
    this.props.setProp(value, 'selectedCompany', 'ach', name);
  }

  /**
   * Change the tab
   * @param tabValue
   */
  changeTabValue(tabValue) {
    // Prevent unnecessary firing
    if (isNaN(tabValue)) {
      return;
    }
    this.setState({
      tabValue
    });
  }

  /**
   * Change which company is selected
   * @param event SyntheticEvent
   * @param companyId Company ID override
   */
  changeSelectedCompany(event, companyId) {
    let value = 0;

    if (event) {
      value = event.target.value;
    }

    const selectedCompany = companyId || parseInt(value, 10);

    if (!selectedCompany) {
      return;
    }
    // Set in props
    this.selectCompany(selectedCompany);
    // Set state
    this.setState({
      selectedCompany
    });
  }

  /**
   * Retrieve the selected company
   */
  selectCompany(companyId) {
    const {changeSelectedCompany, getBranches, getStaff, getFees} = this.props;
    const {administering, selectedCompany} = this.state;
    if (!(administering && administering.count())) {
      return null;
    }
    // Set in props
    changeSelectedCompany(companyId || selectedCompany);
    // Get branches
    getBranches(companyId);
    // Get staff
    getStaff(companyId);
    // Get fees
    getFees(companyId, null, 'companies');
  }

  /**
   * Submit ACH form
   */
  patchCompany(values) {
    const {patchCompany, company} = this.props;
    const selectedCompany = company.get('selectedCompany');
    const data = selectedCompany.filter((item, key) => {
      return values.indexOf(key) !== -1;
    }).toJS();
    if (data.eo) {
      ['claimAmount', 'aggregateAmount', 'deductible'].forEach(prop => {
        data.eo[prop] = parseFloat(data.eo[prop]);
      });
    }
    delete data.id;
    delete data.isAdmin;
    delete data.isManager;
    delete data.isRfpManager;
    patchCompany(selectedCompany.get('id'), data);
  }

  /**
   * @param files
   */
  uploadW9(files) {
    this.props.uploadFile(['w9'], files[0]);
  }

  /**
   * Updates staff
   *
   * @param {Number} staffId
   * @param {Object} data
   */
  updateStaff(staffId, data) {
    const {company, updateStaff, getStaff} = this.props;
    updateStaff(company.getIn(['selectedCompany', 'id']), staffId, data).then(res => {
      if (! res.error) {
        getStaff(company.getIn(['selectedCompany', 'id']));
      }
    });
  }

  /**
   * Submit new manager
   */
  addManager() {
    const {addManager, company, getStaff} = this.props;
    const companyId = company.getIn(['selectedCompany', 'id']);
    return addManager(companyId, company.get('addManagerForm').toJS())
      .then(res => {
        if (!res.error) {
          // Get staff
          getStaff(companyId);
        }
        return res;
      });
  }

  /**
   * Removes a staff from a company
   *
   * @param {Immutable.Map} staff
   */
  deleteStaff(staff) {
    const {company, deleteStaff, getStaff} = this.props;
    const companyId = company.getIn(['selectedCompany', 'id']);

    deleteStaff(companyId, staff.get('id')).then(res => {
      if (! res.error) {
        getStaff(companyId);
      }
    });
  }

  /**
   * Save changes to fee table
   */
  saveProductsCompany() {
    const {jobType, saveProductsCompany} = this.props;
    const customerId = jobType.get('selectedCustomer');
    const feeProp = customerId === DEFAULT_CUSTOMER ? 'fees' : 'customerFees';
    // Get fee values
    const fees = jobType.get(feeProp);
    const data = {
      data: []
    };
    fees.forEach(fee => {
      data.data.push({
        jobType: fee.getIn(['jobType', 'id']),
        amount: parseFloat(fee.get('amount'))
      });
    });
    return saveProductsCompany(this.state.selectedCompany, data);
  }

  render() {
    const {
      company,
      auth,
      removeProp,
      setProp,
      uploadFile,
      createBranch,
      patchBranch,
      getBranches,
      resetBranch,
      ascSearch,
      selectAscAppraiser,
      inviteAppraiser,
      getUserPermissions,
      setUserPermissions,
      jobType,
      handleRowSelect,
      setFeeValue,
      changeSearchValue,
      sortColumn
    } = this.props;
    const {administering, companiesLoaded, selectedCompany, tabValue} = this.state;
    const thisCompany = company.get('selectedCompany') || Immutable.Map();
    // Staff
    const staffUser = thisCompany.get('staff');
    return (
      <div>
        {companiesLoaded && administering.count() > 1 &&
          <div className="row">
            <div className="col-md-12">
              <VpPlainDropdown
                options={administering}
                value={selectedCompany || 0}
                onChange={this.changeSelectedCompany}
                label="Selected Company"
                valueProp="id"
              />
            </div>
          </div>
        }
        {companiesLoaded &&
          <Tabs justified value={tabValue} className="my-tabs" inkBarStyle={{ display: 'none' }} onChange={::this.changeTabValue}>
            <Tab value={1} label="Company Details" className={'my-tab' + (tabValue === 1 ? ' my-active-tab' : '')}>
              <div className={tabValue !== 1 ? 'tab-hide-content' : ''}>
                <AppraiserCompanyCreate
                  company={thisCompany}
                  errors={company.get('errors')}
                  setProp={setProp}
                  patchCompany={this.patchDetails}
                  removeProp={removeProp}
                  uploadFile={uploadFile}
                  prefill={this.changeSelectedCompany}
                  user={auth.get('user')}
                  creatingCompany={false}
                  companyProp="selectedCompany"
                />
                <div className="row">
                  <div className="col-md-12">
                    <div className="text-center">
                      <ActionButton
                        type="submit"
                        text="Update"
                        onClick={this.patchDetails}
                      />
                    </div>
                  </div>
                </div>
              </div>
            </Tab>
            <Tab value={2} label="Users" className={'my-tab' + (tabValue === 2 ? ' my-active-tab' : '')}>
              {staffUser && company.get('branches') && company.get('staff') &&
                <CompanyUsers
                  companyId={company.getIn(['selectedCompany', 'id'])}
                  branches={company.get('branches')}
                  branchErrors={company.get('branchErrors')}
                  setProp={setProp}
                  staffUser={staffUser}
                  staff={company.get('staff')}
                  selectedBranch={company.get('selectedBranch')}
                  uploadFile={uploadFile}
                  createBranch={createBranch}
                  patchBranch={patchBranch}
                  getBranches={getBranches}
                  resetBranch={resetBranch}
                  updateStaff={this.updateStaff}
                  addManagerForm={company.get('addManagerForm')}
                  addManagerErrors={company.get('addManagerErrors')}
                  addManager={this.addManager}
                  inviteForm={company.get('inviteForm')}
                  inviteErrors={company.get('inviteErrors')}
                  ascSearchResults={company.get('ascSearchResults')}
                  ascSelected={company.get('ascSelected')}
                  ascSearch={ascSearch}
                  selectAscAppraiser={selectAscAppraiser}
                  inviteAppraiser={inviteAppraiser}
                  getUserPermissions={getUserPermissions}
                  setUserPermissions={setUserPermissions}
                  permissionsSelectedUser={company.get('permissionsSelectedUser')}
                  permissions={company.get('permissions')}
                  deleteStaff={this.deleteStaff}
                />
              }
            </Tab>
            <Tab value={4} label="Products" className={'my-tab' + (tabValue === 4 ? ' my-active-tab' : '')}>
              <div className="row">
                <div className="col-md-12">
                  <h3 className="text-center">Set Fees For Customer Forms</h3>
                </div>
                <div className="col-md-12">
                  <div className="text-center">
                    <ProgressButton onClick={this.saveProductsCompany} state={this.state.saveProductState} durationSuccess={1000}>
                      Save changes
                    </ProgressButton>
                  </div>
                </div>
              </div>
              <div className="row">
                <div className="col-md-12">
                  <JobTypesTable
                   jobType={jobType}
                   jobTypes={jobType.get('jobTypes')}
                   fees={jobType.get('fees')}
                   handleRowSelect={handleRowSelect}
                   setFeeValue={setFeeValue}
                   defaultJobTypes={Immutable.List()}
                   selectedCustomer={DEFAULT_CUSTOMER}
                   canEditFees
                   changeSearchValue={changeSearchValue}
                   sortColumn={sortColumn}
                   createFeeMap={createFeeMap}
                   isAmc={false}
                   setProp={setProp}
                   />
                </div>
              </div>
            </Tab>
            <Tab value={5} label="Financial" className={'my-tab' + (tabValue === 5 ? ' my-active-tab' : '')}>
              <div className="row">
                <div className="col-md-6">
                  <AchForm
                    form={thisCompany.get('ach') || Immutable.Map()}
                    errors={company.getIn(['errors', 'ach'])}
                    formChange={this.achFormChange}
                    changeDropdown={this.achFormChange}
                    submit={this.patchFinancial}
                    showHeader
                    validateJs={false}
                    isAmc={false}
                    noTimeout
                  />
                </div>
                <div className="col-md-6 text-center" style={style.marginTop20}>
                  <ActionButton
                    type="submit"
                    text="Download Fillable IRS W-9"
                    onClick={downloadW9Form}
                  />
                  <MyDropzone
                    refName="w9"
                    onDrop={this.uploadW9}
                    uploadedFiles={Immutable.fromJS(thisCompany.get('w9')) ? Immutable.List().push(thisCompany.get('w9')) : Immutable.List()}
                    acceptedFileTypes={['ANY']}
                    instructions={thisCompany.get('w9') ? 'Upload new w-9' : 'Upload w-9 form'}
                    error={company.get('w9Error')}
                    required
                  />
                  {!!company.get('w9Error') && <div className={inputGroupClass(true)}>
                    <p className="help-block">
                      {company.get('w9Error')}
                    </p>
                  </div>
                  }
                </div>
                <div className="col-md-12 text-center">
                  <ActionButton onClick={this.patchFinancial} text="Update" type="submit"/>&nbsp;&nbsp;
                </div>
              </div>
            </Tab>
          </Tabs>
        }
      </div>
    );
  }
}
