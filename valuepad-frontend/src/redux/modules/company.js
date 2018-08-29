import {formatAppraiser, updateAppraiserValues, signUpFields, createConstraints} from 'redux/modules/appraiser';

// Set a property
const SET_PROP = 'vp/company/SET_PROP';
// Remove a property
const REMOVE_PROP = 'vp/company/REMOVE_PROP';
// Create company
const CREATE_COMPANY = 'vp/company/CREATE_COMPANY';
const CREATE_COMPANY_SUCCESS = 'vp/company/CREATE_COMPANY_SUCCESS';
const CREATE_COMPANY_FAIL = 'vp/company/CREATE_COMPANY_FAIL';
const CHECKING_TIN = 'vp/company/CHECKING_TIN';
const CHECKING_TIN_SUCCESS = 'vp/company/CHECKING_TIN_SUCCESS';
const CHECKING_TIN_FAIL = 'vp/company/CHECKING_TIN_FAIL';
// Upload document
const FILE_UPLOAD = 'vp/company/FILE_UPLOAD';
const FILE_UPLOAD_SUCCESS = 'vp/company/FILE_UPLOAD_SUCCESS';
const FILE_UPLOAD_FAIL = 'vp/company/FILE_UPLOAD_FAIL';
// Prefill with appraiser's info
const PREFILL_APPRAISER = 'vp/company/PREFILL_APPRAISER';
const PREFILL_APPRAISER_SUCCESS = 'vp/company/PREFILL_APPRAISER_SUCCESS';
const PREFILL_APPRAISER_FAIL = 'vp/company/PREFILL_APPRAISER_FAIL';
// Prefill appraisers ACH
const PREFILL_ACH = 'vp/company/PREFILL_ACH';
// Add manager
const ADD_MANAGER = 'vp/company/ADD_MANAGER';
const ADD_MANAGER_SUCCESS = 'vp/company/ADD_MANAGER_SUCCESS';
const ADD_MANAGER_FAIL = 'vp/company/ADD_MANAGER_FAIL';
// Get companies
const GET_STAFF = 'vp/company/GET_STAFF';
const GET_STAFF_SUCCESS = 'vp/company/GET_STAFF_SUCCESS';
const GET_STAFF_FAIL = 'vp/company/GET_STAFF_FAIL';
// Get companies
const GET_COMPANIES = 'vp/company/GET_COMPANIES';
const GET_COMPANIES_SUCCESS = 'vp/company/GET_COMPANIES_SUCCESS';
const GET_COMPANIES_FAIL = 'vp/company/GET_COMPANIES_FAIL';
// Get branches
const GET_BRANCHES = 'vp/company/GET_BRANCHES';
const GET_BRANCHES_SUCCESS = 'vp/company/GET_BRANCHES_SUCCESS';
const GET_BRANCHES_FAIL = 'vp/company/GET_BRANCHES_FAIL';
// Patch branch
const PATCH_BRANCH = 'vp/company/PATCH_BRANCH';
const PATCH_BRANCH_SUCCESS = 'vp/company/PATCH_BRANCH_SUCCESS';
const PATCH_BRANCH_FAIL = 'vp/company/PATCH_BRANCH_FAIL';
// Patch companies
const PATCH_COMPANY = 'vp/company/PATCH_COMPANY';
const PATCH_COMPANY_SUCCESS = 'vp/company/PATCH_COMPANY_SUCCESS';
const PATCH_COMPANY_FAIL = 'vp/company/PATCH_COMPANY_FAIL';
// Change selected company
const CHANGE_SELECTED_COMPANY = 'vp/company/CHANGE_SELECTED_COMPANY';
// Create branch
const CREATE_BRANCH = 'vp/company/CREATE_BRANCH';
const CREATE_BRANCH_SUCCESS = 'vp/company/CREATE_BRANCH_SUCCESS';
const CREATE_BRANCH_FAIL = 'vp/company/CREATE_BRANCH_FAIL';
// Update staff
const UPDATE_STAFF = 'vp/company/UPDATE_STAFF';
const UPDATE_STAFF_SUCCESS = 'vp/company/UPDATE_STAFF_SUCCESS';
const UPDATE_STAFF_FAIL = 'vp/company/UPDATE_STAFF_FAIL';
// ASC search
const ASC_SEARCH = 'vp/company/ASC_SEARCH';
const ASC_SEARCH_SUCCESS = 'vp/company/ASC_SEARCH_SUCCESS';
const ASC_SEARCH_FAIL = 'vp/company/ASC_SEARCH_FAIL';
// Invite appraiser
const INVITE_APPRAISER = 'vp/company/INVITE_APPRAISER';
const INVITE_APPRAISER_SUCCESS = 'vp/company/INVITE_APPRAISER_SUCCESS';
const INVITE_APPRAISER_FAIL = 'vp/company/INVITE_APPRAISER_FAIL';
// Get user permissions
const GET_PERMISSIONS = 'vp/company/GET_PERMISSIONS';
const GET_PERMISSIONS_SUCCESS = 'vp/company/GET_PERMISSIONS_SUCCESS';
const GET_PERMISSIONS_FAIL = 'vp/company/GET_PERMISSIONS_FAIL';
// Set user permissions
const SET_PERMISSIONS = 'vp/company/SET_PERMISSIONS';
const SET_PERMISSIONS_SUCCESS = 'vp/company/SET_PERMISSIONS_SUCCESS';
const SET_PERMISSIONS_FAIL = 'vp/company/SET_PERMISSIONS_FAIL';
// Reset Branch
const RESET_BRANCH = 'vp/company/RESET_BRANCH';
// Select ASC
const SELECT_ASC_APPRAISER = 'vp/company/SELECT_ASC_APPRAISER';
// Delete staff
const DELETE_STAFF = 'vp/company/DELETE_STAFF';
const DELETE_STAFF_SUCCESS = 'vp/company/DELETE_STAFF_SUCCESS';
const DELETE_STAFF_FAIL = 'vp/company/DELETE_STAFF_FAIL';
// Get manager
const GET_MANAGER = 'vp/company/GET_MANAGER';
const GET_MANAGER_SUCCESS = 'vp/company/GET_MANAGER_SUCCESS';
const GET_MANAGER_FAIL = 'vp/company/GET_MANAGER_FAIL';
// Update manager
const UPDATE_MANAGER = 'vp/company/UPDATE_MANAGER';
const UPDATE_MANAGER_SUCCESS = 'vp/company/UPDATE_MANAGER_SUCCESS';
const UPDATE_MANAGER_FAIL = 'vp/company/UPDATE_MANAGER_FAIL';
// Get notification
const GET_NOTIFICATIONS = 'vp/company/GET_NOTIFICATIONS';
const GET_NOTIFICATIONS_SUCCESS = 'vp/company/GET_NOTIFICATIONS_SUCCESS';
const GET_NOTIFICATIONS_FAIL = 'vp/company/GET_NOTIFICATIONS_FAIL';
// Set notification
const SET_NOTIFICATIONS = 'vp/company/SET_NOTIFICATIONS';
const SET_NOTIFICATIONS_SUCCESS = 'vp/company/SET_NOTIFICATIONS_SUCCESS';
const SET_NOTIFICATIONS_FAIL = 'vp/company/SET_NOTIFICATIONS_FAIL';
// Select appraiser for profile editing
const SELECT_APPRAISER = 'vp/company/SELECT_APPRAISER';
// Get appraiser profile
const GET_APPRAISER_PROFILE = 'vp/company/GET_APPRAISER_PROFILE';
const GET_APPRAISER_PROFILE_SUCCESS = 'vp/company/GET_APPRAISER_PROFILE_SUCCESS';
const GET_APPRAISER_PROFILE_FAIL = 'vp/company/GET_APPRAISER_PROFILE_FAIL';
// Search company appraisers
const SEARCH_COMPANY_APPRAISERS = 'vp/company/SEARCH_COMPANY_APPRAISERS';
const SEARCH_COMPANY_APPRAISERS_SUCCESS = 'vp/company/SEARCH_COMPANY_APPRAISERS_SUCCESS';
const SEARCH_COMPANY_APPRAISERS_FAIL = 'vp/company/SEARCH_COMPANY_APPRAISERS_FAIL';
// Update appraiser profile
const UPDATE_APPRAISER_PROFILE = 'vp/company/UPDATE_APPRAISER_PROFILE';
const UPDATE_APPRAISER_PROFILE_SUCCESS = 'vp/company/UPDATE_APPRAISER_PROFILE_SUCCESS';
const UPDATE_APPRAISER_PROFILE_FAIL = 'vp/company/UPDATE_APPRAISER_PROFILE_FAIL';
// Reassign order
const REASSIGN_ORDER = 'vp/company/REASSIGN_ORDER';
const REASSIGN_ORDER_SUCCESS = 'vp/company/REASSIGN_ORDER_SUCCESS';
const REASSIGN_ORDER_FAIL = 'vp/company/REASSIGN_ORDER_FAIL';
// Modify appraiser details as manager
const SET_APPRAISER_VALUE = 'vp/company/SET_APPRAISER_VALUE';
import Immutable from 'immutable';
import _ from 'lodash';

import {setProp as setPropInherited, fileUpload} from 'helpers/genericFunctions';

const tinIsUsedErrorMessage = 'This Tax ID is already in use. Check with the administrator of the company associated with this Tax ID to be added to this company.';

// Company creation form
export const newCompany = {
  name: '',
  email: '',
  firstName: '',
  lastName: '',
  address1: '',
  address2: '',
  city: '',
  state: 'AL',
  zip: '',
  assignmentZip: '',
  phone: '',
  fax: '',
  taxId: '',
  type: 'individual-ssn',
  otherType: '',
  eo: {
    document: null,
    claimAmount: '',
    aggregateAmount: '',
    expiresAt: '',
    carrier: '',
    deductible: ''
  },
  ach: {
    bankName: '',
    accountType: 'checking',
    accountNumber: '',
    routing: ''
  },
  w9: ''
};

// Format for errors
const newCompanyErrors = Object.assign({}, newCompany);
newCompanyErrors.eo.document = '';
newCompanyErrors.w9 = '';
newCompanyErrors.ach.accountType = '';

const achFormInterface = {
  accountType: 'checking',
  bankName: '',
  routing: '',
  accountNumber: ''
};
const achFormErrorInterface = Object.assign({}, achFormInterface);
achFormErrorInterface.accountType = '';

export const addManagerInterface = {
  branch: 0,
  isAdmin: false,
  isManager: true,
  isRManager: false,
  user: {
    username: '',
    password: '',
    firstName: '',
    lastName: '',
    phone: '',
    email: ''
  },
  notifyUser: false
};

export const inviteFormInterface = {
  licenseState: 'AL',
  licenseNumber: '',
  firstName: '',
  lastName: '',
  phone: '',
  email: '',
  branch: '',
  ascAppraiser: 0,
  requirements: {ach: false, 'sample-reports': false, resume: false}
};

// Initial state
const initialState = Immutable.fromJS({
  // New company form
  newCompany,
  errors: newCompany,
  tinIsUsed: false,
  // Company list
  companies: [],
  // Top nav
  showCompanyNav: false,
  // Ach form
  achForm: achFormInterface,
  // Ach form errors
  achFormErrors: achFormErrorInterface,
  // Selected company
  selectedCompany: {},
  // Selected branch
  selectedBranch: {state: 'AL'},
  // Branch errors
  branchErrors: {},
  // Add maanger
  addManagerForm: addManagerInterface,
  // Add manager errors
  addManagerErrors: addManagerInterface,
  // Update manager
  updateManager: {},
  // Update manager errors
  updateManagerErrors: {},
  // Invite appraiser form
  inviteForm: inviteFormInterface,
  // Send invitation error
  inviteErrors: inviteFormInterface,
  // ASC search results
  ascSearchResults: [],
  // ASC selected
  ascSelected: false,
  // Selected user viewing permissions
  permissionsSelectedUser: {},
  // Selected staff for whom permissions are enabled
  permissions: [],
  notification: {customers: [], selected: {}},
  // Update other appraisers' profiles
  profiles: {
    // Search appraiser query
    searchAppraiserVal: '',
    // Company appraisers
    companyAppraisers: [],
  },
  // Selected appraiser
  profileSelectedAppraiser: {},
  // Errors for modifying appraiser as manager
  profileFormErrors: {},
  // Staff on a company
  staff: [],
  // Company appraisers
  companyAppraisers: [],
  // Reassign props
  reassign: {
    name: '',
    nameError: '',
    distance: '',
    distanceError: ''
  },
});

// Validation constraints
const constraints = createConstraints(signUpFields);

/**
 * Display nice validation messages for company create/patch
 * @param state Current state
 * @param errors Backend errors
 * @param errorPathBegin Error path in state
 */
function validateCompany(state, errors, errorPathBegin = 'errors') {
  _.forEach(errors, (val, key) => {
    const path = key.split('.');
    path.unshift(errorPathBegin);
    const endPath = path[path.length - 1];
    if (path[2] === 'document') {
      val.message = 'An E&O document is required.';
    }
    if (path[2] === 'carrier') {
      val.message = 'An E&O carrier is required.';
    }
    if (path[1] === 'w9') {
      val.message = 'A W9 document is required.';
    }
    if (path[2] === 'routing') {
      val.message = 'Routing number must be exactly nine digits.';
    }
    if (endPath === 'email') {
      val.message = 'Email must be valid.';
    }
    if (endPath === 'password') {
      val.message = 'Password must be 5 to 255 valid, English characters.';
    }
    if (endPath === 'firstName' || endPath === 'lastName') {
      val.message = 'Value is required and must be less than 50 characters.';
    }
    state = state.setIn(path, val.message);
  });
  return state;
}

import {
  profileErrors
} from '../../helpers/validation';

export default function reducer(state = initialState, action = {}) {
  switch (action.type) {
    /**
     * Change the selected company
     */
    case CHANGE_SELECTED_COMPANY:
      const selected = state.get('companies').filter(company => company.get('id') === action.companyId).get(0);
      return state
        .set('selectedCompany', Immutable.fromJS(selected));
    case CREATE_COMPANY:
      return state
        .set('creatingCompany', true)
        .set('errors', Immutable.fromJS(newCompanyErrors))
        .remove('createCompany');
    case CREATE_COMPANY_SUCCESS:
      return state
        .remove('creatingCompany')
        .set('createCompany', true);
    case CREATE_COMPANY_FAIL:
      // Display errors
      state = validateCompany(state, action.error.errors);
      return state
        .remove('creatingCompany')
        .set('createCompany', false);
    case CHECKING_TIN:
      return state
        .set('checkingTin', true);
    case CHECKING_TIN_SUCCESS:
      return state
        .remove('checkingTin')
        .set('tinIsUsed', true)
        .setIn(['newCompanyErrors', 'taxId'], tinIsUsedErrorMessage);
    case CHECKING_TIN_FAIL:
      if (state.getIn(['errors', 'taxId']) === tinIsUsedErrorMessage) {
        state = state.removeIn(['errors', 'taxId']);
      }
      return state
        .remove('checkingTin')
        .set('tinIsUsed', false);
    case FILE_UPLOAD:
      return state
        .remove('fileUploaded')
        .set('uploadingFile', true)
        .removeIn(['newCompany', ...action.docType]);
    case FILE_UPLOAD_SUCCESS:
      let fileUploadState = state;
      const docType = action.docType.join(',');
      // E&O document
      if (docType === 'eo,document') {
        fileUploadState = fileUploadState.setIn(['profileSelectedAppraiser', 'eo', 'document'], Immutable.fromJS(action.result));
      // Resume
      } else if (docType === 'qualifications,resume') {
        fileUploadState = fileUploadState.setIn(['profileSelectedAppraiser', 'qualifications', 'resume'], Immutable.fromJS(action.result));
      // Samples
      } else if (/sampleReport/.test(docType)) {
        fileUploadState = fileUploadState.setIn(['profileSelectedAppraiser', docType], Immutable.fromJS(action.result));
      }
      return fileUploadState
        .remove('uploadingFile')
        .set('fileUploaded', true)
        .setIn(action.docType, Immutable.fromJS(action.result));
    case FILE_UPLOAD_FAIL:
      let errorMessage = '';
      if (action.error && action.error.errors && action.error.errors.document && action.error.errors.document.message) {
        errorMessage = action.error.errors.document.message;
      }
      return state
        .remove('uploadingFile')
        .set('fileUploaded', false)
        .setIn(['fileUpload', 'success'], false)
        .setIn(['fileUpload', 'uploading'], false)
        .setIn(['fileUpload', 'error'], errorMessage);
    case PREFILL_APPRAISER:
      return state
        .remove('prefilledWithAppraiserInfo')
        .set('prefillingWithAppraiserInfo', true);
    case PREFILL_APPRAISER_SUCCESS:
      return state
        .remove('prefillingWithAppraiserInfo')
        .set('prefilledWithAppraiserInfo', true)
        .setIn(['newCompany', 'firstName'], action.result.firstName)
        .setIn(['newCompany', 'lastName'], action.result.lastName)
        .setIn(['newCompany', 'email'], action.result.email)
        .setIn(['newCompany', 'phone'], action.result.phone)
        .setIn(['newCompany', 'fax'], action.result.fax)
        .setIn(['newCompany', 'address1'], action.result.address1)
        .setIn(['newCompany', 'address2'], action.result.address2)
        .setIn(['newCompany', 'city'], action.result.city)
        .setIn(['newCompany', 'state'], action.result.state.code)
        .setIn(['newCompany', 'zip'], action.result.zip)
        .setIn(['newCompany', 'assignmentZip'], action.result.assignmentZip)
        .setIn(['newCompany', 'eo', 'claimAmount'], action.result.eo.claimAmount)
        .setIn(['newCompany', 'eo', 'aggregateAmount'], action.result.eo.aggregateAmount)
        .setIn(['newCompany', 'eo', 'expiresAt'], action.result.eo.expiresAt)
        .setIn(['newCompany', 'eo', 'carrier'], action.result.eo.carrier)
        .setIn(['newCompany', 'eo', 'deductible'], action.result.eo.deductible);
    case PREFILL_APPRAISER_FAIL:
      return state
        .remove('prefillingWithAppraiserInfo')
        .set('prefilledWithAppraiserInfo', false);
    /**
     * Prefille appraiser ACH
     */
    case PREFILL_ACH:
      const ach = Immutable.fromJS(action.ach);
      const formattedAch = ach
        .set('accountNumber', '****' + ach.get('accountNumber'));
      return state.setIn(['newCompany', 'ach'], formattedAch);
    /**
     * Add manager
     */
    case ADD_MANAGER:
      return state
        .remove('addManagerSuccess')
        .set('addingManager', true);
    case ADD_MANAGER_SUCCESS:
      return state
        .remove('addingManager')
        .set('addManagerSuccess', true);
    case ADD_MANAGER_FAIL:
      state = validateCompany(state, action.error.errors, 'addManagerErrors');
      return state
        .remove('addingManager')
        .set('addManagerSuccess', false);
    /**
     * Get branches
     */
    case GET_BRANCHES:
      return state
        .remove('branchesRetrieved')
        .set('retrievingBranches', true);
    case GET_BRANCHES_SUCCESS:
      const branches = Immutable.fromJS(action.result).map(branch => {
        return branch.set('state', branch.getIn(['state', 'code']));
      });
      return state
        .remove('retrievingBranches')
        .set('branchesRetrieved', true)
        .set('branches', branches);
    case GET_BRANCHES_FAIL:
      return state
        .remove('retrievingBranches')
        .set('branchesRetrieved', false);
    /**
     * Get staff
     */
    case GET_STAFF:
      return state
        .remove('staffRetrieved')
        .set('retrievingStaff', true);
    case GET_STAFF_SUCCESS:
      return state
        .remove('retrievingStaff')
        .set('staffRetrieved', true)
        .set('staff', Immutable.fromJS(action.result.data));
    case GET_STAFF_FAIL:
      return state
        .remove('retrievingStaff')
        .set('staffRetrieved', false);
    /**
     * Get companies
     */
    case GET_COMPANIES:
      return state
        .remove('companiesRetrieved')
        .set('retrievingCompanies', true);
    case GET_COMPANIES_SUCCESS:
      const result = action.result;
      // @todo
      if (!result && !result.data) {
        return state
          .set('noCompanyAvailable', true);
      }
      let companies = Immutable.fromJS(action.result.data);
      const props = ['ach', 'address1', 'address2', 'assignmentZip', 'city', 'email', 'eo', 'fax', 'firstName',
                     'lastName', 'phone', 'state', 'taxId', 'type', 'w9', 'zip'];
      companies = companies.map(company => {
        props.forEach(prop => {
          let value = result.data[0][prop];
          if (prop === 'state') {
            value = value.code;
          }
          company = company.set(prop, Immutable.fromJS(value));
        });
        return company;
      });
      // Check if allowed to administer any company
      let showCompanyNav = false;
      if (action.user.get('isBoss')) {
        showCompanyNav = true;
      }
      return state
        .remove('retrievingCompanies')
        .set('companiesRetrieved', true)
        .set('companies', companies)
        .set('showCompanyNav', showCompanyNav);
    case GET_COMPANIES_FAIL:
      return state
        .remove('retrievingCompanies')
        .set('companiesRetrieved', false);
    /**
     * Patch branch
     */
    case PATCH_BRANCH:
      return state
        .remove('patchBranchSuccess')
        .set('branchErrors', Immutable.Map())
        .set('patchingBranch', true);
    case PATCH_BRANCH_SUCCESS:
      return state
        .remove('patchingBranch')
        .set('patchBranchSuccess', true);
    case PATCH_BRANCH_FAIL:
      state = validateCompany(state, action.error.errors, 'branchErrors');
      return state
        .remove('patchingBranch')
        .set('patchBranchSuccess', false);
    /**
     * Patch branch
     */
    case PATCH_COMPANY:
      return state
        .remove('patchCompanySuccess')
        .set('errors', Immutable.fromJS(newCompanyErrors))
        .set('patchingCompany', true);
    case PATCH_COMPANY_SUCCESS:
      return state
        .remove('patchingCompany')
        .set('patchCompanySuccess', true);
    case PATCH_COMPANY_FAIL:
      state = validateCompany(state, action.error.errors);
      return state
        .remove('patchingCompany')
        .set('patchCompanySuccess', false);
    /**
     * Create a branch
     */
    case CREATE_BRANCH:
      return state
        .set('creatingBranch', true)
        .remove('createdBranch');
    case CREATE_BRANCH_SUCCESS:
      return state
        .set('createdBranch', true)
        .remove('creatingBranch');
    case CREATE_BRANCH_FAIL:
      state = validateCompany(state, action.error.errors, 'branchErrors');
      return state
        .set('createdBranch', false)
        .remove('creatingBranch');
    /**
     * ASC search
     */
    case ASC_SEARCH:
      return state
        .set('searchingAsc', true)
        .remove('ascSearchSuccess');
    case ASC_SEARCH_SUCCESS:
      return state
        .set('ascSearchSuccess', true)
        .set('ascSearchResults', Immutable.fromJS(action.result.data))
        .remove('searchingAsc');
    case ASC_SEARCH_FAIL:
      return state
        .set('ascSearchSuccess', false)
        .remove('searchingAsc');
    /**
     * Select ASC
     */
    case SELECT_ASC_APPRAISER:
      const incoming = action.appraiser;
      const appraiser = Immutable.fromJS({
        licenseState: incoming.getIn(['licenseState', 'code']) || '',
        licenseNumber: incoming.get('licenseNumber') || '',
        firstName: incoming.get('firstName') || '',
        lastName: incoming.get('lastName') || '',
        phone: incoming.get('phone') || '',
        email: incoming.get('email') || '',
        branch: state.getIn(['inviteForm', 'branch']),
        ascAppraiser: incoming.get('id')
      });
      return state
        .set('inviteForm', appraiser)
        .set('ascSearchResults', Immutable.List())
        .set('ascSelected', true);
    /**
     * Invite appraiser
     */
    case INVITE_APPRAISER:
      return state
        .set('invitingAppraiser', true)
        .remove('inviteAppraiserSuccess');
    case INVITE_APPRAISER_SUCCESS:
      return state
        .set('inviteAppraiserSuccess', true)
        .remove('invitingAppraiser');
    case INVITE_APPRAISER_FAIL:
      state = validateCompany(state, action.error.errors, 'inviteErrors');
      // Format already in company error
      if (state.getIn(['inviteErrors', 'ascAppraiser']) && action.error.errors.ascAppraiser.identifier === 'already-in-company') {
        state = state.set('ascSelected', false);
        const form = state.get('inviteForm');
        const name = form.get('firstName') + ' ' + form.get('lastName');
        state = state.setIn(['inviteErrors', 'ascAppraiser'], name + ' is already part of this company.');
        state = state.setIn(['inviteForm', 'phone'], '');
        state = state.setIn(['inviteForm', 'email'], '');
      }
      return state
        .set('inviteAppraiserSuccess', false)
        .remove('invitingAppraiser');
    /**
     * Get staff user permissions
     */
    case GET_PERMISSIONS:
      return state
        .set('getPermissionsSuccess', true)
        .remove('getPermissionsSuccess');
    case GET_PERMISSIONS_SUCCESS:
      const ids = action.result.data.map(user => user.id);
      return state
        .set('getPermissionsSuccess', true)
        .set('permissions', Immutable.fromJS(ids))
        .remove('getPermissionsSuccess');
    case GET_PERMISSIONS_FAIL:
      return state
        .set('getPermissionsSuccess', false)
        .remove('getPermissionsSuccess');
    /**
     * Set user permissions
     */
    case SET_PERMISSIONS:
      return state
        .set('settingPermissions', true)
        .remove('setPermissionsSuccess');
    case SET_PERMISSIONS_SUCCESS:
      return state
        .set('setPermissionsSuccess', true)
        .set('permissions', Immutable.List())
        .remove('settingPermissions');
    case SET_PERMISSIONS_FAIL:
      return state
        .set('setPermissionsSuccess', false)
        .remove('settingPermissions');
    /**
     * Search branch
     */
    case RESET_BRANCH:
      return state
        .set('branchErrors', Immutable.Map())
        .set('selectedBranch', initialState.get('selectedBranch'));
    case UPDATE_STAFF:
      return state
        .set('updatingStaff', true)
        .remove('updateStaffSuccess');
    case UPDATE_STAFF_SUCCESS:
      return state
        .set('updateStaffSuccess', true)
        .remove('updatingStaff');
    case UPDATE_STAFF_FAIL:
      return state
        .set('updateStaffSuccess', false)
        .remove('updatingStaff');
    case DELETE_STAFF:
      return state
        .set('deletingStaff', true)
        .remove('deleteStaffSuccess');
    case DELETE_STAFF_SUCCESS:
      return state
        .set('deleteStaffSuccess', true)
        .remove('deletingStaff');
    case DELETE_STAFF_FAIL:
      return state
        .set('deleteStaffSuccess', false)
        .remove('deletingStaff');
    case UPDATE_MANAGER:
      return state
        .set('updatingManager', true)
        .set('updateManagerErrors', Immutable.Map())
        .remove('updateManagerSuccess');
    case UPDATE_MANAGER_SUCCESS:
      return state
        .set('updateManagerSuccess', true)
        .remove('updatingManager');
    case UPDATE_MANAGER_FAIL:
      state = validateCompany(state, action.error.errors, 'updateManagerErrors');
      return state
        .set('updateManagerSuccess', false)
        .remove('updatingManager');
    case GET_MANAGER:
      return state
        .set('gettingManager', true)
        .remove('getManagerSuccess');
    case GET_MANAGER_SUCCESS:
      return state
        .set('getManagerSuccess', true)
        .set('updateManager', Immutable.fromJS(action.result))
        .remove('gettingManager');
    case GET_MANAGER_FAIL:
      return state
        .set('getManagerSuccess', false)
        .remove('gettingManager');
    case GET_NOTIFICATIONS:
      return state
        .set('gettingNotifications', true)
        .remove('getNotificationsSuccess');
    case GET_NOTIFICATIONS_SUCCESS:
      return state
        .setIn(['notification', 'customers'], Immutable.fromJS(action.result.notifications))
        .set('getNotificationsSuccess', true)
        .remove('gettingNotifications');
    case GET_NOTIFICATIONS_FAIL:
      return state
        .set('getNotificationsSuccess', false)
        .remove('gettingNotifications');
    case SET_NOTIFICATIONS:
      return state
        .set('settingNotifications', true)
        .remove('setNotificationsSuccess');
    case SET_NOTIFICATIONS_SUCCESS:
      return state
        .set('setNotificationsSuccess', true)
        .remove('settingNotifications');
    case SET_NOTIFICATIONS_FAIL:
      return state
        .set('setNotificationsSuccess', false)
        .remove('settingNotifications');
    /**
     * Select appraiser for profile editing
     */
    case SELECT_APPRAISER:
      return state.setIn(['profileSelectedAppraiser', 'id'], action.appraiser.get('id'))
        .setIn(['profileSelectedAppraiser', 'name'], action.appraiser.get('displayName'));
    /**
     * Get appraiser profile
     */
    case GET_APPRAISER_PROFILE:
      return state
        .set('getAppraiserProfileSuccess', null);
    case GET_APPRAISER_PROFILE_SUCCESS:
      return state
        .set('getAppraiserProfileSuccess', true)
        .set('profileSelectedAppraiser', formatAppraiser(action.result));
    case GET_APPRAISER_PROFILE_FAIL:
      return state
        .set('getAppraiserProfileSuccess', false);
    /**
     * Search company appraisers
     */
    case SEARCH_COMPANY_APPRAISERS:
      return state
        .remove('searchCompanyAppraisers');
    case SEARCH_COMPANY_APPRAISERS_SUCCESS:
      return state
        .set('searchCompanyAppraisers', true)
        .set('companyAppraisers', Immutable.fromJS(action.result.data));
    case SEARCH_COMPANY_APPRAISERS_FAIL:
      return state
        .set('searchCompanyAppraisers', false);
    /**
     * Update appraiser profile
     */
    case UPDATE_APPRAISER_PROFILE:
      return state
        .remove('updateAppraiserProfileSuccess');
    case UPDATE_APPRAISER_PROFILE_SUCCESS:
      return state
        .set('updateAppraiserProfileSuccess', true);
    case UPDATE_APPRAISER_PROFILE_FAIL:
      let profileFormErrors = state.get('profileFormErrors');
      // Update errors
      Immutable.fromJS(action.error.errors).filter((error, key) => {
        return !state.getIn(['profileFormErrors', key]);
      })
        .map((error, key) => {
          profileFormErrors = profileFormErrors.set(key, Immutable.List().push(error.get('message')));
        });
      return state
        .set('updateAppraiserProfileSuccess', false)
        .set('profileFormErrors', profileFormErrors);
    /**
     * Reassign order
     */
    case REASSIGN_ORDER:
      return state
        .remove('reassignOrderSuccess');
    case REASSIGN_ORDER_SUCCESS:
      return state
        .set('reassignOrderSuccess', true);
    case REASSIGN_ORDER_FAIL:
      return state
        .set('reassignOrderSuccess', false);
    /**
     * Update appraiser values as manager
     */
    case SET_APPRAISER_VALUE:
      const formValues = updateAppraiserValues(action, state, 'profileSelectedAppraiser');
      return state.set('profileSelectedAppraiser', formValues)
        .set('profileFormErrors',
          profileErrors(state, action, 'profileFormErrors', formValues, constraints));
    case SET_PROP:
      return state.setIn(action.name, action.value);
    case REMOVE_PROP:
      return state.removeIn(action.namePath);
    default:
      return state;
  }
}

/**
 * Checks if a company has already been created using the specified TIN
 * @param {string} tin
 */
export function checkTin(tin) {
  return {
    types: [CHECKING_TIN, CHECKING_TIN_SUCCESS, CHECKING_TIN_FAIL],
    promise: client => client.get(`dev:/companies/tax-id/${tin}`)
  };
}

/**
 * Create company
 * @param appraiserId Appraiser creating company
 * @param data Company
 */
export function createCompany(appraiserId, data) {
  return {
    types: [CREATE_COMPANY, CREATE_COMPANY_SUCCESS, CREATE_COMPANY_FAIL],
    promise: client => client.post(`dev:/companies`, {
      data
    })
  };
}

/**
 * Change selected company and set it in props
 * @param companyId Selected company
 */
export function changeSelectedCompany(companyId) {
  return {
    type: CHANGE_SELECTED_COMPANY,
    companyId
  };
}

/**
 * Get company branches
 * @param companyId Company ID
 */
export function getBranches(companyId) {
  return {
    types: [GET_BRANCHES, GET_BRANCHES_SUCCESS, GET_BRANCHES_FAIL],
    promise: client => client.get(`dev:/companies/${companyId}/branches`, {
      data: {
        headers: {
          Include: 'isDefault,taxId,address1,address2,city,state,zip,assignmentZip,eo'
        }
      }
    }),
  };
}

/**
 * Retrieves a list of companies a user belongs to
 * Roles are: admin, manager, and rfp-manager
 *
 * @param {object} user
 */
export function getCompanies(user) {
  return {
    types: [GET_COMPANIES, GET_COMPANIES_SUCCESS, GET_COMPANIES_FAIL],
    promise: client => client.get(`dev:/${user.get('type')}s/${user.get('id')}/companies`, {
      data: {
        headers: {
          Include: 'ach,address1,address2,assignmentZip,city,email,eo,fax,firstName,lastName,phone,privileges,staff,staff.branch,state,taxId,type,w9,zip'
        }
      }
    }),
    user
  };
}

/**
 * Get staff in this company
 * @param companyId Company ID
 */
export function getStaff(companyId) {
  return {
    types: [GET_STAFF, GET_STAFF_SUCCESS, GET_STAFF_FAIL],
    promise: client => client.get(`dev:/companies/${companyId}/staff`, {
      data: {
        headers: {
          Include: 'branch'
        }
      }
    }),
  };
}

/**
 * Patch a company
 * @param companyId Company ID
 * @param data New data
 */
export function patchCompany(companyId, data) {
  return {
    types: [PATCH_COMPANY, PATCH_COMPANY_SUCCESS, PATCH_COMPANY_FAIL],
    promise: client => client.patch(`dev:/companies/${companyId}`, {
      data
    }),
  };
}

/**
 * Patch branch
 * @param companyId Company ID
 * @param branchId Branch ID
 * @param data Branch updates
 */
export function patchBranch(companyId, branchId, data) {
  return {
    types: [PATCH_BRANCH, PATCH_BRANCH_SUCCESS, PATCH_BRANCH_FAIL],
    promise: client => client.patch(`dev:/companies/${companyId}/branches/${branchId}`, {
      data
    }),
  };
}

/**
 * Fills the company data with the appraiser's info
 *
 * @param {number} appraiserId
 */
export function prefillWithAppraiserInfo(appraiserId) {
  return {
    types: [PREFILL_APPRAISER, PREFILL_APPRAISER_SUCCESS, PREFILL_APPRAISER_FAIL],
    promise: client => client.get(`dev:/appraisers/${appraiserId}`, {
      data: {
        headers: {
          Include: 'phone,fax,address1,address2,city,state,zip,assignmentZip,phone,cell,fax,eo,ach,w9'
        }
      }
    })
  };
}

/**
 * Removes a prop from the state tree
 * @param namePath
 */
export function removeProp(...namePath) {
  return {
    promise: () => new Promise(resolve => resolve()),
    type: REMOVE_PROP,
    namePath
  };
}

/**
 * Prefill appraiser ACH
 * @param ach Current ACH settings
 */
export function setAppraiserAchDefaults(ach) {
  return {
    type: PREFILL_ACH,
    ach
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
 * @param path
 * @param document
 */
export function uploadFile(path, document) {
  return fileUpload([FILE_UPLOAD, FILE_UPLOAD_SUCCESS, FILE_UPLOAD_FAIL], path, document);
}

/**
 * Creates a new branch
 *
 * @param {Number} companyId
 * @param {Object} branch
 */
export function createBranch(companyId, branch) {
  return {
    types: [CREATE_BRANCH, CREATE_BRANCH_SUCCESS, CREATE_BRANCH_FAIL],
    promise: client => client.post(`dev:/companies/${companyId}/branches`, {
      data: branch
    })
  };
}

/**
 * Empties out the selected branch data
 */
export function resetBranch() {
  return {
    type: RESET_BRANCH
  };
}

/**
 * Updates staff
 *
 * @param {Number} companyId
 * @param {Number} staffId
 * @param {Object} staff
 */
export function updateStaff(companyId, staffId, staff) {
  return {
    types: [UPDATE_STAFF, UPDATE_STAFF_SUCCESS, UPDATE_STAFF_FAIL],
    promise: client => client.patch(`dev:/companies/${companyId}/staff/${staffId}`, {
      data: staff
    })
  };
}

/**
 * Add manager
 * @param companyId Company ID
 * @param data Manager data
 */
export function addManager(companyId, data) {
  return {
    types: [ADD_MANAGER, ADD_MANAGER_SUCCESS, ADD_MANAGER_FAIL],
    promise: client => client.post(`dev:/companies/${companyId}/managers`, {
      data
    })
  };
}

/**
 * Perform ASC search
 * @param ascForm Form values
 */
export function ascSearch(ascForm) {
  return {
    types: [ASC_SEARCH, ASC_SEARCH_SUCCESS, ASC_SEARCH_FAIL],
    promise: client => client.get(`dev:/asc?filter[licenseState]=${ascForm.get('licenseState')}&search[licenseNumber]=${ascForm.get('licenseNumber')}`)
  };
}

/**
 * Select an ASC appraiser
 * @param appraiser
 * @return {{type: string, appraiser: *}}
 */
export function selectAscAppraiser(appraiser) {
  return {
    type: SELECT_ASC_APPRAISER,
    appraiser
  };
}

/**
 * Invite an appraiser
 * @param companyId
 * @param branchId
 * @param data Appraiser data
 */
export function inviteAppraiser(companyId, branchId, data) {
  if (data.requirements) {
    data.requirements = Array.from(Immutable.fromJS(data.requirements).filter(v => v).keys());
  }

  return {
    types: [INVITE_APPRAISER, INVITE_APPRAISER_SUCCESS, INVITE_APPRAISER_FAIL],
    promise: client => client.post(`dev:/companies/${companyId}/branches/${branchId}/invitations`, {
      data
    })
  };
}

/**
 * Retrieve a user's permissions
 * @param companyId
 * @param staffId
 */
export function getUserPermissions(companyId, staffId) {
  return {
    types: [GET_PERMISSIONS, GET_PERMISSIONS_SUCCESS, GET_PERMISSIONS_FAIL],
    promise: client => client.get(`dev:/companies/${companyId}/staff/${staffId}/permissions`)
  };
}

/**
 * Update a user's permissions
 * @param companyId
 * @param staffId
 * @param data Array of permissions by user ID
 */
export function setUserPermissions(companyId, staffId, data) {
  return {
    types: [SET_PERMISSIONS, SET_PERMISSIONS_SUCCESS, SET_PERMISSIONS_FAIL],
    promise: client => client.put(`dev:/companies/${companyId}/staff/${staffId}/permissions`, {
      data: {
        data
      }
    })
  };
}

/**
 * Removes a staff from a company
 *
 * @param {Number} companyId
 * @param {Number} staffId
 */
export function deleteStaff(companyId, staffId) {
  return {
    types: [DELETE_STAFF, DELETE_STAFF_SUCCESS, DELETE_STAFF_FAIL],
    promise: client => client.delete(`dev:/companies/${companyId}/staff/${staffId}`)
  };
}

/**
 * Retrieve a manager's profile
 *
 * @param {Number} managerId
 */
export function getManager(managerId) {
  return {
    types: [GET_MANAGER, GET_MANAGER_SUCCESS, GET_MANAGER_FAIL],
    promise: client => client.get(`dev:/managers/${managerId}`, {
      data: {
        headers: {
          Include: 'phone,staff,staff.company'
        }
      }
    })
  };
}

/**
 * Update a manager's profile
 *
 * @param {Number} managerId
 * @param {Object} data
 */
export function updateManager(managerId, data) {
  return {
    types: [UPDATE_MANAGER, UPDATE_MANAGER_SUCCESS, UPDATE_MANAGER_FAIL],
    promise: client => client.patch(`dev:/managers/${managerId}`, {
      data
    })
  };
}

/**
 * Get manager's notification
 *
 * @param {Number} managerId
 */
export function getNotifications(managerId) {
  return {
    types: [GET_NOTIFICATIONS, GET_NOTIFICATIONS_SUCCESS, GET_NOTIFICATIONS_FAIL],
    promise: client => client.get(`dev:/managers/${managerId}/settings`)
  };
}

/**
 * Set manager's notification
 *
 * @param {Number} managerId
 * @param {Object[]} data
 */
export function setNotifications(managerId, data) {
  const notifications = [];
  data.forEach(notification => {
    notifications.push({
      customer: Number(notification.customer),
      email: notification.email
    });
  });

  return {
    types: [SET_NOTIFICATIONS, SET_NOTIFICATIONS_SUCCESS, SET_NOTIFICATIONS_FAIL],
    promise: client => client.patch(`dev:/managers/${managerId}/settings`, {
      data: {
        notifications
      }
    })
  };
}

/**
 * Select appraiser for profile editing
 * @param appraiser Selected appraiser ID
 */
export function selectAppraiser(appraiser) {
  return {
    type: SELECT_APPRAISER,
    appraiser
  };
}

/**
 * Get appraiser's profile
 * @param companyId
 * @param appraiserId
 */
export function getAppraiserProfile(companyId, appraiserId) {
  return {
    types: [GET_APPRAISER_PROFILE, GET_APPRAISER_PROFILE_SUCCESS, GET_APPRAISER_PROFILE_FAIL],
    promise: client => client.get(`dev:/companies/${companyId}/appraisers/${appraiserId}`, {
      data: {
        headers: {
          Include: 'companyName,businessTypes,companyType,otherCompanyType,taxIdentificationNumber,w9,company,languages,address1,address2,city,state,zip,assignmentAddress1,assignmentAddress2,assignmentState,assignmentCity,assignmentZip,phone,cell,fax,qualifications,eo,sampleReports'
        }
      }
    })
  };
}

/**
 * Search company appraisers
 * @param companyId Company ID
 * @param orderId Order being seached again
 * @param nameSearch Partial name search
 * @param distanceSearch Distance from job
 */
export function searchCompanyAppraisers(companyId, orderId, nameSearch, distanceSearch) {
  return {
    types: [SEARCH_COMPANY_APPRAISERS, SEARCH_COMPANY_APPRAISERS_SUCCESS, SEARCH_COMPANY_APPRAISERS_FAIL],
    promise: client => client.get(`dev:/companies/${companyId}/appraisers?orderId=${orderId}&distance=${distanceSearch ? distanceSearch : 99999}`, {
      nameSearch
    })
  };
}

/**
 * Update appraiser profile as manager
 * @param companyId
 * @param appraiserId
 * @param data Appraiser profile
 * @return {{types: [*,*,*], promise: (function(*): *)}}
 */
export function updateAppraiserProfile(companyId, appraiserId, data) {
  return {
    types: [UPDATE_APPRAISER_PROFILE, UPDATE_APPRAISER_PROFILE_SUCCESS, UPDATE_APPRAISER_PROFILE_FAIL],
    promise: client => client.patch(`dev:/companies/${companyId}/appraisers/${appraiserId}`, {
      data
    })
  };
}

/**
 * Change appraiser info when modifying as manager
 */
export function appraiserValueChange(event) {
  const {target: {name, value}} = event;
  return {
    type: SET_APPRAISER_VALUE,
    name,
    value
  };
}

/**
 * Update a property value directly (not from event)
 * @param name Prop name
 * @param value Prop value
 */
export function updateProfileValue(name, value) {
  const append = arguments[0] === 'append';
  // Append multiselect
  if (append) {
    name = arguments[1];
    value = arguments[2];
  }
  return {
    type: SET_APPRAISER_VALUE,
    name,
    value,
    append
  };
}

/**
 * Reassign order to another appraiser
 * @param user User doing the reassignment
 * @param orderId Order being reassigned
 * @param appraiserId Appraiser getting order assigned to them
 */
export function reassign(user, orderId, appraiserId) {
  return {
    types: [REASSIGN_ORDER, REASSIGN_ORDER_SUCCESS, REASSIGN_ORDER_FAIL],
    promise: client => client.post(`dev:/${user.get('type')}s/${user.get('id')}/orders/${orderId}/reassign`, {
      data: {
        appraiser: appraiserId
      }
    })
  };
}
