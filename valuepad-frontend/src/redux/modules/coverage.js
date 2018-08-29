const FORM_CHANGE = 'vp/coverage/FORM_CHANGE';
const SET_PROP = 'vp/coverage/SET_PROP';
const SUBMIT = 'vp/coverage/SUBMIT';
const SUBMIT_SUCCESS = 'vp/coverage/SUBMIT_SUCCESS';
const SUBMIT_FAIL = 'vp/coverage/SUBMIT_FAIL';
// Get coverage
const GET_LICENSES = 'vp/coverage/GET_LICENSES';
const GET_LICENSES_SUCCESS = 'vp/coverage/GET_LICENSES_SUCCESS';
const GET_LICENSES_FAIL = 'vp/coverage/GET_LICENSES_FAIL';
// Get counties for a state
const GET_COUNTIES = 'vp/coverage/GET_COUNTIES';
const GET_COUNTIES_SUCCESS = 'vp/coverage/GET_COUNTIES_SUCCESS';
const GET_COUNTIES_FAIL = 'vp/coverage/GET_COUNTIES_FAIL';
// License doc upload
const FILE_UPLOAD = 'vp/coverage/FILE_UPLOAD';
const FILE_UPLOAD_SUCCESS = 'vp/coverage/FILE_UPLOAD_SUCCESS';
const FILE_UPLOAD_FAIL = 'vp/coverage/FILE_UPLOAD_FAIL';
// Add/remove counties
const SELECT_COUNTY = 'vp/coverage/SELECT_COUNTY';
// Add/remove zips
const SELECT_ZIP = 'vp/coverage/SELECT_ZIP';
// ASC search results
const SEARCH_APPRAISER_ASC = 'vp/coverage/SEARCH_APPRAISER_ASC';
const SEARCH_APPRAISER_ASC_SUCCESS = 'vp/coverage/SEARCH_APPRAISER_ASC_SUCCESS';
const SEARCH_APPRAISER_ASC_FAIL = 'vp/coverage/SEARCH_APPRAISER_ASC_FAIL';
// set license primary
const SET_LICENSE_PRIMARY = 'vp/coverage/SET_LICENSE_PRIMARY';
const SET_LICENSE_PRIMARY_SUCCESS = 'vp/coverage/SET_LICENSE_PRIMARY_SUCCESS';
const SET_LICENSE_PRIMARY_FAIL = 'vp/coverage/SET_LICENSE_PRIMARY_FAIL';
// Select license from search results
const SELECT_ASC = 'vp/coverage/SELECT_ASC';
// Clear form, to allow another ASC search
const CLEAR_FORM = 'vp/coverage/CLEAR_FORM';
// Show selected counties
const SHOW_SELECTED_COUNTIES = 'vp/coverage/SHOW_SELECTED_COUNTIES';
const HIDE_SELECTED_COUNTIES = 'vp/coverage/HIDE_SELECTED_COUNTIES';
// Delete coverage
const DELETE_COVERAGE = 'vp/coverage/DELETE_COVERAGE';
const DELETE_COVERAGE_SUCCESS = 'vp/coverage/DELETE_COVERAGE_SUCCESS';
const DELETE_COVERAGE_FAIL = 'vp/coverage/DELETE_COVERAGE_FAIL';
const PREPARE_DELETE_COVERAGE = 'vp/coverage/PREPARE_DELETE_COVERAGE';
const CLOSE_DELETE_MODAL = 'vp/coverage/CLOSE_DELETE_MODAL';
// Select an existing coverage form
const SELECT_EXISTING_FORM = 'vp/coverage/SELECT_EXISTING_FORM';

// US states
import states from '../../components/States/statesList';
/**
 * Validation
 */
import {
  presence as valPresence,
  excludeValue as valExclude,
  dateRange as valDateRange,
  zipFullMessage as valZip,
  frontendErrorsImmutable,
  backendErrorsImmutable
} from 'helpers/validation';

import {fileUpload} from 'helpers/genericFunctions';
// Error
import {GeneralError} from 'components';

import Immutable from 'immutable';

// Sign up form fields
const fields = [
  // Fields
  'number', 'state', 'expiresAt', 'certification', 'isFhaApproved', 'isCommercial', 'document', 'coverage',
  'dbaCompanyName', 'dbaAddress1', 'dbaCity', 'dbaState', 'dbaZip'
];

export const formInterface = {
  number: '',
  state: '',
  certifications: {},
  isFhaApproved: false,
  isCommercial: false,
  document: null,
  coverage: [],
  alias: {
    companyName: '',
    email: '',
    address1: '',
    address2: '',
    state: 'AL',
    city: '',
    zip: ''
  }
};

// Validation constraints
const constraints = {};
// Initial state
const initialState = Immutable.fromJS({
  form: formInterface,
  licenses: [],
  states: {},
  initialLicenseComplete: true,
  showSelectedCounties: [],
  ascResults: [],
  // Selected county and zip list
  countyList: [],
  zipList: [],
  formErrors: {},
  // Temp workaround to separate alias error messages from the license error messages
  aliasErrors: {},
  isUsingAlias: false,
  // Next available state
  nextAvailableState: 'AL'
});

/**
 * Validate fields
 */
fields.forEach(field => {
  constraints[field] = {};
  // Don't validate optional fields
  if (['isFhaApproved', 'isCommercial'].indexOf(field) === -1) {
    valPresence(constraints, field);
  }
  // License expiration, E&O expires at
  if (['expiresAt'].indexOf(field) !== -1) {
    valExclude(constraints, field, {'Invalid date': 'Invalid date'}, '^Invalid date');
    valDateRange(constraints, field, 0);
  }
  if (field === 'dbaZip') {
    valZip(constraints, field, 'Zip code is not a valid five digit zip code');
  }
});

/**
 * Retrieve form either when editing or when adding new coverage area
 * @param state
 * @returns {*|*}
 */
export function getForm(state) {
  // Editing, return new form
  if (!state.get('editing')) {
    return state.get('form');
  }
  // Return selected license
  return state.get('licenses').filter(license => license.get('id') === state.get('licenseSelected')).get(0);
}

/**
 * The index of the selected license
 * @param state
 */
export function getIndexOfSelectedLicense(state) {
  let licenseIndex = null;
  // Find index
  state.get('licenses').forEach((license, index) => {
    if (license.get('id') === state.get('licenseSelected')) {
      licenseIndex = index;
      return false;
    }
  });
  return licenseIndex;
}

/**
 * Set the current form back into state
 * @param state
 * @param form
 */
function setForm(state, form) {
  if (!state.get('editing')) {
    return state.set('form', form);
  }
  // Get index of selected license
  const licenseIndex = getIndexOfSelectedLicense(state);
  // Set selected license
  if (typeof licenseIndex !== 'undefined') {
    return state.setIn(['licenses', licenseIndex], form);
  }
  // Could not find license
  throw new GeneralError('Could not find selected license');
}

/**
 * Determine next available state for a license
 * @param state Current app state
 * @param licenses Existing licenses
 */
function getNextAvailableState(state, licenses) {
  const usedStates = licenses.map(license => license.getIn(['state', 'code']));
  states.forEach(thisState => {
    const stateVal = thisState.get('value');
    if (!usedStates.contains(stateVal)) {
      state = state.set('nextAvailableState', stateVal);
      return false;
    }
  });
  return state;
}

export default function reducer(state = initialState, action = {}) {
  switch (action.type) {
    // Change form
    case FORM_CHANGE:
      const form = state.get('form').set(action.name, action.value);
      return state
        .set('form', form);
    // Set a dropdown to a default value
    case SET_PROP:
      const updatedState = state.setIn(action.name, action.value);
      let errorKey = 'formErrors';

      if (action.name.length === 2 && action.name[1] === 'alias') {
        errorKey = 'aliasErrors';
      }

      const errors = frontendErrorsImmutable(updatedState, action, errorKey, updatedState.get('form'), constraints);
      return updatedState.set(errorKey, errors);
    /**
     * Get licenses
     */
    case GET_LICENSES:
      return state.set('gettingCoverage', true);
    case GET_LICENSES_SUCCESS:
      const existingLicenses = Immutable.fromJS(action.result.data);
      // Get next available state for licenses to be added
      state = getNextAvailableState(state, existingLicenses);
      return state
        .remove('gettingCoverage')
        .set('licenses', existingLicenses);
    case GET_LICENSES_FAIL:
      return state
        .remove('gettingCoverage');
    case SUBMIT:
      return state
        .set('submitting', true)
        .remove('submitSuccess');
    case SUBMIT_SUCCESS:
      let licenses = state.get('licenses');
      // Add coverage
      if (!state.get('editing')) {
        const thisCoverage = state
          .get('form')
          .set('state', state.getIn(['form', 'licenseState']))
          .set('expiresAt', state.getIn(['form', 'licenseExpiresAt']));
        licenses = licenses.push(thisCoverage);
      }
      // Get next available state for licenses to be added
      state = getNextAvailableState(state, licenses);
      return state
        .set('submitSuccess', true)
        // Update licenses
        .set('licenses', licenses)
        // No long submitting
        .remove('submitting')
        // No license currently selected
        .remove('licenseSelected')
        // On any successful submission, we can assume the initial license is completed
        .set('initialLicenseComplete', true)
        // Remove counties
        .set('counties', Immutable.Map())
        // Reset form
        .set('form', Immutable.Map())
        // Editing finished
        .remove('editing')
        // Default back to no ASC selected
        .remove('ascSelected');
    case SUBMIT_FAIL:
      let aliasErrors = Immutable.Map();
      let backendErrors = backendErrorsImmutable(action);

      Object.keys(action.error.errors).forEach(errorKey => {
        if (errorKey.indexOf('alias.') === 0) {
          aliasErrors = aliasErrors.set(
            errorKey.substring('alias.'.length),
            backendErrors.get(errorKey)
          );

          backendErrors = backendErrors.delete(errorKey);
        }
      });

      return state
        .set('submitSuccess', false)
        .set('errors', backendErrors)
        .set('aliasErrors', aliasErrors)
        .remove('submitting');
    /**
     * Get counties
     */
    case GET_COUNTIES:
      return state
        .remove('getCountiesSuccess')
        .set('gettingCounties', true);
    case GET_COUNTIES_SUCCESS:
      const fetchCounties = Immutable.fromJS(action.result.data).sort((a, b) => {
        if (a.get('title') === b.get('title')) return 0;
        if (a.get('title') < b.get('title')) return -1;

        return 1;
      });

      const thirds = fetchCounties.count() / 3;
      const leftOver = fetchCounties.count() % 3;
      let displayCounties = Immutable.List();
      if (leftOver === 0) {
        displayCounties = Immutable.List([
          fetchCounties.slice(0, thirds),
          fetchCounties.slice(thirds, -thirds),
          fetchCounties.slice(-thirds)
        ]);
      } else {
        displayCounties = Immutable.List([
          fetchCounties.slice(0, thirds + 1),
          fetchCounties.slice(thirds + 1, -(thirds - 1)),
          fetchCounties.slice(-thirds + 1),
        ]);
      }

      return state
        .set('gettingCounties', false)
        .set('getCountiesSuccess', true)
        .setIn(['states', action.stateCode], fetchCounties)
        .setIn(['statesCountiesSorted', action.stateCode], displayCounties);
    // @todo
    case GET_COUNTIES_FAIL:
      return state
        .set('getCountiesSuccess', false)
        .set('gettingCounties', false);
    /**
     * License doc upload
     */
    case FILE_UPLOAD:
      return state
        .remove('fileUploadSuccess')
        .set('uploading', true);
    case FILE_UPLOAD_SUCCESS:
      const {id, token, name, url} = action.result;
      const {document} = action;
      // Record of uploaded document
      const documentRecord = Immutable.fromJS({
        id,
        token,
        name,
        size: document.size,
        preview: document.preview,
        type: document.type,
        url
      });
      // Store upload in state
      return state
        .remove('uploading')
        .set('fileUploadSuccess', true)
        .setIn(['form', 'document'], documentRecord);
    case FILE_UPLOAD_FAIL:
      return state
        .set('fileUploadSuccess', false)
        .remove('uploading');
    /**
     * Add/remove counties
     */
    case SELECT_COUNTY:
      const countyId = parseInt(action.name, 10);
      const currentForm = getForm(state);
      const thisCounty = currentForm.get('coverage')
        .filter(coverage => coverage.getIn(['county', 'id']) === countyId);
      // No value for this county currently set
      if (!thisCounty.count()) {
        if (state.get('editing')) {
          const selectedLicenseIndex = getIndexOfSelectedLicense(state);
          state = state.setIn(['licenses', selectedLicenseIndex, 'coverage'], currentForm.get('coverage')
            .push(Immutable.Map()
              .set('county', Immutable.Map().set('id', countyId))
              .set('zips', Immutable.List())));
        } else {
          state = state.setIn(['form', 'coverage'], state.getIn(['form', 'coverage'])
            .push(Immutable.Map()
              .set('county', Immutable.Map().set('id', countyId))
              .set('zips', Immutable.List())));
        }
      } else {
        // Reverse selected
        const countyIndex = currentForm
          .get('coverage')
          .findIndex(coverage => coverage.getIn(['county', 'id']) === countyId);
        // Found index
        if (countyIndex !== -1) {
          const splicedCoverage = currentForm
            .get('coverage')
            .splice(countyIndex, 1);
          state = setForm(state, currentForm.set('coverage', splicedCoverage));
        } else {
          throw new GeneralError('Could not find county to remove from coverage');
        }
      }
      return state;
    /**
     * Add/remove zips
     */
    case SELECT_ZIP:
      // Parse zip
      const zip = action.name;
      const zipForm = getForm(state);
      // Get target county
      const county = zipForm
        .get('coverage')
        .filter(coverage => coverage.getIn(['county', 'id']) === action.county)
        .get(0);
      const existingZips = county.get('zips');
      const indexOfZip = existingZips.indexOf(zip);
      // Push or splice
      const updatedCounty = county.set('zips',
        indexOfZip === -1 ? existingZips.push(zip) : existingZips.splice(indexOfZip, 1));
      // Create updated counties
      const updatedCounties = zipForm
        .get('coverage')
        .map(coverage =>
          coverage.getIn(['county', 'id']) === updatedCounty.getIn(['county', 'id']) ? updatedCounty : coverage);
      // Recreate form
      const updatedZipForm = zipForm
        .set('coverage', zipForm.get('coverage').map(coverage => {
          if (coverage.getIn(['county', 'id']) === action.county) {
            return updatedCounties.filter(coverage => coverage.getIn(['county', 'id']) === action.county)
              .get(0);
          }
          return coverage;
        }));
      return setForm(state, updatedZipForm);
    /**
     * Search ASC for license
     */
    case SEARCH_APPRAISER_ASC:
      return state.set('searchingAsc', true);
    case SEARCH_APPRAISER_ASC_SUCCESS:
      return state
        .set('searchingAsc', false)
        .set('ascResults', Immutable.fromJS(action.result.data));
    case SEARCH_APPRAISER_ASC_FAIL:
      return state.set('searchingAsc', false);
    case SET_LICENSE_PRIMARY:
      return state.set('settingLicensePrimary', true);
    case SET_LICENSE_PRIMARY_SUCCESS:
      return state.set('settingLicensePrimary', false);
    case SET_LICENSE_PRIMARY_FAIL:
      return state.set('settingLicensePrimary', false);
    /**
     * Select license from ASC
     */
    case SELECT_ASC:
      const appraiser = action.appraiser;
      let selectedAscForm = Immutable.fromJS({
        number: appraiser.get('licenseNumber'),
        state: appraiser.getIn(['licenseState', 'code']),
        expiresAt: appraiser.get('licenseExpiresAt'),
        certifications: appraiser.get('certifications'),
        isFhaApproved: false,
        isCommercial: false,
        document: appraiser.get('document') || null,
        coverage: [],
        alias: {state: 'AL'}
      });
      if (appraiser.get('id')) {
        selectedAscForm = selectedAscForm.set('id', appraiser.get('id'));
      }
      return state
        .set('form', selectedAscForm)
        // Selected
        .set('ascSelected', true)
        // Remove remaining ASC results
        .set('ascResults', Immutable.List());
    /**
     * Clear form, to allow for another ASC search
     */
    case CLEAR_FORM:
      return state
        .set('form', Immutable.Map())
        .setIn(['form', 'state'], 'AL')
        .set('ascSelected', false)
        .set('counties', Immutable.Map())
        .set('countyList', Immutable.List())
        .set('zipList', Immutable.List());
    /**
     * Display selected counties for a license
     */
    case SHOW_SELECTED_COUNTIES:
      const counties = action.license
        .get('coverage')
        .map(coverage => {
          return coverage.getIn(['county', 'title']);
        });
      return state
        .set('showSelectedCounties', counties);
    case HIDE_SELECTED_COUNTIES:
      return state
        .set('showSelectedCounties', Immutable.List());
    /**
     * Delete coverage
     */
    case PREPARE_DELETE_COVERAGE:
      return state
        .set('showDeleteModal', true)
        .set('deleteCoverage', action.coverage.get('id'));
    case CLOSE_DELETE_MODAL:
      return state
        .set('showDeleteModal', false)
        .remove('deleteCoverage');
    case DELETE_COVERAGE:
      return state.set('deletingCoverage', true);
    case DELETE_COVERAGE_SUCCESS:
      // Get coverage to be deleted
      const coverageId = state.get('deleteCoverage');
      // Filtered license
      const licensesAfterDeletion = state.get('licenses').filter(license => {
        return license.get('id') !== coverageId;
      });
      return state
        .set('licenses', licensesAfterDeletion)
        .remove('deleteCoverage')
        .set('showDeleteModal', false);
    case DELETE_COVERAGE_FAIL:
      return state.set('deletingCoverage', false);
    /**
     * Select an existing form
     */
    case SELECT_EXISTING_FORM:
      // Selected form
      let selectedForm = state.get('licenses').filter(license => license.get('id') === action.license.get('id')).get(0);
      const isUsingAlias = selectedForm.get('alias') !== null;
      const coverage = selectedForm.get('coverage');
      let zipList = Immutable.List();
      // Get counties selected
      const countyList = coverage.map(county => {
        const zips = county.get('zips');
        // Get zips selected
        if (zips.count()) {
          zipList = zipList.concat(zips);
        }
        return county.getIn(['county', 'id']);
      });
      // Convert counties to ints
      const coverageForForm = coverage.map(county => {
        return county.set('county', county.getIn(['county', 'id']));
      });

      if (!isUsingAlias) {
        selectedForm = selectedForm.set('alias', Immutable.fromJS({state: 'AL'}));
      } else {
        selectedForm = selectedForm.setIn(['alias', 'state'], selectedForm.getIn(['alias', 'state', 'code']));
      }

      return state
        .set('editing', true)
        .set('isUsingAlias', isUsingAlias)
        .set('licenseSelected', action.license.get('id'))
        .set('form', selectedForm.set('state', selectedForm.getIn(['state', 'code'])))
        .setIn(['form', 'coverage'], coverageForForm)
        .set('countyList', countyList)
        .set('zipList', zipList);
    default:
      return state;
  }
}

/**
 * Retrieve appraiser coverage areas
 * @param user User
 * @param selectedAppraiser Appraiser selected for customer view
 */
export function getLicenses(user, selectedAppraiser) {
  let userType = user.get('type');
  let userId = user.get('id');
  // Select appraiser as customer
  if (user.get('type') === 'customer') {
    userType = 'appraiser';
    userId = selectedAppraiser;
  }
  return {
    types: [GET_LICENSES, GET_LICENSES_SUCCESS, GET_LICENSES_FAIL],
    promise: client => client.get(`dev:/${userType}s/${userId}/licenses`)
  };
}

/**
 * Set a property explicitly
 */
export function setProp(value, ...name) {
  return {
    promise: () => new Promise(resolve => resolve()),
    type: SET_PROP,
    name,
    value
  };
}

/**
 * Submit coverage add state form
 */
export function submitCoverage(user, form, editing) {
  // HTTP method
  const method = editing ? 'patch' : 'post';
  // URL
  const url = editing ?
              `dev:/${user.get('type')}s/${user.get('id')}/licenses/${form.get('id')}` :
              `dev:/${user.get('type')}s/${user.get('id')}/licenses`;
  // Submit
  return {
    types: [SUBMIT, SUBMIT_SUCCESS, SUBMIT_FAIL],
    promise: client => client[method](url, {
      data: form.toJS()
    })
  };
}

/**
 * Get counties for a state
 */
export function getCounties(stateCode) {
  return {
    types: [GET_COUNTIES, GET_COUNTIES_SUCCESS, GET_COUNTIES_FAIL],
    promise: client => client.get(`dev:/location/states/${stateCode}/counties`, {
      data: {
        headers: {
          Include: 'zips'
        }
      }
    }),
    stateCode
  };
}

/**
 * Upload license doc
 * @param docType
 * @param document
 */
export function uploadFile(docType, document) {
  return fileUpload([FILE_UPLOAD, FILE_UPLOAD_SUCCESS, FILE_UPLOAD_FAIL], docType, document);
}

/**
 * Search appraisers via ASC
 * @returns {*}
 */
export function searchAsc(params) {
  return {
    types: [SEARCH_APPRAISER_ASC, SEARCH_APPRAISER_ASC_SUCCESS, SEARCH_APPRAISER_ASC_FAIL],
    promise: client => client.get(
      `dev:/asc?search[licenseNumber]=${params.licenseNumber}&filter[licenseState]=${params.licenseState}&filter[isTied]=false`)
  };
}

/**
 * Select license from ASC
 */
export function selectAsc(appraiser) {
  return {
    type: SELECT_ASC,
    appraiser
  };
}

/**
 * Clear form, typically so user can search ASC again
 * @returns {{type: *}}
 */
export function clearForm() {
  return {
    type: CLEAR_FORM
  };
}

/**
 * Display selected counties
 */
export function showSelectedCounties(license) {
  return {
    type: SHOW_SELECTED_COUNTIES,
    license
  };
}

/**
 * Hide selected counties modal
 */
export function hideSelectedCounties() {
  return {
    type: HIDE_SELECTED_COUNTIES
  };
}

/**
 * Prepare for deleting coverage area
 * @param coverage
 */
export function prepareDelete(coverage) {
  return {
    type: PREPARE_DELETE_COVERAGE,
    coverage
  };
}

/**
 * Close delete modal
 */
export function closeDeleteModal() {
  return {
    type: CLOSE_DELETE_MODAL
  };
}

/**
 * Delete a coverage item
 */
export function deleteCoverage(user, licenseId) {
  return {
    types: [DELETE_COVERAGE, DELETE_COVERAGE_SUCCESS, DELETE_COVERAGE_FAIL],
    promise: client => client.delete(`dev:/${user.get('type')}s/${user.get('id')}/licenses/${licenseId}`)
  };
}

/**
 * Select an existing form
 */
export function setSelectedForm(license) {
  return {
    type: SELECT_EXISTING_FORM,
    license
  };
}

export function setAsPrimary(user, licenseId) {
  return {
    types: [SET_LICENSE_PRIMARY, SET_LICENSE_PRIMARY_SUCCESS, SET_LICENSE_PRIMARY_FAIL],
    promise: client => client.post(`dev:/${user.get('type')}s/${user.get('id')}/change-primary-license`, {
      data: {
        license: licenseId
      }
    })
  };
}
