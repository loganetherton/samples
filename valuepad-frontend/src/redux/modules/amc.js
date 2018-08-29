const SIGNUP_FORM_CHANGE = 'vp/amc/SIGNUP_FORM_CHANGE';
const SIGNUP_AMC = 'vp/amc/SIGNUP_AMC';
const SIGNUP_AMC_SUCCESS = 'vp/amc/SIGNUP_AMC_SUCCESS';
const SIGNUP_AMC_FAIL = 'vp/amc/SIGNUP_AMC_FAIL';
const SIGNUP_AMC_OWNER = 'vp/amc/SIGNUP_AMC_OWNER';
const SIGNUP_AMC_OWNER_SUCCESS = 'vp/amc/SIGNUP_AMC_OWNER_SUCCESS';
const SIGNUP_AMC_OWNER_FAIL = 'vp/amc/SIGNUP_AMC_OWNER_FAIL';
const DELETE_AMC = 'vp/amc/DELETE_AMC';
const DELETE_AMC_SUCCESS = 'vp/amc/DELETE_AMC_SUCCESS';
const DELETE_AMC_FAILURE = 'vp/amc/DELETE_AMC_FAILURE';
// Update AMC
const UPDATE_AMC = 'vp/amc/UPDATE_AMC';
const UPDATE_AMC_SUCCESS = 'vp/amc/UPDATE_AMC_SUCCESS';
const UPDATE_AMC_FAIL = 'vp/amc/UPDATE_AMC_FAIL';
// Get AMC
const GET_AMC = 'vp/amc/GET_AMC';
const GET_AMC_SUCCESS = 'vp/amc/GET_AMC_SUCCESS';
const GET_AMC_FAIL = 'vp/amc/GET_AMC_FAIL';
// Update AMC's password
const UPDATE_PASSWORD = 'vp/amc/UPDATE_PASSWORD';
const UPDATE_PASSWORD_SUCCESS = 'vp/amc/UPDATE_PASSWORD_SUCCESS';
const UPDATE_PASSWORD_FAIL = 'vp/amc/UPDATE_PASSWORD_FAIL';
// Set prop
const SET_PROP = 'vp/amc/SET_PROP';

/**
 * Validation
 */
import {
  email as valEmail,
  zip as valZip,
  pattern as valPattern,
  presence as valPresence,
  excludeValue as valState,
  backendErrorsImmutable,
  frontendErrorsImmutable,
  validateUsernamePasswordAfterType
} from '../../helpers/validation';

import Immutable from 'immutable';
// Expose set default
import {setDefault, setProp as setPropInherited} from 'helpers/genericFunctions';

// Sign up form fields
const signUpFields = [
  // Company fields
  'companyName', 'address1', 'address2', 'city', 'state', 'zip',
  // Owner fields
  'email', 'username', 'password', 'confirm', 'firstName', 'lastName', 'phone', 'fax'
];

// Validation constraints
const constraints = {};
// Initial state
const initialState = Immutable.fromJS({
  signUpForm: {},
  signUpFormErrors: {}
});

/**
 * Validate fields, create initial state
 */
signUpFields.forEach(field => {
  constraints[field] = {};
  // Don't validate for presence of state or fax
  if (['fax', 'address2'].indexOf(field) === -1) {
    valPresence(constraints, field, '^This field is required');
  }
  // Email validation
  if (field === 'email') {
    valEmail(constraints, field);
  }
  // Zip code
  if (field === 'zip') {
    valZip(constraints, field);
  }
  // Phone/fax validation
  if (['fax', 'phone'].indexOf(field) !== -1) {
    valPattern(constraints, field, /\(\d{3}\)\s?\d{3}-\d{4}/, 'must be in the following format: (xxx) xxx-xxxx');
  }
  // Make sure a state is chosen
  if (field === 'state') {
    valState(constraints, field, {NONE: ''});
  }
});

export default function reducer(state = initialState, action = {}) {
  switch (action.type) {
    // Change sign up form
    case SIGNUP_FORM_CHANGE:
      // Sign up form
      const signUpForm = state.get('signUpForm').set(action.name, action.value);
      // Apply conditional validation for username and password
      state = validateUsernamePasswordAfterType(state, action, constraints);
      return state
        .set('signUpForm', signUpForm)
        .set('signUpFormErrors', frontendErrorsImmutable(state, action, 'signUpFormErrors', signUpForm, constraints));
    /**
     * Sign up AMC methods
     */
    case SIGNUP_AMC:
      return state
        .set('signingUpAmc', true)
        .remove('signUpAmcSuccess');
    case SIGNUP_AMC_SUCCESS:
      return state
        .set('newAmc', Immutable.fromJS(action.result))
        .remove('signingUpAmc')
        .set('signUpAmcSuccess', true);
    case SIGNUP_AMC_FAIL:
      const backendErrors = backendErrorsImmutable(action);
      return state
        .set('signUpFormErrors', backendErrors)
        .remove('signingUpAmc')
        .set('signUpAmcSuccess', false);
    /**
     * Signup AMC owner methods
     */
    case SIGNUP_AMC_OWNER:
      return state
        .set('signingUpOwner', true);
    case SIGNUP_AMC_OWNER_SUCCESS:
      return state
        .remove('signingUpOwner');
    case SIGNUP_AMC_OWNER_FAIL:
      const signUpBackendErrors = backendErrorsImmutable(action);
      return state
        .remove('signingUpOwner')
        .set('signUpFormErrors', signUpBackendErrors);
    /**
     * Delete AMC on failure (if AMC is created, but owner fails to create
     */
    case DELETE_AMC:
      return state.set('deletingAmc', true);
    case DELETE_AMC_SUCCESS:
      return state.set('deletingAmc', false);
    // @todo Not quite sure how to handle this one yet
    case DELETE_AMC_FAILURE:
      return state.set('deletingAmc', false);
    /**
     * Update a AMC
     */
    case UPDATE_AMC:
      return state
        .set('updatingAmc', true)
        .remove('updateAmcSuccess');
    case UPDATE_AMC_SUCCESS:
      return state
        .set('updateAmcSuccess', true)
        .remove('updatingAmc');
    case UPDATE_AMC_FAIL:
      return state
        .set('updateAmcSuccess', false)
        .remove('updatingAmc');
    /**
     * Get a AMC
     */
    case GET_AMC:
      return state
        .set('gettingAmc', true)
        .remove('getAmcSuccess');
    case GET_AMC_SUCCESS:
      let amc = Immutable.fromJS(action.result);
      amc = amc.set('state', amc.getIn(['state', 'code']));
      amc = amc.remove('username');

      return state
        .set('getAmcSuccess', true)
        .set('signUpForm', amc)
        .remove('gettingAmc');
    case GET_AMC_FAIL:
      return state
        .set('getAmcSuccess', false)
        .remove('gettingAmc');
    /**
     * Update password
     */
    case UPDATE_PASSWORD:
      return state
        .set('passwordReset', true)
        .remove('passwordResetSuccess');
    case UPDATE_PASSWORD_SUCCESS:
      return state
        .set('passwordResetSuccess', true)
        .set('signUpForm', Immutable.Map())
        .remove('passwordReset');
    case UPDATE_PASSWORD_FAIL:
      return state
        .set('passwordResetSuccess', false)
        .set('signUpFormErrors', backendErrorsImmutable(action))
        .remove('passwordReset');
    /**
     * Set a property explicitly
     */
    case SET_PROP:
      // Set value first for error state
      const setPropState = state.setIn(action.name, action.value);
      return setPropState
        .set('signUpFormErrors',
          frontendErrorsImmutable(setPropState, action, 'signUpFormErrors', setPropState.get('signUpForm'), constraints));
    default:
      return state;
  }
}

/**
 * Change AMC signup form
 */
export function formChange(event) {
  const {target: {name, value}} = event;
  return {
    type: SIGNUP_FORM_CHANGE,
    name,
    value
  };
}

/**
 * Create new AMC
 * @param form
 */
export function createAmc(form) {
  return {
    types: [SIGNUP_AMC, SIGNUP_AMC_SUCCESS, SIGNUP_AMC_FAIL],
    promise: client => client.post('dev:/amcs', {
      data: {
        ...form
      }
    })
  };
}

/**
 * Set default value for state
 * @param name
 * @param value
 */
export function amcSetDefault(name, value) {
  return setDefault(SIGNUP_FORM_CHANGE, name, value);
}

/**
 * Set a property explicitly
 * @param value Value to set it to
 * @param name Name arguments forming array for setIn
 */
export function setProp(value, ...name) {
  return setPropInherited(SET_PROP, value, ...name);
}

/**
 * Update an AMC
 *
 * @param amcId The ID of the AMC
 * @param data New information about the AMC
 */
export function updateAmc(amcId, data) {
  return {
    types: [UPDATE_AMC, UPDATE_AMC_SUCCESS, UPDATE_AMC_FAIL],
    promise: client => client.patch(`dev:/amcs/${amcId}`, {
      data
    })
  };
}

/**
 * Get an AMC
 *
 * @param amcId The ID of the AMC
 */
export function getAmc(amcId) {
  return {
    types: [GET_AMC, GET_AMC_SUCCESS, GET_AMC_FAIL],
    promise: client => client.get(`dev:/amcs/${amcId}`, {
      data: {
        headers: {
          Include: 'address1,address2,city,state,zip,phone,fax,lenders'
        }
      }
    })
  };
}

/**
 * Update an AMC's password
 *
 * @param amcId The ID of the AMC
 * @param password The AMC's new password
 */
export function updatePassword(amcId, password) {
  return {
    types: [UPDATE_PASSWORD, UPDATE_PASSWORD_SUCCESS, UPDATE_PASSWORD_FAIL],
    promise: client => client.patch(`dev:/amcs/${amcId}`, {
      data: {
        password
      }
    })
  };
}
