import React, {
  Component,
  PropTypes
} from 'react';
import { connect } from 'react-redux';
import {push, replace} from 'redux-router';
import Immutable from 'immutable';
// Orders submenu
import {
  Confirm,
  OrdersSubmenu,
  OrdersTable,
  OrdersFullscreen,
  OrdersDetailsPane,
  AcceptOrder,
  AcceptOrderWithConditions,
  DeclineOrder,
  SubmitBid,
  OrderInstructionsDialog,
  Inspection,
  NoData,
  OrderInvitation,
  NoAppraiserSelected,
  VpTextField,
  Reassign
} from 'components';
import SuperSearch from 'components/OrdersTable/SuperSearch';
import {
  getOrderQueue,
  getOrderStatuses,
  getOrder,
  selectRecord,
  closeDetailsPane,
  toggleDialog,
  acceptOrder,
  acceptWithConditions,
  setProp,
  declineOrder,
  doSubmitBid,
  doScheduleInspection,
  doInspectionComplete,
  getMessages,
  newMessage,
  getNotifications,
  getRevisions,
  searchNotifications,
  getAdditionalStatuses,
  submitAdditionalStatus,
  uploadFile,
  getAdditionalDocuments,
  getAdditionalDocumentTypes,
  sendEmail,
  addDoc,
  getAppraisalDoc,
  getDocFormats,
  clearOrders,
  fetchMarkerAddress,
  getCcOnFile,
  payTechFee,
  resetSearchParams,
  initialState,
  placeOrderOnHold,
  resumeOrder
} from 'redux/modules/orders';
import {
  getAch,
  getAppraiser,
  getCustomerJobTypes,
  getJobTypes,
  getCustomerFees,
  setProp as setPropInvitations,
  submitAch,
  uploadFile as uploadFileInvitations,
  updateAppraiser,
  selectJobType,
  createJobTypeRequest,
  setFeeValue,
  applyDefaultFees,
  saveJobTypeFees,
  getDefaultFees,
  acceptInvitation,
  removeProp,
  sortColumn,
  changeSearchValue
} from 'redux/modules/invitations';
import {
  removePrintContent,
  setPrintContent
} from 'redux/modules/auth';
import {searchCompanyAppraisers, reassign, setProp as setPropCompany} from 'redux/modules/company';

import {ORDERS_URL, ORDERS_NEW_URL} from 'redux/modules/urls';

import {Drawer} from 'material-ui';

import moment from 'moment';

// Set timers for search on stop typing
const searchTimers = {};

const styles = {
  placeOnHoldButtons: {padding: '24px', fontSize: 'large'},
  placeOnHoldError: {borderRadius: '25px'},
  superSearchWrapper: {padding: 0},
  superSearchIcon: {position: 'relative', top: '5px'},
  resetSearchButton: {margin: 0}
};

const labels = {
  continue: {submit: 'Continue'},
  close: {cancel: 'Close'}
};

// Columns to display
const columns = {
  new: ['fileNumberColumn', 'nameColumn', 'submittedByColumn', 'addressColumn', 'cityColumn', 'stateColumn', 'zipColumn',
        'statusColumn', 'dueDateColumn', 'orderedDateColumn', 'actionsColumn'],
  newAmc: ['fileNumberColumn', 'nameColumn', 'clientNameColumn', 'addressColumn', 'cityColumn', 'stateColumn', 'zipColumn',
        'statusColumn', 'dueDateColumn', 'orderedDateColumn', 'actionsColumn'],
  accepted: ['fileNumberColumn', 'nameColumn', 'submittedByColumn', 'addressColumn', 'cityColumn', 'stateColumn',
             'zipColumn', 'acceptedDateColumn', 'dueDateColumn', 'orderedDateColumn', 'actionsColumn'],
  'scheduled': ['fileNumberColumn', 'nameColumn', 'submittedByColumn', 'addressColumn', 'cityColumn',
                'stateColumn', 'zipColumn', 'inspectionScheduledAtColumn', 'estimatedCompletionDateColumn',
                'dueDateColumn', 'orderedDateColumn', 'actionsColumn'],
  'inspected': ['fileNumberColumn', 'nameColumn', 'submittedByColumn', 'addressColumn', 'cityColumn',
                'stateColumn', 'zipColumn', 'inspectionScheduledAtColumn', 'inspectionCompletedAtColumn',
                'estimatedCompletionDateColumn', 'dueDateColumn', 'orderedDateColumn', 'actionsColumn'],
  'on-hold': ['fileNumberColumn', 'nameColumn', 'submittedByColumn', 'addressColumn', 'cityColumn', 'stateColumn',
             'zipColumn', 'whenPutOnHoldColumn', 'dueDateColumn', 'orderedDateColumn',
             'actionsColumn'], // @todo Need additional filter methods here
  due: ['fileNumberColumn', 'nameColumn', 'submittedByColumn', 'addressColumn', 'cityColumn', 'stateColumn', 'zipColumn',
        'inspectionScheduledAtColumn', 'inspectionCompletedAtColumn', 'estimatedCompletionDateColumn', 'dueDateColumn',
        'orderedDateColumn', 'actionsColumn'],
  late: ['fileNumberColumn', 'nameColumn', 'submittedByColumn', 'addressColumn', 'cityColumn', 'stateColumn',
         'zipColumn', 'dueDateColumn', 'orderedDateColumn', 'actionsColumn'],
  'ready-for-review': ['fileNumberColumn', 'nameColumn', 'submittedByColumn', 'addressColumn', 'cityColumn', 'stateColumn',
             'zipColumn', 'statusColumn', 'revisionReceivedColumn', 'dueDateColumn', 'orderedDateColumn'],
  completed: ['fileNumberColumn', 'nameColumn', 'submittedByColumn', 'addressColumn', 'cityColumn', 'stateColumn',
              'zipColumn', 'completedAtColumn', 'dueDateColumn', 'actionsColumn'],
  revision: ['fileNumberColumn', 'nameColumn', 'submittedByColumn', 'addressColumn', 'cityColumn', 'stateColumn',
             'zipColumn', 'statusColumn', 'revisionReceivedColumn', 'dueDateColumn', 'orderedDateColumn'],
  open: ['fileNumberColumn', 'nameColumn', 'submittedByColumn', 'addressColumn', 'cityColumn', 'stateColumn', 'zipColumn',
        'statusColumn', 'dueDateColumn', 'orderedDateColumn', 'actionsColumn']
};

/**
 * Main orders component
 */
@connect(
  state => ({
    auth: state.auth,
    orders: state.orders,
    browser: state.browser,
    invitations: state.invitations,
    router: state.router,
    jobType: state.jobType,
    customer: state.customer,
    company: state.company
  }),
  {
    getOrderQueue,
    getOrderStatuses,
    selectRecord,
    pushState: push,
    replaceState: replace,
    closeDetailsPane,
    toggleDialog,
    acceptOrder,
    getOrder,
    acceptWithConditions,
    setProp,
    declineOrder,
    doSubmitBid,
    doScheduleInspection,
    doInspectionComplete,
    getMessages,
    newMessage,
    getNotifications,
    getRevisions,
    searchNotifications,
    getAdditionalStatuses,
    submitAdditionalStatus,
    uploadFile,
    getAdditionalDocuments,
    getAdditionalDocumentTypes,
    sendEmail,
    addDoc,
    getAppraisalDoc,
    getDocFormats,
    clearOrders,
    fetchMarkerAddress,
    getCcOnFile,
    payTechFee,
    getAch,
    getAppraiser,
    getCustomerJobTypes,
    getJobTypes,
    getCustomerFees,
    setPropInvitations,
    submitAch,
    uploadFileInvitations,
    updateAppraiser,
    selectJobType,
    setFeeValue,
    applyDefaultFees,
    saveJobTypeFees,
    getDefaultFees,
    acceptInvitation,
    setPrintContent,
    removePrintContent,
    removeProp,
    sortColumn,
    changeSearchValue,
    resetSearchParams,
    placeOrderOnHold,
    resumeOrder,
    searchCompanyAppraisers,
    reassign,
    setPropCompany
  })
export default class Orders extends Component {
  static propTypes = {
    // Orders
    orders: PropTypes.instanceOf(Immutable.Map),
    // Auth
    auth: PropTypes.instanceOf(Immutable.Map),
    // Company
    company: PropTypes.instanceOf(Immutable.Map),
    // Customer reducer
    customer: PropTypes.instanceOf(Immutable.Map),
    // Invitations
    invitations: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Order type
    params: PropTypes.object.isRequired,
    // Router
    router: PropTypes.object.isRequired,
    // Get order queue
    getOrderQueue: PropTypes.func.isRequired,
    // get the order statuses
    getOrderStatuses: PropTypes.func.isRequired,
    // Get a single order
    getOrder: PropTypes.func.isRequired,
    // Select a record
    selectRecord: PropTypes.func.isRequired,
    // Selected record
    selectedRecord: PropTypes.instanceOf(Immutable.Map),
    // Transition state
    pushState: PropTypes.func.isRequired,
    replaceState: PropTypes.func.isRequired,
    // Close the details pane
    closeDetailsPane: PropTypes.func.isRequired,
    // Show accept dialog modal
    toggleDialog: PropTypes.func.isRequired,
    // Accept the order
    acceptOrder: PropTypes.func.isRequired,
    // Accept order with conditions
    acceptWithConditions: PropTypes.func.isRequired,
    // Explicitly set a property
    setProp: PropTypes.func.isRequired,
    // Decline an order
    declineOrder: PropTypes.func.isRequired,
    // Submit bid
    doSubmitBid: PropTypes.func.isRequired,
    // Schedule an inspection
    doScheduleInspection: PropTypes.func.isRequired,
    // Complete inspection
    doInspectionComplete: PropTypes.func.isRequired,
    // Get messages
    getMessages: PropTypes.func.isRequired,
    // submit new message
    newMessage: PropTypes.func.isRequired,
    // Get notifications
    getNotifications: PropTypes.func.isRequired,
    // Get revisions
    getRevisions: PropTypes.func.isRequired,
    // Search notifications
    searchNotifications: PropTypes.func.isRequired,
    // Get additional statuses
    getAdditionalStatuses: PropTypes.func.isRequired,
    // Submit additional status
    submitAdditionalStatus: PropTypes.func.isRequired,
    // Upload file
    uploadFile: PropTypes.func.isRequired,
    // Add document
    addDoc: PropTypes.func.isRequired,
    // Get existing appraisal doc
    getAppraisalDoc: PropTypes.func.isRequired,
    // Get sub doc formats
    getDocFormats: PropTypes.func.isRequired,
    // Clear orders
    clearOrders: PropTypes.func.isRequired,
    // fetch marker address
    fetchMarkerAddress: PropTypes.func.isRequired,
    // Get credit card on file
    getCcOnFile: PropTypes.func.isRequired,
    // pay tech fee
    payTechFee: PropTypes.func.isRequired,
    // Documents
    getAdditionalDocumentTypes: PropTypes.func.isRequired,
    getAdditionalDocuments: PropTypes.func.isRequired,
    /**
     * Invitations functions
     */
    // Get ACH for invitations
    getAch: PropTypes.func.isRequired,
    // Get appraiser for invitations
    getAppraiser: PropTypes.func.isRequired,
    // Get customer job types
    getCustomerJobTypes: PropTypes.func.isRequired,
    // Default job types
    getJobTypes: PropTypes.func.isRequired,
    // Customer fees
    getCustomerFees: PropTypes.func.isRequired,
    // Set prop invitations
    setPropInvitations: PropTypes.func.isRequired,
    // Submit ACH
    submitAch: PropTypes.func.isRequired,
    // Upload file invitations
    uploadFileInvitations: PropTypes.func.isRequired,
    // Update appraiser
    updateAppraiser: PropTypes.func.isRequired,
    // Select a job type
    selectJobType: PropTypes.func.isRequired,
    // Set fee value
    setFeeValue: PropTypes.func.isRequired,
    // Apply default fees
    applyDefaultFees: PropTypes.func.isRequired,
    // Save job type fees
    saveJobTypeFees: PropTypes.func.isRequired,
    // Get default fees
    getDefaultFees: PropTypes.func.isRequired,
    // Accept invitation
    acceptInvitation: PropTypes.func.isRequired,
    // Remove prop
    removeProp: PropTypes.func.isRequired,
    // Job type reducer
    jobType: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Sort job types
    sortColumn: PropTypes.func.isRequired,
    // Search job types
    changeSearchValue: PropTypes.func.isRequired,
    // Reset search parameters
    resetSearchParams: PropTypes.func.isRequired,
    // Set print content
    setPrintContent: PropTypes.func.isRequired,
    // Place order on hold
    placeOrderOnHold: PropTypes.func.isRequired,
    // Resume order which had been placed on hold
    resumeOrder: PropTypes.func.isRequired,
    // Search company appraisers
    searchCompanyAppraisers: PropTypes.func.isRequired,
    // Reassign order
    reassign: PropTypes.func.isRequired,
    // Set prop company reducer
    setPropCompany: PropTypes.func.isRequired
  };

  static contextTypes = {
    pusher: PropTypes.object
  };

  /**
   * Init columns
   * @param props
   */
  constructor(props) {
    super(props);
    // Start on new, set initial width
    this.state = {
      columns: [],
      // Record to display in details pane
      selectedRecord: null,
      // which dialog is show for instructions
      instructionsDialog: null,
      selectedAppraiser: 0
    };

    this.submitReassign = ::this.submitReassign;
    this.pusherBind = ::this.pusherBind;
    this.documentUpdate = ::this.documentUpdate;
    this.pusherUnbind = ::this.pusherUnbind;
    this.ordersUpdated = ::this.ordersUpdated;
    this.getAppraiserForInvitations = ::this.getAppraiserForInvitations;
    this.searchPromise = ::this.searchPromise;
    this.shouldGetOrders = ::this.shouldGetOrders;
    this.toggleDialogAfterOrderAction = ::this.toggleDialogAfterOrderAction;
    this.showDetailsPane = ::this.showDetailsPane;
    this.getOrder = ::this.getOrder;
    this.getOrderComponents = ::this.getOrderComponents;
    this.reduceOrderPromises = ::this.reduceOrderPromises;
    this.getDocuments = ::this.getDocuments;
    this.getMessages = ::this.getMessages;
    this.getStatuses = ::this.getStatuses;
    this.getNotifications = ::this.getNotifications;
    this.getRevisions = ::this.getRevisions;
    this.goToDetailsView = ::this.goToDetailsView;
    this.submitAcceptOrder = ::this.submitAcceptOrder;
    this.submitAcceptWithConditions = ::this.submitAcceptWithConditions;
    this.submitDeclineOrder = ::this.submitDeclineOrder;
    this.toggleAcceptWithConditionsDialog = ::this.toggleAcceptWithConditionsDialog;
    this.toggleSubmitBid = ::this.toggleSubmitBid;
    this.submitInstructions = ::this.submitInstructions;
    this.submitSubmitBid = ::this.submitSubmitBid;
    this.submitScheduleInspection = ::this.submitScheduleInspection;
    this.submitInspectionComplete = ::this.submitInspectionComplete;
    this.tookAction = ::this.tookAction;
    this.changeSuperSearch = ::this.changeSuperSearch;
    this.changeType = ::this.changeType;
    this.resetSearchParams = ::this.resetSearchParams;
    this.openFileDetails = ::this.openFileDetails;
    this.changePlaceOnHoldReason = ::this.changePlaceOnHoldReason;
    this.placeOnHoldBody = ::this.placeOnHoldBody;
    this.resumeBody = ::this.resumeBody;
    this.placeOnHold = ::this.placeOnHold;
    this.resumeOrder = ::this.resumeOrder;
    // Toggle dialogs
    this.toggleDialog = ::this.toggleDialog;
    this.toggleDialogAccept = this.toggleDialog.bind(this, 'accept');
    this.toggleDialogAcceptConditions = this.toggleDialog.bind(this, 'accept-with-conditions');
    this.toggleDialogDecline = this.toggleDialog.bind(this, 'decline');
    this.toggleDialogInspectionComplete = this.toggleDialog.bind(this, 'inspection-complete');
    this.toggleDialogInstructionsReview = this.toggleDialog.bind(this, 'instructions-review');
    this.toggleDialogPlaceHold = this.toggleDialog.bind(this, 'place-on-hold');
    this.toggleDialogReassign = this.toggleDialog.bind(this, 'reassign');
    this.toggleDialogResume = this.toggleDialog.bind(this, 'resume');
    this.toggleDialogScheduleInspection = this.toggleDialog.bind(this, 'schedule-inspection');
    this.toggleSubmitBidDialog = this.toggleDialog.bind(this, 'submit-bid');
    this.toggleDialogInstructions = this.toggleDialog.bind(this, 'instructions');
    this.toggleInvitationDialog = this.toggleDialog.bind(this, 'invitation');
    // Pusher
    this.pusherCreateOrder = this.ordersUpdated.bind(this, 'order:create');
    this.pusherUpdateOrder = this.ordersUpdated.bind(this, 'order:update');
    this.pusherDeleteOrder = this.ordersUpdated.bind(this, 'order:delete');
    this.pusherUpdateProcessStatus = this.ordersUpdated.bind(this, 'order:update-process-status');
    this.pusherBidRequest = this.ordersUpdated.bind(this, 'order:bid-request');
    this.pusherCreateDoc = this.documentUpdate.bind(this, 'order:create-document');
    this.pusherUpdateDoc = this.documentUpdate.bind(this, 'order:update-document');
    this.pusherDeleteDoc = this.documentUpdate.bind(this, 'order:delete-document');
    this.pusherCreateAdditionalDoc = this.documentUpdate.bind(this, 'order:create-additional-document');
    this.pusherDeleteAdditionalDoc = this.documentUpdate.bind(this, 'order:delete-additional-document');
    // No-op
    this.noop = () => ({});
  }

  /**
   * Retrieve orders
   */
  componentDidMount() {
    const {setProp, getOrderStatuses, auth, params, getCcOnFile, orders, router, customer} = this.props;
    const user = auth.get('user');
    const userId = user.get('id');
    const userType = user.get('type');
    // Set initial load to false
    setProp(false, 'initialLoad');
    // Get orders on initial load if the user is validated
    if (userId) {
      // Customer view
      if (userType === 'customer') {
        const selectedAppraiser = customer.get('selectedAppraiser');
        if (selectedAppraiser) {
          getOrderStatuses(user, selectedAppraiser);
        }
      } else {
        // Retrieve the status of each queue
        // Note: this will trigger an update, which will in turn update search params
        getOrderStatuses(user);
      }
      if (userType === 'appraiser') {
        // Get credit card on file
        getCcOnFile(userId);
        // Get information necessary for invitations
        this.getAppraiserForInvitations(userId);
      }
    }
    // Set order type into props
    if (params.type) {
      setProp(params.type, 'type');
      // Make sure we have the right URL in uiState
      if (orders.getIn(['uiState', 'url']) !== router.location.pathname) {
        setProp(router.location.pathname, 'uiState', 'url');
      }
    }
    // Put page into props
    setProp(1, 'page');
  }

  /**
   * Keep reference to which orders type is selected
   * @param nextProps
   */
  componentWillReceiveProps(nextProps) {
    const {
      orders,
      getOrderQueue,
      getOrderStatuses,
      auth,
      closeDetailsPane,
      params,
      getCcOnFile,
      invitations,
      setProp,
      customer,
      resetSearchParams
    } = this.props;
    const {
      orders: nextOrders,
      auth: nextAuth,
      params: nextParams,
      invitations: nextInvitations,
      customer: nextCustomer
    } = nextProps;
    // Next user ID
    const nextUser = nextAuth.get('user');
    const nextUserId = nextUser.get('id');
    const userType = nextUser.get('type');
    // Orders type
    const type = orders.get('type');
    const nextType = nextOrders.get('type');
    // Selected appraiser for customer view
    // const selectedAppraiser =
    const nextSelectedAppraiser = nextCustomer.get('selectedAppraiser');
    // Back/forward button
    if ((params.type !== nextParams.type) || (params.page !== nextParams.page)) {
      if (nextParams.type !== nextType) {
        this.changeType(null, nextParams.type);
      }
    }
    // Toggle dialog on successful request
    const toggleDialog = this.toggleDialogAfterOrderAction.bind(this, orders, nextOrders);
    // Change order display type, or load initial display
    if (type !== nextType || (nextType && !this.state.columns.length && nextUserId)) {
      if (nextType === 'new' && userType === 'amc') {
        this.setState({
          columns: columns.newAmc
        });
      } else {
        this.setState({
          columns: columns[nextType]
        });
      }
      // Reset search params
      this.resetSearchParams();
      // Close sidebar
      closeDetailsPane(true);
    }

    // Determine whether or not to retrieve order statuses
    if (!auth.get('user') && nextUserId && !nextProps.orders.get('gettingOrderStatuses')) {
      getOrderStatuses(nextAuth.get('user'));
      if (nextAuth.getIn(['user', 'type']) === 'appraiser') {
        getCcOnFile(nextUserId);
      }
    }

    // Select customer on this state
    if (userType === 'customer') {
      if (customer.get('selectedAppraiser') !== nextSelectedAppraiser) {
        setProp(initialState.get('uiState'), 'uiState');
        getOrderStatuses(nextUser, nextSelectedAppraiser);
        if (Immutable.is(initialState.get('search'), nextOrders.get('search'))) {
          getOrderQueue(nextUser, nextType, nextOrders.get('search').toJS(), nextSelectedAppraiser);
        } else {
          resetSearchParams();
        }
      }

      if (this.state.selectedAppraiser !== this.context.pusher.selectedAppraiser) {
        this.pusherUnbind();
        this.pusherBind();
      }
    }

    // Determine whether to retrieve orders
    this.shouldGetOrders(nextProps)
      .then(search => {
        if (search) {
          setProp(Immutable.List(), 'orders');
          let search = nextOrders.get('search');
          const page = nextOrders.getIn(['uiState', 'page']);
          // make sure we're getting the correct page according to uiState
          if (search.get('page') !== page) {
            search = search.set('page', page);
          }
          if (nextType) {
            if (userType === 'customer') {
              if (nextSelectedAppraiser) {
                getOrderQueue(nextUser, nextType, search.toJS(), nextSelectedAppraiser);
              }
            } else {
              getOrderQueue(nextUser, nextType, search.toJS());
            }
          }
        }
      });

    // Get information necessary for invitations
    if (!auth.get('user') && nextUserId && nextAuth.getIn(['user', 'type']) === 'appraiser') {
      this.getAppraiserForInvitations(nextUserId);
    }
    // Display dialog after accepting invitation
    if (!invitations.get('acceptInvitationSuccess') && nextInvitations.get('acceptInvitationSuccess')) {
      this.props.toggleDialog('invitation');
      this.props.toggleDialog(orders.get('dialogAfterInvitationAccept'));
      getOrderQueue(nextUser, nextType, nextOrders.get('search').toJS());
    }
    // Accept orders successful
    toggleDialog('acceptOrderSuccess', 'accept');
    // Accept with conditions successful
    toggleDialog('acceptWithConditionsSuccess', 'accept-with-conditions');
    // Decline order successful
    toggleDialog('declineOrderSuccess', 'decline');
    // Submit bid successful
    toggleDialog('submitBidSuccess', 'submit-bid');
    // Toggle inspection complete after submit
    toggleDialog('scheduleInspectionSuccess', 'schedule-inspection');
    // Toggle inspection complete after submit
    toggleDialog('inspectionMarkedComplete', 'inspection-complete');
  }

  componentWillMount() {
    // listen for pusher events
    this.pusherBind();
  }

  /**
   * Remove search param on unmount so transition back will trigger an update
   */
  componentWillUnmount() {
    const {setProp} = this.props;
    setProp(null, 'type');

    // remove pusher subscriptions
    this.pusherUnbind();
  }

  pusherBind() {
    const {pusher} = this.context;
    const channel = pusher.channel;
    if (channel) {
      // bind to all of the events and attach the context
      channel.bind('order:create', this.pusherCreateOrder, this);
      channel.bind('order:update', this.pusherUpdateOrder, this);
      channel.bind('order:delete', this.pusherDeleteOrder, this);
      channel.bind('order:update-process-status', this.pusherUpdateProcessStatus, this);
      channel.bind('order:bid-request', this.pusherBidRequest, this);
      // Document updates
      channel.bind('order:create-document', this.pusherCreateDoc, this);
      channel.bind('order:update-document', this.pusherUpdateDoc, this);
      channel.bind('order:delete-document', this.pusherDeleteDoc, this);
      channel.bind('order:create-additional-document', this.pusherCreateAdditionalDoc, this);
      channel.bind('order:delete-additional-document', this.pusherDeleteAdditionalDoc, this);
    }
    // For changing appraisers
    if (pusher.selectedAppraiser) {
      this.setState({
        selectedAppraiser: pusher.selectedAppraiser
      });
    }
  }

  /**
   * Update document realtime
   * @param event Pusher event
   */
  documentUpdate(event) {
    const {getAdditionalDocuments, getAppraisalDoc, auth, orders, customer} = this.props;
    const user = auth.get('user');
    const selectedAppraiser = customer.get('selectedAppraiser');
    if (user.get('type') === 'customer' && !selectedAppraiser) {
      return;
    }
    const selectedRecord = orders.get('selectedRecord');
    if (event.indexOf('additional-document') !== -1) {
      getAdditionalDocuments(auth.get('user'), selectedRecord.get('id'));
    } else {
      getAppraisalDoc(auth.get('user'), selectedRecord.get('id'));
    }
  }

  /**
   * @todo
   */
  pusherUnbind() {
    const {channel} = this.context.pusher;
    if (channel) {
      // since we have a context we can remove all the events in that context
      channel.unbind(null, null, this);
    }
  }

  /**
   * Orders have been updated with pusher
   */
  ordersUpdated(channel, pushResponse) {
    const {auth, getOrderQueue, getOrder, getOrderStatuses, orders, setProp, params, customer} = this.props;
    const user = auth.get('user');
    // Customer view
    const selectedAppraiser = customer.get('selectedAppraiser');
    if (user.get('type') === 'customer' && !selectedAppraiser) {
      return;
    }

    // set this to reload messages
    setProp(undefined, 'retrieveMessageSuccess');

    // check if we are in the detail view or on the search page
    if (params.orderId) {
      getOrder(user, params.orderId, selectedAppraiser);
      this.getRevisions(selectedAppraiser || user, params.orderId);
    } else {
      // Push process status
      if (orders.get('selectedRecord') &&
          pushResponse && pushResponse.order && pushResponse.order.id && pushResponse.newProcessStatus &&
          orders.getIn(['selectedRecord', 'id']) === pushResponse.order.id
      ) {
        this.props.setProp(pushResponse.newProcessStatus, 'selectedRecord', 'processStatus');
        this.getRevisions(selectedAppraiser || user, orders.getIn(['selectedRecord', 'id']));
      }
    }
    const queueType = orders.get('type');
    if (queueType && !params.orderId) {
      // update the orders
      getOrderQueue(user, orders.get('type'), orders.get('search'), this.state.selectedAppraiser);
    }
    // update the order statuses
    getOrderStatuses(user, this.state.selectedAppraiser);
  }

  /**
   * Get appraiser for invitations
   */
  getAppraiserForInvitations(userId) {
    const {getAppraiser, getAch, getDefaultFees} = this.props;
    getAppraiser(userId);
    getAch(userId);
    getDefaultFees(userId);
  }

  /**
   * Search for an order once the user stops typing
   * @param orders This orders
   * @param nextOrders Next orders
   * @returns {Promise}
   */
  searchPromise(orders, nextOrders) {
    const lastSearchProp = nextOrders.get('lastSearchProp');
    return new Promise(resolve => {
      if (orders.getIn(['search', lastSearchProp]) !== nextOrders.getIn(['search', lastSearchProp])) {
        // Remove results if search is deleted
        if (searchTimers[lastSearchProp]) {
          clearTimeout(searchTimers[lastSearchProp]);
        }
        // Set timeout for searching on stop typing
        searchTimers[lastSearchProp] = setTimeout(() => {
          if (searchTimers[lastSearchProp]) {
            searchTimers[lastSearchProp] = null;
          }
          resolve(true);
        }, 300);
      } else {
        resolve(true);
      }
    });
  }

  /**
   * Determine whether to retrieve orders
   */
  shouldGetOrders(nextProps) {
    const {orders} = this.props;
    const {orders: nextOrders, auth, customer} = nextProps;
    if (auth.getIn(['user', 'type']) === 'customer' && !customer.get('selectedAppraiser')) {
      return new Promise(resolve => resolve(false));
    }

    // we have no search params at all
    if (nextOrders.get('search').isEmpty()) {
      return new Promise(resolve => resolve(false));
    }

    // Not authenticated, don't query
    if (!nextProps.auth.getIn(['user', 'id'])) {
      return new Promise(resolve => resolve(false));
    }
    // If performing a column search
    if (!Immutable.is(orders.get('search'), nextOrders.get('search'))) {
      return this.searchPromise(orders, nextOrders);
    }
    // If changing type
    if (orders.get('type') !== nextOrders.get('type')) {
      return new Promise(resolve => resolve(true));
    }

    // Catch edge cases (such as when loading on all, and no search params are present)
    if (!orders.get('initialLoad') && !nextOrders.get('initialLoad')) {
      return new Promise(resolve => resolve(true));
    }

    if (nextOrders.get('gettingOrders')) {
      return new Promise(resolve => resolve(false));
    }

    // default to not getting orders
    return new Promise(resolve => resolve(false));
  }

  /**
   * Toggle a dialog after making a successful request to the backend
   * @param orders Orders
   * @param nextOrders Next prop orders
   * @param prop Property to look for for success
   * @param dialogIdentifier Dialog ID
   */
  toggleDialogAfterOrderAction(orders, nextOrders, prop, dialogIdentifier) {
    if (!orders.get(prop) && nextOrders.get(prop)) {
      this.toggleDialog(dialogIdentifier);
    }
  }

  /**
   * Open details pane
   * @param selectedRecord Selected record
   */
  showDetailsPane(selectedRecord) {
    const {selectRecord} = this.props;
    selectRecord({
      selectedRecord,
      detailsPaneOpen: true
    })
    .then(() => {
      this.getOrderComponents(selectedRecord);
    });
  }

  /**
   * Retrieve order and then order components
   * @param user
   * @param orderId
   */
  getOrder(user, orderId) {
    const {getOrder, customer} = this.props;
    const selectedAppraiser = customer.get('selectedAppraiser');
    if (user.get('type') === 'customer' && !selectedAppraiser) {
      return;
    }
    getOrder(user, orderId, selectedAppraiser)
      .then(res => {
        this.getOrderComponents(Immutable.fromJS(res.result));
      });
  }

  /**
   * Retrieve all order components
   * @param selectedRecord
   */
  getOrderComponents(selectedRecord) {
    const {auth, params} = this.props;
    const user = auth.get('user');
    if (!selectedRecord) {
      return;
    }
    const orderId = selectedRecord.get('id');
    const methods = {
      messages: this.getMessages.bind(this, user, orderId),
      documents: this.getDocuments.bind(this, user, selectedRecord),
      statuses: this.getStatuses.bind(this, user, orderId),
      map: this.props.fetchMarkerAddress.bind(this, selectedRecord.get('property')),
      notifications: this.getNotifications.bind(this, user, orderId),
      revisions: this.getRevisions.bind(this, user, orderId)
    };
    // Load other items if necessary
    if (['new', 'request-for-bid'].indexOf(selectedRecord.get('processStatus')) === -1) {
      // No map if not on fullscreen
      if (!params.orderId) {
        delete methods.map;
      }
      let first;
      // Load the current tab first
      if (params.tab) {
        first = methods[params.tab];
        delete methods[params.tab];
      }
      const remainingPromises = Object.values(methods);
      new Promise(resolve => {
        if (first) {
          resolve(first());
        } else {
          resolve();
        }
      })
        .then(() => {
          this.reduceOrderPromises(remainingPromises);
        });
    } else {
      if (params.orderId) {
        this.props.fetchMarkerAddress(selectedRecord.get('property'));
      }
    }
  }

  /**
   * Call the order promises one by one
   * @param promises
   */
  reduceOrderPromises(promises) {
    const promise = promises.shift();
    if (promise) {
      promise().then(() => {
        this.reduceOrderPromises(promises);
      });
    }
  }

  /**
   * Get appraisal docs for this order
   * @param user
   * @param selectedRecord
   */
  getDocuments(user, selectedRecord) {
    const {getAdditionalDocuments, getAdditionalDocumentTypes, getAppraisalDoc, getDocFormats, customer} = this.props;
    const selectedAppraiser = customer.get('selectedAppraiser');
    // Skip for customer
    const additionalDocTypeFunc = user.get('type') === 'customer' ? () => {} :
                                  getAdditionalDocumentTypes(user, selectedRecord.get('id'), selectedAppraiser);
    return Promise.all([
      // Additional doc types
      additionalDocTypeFunc,
      // Uploaded appraisal doc
      getAppraisalDoc(user, selectedRecord.get('id'), selectedAppraiser),
      // Get sub document formats
      getDocFormats(user, selectedRecord.get('id'), selectedAppraiser),
      // get the additional documents
      getAdditionalDocuments(user, selectedRecord.get('id'), selectedAppraiser)
    ]);
  }

  /**
   * Order messages
   * @param user
   * @param orderId
   */
  getMessages(user, orderId) {
    return this.props.getMessages(user, orderId);
  }

  /**
   * Order additional statuses
   * @param user
   * @param orderId
   */
  getStatuses(user, orderId) {
    const {getAdditionalStatuses, customer} = this.props;
    return getAdditionalStatuses(user, orderId, customer.get('selectedAppraiser'));
  }

  /**
   * Order notifications
   * @param user
   * @param orderId
   */
  getNotifications(user, orderId) {
    const {getNotifications, customer} = this.props;
    return getNotifications(user, orderId, customer.get('selectedAppraiser'));
  }

  /**
   * Order revisions
   * @param user
   * @param orderId
   */
  getRevisions(user, orderId) {
    return this.props.getRevisions(user, orderId);
  }

  /**
   * Move to orders details view
   */
  goToDetailsView(selectedRecord) {
    this.props.pushState(`${ORDERS_URL}/${selectedRecord.get('id')}/`);
  }

  /**
   * Toggle function dialog
   */
  toggleDialog(dialogType, selectedRecord) {
    const {auth, getCustomerJobTypes, getJobTypes, getCustomerFees, setProp} = this.props;
    const userId = auth.getIn(['user', 'id']);
    let displayDialog = true;
    // Select this record
    if (Immutable.Iterable.isIterable(selectedRecord)) {
      const invitationId = selectedRecord.getIn(['invitation', 'id']);
      // If invitation and accept, accept with conditions
      if (invitationId && ['accept', 'accept-with-conditions'].indexOf(dialogType) !== -1) {
        displayDialog = false;
        const customerId = selectedRecord.getIn(['invitation', 'customer', 'id']);
        // Store the dialog for after invitation accepted
        setProp(dialogType, 'dialogAfterInvitationAccept');
        // Get information necessary for invitation
        Promise.all([
          getCustomerJobTypes(userId, customerId),
          getJobTypes(),
          getCustomerFees(userId, customerId)
        ])
        .then(() => {
          this.props.toggleDialog('invitation', selectedRecord);
        });
      }
      // Select record
      this.props.selectRecord({
        selectedRecord
      });
    }
    // Toggle the dialog
    if (displayDialog) {
      this.props.toggleDialog(dialogType, selectedRecord);
    }
  }

  /**
   * submit accept order
   */
  submitAcceptOrder() {
    const {acceptOrder, auth, orders, params, closeDetailsPane, getOrderStatuses} = this.props;
    acceptOrder(auth.get('user'), orders.getIn(['selectedRecord', 'id'])).then(() => {
      if (!params.orderId) {
        // call after user takes action
        this.tookAction();
      } else {
        getOrderStatuses(auth.get('user'));
        closeDetailsPane();
      }
    });
  }

  /**
   * Accept order with conditions
   */
  submitAcceptWithConditions() {
    const {acceptWithConditions, auth, orders, params, pushState, closeDetailsPane, getOrderStatuses} = this.props;
    let conditions = orders.get('acceptWithConditions');
    const request = conditions.get('request');
    // Remove unused fields
    switch (request) {
      case 'fee-increase':
        conditions = conditions.remove('dueDate');
        break;
      case 'due-date-extension':
        conditions = conditions.remove('fee');
        break;
      case 'other':
        conditions = conditions.remove('dueDate').remove('fee');
        break;
    }
    const fee = conditions.get('fee');
    // Parse fee into a way the backend can handle
    if (fee) {
      conditions = conditions.set('fee', parseFloat(parseFloat(fee).toFixed(2)));
    }
    // Format due date
    if (conditions.get('dueDate')) {
      conditions = conditions.set('dueDate', moment(conditions.get('dueDate')).format());
    }

    // Send request
    acceptWithConditions(auth.get('user'), orders.getIn(['selectedRecord', 'id']), conditions.toJS()).then(() => {
      if (!params.orderId) {
        // call after user takes action
        this.tookAction();
      } else {
        getOrderStatuses(auth.get('user'));
        closeDetailsPane();
        pushState(ORDERS_NEW_URL);
      }
    });
  }

  /**
   * Submit the decline order dialog
   */
  submitDeclineOrder() {
    const {declineOrder, auth, orders, params, pushState, closeDetailsPane, getOrderStatuses} = this.props;
    declineOrder(auth.get('user'), orders.getIn(['selectedRecord', 'id']), orders.get('decline').toJS()).then(() => {
      if (!params.orderId) {
        // call after user takes action
        this.tookAction();
      } else {
        getOrderStatuses(auth.get('user'));
        closeDetailsPane();
        pushState(ORDERS_NEW_URL);
      }
    });
  }

  /**
   * Toggle the instructions/accept with conditions dialog
   */
  toggleAcceptWithConditionsDialog(selectedRecord) {
    const {setProp, selectRecord} = this.props;
    // Instructions not yet agreed to
    setProp(false, 'agreeToInstructions');
    // Select record
    selectRecord({
      selectedRecord
    });
    // No instruction, show submit bid
    this.toggleDialogAcceptConditions(selectedRecord);
  }

  /**
   * Toggle the instructions/submit bid dialog
   */
  toggleSubmitBid(rfp, selectedRecord) {
    this.isRfp = true;
    // Non-RFP
    if (typeof rfp !== 'boolean') {
      selectedRecord = rfp;
      this.isRfp = false;
    }
    // Instructions not yet agreed to
    this.props.setProp(false, 'agreeToInstructions');
    // Select record
    this.props.selectRecord({
      selectedRecord
    });
    // If we have tech fees, instructions, or instruction documents, show instructions
    if (selectedRecord && selectedRecord.get('techFee') || selectedRecord.get('instructionDocuments').count() || selectedRecord.get('instruction')) {
      this.toggleDialogInstructions();
      this.setState({
        instructionsDialog: 'submit-bid'
      });
    } else {
      // No instruction, show submit bid
      this.toggleSubmitBidDialog();
    }
  }

  /**
   * Submit instructions dialog
   */
  submitInstructions() {
    // Instructions are agreed to
    this.props.setProp(true, 'agreeToInstructions');
    // Hide instructions
    this.toggleDialogInstructions();
    // Once they agree, show the submit bid
    this.toggleDialog(this.state.instructionsDialog);
  }

  /**
   * Submit the submit bid dialog
   */
  submitSubmitBid(rfpAppraisers) {
    const {doSubmitBid, auth, orders} = this.props;
    const submitBid = orders.get('submitBid');
    const amount = parseFloat(submitBid.get('amount')).toFixed(2);
    // Submit bid record
    const formattedSubmit = submitBid
      .set('estimatedCompletionDate', moment(submitBid.get('estimatedCompletionDate'), 'M/D/YYYY HH:mm:ss').format())
      .set('amount', parseFloat(amount))
      .toJS();
    // Attach appraisers for RFP
    if (rfpAppraisers) {
      formattedSubmit.appraisers = rfpAppraisers;
    }
    doSubmitBid(auth.get('user'), orders.getIn(['selectedRecord', 'id']), formattedSubmit).then(() => {
      // call after user takes action
      this.tookAction();
    });
  }

  /**
   * Submit the schedule inspection dialog
   */
  submitScheduleInspection(scheduledAt, estimatedCompletionDate) {
    const {doScheduleInspection, auth, orders} = this.props;
    // API request
    doScheduleInspection(auth.get('user'), orders.getIn(['selectedRecord', 'id']), {
      scheduledAt,
      estimatedCompletionDate
    }).then(() => {
      // call after user takes action
      this.tookAction();
    });
  }

  /**
   * Submit the inspection complete dialog
   */
  submitInspectionComplete(inspectionDate, estimatedCompletionDate) {
    const {doInspectionComplete, auth, orders} = this.props;
    doInspectionComplete(auth.get('user'), orders.getIn(['selectedRecord', 'id']), {
      estimatedCompletionDate: estimatedCompletionDate,
      completedAt: inspectionDate
    }).then(() => {
      // call after user takes action
      this.tookAction();
    });
  }

  /**
   * Submit reassign dialog
   * @param appraiser Appraiser being reassigned order
   * @param orderId Order ID
   */
  submitReassign(appraiser, orderId) {
    const {reassign, auth} = this.props;
    reassign(auth.get('user'), orderId, appraiser.get('id'))
    .then(res => {
      if (!res.error) {
        this.toggleDialogReassign();
      }
    });
  }

  /**
   * Retrieve updated orders after process status change, or update single order in details view
   */
  tookAction() {
    const {auth, orders, getOrderQueue, getOrderStatuses, params} = this.props;
    const user = auth.get('user');

    if (!params.orderId) {
      // update the order statuses
      getOrderStatuses(user);
      // refresh the list view
      getOrderQueue(user, orders.get('type'), orders.get('search').toJS());
    } else {
      // Retrieve this same order on details view
      this.getOrder(user, params.orderId);
    }
  }

  /**
   * Update super search property
   * @param event
   */
  changeSuperSearch(event) {
    this.props.setProp(1, 'search', 'page');
    this.props.setProp(event.target.value, 'search', 'query');
  }

  /**
   * Change orders type
   * @param url URL to transition to
   * @param type Orders type to display
   */
  changeType(url, type) {
    const {setProp, orders, pushState} = this.props;
    // nothing to do for the same type
    if (type === orders.get('type')) {
      return;
    }
    // Change URL
    if (url) {
      pushState(`${url}`);
      // Keep reference to this type in UI state
      setProp(Immutable.Map({
        url,
        page: 1,
        detailsPaneOpen: false,
        record: null
      }), 'uiState');
    }
    // Update type
    setProp(type, 'type');
    // Update process status search type
    this.resetSearchParams();
  }

  /**
   * Reset search params based on URL params
   */
  resetSearchParams() {
    this.props.resetSearchParams();
  }

  /**
   * Open the file details pane
   * @param selectedOrder Order record
   * @param column Clicked column
   */
  openFileDetails(selectedOrder, column) {
    if (column === 'actionsColumn') {
      return;
    }
    const {setProp} = this.props;
    setProp(1, 'detailsTab');
    setProp(selectedOrder, 'uiState', 'record');
    setProp(true, 'uiState', 'detailsPaneOpen');
    // Send over selected record
    this.showDetailsPane(selectedOrder);
  }

  /**
   * Change the reason for placing an order on hold
   */
  changePlaceOnHoldReason(event) {
    const {setProp} = this.props;
    setProp(event.target.value, 'placeOnHoldReason');
  }

  /**
   * Place order on hold
   */
  placeOnHoldBody() {
    const {orders} = this.props;
    return (
      <div>
        <div className="row">
          <div className="col-md-12">
            <VpTextField
              value={orders.get('placeOnHoldReason')}
              label="Reason for placing order on hold"
              name="placeOnHoldReason"
              onChange={this.changePlaceOnHoldReason}
              error={orders.getIn(['errors', 'placeOnHoldReason'])}
              enterFunction={this.placeOnHold}
            />
          </div>
        </div>
        {orders.get('placeOnHoldError') &&
          <div className="alert alert-danger" style={styles.placeOnHoldError}>
            <p>Failed to place order on hold. Please try again later.</p>
          </div>
        }
      </div>
    );
  }

  /**
   * Body for confirm to resume order which is placed on hold
   */
  resumeBody() {
    return (
      <div className="row">
        <div className="col-md-12">
          <p>Would you like to resume this order which is currently On Hold?</p>
        </div>
      </div>
    );
  }

  /**
   * Place an order on hold
   */
  placeOnHold() {
    const {orders, placeOrderOnHold, auth, closeDetailsPane} = this.props;
    const body = {
      explanation: orders.get('placeOnHoldReason')
    };
    closeDetailsPane();
    this.toggleDialogPlaceHold();
    placeOrderOnHold(auth.get('user'), orders.getIn(['selectedRecord', 'id']), body);
  }

  /**
   * Resume order which had been placed on hold
   */
  resumeOrder() {
    const {orders, resumeOrder, auth, closeDetailsPane} = this.props;
    closeDetailsPane();
    this.toggleDialogResume();
    resumeOrder(auth.get('user'), orders.getIn(['selectedRecord', 'id']));
  }

  render() {
    let {columns} = this.state;
    const {
      orders,
      closeDetailsPane,
      getAdditionalStatuses,
      params,
      invitations,
      setPropInvitations,
      submitAch,
      auth,
      uploadFileInvitations,
      updateAppraiser,
      selectJobType,
      setFeeValue,
      applyDefaultFees,
      saveJobTypeFees,
      acceptInvitation,
      removeProp,
      pushState,
      replaceState,
      jobType,
      changeSearchValue,
      sortColumn,
      setPrintContent,
      customer,
      setProp,
      searchCompanyAppraisers,
      company,
      setPropCompany
    } = this.props;
    // Selected record
    const selectedRecord = orders.get('selectedRecord') || Immutable.Map();
    // Search in progress
    const searchInProgress = !!orders.get('search').count();
    // Current type
    const type = orders.get('type');
    // User type
    const userType = auth.getIn(['user', 'type']);
    let orderCompanyId = null;
    // Manager company ID
    if (selectedRecord) {
      orderCompanyId = selectedRecord.getIn(['company', 'id']);
    }

    // No customer selected for current appraiser
    const selectedAppraiser = customer.get('selectedAppraiser');
    if (userType === 'customer' && !selectedAppraiser) {
      return <NoAppraiserSelected/>;
    }

    // Manager of companies
    const companyManagement = {
      managerOfCompanies: [],
      rfpManagerOfCompanies: []
    };

    // Manager record
    if (userType === 'manager') {
      const staff = company.getIn(['updateManager', 'staff']);
      if (staff) {
        const companyId = staff.getIn(['company', 'id']);
        if (staff.get('isRfpManager')) {
          companyManagement.rfpManagerOfCompanies.push(companyId);
        }
        companyManagement.managerOfCompanies.push(companyId);
      }
    } else {
      const companies = company.get('companies');
      companies.forEach(thisCompany => {
        const companyId = thisCompany.get('id');
        if (thisCompany.getIn(['staff', 'isManager'])) {
          companyManagement.managerOfCompanies.push(companyId);
        }
        if (thisCompany.getIn(['staff', 'isRfpManager'])) {
          companyManagement.rfpManagerOfCompanies.push(companyId);
        }
      });
    }

    // Display column on right with company of order
    if (userType === 'manager' || company.get('companies').count()) {
      columns = columns || [];
      columns = columns.slice();
      columns.push('companyColumn');
    }

    return (
      <div data-orders-container>
        {type && <div className="row">
          <div className="col-md-12">
            <OrdersSubmenu
              selectedMenu={type}
              changeType={this.changeType}
              statuses={orders.get('orderStatuses')}
              clearOrders={this.props.clearOrders}
            />
          </div>
        </div>
        }

        {/*Viewing orders table*/}
        {type &&
         <div className="row">
           {/*No orders*/}
           {!orders.get('orders').count() && !searchInProgress &&
            <NoData text="No orders available" />
           }
           {/*Orders table*/}
           {(!!orders.get('orders').count() || searchInProgress) &&
            <div>
              <div className="col-md-12">
                <OrdersTable
                  auth={auth}
                  pushState={pushState}
                  orders={orders}
                  setProp={setProp}
                  columns={columns}
                  showDetailsPane={this.showDetailsPane}
                  toggleAcceptDialog={this.toggleDialogAccept}
                  toggleAcceptWithConditionsDialog={this.toggleAcceptWithConditionsDialog}
                  toggleDeclineDialog={this.toggleDialogDecline}
                  toggleSubmitBid={this.toggleSubmitBid}
                  toggleScheduleInspection={this.toggleDialogScheduleInspection}
                  toggleReassign={this.toggleDialogReassign}
                  toggleInspectionComplete={this.toggleDialogInspectionComplete}
                  ordersWithSort={orders.get('orders')}
                  openFileDetails={this.openFileDetails}
                  changeType={this.changeType}
                  selectedAppraiser={selectedAppraiser}
                  companyManagement={companyManagement}
                  headDisplay={() => {
                    return (
                      <div>
                        <div className="col-md-8" style={styles.superSearchWrapper}>
                          <div>
                            <i className="material-icons" style={styles.superSearchIcon}>search</i>
                            <SuperSearch
                              value={orders.getIn(['search', 'query'], '')}
                              onChange={this.changeSuperSearch}
                            />
                          </div>
                        </div>
                        <div className="col-md-4">
                          <div className="pull-right">
                            <button style={styles.resetSearchButton} className="btn btn-blue" onTouchTap={this.resetSearchParams}>
                              <i className="material-icons">search</i>
                              Clear Search
                            </button>
                          </div>
                        </div>
                      </div>
                    );
                  }}
                />
              </div>
            </div>
           }
           {/*Record details pane*/}
           {selectedRecord && Immutable.Iterable.isIterable(selectedRecord) && orders.get('detailsPaneOpen') &&
            <Drawer width={700} openSecondary open className="preview-pane">
              <OrdersDetailsPane
                {...this.props}
                selectedTab={this.props.orders.get('detailsSelectedTab')}
                selectedRecord={selectedRecord}
                goToDetailsView={this.goToDetailsView}
                toggleAcceptDialog={this.toggleDialogAccept}
                toggleAcceptWithConditionsDialog={this.toggleDialogAcceptConditions}
                toggleDeclineDialog={this.toggleDialogDecline}
                toggleSubmitBid={this.toggleSubmitBid}
                toggleScheduleInspection={this.toggleDialogScheduleInspection}
                toggleReassign={this.toggleDialogReassign}
                toggleInspectionComplete={this.toggleDialogInspectionComplete}
                toggleOnHold={this.toggleDialogPlaceHold}
                toggleResume={this.toggleDialogResume}
                closeDetailsPane={closeDetailsPane}
                getAdditionalStatuses={getAdditionalStatuses}
                toggleInstructions={this.toggleDialogInstructionsReview}
                pushState={pushState}
                replaceState={replaceState}
                selectedAppraiser={selectedAppraiser}
                userType={userType}
                companyManagement={companyManagement}
              />
            </Drawer>
           }
         </div>
        }

        {/*Viewing orders details*/}
        {params.orderId &&
         <OrdersFullscreen
           {...this.props}
           invitations={invitations}
           toggleAcceptDialog={this.toggleDialogAccept}
           toggleAcceptWithConditionsDialog={this.toggleDialogAcceptConditions}
           toggleDeclineDialog={this.toggleDialogDecline}
           toggleSubmitBid={this.toggleSubmitBid}
           toggleScheduleInspection={this.toggleDialogScheduleInspection}
           toggleReassign={this.toggleDialogReassign}
           toggleInspectionComplete={this.toggleDialogInspectionComplete}
           toggleInstructions={this.toggleDialogInstructionsReview}
           toggleOnHold={this.toggleDialogPlaceHold}
           toggleResume={this.toggleDialogResume}
           getAdditionalStatuses={getAdditionalStatuses}
           pushState={pushState}
           getOrder={this.getOrder}
           selectedAppraiser={selectedAppraiser}
           userType={userType}
           companyManagement={companyManagement}
         />
        }

        {selectedRecord &&
         <div>
           {/*Accept dialog*/}
           {orders.get('show-accept-dialog') &&
            <AcceptOrder
              setProp={setProp}
              selectedRecord={orders.get('selectedRecord')}
              show={orders.get('show-accept-dialog')}
              hide={this.toggleDialogAccept}
              submit={this.submitAcceptOrder}
              acceptOrderSuccess={orders.get('acceptOrderSuccess')}
              setPrintContent={setPrintContent}
            />}
           {/*Accept with conditions dialog*/}
           {orders.get('show-accept-with-conditions-dialog') &&
            <AcceptOrderWithConditions
              setProp={setProp}
              orders={orders}
              show={orders.get('show-accept-with-conditions-dialog')}
              hide={this.toggleDialogAcceptConditions}
              acceptWithConditions={orders.get('acceptWithConditions')}
              submit={this.submitAcceptWithConditions}
            />
           }
           {/*Invitation dialog*/}
           {orders.get('show-invitation-dialog') &&
            <OrderInvitation
              invitations={invitations}
              show={orders.get('show-invitation-dialog')}
              hide={this.toggleInvitationDialog}
              selectedInvitation={selectedRecord.get('invitation') || Immutable.Map()}
              setProp={setPropInvitations}
              submitAch={submitAch}
              userId={auth.getIn(['user', 'id']) || 0}
              uploadFile={uploadFileInvitations}
              updateAppraiser={updateAppraiser}
              selectJobType={selectJobType}
              createJobTypeRequest={createJobTypeRequest}
              setFeeValue={setFeeValue}
              applyDefaultFees={applyDefaultFees}
              saveJobTypeFees={saveJobTypeFees}
              acceptInvitation={acceptInvitation}
              removeProp={removeProp}
              jobType={jobType}
              sortColumn={sortColumn}
              changeSearchValue={changeSearchValue}
            />
           }
           {/*Decline order dialog*/}
           {orders.get('show-decline-dialog') &&
            <DeclineOrder
              setProp={setProp}
              show={orders.get('show-decline-dialog')}
              hide={this.toggleDialogDecline}
              decline={orders.get('decline')}
              submit={this.submitDeclineOrder}
            />}
           {/*Instructions*/}
           {orders.get('show-instructions-dialog') &&
            <OrderInstructionsDialog
              show={orders.get('show-instructions-dialog')}
              hide={this.toggleDialogInstructions}
              selectedRecord={orders.get('selectedRecord') || Immutable.Map()}
              submit={this.submitInstructions}
              setProp={setProp}
              buttonText={{submit: 'Continue'}}
              setPrintContent={setPrintContent}
              deferSettingPrintContent
            />}
           {/*Review instructions from within an accepted order*/}
           {orders.get('show-instructions-review-dialog') &&
            <OrderInstructionsDialog
              show={orders.get('show-instructions-review-dialog')}
              hide={this.toggleDialogInstructionsReview}
              selectedRecord={orders.get('selectedRecord') || Immutable.Map()}
              submit={this.noop}
              setProp={setProp}
              buttonText={{cancel: 'Close'}}
              setPrintContent={setPrintContent}
              submitHide
            />}
           {/*Submit bid*/}
           {orders.get('show-submit-bid-dialog') &&
            <SubmitBid
              orders={orders}
              setProp={setProp}
              show={orders.get('show-submit-bid-dialog')}
              hide={this.toggleSubmitBidDialog}
              submitBid={orders.get('submitBid')}
              submit={this.submitSubmitBid}
              rfpBid={this.isRfp}
              searchCompanyAppraisers={searchCompanyAppraisers}
              selectedRecord={selectedRecord}
              companyAppraisers={company.get('companyAppraisers')}
            />}
           {/*Schedule inspection*/}
           {orders.get('show-schedule-inspection-dialog') &&
            <Inspection
              show={orders.get('show-schedule-inspection-dialog')}
              hide={this.toggleDialogScheduleInspection}
              submit={this.submitScheduleInspection}
              setProp={setProp}
              orders={orders}
              schedule
            />}
           {/*Inspection complete*/}
           {orders.get('show-inspection-complete-dialog') &&
            <Inspection
              show={orders.get('show-inspection-complete-dialog')}
              hide={this.toggleDialogInspectionComplete}
              submit={this.submitInspectionComplete}
              setProp={setProp}
              orders={orders}
            />}
           {/*Reassign*/}
           {orders.get('show-reassign-dialog') &&
             <Reassign
               show={orders.get('show-reassign-dialog')}
               hide={this.toggleDialogReassign}
               record={orders.get('selectedRecord')}
               submit={this.submitReassign}
               setProp={setPropCompany}
               reassign={company.get('reassign')}
               searchCompanyAppraisers={searchCompanyAppraisers}
               orderId={selectedRecord.get('id')}
               companyId={orderCompanyId}
               searchResults={company.get('companyAppraisers')}
               managerId={auth.getIn(['user', 'id'])}
             />
           }
           {/*Place on hold dialog*/}
           {userType === 'amc' &&
             <div>
               <Confirm
                 body={this.placeOnHoldBody()}
                 title="Place order on hold"
                 show={orders.get('show-place-on-hold-dialog')}
                 hide={this.toggleDialogPlaceHold}
                 submit={this.placeOnHold}
                 buttonText={labels.close}
                 bodyStyle={styles.placeOnHoldButtons}
               />
               <Confirm
                 body={this.resumeBody()}
                 title="Resume order"
                 show={orders.get('show-resume-dialog')}
                 hide={this.toggleDialogReassign}
                 submit={this.resumeOrder}
                 buttonText={labels.close}
                 bodyStyle={styles.placeOnHoldButtons}
               />
             </div>
           }
         </div>
        }
      </div>
    );
  }
}
