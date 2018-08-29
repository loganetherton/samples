const SET_UNSAVED = 'vp/unsaved/SET_UNSAVED';

import Immutable from 'immutable';

// Initial state
const initialState = Immutable.fromJS({
  isDevelopment: true,
  amcVisible: false
});

export default function reducer(state = initialState, action = {}) {
  switch (action.type) {
    case SET_UNSAVED:
      return state
        .set('hasUnsavedChanges', action.hasUnsaved);
    default:
      return state;
  }
}

/**
 * Set a flag that determines whether or not there are unsaved changes
 *
 * @param {bool} hasUnsaved
 */
export function setUnsaved(hasUnsaved) {
  return {
    type: SET_UNSAVED,
    hasUnsaved
  };
}
