const LOGIN = 'vp/auth/LOGIN';
const LOGIN_SUCCESS = 'vp/auth/LOGIN_SUCCESS';
const LOGIN_FAIL = 'vp/auth/LOGIN_FAIL';
const AUTO_LOGIN = 'vp/auth/AUTO_LOGIN';
const AUTO_LOGIN_SUCCESS = 'vp/auth/AUTO_LOGIN_SUCCESS';
const AUTO_LOGIN_FAIL = 'vp/auth/AUTO_LOGIN_FAIL';
const REGISTER = 'vp/auth/REGISTER';
const REGISTER_SUCCESS = 'vp/auth/REGISTER_SUCCESS';
const REGISTER_FAIL = 'vp/auth/REGISTER_FAIL';
const LOGOUT = 'redux-example/auth/LOGOUT';
const LOGOUT_SUCCESS = 'redux-example/auth/LOGOUT_SUCCESS';
const LOGOUT_FAIL = 'redux-example/auth/LOGOUT_FAIL';
const VALIDATE_USER = 'vp/auth/VALIDATE_USER';
const VALIDATE_USER_SUCCESS = 'vp/auth/VALIDATE_USER_SUCCESS';
const VALIDATE_USER_FAIL = 'vp/auth/VALIDATE_USER_FAIL';
const REFRESH_SESSION = 'vp/auth/REFRESH_SESSION';
const REFRESH_SESSION_SUCCESS = 'vp/auth/REFRESH_SESSION_SUCCESS';
const REFRESH_SESSION_FAIL = 'vp/auth/REFRESH_SESSION_FAIL';
const FORM_CHANGE = 'vp/auth/FORM_CHANGE';
const RECOVER_ACCOUNT = 'vp/auth/RECOVER_ACCOUNT';
const RECOVER_ACCOUNT_SUCCESS = 'vp/auth/RECOVER_ACCOUNT_SUCCESS';
const RECOVER_ACCOUNT_FAIL = 'vp/auth/RECOVER_ACCOUNT_FAIL';
const RESET_PASSWORD = 'vp/auth/RESET_PASSWORD';
const RESET_PASSWORD_SUCCESS = 'vp/auth/RESET_PASSWORD_SUCCESS';
const RESET_PASSWORD_FAIL = 'vp/auth/RESET_PASSWORD_FAIL';
// Set a property
const SET_PROP = 'vp/auth/SET_PROP';
// Set a property
const REMOVE_PROP = 'vp/auth/REMOVE_PROP';
// Print content
const SET_PRINT_CONTENT = 'vp/auth/SET_PRINT_CONTENT';
const REMOVE_PRINT_CONTENT = 'vp/auth/REMOVE_PRINT_CONTENT';
// Remove session
const REMOVE_SESSION = 'vp/auth/REMOVE_SESSION';
// Change tab on login/sign up page
const CHANGE_AUTH_TAB = 'vp/auth/CHANGE_AUTH_TAB';
// Hide welcome message / initial display
const HIDE_INITIAL_DISPLAY = 'vp/auth/HIDE_INITIAL_DISPLAY';
const HIDE_INITIAL_DISPLAY_SUCCESS = 'vp/auth/HIDE_INITIAL_DISPLAY_SUCCESS';
const HIDE_INITIAL_DISPLAY_FAIL = 'vp/auth/HIDE_INITIAL_DISPLAY_FAIL';
// Tabs
export const LOGIN_TAB = 1;
export const AMC_TAB = 2;
export const APPRAISER_TAB = 3;

import Immutable from 'immutable';
import moment from 'moment';

const initialState = Immutable.fromJS({
  form: {
    username: '',
    password: ''
  },
  validationSet: {},
  formErrors: {},
  resetPassword: {},
  resetPasswordErrors: {},
  autoLoginForm: {},
  autoLoginErrors: {},
  printWindow: null,
  // Recover account
  recoverAccount: {
    email: ''
  },
  recoverAccountErrors: {}
});

// Validation methods
import {
  backendErrorsImmutable,
  frontendErrorsImmutable,
  presence as valPresence,
  validateUsernamePasswordAfterType,
} from '../../helpers/validation';

import {
  storeSession,
} from 'helpers/genericFunctions';

// Validation
const fields = ['username', 'password'];
const constraints = {};

// Don't put any special validation here, let the backend handle it
fields.forEach(field => {
  constraints[field] = {};
});

// Set constraints
fields.forEach(field => {
  constraints[field] = {};
  // Make sure both validation for presence
  valPresence(constraints, field);
});

export default function reducer(state = initialState, action = {}) {
  switch (action.type) {
    /**
     * Set a property explicitly
     */
    case SET_PROP:
      return state.setIn(action.name, action.value);
    case REMOVE_PROP:
      return state.removeIn(action.name);
    case SET_PRINT_CONTENT:
      return state.set('printWindow', action.content);
    case REMOVE_PRINT_CONTENT:
      return state.set('printWindow', null);
    case FORM_CHANGE:
      const form = state.get('form').set(action.name, action.value);
      // Apply any conditional validation
      state = validateUsernamePasswordAfterType(state, action, constraints);
      return state
        .set('form', form)
        .set('formErrors', frontendErrorsImmutable(state, action, 'formErrors', form, constraints));
    /**
     * Login normally
     */
    case LOGIN:
      return state
        .set('loggingIn', true)
        .remove('loginSuccess');
    case LOGIN_SUCCESS:
      // Store session
      storeSession(action.result);
      // Get user for session
      return state
        .set('loggingIn', false)
        .set('loginSuccess', true)
        .set('signingUp', action.signingUp)
        .set('autoLoginForm', action.signingUp ? Immutable.fromJS(action.result.user) : Immutable.Map())
        .set('user', Immutable.fromJS(action.result.user)
          .set('token', action.result.token)
          .set('sessionId', action.result.id))
        .set('tokenExpires', action.result.expireAt)
        .set('keepLoggedIn', action.keepLoggedIn);
    case LOGIN_FAIL:
      return state
        .set('loggingIn', false)
        .set('loginSuccess', false)
        .set('user', null)
        .set('formErrors', backendErrorsImmutable(action));
    case AUTO_LOGIN:
      return state
        .set('autoLoginUpdate', true)
        .remove('autoLoginUpdateSuccess');
    case AUTO_LOGIN_SUCCESS:
      return state
        .remove('autoLoginUpdate')
        .set('autoLoginUpdateSuccess', true);
    case AUTO_LOGIN_FAIL:
      return state
        .remove('autoLoginUpdate')
        .set('autoLoginUpdateSuccess', false)
        .set('autoLoginErrors', backendErrorsImmutable(action));
    /**
     * Validate a user based on their token
     */
    case VALIDATE_USER:
      return state
        .set('validating', true)
        .remove('validateSuccess')
        .remove('sessionRequiresRefresh');
    // Validate success
    case VALIDATE_USER_SUCCESS:
      // Store session
      storeSession(action.result);
      // See if session needs to be regenerated
      const now = moment();
      const expires = moment(action.result.expireAt);
      let validateState;
      // Attempt to refresh expired session
      if (now > expires) {
        //return this.props.refreshSession();
        validateState = state.set('sessionRequiresRefresh', true);
      } else {
        // Create
        validateState = state
          .remove('validating')
          .set('validateSuccess', true)
          .set('user', Immutable.fromJS(action.result.user)
          .set('token', action.result.token)
          .set('sessionId', action.result.id));
      }
      return validateState;
    // Validate failure
    case VALIDATE_USER_FAIL:
      return state
        .remove('validating')
        .set('validateSuccess', false)
        .set('user', null)
        .set('error', action.error);
    /**
     * Register
     */
    case REGISTER:
      return state
        .set('registering', true)
        .remove('registerSuccess');
    // Successfully registered
    case REGISTER_SUCCESS:
      return state
        .set('registering', false)
        .set('registerSuccess', true)
        .set('user', action.result);
    // Unable to register
    case REGISTER_FAIL:
      return state
        .set('registerError', action.error)
        .set('registerSuccess', false)
        .set('registering', false);
    /**
     * Logout
     */
    case LOGOUT:
      return state
        .set('loggingOut', true)
        .remove('logoutSuccess');
    case LOGOUT_SUCCESS:
      return state
        .set('loggingOut', false)
        .set('logoutSuccess', true)
        .set('user', null);
    case LOGOUT_FAIL:
      return state
        .set('loggingOut', false)
        .set('logoutSuccess', false)
        .set('logoutError', action.error);
    /**
     * Recover account
     */
    case RECOVER_ACCOUNT:
      return state
        .remove('recoverAccountSuccess')
        .set('sendingRecoverAccount');
    case RECOVER_ACCOUNT_SUCCESS:
      return state
        .remove('sendingRecoverAccount')
        .set('recoverAccountSuccess', true);
    case RECOVER_ACCOUNT_FAIL:
      return state
        .remove('sendingRecoverAccount')
        .set('recoverAccountSuccess', false)
        .set('recoverAccountErrors', backendErrorsImmutable(action));

    /**
     * Reset password
     */
    case RESET_PASSWORD:
      return state
        .remove('resetPasswordSuccess')
        .set('sendingResetPassword');
    case RESET_PASSWORD_SUCCESS:
      return state
        .remove('sendingResetPassword')
        .set('resetPasswordSuccess', true);
    case RESET_PASSWORD_FAIL:
      return state
        .remove('sendingResetPassword')
        .set('resetPasswordSuccess', false)
        .set('resetPasswordErrors', backendErrorsImmutable(action));
    /**
     * Refresh session
     */
    case REFRESH_SESSION:
      return state.set('refreshingSession', true)
        .remove('refreshSessionSuccess');
    case REFRESH_SESSION_SUCCESS:
      // Store session
      storeSession(action.result);
      return state
        .set('refreshingSession', false)
        .set('refreshSessionSuccess', true)
        .set('user', Immutable.fromJS(action.result.user)
          .set('token', action.result.token)
          .set('sessionId', action.result.id));
    case REFRESH_SESSION_FAIL:
      return state
        .set('refreshSessionSuccess', false)
        .set('refreshingSession', false);
    /**
     * Remove session from localStorage
     */
    case REMOVE_SESSION:
      localStorage.removeItem('token');
      localStorage.removeItem('userId');
      localStorage.removeItem('sessionId');
      return state;
    /**
     * Hide initial display
     */
    case HIDE_INITIAL_DISPLAY:
      return state
        .remove('initialDisplayHidden')
        .set('hidingInitialDisplay', true);
    case HIDE_INITIAL_DISPLAY_SUCCESS:
      return state
        .remove('hidingInitialDisplay')
        .set('initialDisplayHidden', true);
    case HIDE_INITIAL_DISPLAY_FAIL:
      return state
        .remove('hidingInitialDisplay')
        .set('initialDisplayHidden', false);
    /**
     * Change auth tab
     */
    case CHANGE_AUTH_TAB:
      return state.set('authTab', action.authTab);
    default:
      return state;
  }
}

/**
 * Update a form element
 */
export function authFormChange(props) {
  return {
    type: FORM_CHANGE,
    ...props
  };
}

/**
 * Log the user in
 * @param form username/password
 * @param keepLoggedIn Keep the user logged in
 */
export function login(form, keepLoggedIn = false) {
  return {
    types: [LOGIN, LOGIN_SUCCESS, LOGIN_FAIL],
    promise: (client) => client.post('dev:/sessions', {
      data: {
        ...form,
        headers: {
          Include: 'user.isRegistered,user.showInitialDisplay,user.isBoss'
        }
      }
    }),
    // Pass true if signing in during the signup process
    signingUp: form.signingUp,
    keepLoggedIn
  };
}

/**
 * Update a user from the auto login
 */
export function autoLoginUpdate(appraiserId, form) {
  return {
    types: [AUTO_LOGIN, AUTO_LOGIN_SUCCESS, AUTO_LOGIN_FAIL],
    promise: (client) => client.patch(`dev:/appraisers/${appraiserId}`, {
      data: {
        ...form
      }
    })
  };
}

/**
 * Check to see if a user token is valid
 * @param params Token and sessionId
 */
export function validateUser(params) {
  return {
    types: [VALIDATE_USER, VALIDATE_USER_SUCCESS, VALIDATE_USER_FAIL],
    promise: (client) => client.get(`dev:/sessions/${params.sessionId}`, {
      data: {
        token: params.token,
        headers: {
          Include: 'user.showInitialDisplay,user.isBoss'
        }
      }
    })
  };
}

/**
 * Log the user out
 * @returns {{types: *[], promise: promise}}
 */
export function logout() {
  const {token, sessionId} = localStorage;
  let action;
  // If we have a token, logout
  if (token) {
    action = {
      types: [LOGOUT, LOGOUT_SUCCESS, LOGOUT_FAIL],
      promise: (client) => client.delete(`dev:/sessions/${sessionId}`, {
        data: {
          token
        }
      })
    };
    // No token, just log out
  } else {
    action = {
      type: LOGOUT_SUCCESS
    };
  }
  return action;
}

/**
 * Refresh a session
 */
export function refreshSession() {
  const {sessionId, token} = localStorage;
  let reducerVal;
  // Refresh session
  if (token && sessionId) {
    reducerVal = {
      types: [REFRESH_SESSION, REFRESH_SESSION_SUCCESS, REFRESH_SESSION_FAIL],
      promise: client => client.post(`dev:/sessions/${sessionId}/refresh`),
      data: {
        token,
        headers: {
          Include: 'user.showInitialDisplay,user.isBoss'
        }
      }
    };
    // No token or session ID
  } else {
    reducerVal = {
      type: REFRESH_SESSION_FAIL
    };
  }
  return reducerVal;
}

/**
 * Remove session
 */
export function removeSession() {
  return {
    type: REMOVE_SESSION
  };
}

/**
 * Change tab on main auth screen
 * @param tab
 */
export function changeTabHomePage(tab) {
  return {
    type: CHANGE_AUTH_TAB,
    authTab: tab
  };
}

/**
 * Set a property explicitly
 * @param value Value to set it to
 * @param name Name arguments forming array for setIn
 */
export function setProp(value, ...name) {
  return {
    type: SET_PROP,
    name,
    value
  };
}

/**
 * Remove a property explicitly
 * @param value Value to set it to
 * @param name Name arguments forming array for setIn
 */
export function removeProp(...name) {
  return {
    type: REMOVE_PROP,
    name
  };
}

/**
 * Set the content to print
 */
export function setPrintContent(content) {
  return {
    type: SET_PRINT_CONTENT,
    content
  };
}

/**
 * Remove the content to print
 */
export function removePrintContent() {
  return {
    type: REMOVE_PRINT_CONTENT
  };
}

/**
 * Recover account
 */
export function recoverAccount(email) {
  return {
    types: [RECOVER_ACCOUNT, RECOVER_ACCOUNT_SUCCESS, RECOVER_ACCOUNT_FAIL],
    promise: client => client.post(`dev:/help/hints`, {
      data: {
        email
      }
    })
  };
}

/**
 * Request for reset password
 */
export function resetPassword(token, password) {
  return {
    types: [RESET_PASSWORD, RESET_PASSWORD_SUCCESS, RESET_PASSWORD_FAIL],
    promise: client => client.post(`dev:/password/change`, {
      data: {
        token,
        password
      }
    })
  };
}

/**
 * Hide welcome message / initial display
 * @param userId
 */
export function hideInitialDisplay(userId) {
  return {
    types: [HIDE_INITIAL_DISPLAY, HIDE_INITIAL_DISPLAY_SUCCESS, HIDE_INITIAL_DISPLAY_FAIL],
    promise: client => client.patch(`dev:/appraisers/${userId}`, {
      data: {
        showInitialDisplay: false,
        headers: {
          'Soft-Validation-Mode': true
        }
      }
    })
  };
}
