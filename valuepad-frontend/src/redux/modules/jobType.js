export const GET_JOB_TYPES = 'vp/jobType/GET_JOB_TYPES';
export const GET_JOB_TYPES_SUCCESS = 'vp/jobType/GET_JOB_TYPES_SUCCESS';
export const GET_JOB_TYPES_FAIL = 'vp/jobType/GET_JOB_TYPES_FAIL';
export const GET_CUSTOMERS = 'vp/jobType/GET_CUSTOMERS';
export const GET_CUSTOMERS_SUCCESS = 'vp/jobType/GET_CUSTOMERS_SUCCESS';
export const GET_CUSTOMERS_FAIL = 'vp/jobType/GET_CUSTOMERS_FAIL';
export const GET_FEES = 'vp/jobType/GET_FEES';
export const GET_FEES_SUCCESS = 'vp/jobType/GET_FEES_SUCCESS';
export const GET_FEES_FAIL = 'vp/jobType/GET_FEES_FAIL';
export const GET_CUSTOMER_JOBTYPES = 'vp/jobType/GET_CUSTOMER_JOBTYPES';
export const GET_CUSTOMER_JOBTYPES_SUCCESS = 'vp/jobType/GET_CUSTOMER_JOBTYPES_SUCCESS';
export const GET_CUSTOMER_JOBTYPES_FAIL = 'vp/jobType/GET_CUSTOMER_JOBTYPES_FAIL';
export const SET_PROP = 'vp/jobType/SET_PROP';
export const SELECT_CUSTOMER = 'vp/jobType/SELECT_CUSTOMER';
export const ROW_SELECT = 'vp/jobType/ROW_SELECT';
export const SET_FEE_VALUE = 'vp/jobType/SET_FEE_VALUE';
export const SAVE = 'vp/jobType/SAVE';
export const SAVE_SUCCESS = 'vp/jobType/SAVE_SUCCESS';
export const SAVE_FAIL = 'vp/jobType/SAVE_FAIL';
export const APPLY_DEFAULT_FEES = 'vp/jobType/APPLY_DEFAULT_FEES';
export const APPLY_DEFAULT_FEES_SUCCESS = 'vp/jobType/APPLY_DEFAULT_FEES_SUCCESS';
export const APPLY_DEFAULT_FEES_FAIL = 'vp/jobType/APPLY_DEFAULT_FEES_FAIL';
// Get fee totals
export const GET_FEE_TOTALS = 'vp/jobType/GET_FEE_TOTALS';
export const GET_FEE_TOTALS_SUCCESS = 'vp/jobType/GET_FEE_TOTALS_SUCCESS';
export const GET_FEE_TOTALS_FAIL = 'vp/jobType/GET_FEE_TOTALS_FAIL';
// Change search value
export const CHANGE_SEARCH_VAL = 'vp/jobType/CHANGE_SEARCH_VAL';
// Sort by column
export const SORT_COLUMN = 'vp/jobType/SORT_COLUMN';
// Reset sorting
export const RESET_SORT = 'vp/jobType/RESET_SORT';
// AMC change fee location
export const CHANGE_LOCATION = 'vp/jobType/CHANGE_LOCATION';
// Get location fees
export const GET_AMC_LOCATION_FEES = 'vp/jobType/GET_AMC_LOCATION_FEES';
export const GET_AMC_LOCATION_FEES_SUCCESS = 'vp/jobType/GET_AMC_LOCATION_FEES_SUCCESS';
export const GET_AMC_LOCATION_FEES_FAIL = 'vp/jobType/GET_AMC_LOCATION_FEES_FAIL';
// Save location fees
export const SAVE_AMC_LOCATION_FEES = 'vp/jobType/SAVE_AMC_LOCATION_FEES';
export const SAVE_AMC_LOCATION_FEES_SUCCESS = 'vp/jobType/SAVE_AMC_LOCATION_FEES_SUCCESS';
export const SAVE_AMC_LOCATION_FEES_FAIL = 'vp/jobType/SAVE_AMC_LOCATION_FEES_FAIL';
// Get counties
export const GET_COUNTIES = 'vp/jobType/GET_COUNTIES';
export const GET_COUNTIES_SUCCESS = 'vp/jobType/GET_COUNTIES_SUCCESS';
export const GET_COUNTIES_FAIL = 'vp/jobType/GET_COUNTIES_FAIL';
// Get zips
export const GET_ZIPS = 'vp/jobType/GET_ZIPS';
export const GET_ZIPS_SUCCESS = 'vp/jobType/GET_ZIPS_SUCCESS';
export const GET_ZIPS_FAIL = 'vp/jobType/GET_ZIPS_FAIL';
// Save AMC fees
export const SAVE_AMC_FEES = 'vp/jobType/SAVE_AMC_FEES';
export const SAVE_AMC_FEES_SUCCESS = 'vp/jobType/SAVE_AMC_FEES_SUCCESS';
export const SAVE_AMC_FEES_FAIL = 'vp/jobType/SAVE_AMC_FEES_FAIL';

// Default customer
export const DEFAULT_CUSTOMER = 0;

// Regex for a valid fee value
const feeValueRegex = /^\d+(\.\d{0,2})?$/;

import Immutable from 'immutable';
import queryString from 'query-string';
import {setProp as setPropInherited} from 'helpers/genericFunctions';

/**
 * Key map for getting/saving by location
 */
const locationKeyMap = {
  states: {
    propPath: ['state', 'code'],
    reducerVal: 'amcState'
  },
  counties: {
    propPath: ['county', 'id'],
    reducerVal: 'amcCounty'
  },
  zips: {
    propPath: ['zip'],
    reducerVal: 'amcZip'
  }
};

// Unsorted
export const startSortVal = Immutable.fromJS({
  enabled: 0,
  industryForm: -1,
  fee: 0,
  location: 0
});

export const startSortValCustomer = Immutable.fromJS({
  enabled: 0,
  customerForm: -1,
  industryForm: 0,
  fee: 0,
  location: 0
});

export const unsetSortVal = Immutable.fromJS({
  enabled: 0,
  customerForm: 0,
  industryForm: 0,
  fee: 0,
  location: 0
});

const initialState = Immutable.fromJS({
  jobTypes: [],
  customerJobTypes: [],
  fees: [],
  customerFees: [],
  rowValues: {},
  industryFormSearch: '',
  customerFormSearch: '',
  sorts: startSortVal,
  initialFees: false,
  selectedCustomer: DEFAULT_CUSTOMER,
  // AMC fees which exist in the backend
  amcFeesInBackend: {},
  // AMC location values
  amcState: {},
  counties: [],
  amcCounty: {},
  zips: [],
  amcZip: {},
  totals: {},
  backendTotals: {},
  defaultFeesApplied: false
});

export default function reducer(state = initialState, action = {}) {
  switch (action.type) {
    /**
     * Set a prop manually
     */
    case SET_PROP:
      return state.setIn(action.name, action.value);
    /**
     * Retrieve job types
     */
    case GET_JOB_TYPES:
      return state
        .set('gettingJobTypes', true)
        .remove('getJobTypesSuccess');
    case GET_JOB_TYPES_SUCCESS:
      const jobTypesData = Immutable.fromJS(action.result.data);
      return state
        .set('getJobTypesSuccess', true)
        .remove('gettingJobTypes')
        .set('jobTypes', sortByTitle(jobTypesData, ['title'], -1));
    case GET_JOB_TYPES_FAIL:
      return state
        .remove('gettingJobTypes')
        .set('getJobTypesSuccess', false);
    /**
     * Retrieve customer job types
     */
    case GET_CUSTOMER_JOBTYPES:
      return state
        .set('gettingJobTypes', true)
        .remove('getJobTypesSuccess');
    case GET_CUSTOMER_JOBTYPES_SUCCESS:
      const customerJobTypesData = Immutable.fromJS(action.result.data);
      return state
        .set('getJobTypesSuccess', true)
        .remove('gettingJobTypes')
        .set('customerJobTypes', sortByTitle(customerJobTypesData, ['title'], -1))
        .remove('originalCustomerJobTypes');
    case GET_CUSTOMER_JOBTYPES_FAIL:
      return state
        .remove('gettingJobTypes')
        .set('getJobTypesSuccess', false);
    /**
     * Retrieve customers
     */
    case GET_CUSTOMERS:
      return state
        .set('gettingCustomers', true);
    case GET_CUSTOMERS_SUCCESS:
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
        .remove('gettingCustomers')
        .set('customers', sortedCustomers)
        .set('getCustomersSuccess', true);
    case GET_CUSTOMERS_FAIL:
      return state
        .remove('gettingCustomers')
        .set('getCustomersSuccess', false);
    /**
     * Get fees
     */
    case GET_FEES:
      let defaultFees = [];
      // Keep default fees
      if (action.customer && state.get('fees')) {
        defaultFees = state.get('fees').map(fee => fee.set('default', true));
      }
      return state
        .set('gettingFees', true)
        .set('defaultFees', defaultFees)
        .set('initialFees', true)
        .remove('getFeesSuccess');
    case GET_FEES_SUCCESS:
      const feeProp = action.customer ? 'customerFees' : 'fees';
      const fees = Immutable.fromJS(action.result.data);
      let newFeeState = state
        .set(feeProp, fees)
        .remove('gettingFees')
        .set('getFeesSuccess', true);
      // Set sorted by customer form
      if (action.customer) {
        newFeeState = newFeeState
          .set('sorts', action.customer ? startSortValCustomer : startSortVal);
      }
      // Store AMC fees that exist in backend
      if (action.userType === 'amc') {
        let backendFeeMap = Immutable.Map();
        fees.forEach(fee => {
          backendFeeMap = backendFeeMap.set(fee.getIn(['jobType', 'id']), true);
        });
        newFeeState = newFeeState.set('amcFeesInBackend', backendFeeMap);
      }
      return newFeeState;
    case GET_FEES_FAIL:
      return state
        .remove('gettingFees')
        .set('getFeesSuccess', false);
    /**
     * Set fees
     */
    case SET_FEE_VALUE:
      let hasError = false;
      if (!feeValueRegex.test(action.value)) {
        hasError = true;
      }
      const feeValueProp = action.customerId === DEFAULT_CUSTOMER ? 'fees' : 'customerFees';
      const selectedJobTypeForFee = action.jobType;
      let feeIndex;
      // Find index of fee
      state.get(feeValueProp).forEach((fee, index) => {
        if (fee.getIn(['jobType', 'id']) === selectedJobTypeForFee.get('id')) {
          feeIndex = index;
        }
      });
      let stateAfterFeeSet = state
        .setIn([feeValueProp, feeIndex, 'amount'], action.value);
      // Display error
      if (hasError) {
        stateAfterFeeSet = stateAfterFeeSet
          .setIn([feeValueProp, feeIndex, 'error'], true);
      } else {
        stateAfterFeeSet = stateAfterFeeSet
          .removeIn([feeValueProp, feeIndex, 'error']);
      }
      return stateAfterFeeSet;
    /**
     * Select customer
     */
    case SELECT_CUSTOMER:
      return state
        .set('selectedCustomer', action.customerId)
        .set('totals', state.get('backendTotals'));
    /**
     * Handle a row being selected/deselected
     */
    case ROW_SELECT:
      const selectedCustomer = state.get('selectedCustomer');
      const currentTotal = state.getIn(['totals', selectedCustomer]) || 0;
      const customer = action.customer;
      const existingFeeProp = customer ? 'customerFees' : 'fees';
      const selectedJobType = action.jobType;
      const selectedJobTypeId = selectedJobType.get('id');
      let existingFees;
      // Click enable for customer
      existingFees = state.get(existingFeeProp);
      // See if this is already clicked or not
      const alreadySet = existingFees.filter(fee => {
        return fee.getIn(['jobType', 'id']) === selectedJobTypeId && !fee.get('removed');
      });
      let newTotal;
      newTotal = currentTotal + (alreadySet.count() === 1 ? -1 : 1);
      // @todo Handle exists in backend
      let found = false;
      existingFees.forEach((fee, index) => {
        if (fee.getIn(['jobType', 'id']) === selectedJobTypeId) {
          found = true;
          existingFees = existingFees.setIn([index, 'removed'], !existingFees.getIn([index, 'removed']));
          return false;
        }
      });
      if (!found) {
        existingFees = existingFees.push(Immutable.fromJS({
          amount: 0,
          jobType: selectedJobType,
          removed: false
        }));
      }
      return state
        .set(existingFeeProp, existingFees)
        .setIn(['totals', selectedCustomer], newTotal);
    case SAVE:
      return state
        .set('saving', true)
        .remove('saveSuccess');
    case SAVE_SUCCESS:
      const batchResult = Immutable.fromJS(action.result);
      const requests = Immutable.fromJS(action.batchRequests);
      let updatedFeeList = Immutable.Map();
      let deletedList = Immutable.Map();
      // Find new fees
      batchResult.forEach(result => {
        if (result.getIn(['body', 'data'])) {
          result.getIn(['body', 'data']).forEach(fee => {
            updatedFeeList = updatedFeeList.set(fee.getIn(['jobType', 'id']), fee);
          });
        }
      });
      // Find deleted items
      requests.forEach(thisRequest => {
        if (/DELETE/.test(thisRequest.get('url'))) {
          const ids = queryString.parse(thisRequest.get('url').split('?')[1]).ids;
          ids.split(',').forEach(id => {
            deletedList = deletedList.set(parseInt(id, 10), true);
          });
        }
      });
      const feeListProp = action.customerId === DEFAULT_CUSTOMER ? 'fees' : 'customerFees';
      let feeList = state.get(feeListProp);
      // Add new fees
      feeList = feeList.map(fee => {
        const newFee = updatedFeeList.get(fee.getIn(['jobType', 'id']));
        if (newFee) {
          return newFee;
        }
        return fee;
        // Remove deleted fees
      }).filter(fee => !deletedList.get(fee.get('id')));
      return state
        .remove('saving')
        .set('saveSuccess', true)
        .set(feeListProp, feeList)
        .set('backendTotals', state.get('totals'));
    case SAVE_FAIL:
      return state
        .remove('saving')
        .set('saveSuccess', true);
    /**
     * Get fee totals to display next to customer names
     */
    case GET_FEE_TOTALS:
      return state
        .set('gettingFeeTotals', true)
        .remove('getFeeTotalsSuccess');
    case GET_FEE_TOTALS_SUCCESS:
      // Set default customer
      const totals = Immutable.fromJS(action.result.data).map(total => {
        if (total.get('customer') === null) {
          return total.set('customer', DEFAULT_CUSTOMER);
        }
        return total.set('customer', total.getIn(['customer', 'id']));
      });
      let totalsMap = Immutable.Map();
      totals.forEach(total => {
        totalsMap = totalsMap.set(total.get('customer'), total.get('enabled'));
      });
      return state
        .remove('gettingFeeTotals')
        .set('getFeeTotalsSuccess', true)
        .set('totals', totalsMap)
        .set('backendTotals', totalsMap);
    case GET_FEE_TOTALS_FAIL:
      return state
        .remove('gettingFeeTotals')
        .set('getFeeTotalsSuccess', false);
    case APPLY_DEFAULT_FEES:
      return state
        .set('applyingDefaultFees', true)
        .remove('applyDefaultFeesSuccess');
    /**
     * Apply default fees to the selected customer
     */
    case APPLY_DEFAULT_FEES_SUCCESS:
      return state
        .remove('applyingDefaultFees')
        .set('applyDefaultFeesSuccess', true)
        .set('defaultFeesApplied', true);
    case APPLY_DEFAULT_FEES_FAIL:
      return state
        .remove('applyingDefaultFees')
        .set('applyDefaultFeesSuccess', false);
    /**
     * Change search value
     */
    case CHANGE_SEARCH_VAL:
      let searchState = state;
      const isCustomer = action.isCustomer;
      // Search customer forms
      if (isCustomer) {
        // Save customer job types on initial search
        if (!state.get('originalCustomerJobTypes')) {
          searchState = searchState.set('originalCustomerJobTypes', searchState.get('customerJobTypes'));
        }
      // Search industry forms
      } else {
        // Save original job types on initial search
        if (!state.get('originalJobTypes')) {
          searchState = searchState.set('originalJobTypes', searchState.get('jobTypes'));
        }
      }
      const originalJobTypes = searchState.get(isCustomer ? 'originalCustomerJobTypes' : 'originalJobTypes');
      // Search case insensitive
      const searchVal = new RegExp(action.value, 'i');
      let filtered;
      // Default form search
      if (!isCustomer) {
        filtered = originalJobTypes.filter(jobType => searchVal.test(jobType.get('title')));
      } else {
        // Customer form
        if (action.formType === 'customerFormSearch') {
          filtered = originalJobTypes.filter(jobType => searchVal.test(jobType.get('title')));
        // Industry form
        } else {
          filtered = originalJobTypes.filter(jobType => searchVal.test(jobType.getIn(['local', 'title'])));
        }
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
      newSortVal = originalSortVal === -1 ? 1 : -1;
      // Reset all search values
      stateForSorting = stateForSorting.set('sorts', unsetSortVal);
      // Update this sort val
      stateForSorting = stateForSorting.setIn(['sorts', action.column], newSortVal);
      // Default
      if (!action.isCustomer) {
        let jobTypesForSorting = state.get('jobTypes');
        const feeMap = createFeeMap(state.get('fees'));
        // Enabled
        if (action.column === 'enabled') {
          jobTypesForSorting = sortByEnabled(jobTypesForSorting, feeMap, newSortVal);
        // Industry form name
        } else if (action.column === 'industryForm') {
          jobTypesForSorting = sortByTitle(jobTypesForSorting, ['title'], newSortVal);
        } else {
          jobTypesForSorting = sortByFee(jobTypesForSorting, feeMap, newSortVal);
        }
        stateForSorting = stateForSorting
          .set('jobTypes', jobTypesForSorting);
      } else {
        let jobTypesForSorting = state.get('customerJobTypes');
        const feeMap = createFeeMap(state.get('customerFees'));
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
      }
      return stateForSorting;
    /**
     * Reset sorting
     */
    case RESET_SORT:
      return state.set('sorts', startSortVal);
    /**
     * Change fee location
     */
    case CHANGE_LOCATION:
      // Change location based on job type ID
      const locationFees = state.get('fees').map(fee => {
        if (fee.getIn(['jobType', 'id']) === action.jobTypeId) {
          fee = fee.set('qualifier', action.location);
        }
        return fee;
      });
      return state
        .set('fees', locationFees);
    /**
     * Get counties for a state
     */
    case GET_COUNTIES:
      return state
        .set('gettingCounties', true)
        .remove('getCountiesSuccess');
    case GET_COUNTIES_SUCCESS:
      return state
        .set('counties', Immutable.fromJS(action.result.data))
        .remove('gettingCounties')
        .set('getCountiesSuccess', true);
    case GET_COUNTIES_FAIL:
      return state
        .remove('gettingCounties')
        .set('getCountiesSuccess', false);
    /**
     * Get zips for a state
     */
    case GET_ZIPS:
      return state
        .set('gettingZips', true)
        .remove('getZipsSuccess');
    case GET_ZIPS_SUCCESS:
      return state
        .set('amcZip', Immutable.Map())
        .set('zips', Immutable.fromJS(action.result.data))
        .remove('gettingZips')
        .set('getZipsSuccess', true);
    case GET_ZIPS_FAIL:
      return state
        .remove('gettingZips')
        .set('getZipsSuccess', false);
    /**
     * Save AMC fees
     */
    case SAVE_AMC_FEES:
      return state
        .set('savingAmcFees', true)
        .remove('saveAmcFeesSuccess');
    case SAVE_AMC_FEES_SUCCESS:
      let newFeesInBackend = Immutable.Map();
      action.result.data.forEach(fee => {
        newFeesInBackend = newFeesInBackend.set(fee.jobType.id, true);
      });
      return state
        .remove('savingAmcFees')
        .set('amcFeesInBackend', newFeesInBackend)
        .set('saveAmcFeesSuccess', true);
    case SAVE_AMC_FEES_FAIL:
      return state
        .remove('savingAmcFees')
        .set('saveAmcFeesSuccess', false);
    /**
     * Get AMC location fees
     */
    case GET_AMC_LOCATION_FEES:
      return state
        .set('gettingAmcLocationFees', true)
        .remove('getAmcLocationFeesSuccess');
    case GET_AMC_LOCATION_FEES_SUCCESS:
      const keyMap = locationKeyMap[action.locationType];
      const locationResult = Immutable.fromJS(action.result.data);
      let backendLocationFees = Immutable.Map();
      locationResult.forEach(fee => {
        backendLocationFees = backendLocationFees.set(fee.getIn(keyMap.propPath), fee.get('amount'));
      });
      return state
        .remove('gettingAmcLocationFees')
        .set(keyMap.reducerVal, backendLocationFees)
        .set('getAmcLocationFeesSuccess', true);
    case GET_AMC_LOCATION_FEES_FAIL:
      return state
        .remove('gettingAmcLocationFees')
        .set('getAmcLocationFeesSuccess', false);
    /**
     * Save AMC location fees
     */
    case SAVE_AMC_LOCATION_FEES:
      return state
        .set('settingAmcLocationFees', true)
        .remove('setAmcLocationFeesSuccess');
    case SAVE_AMC_LOCATION_FEES_SUCCESS:
      return state
        .remove('settingAmcLocationFees')
        .set('setAmcLocationFeesSuccess', true);
    case SAVE_AMC_LOCATION_FEES_FAIL:
      return state
        .remove('settingAmcLocationFees')
        .set('setAmcLocationFeesSuccess', false);
    default:
      return state;
  }
}

/**
 * Sort by fee value
 * @param jobTypes Job types list
 * @param feeMap Map of fees to job type
 * @param sortVal Asc or desc
 * @returns {*}
 */
export function sortByFee(jobTypes, feeMap, sortVal) {
  return jobTypes.sort((a, b) => {
    const aFee = feeMap.get(a.get('id'));
    const bFee = feeMap.get(b.get('id'));
    // Two fees
    if (aFee && bFee) {
      if (aFee.get('amount') === bFee.get('amount')) {
        return 0;
      }
      return aFee.get('amount') > bFee.get('amount') ? sortVal : sortVal * -1;
      // Single fee
    } else if (bFee && !aFee) {
      return sortVal * -1;
    } else if (aFee && !bFee) {
      return sortVal;
    } else {
      return 0;
    }
  });
}

/**
 * Sort by enabled/disabled
 * @param jobTypes Job types list
 * @param feeMap Map of fees to job type
 * @param sortVal Asc or desc
 * @returns {*}
 */
export function sortByEnabled(jobTypes, feeMap, sortVal) {
  return jobTypes.sort((a, b) => {
    const aSelected = feeMap.get(a.get('id'));
    const bSelected = feeMap.get(b.get('id'));
    if (aSelected && !bSelected) {
      return sortVal;
    } else if (bSelected && !aSelected) {
      return sortVal * -1;
    } else {
      return 0;
    }
  });
}

/**
 * Sort job types by title
 * @param jobTypes Job type list
 * @param titlePath Path to title property
 * @param sortVal Asc or desc
 */
export function sortByTitle(jobTypes, titlePath, sortVal) {
  return jobTypes.sort((a, b) => {
    a = a.getIn(titlePath) ? a.getIn(titlePath).toLowerCase() : 'zzzzz';
    b = b.getIn(titlePath) ? b.getIn(titlePath).toLowerCase() : 'zzzzz';
    if (a === b) {
      return 0;
    }
    return a > b ? sortVal * -1 : sortVal;
  });
}

/**
 * Retrieve job types
 */
export function getJobTypes() {
  return {
    types: [GET_JOB_TYPES, GET_JOB_TYPES_SUCCESS, GET_JOB_TYPES_FAIL],
    promise: client => client.get('dev:/job-types')
  };
}

/**
 * Retrieve job types for a specific customer
 * @param user User record
 * @param customerId
 */
export function getCustomerJobTypes(user, customerId) {
  let url;
  const userType = user.get('type');
  const userId = user.get('id');
  if (userType === 'customer') {
    url = `dev:/customers/${userId}/job-types?filter[isPayable]=true`;
  } else {
    url = `dev:/${userType}s/${userId}/customers/${customerId}/job-types?filter[isPayable]=true`;
  }
  return {
    types: [GET_CUSTOMER_JOBTYPES, GET_CUSTOMER_JOBTYPES_SUCCESS, GET_CUSTOMER_JOBTYPES_FAIL],
    promise: client => client.get(url),
    customerId
  };
}

/**
 * Retrieve the user type, accounting for companies
 * @param userType
 * @return {string}
 */
function getUserType(userType) {
  return userType[userType.length - 1] === 's' ? userType : userType + 's';
}

/**
 * Get customer fees
 * @param user User
 * @param customerId ID for customer getting fees
 * @param userType Appraiser or AMC
 */
export function getFees(user, customerId, userType) {
  const userId = typeof user === 'number' ? user : user.get('id');
  // Create user type
  userType = getUserType(userType || user.get('type'));
  // URL for default fees
  let url = `dev:/${userType}/${userId}/fees`;
  // Customer fees
  if (customerId) {
    url = `dev:/${userType}/${userId}/customers/${customerId}/fees`;
  }
  // Customer user type
  if (userType === 'customer') {
    url = `dev:/customers/${customerId}/appraisers/${userId}/fees`;
  }
  return {
    types: [GET_FEES, GET_FEES_SUCCESS, GET_FEES_FAIL],
    promise: client => client.get(url),
    customer: !!customerId,
    userType
  };
}

/**
 * Retrieve customers for the current appraiser
 * @param user User
 */
export function getCustomers(user) {
  return {
    types: [GET_CUSTOMERS, GET_CUSTOMERS_SUCCESS, GET_CUSTOMERS_FAIL],
    promise: client => client.get(`dev:/${user.get('type')}s/${user.get('id')}/customers`, {
      data: {
        headers: {
          Include: 'settings'
        }
      }
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
 * Set a property manually
 * @param value Prop value
 * @param name Prop name path
 */
export function setProp(value, ...name) {
  return setPropInherited(SET_PROP, value, ...name);
}

/**
 * Set a fee value
 */
export function setFeeValue(jobType, value, customerId) {
  return {
    type: SET_FEE_VALUE,
    jobType,
    value,
    customerId
  };
}


/**
 * Handle a row being selected/deselected
 */
export function handleRowSelect(jobType, customer) {
  return {
    type: ROW_SELECT,
    jobType,
    customer
  };
}

/**
 * Save changes
 * @param user User record
 * @param customerId ID of customer being modified
 * @param fees Fee records
 * @param requestType Type of request (PUT, PATCH, etc)
 */
export function createBatchRequests(user, customerId, fees, requestType) {
  let url, method;
  const userId = user.get('id');
  const userType = user.get('type');
  const methodMapping = {
    update: 'patch',
    delete: 'delete',
    new: 'post'
  };

  method = methodMapping[requestType];

  if (!method) {
    throw new Error('Could not determine fee update method');
  }

  if (customerId !== DEFAULT_CUSTOMER) {
    url = `/api/v2.0/${userType}s/${userId}/customers/${customerId}/fees`;
  } else {
    url = `/api/v2.0/${userType}s/${userId}/fees`;
  }

  const body = {};

  if (requestType === 'delete') {
    url += '?ids=' + fees.map(fee => fee.get('id')).join(',');
  } else {
    body.bulk = fees.map(fee => {
      const x = {amount: fee.get('amount')};

      if (requestType === 'new') {
        x.jobType = fee.get('jobType');
      } else {
        x.id = fee.get('id');
      }

      return x;
    });
  }

  return {
    url: `${method.toUpperCase()} ${url}`,
    body
  };
}

/**
 * Save changes as a batch
 * @param batchRequests Array of requests
 * @param customerId ID of current customer
 */
export function saveChanges(batchRequests, customerId) {
  return {
    types: [SAVE, SAVE_SUCCESS, SAVE_FAIL],
    promise: client => client.post(`batch:/batch`, {
      data: batchRequests
    }),
    batchRequests,
    customerId
  };
}

/**
 * Save changes for company
 * @param companyId Company Id
 * @param data Complete jobtype data
 */
export function saveProductsCompany(companyId, data) {
  return {
    types: [SAVE, SAVE_SUCCESS, SAVE_FAIL],
    promise: client => client.put(`dev:/companies/${companyId}/fees`, {
      data
    })
  };
}

/**
 * Save AMC fees
 * @param amcId AMC ID
 * @param data Updated fees
 */
export function saveAmcFees(amcId, data) {
  return {
    types: [SAVE_AMC_FEES, SAVE_AMC_FEES_SUCCESS, SAVE_AMC_FEES_FAIL],
    promise: client => client.put(`dev:/amcs/${amcId}/fees`, {
      data: {
        data
      }
    })
  };
}

/**
 * Get AMC location fees
 * @param amcId AMC ID
 * @param jobTypeId Job type ID
 * @param type Location type
 * @param stateCode Currently selected state
 * @param customerId Customer ID
 */
export function getAmcLocationFees(amcId, jobTypeId, type, stateCode, customerId) {
  let url;
  const isCustomer = customerId !== DEFAULT_CUSTOMER;
  if (type === 'states') {
    url = isCustomer ? `/amcs/${amcId}/customers/${customerId}/fees/${jobTypeId}/${type}` :
          `/amcs/${amcId}/fees/${jobTypeId}/${type}`;
  } else {
    url = isCustomer ? `/amcs/${amcId}/customers/${customerId}/fees/${jobTypeId}/states/${stateCode}/${type}` :
          `/amcs/${amcId}/fees/${jobTypeId}/states/${stateCode}/${type}`;
  }
  return {
    types: [GET_AMC_LOCATION_FEES, GET_AMC_LOCATION_FEES_SUCCESS, GET_AMC_LOCATION_FEES_FAIL],
    promise: client => client.get(`dev:${url}`),
    locationType: type
  };
}

/**
 * Save AMC county fees
 * @param amcId AMC ID
 * @param jobTypeId Job type ID
 * @param data County fees
 * @param type Location type
 * @param stateCode State code for counties and zips
 * @param customerId Customer ID
 */
export function setAmcLocationFees(amcId, jobTypeId, data, type, stateCode = null, customerId) {
  let url;
  const isCustomer = customerId !== DEFAULT_CUSTOMER;
  if (type === 'states') {
    url = isCustomer ? `/amcs/${amcId}/customers/${customerId}/fees/${jobTypeId}/${type}` : `/amcs/${amcId}/fees/${jobTypeId}/${type}`;
  } else {
    url = isCustomer ? `/amcs/${amcId}/customers/${customerId}/fees/${jobTypeId}/states/${stateCode}/${type}` :
          `/amcs/${amcId}/fees/${jobTypeId}/states/${stateCode}/${type}`;
  }
  return {
    types: [SAVE_AMC_LOCATION_FEES, SAVE_AMC_LOCATION_FEES_SUCCESS, SAVE_AMC_LOCATION_FEES_FAIL],
    promise: client => client.put(`dev:${url}`, {
      data: {
        data
      }
    })
  };
}

/**
 * Apply default fees to the selected customer
 * @param amcId AMC ID
 * @param customerId Selected customer
 */
export function applyDefaultFees(amcId, customerId) {
  return {
    types: [APPLY_DEFAULT_FEES, APPLY_DEFAULT_FEES_SUCCESS, APPLY_DEFAULT_FEES_FAIL],
    promise: client => client.put(`dev:/amcs/${amcId}/customers/${customerId}/fees/apply-default-location-fees`),
  };
}

/**
 * Get totals for this appraiser
 * @param user User record
 */
export function getFeeTotals(user) {
  return {
    types: [GET_FEE_TOTALS, GET_FEE_TOTALS_SUCCESS, GET_FEE_TOTALS_FAIL],
    promise: client => client.get(`dev:/${user.get('type')}s/${user.get('id')}/fees/totals`)
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
 * Create a map of fees based on job type ID
 * @param fees
 */
export function createFeeMap(fees) {
  let feeMap = Immutable.Map();
  fees.forEach(fee => {
    feeMap = feeMap.set(fee.getIn(['jobType', 'id']), fee);
  });
  return feeMap;
}

/**
 * Reset sorting when switching customers
 */
export function resetSort() {
  return {
    type: RESET_SORT
  };
}

/**
 * AMC change jobtype location
 * @param jobTypeId Job type for fee
 * @param location Location val
 */
export function changeLocation(jobTypeId, location) {
  return {
    type: CHANGE_LOCATION,
    jobTypeId,
    location
  };
}

/**
 * Get counties for a state
 * @param state State that the counties are in
 */
export function getCounties(state) {
  return {
    types: [GET_COUNTIES, GET_COUNTIES_SUCCESS, GET_COUNTIES_FAIL],
    promise: client => client.get(`dev:/location/states/${state}/counties`)
  };
}

/**
 * Retrieve zips for a state
 * @param state State that the zips are in
 */
export function getZips(state) {
  return {
    types: [GET_ZIPS, GET_ZIPS_SUCCESS, GET_ZIPS_FAIL],
    promise: client => client.get(`dev:/location/states/${state}/zips`)
  };
}
