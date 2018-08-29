import Immutable from 'immutable';

import {setProp as setPropInherited} from 'helpers/genericFunctions';

// Set prop
const SET_PROP = 'vp/customer/SET_PROP';
// Search appraisers
const SEARCH_APPRAISERS = 'vp/customer/SEARCH_APPRAISERS';
const SEARCH_APPRAISERS_SUCCESS = 'vp/customer/SEARCH_APPRAISERS_SUCCESS';
const SEARCH_APPRAISERS_FAIL = 'vp/customer/SEARCH_APPRAISERS_FAIL';
// Select appraiser from list
const SELECT_APPRAISER = 'vp/customer/SELECT_APPRAISER';

// Initial state
const initialState = Immutable.fromJS({
  selectedAppraiser: null,
  // Appraiser search
  searchAppraiserVal: '',
  // Appraiser search results
  searchResults: []
});

export default function reducer(state = initialState, action = {}) {
  switch (action.type) {
    /**
     * Set a property explicitly
     */
    case SET_PROP:
      return state
        .setIn(action.name, action.value);
    /**
     * Search appraisers
     */
    case SEARCH_APPRAISERS:
      return state
        .set('searchingAppraisers', true)
        .remove('searchAppraisersSuccess');
    case SEARCH_APPRAISERS_SUCCESS:
      return state
        .remove('searchingAppraisers')
        .set('searchAppraisersSuccess', true)
        .set('searchResults', Immutable.fromJS(action.result.data));
    case SEARCH_APPRAISERS_FAIL:
      return state
        .remove('searchingAppraisers')
        .set('searchAppraisersSuccess', false);
    /**
     * Select appraiser from search results
     */
    case SELECT_APPRAISER:
      const appraiserId = parseInt(action.appraiserId, 10);
      localStorage.setItem('selectedAppraiser', appraiserId);
      return state
        .set('selectedAppraiser', appraiserId);
    default:
      return state;
  }
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
 * Search appraisers by full name
 * @param customerId Current custom
 * @param value Search value
 */
export function searchAppraisers(customerId, value) {
  return {
    types: [SEARCH_APPRAISERS, SEARCH_APPRAISERS_SUCCESS, SEARCH_APPRAISERS_FAIL],
    promise: client => client.get(`dev:/customers/${customerId}/appraisers?search[fullName]=${value}`)
  };
}

/**
 * Select an appraiser
 * @param appraiserId
 */
export function selectAppraiser(appraiserId) {
  return {
    type: SELECT_APPRAISER,
    appraiserId
  };
}
