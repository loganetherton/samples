const ADD_FEATURE = 'vp/features/ADD_FEATURE';

import Immutable from 'immutable';

// Initial state
const initialState = Immutable.fromJS({});

export default function reducer(state = initialState, action = {}) {
  switch (action.type) {
    case ADD_FEATURE:
      const {name, callback} = action;

      if (!callback instanceof Function) {
        return state.set(name, () => callback);
      }

      return state.set(name, callback);
    default:
      return state;
  }
}

/**
 * Adds a feature checker
 *
 * @param {string} name - Name of the feature
 * @param {*} callback - Either a callback that returns a value or simply a value to return
 */
export function addFeature(name, callback) {
  return {
    type: ADD_FEATURE,
    name,
    callback
  };
}

/**
 * Checks whether a feature is enabled
 *
 * @param {Function} callback - Feature callback function
 * @param {...*} options - Arguments that'll be passed to the callback function
 */
export function isFeatureEnabled(callback, ...options) {
  if (callback) {
    return callback(...options);
  }
}
