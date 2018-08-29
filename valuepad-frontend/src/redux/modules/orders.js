const SEARCH_DETAILS = 'vp/orders/SEARCH_DETAILS';
// Retrieve all orders
const GET_ORDERS = 'vp/orders/GET_ORDERS';
const GET_ORDERS_SUCCESS = 'vp/orders/GET_ORDERS_SUCCESS';
const GET_ORDERS_FAIL = 'vp/orders/GET_ORDERS_FAIL';
// Clear orders
const CLEAR_ORDERS = 'vp/orders/CLEAR_ORDERS';
// Retrieve appraiser order statuses
const GET_ORDER_STATUSES = 'vp/orders/GET_ORDER_STATUSES';
const GET_ORDER_STATUSES_SUCCESS = 'vp/orders/GET_ORDER_STATUSES_SUCCESS';
const GET_ORDER_STATUSES_FAIL = 'vp/orders/GET_ORDER_STATUSES_FAIL';
// Retrieve a single order
const GET_ORDER = 'vp/orders/GET_ORDER';
const GET_ORDER_SUCCESS = 'vp/orders/GET_ORDER_SUCCESS';
const GET_ORDER_FAIL = 'vp/orders/GET_ORDER_FAIL';
// Close details
const CLOSE_DETAILS_PANE = 'vp/orders/CLOSE_DETAILS_PANE';
// Accept order dialog
const ACCEPT_ORDER = 'vp/orders/ACCEPT_ORDER';
const ACCEPT_ORDER_SUCCESS = 'vp/orders/ACCEPT_ORDER_SUCCESS';
const ACCEPT_ORDER_FAIL = 'vp/orders/ACCEPT_ORDER_FAIL';
// Accept with conditions dialog
const ACCEPT_ORDER_WITH_CONDITIONS = 'vp/orders/ACCEPT_ORDER_WITH_CONDITIONS';
const ACCEPT_ORDER_WITH_CONDITIONS_SUCCESS = 'vp/orders/ACCEPT_ORDER_WITH_CONDITIONS_SUCCESS';
const ACCEPT_ORDER_WITH_CONDITIONS_FAIL = 'vp/orders/ACCEPT_ORDER_WITH_CONDITIONS_FAIL';
// Decline order dialog
const DECLINE_ORDER = 'vp/orders/DECLINE_ORDER';
const DECLINE_ORDER_SUCCESS = 'vp/orders/DECLINE_ORDER_SUCCESS';
const DECLINE_ORDER_FAIL = 'vp/orders/DECLINE_ORDER_FAIL';
// Submit bid dialog
const SUBMIT_BID = 'vp/orders/SUBMIT_BID';
const SUBMIT_BID_SUCCESS = 'vp/orders/SUBMIT_BID_SUCCESS';
const SUBMIT_BID_FAIL = 'vp/orders/SUBMIT_BID_FAIL';
// Schedule inspection dialog
const SCHEDULE_INSPECTION = 'vp/orders/SCHEDULE_INSPECTION';
const SCHEDULE_INSPECTION_SUCCESS = 'vp/orders/SCHEDULE_INSPECTION_SUCCESS';
const SCHEDULE_INSPECTION_FAIL = 'vp/orders/SCHEDULE_INSPECTION_FAIL';
// Inspection complete dialog
const INSPECTION_COMPLETE = 'vp/orders/INSPECTION_COMPLETE';
const INSPECTION_COMPLETE_SUCCESS = 'vp/orders/INSPECTION_COMPLETE_SUCCESS';
const INSPECTION_COMPLETE_FAIL = 'vp/orders/INSPECTION_COMPLETE_FAIL';
// Set a property
const SET_PROP = 'vp/orders/SET_PROP';
// Email office
const GET_MESSAGES = 'vp/orders/GET_MESSAGES';
const GET_MESSAGES_SUCCESS = 'vp/orders/GET_MESSAGES_SUCCESS';
const GET_MESSAGES_FAIL = 'vp/orders/GET_MESSAGES_FAIL';
const NEW_MESSAGE = 'vp/orders/NEW_MESSAGE';
const NEW_MESSAGE_SUCCESS = 'vp/orders/NEW_MESSAGE_SUCCESS';
const NEW_MESSAGE_FAIL = 'vp/orders/NEW_MESSAGE_FAIL';
// Notifications
const GET_NOTIFICATIONS = 'vp/orders/GET_NOTIFICATIONS';
const GET_NOTIFICATIONS_SUCCESS = 'vp/orders/GET_NOTIFICATIONS_SUCCESS';
const GET_NOTIFICATIONS_FAIL = 'vp/orders/GET_NOTIFICATIONS_FAIL';
const SEARCH_NOTIFICATIONS = 'vp/orders/SEARCH_NOTIFICATIONS';
const SEARCH_NOTIFICATIONS_SUCCESS = 'vp/orders/SEARCH_NOTIFICATIONS_SUCCESS';
const SEARCH_NOTIFICATIONS_FAIL = 'vp/orders/SEARCH_NOTIFICATIONS_FAIL';
// Revisions
const GET_REVISIONS = 'vp/order/GET_REVISIONS';
const GET_REVISIONS_SUCCESS = 'vp/order/GET_REVISIONS_SUCCESS';
const GET_REVISIONS_FAIL = 'vp/order/GET_REVISIONS_FAIL';
// Additional status
const GET_ADDITIONAL_STATUSES = 'vp/orders/GET_ADDITIONAL_STATUSES';
const GET_ADDITIONAL_STATUSES_SUCCESS = 'vp/orders/GET_ADDITIONAL_STATUSES_SUCCESS';
const GET_ADDITIONAL_STATUSES_FAIL = 'vp/orders/GET_ADDITIONAL_STATUSES_FAIL';
const SUBMIT_ADDITIONAL_STATUS = 'vp/orders/SUBMIT_ADDITIONAL_STATUS';
const SUBMIT_ADDITIONAL_STATUS_SUCCESS = 'vp/orders/SUBMIT_ADDITIONAL_STATUS_SUCCESS';
const SUBMIT_ADDITIONAL_STATUS_FAIL = 'vp/orders/SUBMIT_ADDITIONAL_STATUS_FAIL';
// Additional documents
const GET_ADDITIONAL_DOCUMENTS = 'vp/orders/GET_ADDITIONAL_DOCUMENTS';
const GET_ADDITIONAL_DOCUMENTS_SUCCESS = 'vp/orders/GET_ADDITIONAL_DOCUMENTS_SUCCESS';
const GET_ADDITIONAL_DOCUMENTS_FAIL = 'vp/orders/GET_ADDITIONAL_DOCUMENTS_FAIL';
// Additional document types
const GET_ADDITIONAL_DOCUMENT_TYPES = 'vp/orders/GET_ADDITIONAL_DOCUMENT_TYPES';
const GET_ADDITIONAL_DOCUMENT_TYPES_SUCCESS = 'vp/orders/GET_ADDITIONAL_DOCUMENT_TYPES_SUCCESS';
const GET_ADDITIONAL_DOCUMENT_TYPES_FAIL = 'vp/orders/GET_ADDITIONAL_DOCUMENT_TYPES_FAIL';
// File uploads
const FILE_UPLOAD = 'vp/orders/FILE_UPLOAD';
const FILE_UPLOAD_SUCCESS = 'vp/orders/FILE_UPLOAD_SUCCESS';
const FILE_UPLOAD_FAIL = 'vp/orders/FILE_UPLOAD_FAIL';
// Send document via email
const EMAIL_DOC = 'vp/orders/EMAIL_DOC';
const EMAIL_DOC_SUCCESS = 'vp/orders/EMAIL_DOC_SUCCESS';
const EMAIL_DOC_FAIL = 'vp/orders/EMAIL_DOC_FAIL';
// Add document to order
const ADD_DOC = 'vp/orders/ADD_DOC';
const ADD_DOC_SUCCESS = 'vp/orders/ADD_DOC_SUCCESS';
const ADD_DOC_FAIL = 'vp/orders/ADD_DOC_FAIL';
// Get appraisal doc
const GET_APPRAISAL_DOC = 'vp/orders/GET_APPRAISAL_DOC';
const GET_APPRAISAL_DOC_SUCCESS = 'vp/orders/GET_APPRAISAL_DOC_SUCCESS';
const GET_APPRAISAL_DOC_FAIL = 'vp/orders/GET_APPRAISAL_DOC_FAIL';
// Get sub doc formats
const GET_DOC_FORMATS = 'vp/orders/GET_DOC_FORMATS';
const GET_DOC_FORMATS_SUCCESS = 'vp/orders/GET_DOC_FORMATS_SUCCESS';
const GET_DOC_FORMATS_FAIL = 'vp/orders/GET_DOC_FORMATS_FAIL';
// Sub doc identifier
const SUB_DOC = '__SUBDOC__';

const SELECT_RECORD = 'vp/orders/SELECT_RECORD';
const SELECT_RECORD_SUCCESS = 'vp/orders/SELECT_RECORD_SUCCESS';
const SELECT_RECORD_FAILURE = 'vp/orders/SELECT_RECORD_FAILURE';
const TOGGLE_DIALOG = 'vp/orders/TOGGLE_DIALOG';
const FETCH_MARKER = 'vp/orders/FETCH_MARKER';
const FETCH_MARKER_SUCCESS = 'vp/orders/FETCH_MARKER_SUCCESS';
const FETCH_MARKER_FAIL = 'vp/orders/FETCH_MARKER_FAIL';
// Get appraiser credit card info
const GET_CC_INFO = 'vp/settings/GET_CC_INFO';
const GET_CC_INFO_SUCCESS = 'vp/settings/GET_CC_INFO_SUCCESS';
const GET_CC_INFO_FAIL = 'vp/settings/GET_CC_INFO_FAIL';
// Pay tech fee
const PAY_TECH_FEE = 'vp/settings/PAY_TECH_FEE';
const PAY_TECH_FEE_SUCCESS = 'vp/settings/PAY_TECH_FEE_SUCCESS';
const PAY_TECH_FEE_FAIL = 'vp/settings/PAY_TECH_FEE_FAIL';
// Place order on hold
const PLACE_ON_HOLD = 'vp/settings/PLACE_ON_HOLD';
const PLACE_ON_HOLD_SUCCESS = 'vp/settings/PLACE_ON_HOLD_SUCCESS';
const PLACE_ON_HOLD_FAIL = 'vp/settings/PLACE_ON_HOLD_FAIL';
// Resume order which had been placed on hold
const RESUME = 'vp/settings/RESUME';
const RESUME_SUCCESS = 'vp/settings/RESUME_SUCCESS';
const RESUME_FAIL = 'vp/settings/RESUME_FAIL';

// Regex for dollar value with change optional
const DOLLAR_VALUE_REGEX_NO_CHANGE = /^\d+(\.\d{2})?$/;

import Immutable from 'immutable';
import moment from 'moment';
/**
 * Validation
 */
import {
  pattern as valPattern,
  presence as valPresence,
  frontendErrors,
  email as valEmail,
} from '../../helpers/validation';

// Orders URL
import {ORDERS_NEW_URL} from 'redux/modules/urls';

// Upload documents
import {fileUpload, createQueryParams} from 'helpers/genericFunctions';

// Fields that we might need to validate
const orderFields = [
  // Nested
  'acceptWithConditions:fee', 'submitBid:amount', 'appraisalDocs:appraisalDocEmailRecipient'
];

// Validation constraints
const constraints = {};

/**
 * Validate fields, create initial state
 */
orderFields.forEach(field => {
  constraints[field] = {};
  // Can be blank
  valPresence(constraints, field, 'This field is required');
  // Value fee
  if (['acceptWithConditions:fee', 'submitBid:amount'].indexOf(field) !== -1) {
    valPattern(constraints, field, DOLLAR_VALUE_REGEX_NO_CHANGE);
  }
  // Appraisal doc recipient email
  if (field === 'appraisalDocs:appraisalDocEmailRecipient') {
    valEmail(constraints, field);
  }
});

// Complete list of items that can be included on orders requests
const allOrdersIncludes = 'referenceNumber,clientName,clientAddress1,clientAddress2,clientCity,clientState,clientZip,clientDisplayedOnReportName,clientDisplayedOnReportAddress1,clientDisplayedOnReportAddress2,clientDisplayedOnReportCity,clientDisplayedOnReportState,clientDisplayedOnReportZip,amcLicenseNumber,amcLicenseExpiresAt,jobType,additionalJobTypes,isPaid,fee,techFee,purchasePrice,fhaNumber,loanAmount,loanNumber,loanType,processStatus,approachesToBeIncluded,dueDate,orderedAt,assignedAt,acceptedAt,putOnHoldAt,revisionReceivedAt,inspectionScheduledAt,inspectionCompletedAt,estimatedCompletionDate,completedAt,paidAt,property,instructionDocuments,instruction,additionalDocuments,bid,isTechFeePaid,invitation,Invitation,customer.settings,customer.phone,isRush,intendedUse,fdic,assignee,company';

// Initial state for appraisal docs for an order
const appraisalDocsInitialState = {
  appraisalDoc: [],
  appraisalSubDocs: {},
  additionalDoc: [],
  // Formats
  formats: {
    primary: [],
    extra: []
  },
  additionalDocTypes: [],
  // Selected document type
  selectedDocType: 0
};

export const initialState = Immutable.fromJS({
  // Init empty orders
  orders: [],
  // Meta
  meta: {},
  // Init empty order statuses
  orderStatuses: Immutable.Map(),
  // Search
  search: {
    'search[fileNumber]': '',
    'search[borrowerName]': '',
    'search[customer][name]': '',
    'search[property][address]': '',
    'search[property][city]': '',
    'filter[property][zip]': '',
    'filter[client][name]': '',
    'filter[acceptedAt]': '',
    'filter[inspectionScheduledAt]': '',
    'filter[inspectionCompletedAt]': '',
    'filter[estimatedCompletionDate]': '',
    'filter[putOnHoldAt]': '',
    'filter[revisionReceivedAt]': '',
    'filter[completedAt]': '',
    'filter[orderedAt]': '',
    page: 1,
    orderBy: 'orderedAt:desc'
  },
  // No selected record at first
  selectedRecord: null,
  /**
   * Dialogs
   */
  // Show accept dialog
  'show-accept-dialog': false,
  // Accept with conditions dialog
  'show-accept-with-conditions-dialog': false,
  // Decline order dialog
  'show-decline-dialog': false,
  // Decline submit bid dialog
  'show-submit-bid-dialog': false,
  // Instructions dialog
  'show-instructions-dialog': false,
  // Schedule inspection dialog
  'show-schedule-inspection-dialog': false,
  // Show inspection complete
  'show-inspection-complete-dialog': false,
  // Place on hold dialog
  'show-place-on-hold-dialog': false,
  // Resume order after being placed on hold
  'show-resume-dialog': false,
  // Place on hold reason
  placeOnHoldReason: '',
  // Error placing on hold
  placeOnHoldError: false,
  // Accept with conditions property
  acceptWithConditions: {
    request: ''
  },
  // Decline order
  decline: {
    reason: 'too-busy',
    message: ''
  },
  // Instructions
  instructions: {},
  // Submit bid
  submitBid: {
    estimatedCompletionDate: '',
    comments: '',
    amount: ''
  },
  // Schedule inspection dialog
  scheduleInspection: {
    scheduledAt: '',
    estimatedCompletionDate: ''
  },
  // Inspection complete dialog
  inspectionComplete: {
    completedAt: '',
    estimatedCompletionDate: ''
  },
  // Appraisal docs
  appraisalDocs: appraisalDocsInitialState,
  // Email office
  emailOffice: {
    // New message
    message: '',
    // Messages
    messages: []
  },
  revisions: [],
  // Notification log
  notificationLog: {
    // Search notifications
    search: '',
    // Notifications
    notifications: []
  },
  // Additional status
  additionalStatus: {
    // List of additional statuses
    statuses: [],
    // Selected status
    selectedStatus: 0,
    // Message
    message: ''
  },
  // State of the UI, so we can handle back buttons, etc
  uiState: {
    record: null,
    detailsPaneOpen: false,
    // Queue being shown
    url: ORDERS_NEW_URL,
    // Page
    page: 1
  },
  // Errors
  errors: {},
  detailsSelectedTab: 'documents'
});

/**
 * Update an order's status
 * @param state Current state
 * @param orderId ID of order being updated
 * @param newStatus Status to be updated to
 * @returns {*}
 */
function updateOrderStatus(state, orderId, newStatus) {
  return state.get('orders').map(order => {
    if (order.get('id') === orderId) {
      order = order.set('processStatus', newStatus);
    }
    return order;
  });
}

/**
 * Remove an order from the table
 * @param state Current state
 * @param orderId ID of order being removed
 * @returns {*}
 */
function removeOrder(state, orderId) {
  return state.get('orders').filter(order => {
    return order.get('id') !== orderId;
  });
}

/**
 * Add an appraisal docs to state
 * @param state Incoming state
 * @param action Action
 * @param type Type of appraisal doc
 * @param docType Doc type for additional documents
 */
function addAppraisalDoc(state, action, type, docType) {
  // Get existing appraisal docs
  let appraisalDocs = state.getIn(['appraisalDocs', ...type]);
  let newDoc;
  // New document
  if (!docType) {
    newDoc = Immutable.fromJS(Object.assign(action.result, {preview: action.document.preview}));
    // Sub document
  } else if (docType === SUB_DOC) {
    newDoc = Immutable.fromJS(action.result);
    // New document with type specified (specifying additional doc)
  } else {
    newDoc = Immutable.fromJS(Object.assign(action.result, {preview: action.document.preview, docType}));
  }
  if (Immutable.List.isList(appraisalDocs)) {
    // Push onto list and store
    appraisalDocs = appraisalDocs.push(newDoc);
  } else {
    appraisalDocs = newDoc;
  }
  // Wrap in array so we can always select down properly
  type = Array.isArray(type) ? type : [type];
  return state
    .setIn(['appraisalDocs', ...type], appraisalDocs)
    .setIn(['appraisalDocs', 'mostRecentType'], type[type.length - 1]);
}

export default function reducer(state = initialState, action = {}) {
  switch (action.type) {
    /**
     * Search
     */
    case SEARCH_DETAILS:
      return state;
    /**
     * Retrieve orders
     */
    case GET_ORDERS:
      return state
        .set('gettingOrders', true)
        .remove('getOrdersSuccess')
        .set('initialLoad', true);
    case GET_ORDERS_SUCCESS:
      return state
        .set('orders', Immutable.fromJS(action.result.data))
        .set('meta', Immutable.fromJS(action.result.meta))
        .remove('gettingOrders')
        .set('getOrdersSuccess', true);
    case GET_ORDERS_FAIL:
      return state
        .remove('gettingOrders')
        .set('getOrdersSuccess', false);
    /**
     * Clears orders
     */
    case CLEAR_ORDERS:
      return state
        .set('orders', Immutable.fromJS([]))
        .set('meta', Immutable.fromJS([]));
    /**
     * Retrieve an appraisers order statuses
     */
    case GET_ORDER_STATUSES:
      return state
        .set('gettingOrderStatuses', true)
        .remove('getOrderStatusesSuccess');
    case GET_ORDER_STATUSES_SUCCESS:
      return state
        .set('orderStatuses', Immutable.fromJS(action.result))
        .remove('gettingOrderStatuses')
        .set('getOrderStatusesSuccess', true);
    case GET_ORDER_STATUSES_FAIL:
      return state
        .remove('gettingOrderStatuses')
        .set('getOrderStatusesSuccess', false);
    /**
     * Retrieve a single order
     */
    case GET_ORDER:
      return state
        .set('gettingOrder', true)
        .remove('getOrderSuccess');
    case GET_ORDER_SUCCESS:
      return state
        .set('selectedRecord', Immutable.fromJS(action.result))
        .remove('gettingOrder')
        .set('getOrderSuccess', true);
    case GET_ORDER_FAIL:
      return state
        .remove('gettingOrder')
        .set('getOrderSuccess', false);
    /**
     * Select a record
     */
    case SELECT_RECORD_SUCCESS:
      let newState = state;
      // Set record
      newState = newState
        .set('selectedRecord', action.selectedRecord)
        .set('selectedRecordTime', Date.now());
      // Set state of details pane if one is specified
      if (typeof action.detailsPaneOpen === 'boolean') {
        newState = newState.set('detailsPaneOpen', action.detailsPaneOpen);
      }
      return newState;
    /**
     * Close the details pane
     */
    case CLOSE_DETAILS_PANE:
      let closedState = state
        .set('detailsSelectedTab', 'documents')
        .set('detailsPaneOpen', false);
      if (!action.persistUiState) {
        closedState = closedState.setIn(['uiState', 'detailsPaneOpen'], false);
      }
      return closedState;
    /**
     * Toggle accept order dialog
     */
    case TOGGLE_DIALOG:
      let toggleState = state
        .set(`show-${action.dialogType}-dialog`, !state.get(`show-${action.dialogType}-dialog`));
      // Set order
      if (action.order && Immutable.Iterable.isIterable(action.order)) {
        toggleState = toggleState.set('selectedRecord', action.order);
      }
      return toggleState;
    /**
     * Accept order
     */
    case ACCEPT_ORDER:
      return state
        .set('acceptingOrder', true)
        .remove('processStatusChange')
        .remove('acceptOrderSuccess');
    case ACCEPT_ORDER_SUCCESS:
      return state
        .set('detailsPaneOpen', false)
        .set('processStatusChange', true)
        .remove('acceptingOrder')
        .set('acceptOrderSuccess', true)
        .set('orders', updateOrderStatus(state, action.orderId, 'accepted'));
    case ACCEPT_ORDER_FAIL:
      return state
        .remove('acceptingOrder')
        .set('acceptOrderSuccess', false);
    /**
     * Accept order with conditions
     */
    case ACCEPT_ORDER_WITH_CONDITIONS:
      return state
        .set('acceptingWithConditions', true)
        .remove('processStatusChange')
        .remove('acceptWithConditionsSuccess');
    case ACCEPT_ORDER_WITH_CONDITIONS_SUCCESS:
      // Update order status
      return state
        .set('detailsPaneOpen', false)
        .set('processStatusChange', true)
        .remove('acceptingWithConditions')
        .set('acceptWithConditionsSuccess', true)
        .set('acceptWithConditions', Immutable.Map())
        // Orders should be removed after accepted with conditions
        // @link: https://www.pivotaltracker.com/story/show/112469569
        .set('orders', removeOrder(state, action.orderId));
    case ACCEPT_ORDER_WITH_CONDITIONS_FAIL:
      return state
        .remove('acceptingWithConditions')
        .set('acceptWithConditionsSuccess', false)
        .setIn(['errors', 'acceptWithConditions'], Immutable.fromJS(action.error.errors));
    /**
     * Decline order
     */
    case DECLINE_ORDER:
      return state
        .set('decliningOrder', true)
        .remove('processStatusChange')
        .remove('declineOrderSuccess');
    case DECLINE_ORDER_SUCCESS:
      return state
        .set('detailsPaneOpen', false)
        .set('processStatusChange', true)
        .remove('decliningOrder')
        .set('decline', Immutable.Map())
        .set('declineOrderSuccess', true)
        .set('orders', removeOrder(state, action.orderId));
    case DECLINE_ORDER_FAIL:
      return state
        .remove('decliningOrder')
        .set('declineOrderSuccess', false);
    /**
     * Submit bid
     */
    case SUBMIT_BID:
      return state
        .set('submittingBid', true)
        .remove('submitBidSuccess');
    case SUBMIT_BID_SUCCESS:
      return state
        .set('detailsPaneOpen', false)
        .remove('submittingBid')
        .set('submitBidSuccess', true)
        .set('submitBid', Immutable.Map())
        .set('orders', updateOrderStatus(state, action.orderId, 'request-for-bid'));
    case SUBMIT_BID_FAIL:
      return state
        .remove('submittingBid')
        .set('submitBidSuccess', false)
        .setIn(['submitBid', 'error'], Immutable.fromJS(action.error.message));
    /**
     * Schedule inspection
     */
    case SCHEDULE_INSPECTION:
      return state
        .remove('scheduleInspectionErrors')
        .set('schedulingInspection', true)
        .remove('scheduleInspectionSuccess');
    case SCHEDULE_INSPECTION_SUCCESS:
      return state
        .set('detailsPaneOpen', false)
        .remove('schedulingInspection')
        .set('scheduleInspection', Immutable.Map())
        .set('scheduleInspectionSuccess', true)
        .set('orders', updateOrderStatus(state, action.orderId, 'inspection-scheduled'));
    case SCHEDULE_INSPECTION_FAIL:
      return state
        .remove('schedulingInspection')
        .set('scheduleInspectionSuccess', false);
    /**
     * Schedule inspection
     */
    case INSPECTION_COMPLETE:
      return state
        .remove('inspectionCompleteErrors')
        .set('markingInspectionComplete', true)
        .remove('inspectionMarkedComplete');
    case INSPECTION_COMPLETE_SUCCESS:
      return state
        .set('detailsPaneOpen', false)
        .remove('markingInspectionComplete')
        .set('inspectionComplete', Immutable.Map())
        .set('inspectionMarkedComplete', true)
        .set('orders', updateOrderStatus(state, action.orderId, 'inspection-completed'));
    case INSPECTION_COMPLETE_FAIL:
      return state
        .remove('markingInspectionComplete')
        .set('inspectionMarkedComplete', false);
    /**
     * Manually set a property
     */
    case SET_PROP:
      let stateWithProp = state.setIn(action.name, action.value);
      // Store last search prop
      if (action.name[0] === 'search') {
        stateWithProp = stateWithProp.set('lastSearchProp', action.name[1]);
      }
      if (action.validate) {
        // @todo This should be immutable
        return frontendErrors(stateWithProp, action, constraints);
      }
      return stateWithProp;
    /**
     * Get messages
     */
    case GET_MESSAGES:
      return state
        .set('retrievingMessages', true)
        .remove('retrieveMessageSuccess');
    case GET_MESSAGES_SUCCESS:
      return state
        .setIn(['emailOffice', 'messages'], Immutable.fromJS(action.result.data))
        .remove('retrievingMessages')
        .set('retrieveMessageSuccess', action.orderId);
    case GET_MESSAGES_FAIL:
      return state
        .remove('retrievingMessages')
        .set('retrieveMessageSuccess', action.orderId);
    /**
     * New message
     */
    case NEW_MESSAGE:
      return state
        .set('submittingNewMessage', true)
        .remove('newMessageSuccess');
    case NEW_MESSAGE_SUCCESS:
      return state
        .remove('submittingNewMessage')
        .set('newMessageSuccess', true)
        .setIn(['emailOffice', 'message'], '');
    case NEW_MESSAGE_FAIL:
      return state
        .remove('submittingNewMessage')
        .set('newMessageSuccess', false);
    /**
     * Get notifications
     */
    case GET_NOTIFICATIONS:
      return state
        .set('gettingNotifications', true)
        .remove('getNotificationsSuccess');
    case GET_NOTIFICATIONS_SUCCESS:
      return state
        .setIn(['notificationLog', 'notifications'], Immutable.fromJS(action.result.data))
        .remove('gettingNotifications')
        .set('getNotificationsSuccess', true);
    case GET_NOTIFICATIONS_FAIL:
      return state
        .remove('gettingNotifications')
        .set('getNotificationsSuccess', false);
    /**
     * Search notifications
     */
    case SEARCH_NOTIFICATIONS:
      return state
        .set('searchingNotifications', true)
        .remove('searchNotificationsSuccess');
    case SEARCH_NOTIFICATIONS_SUCCESS:
      return state
        .remove('searchingNotifications')
        .set('searchNotificationsSuccess', true);
    case SEARCH_NOTIFICATIONS_FAIL:
      return state
        .remove('searchingNotifications')
        .set('searchNotificationsSuccess', false);
    /**
     * Revisions
     */
    case GET_REVISIONS:
      return state
        .remove('gettingRevisionsSuccess')
        .set('gettingRevisions');
    case GET_REVISIONS_SUCCESS:
      const data = Immutable.fromJS(action.result);
      const revisions = data.getIn([0, 'body', 'data']);
      const reconsiderations = data.getIn([1, 'body', 'data']);
      let finalRevisions = [];

      // add the reconsiderations
      reconsiderations.forEach((reconsideration) => {
        finalRevisions.push({
          type: 'reconsideration',
          date: reconsideration.get('createdAt'),
          data: reconsideration.toJS(),
        });
      });

      // add the revisions
      revisions.forEach((revision) => {
        finalRevisions.push({
          type: 'revision',
          date: revision.get('createdAt'),
          data: revision.toJS(),
        });
      });

      // sort the revisions/reconsiderations properly
      finalRevisions = finalRevisions.sort((a, b) => {
        const at = moment(a.date).format('x');
        const bt = moment(b.date).format('x');

        if (at === bt) return 0;
        return at < bt ? 1 : -1;
      });

      return state
        .remove('gettingRevisions')
        .set('gettingRevisionsSuccess', true)
        .set('revisions', Immutable.fromJS(finalRevisions));
    case GET_REVISIONS_FAIL:
      return state
        .remove('gettingRevisions')
        .set('gettingRevisionsSuccess', false);
    /**
     * Get additional documents
     */
    case GET_ADDITIONAL_DOCUMENTS:
      return state
        .set('gettingAdditionalDocuments', true)
        .remove('getAdditionalDocumentsSuccess');
    case GET_ADDITIONAL_DOCUMENTS_SUCCESS:
      return state
        .setIn(['appraisalDocs', 'additionalDoc'], Immutable.fromJS(action.result.data))
        .remove('gettingAdditionalDocuments')
        .set('getAdditionalDocumentsSuccess', true);
    case GET_ADDITIONAL_DOCUMENTS_FAIL:
      return state
        .remove('gettingAdditionalDocuments')
        .set('getAdditionalDocumentsSuccess', false);
    /**
     * Get additional document types
     */
    case GET_ADDITIONAL_DOCUMENT_TYPES:
      return state
        .set('gettingAdditionalDocumentTypes', true)
        .remove('getAdditionalDocumentTypesSuccess');
    case GET_ADDITIONAL_DOCUMENT_TYPES_SUCCESS:
      return state
        .setIn(['appraisalDocs', 'additionalDocTypes'], Immutable.fromJS(action.result.data))
        .remove('gettingAdditionalDocumentTypes')
        .set('getAdditionalDocumentTypesSuccess', true);
    case GET_ADDITIONAL_DOCUMENT_TYPES_FAIL:
      return state
        .remove('gettingAdditionalDocumentTypes')
        .set('getAdditionalDocumentTypesSuccess', false);
    /**
     * Get available additional statuses
     */
    case GET_ADDITIONAL_STATUSES:
      return state
        .set('gettingAdditionalStatuses', true)
        .mergeIn(['additionalStatus'], Immutable.fromJS({
          selectedStatus: 0,
          message: ''
        }))
        .remove('getAdditionalStatusSuccess');
    case GET_ADDITIONAL_STATUSES_SUCCESS:
      return state
        .setIn(['additionalStatus', 'statuses'], Immutable.fromJS(action.result.data))
        .mergeIn(['additionalStatus'], Immutable.fromJS({
          selectedStatus: 0,
          message: ''
        }))
        .remove('gettingAdditionalStatuses')
        .set('getAdditionalStatusSuccess', true);
    case GET_ADDITIONAL_STATUSES_FAIL:
      return state
        .remove('gettingAdditionalStatuses')
        .set('getAdditionalStatusSuccess', false);
    /**
     * Submit a new additional status
     */
    case SUBMIT_ADDITIONAL_STATUS:
      return state
        .set('submittingAdditionalStatus', true)
        .remove('submitAdditionalStatusSuccess');
    case SUBMIT_ADDITIONAL_STATUS_SUCCESS:
      return state
        .remove('submittingAdditionalStatus')
        .mergeIn(['additionalStatus'], Immutable.fromJS({
          selectedStatus: 0,
          message: ''
        }))
        .set('submitAdditionalStatusSuccess', true);
    case SUBMIT_ADDITIONAL_STATUS_FAIL:
      return state
        .remove('submittingAdditionalStatus')
        .set('submitAdditionalStatusSuccess', false);
    /**
     * File upload
     */
    case FILE_UPLOAD:
      return state
        .remove('fileUploadSuccess')
        .remove('addingAdditionalDoc')
        .set('uploadingFile', true);
    case FILE_UPLOAD_SUCCESS:
      let uploadState = state;
      switch (action.actionType) {
        case 'appraisalDoc':
          // Add to upload array
          uploadState = addAppraisalDoc(state, action, ['appraisalDoc']);
          break;
        case 'subDoc':
          // Add to upload array
          uploadState = addAppraisalDoc(state, action, ['appraisalSubDocs', action.result.format], SUB_DOC);
          break;
        case 'additionalDoc':
          // Find the associated doc type
          //const selectedDocType = state.getIn(['appraisalDocs', 'selectedDocType']);
          // Add to upload array
          //uploadState = addAppraisalDoc(state, action, ['additionalDoc'], SUB_DOC);
          uploadState = uploadState.set('addingAdditionalDoc', Immutable.fromJS(action.result));
          break;
      }
      return uploadState
        .set('fileUploadSuccess', true)
        .remove('uploadingFile');
    case FILE_UPLOAD_FAIL:
      return state
        .set('fileUploadSuccess', false)
        .remove('addingAdditionalDoc')
        .remove('uploadingFile');
    /**
     * Email a document
     */
    case EMAIL_DOC:
      return state
        .set('emailingDoc', true)
        .remove('emailDocSuccess');
    case EMAIL_DOC_SUCCESS:
      return state
        .remove('emailingDoc')
        .set('emailDocSuccess', true);
    case EMAIL_DOC_FAIL:
      return state
        .remove('emailingDoc')
        .set('emailDocSuccess', false)
        .setIn(['errors', 'appraisalDocs', 'appraisalDocEmailRecipient'],
          ['An error has occurred emailing the selected recipient. Please try again later.']);
    /**
     * Add a primary doc
     */
    case ADD_DOC:
      return state
        .set('addingDoc', true)
        .remove('addDocSuccess');
    case ADD_DOC_SUCCESS:
      return state
        .remove('addingDoc')
        .set('addDocSuccess', true);
    case ADD_DOC_FAIL:
      let errorMessage;
      try {
        errorMessage = action.error.errors.document.message;
      } catch (e) {
        errorMessage = '';
      }
      return state
        .remove('addingDoc')
        .set('addDocSuccess', false)
        .setIn(['errors', 'addDoc'], errorMessage);
    /**
     * Get existing appraisal doc
     */
    case GET_APPRAISAL_DOC:
      return state
        .set('gettingAppraisalDoc', true)
        .remove('getAppraisalDocSuccess');
    case GET_APPRAISAL_DOC_SUCCESS:
      let subDocMap = Immutable.Map();
      let ordersDocState;
      let documents;
      // Display all docs for customer
      if (action.userType === 'customer') {
        const data = Immutable.fromJS(action.result.data);
        documents = data.map(document => {
          return document.get('primary').set('extra', document.get('extra'));
        });
      } else {
        documents = Immutable.fromJS(action.result.primaries) || Immutable.List();
      }
      if (!action.result) {
        ordersDocState = state
          .remove('gettingAppraisalDoc')
          .set('getAppraisalDocSuccess', true)
          .setIn(['appraisalDocs', 'appraisalDoc'], Immutable.List())
          .setIn(['appraisalDocs', 'appraisalSubDocs'], subDocMap);
      } else {
        // Doc visible, but appraiser can't see it
        if (action.result.showToAppraiser === false) {
          ordersDocState = state
            .remove('gettingAppraisalDoc')
            .set('getAppraisalDocSuccess', true)
            .setIn(['selectedRecord', 'docVisible'], false)
            .setIn(['appraisalDocs', 'createdAt'], action.result.createdAt);
        } else {
          ordersDocState = state
            .setIn(['appraisalDocs', 'appraisalDoc'], documents)
            .remove('gettingAppraisalDoc')
            .set('getAppraisalDocSuccess', true)
            .setIn(['appraisalDocs', 'createdAt'], action.result.createdAt)
            .setIn(['selectedRecord', 'docVisible'], true);
        }
        if (action.result.extra) {
          // Create map of sub docs
          action.result.extra.forEach(doc => {
            doc = Immutable.fromJS(doc);
            subDocMap = subDocMap.set(doc.get('format'), doc);
          });
        }
        ordersDocState = ordersDocState
          .setIn(['appraisalDocs', 'appraisalDoc'], documents)
          .setIn(['appraisalDocs', 'appraisalSubDocs'], subDocMap);
      }
      return ordersDocState;
    case GET_APPRAISAL_DOC_FAIL:
      return state
        .remove('gettingAppraisalDoc')
        .set('getAppraisalDocSuccess', false)
        .removeIn(['selectedRecord', 'docVisible']);
    /**
     * Get sub document formats
     */
    case GET_DOC_FORMATS:
      return state
        .set('gettingDocFormats', true)
        .remove('getDocFormatSuccess');
    case GET_DOC_FORMATS_SUCCESS:
      return state
        .setIn(['appraisalDocs', 'formats'], Immutable.fromJS(action.result))
        .remove('gettingDocFormats')
        .set('getDocFormatSuccess', true);
    case GET_DOC_FORMATS_FAIL:
      return state
        .remove('gettingDocFormats')
        .set('getDocFormatSuccess', false);
    case FETCH_MARKER:
      return state
        .setIn(['selectedRecord', 'property', 'fetchFailed'], false)
        .setIn(['selectedRecord', 'property', 'fetching'], true);
    case FETCH_MARKER_SUCCESS:
      return state
        .setIn(['selectedRecord', 'property', 'position'], {
          lat: action.result.position.lat(),
          lng: action.result.position.lng()
        })
        .setIn(['selectedRecord', 'property', 'fetchFailed'], false)
        .setIn(['selectedRecord', 'property', 'fetching'], false);
    case FETCH_MARKER_FAIL:
      return state
        .setIn(['selectedRecord', 'property', 'fetching'], false)
        .setIn(['selectedRecord', 'property', 'fetchFailed'], true);
    /**
     * Retrieve appraiser CC on file
     */
    case GET_CC_INFO:
      return state
        .set('gettingCcInfo', true)
        .remove('getCcInfoSuccess');
    case GET_CC_INFO_SUCCESS:
      // CC number
      return state
        .remove('gettingCcInfo')
        .set('getCcInfoSuccess', true)
        .set('ccNumber', action.result.number);
    case GET_CC_INFO_FAIL:
      return state
        .remove('gettingCcInfo')
        .set('getCcInfoSuccess', false);
    /**
     * Pay the tech fee associated with an order
     */
    case PAY_TECH_FEE:
      return state
        .set('payingTechFee', true)
        .remove('payTechFeeSuccess');
    case PAY_TECH_FEE_SUCCESS:
      return state
        .remove('payingTechFee')
        .set('payTechFeeSuccess', true)
        .setIn(['selectedRecord', 'isTechFeePaid'], true);
    case PAY_TECH_FEE_FAIL:
      return state
        .remove('payingTechFee')
        .set('payTechFeeSuccess', false)
        .set('creditCardRejection', action.error.message);
    /**
     * Place order on hold
     */
    case PLACE_ON_HOLD:
      return state
        .set('placingOnHold', true)
        .remove('placeOnHoldSuccess');
    case PLACE_ON_HOLD_SUCCESS:
      return state
        .remove('placingOnHold')
        .set('placeOnHoldSuccess', true)
        .set('placeOnHoldReason', '');
    case PLACE_ON_HOLD_FAIL:
      return state
        .remove('placingOnHold')
        .set('placeOnHoldSuccess', false)
        .set('placeOnHoldError', true);
    /**
     * Resume order which had been placed on hold
     */
    case RESUME:
      return state
        .set('resumingOrder', true)
        .remove('resumeOrderSuccess');
    case RESUME_SUCCESS:
      return state
        .remove('resumingOrder')
        .set('resumeOrderSuccess', true);
    case RESUME_FAIL:
      return state
        .remove('resumingOrder')
        .set('resumeOrderSuccess', false);
    default:
      return state;
  }
}

/**
 * Retrieve a single order
 * @param user
 * @param orderId
 */
export function getOrder(user, orderId) {
  return {
    types: [GET_ORDER, GET_ORDER_SUCCESS, GET_ORDER_FAIL],
    promise: client => client.get(`dev:/${user.get('type')}s/${user.get('id')}/orders/${orderId}`, {
      data: {
        headers: {
          Include: allOrdersIncludes
        }
      }
    })
  };
}

/**
 * Get the status of the order for an appraiser
 * @param user
 * @param selectedAppraiser Appraiser for customer view
 */
export function getOrderStatuses(user, selectedAppraiser) {
  const userId = user.get('id');
  const userType = user.get('type');
  let url;
  // Customer view
  if (user.get('type') === 'customer') {
    url = `dev:/customers/${userId}/appraisers/${selectedAppraiser}/queues/counters`;
  } else {
    url = `dev:/${userType}s/${userId}/queues/counters`;
  }
  return {
    types: [GET_ORDER_STATUSES, GET_ORDER_STATUSES_SUCCESS, GET_ORDER_STATUSES_FAIL],
    promise: client => client.get(url)
  };
}

/**
 * Retrieve orders from a certain queue
 * @param user
 * @param queue A string that specifies the queue name
 * @param searchVals Any kind of search or filter vals
 * @param selectedAppraiser Selected appraiser for customer view
 */
export function getOrderQueue(user, queue, searchVals, selectedAppraiser) {
  const userId = user.get('id');
  const userType = user.get('type');
  let url;
  // Customer view
  if (user.get('type') === 'customer') {
    url = `dev:/customers/${userId}/appraisers/${selectedAppraiser}/queues/${queue}?${createQueryParams(searchVals)}`;
  } else {
    url = `dev:/${userType}s/${userId}/queues/${queue}?${createQueryParams(searchVals)}`;
  }
  return {
    types: [GET_ORDERS, GET_ORDERS_SUCCESS, GET_ORDERS_FAIL],
    promise: client => client.get(url, {
      data: {
        headers: {
          Include: allOrdersIncludes,
          'Act-As-Assignee': true
        }
      }
    })
  };
}

/**
 * Set a property explicitly
 * @param value Property value
 * @param name Name path
 */
export function setProp(value, ...name) {
  let validate = false;
  // Trigger validation
  if (name[name.length - 1] === 'validate') {
    name = name.slice(0, name.length - 1);
    validate = true;
  }
  return {
    type: SET_PROP,
    name,
    value,
    validate
  };
}

/**
 * Select a record
 * @param params
 */
export function selectRecord(params) {
  return {
    types: [SELECT_RECORD, SELECT_RECORD_SUCCESS, SELECT_RECORD_FAILURE],
    promise: () => new Promise(resolve => resolve()),
    ...params
  };
}

/**
 * Close the details pane
 */
export function closeDetailsPane(persistUiState) {
  persistUiState = persistUiState === true;
  return {
    type: CLOSE_DETAILS_PANE,
    persistUiState
  };
}

/**
 * Show the accept order dialog
 * @param dialogType Type of dialog to toggle
 * @param order Order record
 */
export function toggleDialog(dialogType, order) {
  return {
    type: TOGGLE_DIALOG,
    dialogType,
    order
  };
}

/**
 * Accept the order
 * @param user User object
 * @param orderId Order ID
 */
export function acceptOrder(user, orderId) {
  return {
    types: [ACCEPT_ORDER, ACCEPT_ORDER_SUCCESS, ACCEPT_ORDER_FAIL],
    promise: client => client.post(`dev:/${user.get('type')}s/${user.get('id')}/orders/${orderId}/accept`),
    orderId
  };
}

/**
 * Accept an order with conditions
 * @param user User object
 * @param orderId Order ID
 * @param conditions Conditions
 */
export function acceptWithConditions(user, orderId, conditions) {
  return {
    types: [ACCEPT_ORDER_WITH_CONDITIONS, ACCEPT_ORDER_WITH_CONDITIONS_SUCCESS, ACCEPT_ORDER_WITH_CONDITIONS_FAIL],
    promise: client => client.post(`dev:/${user.get('type')}s/${user.get('id')}/orders/${orderId}/accept-with-conditions`, {
      data: conditions
    })
  };
}

/**
 * Decline an order
 * @param user User object
 * @param orderId Order ID
 * @param data Decline reason and message
 */
export function declineOrder(user, orderId, data) {
  return {
    types: [DECLINE_ORDER, DECLINE_ORDER_SUCCESS, DECLINE_ORDER_FAIL],
    promise: client => client.post(`dev:/${user.get('type')}s/${user.get('id')}/orders/${orderId}/decline`, {
      data
    })
  };
}

/**
 * Submit bid
 * @param user
 * @param orderId
 * @param bid Bid record
 */
export function doSubmitBid(user, orderId, bid) {
  return {
    types: [SUBMIT_BID, SUBMIT_BID_SUCCESS, SUBMIT_BID_FAIL],
    promise: client => client.post(`dev:/${user.get('type')}s/${user.get('id')}/orders/${orderId}/bid`, {
      data: bid
    })
  };
}

/**
 * Schedule inspection
 * @param user
 * @param orderId
 * @param schedule Schedule record
 */
export function doScheduleInspection(user, orderId, schedule) {
  return {
    types: [SCHEDULE_INSPECTION, SCHEDULE_INSPECTION_SUCCESS, SCHEDULE_INSPECTION_FAIL],
    promise: client => client.post(`dev:/${user.get('type')}s/${user.get('id')}/orders/${orderId}/schedule-inspection`, {
      data: schedule
    })
  };
}

/**
 * Set inspection complete
 * @param user
 * @param orderId
 * @param complete Inspection complete record
 */
export function doInspectionComplete(user, orderId, complete) {
  return {
    types: [INSPECTION_COMPLETE, INSPECTION_COMPLETE_SUCCESS, INSPECTION_COMPLETE_FAIL],
    promise: client => client.post(`dev:/${user.get('type')}s/${user.get('id')}/orders/${orderId}/complete-inspection`, {
      data: complete
    })
  };
}

/**
 * Get existing messages for email office
 */
export function getMessages(user, orderId) {
  return {
    types: [GET_MESSAGES, GET_MESSAGES_SUCCESS, GET_MESSAGES_FAIL],
    promise: client => client.get(`dev:/${user.get('type')}s/${user.get('id')}/orders/${orderId}/messages?perPage=1000&orderBy=createdAt:desc`, {
      data: {
        headers: {
          Include: 'sender.companyName'
        }
      }
    }),
    orderId
  };
}

/**
 * Submit a new message
 * @param user
 * @param orderId Order ID
 * @param message Message text
 * @returns {{type: string, appraiserId: *, message: *}}
 */
export function newMessage(user, orderId, message) {
  return {
    types: [NEW_MESSAGE, NEW_MESSAGE_SUCCESS, NEW_MESSAGE_FAIL],
    promise: client => client.post(`dev:/${user.get('type')}s/${user.get('id')}/orders/${orderId}/messages`, {
      data: {
        content: message,
        headers: {
          Include: 'sender.companyName'
        }
      }
    })
  };
}

/**
 * Get notifications
 * @param user
 * @param orderId
 * @param selectedAppraiser
 */
export function getNotifications(user, orderId, selectedAppraiser) {
  let userType = user.get('type');
  let userId = user.get('id');
  const headers = {};
  // Customer user
  if (userType === 'customer') {
    userType = 'appraiser';
    userId = selectedAppraiser;
    headers['Act-As-Assignee'] = true;
  }
  return {
    types: [GET_NOTIFICATIONS, GET_NOTIFICATIONS_SUCCESS, GET_NOTIFICATIONS_FAIL],
    promise: client => client.get(`dev:/${userType}s/${userId}/orders/${orderId}/logs?perPage=10000&filter[initiator]=true`)
  };
}

/**
 * Search notifications
 * @param appraiserId
 * @param searchString
 */
export function searchNotifications(appraiserId, searchString) {
  return {
    type: SEARCH_NOTIFICATIONS,
    appraiserId,
    searchString
  };
}

/**
 * Get additional statuses
 * @param user
 * @param orderId
 * @param selectedAppraiser
 */
export function getAdditionalStatuses(user, orderId, selectedAppraiser) {
  let userType = user.get('type');
  let userId = user.get('id');
  const headers = {};
  // Customer user
  if (userType === 'customer') {
    userType = 'appraiser';
    userId = selectedAppraiser;
    headers['Act-As-Assignee'] = true;
  }
  return {
    types: [GET_ADDITIONAL_STATUSES, GET_ADDITIONAL_STATUSES_SUCCESS, GET_ADDITIONAL_STATUSES_FAIL],
    promise: client => client.get(`dev:/${userType}s/${userId}/orders/${orderId}/additional-statuses`)
  };
}

/**
 * Submit additional status
 * @param user
 * @param orderId
 * @param status
 * @param message
 */
export function submitAdditionalStatus(user, orderId, status, message) {
  const userType = user.get('type');
  const userId = user.get('id');
  return {
    types: [SUBMIT_ADDITIONAL_STATUS, SUBMIT_ADDITIONAL_STATUS_SUCCESS, SUBMIT_ADDITIONAL_STATUS_FAIL],
    promise: client => client.post(`dev:/${userType}s/${userId}/orders/${orderId}/change-additional-status`, {
      data: {
        additionalStatus: Number(status),
        comment: message
      }
    })
  };
}

/**
 * Get revisions/reconsideration requests
 */
export function getRevisions(user, orderId) {
  const requests = [
    `GET /api/v2.0/${user.get('type')}s/${user.get('id')}/orders/${orderId}/revisions`,
    `GET /api/v2.0/${user.get('type')}s/${user.get('id')}/orders/${orderId}/reconsiderations`
  ];

  return {
    types: [GET_REVISIONS, GET_REVISIONS_SUCCESS, GET_REVISIONS_FAIL],
    promise: client => client.post(`batch:/batch`, {
      data: requests
    })
  };
}

/**
 * Upload appraiser documents
 */
export function uploadFile(docType, document, actionType) {
  return fileUpload([FILE_UPLOAD, FILE_UPLOAD_SUCCESS, FILE_UPLOAD_FAIL], docType, document, actionType);
}

/**
 * Retrieve additional documents
 * @param user
 * @param orderId
 */
export function getAdditionalDocuments(user, orderId) {
  let url;
  if (user.get('type') === 'customer') {
    url = `dev:/customers/${user.get('id')}/orders/${orderId}/additional-documents`;
  } else {
    url = `dev:/${user.get('type')}s/${user.get('id')}/orders/${orderId}/additional-documents`;
  }
  return {
    types: [GET_ADDITIONAL_DOCUMENTS, GET_ADDITIONAL_DOCUMENTS_SUCCESS, GET_ADDITIONAL_DOCUMENTS_FAIL],
    promise: client => client.get(url)
  };
}

/**
 * Retrieve additional document types
 * @param user
 * @param orderId
 */
export function getAdditionalDocumentTypes(user, orderId) {
  return {
    types: [GET_ADDITIONAL_DOCUMENT_TYPES, GET_ADDITIONAL_DOCUMENT_TYPES_SUCCESS, GET_ADDITIONAL_DOCUMENT_TYPES_FAIL],
    promise: client => client.get(`dev:/${user.get('type')}s/${user.get('id')}/orders/${orderId}/additional-documents/types`)
  };
}

/**
 * Add a document to an order
 * @param user User
 * @param orderId Order ID
 * @param documentId Uploaded doc ID
 * @param documentToken Uploaded doc token
 * @param docType Which type of doc is being uploaded
 * @param additionalDoc Additional doc type only
 */
export function addDoc(user, orderId, documentId, documentToken, docType, additionalDoc) {
  let data = {};
  let method = 'post';
  let url = `dev:/${user.get('type')}s/${user.get('id')}/orders/${orderId}/document`;
  const dataObject = {
    id: documentId,
    token: documentToken
  };
  switch (docType) {
    // Primary appraisal doc
    case 'appraisalDoc':
      data = {
        primary: dataObject
      };
      break;
    case 'subDoc':
      method = 'patch';
      data = {
        extra: documentToken
      };
      break;
    case 'additionalDoc':
      url = `dev:/${user.get('type')}s/${user.get('id')}/orders/${orderId}/additional-documents`;
      data = {
        id: documentId,
        type: additionalDoc.type,
        label: additionalDoc.label,
        document: {
          id: additionalDoc.document,
          token: documentToken,
        },
      };
      break;
  }
  return {
    types: [ADD_DOC, ADD_DOC_SUCCESS, ADD_DOC_FAIL],
    promise: client => client[method](url, {
      data,
      options: {
        overrideToken: false,
      }
    })
  };
}

/**
 * Get existing appraisal doc
 * @param user
 * @param orderId
 * @param selectedAppraiser
 */
export function getAppraisalDoc(user, orderId, selectedAppraiser) {
  let userType = user.get('type');
  let userId = user.get('id');
  const headers = {};
  // Customer user
  if (userType === 'customer') {
    userType = 'appraiser';
    userId = selectedAppraiser;
    headers['Act-As-Assignee'] = true;
  }
  return {
    types: [GET_APPRAISAL_DOC, GET_APPRAISAL_DOC_SUCCESS, GET_APPRAISAL_DOC_FAIL],
    promise: client => client.get(`dev:/${userType}s/${userId}/orders/${orderId}/document`, {
      data: {
        headers
      }
    })
  };
}

/**
 * Get available sub document formats
 * @param user
 * @param orderId
 * @param selectedAppraiser
 */
export function getDocFormats(user, orderId, selectedAppraiser) {
  let userType = user.get('type');
  let userId = user.get('id');
  const headers = {};
  // Customer user
  if (userType === 'customer') {
    userType = 'appraiser';
    userId = selectedAppraiser;
    headers['Act-As-Assignee'] = true;
  }
  return {
    types: [GET_DOC_FORMATS, GET_DOC_FORMATS_SUCCESS, GET_DOC_FORMATS_FAIL],
    promise: client => client.get(`dev:/${userType}s/${userId}/orders/${orderId}/document/formats`, {
      data: {
        headers
      }
    })
  };
}

/**
 * Send document via email
 */
export function sendEmail(appraiserId, orderId) {
  // @todo Just until I can access the backend
  return {
    type: EMAIL_DOC_FAIL,
    appraiserId,
    orderId
  };
  // return {
  //   types: [EMAIL_DOC, EMAIL_DOC_SUCCESS, EMAIL_DOC_SUCCESS],
  //   promise: client => client.get(`dev:/appraisers/${appraiserId}/orders/${orderId}/email-appraisal-doc`)
  // };
}

export function clearOrders() {
  return {
    type: CLEAR_ORDERS,
  };
}

/**
 * Fetch marker for Google Maps display
 * @param marker
 */
export function fetchMarkerAddress(marker) {
  const geocoder = new window.google.maps.Geocoder();
  let address = '';

  if (marker.get('address1')) {
    address += marker.get('address1');
  }
  if (marker.get('address2')) {
    address += marker.get('address2');
  }
  if (marker.get('city')) {
    address += ', ' + marker.get('city');
  }
  if (marker.getIn(['state', 'code'])) {
    address += ', ' + marker.getIn(['state', 'code']);
  }
  if (marker.get('zip')) {
    address += ' ' + marker.get('zip');
  }

  const promise = () => {
    return new Promise((resolve, reject) => {
      geocoder.geocode({address: address}, function(results, status) {
        if (status === window.google.maps.GeocoderStatus.OK) {
          return resolve({
            position: results[0].geometry.location
          });
        } else {
          return reject({
            position: null
          });
        }
      });
    });
  };

  return {
    types: [FETCH_MARKER, FETCH_MARKER_SUCCESS, FETCH_MARKER_FAIL],
    promise: promise
  };
}

/**
 * See if the current appraiser has a credit card on file
 * @param appraiserId
 */
export function getCcOnFile(appraiserId) {
  return {
    types: [GET_CC_INFO, GET_CC_INFO_SUCCESS, GET_CC_INFO_FAIL],
    promise: client => client.get(`dev:/appraisers/${appraiserId}/payment/credit-card`)
  };
}

/**
 * Pay the tech fee
 * @param appraiserId
 * @param orderId
 */
export function payTechFee(appraiserId, orderId) {
  return {
    types: [PAY_TECH_FEE, PAY_TECH_FEE_SUCCESS, PAY_TECH_FEE_FAIL],
    promise: client => client.post(`dev:/appraisers/${appraiserId}/orders/${orderId}/pay-tech-fee`)
  };
}

/**
 * Reset search parameters
 */
export function resetSearchParams() {
  return setProp(initialState.get('search'), 'search');
}

/**
 * Place order on hold
 * @param user User
 * @param orderId Order ID
 * @param data Message
 */
export function placeOrderOnHold(user, orderId, data) {
  return {
    types: [PLACE_ON_HOLD, PLACE_ON_HOLD_SUCCESS, PLACE_ON_HOLD_FAIL],
    promise: client => client.post(`dev:/amcs/${user.get('id')}/orders/${orderId}/workflow/on-hold`, {
      data
    })
  };
}

/**
 * Resume order which had been placed on hold
 * @param user User
 * @param orderId Order ID
 */
export function resumeOrder(user, orderId) {
  return {
    types: [RESUME, RESUME_SUCCESS, RESUME_FAIL],
    promise: client => client.post(`dev:/amcs/${user.get('id')}/orders/${orderId}/workflow/resume`)
  };
}
