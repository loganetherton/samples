import React, {Component, PropTypes} from 'react';
import {connect} from 'react-redux';
import Immutable from 'immutable';
import {push} from 'redux-router';
import {Dialog} from 'material-ui';

import {
  ActionButton,
  AppraiserCompanyCreate,
  NoAppraiserSelected,
  FilterDatePicker,
  Pagination,
  VpTextField
} from 'components';

import QueryInput from 'components/AccountingTable/QueryInput';

import {
  setProp,
  clearSearch,
  getRecords,
  initialSearchState,
  initialPageState,
  getTotals,
} from 'redux/modules/accounting';
import {getCustomers} from 'redux/modules/jobType';
import {setProp as setPropOrders} from 'redux/modules/orders';
import {ACCOUNTING_UNPAID_URL, ACCOUNTING_URL} from 'redux/modules/urls';
import {
  setProp as setPropCompany,
  createCompany,
  checkTin,
  removeProp as removePropCompany,
  uploadFile,
  prefillWithAppraiserInfo,
  getCompanies,
  newCompany,
  setAppraiserAchDefaults,
} from 'redux/modules/company';
import {getAchInfo} from 'redux/modules/settings';
import {AccountingTable} from 'components';

import moment from 'moment';

const styles = {
  marginTop20: {marginTop: '20px'},
  ml10: { marginLeft: '10px' },
  mb0: {marginBottom: 0},
  tabCounter: {fontWeight: 'bold', fontSize: '20px'},
  uppercase: { textTransform: 'uppercase' },
  m0: { margin: 0 },
  verticalBottom: { verticalAlign: 'bottom' },
  pl0: { paddingLeft: 0 },
  status: { width: '100%', height: '28px', border: 'none', background: 'none', color: '#AAAAAA', textTransform: 'uppercase' },
  changeStatus: { width: '100%', border: 'none', fontSize: '12px', margin: 0, padding: 0, left: '-7px', position: 'relative', background: 'none' }
};

// Create prop paths
const propPathsNewCompany = {};
Immutable.fromJS(newCompany).forEach((val, prop) => {
  propPathsNewCompany[prop] = ['newCompany', prop];
  propPathsNewCompany[prop + 'Error'] = ['newCompanyErrors', prop];
});

@connect(
  state => ({
    accounting: state.accounting,
    auth: state.auth,
    browser: state.browser,
    company: state.company,
    customer: state.customer,
    jobType: state.jobType,
  }), {
    setProp,
    pushState: push,
    clearSearch,
    getRecords,
    getTotals,
    getCustomers,
    setPropOrders,
    setPropCompany,
    createCompany,
    checkTin,
    removePropCompany,
    uploadFile,
    prefillWithAppraiserInfo,
    getCompanies,
    getAchInfo,
    setAppraiserAchDefaults
  })
export default class Accounting extends Component {
  static propTypes = {
    // Accounting
    accounting: PropTypes.instanceOf(Immutable.Map),
    // Auth
    auth: PropTypes.instanceOf(Immutable.Map),
    // Browser dimensions
    browser: PropTypes.object.isRequired,
    // Appraier company
    company: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Customer reducer
    customer: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Job Type
    jobType: PropTypes.instanceOf(Immutable.Map),
    // Get records
    getRecords: PropTypes.func.isRequired,
    // URL params
    params: PropTypes.object.isRequired,
    // Change state
    pushState: PropTypes.func.isRequired,
    // Clear search
    clearSearch: PropTypes.func.isRequired,
    // Set property
    setProp: PropTypes.func.isRequired,
    // get the totals
    getTotals: PropTypes.func.isRequired,
    // Get customers' names,
    getCustomers: PropTypes.func.isRequired,
    // Set prop as orders
    setPropOrders: PropTypes.func.isRequired,
    // Set prop as company
    setPropCompany: PropTypes.func.isRequired,
    // Create company
    createCompany: PropTypes.func.isRequired,
    // TIN Checker
    checkTin: PropTypes.func.isRequired,
    // Remove company prop
    removePropCompany: PropTypes.func.isRequired,
    // Upload company file
    uploadFile: PropTypes.func.isRequired,
    // Prefills company's info with appraiser's
    prefillWithAppraiserInfo: PropTypes.func.isRequired,
    // Retrieves companies
    getCompanies: PropTypes.func.isRequired,
    // Get ACH info
    getAchInfo: PropTypes.func.isRequired,
    // Set ACH info once it's retrieved
    setAppraiserAchDefaults: PropTypes.func.isRequired
  };

  static contextTypes = {
    pusher: PropTypes.object
  };

  /**
   * Default per page to 10
   */
  constructor(props) {
    super(props);
    this.state = {
      perPage: 10,
      markPaidVisible: false,
      statusVisible: false,
      selectedStatus: null,
      selectedOrder: null,
      showCreateCompany: false,
      showCompanyCreationForm: false,
      showTaxIdForm: false
    };

    this.prefillWithAppraiserInfo = props.prefillWithAppraiserInfo.bind(
      this, props.auth.getIn(['user', 'id'])
    );
    this.createAppraiserCompany = ::this.createAppraiserCompany;
    this.toggleCreateCompany = ::this.toggleCreateCompany;
    this.toggleTaxIdForm = ::this.toggleTaxIdForm;
    this.onChangeTaxId = ::this.onChangeTaxId;
    this.submitTaxId = ::this.submitTaxId;
    this.retrieveRecords = ::this.retrieveRecords;
    this.toggleMarkPaidDialog = ::this.toggleMarkPaidDialog;
    this.markOrderPaid = ::this.markOrderPaid;
    this.changeOrderStatus = ::this.changeOrderStatus;
    this.editSearch = ::this.editSearch;
    this.changePerPage = ::this.changePerPage;
    this.toggleStatusDialog = ::this.toggleStatusDialog;
    this.selectStatus = ::this.selectStatus;
    this.changeViewUnpaid = this.changeViewType.bind(this, 'unpaid');
    this.changeViewPaid = this.changeViewType.bind(this, 'paid');
  }

  /**
   * Retrieve invitations on mount
   */
  componentDidMount() {
    const {auth, company, pushState, params, accounting, setProp, getRecords, getTotals, getCustomers, customer, getCompanies} = this.props;
    // Verify a valid accounting type
    const accountingType = accounting.get('tab');
    // Transition to unpaid if invalid params
    if (accountingType && accountingType !== 'unpaid' && accountingType !== 'paid') {
      pushState(ACCOUNTING_UNPAID_URL);
      return;
    }
    let tab = accounting.get('tab');
    // If loading on this page, set tab and page
    if (!tab) {
      tab = params.type;
      setProp(tab, 'tab');
    }
    const user = auth.get('user');
    const selectedAppraiser = customer.get('selectedAppraiser');
    // Customer type
    if (user.get('type') === 'customer') {
      if (selectedAppraiser) {
        getTotals(user, selectedAppraiser);
        getRecords(user, params.type, accounting.getIn(['page', params.type]), accounting.get('search').toJS(),
          this.state.perPage, selectedAppraiser);
      }
    } else {
      getTotals(user, null, accounting.getIn(['search', tab, 'company'], ''));
      getRecords(user, params.type, accounting.getIn(['page', params.type]), accounting.get('search').toJS());
    }
  // } else if (!company.get('retrievingCompanies') && auth.get('user')) {
    if (user.get('type') === 'appraiser') {
      if (!company.get('retrievingCompanies')) {
        getCompanies(user)
        .then(this.showCompanyCreationForm());
      } else {
        this.showCompanyCreationForm();
      }
      getCustomers(user);
    }
  }

  /**
   * Get invitations
   */
  componentWillReceiveProps(nextProps) {
    const {getRecords, customer, getTotals} = this.props;
    const {auth: nextAuth, customer: nextCustomer, params, accounting} = nextProps;
    const nextUser = nextAuth.get('user');
    // Select customer on this page
    if (nextUser.get('type') === 'customer') {
      const selectedAppraiser = customer.get('selectedAppraiser');
      const nextSelectedAppraiser = nextCustomer.get('selectedAppraiser');

      if (selectedAppraiser !== nextSelectedAppraiser) {
        if (selectedAppraiser) {
          this.pusherUnbind();
        }

        getTotals(nextUser, nextSelectedAppraiser);
        getRecords(nextUser, params.type, accounting.getIn(['page', params.type]), accounting.get('search').toJS(),
          this.state.perPage, nextSelectedAppraiser);

        this.pusherBind();
      }

      if (!nextSelectedAppraiser) {
        this.pusherUnbind();
      }
    }
  }

  componentWillMount() {
    // listen for pusher events
    this.pusherBind();
  }

  /**
   * Set tab, page, search, and search results to null
   */
  componentWillUnmount() {
    const {setProp} = this.props;
    setProp(null, 'tab');
    setProp(Immutable.fromJS(initialPageState), 'page');
    setProp(Immutable.fromJS(initialSearchState), 'search');

    // remove pusher subscriptions
    this.pusherUnbind();
  }

  pusherBind() {
    const {channel} = this.context.pusher;
    if (channel) {
      // bind to tall of the events and attach the context
      channel.bind('order:create', this.ordersUpdated.bind(this, 'order:create'), this);
      channel.bind('order:update', this.ordersUpdated.bind(this, 'order:update'), this);
      channel.bind('order:delete', this.ordersUpdated.bind(this, 'order:delete'), this);
      channel.bind('order:update-process-status', this.ordersUpdated.bind(this, 'order:update-process-status'), this);
      channel.bind('order:change-additional-status', this.ordersUpdated.bind(this, 'order:change-additional-status'), this);
      channel.bind('order:bid-request', this.ordersUpdated.bind(this, 'order:bid-request'), this);
      channel.bind('order:send-message', this.ordersUpdated.bind(this, 'order:send-message'), this);
      channel.bind('order:create-log', this.ordersUpdated.bind(this, 'order:create-log'), this);
    }
  }

  pusherUnbind() {
    const {channel} = this.context.pusher;
    if (channel) {
      // since we have a context we can remove all the events in that context
      channel.unbind(null, null, this);
    }
  }

  /**
   * Update view and search values before record retrieval
   * @param tab Current tab
   * @param searchProp Search prop to update
   * @param searchVal Search val
   */
  updateBeforeRetrieval(tab, searchProp, searchVal) {
    const {accounting} = this.props;
    // change the page back to 1 when searching
    this.changePage(tab, false, { selected: 0 });
    // Remove unused search params, update search params
    const search = accounting.get('search').map(type => type.filter(param => param));
    if (typeof searchProp !== 'undefined' && typeof searchVal !== 'undefined') {
      return search.setIn([tab, searchProp], searchVal).toJS();
    } else {
      return search.toJS();
    }
  }

  /**
   * Retrieve orders for accounting
   * @param currentTab Paid or unpaid
   * @param search Search params
   * @param page Page to retrieve
   * @param perPage Per page values
   */
  retrieveRecords(currentTab, search, page = 1, perPage = this.state.perPage) {
    const {getRecords, auth, accounting, customer} = this.props;
    const user = auth.get('user');
    const selectedAppraiser = customer.get('selectedAppraiser');
    if (!page) {
      page = currentTab === 'unpaid' ? accounting.getIn(['page', 'unpaid']) : accounting.getIn(['page', 'paid']);
    }
    // Search unpaid
    if (currentTab === 'unpaid') {
      getRecords(user, 'unpaid', page, search, perPage, selectedAppraiser);
      // Search paid
    } else {
      getRecords(user, 'paid', page, search, perPage, selectedAppraiser);
    }
  }

  ordersUpdated() {
    const {accounting, auth, getRecords, getTotals, params, customer} = this.props;
    const selectedAppraiser = customer.get('selectedAppraiser');
    // update the tabs
    getTotals(auth.get('user'), selectedAppraiser, accounting.getIn(['search', accounting.get('tab'), 'company'], ''));
    getRecords(auth.get('user'), params.type, accounting.getIn(['page', params.type]),
      accounting.get('search').toJS(), this.state.perPage, selectedAppraiser);
  }

  /**
   * Get both paid and unpaid records
   * @param user
   * @param accounting
   */
  getBothRecordSets(user, accounting) {
    const {getRecords, customer} = this.props;
    const perPage = this.state.perPage;
    const selectedAppraiser = customer.get('selectedAppraiser');
    const search = accounting.get('search') || Immutable.fromJS(initialSearchState);
    getRecords(user, 'paid', accounting.getIn(['page', 'paid']), search.toJS(), perPage, selectedAppraiser);
    getRecords(user, 'unpaid', accounting.getIn(['page', 'unpaid']), search.toJS(), perPage, selectedAppraiser);
  }

  /**
   * Edit search input
   * @param event Synthetic event
   */
  editSearch(event) {
    const {accounting, setProp} = this.props;
    const tab = accounting.get('tab');
    const {value} = event.target;
    // set the search query
    setProp(value, 'search', tab, 'query');

    // Revert to page one, update search
    const search = this.updateBeforeRetrieval(tab, 'query', value);
    this.retrieveRecords(tab, search);
  }

  /**
   * Change search date
   * @param type From or to
   * @param tab Unpaid or paid
   * @param date Incoming date value
   */
  changeSearchDate(type, tab, date) {
    const {setProp} = this.props;

    let dateVal;
    // set the date
    if (date) {
      dateVal = moment(date).format('YYYY-MM-DD');
    } else {
      dateVal = '';
    }
    setProp(dateVal, 'search', tab, type);
    // Revert to page one, update search
    const search = this.updateBeforeRetrieval(tab, type, dateVal);
    this.retrieveRecords(tab, search);
  }

  /**
   * Change date filter in "Filter by..." date dropdown
   * @param tab
   * @param event
   */
  changeDateFilter(tab, event) {
    const {setProp} = this.props;
    const value = event.target.value;
    setProp(value, 'search', tab, 'filter');
    // Revert to page one, update search
    const search = this.updateBeforeRetrieval(tab, 'filter', value);
    this.retrieveRecords(tab, search);
  }

  /**
   * Change the submitter filter
   *
   * @param tab Unpaid or paid
   * @param event Synthetic event
   */
  changeSubmitter(tab, event) {
    const {setProp} = this.props;
    const value = event.target.value;
    setProp(value, 'search', tab, 'submitter');
    // Revert to page one, update search
    const search = this.updateBeforeRetrieval(tab, 'submitter', value);
    this.retrieveRecords(tab, search);
  }

  /**
   * Change page within a view
   * @param tab Current tab
   * @param retrieveRecord Whether to retrieve a new record set
   * @param pagination Pagination object
   */
  changePage(tab, retrieveRecord, pagination) {
    const {setProp, accounting} = this.props;
    const nextPage = pagination.selected + 1;
    // Set page
    setProp(nextPage, 'page', tab);

    if (retrieveRecord) {
      // Remove unused search params, update search params
      const search = accounting.get('search').map(type => type.filter(param => param)).toJS();
      this.retrieveRecords(tab, search, nextPage, this.state.perPage);
    }
  }

  /**
   * Change between paid and unpaid
   * @param type Paid or unpaid
   */
  changeViewType(type) {
    const {pushState, setProp, accounting} = this.props;
    const nextPerPage = 10;
    const nextPage = 1;
    setProp(type, 'tab');
    setProp(nextPage, 'page', type);
    pushState(`${ACCOUNTING_URL}/${type}`);
    this.setState({
      perPage: 10
    });
    // Remove unused search params, update search params
    const search = accounting.get('search').map(type => type.filter(param => param)).toJS();
    this.retrieveRecords(type, search, nextPage, nextPerPage);
  }

  /**
   * Clear entire search query
   * @param tab Current tab
   * @param changePage Change to page one
   */
  clearSearch(tab, changePage) {
    this.props.clearSearch(tab, changePage);
  }

  /**
   * Change per page dropdown
   */
  changePerPage(event) {
    const {params, accounting, setProp} = this.props;
    const perPage = parseInt(event.target.value, 10);
    setProp(Immutable.fromJS({
      paid: 1,
      unpaid: 1
    }), 'page');
    this.setState({
      perPage
    });
    const tab = accounting.get('tab');
    // Revert to page one, update search
    const search = this.updateBeforeRetrieval(tab);
    // Get records
    this.retrieveRecords(params.type, search, 1, perPage);
  }

  /**
   * Create total rows
   * @param tab
   * @param accounting
   */
  createRecordTotals(tab, accounting) {
    let records = accounting.getIn([tab, 'data']);
    const grandTotals = accounting.getIn(['totals', tab]);
    // Create totals
    if (records) {
      const appFee = records.reduce((previous, record) => record.get('fee') + previous, 0);
      const techFee = records.reduce((previous, record) => record.get('techFee') + previous, 0);
      // Append totals
      records = records.push(Immutable.fromJS({
        total: true,
        fileNumber: 'Page Total',
        fee: appFee,
        techFee: techFee
      }));
    }

    if (grandTotals) {
      records = records.push(Immutable.fromJS({
        total: true,
        fileNumber: 'Grand Total',
        fee: grandTotals.get('fee'),
        techFee: grandTotals.get('techFee')
      }));
    }
    return records;
  }

  /**
   * Toggle mark paid dialog
   */
  toggleMarkPaidDialog() {
    this.setState({
      markPaidVisible: !this.state.markPaidVisible
    });
  }

  /**
   * Mark order as paid
   */
  markOrderPaid(orderId) {
    this.toggleMarkPaidDialog();
    // Hold reference to order
    this.setState({
      selectedOrder: orderId
    });
  }

  /**
   * Toggle status dialog
   */
  toggleStatusDialog() {
    this.setState({
      statusVisible: !this.state.statusVisible
    });
  }

  /**
   * Change order status
   */
  changeOrderStatus(orderId) {
    this.toggleStatusDialog();
    // Hold reference to order
    this.setState({
      selectedOrder: orderId,
      selectedStatus: null
    });
  }

  /**
   * Select a status
   * @param event SyntheticEvent
   */
  selectStatus(event) {
    this.setState({
      selectedStatus: event.target.value
    });
  }

  /**
   * AMC dialog buttons
   */
  dialogButtons(toggleFn, submitFn, submitText, disabled = false) {
    return ([
      <ActionButton
        type="cancel"
        text="Cancel"
        onClick={toggleFn}
      />,
      <ActionButton
        style={styles.ml10}
        type="submit"
        text={submitText}
        onClick={submitFn}
        disabled={disabled}
      />
    ]);
  }

  /**
   * Show create company dialog
   */
  toggleCreateCompany() {
    this.setState({
      showCreateCompany: !this.state.showCreateCompany
    });
  }

  /**
   * Show company create tax ID form
   */
  toggleTaxIdForm() {
    this.props.setPropCompany('', ...propPathsNewCompany.taxId);
    this.setState({
      showTaxIdForm: !this.state.showTaxIdForm
    });
  }

  /**
   * Remove empty values before submission
   * @param data Company creation data
   */
  removeEmptyVals(data) {
    return data.filter(val => {
      return !!val;
    });
  }

  /**
   * Create appraiser company
   */
  createAppraiserCompany() {
    const {auth, createCompany, company, setPropCompany} = this.props;
    // Revert errors
    setPropCompany(Immutable.fromJS(newCompany), 'errors');
    let companyData = company.get('newCompany');

    // Optional
    if (!companyData.get('address2')) {
      companyData = companyData.remove('address2');
    }
    companyData = this.removeEmptyVals(companyData);
    companyData = companyData.map((val, prop) => {
      if (prop === 'eo') {
        val = val.set('claimAmount', parseFloat(val.get('claimAmount')));
        val = val.set('deductible', parseFloat(val.get('deductible')));
        val = val.set('aggregateAmount', parseFloat(val.get('aggregateAmount')));
      }
      return val;
    });
    // Create company, hide form
    createCompany(auth.getIn(['user', 'id']), companyData.toJS())
      .then(() => {
        this.setState({
          showCompanyCreationForm: false
        });
      });
  }

  /**
   * Handle on change for tax ID
   * @param event
   */
  onChangeTaxId(event) {
    const {company, setPropCompany} = this.props;
    let val = event.target.value;
    val = val.replace(/[^0-9-]/g, '');
    // Get current for comparison
    const currentVal = company.getIn(propPathsNewCompany.taxId);
    // Properly format tax ID
    if (currentVal.length === 2 && val.length === 3) {
      val = val.slice(0, 2) + '-' + val.slice(2);
    } else if (val[val.length - 1] === '-') {
      val = val.slice(0, val.length - 1);
    } else if (val.length > 10) {
      val = currentVal;
    }
    // Make sure we update on each change
    if (val === currentVal) {
      this.forceUpdate();
    } else {
      setPropCompany(val, ...propPathsNewCompany.taxId);
      // Check on full tax ID
      if (val.length === 10) {
        this.submitTaxId(false);
      // Remove if not 10
      } else if (company.getIn(propPathsNewCompany.taxIdError)) {
        setPropCompany('', ...propPathsNewCompany.taxIdError);
      }
    }
  }

  /**
   * Submit tax ID
   */
  submitTaxId(proceed = true) {
    const {checkTin, company} = this.props;
    // Check if the tax ID is already in use
    checkTin(company.getIn(propPathsNewCompany.taxId))
      .then(res => {
        // Not found, proceed with company creation
        if (proceed && res.error.code === 404) {
          this.setState({
            showTaxIdForm: false,
            showCreateCompany: true
          });
        }
      });
  }

  /**
   * Determines whether the company creation form should be displayed to the user
   */
  showCompanyCreationForm() {
    const {auth} = this.props;
    const user = auth.get('user');
    if (user.get('isBoss')) {
      // Show form is not a company admin
      this.setState({
        showCompanyCreationForm: false
      });
    }
  }

  /**
   * Changes the selected company
   *
   * @param {string} tab Either 'paid' or 'unpaid'
   * @param event
   */
  changeCompany(tab, event) {
    const {auth, setProp, getTotals} = this.props;
    const value = event.target.value;
    setProp(value, 'search', tab, 'company');
    // Revert to page one, update search
    const search = this.updateBeforeRetrieval(tab, 'company', value);
    this.retrieveRecords(tab, search);
    getTotals(auth.get('user'), null, value);
  }

  render() {
    const {
      accounting,
      auth,
      checkTin,
      company,
      customer,
      getAchInfo,
      jobType,
      params,
      removePropCompany,
      setPropCompany,
      setPropOrders,
      uploadFile,
      setAppraiserAchDefaults
    } = this.props;
    const {
      perPage,
      markPaidVisible,
      statusVisible,
      selectedStatus,
      showCompanyCreationForm,
      showTaxIdForm,
      showCreateCompany
    } = this.state;
    const tab = accounting.get('tab') || params.page;
    const page = accounting.getIn(['page', tab]);
    // Search params
    const search = accounting.getIn(['search', tab]) || Immutable.Map();

    // Records
    const records = this.createRecordTotals.call(this, tab, accounting);

    // Total pages
    const totalPages = accounting.getIn([tab, 'meta', 'pagination', 'totalPages']);
    let displayPagination = false;
    if (totalPages) {
      displayPagination = parseInt(totalPages, 10) > 1;
    }

    // Customer list
    const customers = jobType.get('customers');
    const user = auth.get('user');
    const userType = user.get('type');

    // No customer selected for current appraiser
    if (userType === 'customer' && !customer.get('selectedAppraiser')) {
      return <NoAppraiserSelected/>;
    }

    let companies = Immutable.List();

    if (userType !== 'customer') {
      companies = company.get('companies');
    }

    return (
      <div className="container-fluid">
        <div className="row">
          <div className="col-md-12">
            <div className="row btn-tab-row" style={styles.mb0}>
              <div className="btn-tab-col col-md-1 col-sm-2 col-xs-4" role="button">
                <div className={tab === 'unpaid' ? 'btn-tab btn-tab-active' : 'btn-tab btn-tab-before-active'} onClick={this.changeViewUnpaid}>
                  <div>
                    <div style={styles.tabCounter}>{accounting.getIn(['totals', 'unpaid', 'total'])}</div>
                    <div><small style={styles.uppercase}>Unpaid</small></div>
                  </div>
                </div>
              </div>
              <div className="btn-tab-col col-md-1 col-sm-2 col-xs-4" role="button">
                <div className={tab === 'paid' ? 'btn-tab btn-tab-active' : 'btn-tab'} onClick={this.changeViewPaid}>
                  <div>
                    <div style={styles.tabCounter}>{accounting.getIn(['totals', 'paid', 'total'])}</div>
                    <div><small style={styles.uppercase}>Paid</small></div>
                  </div>
                </div>
              </div>
              {showCompanyCreationForm && false &&
                <div className="btn-tab-col col-md-10 col-sm-8 col-xs-4">
                  <div className="pull-right">
                    <button style={styles.m0} className="btn btn-blue" onClick={this.toggleTaxIdForm}>
                      <i className="material-icons">settings</i>
                      Create Company
                    </button>
                  </div>
                </div>
              }
            </div>
          </div>
        </div>
        <div className="row">
          <AccountingTable
            records={records}
            type={params.type}
            auth={auth}
            markOrderPaid={this.markOrderPaid}
            changeOrderStatus={this.changeOrderStatus}
            setPropOrders={setPropOrders}
            selectedCompany={search.get('company', '')}
            headDisplay={(columnCount) => {
              return (
                <tr key="search">
                  <td colSpan={columnCount} style={styles.verticalBottom}>
                    <div className="col-md-2" style={styles.pl0}>
                      <QueryInput value={search.get('query')} editSearch={this.editSearch} />
                      <hr className="under-field" />
                    </div>
                    <div className="col-md-1" style={styles.pl0}>
                      <FilterDatePicker
                        className="accounting-datepicker"
                        form={search || Immutable.List()}
                        changeHandler={this.changeSearchDate.bind(this, 'from', tab)}
                        name={'from'}
                        placeholderText={"From Date"}
                      />
                      <hr className="under-field" />
                    </div>
                    <div className="col-md-1" style={styles.pl0}>
                      <FilterDatePicker
                        className="accounting-datepicker"
                        form={search || Immutable.List()}
                        changeHandler={this.changeSearchDate.bind(this, 'to', tab)}
                        name={'to'}
                        placeholderText={"To Date"}
                      />
                      <hr className="under-field" />
                    </div>
                    <div className="col-md-2" style={styles.pl0}>
                      <select
                        style={styles.status}
                        name="dateField"
                        onChange={this.changeDateFilter.bind(this, tab)}
                      >
                        <option value="orderedAt">Filter by ordered date</option>
                        <option value="completedAt">Filter by completed date</option>
                        {tab === 'paid' &&
                          <option value="paidAt">Filter by paid date</option>
                        }
                      </select>
                      <hr className="under-field" />
                    </div>
                    {userType === 'appraiser' &&
                      <div>
                        <div className="col-md-2" style={styles.pl0}>
                          <select
                            style={styles.status}
                            name="submitter"
                            onChange={this.changeSubmitter.bind(this, tab)}
                            value={search.get('submitter') || ''}>
                            <option value="">Show all</option>
                            {customers && customers.map(customer => {
                              return (
                                <option value={customer.get('name')} key={customer.get('id')}>{customer.get('name')}</option>
                              );
                            })}
                          </select>
                          <hr className="under-field" />
                        </div>
                        {!!companies.count() &&
                          <div className="col-md-2">
                            <select
                              style={styles.status}
                              name="company"
                              onChange={this.changeCompany.bind(this, tab)}
                              value={search.get('company', '')}
                            >
                              <option value="">No company selected</option>
                              {companies.map(company => {
                                return (
                                  <option value={company.get('id')} key={company.get('id')}>{company.get('name')}</option>
                                );
                              })}
                            </select>
                            <hr className="under-field" />
                          </div>
                        }
                      </div>
                    }
                    <div className="col-md-2 pull-right">
                      <div className="pull-right">
                        <button style={styles.m0} className="btn btn-blue" onTouchTap={this.clearSearch.bind(this, tab, true)}>
                          <i className="material-icons">search</i>
                          Clear Search
                        </button>
                      </div>
                    </div>
                  </td>
                </tr>
              );
            }}
          />
        </div>
        <Pagination
          show={displayPagination}
          pageNum={accounting.getIn([tab, 'meta', 'pagination', 'totalPages'], 0)}
          forceSelected={Number(page) - 1}
          onClickPage={this.changePage.bind(this, tab, true)}
          containerClass="accounting-pagination-container"
          subContainerClass={"pages pagination"}
          activeClassName={"active"}
          changePerPage={this.changePerPage}
          perPageValue={perPage}
        />
        {/*AMC dialogs*/}
        <Dialog
          title="Mark Paid"
          actions={this.dialogButtons(this.toggleMarkPaidDialog, this.markOrderPaid, 'Mark order paid')}
          modal
          open={markPaidVisible}
        >
          <p>Mark the selected order as paid</p>
        </Dialog>
        <Dialog
          title="Change Status"
          actions={this.dialogButtons(this.toggleStatusDialog, this.changeOrderStatus, 'Change status')}
          modal
          open={statusVisible}
        >
          <select
            name="status"
            value={selectedStatus}
            style={styles.changeStatus}
            onChange={this.selectStatus}
          >
            {['Unpaid', 'Rebill', 'Refund'].map(type => <option key={type} value={type}>{type}</option>)}
          </select>
        </Dialog>
        {/*Search for company by tax ID form*/}
        <Dialog
          title="Add Company"
          actions={this.dialogButtons(this.toggleTaxIdForm, this.submitTaxId, 'Submit', company.getIn(propPathsNewCompany.taxIdError) || company.getIn(propPathsNewCompany.taxId).length < 10)}
          modal
          open={showTaxIdForm}
          autoScrollBodyContent
        >
          <div className="row" style={styles.marginTop20}>
            <div className="col-md-12">
              Enter your company's tax ID
              <VpTextField
                value={company.getIn(propPathsNewCompany.taxId) || ''}
                name="taxId"
                placeholder="xx-xxxxxxx"
                error={company.getIn(propPathsNewCompany.taxIdError) || ''}
                enterFunction={this.submitTaxId}
                onChange={this.onChangeTaxId}
                required
                noTimeout
              />
            </div>
          </div>
        </Dialog>
        {/*Actual create company form*/}
        <Dialog
          title="Create Company"
          actions={this.dialogButtons(this.toggleCreateCompany, this.createAppraiserCompany, 'Create company')}
          modal
          open={showCreateCompany}
          autoScrollBodyContent
        >
          <AppraiserCompanyCreate
            company={company.get('newCompany')}
            errors={company.get('errors')}
            setProp={setPropCompany}
            createCompany={this.createAppraiserCompany}
            checkTin={checkTin}
            removeProp={removePropCompany}
            uploadFile={uploadFile}
            prefill={this.prefillWithAppraiserInfo}
            getAchInfo={getAchInfo}
            user={user}
            setAppraiserAchDefaults={setAppraiserAchDefaults}
            companyProp="newCompany"
          />
        </Dialog>
      </div>
    );
  }
}
