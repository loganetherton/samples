const FORM_CHANGE = 'vp/w9/FORM_CHANGE';
const REMOVE_ITEM = 'vp/w9/REMOVE_ITEM';
const SET_DEFAULT = 'vp/w9/SET_DEFAULT';
const SUBMIT = 'vp/w9/SUBMIT';
const SUBMIT_SUCCESS = 'vp/w9/SUBMIT_SUCCESS';
const SUBMIT_FAIL = 'vp/w9/SUBMIT_FAIL';
// Get w9
const GET_W9 = 'vp/w9/GET_W9';
const GET_W9_SUCCESS = 'vp/w9/GET_W9_SUCCESS';
const GET_W9_FAIL = 'vp/w9/GET_W9_FAIL';
// Update w9
const UPDATE_W9 = 'vp/w9/UPDATE_W9';
const UPDATE_W9_SUCCESS = 'vp/w9/UPDATE_W9_SUCCESS';
const UPDATE_W9_FAIL = 'vp/w9/UPDATE_W9_FAIL';
// Set prop
const SET_PROP = 'vp/w9/SET_PROP';

/**
 * Validation
 */
import {
  frontendErrorsImmutable,
  zip as valZip,
  pattern as valPattern,
  presence as valPresence
} from '../../helpers/validation';

import Immutable from 'immutable';

// Sign up form fields
const fields = [
  // Fields
  'name', 'federalTaxClassification', 'businessName', 'limitedLiabilityCompanyType', 'accountNumbers',
  'federalTaxClassificationInstruction', 'limitedLiabilityCompanyType', 'exemptionFATCAReporting', 'exemptionPayeeCode',
  'requesterName', 'taxIdentificationNumber', 'signature', 'initials', 'zip'
];

// Validation constraints
const constraints = {};
// Initial state
const initialState = Immutable.fromJS({
  w9: {},
  w9Errors: {},
  // If we're updating or not
  w9Exists: false,
  // Display update success dialog
  updateW9Success: false,
});

/**
 * Validate fields, create initial state
 */
fields.forEach(field => {
  constraints[field] = {};
  // Don't validate optional fields
  if (['accountNumbers', 'exemptionFATCAReporting', 'exemptionPayeeCode', 'businessName', 'requesterName', 'requesterAddress'].indexOf(field) === -1) {
    valPresence(constraints, field);
  }
  // Zip
  if (field === 'zip') {
    valZip(constraints, field);
  }
  // Name and business name
  if (['name', 'businessName', 'signature'].indexOf(field) !== -1) {
    valPattern(constraints, field, /^[0-9a-zA-Z ]+$/, 'must be alphanumeric');
  }
  // Signature accepts only letters
  if (field === 'initials') {
    valPattern(constraints, field, /^[a-zA-Z]+$/, 'must be only letters');
  }
  // Tax identification regex validation
  if (field === 'taxIdentificationNumber') {
    valPattern(constraints, field, /^(\d{3}-\d{2}-\d{4}|\d{2}-\d{7})$/,
      'must follow one of these patterns: xxx-xx-xxxx or xx-xxxxxxx');
  }
});

export default function reducer(state = initialState, action = {}) {
  switch (action.type) {
    // Change w9 form
    case FORM_CHANGE:
      // Sign up form
      const w9 = state.get('w9').set(action.name, action.value);
      return state
        .set('w9', w9)
        .set('w9Errors', frontendErrorsImmutable(state, action, 'w9Errors', w9, constraints));
    // Remove an item from the w9
    case REMOVE_ITEM:
      // Remove items from form
      const newW9 = state.get('w9').delete(action.name);
      // Remove from validation errors
      const w9Errors = state.get('w9Errors').delete(action.name);
      return state.set('w9', newW9).set('w9Errors', w9Errors);
    // Set a dropdown to a default value
    case SET_DEFAULT:
      return state.set('w9', state.get('w9').set(action.name, action.value));
    /**
     * Submit w9
     */
    case SUBMIT:
      return state
        .set('submitting', true)
        .remove('submitSuccess');
    case SUBMIT_SUCCESS:
      return state
        .remove('submitting')
        .set('submitSuccess', true);
    // @todo Handle
    case SUBMIT_FAIL:
      return state
        .remove('submitting')
        .set('submitSuccess', false);
    /**
     * Get existing w9
     */
    case GET_W9:
      return state
        .set('gettingW9', true)
        .remove('getW9Success')
        .remove('w9Exists');
    case GET_W9_SUCCESS:
      const existingW9 = Immutable.fromJS(action.result);
      return state
        .remove('gettingW9')
        .set('getW9Success', true)
        .set('w9Exists', true)
        .set('w9', existingW9
          .set('accountNumbers', existingW9.get('accountNumbers').join(','))
          .set('state'), existingW9.getIn(['state', 'code']));
    case GET_W9_FAIL:
      return state
        .remove('gettingW9')
        .set('getW9Success', false)
        .set('w9Exists', false);
    /**
     * Update existing w9
     */
    case UPDATE_W9:
      return state
        .set('updatingW9', true)
        .set('updateW9Success', false);
    case UPDATE_W9_SUCCESS:
      return state
        .remove('updatingW9')
        .set('updateW9Success', true);
    case UPDATE_W9_FAIL:
      return state
        .remove('updatingW9')
        .set('updateW9Success', false);
    /**
     * Set a prop
     */
    case SET_PROP:
      return state
        .setIn(action.name, action.value);
    default:
      return state;
  }
}

/**
 * Change appraiser firm signup form
 */
export function formChange(props) {
  return {
    type: FORM_CHANGE,
    ...props
  };
}

/**
 * Remove from form
 * @returns {{type: string}}
 */
export function removeFromForm(name) {
  return {
    type: REMOVE_ITEM,
    name
  };
}

/**
 * Set a dropdown to a default field
 */
export function setDefault(name, value) {
  return {
    type: SET_DEFAULT,
    name,
    value
  };
}

/**
 * Modify the w9 before submit
 * @param form
 */
function modifyBeforeSubmit(form) {
  // Convert account numbers to an array
  if (form.accountNumbers) {
    form.accountNumbers = form.accountNumbers.split(',')
      .map(account => account.trim()).filter(account => account);
  }
  // Remove empty string properties
  for (const prop in form) {
    if (form.hasOwnProperty(prop)) {
      if (typeof form[prop] === 'string' && !form[prop].trim().length) {
        delete form[prop];
      }
    }
  }
}

/**
 * Submit w9 form
 */
export function submit(appraiserId, form) {
  // Modify so the backend accepts the w9
  modifyBeforeSubmit(form);
  return {
    types: [SUBMIT, SUBMIT_SUCCESS, SUBMIT_FAIL],
    promise: client => client.post(`dev:/appraisers/${appraiserId}/w9`, {
      data: {
        ...form
      }
    })
  };
}

/**
 * Retrieve an existing w9
 * @param appraiserId
 */
export function getW9(appraiserId) {
  return {
    types: [GET_W9, GET_W9_SUCCESS, GET_W9_FAIL],
    promise: client => client.get(`dev:/appraisers/${appraiserId}/w9`)
  };
}

/**
 * Submit from profile page
 * @param appraiserId
 * @param form W9 form
 * @param update Whether to update or create new
 * @param signUp Submitting during sign up
 */
export function updateW9(appraiserId, form, update, signUp) {
  const method = update ? 'patch' : 'post';
  const types = signUp ? [SUBMIT, SUBMIT_SUCCESS, SUBMIT_FAIL] : [UPDATE_W9, UPDATE_W9_SUCCESS, UPDATE_W9_FAIL];
  // Modify so the backend accepts the w9
  modifyBeforeSubmit(form);
  return {
    types,
    promise: client => client[method](`dev:/appraisers/${appraiserId}/w9`, {
      data: form
    }),
    signUp
  };
}

/**
 * Set a property explicitly
 * @param value
 * @param name
 */
export function setProp(value, ...name) {
  return {
    type: SET_PROP,
    value,
    name
  };
}
