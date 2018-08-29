// Get records
const GET_RECORDS = 'vp/accounting/GET_RECORDS';
const GET_RECORDS_SUCCESS = 'vp/accounting/GET_RECORDS_SUCCESS';
const GET_RECORDS_FAIL = 'vp/accounting/GET_RECORDS_FAIL';
// Get totals
const GET_TOTALS = 'vp/accounting/GET_TOTALS';
const GET_TOTALS_SUCCESS = 'vp/accounting/GET_TOTALS_SUCCESS';
const GET_TOTALS_FAIL = 'vp/accounting/GET_TOTALS_FAIL';
// Set a property explicitly
const SET_PROP = 'vp/accounting/SET_PROP';
// Clear search
const CLEAR_SEARCH = 'vp/accounting/CLEAR_SEARCH';

import Immutable from 'immutable';

import {setProp as setPropInherited, createQueryParams} from 'helpers/genericFunctions';

// Just the fields needed for accounting
const accountingIncludes = 'customer,clientName,jobType,fee,techFee,paidAt,orderedAt,property,completedAt,assignee,assignee.taxIdentificationNumber,company.taxId';

export const unpaidFromDate = 'filter[orderedAt][from]';
export const unpaidToDate = 'filter[orderedAt][to]';
export const paidFromDate = 'filter[paidAt][from]';
export const paidToDate = 'filter[paidAt][to]';

// Initial search state for accounting
export const initialSearchState = {
  unpaid: {
    query: '',
    filter: 'orderedAt',
    submitter: '',
    from: '',
    to: '',
    company: '',
  },
  paid: {
    query: '',
    filter: 'orderedAt',
    submitter: '',
    from: '',
    to: '',
    company: '',
  }
};

// Pages on load
export const initialPageState = {
  paid: 1,
  unpaid: 1
};

const initialState = Immutable.fromJS({
  // Unpaid accounting records
  unpaid: {
    data: [],
    meta: {}
  },
  // Paid accounting records
  paid: {
    data: [],
    meta: {}
  },
  totals: {
    paid: null,
    unpaid: null,
  },
  // Page
  page: initialPageState,
  // Search
  search: initialSearchState,
  // Initial load complete
  initialLoad: false
});

export default function reducer(state = initialState, action = {}) {
  switch (action.type) {
    /**
     * Get records
     */
    case GET_RECORDS:
      return state
        .remove('getRecordsSuccess')
        .set('initialLoad', true)
        .set('gettingRecords', true);
    case GET_RECORDS_SUCCESS:
      return state
        .set('getRecordsSuccess', true)
        .set(action.isPaid ? 'paid' : 'unpaid', Immutable.fromJS(action.result))
        .remove('gettingRecords');
    case GET_RECORDS_FAIL:
      return state
        .set('getRecordsSuccess', false)
        .remove('gettingRecords');
    /**
     * Get totals
     */
    case GET_TOTALS:
      return state
        .remove('getTotalsSuccess')
        .set('gettingTotals', true);
    case GET_TOTALS_SUCCESS:
      return state
      .set('getTotalsSuccess', true)
      .set('totals', Immutable.fromJS(action.result))
      .remove('gettingTotals');
    case GET_TOTALS_FAIL:
      return state
      .set('getTotalsSuccess', false)
      .remove('gettinTotals');
    /**
     * Clear search
     */
    case CLEAR_SEARCH:
      let newState = state
        .setIn(['search', action.tab], Immutable.fromJS(initialSearchState[action.tab]));
      if (action.changePage) {
        newState = newState.setIn(['page', action.tab], 1);
      }
      return newState;
    /**
     * Set a property explicitly
     */
    case SET_PROP:
      return state
        .setIn(action.name, action.value);
    default:
      return state;
  }
}

/**
 * Retrieve records for accounting
 * @param user
 * @param tab Accounting type
 * @param page Table page
 * @param search Search
 * @param perPage Results per page
 * @param selectedAppraiser Appraiser for customer view
 */
export function getRecords(user, tab, page, search, perPage = 10, selectedAppraiser) {
  // get the filters
  const filters = (tab === 'paid') ? search.paid : search.unpaid;

  const userId = user.get('id');
  const userType = user.get('type');
  let url;

  // get the date filter
  const dateFilter = `filter[${filters.filter}]`;
  const fromFilter = `${dateFilter}[from]`;
  const toFilter = `${dateFilter}[to]`;

  const headers = {
    Include: accountingIncludes
  };

  // create the search
  const fullSearchParams = {
    'filter[isPaid]': (tab === 'paid') ? 'true' : 'false',
    page: page,
    query: search[tab].query,
    orderBy: search[tab].filter + ':desc',
    perPage,
    'search[customer][name]': search[tab].submitter,
  };

  fullSearchParams[fromFilter] = filters.from;
  fullSearchParams[toFilter] = filters.to;

  if (filters.company) {
    fullSearchParams['filter[company]'] = filters.company;
  }

  // Customer view
  if (user.get('type') === 'customer') {
    url = `dev:/customers/${userId}/appraisers/${selectedAppraiser}/orders?${createQueryParams(fullSearchParams)}`;
  } else {
    url = `dev:/${userType}s/${userId}/orders/accounting?${createQueryParams(fullSearchParams)}`;
  }

  return {
    types: [GET_RECORDS, GET_RECORDS_SUCCESS, GET_RECORDS_FAIL],
    promise: client => client.get(url, {
      data: {
        headers
      }
    }),
    isPaid: tab === 'paid'
  };
}

export function getTotals(user, selectedAppraiser, selectedCompany) {
  const userId = user.get('id');
  const userType = user.get('type');
  const data = {};
  let url;
  // Customer view
  if (user.get('type') === 'customer') {
    url = `dev:/customers/${userId}/appraisers/${selectedAppraiser}/orders/totals`;
  } else {
    url = `dev:/${userType}s/${userId}/orders/totals`;
    if (selectedCompany) {
      const params = {'filter[company]': selectedCompany};
      url += `?${createQueryParams(params)}`;
    }
  }
  return {
    types: [GET_TOTALS, GET_TOTALS_SUCCESS, GET_TOTALS_FAIL],
    promise: client => client.get(url, {data}),
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
 * Clear search parameters
 * @param tab Current tab
 * @param changePage Change to page 1
 */
export function clearSearch(tab, changePage) {
  return {
    type: CLEAR_SEARCH,
    tab,
    changePage
  };
}
