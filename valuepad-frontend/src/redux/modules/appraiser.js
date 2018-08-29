// Basic form update
const SIGN_UP_FORM_CHANGE = 'vp/appraiser/SIGN_UP_FORM_CHANGE';
// ASC search results
const SEARCH_APPRAISER_ASC = 'vp/appraiser/SEARCH_APPRAISER_ASC';
const SEARCH_APPRAISER_ASC_SUCCESS = 'vp/appraiser/SEARCH_APPRAISER_ASC_SUCCESS';
const SEARCH_APPRAISER_ASC_FAIL = 'vp/appraiser/SEARCH_APPRAISER_ASC_FAIL';
const DISPLAY_ASC_RESULTS = 'vp/appraiser/DISPLAY_ASC_RESULTS';
// Appraiser is selected from ASC
const SET_FOUND_ASC_APPRAISER = 'vp/appraiser/SET_FOUND_ASC_APPRAISER';
// Sign up
const SIGN_UP = 'vp/appraiser/SIGN_UP';
const SIGN_UP_SUCCESS = 'vp/appraiser/SIGN_UP_SUCCESS';
const SIGN_UP_FAIL = 'vp/appraiser/SIGN_UP_FAIL';
// Return to first step
const BACK_TO_STEP_ONE = 'vp/appraiser/BACK_TO_STEP_ONE';
// File upload
const FILE_UPLOAD = 'vp/appraiser/FILE_UPLOAD';
const FILE_UPLOAD_SUCCESS = 'vp/appraiser/FILE_UPLOAD_SUCCESS';
const FILE_UPLOAD_FAIL = 'vp/appraiser/FILE_UPLOAD_FAIL';
// Get appraiser
const GET_APPRAISER = 'vp/appraiser/GET_APPRAISER';
const GET_APPRAISER_SUCCESS = 'vp/appraiser/GET_APPRAISER_SUCCESS';
const GET_APPRAISER_FAIL = 'vp/appraiser/GET_APPRAISER_FAIL';
// Update appraiser
const UPDATE_APPRAISER = 'vp/appraiser/UPDATE_APPRAISER';
const UPDATE_APPRAISER_SUCCESS = 'vp/appraiser/UPDATE_APPRAISER_SUCCESS';
const UPDATE_APPRAISER_FAIL = 'vp/appraiser/UPDATE_APPRAISER_FAIL';
// Search username
const SEARCH_USERNAME = 'vp/appraiser/SEARCH_USERNAME';
const SEARCH_USERNAME_SUCCESS = 'vp/appraiser/SEARCH_USERNAME_SUCCESS';
const SEARCH_USERNAME_FAIL = 'vp/appraiser/SEARCH_USERNAME_FAIL';
// Get languages
const GET_LANGUAGES = 'vp/appraiser/GET_LANGUAGES';
const GET_LANGUAGES_SUCCESS = 'vp/appraiser/GET_LANGUAGES_SUCCESS';
const GET_LANGUAGES_FAIL = 'vp/appraiser/GET_LANGUAGES_FAIL';
// Set default values
const SET_DEFAULT = 'vp/appraiser/SET_DEFAULT';
// Set a property
const SET_PROP = 'vp/appraiser/SET_PROP';
// Remove a property
const REMOVE_PROP = 'vp/appraiser/REMOVE_PROP';

import moment from 'moment';
import Immutable from 'immutable';

/**
 * Validation
 */
import {
  email as valEmail,
  zipFullMessage as valZip,
  pattern as valPattern,
  presence as valPresence,
  excludeValue as valExclude,
  yearRange as valYearRange,
  dateRange as valDateRange,
  numRange as valNumRange,
  length as valLength,
  backendErrorsImmutable,
  profileErrors
} from '../../helpers/validation';

import {fileUpload, setProp as setPropInherited} from 'helpers/genericFunctions';

// Sign up form fields
export const signUpFields = [
  // User object field
  'firstName', 'lastName', 'username', 'password', 'confirm', 'email',
  // Appraiser validation
  'languages', 'companyName', 'address1', 'address2', 'city', 'state', 'zip', 'assignmentAddress1',
  'assignmentAddress2', 'assignmentCity', 'assignmentState', 'assignmentZip', 'cell', 'licensedSince',
  'licenseExpiresAt', 'claimAmount', 'aggregateAmount', 'aggregateAmount', 'carrier', 'deductible', 'certification', 'fax', 'phone',
  'taxIdentificationNumber', 'yearsLicensed', 'businessTypes'
];

export function createConstraints(signUpFields) {
  // Validation constraints
  const constraints = {};
  /**
   * Validate fields, create initial state
   */
  signUpFields.forEach(field => {
    constraints[field] = {};
    // Required
    switch (field) {
      case 'address1':
        valPresence(constraints, field, 'Address is required');
        break;
      case 'firstName':
        valPresence(constraints, field, 'First name is required');
        valPattern(constraints, field, /[a-zA-Z]+/, 'First name can only contain letters');
        break;
      case 'lastName':
        valPresence(constraints, field, 'Last name is required');
        valPattern(constraints, field, /[a-zA-Z]+/, 'Last name can only contain letters');
        break;
      case 'username':
        valPresence(constraints, field, 'Username is required');
        valPattern(constraints, field, /[a-zA-Z0-9@._\-]{5,50}/,
          'Username can only contain letters, digits, @, ., _, - and must be between 5 and 50 characters');
        break;
      case 'password':
        valPresence(constraints, field, 'Password is required');
        valPattern(constraints, field, /^[0-9a-zA-Z `~!@#\$%\^&\*\(\)_\+=-\?/\|\.,'"<>\{\}\[\]:;]{5,255}$/,
          'Password must be at least 5 characters');
        break;
      case 'confirm':
        valPresence(constraints, field, 'Confirm password is required');
        break;
      case 'email':
        valEmail(constraints, field, 'Email is not valid');
        valPresence(constraints, field, 'Email is required');
        break;
      case 'languages':
        valPresence(constraints, field, 'Languages are required');
        break;
      case 'companyName':
        valPresence(constraints, field, 'Company name is required');
        break;
      case 'city':
        valPresence(constraints, field, 'City is required');
        break;
      case 'state':
        valPresence(constraints, field, 'State is required');
        break;
      case 'zip':
        valPresence(constraints, field, 'Zip is required');
        valZip(constraints, field, 'Zip code is not a valid five digit zip code');
        break;
      case 'assignmentAddress1':
        valPresence(constraints, field, 'Assignment address is required');
        break;
      case 'assignmentCity':
        valPresence(constraints, field, 'Assignment city is required');
        break;
      case 'assignmentState':
        valPresence(constraints, field, 'Assignment state is required');
        break;
      case 'assignmentZip':
        valPresence(constraints, field, 'Assignment zip is required');
        valZip(constraints, field, 'Assignment zip code is not a valid five digit zip code');
        break;
      case 'cell':
        valPattern(constraints, field, /\(\d{3}\)\s\d{3}-\d{4}/, 'Cell must be in the following format: (xxx) xxx-xxxx');
        valPresence(constraints, field, 'Cell phone is required');
        break;
      case 'licensedSince':
        valPresence(constraints, field, 'Licensed since is required');
        valYearRange(constraints, field, 100, 0);
        break;
      case 'licenseExpiresAt':
        valPresence(constraints, field, 'License expires at is required');
        valExclude(constraints, field, {'Invalid date': 'Invalid date'}, '^Invalid date');
        valDateRange(constraints, field, 0);
        break;
      case 'claimAmount':
        valPresence(constraints, field, 'E&O claim amount is required');
        valNumRange(constraints, field, {greaterThan: 0, intOnly: false}, 'E&O claim amount must be a number greater than 0');
        break;
      case 'certification':
        valPresence(constraints, field, 'Certification is required');
        break;
      case 'fax':
        valPattern(constraints, field, /\(\d{3}\)\s\d{3}-\d{4}/, 'Fax must be in the following format: (xxx) xxx-xxxx');
        break;
      case 'phone':
        valPresence(constraints, field, 'Phone is required');
        valPattern(constraints, field, /\(\d{3}\)\s\d{3}-\d{4}/, 'Phone number must be in the following format: (xxx) xxx-xxxx');
        break;
      case 'taxIdentificationNumber':
        valPresence(constraints, field, 'Taxpayer Identification Number is required');
        valPattern(constraints, field, /^(\d{3}-\d{2}-\d{4}|\d{2}-\d{7})$/,
          'Taxpayer Identification Number must follow one of these patterns: xxx-xx-xxxx or xx-xxxxxxx');
        break;
      case 'businessTypes':
        valPresence(constraints, field, 'Business type is required');
        valLength(constraints, field, 'One or more business type must be selected');
        break;
      /**
       * E&O
       */
      case 'aggregateAmount':
        valPresence(constraints, field, 'E&O aggregate amount is required');
        valNumRange(constraints, field, {greaterThan: 0, intOnly: false}, 'E&O aggregate amount must be a number greater than 0');
        break;
      case 'expiresAt':
        valPresence(constraints, field, 'E&O expires at is required');
        valExclude(constraints, field, {'Invalid date': 'Invalid date'}, '^Invalid date');
        valDateRange(constraints, field, 0);
        break;
      case 'carrier':
        valPresence(constraints, field, 'E&O carrier is required');
        valPattern(constraints, field, /[0-9a-zA-Z\s]+/, 'E&O carrier must be alphanumeric');
        break;
      case 'deductible':
        valPresence(constraints, field, 'E&O deductible amount is required');
        valNumRange(constraints, field, {greaterThan: 0, intOnly: false}, 'E&O deductible must be a number greater than 0');
        break;
      /**
       * Qualifications
       */
      case 'yearsLicensed':
        valPresence(constraints, field, 'Years licensed is required');
    }
  });
}

const constraints = createConstraints(signUpFields);

// Initial state
const initialState = Immutable.fromJS({
  signUpForm: {
    licenseNumber: '',
    licenseState: '',
    eo: {
      question1: false,
      question2: false,
      question3: false,
      question4: false,
      question5: false,
      question6: false,
      question7: false
    },
    qualifications: {
      commercialQualified: false,
      isNewConstructionCourseCompleted: false,
      isFamiliarWithFullScopeInNewConstruction: false,
      primaryLicense: {
        certifications: ['']
      }
    },
    agreeTerms: false,
    state: '',
    businessTypes: ['not-applicable']
  },
  signUpFormErrors: {},
  ascResults: [],
  openUpdateDialog: false,
  openUpdateFailDialog: false,
  availableLanguages: []
});

/**
 * Form existing appraiser for profile
 * @param result
 * @return {any}
 */
export function formatAppraiser(result) {
  let appraiser = Immutable.fromJS(result);
  // State
  appraiser = appraiser.set('state', appraiser.getIn(['state', 'code']));
  // Assignment state
  appraiser = appraiser.set('assignmentState', appraiser.getIn(['assignmentState', 'code']));
  // Languages
  appraiser = appraiser.set('languages',
    appraiser.get('languages').toList().map(language => language.get('code')));
  // Primary license expiration
  appraiser = appraiser.set('licenseExpiresAt', appraiser.getIn(['primaryLicense', 'expiresAt']));
  // Remove license number and state
  appraiser = appraiser.removeIn(['primaryLicense', 'state']);
  appraiser = appraiser.removeIn(['primaryLicense', 'number']);
  // Remove username
  appraiser = appraiser.remove('username');
  // Certification
  appraiser = appraiser.set('certification', appraiser.getIn(['primaryLicense', 'certification']));
  // Sample Reports
  if (appraiser.get('sampleReports')) {
    appraiser.get('sampleReports').map((report, i) => {
      appraiser = appraiser.set(`sampleReport${i + 1}`, report);
    });
  }
  return appraiser;
}

/**
 * Update appraiser profile values
 * @param action
 * @param state
 * @param dictKey Key of appraiser values in state
 */
export function updateAppraiserValues(action, state, dictKey) {
  let signUpForm;
  if (action.name === 'va' || action.name === 'certifiedRelocationProfessional') {
    signUpForm = state
      .get(dictKey)
      .set(action.name, !state.getIn([dictKey, action.name]));
    // Append, for multiselect
  } else if (action.append) {
    const appendName = Array.isArray(action.name) ? action.name : [action.name];
    const arrayBefore = state.getIn([dictKey, ...appendName]) || Immutable.List();
    const indexOfItem = arrayBefore.indexOf(action.value);
    let arrayAfter;
    // Remove from array
    if (indexOfItem !== -1) {
      arrayAfter = arrayBefore.splice(indexOfItem, 1);
      // Add to array
    } else {
      arrayAfter = arrayBefore.push(action.value);
    }
    signUpForm = state.get(dictKey).setIn([...appendName], arrayAfter);
  } else {
    // Sign up form
    signUpForm = state.get(dictKey).set(action.name, action.value);
  }
  return signUpForm;
}

export default function reducer(state = initialState, action = {}) {
  switch (action.type) {
    // Change sign up form
    case SIGN_UP_FORM_CHANGE:
      const formValues = updateAppraiserValues(action, state, 'signUpForm');
      return state.set('signUpForm', formValues)
        .set('signUpFormErrors',
          profileErrors(state, action, 'signUpFormErrors', formValues, constraints));
    /**
     * Search appraiser ASC methods
     */
    case SEARCH_APPRAISER_ASC:
      return state.set('searchingAsc', true);
    case SEARCH_APPRAISER_ASC_SUCCESS:
      return state.set('searchingAsc', false);
    case SEARCH_APPRAISER_ASC_FAIL:
      return state.set('searchingAsc', false);
    // Display results from ASC search
    case DISPLAY_ASC_RESULTS:
      return state.set('ascResults', Immutable.fromJS(action.results));
    // Select found ASC appraiser for sign up
    case SET_FOUND_ASC_APPRAISER:
      // Remove state objects, replace with code
      let appraiser = action.appraiser;
      appraiser = appraiser
        .set('licenseExpiresAt', moment(appraiser.get('licenseExpiresAt')).format())
        .setIn(['qualifications', 'primaryLicense'], Immutable.fromJS({
          number: appraiser.get('licenseNumber'),
          state: appraiser.getIn(['licenseState', 'code']),
          expiresAt: appraiser.get('licenseExpiresAt'),
          certifications: appraiser.get('certifications')
        }))
        .set('address1', appraiser.get('address'))
        .set('licenseState', appraiser.getIn(['licenseState', 'code']))
        .set('state', appraiser.getIn(['state', 'code']));
      const formAfterAsc = state.get('signUpForm').mergeDeep(appraiser);
      return state
        .set('signUpForm', formAfterAsc)
        .set('ascResults', Immutable.List())
        .set('ascSelected', true);
    /**
     * Return to step 1
     */
    case BACK_TO_STEP_ONE:
      return state.set('ascSelected', false);
    /**
     * File upload methods
     */
    case FILE_UPLOAD:
      return state.set('fileUpload', Immutable.fromJS({
        uploading: true,
        file: action.docType
      }));
    case FILE_UPLOAD_SUCCESS:
      const result = Object.assign(action.result, {preview: action.document.preview});
      // File name of uploaded file
      const fileName = state.getIn(['fileUpload', 'file']);
      // Existing file
      const existingFile = state.getIn(['signUpForm', fileName]);
      let fileUploadForm;
      // Allow sample reports to be an array of files
      if (existingFile && Immutable.List.isList(existingFile) && fileName === 'sampleReports') {
        // Push more into this array
        fileUploadForm = state
          .setIn(['signUpForm', 'sampleReports'], existingFile.concat(Immutable.fromJS([result])))
          .get('signUpForm');
        // First upload of sample report
      } else if (fileName === 'sampleReports') {
        fileUploadForm = state.get('signUpForm').merge({
          [fileName]: [Immutable.fromJS(result)]
        });
        // Single document of any other kind
      } else {
        // Retrieve path of uploaded doc
        const docPath = state.getIn(['fileUpload', 'file']);
        fileUploadForm = state.get('signUpForm').setIn(Immutable.List.isList(docPath) ? docPath : [docPath], Immutable.fromJS(result));
      }
      return state.mergeDeep({signUpForm: fileUploadForm, fileUpload: {uploading: false, success: true}});
    case FILE_UPLOAD_FAIL:
      let errorMessage = '';
      if (action.error && action.error.errors && action.error.errors.document && action.error.errors.document.message) {
        errorMessage = action.error.errors.document.message;
      }
      return state
        .setIn(['fileUpload', 'success'], false)
        .setIn(['fileUpload', 'uploading'], false)
        .setIn(['fileUpload', 'error'], errorMessage);
    /**
     * Sign up firm methods
     */
    case SIGN_UP:
      return state.set('signingUp', true);
    case SIGN_UP_SUCCESS:
      return state.set('signingUp', false);
    case SIGN_UP_FAIL:
      return state
        .set('signUpFormErrors', backendErrorsImmutable(action))
        .set('signingUp', false);
    /**
     * Get an appraiser
     */
    case GET_APPRAISER:
      return state
        .set('gettingAppraiser', true)
        .remove('getAppraiserSuccess');
    case GET_APPRAISER_SUCCESS:
      return state
        .set('signUpForm', formatAppraiser(action.result))
        .remove('gettingAppraiser')
        .set('getAppraiserSuccess', true);
    case GET_APPRAISER_FAIL:
      return state
        .remove('gettingAppraiser')
        .set('getAppraiserSuccess', false);
    /**
     * Update appraiser
     */
    case UPDATE_APPRAISER:
      return state
        .remove('updateAppraiserSuccess')
        .set('updatingAppraiser', true);
    case UPDATE_APPRAISER_SUCCESS:
      return state
        .set('updateAppraiserSuccess', true)
        .remove('updatingAppraiser');
    // Append backend errors to frontend, preferring frontend error strings
    case UPDATE_APPRAISER_FAIL:
      let signUpFormErrors = state.get('signUpFormErrors');
      Immutable.fromJS(action.error.errors).filter((error, key) => {
        return !state.getIn(['signUpFormErrors', key]);
      })
        .map((error, key) => {
          signUpFormErrors = signUpFormErrors.set(key, Immutable.List().push(error.get('message')));
        });
      return state
        .set('updateAppraiserSuccess', false)
        .set('signUpFormErrors', signUpFormErrors)
        .remove('updatingAppraiser');
    /**
     * Search username
     */
    case SEARCH_USERNAME:
      return state
        .remove('searchUsernameSuccess')
        .remove('usernameAvailable')
        .set('searchingUsername', true);
    // Triggers if username already is taken
    case SEARCH_USERNAME_SUCCESS:
      return state
        .set('searchUsernameSuccess', true)
        .set('usernameAvailable', false)
        .setIn(['signUpFormErrors', 'username'], Immutable.List().push('Username already taken'))
        .remove('searchingUsername');
    // This triggers if username is not found
    case SEARCH_USERNAME_FAIL:
      return state
        .set('searchUsernameSuccess', true)
        .set('usernameAvailable', true)
        .remove('searchingUsername');
    /**
     * Get languages
     */
    case GET_LANGUAGES:
      return state
        .remove('getLanguagesSuccess')
        .set('gettingLanguages', true);
    case GET_LANGUAGES_SUCCESS:
      return state
        .set('getLanguagesSuccess', true)
        .set('availableLanguages', Immutable.fromJS(action.result.data))
        .remove('gettingLanguages');
    case GET_LANGUAGES_FAIL:
      return state
        .set('getLanguagesSuccess', true)
        .remove('gettingLanguages');
    /**
     * Set a default for a dropdown
     */
    case SET_DEFAULT:
      return state.setIn(action.namePath, action.value);
    /**
     * Set a property correctly
     */
    case SET_PROP:
      // Set value first for error state
      const setPropState = state.setIn(action.name, action.value);
      return setPropState
        .set('signUpFormErrors',
          profileErrors(setPropState, action, 'signUpFormErrors', setPropState.get('signUpForm'), constraints));
    /**
     * Remove a property
     */
    case REMOVE_PROP:
      return state.removeIn(action.namePath);
    default:
      return state;
  }
}

/**
 * Change appraiser firm signup form
 */
export function formChange(event) {
  const {target: {name, value}} = event;
  return {
    type: SIGN_UP_FORM_CHANGE,
    name,
    value
  };
}

/**
 * Update a property value directly (not from event)
 * @param name Prop name
 * @param value Prop value
 */
export function updateValue(name, value) {
  const append = arguments[0] === 'append';
  // Append multiselect
  if (append) {
    name = arguments[1];
    value = arguments[2];
  }
  return {
    type: SIGN_UP_FORM_CHANGE,
    name,
    value,
    append
  };
}

/**
 * Search appraiser ASC for appraiser sign up
 */
export function searchAppraiserAsc(params) {
  return {
    types: [SEARCH_APPRAISER_ASC, SEARCH_APPRAISER_ASC_SUCCESS, SEARCH_APPRAISER_ASC_FAIL],
    promise: client => client.get(
      `dev:/asc?search[licenseNumber]=${params.licenseNumber}&filter[licenseState]=${params.licenseState}&filter[isTied]=false`)
  };
}

/**
 * Display ASC results
 */
export function showAscResults(results) {
  return {
    type: DISPLAY_ASC_RESULTS,
    results
  };
}

/**
 * Set found appraiser into state
 */
export function setFoundAppraiser(appraiser) {
  return {
    type: SET_FOUND_ASC_APPRAISER,
    appraiser
  };
}

/**
 * Move back to step one, remove ASC selected
 */
export function backToStepOne() {
  return {
    type: BACK_TO_STEP_ONE
  };
}

/**
 * Upload file during appraiser sign up
 */
export function uploadFile(docType, document) {
  return fileUpload([FILE_UPLOAD, FILE_UPLOAD_SUCCESS, FILE_UPLOAD_FAIL], docType, document);
}

/**
 * Create appraiser
 */
export function createAppraiser(data) {
  return {
    types: [SIGN_UP, SIGN_UP_SUCCESS, SIGN_UP_FAIL],
    promise: client => client.post('dev:/appraisers', {
      data
    })
  };
}

/**
 * Set a default for a dropdown
 */
export function setDefault(value, ...namePath) {
  return {
    type: SET_DEFAULT,
    namePath,
    value
  };
}

/**
 * Retrieve an appraiser
 */
export function getAppraiser(appraiserId) {
  return {
    types: [GET_APPRAISER, GET_APPRAISER_SUCCESS, GET_APPRAISER_FAIL],
    promise: client => client.get(`dev:/appraisers/${appraiserId}`, {
      data: {
        headers: {
          Include: 'companyName,businessTypes,companyType,taxIdentificationNumber,w9,company,languages,address1,address2,city,state,zip,assignmentAddress1,assignmentAddress2,assignmentState,assignmentCity,assignmentZip,phone,cell,fax,qualifications,eo,sampleReports'
        }
      }
    })
  };
}

/**
 * Update appraiser
 * @param appraiserId Appraiser ID
 * @param appraiser Appraiser record
 */
export function updateAppraiser(appraiserId, appraiser) {
  return {
    types: [UPDATE_APPRAISER, UPDATE_APPRAISER_SUCCESS, UPDATE_APPRAISER_FAIL],
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
 * Set a property explicitly
 * @param value Value to set it to
 * @param name Name arguments forming array for setIn
 */
export function setProp(value, ...name) {
  return setPropInherited(SET_PROP, value, ...name);
}

/**
 * Remove a property
 * @param namePath
 * @returns {{type: *, namePath: *}}
 */
export function removeProp(...namePath) {
  return {
    promise: () => new Promise(resolve => resolve()),
    type: REMOVE_PROP,
    namePath
  };
}

/**
 * Determine if username is available
 * @param username
 */
export function searchUsername(username) {
  return {
    types: [SEARCH_USERNAME, SEARCH_USERNAME_SUCCESS, SEARCH_USERNAME_FAIL],
    promise: client => client.get(`dev:/users/${username}`)
  };
}

/**
 * Retrieve available languages
 */
export function getLanguages() {
  return {
    types: [GET_LANGUAGES, GET_LANGUAGES_SUCCESS, GET_LANGUAGES_FAIL],
    promise: client => client.get(`dev:/languages`)
  };
}
