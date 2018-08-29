const SET_PROP = 'vp/settings/SET_PROP';
const REMOVE_ITEM = 'vp/settings/REMOVE_ITEM';
// ACH
const GET_ACH_INFO = 'vp/settings/GET_ACH_INFO';
const GET_ACH_INFO_SUCCESS = 'vp/settings/GET_ACH_INFO_SUCCESS';
const GET_ACH_INFO_FAIL = 'vp/settings/GET_ACH_INFO_FAIL';
const SUBMIT_ACH_INFO = 'vp/settings/SUBMIT_ACH_INFO';
const SUBMIT_ACH_INFO_SUCCESS = 'vp/settings/SUBMIT_ACH_INFO_SUCCESS';
const SUBMIT_ACH_INFO_FAIL = 'vp/settings/SUBMIT_ACH_INFO_FAIL';
// CC
const GET_CC_INFO = 'vp/settings/GET_CC_INFO';
const GET_CC_INFO_SUCCESS = 'vp/settings/GET_CC_INFO_SUCCESS';
const GET_CC_INFO_FAIL = 'vp/settings/GET_CC_INFO_FAIL';
const SUBMIT_CC_INFO = 'vp/settings/SUBMIT_CC_INFO';
const SUBMIT_CC_INFO_SUCCESS = 'vp/settings/SUBMIT_CC_INFO_SUCCESS';
const SUBMIT_CC_INFO_FAIL = 'vp/settings/SUBMIT_CC_INFO_FAIL';
// Availability
const GET_AVAILABILITY = 'vp/settings/GET_AVAILABILITY';
const GET_AVAILABILITY_SUCCESS = 'vp/settings/GET_AVAILABILITY_SUCCESS';
const GET_AVAILABILITY_FAIL = 'vp/settings/GET_AVAILABILITY_FAIL';
const SET_AVAILABILITY = 'vp/settings/SET_AVAILABILITY';
const SET_AVAILABILITY_SUCCESS = 'vp/settings/SET_AVAILABILITY_SUCCESS';
const SET_AVAILABILITY_FAIL = 'vp/settings/SET_AVAILABILITY_FAIL';
const GET_NOTIFICATIONS = 'vp/settings/GET_NOTIFICATIONS';
const GET_NOTIFICATIONS_SUCCESS = 'vp/settings/GET_NOTIFICATIONS_SUCCESS';
const GET_NOTIFICATIONS_FAIL = 'vp/settings/GET_NOTIFICATIONS_FAIL';
const SET_NOTIFICATIONS = 'vp/settings/SET_NOTIFICATIONS';
const SET_NOTIFICATIONS_SUCCESS = 'vp/settings/SET_NOTIFICATIONS_SUCCESS';
const SET_NOTIFICATIONS_FAIL = 'vp/settings/SET_NOTIFICATIONS_FAIL';
const SELECT_CUSTOMER = 'vp/settings/SELECT_CUSTOMER';
const REMOVE_CUSTOMER = 'vp/settings/REMOVE_CUSTOMER';
const SET_PASSWORD = 'vp/settings/SET_PASSWORD';
const SET_PASSWORD_SUCCESS = 'vp/settings/SET_PASSWORD_SUCCESS';
const SET_PASSWORD_FAIL = 'vp/settings/SET_PASSWORD_FAIL';
// Send issue
const SEND_ISSUE = 'vp/settings/SEND_ISSUE';
const SEND_ISSUE_SUCCESS = 'vp/settings/SEND_ISSUE_SUCCESS';
const SEND_ISSUE_FAIL = 'vp/settings/SEND_ISSUE_FAIL';
// Send request a feature
const REQUEST_FEATURE = 'vp/settings/REQUEST_FEATURE';
const REQUEST_FEATURE_SUCCESS = 'vp/settings/REQUEST_FEATURE_SUCCESS';
const REQUEST_FEATURE_FAIL = 'vp/settings/REQUEST_FEATURE_FAIL';
// Get customers
const GET_CUSTOMER = 'vp/settings/GET_CUSTOMER';
const GET_CUSTOMER_SUCCESS = 'vp/settings/GET_CUSTOMER_SUCCESS';
const GET_CUSTOMER_FAIL = 'vp/settings/GET_CUSTOMER_FAIL';

// Default customer
export const DEFAULT_CUSTOMER = 0;

/**
 * Validation
 */
import {
  zip as valZip,
  pattern as valPattern,
  presence as valPresence,
  backendErrorsImmutable
} from '../../helpers/validation';

import Immutable from 'immutable';
import moment from 'moment';

// Sign up form fields
const fields = [
  // Fields
  'currentUsername', 'currentPassword', 'oldPassword', 'newPassword', 'confirmPassword', 'bankName', 'accountNumber',
  'routing'
];

/**
 * Initial ACH and CC objects
 */
const initialCcObject = {
  form: {
    number: '',
    code: '',
    expiresAt: {
      month: parseInt(moment().format('MM'), 10),
      year: parseInt(moment().format('YYYY'), 10)
    }
  },
  errors: {}
};
export const initialAchObject = {
  // Account number for display in string representation
  accountNumber: '',
  form: {
    accountType: 'checking',
    bankName: '',
    routing: '',
    accountNumber: ''
  },
  errors: {}
};

// Validation constraints
const constraints = {};
// Initial state
const initialState = Immutable.fromJS({
  achInfo: initialAchObject,
  // Display ach form
  showAchForm: false,
  // CC number for display in string representation
  number: '',
  ccInfo: initialCcObject,
  // Display CC form
  showCcForm: false,
  availability: {
    form: {
      isOnVacation: false,
      message: ''
    },
    errors: {}
  },
  notification: {
    selected: {},
    customers: []
  },
  password: {
    form: {},
    errors: {}
  },
  // Report an issue value
  reportIssueValue: '',
  // Request feature value
  requestFeatureValue: '',
  customers: [],
  selectedCustomer: DEFAULT_CUSTOMER
});

/**
 * Validate fields, create initial state
 */
fields.forEach(field => {
  constraints[field] = {};
  // All fields required
  valPresence(constraints, field);
  // Zip
  if (field === 'zip') {
    valZip(constraints, field);
  }
  // Name and business name
  if (['routing'].indexOf(field) !== -1) {
    valPattern(constraints, field, /^[0-9]{9}$/, 'must be a nine digit number');
  }
});

export default function reducer(state = initialState, action = {}) {
  switch (action.type) {
    /**
     * Set property explicitly
     */
    case SET_PROP:
      // Apply any conditional validation
      return state.setIn(action.name, action.value);
    /**
     * Remove an item
     */
    case REMOVE_ITEM:
      return state;
    /**
     * Retrieve CC info
     */
    case GET_CC_INFO:
      return state
        .set('gettingCcInfo', true)
        .remove('getCcInfoSuccess')
        .set('showCcForm', false);
    case GET_CC_INFO_SUCCESS:
      let ccNumber = null;
      let stateGetCc = state
        .remove('gettingCcInfo')
        .set('getCcInfoSuccess', true);
      // CC number
      if (action.result && action.result.number) {
        ccNumber = action.result.number;
        stateGetCc = stateGetCc.setIn(['ccInfo', 'number'], ccNumber);
      } else {
        // Show form if no cc info is available
        stateGetCc = stateGetCc.set('showCcForm', true);
      }
      return stateGetCc;
    case GET_CC_INFO_FAIL:
      return state
        .remove('gettingCcInfo')
        .set('getCcInfoSuccess', false);
    /**
     * Update CC info
     */
    case SUBMIT_CC_INFO:
      return state
        .set('submittingCcInfo', true)
        .remove('submitCcInfoSuccess');
    case SUBMIT_CC_INFO_SUCCESS:
      return state
        .remove('submittingCcInfo')
        .set('submitCcInfoSuccess', true)
        .set('showCcForm', false)
        .set('ccInfo', Immutable.fromJS(initialCcObject))
        .setIn(['ccInfo', 'number'], action.result.number);
    case SUBMIT_CC_INFO_FAIL:
      return state
        .remove('submittingCcInfo')
        .set('submitCcInfoSuccess', false)
        .setIn(['ccInfo', 'errors'], Immutable.fromJS(action.error.errors));
    /**
     * Retrieve current ACH info
     */
    case GET_ACH_INFO:
      return state
        .set('getAch', true)
        .remove('getAchSuccess')
        .set('showAchForm', false);
    case GET_ACH_INFO_SUCCESS:
      let newState = state
        .remove('getAch')
        .set('getAchSuccess', true)
        .set('achInfo', Immutable.fromJS(initialAchObject));

      // make sure we have some data
      if (action.result.accountNumber) {
        newState = newState.setIn(['achInfo', 'accountNumber'], action.result.accountNumber);
        // Show form if no ACH info available
      } else {
        newState = newState.set('showAchForm', true);
      }

      return newState;
    case GET_ACH_INFO_FAIL:
      return state
        .remove('getAch')
        .set('getAchSuccess', false);
    /**
     * Submit updated ACH info
     */
    case SUBMIT_ACH_INFO:
      return state
        .set('submitAch', true)
        .remove('submitAchSuccess');
    case SUBMIT_ACH_INFO_SUCCESS:
      return state
        .remove('submitAch')
        .set('submitAchSuccess', true)
        .setIn(['achInfo', 'errors'], Immutable.Map())
        .setIn(['achInfo', 'form'], Immutable.Map().set('accountType', 'checking'))
        .setIn(['achInfo', 'accountNumber'], action.result.accountNumber)
        .set('showAchForm', false);
    case SUBMIT_ACH_INFO_FAIL:
      return state
        .remove('submitAch')
        .set('submitAchSuccess', false)
        .setIn(['achInfo', 'errors'], backendErrorsImmutable(action));
    /**
     * Get availability
     */
    case GET_AVAILABILITY:
      return state
        .set('gettingAvailability', true)
        .remove('getAvailabilitySuccess');
    case GET_AVAILABILITY_SUCCESS:
      return state
        .remove('gettingAvailability')
        .set('getAvailabilitySuccess', true)
        .setIn(['availability', 'form'], Immutable.fromJS(
          (action.perCustomer ? action.result : action.result.availability) || {isOnVacation: false, message: ''}
        ));
    case GET_AVAILABILITY_FAIL:
      return state
        .remove('gettingAvailability')
        .set('getAvailabilitySuccess', false);
    /**
     * Set availability
     */
    case SET_AVAILABILITY:
      return state
        .set('settingAvailability', true)
        .remove('setAvailabilitySuccess');
    case SET_AVAILABILITY_SUCCESS:
      return state
        .remove('settingAvailability')
        .setIn(['availability', 'errors'], Immutable.Map())
        .set('setAvailabilitySuccess', true);
    case SET_AVAILABILITY_FAIL:
      return state
        .remove('settingAvailability')
        .setIn(['availability', 'errors'], backendErrorsImmutable(action))
        .set('setAvailabilitySuccess', false);
    /**
     * Select a customer
     */
    case SELECT_CUSTOMER:
      return state.setIn(['notification', 'selected', action.customerId], Immutable.fromJS({
        customer: Number(action.customerId),
        email: true
      }));
    /**
     * Remove a customer
     */
    case REMOVE_CUSTOMER:
      return state.setIn(['notification', 'selected', action.customerId], Immutable.fromJS({
        customer: Number(action.customerId),
        email: false
      }));
    /**
     * Get/set notifications
     */
    case GET_NOTIFICATIONS:
      return state
        .set('gettingNotifications', true);
    case GET_NOTIFICATIONS_SUCCESS:
      return state
        .set('gettingNotifications', false)
        .setIn(['notification', 'customers'], Immutable.fromJS(action.result.notifications));
    case GET_NOTIFICATIONS_FAIL:
      return state
        .remove('gettingNotifications')
        .set('gettingNotificationsFail', true);
    case SET_NOTIFICATIONS:
      return state
        .set('settingNotifications', true)
        .remove('setNotificationSuccess');
    case SET_NOTIFICATIONS_SUCCESS:
      return state
        .remove('settingNotifications')
        .set('setNotificationSuccess', true);
    case SET_NOTIFICATIONS_FAIL:
      return state
        .remove('settingNotifications')
        .set('setNotificationSuccess', false);
    /**
     * Set new password
     */
    case SET_PASSWORD:
      return state
        .set('passwordReset', true)
        .remove('passwordResetSuccess');
    case SET_PASSWORD_SUCCESS:
      return state
        .remove('passwordReset')
        .set('passwordResetSuccess', true)
        .setIn(['password', 'errors'], Immutable.Map())
        .setIn(['password', 'form'], Immutable.Map());
    case SET_PASSWORD_FAIL:
      return state
        .remove('passwordReset')
        .set('passwordResetSuccess', false)
        .setIn(['password', 'errors'], backendErrorsImmutable(action));
    /**
     * Send issue
     */
    case SEND_ISSUE:
      return state
        .set('sendingIssue', true)
        .remove('sendIssueSuccess');
    case SEND_ISSUE_SUCCESS:
      return state
        .remove('sendingIssue')
        .set('sendIssueSuccess', true)
        .set('reportIssueValue', '');
    case SEND_ISSUE_FAIL:
      return state
        .remove('sendingIssue')
        .set('sendIssueSuccess', false);
    /**
     * Request feature
     */
    case REQUEST_FEATURE:
      return state
        .set('requestFeature', true)
        .remove('requestFeatureSuccess');
    case REQUEST_FEATURE_SUCCESS:
      return state
        .remove('requestFeature')
        .set('requestFeatureSuccess', true)
        .set('requestFeatureValue', '');
    case REQUEST_FEATURE_FAIL:
      return state
        .remove('requestFeature')
        .set('requestFeatureSuccess', false);
    case GET_CUSTOMER:
      return state
        .set('gettingCustomer', false)
        .remove('gettingCustomerSuccess');
    case GET_CUSTOMER_SUCCESS:
      const customers = Immutable.fromJS(action.result.data);
      const sortedCustomers = customers.sort((a, b) => {
        a = typeof a.get('name') === 'string' ? a.get('name').toLowerCase() : '';
        b = typeof b.get('name') === 'string' ? b.get('name').toLowerCase() : '';
        if (a === b) {
          return 0;
        }
        return a < b ? -1 : 1;
      });
      return state
        .set('getCustomersSuccess', true)
        .set('customers', sortedCustomers)
        .remove('gettingCustomers');
    case GET_CUSTOMER_FAIL:
      return state
        .set('gettingCustomerSuccess', false)
        .remove('gettingCustomer');
    default:
      return state;
  }
}

/**
 * Remove item (such as when setting to not on vacation)
 */
export function removeItem(name) {
  return {
    type: REMOVE_ITEM,
    name
  };
}

/**
 * Explicitly set a value
 */
export function setProp(value, ...name) {
  return {
    type: SET_PROP,
    name,
    value
  };
}

/**
 * Retrieve ACH info
 */
export function getAchInfo(user, selectedAppraiser) {
  let url;
  const userId = user.get('id');
  const userType = user.get('type');
  if (userType === 'appraiser') {
    url = `dev:/appraisers/${userId}/ach`;
  } else if (userType === 'customer') {
    url = `dev:/customers/${userId}/appraisers/${selectedAppraiser}/ach`;
  } else if (userType === 'amc') {
    url = `dev:/amcs/${userId}/payment/bank-account`;
  }
  return {
    types: [GET_ACH_INFO, GET_ACH_INFO_SUCCESS, GET_ACH_INFO_FAIL],
    promise: (client) => client.get(url)
  };
}

/**
 * Submit ACH info
 */
export function submitAchInfo(user, data) {
  let url;
  if (user.get('type') === 'appraiser') {
    url = `dev:/appraisers/${user.get('id')}/ach`;
  } else if (user.get('type') === 'amc') {
    url = `dev:/amcs/${user.get('id')}/payment/bank-account`;
  }
  return {
    types: [SUBMIT_ACH_INFO, SUBMIT_ACH_INFO_SUCCESS, SUBMIT_ACH_INFO_FAIL],
    promise: client => client.put(url, {
      data
    })
  };
}

/**
 * Retrieve CC info
 */
export function getCcInfo(user, selectedAppraiser) {
  let userId = user.get('id');
  let userType = user.get('type');
  // Customer view
  if (userType === 'customer') {
    userId = selectedAppraiser;
    userType = 'appraiser';
  }
  return {
    types: [GET_CC_INFO, GET_CC_INFO_SUCCESS, GET_CC_INFO_FAIL],
    promise: (client) => client.get(`dev:/${userType}s/${userId}/payment/credit-card`)
  };
}

/**
 * Submit CC info
 */
export function submitCcInfo(user, data) {
  return {
    types: [SUBMIT_CC_INFO, SUBMIT_CC_INFO_SUCCESS, SUBMIT_CC_INFO_FAIL],
    promise: client => client.put(`dev:/${user.get('type')}s/${user.get('id')}/payment/credit-card`, {
      data
    })
  };
}

/**
 * Get availability
 * @param {Number|Object} user User record
 * @param {Number} customerId
 */
export function getAvailability(user, customerId) {
  let url;
  let perCustomer = false;

  if (!isNaN(user)) {
    url = `dev:/appraisers/${user}`;
  } else {
    url = `dev:/${user.get('type')}s/${user.get('id')}`;

    if (['appraiser', 'manager'].indexOf(user.get('type')) !== -1 && customerId && customerId !== DEFAULT_CUSTOMER) {
      url += `/customers/${customerId}/availability`;
      perCustomer = true;
    }
  }

  return {
    types: [GET_AVAILABILITY, GET_AVAILABILITY_SUCCESS, GET_AVAILABILITY_FAIL],
    promise: client => client.get(url, {
      data: {
        headers: {
          Include: 'availability'
        }
      }
    }),
    perCustomer
  };
}

/**
 * Submit availability
 * @param user User record
 * @param {Number} customerId
 * @param data Availability data
 */
export function setAvailability(user, customerId, data) {
  let url = `dev:/${user.get('type')}s/${user.get('id')}`;

  if (['appraiser', 'manager'].indexOf(user.get('type')) !== -1 && customerId && customerId !== DEFAULT_CUSTOMER) {
    url += `/customers/${customerId}`;
  }

  return {
    types: [SET_AVAILABILITY, SET_AVAILABILITY_SUCCESS, SET_AVAILABILITY_FAIL],
    promise: client => client.patch(url + '/availability', {
      data: data
    })
  };
}

/**
 * Select a customer
 * @param customerId
 */
export function selectCustomer(customerId) {
  return {
    type: SELECT_CUSTOMER,
    customerId
  };
}

/**
 * Remove a customer
 * @param customerId
 */
export function removeCustomer(customerId) {
  return {
    type: REMOVE_CUSTOMER,
    customerId
  };
}

/**
 * Get notifications
 */
export function getNotification(user, selectedAppraiser) {
  const userId = user.get('id');
  const userType = user.get('type');
  let url = `dev:/appraisers/${userId}/settings`;
  // Customer view
  if (userType === 'customer') {
    url = `dev:/customers/${userId}/appraisers/${selectedAppraiser}/settings`;
  }
  return {
    types: [GET_NOTIFICATIONS, GET_NOTIFICATIONS_SUCCESS, GET_NOTIFICATIONS_FAIL],
    promise: client => client.get(url)
  };
}

/**
 * Set notifications
 */
export function setNotification(appraiserId, settings) {
  const notifications = [];
  settings.forEach((setting) => {
    notifications.push(setting.toJS());
  });

  return {
    types: [SET_NOTIFICATIONS, SET_NOTIFICATIONS_SUCCESS, SET_NOTIFICATIONS_FAIL],
    promise: client => client.patch(`dev:/appraisers/${appraiserId}/settings`, {
      data: {
        notifications
      }
    })
  };
}

/**
 * Patch appraiser
 */
export function updatePassword(appraiserId, appraiser) {
  return {
    types: [SET_PASSWORD, SET_PASSWORD_SUCCESS, SET_PASSWORD_FAIL],
    promise: client => client.patch(`dev:/appraisers/${appraiserId}`, {
      data: Object.assign(appraiser, {
        headers: {
          'Soft-Validation-Mode': true
        }
      })
    })
  };
}

/**
 * Report an issue
 */
export function sendIssue(description) {
  return {
    types: [SEND_ISSUE, SEND_ISSUE_SUCCESS, SEND_ISSUE_FAIL],
    promise: client => client.post(`dev:/help/issues`, {
      data: {
        description
      }
    })
  };
}

/**
 * Request a feature
 */
export function requestFeature(description) {
  return {
    types: [REQUEST_FEATURE, REQUEST_FEATURE_SUCCESS, REQUEST_FEATURE_FAIL],
    promise: client => client.post(`dev:/help/feature-requests`, {
      data: {
        description
      }
    })
  };
}

/**
 * Given an appraiser or a manager, retrieves their customer list
 *
 * @param {number} user
 */
export function getCustomers(user) {
  return {
    types: [GET_CUSTOMER, GET_CUSTOMER_SUCCESS, GET_CUSTOMER_FAIL],
    promise: client => client.get(`dev:/${user.get('type')}s/${user.get('id')}/customers`)
  };
}
