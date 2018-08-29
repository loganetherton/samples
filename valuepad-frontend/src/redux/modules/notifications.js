// Retrieve notifications
const GET_NOTIFICATIONS = 'vp/notifications/GET_NOTIFICATIONS';
const GET_NOTIFICATIONS_SUCCESS = 'vp/notifications/GET_NOTIFICATIONS_SUCCESS';
const GET_NOTIFICATIONS_FAIL = 'vp/notifications/GET_NOTIFICATIONS_FAIL';
// count notifications
const INCREMENT_COUNTER = 'vp/notifications/INCREMENT_COUNTER';
const RESET_COUNTER = 'vp/notifications/RESET_COUNTER';

// Set prop
const SET_PROP = 'vp/notifications/SET_PROP';

import Immutable from 'immutable';

import {setProp as setPropInherited} from 'helpers/genericFunctions';

const initialState = Immutable.fromJS({
  // Notifications list
  notifications: [],
  counter: 0,
  panelOpen: false,
});

export default function reducer(state = initialState, action = {}) {
  switch (action.type) {
    case GET_NOTIFICATIONS:
      return state
        .remove('getNotificationsSuccess')
        .set('gettingNotifications', true);
    case GET_NOTIFICATIONS_SUCCESS:
      const notifications = Immutable.fromJS(action.result.data);
      // Sort by descending created time
      const sortedNotifications = notifications.sort((current, next) => {
        return current.get('createdAt') < next.get('createdAt') ? 1 : -1;
      });
      return state
        .set('getNotificationsSuccess', true)
        .set('notifications', sortedNotifications)
        .remove('gettingNotifications');
    case GET_NOTIFICATIONS_FAIL:
      return state
        .set('getNotificationsSuccess', false)
        .remove('gettingNotifications');
    case INCREMENT_COUNTER:
      if (state.get('panelOpen')) {
        return state;
      }
      const counter = state.get('counter') + 1;
      return state.set('counter', counter);
    case RESET_COUNTER:
      return state.set('counter', 0);
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
 * Retrieve notifications for a user
 */
export function getNotifications(user, selectedAppraiser) {
  let url = `dev:/${user.get('type')}s/${user.get('id')}/logs?orderBy=createdAt:desc`;
  let headers = {};
  if (user.get('type') === 'customer') {
    url = `dev:/customers/${user.get('id')}/appraisers/${selectedAppraiser}/logs?orderBy=createdAt:desc`;
    headers = {
      'Act-As-Assignee': selectedAppraiser
    };
  }
  return {
    types: [GET_NOTIFICATIONS, GET_NOTIFICATIONS_SUCCESS, GET_NOTIFICATIONS_FAIL],
    promise: client => client.get(url, {
      data: {
        headers
      }
    })
  };
}

export function incrementCounter() {
  return {
    type: INCREMENT_COUNTER,
  };
}

export function resetCounter() {
  return {
    type: RESET_COUNTER,
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
