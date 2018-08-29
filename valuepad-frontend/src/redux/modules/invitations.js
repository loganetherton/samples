import {sortByTitle, sortByEnabled, sortByFee, createFeeMap, startSortVal} from 'redux/modules/jobType';
// Retrieve invitations
const GET_INVITATIONS = 'vp/invitations/GET_INVITATIONS';
const GET_INVITATIONS_SUCCESS = 'vp/invitations/GET_INVITATIONS_SUCCESS';
const GET_INVITATIONS_FAIL = 'vp/invitations/GET_INVITATIONS_FAIL';
// Retrieve company invitations
const GET_COMPANY_INVITATIONS = 'vp/invitations/GET_COMPANY_INVITATIONS';
const GET_COMPANY_INVITATIONS_SUCCESS = 'vp/invitations/GET_COMPANY_INVITATIONS_SUCCESS';
const GET_COMPANY_INVITATIONS_FAIL = 'vp/invitations/GET_COMPANY_INVITATIONS_FAIL';
// Get pending invitations total
const GET_PENDING_INVITATIONS_TOTAL = 'vp/invitations/GET_PENDING_INVITATIONS_TOTAL';
const GET_PENDING_INVITATIONS_TOTAL_SUCCESS = 'vp/invitations/GET_PENDING_INVITATIONS_TOTAL_SUCCESS';
const GET_PENDING_INVITATIONS_TOTAL_FAIL = 'vp/invitations/GET_PENDING_INVITATIONS_TOTAL_FAIL';
// Set prop explicitly
const SET_PROP = 'vp/invitations/SET_PROP';
// Accept invitation
const ACCEPT_INVITATION = 'vp/invitations/ACCEPT_INVITATION';
const ACCEPT_INVITATION_SUCCESS = 'vp/invitations/ACCEPT_INVITATION_SUCCESS';
const ACCEPT_INVITATION_FAIL = 'vp/invitations/ACCEPT_INVITATION_FAIL';
// Decline invitation
const DECLINE_INVITATION = 'vp/invitations/DECLINE_INVITATION';
const DECLINE_INVITATION_SUCCESS = 'vp/invitations/DECLINE_INVITATION_SUCCESS';
const DECLINE_INVITATION_FAIL = 'vp/invitations/DECLINE_INVITATION_FAIL';
// ACH
const GET_ACH = 'vp/invitations/GET_ACH';
const GET_ACH_SUCCESS = 'vp/invitations/GET_ACH_SUCCESS';
const GET_ACH_FAIL = 'vp/invitations/GET_ACH_FAIL';
// Get appraiser
const GET_APPRAISER = 'vp/invitations/GET_APPRAISER';
const GET_APPRAISER_SUCCESS = 'vp/invitations/GET_APPRAISER_SUCCESS';
const GET_APPRAISER_FAIL = 'vp/invitations/GET_APPRAISER_FAIL';
// Get customer job types
const GET_CUSTOMER_JOB_TYPES = 'vp/invitations/GET_CUSTOMER_JOB_TYPES';
const GET_CUSTOMER_JOB_TYPES_SUCCESS = 'vp/invitations/GET_CUSTOMER_JOB_TYPES_SUCCESS';
const GET_CUSTOMER_JOB_TYPES_FAIL = 'vp/invitations/GET_CUSTOMER_JOB_TYPES_FAIL';
// Get job types
const GET_JOB_TYPES = 'vp/invitations/GET_JOB_TYPES';
const GET_JOB_TYPES_SUCCESS = 'vp/invitations/GET_JOB_TYPES_SUCCESS';
const GET_JOB_TYPES_FAIL = 'vp/invitations/GET_JOB_TYPES_FAIL';
// Get fees
const GET_FEES = 'vp/invitations/GET_FEES';
const GET_FEES_SUCCESS = 'vp/invitations/GET_FEES_SUCCESS';
const GET_FEES_FAIL = 'vp/invitations/GET_FEES_FAIL';
// Get default fees
const GET_DEFAULT_FEES = 'vp/invitations/GET_DEFAULT_FEES';
const GET_DEFAULT_FEES_SUCCESS = 'vp/invitations/GET_DEFAULT_FEES_SUCCESS';
const GET_DEFAULT_FEES_FAIL = 'vp/invitations/GET_DEFAULT_FEES_FAIL';
// Apply default fees
const APPLY_DEFAULT_FEES = 'vp/invitations/APPLY_DEFAULT_FEES';
// Save job types
const SAVE_JOB_TYPES = 'vp/invitations/SAVE_JOB_TYPES';
const SAVE_JOB_TYPES_SUCCESS = 'vp/invitations/SAVE_JOB_TYPES_SUCCESS';
const SAVE_JOB_TYPES_FAIL = 'vp/invitations/SAVE_JOB_TYPES_FAIL';
// Select a job type
const SELECT_JOB_TYPE = 'vp/invitations/SELECT_JOB_TYPE';
// Set fee for job type
const SET_JOB_TYPE_FEE = 'vp/invitations/SET_JOB_TYPE_FEE';
// Search/sort job types
const SORT_COLUMN = 'vp/invitations/SORT_COLUMN';
const CHANGE_SEARCH_VAL = 'vp/invitations/CHANGE_SEARCH_VAL';
// ACH
const SUBMIT_ACH = 'vp/invitations/SUBMIT_ACH';
const SUBMIT_ACH_SUCCESS = 'vp/invitations/SUBMIT_ACH_SUCCESS';
const SUBMIT_ACH_FAIL = 'vp/invitations/SUBMIT_ACH_FAIL';
// File upload
const FILE_UPLOAD = 'vp/invitations/FILE_UPLOAD';
const FILE_UPLOAD_SUCCESS = 'vp/invitations/FILE_UPLOAD_SUCCESS';
const FILE_UPLOAD_FAIL = 'vp/invitations/FILE_UPLOAD_FAIL';
// Update appraiser
const UPDATE_APPRAISER = 'vp/invitations/UPDATE_APPRAISER';
const UPDATE_APPRAISER_SUCCESS = 'vp/invitations/UPDATE_APPRAISER_SUCCESS';
const UPDATE_APPRAISER_FAIL = 'vp/invitations/UPDATE_APPRAISER_FAIL';
// Remove prop
const REMOVE_PROP = 'vp/invitations/REMOVE_PROP';

import Immutable from 'immutable';

import {
  setProp as setPropInherited,
  selectJobType as handleJobTypeSelect,
  setJobTypeFee,
  fileUpload,
  applyDefaultJobTypeValues
} from 'helpers/genericFunctions';

// ACH form
import {initialAchObject} from 'redux/modules/settings';

const initialState = Immutable.fromJS({
  // Total number of pending invitations
  pendingInvitationsTotal: 0,
  // Collection of invitations
  invitations: [],
  // Company invitations
  companyInvitations: [],
  // Meta
  meta: {},
  // Currently selected invitation
  selectedInvitation: null,
  // Requirements this appraiser meets
  metRequirements: {
    ach: false,
    'sample-reports': false,
    resume: false
  },
  // Job types
  jobTypes: [],
  customerJobTypes: [],
  originalCustomerJobTypes: [],
  fees: [],
  defaultFees: [],
  jobTypesInBackend: [],
  // ACH form
  ach: initialAchObject,
  // Sample reports
  sampleReports: {
    form: {},
    errors: {}
  },
  // Resume
  resume: {
    resume: null,
    errors: {}
  },
  // Job type sorts
  sorts: startSortVal,
  customerFormSearch: '',
  industryFormSearch: ''
});

import {backendErrorsImmutable} from '../../helpers/validation';

/**
 * Filter invitation after decline/accept
 * @param state Incoming state
 * @param action Incoming action
 */
function filterInvitations(state, action) {
  let stateAfterFilter;
  if (action.company) {
    stateAfterFilter = state
      .set('companyInvitations', state.get('companyInvitations').filter(invitation => invitation.get('id') !== action.invitationId));
  } else {
    stateAfterFilter = state
      .set('invitations', state.get('invitations').filter(invitation => invitation.get('id') !== action.invitationId));
  }
  return stateAfterFilter;
}

export default function reducer(state = initialState, action = {}) {
  switch (action.type) {
    /**
     * Get invitations
     */
    case GET_INVITATIONS:
      return state
        .remove('getInvitationsSuccess')
        .set('gettingInvitations', true);
    case GET_INVITATIONS_SUCCESS:
      return state
        .set('getInvitationsSuccess', true)
        .set('invitations', Immutable.fromJS(action.result.data))
        .set('meta', Immutable.fromJS(action.result.meta))
        .remove('gettingInvitations');
    case GET_INVITATIONS_FAIL:
      return state
        .set('getInvitationsSuccess', false)
        .remove('gettingInvitations');
    /**
     * Get company invitations
     */
    case GET_COMPANY_INVITATIONS:
      return state
        .remove('getCompanyInvitationsSuccess')
        .set('gettingCompanyInvitations', true);
    case GET_COMPANY_INVITATIONS_SUCCESS:
      let invitationState;
      if (action.total) {
        invitationState = state
          .set('pendingInvitationsTotal', state.get('pendingInvitationsTotal') + action.result.data.length);
      } else {
        invitationState = state
          .set('companyInvitations', Immutable.fromJS(action.result.data));
      }
      return invitationState
        .set('getCompanyInvitationsSuccess', true)
        .remove('gettingCompanyInvitations');
    case GET_COMPANY_INVITATIONS_FAIL:
      return state
        .set('getCompanyInvitationsSuccess', false)
        .remove('gettingCompanyInvitations');
    /**
     * Get pending invitations total
     */
    case GET_PENDING_INVITATIONS_TOTAL:
      return state
        .set('gettingPendingInvitationsTotal', true)
        .remove('gettingPendingInvitationsTotalSuccess');
    case GET_PENDING_INVITATIONS_TOTAL_SUCCESS:
      return state
        .set('gettingPendingInvitationsTotalSuccess', true)
        .set('pendingInvitationsTotal', action.result.meta.pagination.total)
        .remove('gettingPendingInvitationsTotal');
    case GET_PENDING_INVITATIONS_TOTAL_FAIL:
      return state
        .set('gettingPendingInvitationsTotalSuccess', false)
        .remove('gettingPendingInvitationsTotal');
    /**
     * Get ACH
     */
    case GET_ACH:
      return state
        .remove('getAchSuccess')
        .set('gettingAch', true);
    case GET_ACH_SUCCESS:
      const achFromDb = Immutable.fromJS(action.result);
      const achExists = !!(achFromDb.get('accountNumber') && achFromDb.get('accountType') &&
                           achFromDb.get('bankName') && achFromDb.get('routing'));
      return state
        .set('getAchSuccess', true)
        .setIn(['metRequirements', 'ach'], achExists)
        .remove('gettingAch');
    case GET_ACH_FAIL:
      return state
        .set('getAchSuccess', false)
        .remove('gettingAch');
    /**
     * Get appraiser
     */
    case GET_APPRAISER:
      return state
        .remove('getAppraiserSuccess')
        .set('gettingAppraiser', true);
    case GET_APPRAISER_SUCCESS:
      const appraiser = Immutable.fromJS(action.result);
      // See if we have sample reports
      const sampleReportExists = Immutable.List.isList(appraiser.get('sampleReports')) ?
                                 !!appraiser.get('sampleReports').count() : false;
      // See if we have resume
      const resumeExists = !!appraiser.getIn(['qualifications', 'resume']);
      return state
        .set('getAppraiserSuccess', true)
        .setIn(['metRequirements', 'sample-reports'], sampleReportExists)
        .setIn(['metRequirements', 'resume'], resumeExists)
        .remove('gettingAppraiser');
    case GET_APPRAISER_FAIL:
      return state
        .set('getAppraiserSuccess', false)
        .remove('gettingAppraiser');
    /**
     * Set a property explicitly
     */
    case SET_PROP:
      return state
        .setIn(action.name, action.value);
    /**
     * Accept invitation
     */
    case ACCEPT_INVITATION:
      return state
        .set('acceptingInvitation', true)
        .remove('acceptInvitationSuccess');
    case ACCEPT_INVITATION_SUCCESS:
      return filterInvitations(state, action)
        .remove('acceptingInvitation')
        .set('acceptInvitationSuccess', true)
        .set('pendingInvitationsTotal', Math.max(0, state.get('pendingInvitationsTotal') - 1));
    case ACCEPT_INVITATION_FAIL:
      return state
        .remove('acceptingInvitation')
        .set('acceptInvitationSuccess', false);
    /**
     * Get job types
     */
    case GET_JOB_TYPES:
      return state
        .set('gettingJobTypes', true)
        .remove('getJobTypesSuccess');
    case GET_JOB_TYPES_SUCCESS:
      return state
        .remove('gettingJobTypes')
        .set('jobTypes', Immutable.fromJS(action.result.data))
        .set('getJobTypesSuccess', true);
    case GET_JOB_TYPES_FAIL:
      return state
        .remove('gettingJobTypes')
        .set('getJobTypesSuccess', false);
    /**
     * Get fees
     */
    case GET_FEES:
      return state
        .set('gettingFees', true)
        .remove('getFeesSuccess');
    case GET_FEES_SUCCESS:
      const fees = Immutable.fromJS(action.result.data);
      const dbFeeIds = fees.map(fee => {
        return fee.get('id');
      });
      return state
        .remove('gettingFees')
        .set('fees', fees)
        .set('jobTypesInBackend', dbFeeIds)
        .set('getFeesSuccess', true);
    case GET_FEES_FAIL:
      return state
        .remove('gettingFees')
        .set('getFeesSuccess', false);
    /**
     * Get default fees
     */
    case GET_DEFAULT_FEES:
      return state
        .set('gettingDefaultFees', true)
        .remove('getDefaultFeesSuccess');
    case GET_DEFAULT_FEES_SUCCESS:
      return state
        .remove('gettingDefaultFees')
        .set('getDefaultFeesSuccess', true)
        .set('defaultFees', Immutable.fromJS(action.result.data));
    case GET_DEFAULT_FEES_FAIL:
      return state
        .remove('gettingDefaultFees')
        .set('getDefaultFeesSuccess', false);
    /**
     * Apply default fees
     */
    case APPLY_DEFAULT_FEES:
      return state
        .set('fees', applyDefaultJobTypeValues(state));
    /**
     * Get customer job types
     */
    case GET_CUSTOMER_JOB_TYPES:
      return state
        .set('gettingCustomerJobTypes', true)
        .remove('allFeesUpdated')
        .remove('getCustomerJobTypesSuccess');
    case GET_CUSTOMER_JOB_TYPES_SUCCESS:
      return state
        .remove('gettingCustomerJobTypes')
        .set('customerJobTypes', Immutable.fromJS(action.result.data))
        .set('getCustomerJobTypesSuccess', true);
    case GET_CUSTOMER_JOB_TYPES_FAIL:
      return state
        .remove('gettingCustomerJobTypes')
        .set('getCustomerJobTypesSuccess', false);
    /**
     * Handle a job type being selected/deselected
     */
    case SELECT_JOB_TYPE:
      return state
        .set('fees', handleJobTypeSelect(state, action.jobType));
    /**
     * Set job type fee
     */
    case SET_JOB_TYPE_FEE:
      return setJobTypeFee(state, action);
    /**
     * Save job type fees
     */
    case SAVE_JOB_TYPES:
      return state
        .set('savingJobTypes', true)
        .remove('saveJobTypesSuccess');
    case SAVE_JOB_TYPES_SUCCESS:
      let dbFees = state.get('jobTypesInBackend');
      // New fees
      action.result.forEach(fee => {
        if (fee.body) {
          dbFees = dbFees.push(fee.body.id);
        }
      });
      // Deleted fees
      action.batchRequests.forEach(fee => {
        if (/DELETE/.test(fee.url)) {
          dbFees = dbFees.filter(feeId => feeId !== fee.body);
        }
      });
      // Add to exists in backend
      return state
        .remove('savingJobTypes')
        .set('saveJobTypesSuccess', true)
        .set('jobTypesInBackend', dbFees);
    case SAVE_JOB_TYPES_FAIL:
      return state
        .remove('savingJobTypes')
        .set('saveJobTypesSuccess', false);
    /**
     * Change search value
     */
    case CHANGE_SEARCH_VAL:
      let searchState = state;
      const isCustomer = action.isCustomer;
      // Save customer job types on initial search
      if (!state.get('originalCustomerJobTypes')) {
        searchState = searchState.set('originalCustomerJobTypes', searchState.get('customerJobTypes'));
      }
      const originalJobTypes = searchState.get('originalCustomerJobTypes');
      // Search case insensitive
      const searchVal = new RegExp(action.value, 'i');
      let filtered;
      // Customer form
      if (action.formType === 'customerFormSearch') {
        filtered = originalJobTypes.filter(jobType => searchVal.test(jobType.get('title')));
        // Industry form
      } else {
        filtered = originalJobTypes.filter(jobType => searchVal.test(jobType.getIn(['local', 'title'])));
      }
      // customerFormSearch
      return searchState
        .set(action.formType, action.value)
        .set(isCustomer ? 'customerJobTypes' : 'jobTypes', filtered)
        .set('sorts', startSortVal);
    /**
     * Sort by a column
     */
    case SORT_COLUMN:
      let stateForSorting = state;
      // Sort vals
      const originalSortVal = stateForSorting.getIn(['sorts', action.column]);
      let newSortVal;
      if (originalSortVal === -1) {
        newSortVal = 1;
      } else if (originalSortVal === 1 || originalSortVal === 0) {
        newSortVal = -1;
      }
      // Reset all search values
      stateForSorting = stateForSorting.set('sorts', startSortVal);
      // Update this sort val
      stateForSorting = stateForSorting.setIn(['sorts', action.column], newSortVal);
      // Default
      let jobTypesForSorting = state.get('customerJobTypes');
      const feeMap = createFeeMap(state.get('fees'));
      // Enabled
      if (action.column === 'enabled') {
        jobTypesForSorting = sortByEnabled(jobTypesForSorting, feeMap, newSortVal);
        // Industry form name
      } else if (action.column === 'industryForm') {
        jobTypesForSorting = sortByTitle(jobTypesForSorting, ['local', 'title'], newSortVal);
        // Customer form
      } else if (action.column === 'customerForm') {
        jobTypesForSorting = sortByTitle(jobTypesForSorting, ['title'], newSortVal);
      } else {
        jobTypesForSorting = sortByFee(jobTypesForSorting, feeMap, newSortVal);
      }
      stateForSorting = stateForSorting.set('customerJobTypes', jobTypesForSorting);
      return stateForSorting;
    /**
     * Decline invitation
     */
    case DECLINE_INVITATION:
      return state
        .set('decliningInvitation', true)
        .remove('declineInvitationSuccess');
    case DECLINE_INVITATION_SUCCESS:
      return filterInvitations(state, action)
        .remove('decliningInvitation')
        .set('declineInvitationSuccess', true)
        .set('pendingInvitationsTotal', Math.max(0, state.get('pendingInvitationsTotal') - 1));
    case DECLINE_INVITATION_FAIL:
      return state
        .remove('decliningInvitation')
        .set('declineInvitationSuccess', false)
        .set('declineInvitationError', action.error.message);
    /**
     * Submit ACH
     */
    case SUBMIT_ACH:
      return state
        .set('submittingAch', true)
        .remove('submitAchSuccess');
    case SUBMIT_ACH_SUCCESS:
      return state
        .remove('submittingAch')
        .set('submitAchSuccess', true)
        .setIn(['ach', 'errors'], Immutable.Map());
    case SUBMIT_ACH_FAIL:
      return state
        .remove('submittingAch')
        .set('submitAchSuccess', false)
        .setIn(['ach', 'errors'], backendErrorsImmutable(action));
    /**
     * Upload sample report
     */
    case FILE_UPLOAD:
      return state
        .set('uploadingSample', true)
        .remove('submitSampleSuccess');
    case FILE_UPLOAD_SUCCESS:
      const uploadedFile = Immutable.fromJS(action.result);
      // New state
      let newState = state
        .remove('uploadingSample')
        .set('submitSampleSuccess', true);
      // Resume
      if (action.docType[0].indexOf('resume') !== -1) {
        newState = newState
          .setIn(['resume', ...action.docType], uploadedFile)
          .removeIn(['resume', 'errors', 'noData']);
      // Sample reports
      } else if (action.docType[0].indexOf('sample') !== -1) {
        newState = newState
          .setIn(['sampleReports', 'form', ...action.docType], uploadedFile)
          .removeIn(['sampleReports', 'errors', 'noData']);
      }
      return newState;
    case FILE_UPLOAD_FAIL:
      return state
        .remove('uploadingSample')
        .set('submitSampleSuccess', false);
    /**
     * Update appraiser
     */
    case UPDATE_APPRAISER:
      return state
        .set('updatingAppraiser', true)
        .remove('updateAppraiserSuccess');
    case UPDATE_APPRAISER_SUCCESS:
      return state
        .remove('updatingAppraiser')
        .set('updateAppraiserSuccess', true);
    case UPDATE_APPRAISER_FAIL:
      return state
        .remove('updatingAppraiser')
        .set('updateAppraiserSuccess', false);
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
 * Retrieve invitations for a user
 *
 * @param {number} appraiserId
 * @param {number} page
 */
export function getInvitations(appraiserId, page = 1) {
  return {
    types: [GET_INVITATIONS, GET_INVITATIONS_SUCCESS, GET_INVITATIONS_FAIL],
    promise: client => client.get(`dev:/appraisers/${appraiserId}/invitations?page=${page}&filter[status]=pending`)
  };
}

/**
 * Retrieve company invitations for a user
 *
 * @param {number} appraiserId
 * @param {boolean} total Calculate total
 */
export function getCompanyInvitations(appraiserId, total = false) {
  return {
    types: [GET_COMPANY_INVITATIONS, GET_COMPANY_INVITATIONS_SUCCESS, GET_COMPANY_INVITATIONS_FAIL],
    promise: client => client.get(`dev:/appraisers/${appraiserId}/company-invitations`, {
      data: {
        headers: {
          Include: 'branch.company'
        }
      }
    }),
    total
  };
}

/**
 * Get the total number of pending invitations
 *
 * @param {number} appraiserId Appraiser ID
 */
export function getPendingInvitationsTotal(appraiserId) {
  return {
    types: [GET_PENDING_INVITATIONS_TOTAL, GET_PENDING_INVITATIONS_TOTAL_SUCCESS, GET_PENDING_INVITATIONS_TOTAL_FAIL],
    // We add perPage=1 in an attempt to get faster response from the server
    promise: client => client.get(`dev:/appraisers/${appraiserId}/invitations?perPage=1&filter[status]=pending`)
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
 * Accept an invitation
 * @param appraiserId Appraiser ID
 * @param invitationId Invitation ID
 * @param company Company invitation
 */
export function acceptInvitation(appraiserId, invitationId, company = false) {
  const url = company ? 'company-invitations' : 'invitations';
  return {
    types: [ACCEPT_INVITATION, ACCEPT_INVITATION_SUCCESS, ACCEPT_INVITATION_FAIL],
    promise: client => client.post(`dev:/appraisers/${appraiserId}/${url}/${invitationId}/accept`),
    invitationId,
    company
  };
}

/**
 * Decline an invitation
 * @param appraiserId Appraiser ID
 * @param invitationId Invitation ID
 * @param company Company invitation
 */
export function declineInvitation(appraiserId, invitationId, company = false) {
  const url = company ? 'company-invitations' : 'invitations';
  return {
    types: [DECLINE_INVITATION, DECLINE_INVITATION_SUCCESS, DECLINE_INVITATION_FAIL],
    promise: client => client.post(`dev:/appraisers/${appraiserId}/${url}/${invitationId}/decline`),
    invitationId,
    company
  };
}

/**
 * Get appraiser's ACH info
 * @param appraiserId
 */
export function getAch(appraiserId) {
  return {
    types: [GET_ACH, GET_ACH_SUCCESS, GET_ACH_FAIL],
    promise: (client) => client.get(`dev:/appraisers/${appraiserId}/ach`)
  };
}

/**
 * Get appraisers (for resume and sample reports)
 * @param appraiserId
 */
export function getAppraiser(appraiserId) {
  return {
    types: [GET_APPRAISER, GET_APPRAISER_SUCCESS, GET_APPRAISER_FAIL],
    promise: client => client.get(`dev:/appraisers/${appraiserId}`, {
      data: {
        headers: {
          Include: 'sampleReports,qualifications'
        }
      }
    })
  };
}

/**
 * Get industry job types
 */
export function getJobTypes() {
  return {
    types: [GET_JOB_TYPES, GET_JOB_TYPES_SUCCESS, GET_JOB_TYPES_FAIL],
    promise: client => client.get('dev:/job-types')
  };
}

/**
 * Retrieve job types for the inviting customer
 */
export function getCustomerJobTypes(appraiserId, customerId) {
  return {
    types: [GET_CUSTOMER_JOB_TYPES, GET_CUSTOMER_JOB_TYPES_SUCCESS, GET_CUSTOMER_JOB_TYPES_FAIL],
    promise: client => client.get(`dev:/appraisers/${appraiserId}/customers/${customerId}/job-types?filter[isPayable]=true`)
  };
}

/**
 * Get customer fees
 * @param appraiserId
 * @param customerId
 */
export function getCustomerFees(appraiserId, customerId) {
  return {
    types: [GET_FEES, GET_FEES_SUCCESS, GET_FEES_FAIL],
    promise: client => client.get(`dev:/appraisers/${appraiserId}/customers/${customerId}/fees`),
    customer: !!customerId
  };
}

/**
 * Get default job type fees set by this appraiser
 * @param appraiserId
 */
export function getDefaultFees(appraiserId) {
  return {
    types: [GET_DEFAULT_FEES, GET_DEFAULT_FEES_SUCCESS, GET_DEFAULT_FEES_FAIL],
    promise: client => client.get(`dev:/appraisers/${appraiserId}/fees`)
  };
}

/**
 * Apply default fees to the shown list of job types
 */
export function applyDefaultFees() {
  return {
    type: APPLY_DEFAULT_FEES
  };
}

/**
 * Select a job type
 */
export function selectJobType(jobType) {
  return {
    type: SELECT_JOB_TYPE,
    jobType
  };
}

/**
 * Set a fee value
 */
export function setFeeValue(jobType, value) {
  return {
    type: SET_JOB_TYPE_FEE,
    jobType,
    value
  };
}

/**
 * Save changes
 * @param appraiserId User ID
 * @param customerId Customer ID
 * @param fee Request object
 * @param method Request method
 * @param feeId Existing fee ID
 */
export function createJobTypeRequest(appraiserId, customerId, fee, method, feeId) {
  let url, body;
  switch (method) {
    case 'post':
      url = `/api/v2.0/appraisers/${appraiserId}/customers/${customerId}/fees`;
      body = fee;
      break;
    case 'patch':
      url = `/api/v2.0/appraisers/${appraiserId}/customers/${customerId}/fees/${feeId}`;
      body = {
        amount: fee.amount,
        id: feeId
      };
      break;
    case 'delete':
      url = `/api/v2.0/appraisers/${appraiserId}/customers/${customerId}/fees/${feeId}`;
      body = feeId;
      break;
  }
  return {
    url: `${method.toUpperCase()} ${url}`,
    body
  };
}

export function saveJobTypeFees(batchRequests, customerId) {
  return {
    types: [SAVE_JOB_TYPES, SAVE_JOB_TYPES_SUCCESS, SAVE_JOB_TYPES_FAIL],
    promise: client => client.post(`batch:/batch`, {
      data: batchRequests
    }),
    batchRequests,
    customerId
  };
}

/**
 * Change search value, display results
 * @param formType Whether customer or industry forms
 * @param value Search value
 * @param isCustomer If on a customer, not default
 */
export function changeSearchValue(formType, value, isCustomer) {
  return {
    type: CHANGE_SEARCH_VAL,
    formType,
    value,
    isCustomer
  };
}

/**
 * Sort by a specific column
 * @param column
 * @param isCustomer
 */
export function sortColumn(column, isCustomer) {
  return {
    type: SORT_COLUMN,
    column,
    isCustomer
  };
}

/**
 * Submit ACH
 */
export function submitAch(appraiserId, data) {
  return {
    types: [SUBMIT_ACH, SUBMIT_ACH_SUCCESS, SUBMIT_ACH_FAIL],
    promise: client => client.put(`dev:/appraisers/${appraiserId}/ach`, {
      data
    })
  };
}

/**
 * Upload file during invitation process
 */
export function uploadFile(docType, document) {
  return fileUpload([FILE_UPLOAD, FILE_UPLOAD_SUCCESS, FILE_UPLOAD_FAIL], docType, document);
}

/**
 * Update appraiser for resume and sample reports
 * @param appraiserId
 * @param appraiser Appraiser for update
 */
export function updateAppraiser(appraiserId, appraiser) {
  return {
    types: [UPDATE_APPRAISER, UPDATE_APPRAISER_SUCCESS, UPDATE_APPRAISER_FAIL],
    promise: client => client.patch(`dev:/appraisers/${appraiserId}`, {
      data: appraiser
    })
  };
}

/**
 * Remove a property
 * @param namePath Array path to prop
 */
export function removeProp(...namePath) {
  return {
    promise: () => new Promise(resolve => resolve()),
    type: REMOVE_PROP,
    namePath
  };
}
