// Retrieve messages
const GET_MESSAGES = 'vp/messages/GET_MESSAGES';
const GET_MESSAGES_SUCCESS = 'vp/messages/GET_MESSAGES_SUCCESS';
const GET_MESSAGES_FAIL = 'vp/messages/GET_MESSAGES_FAIL';
// Retrieve message totals
const GET_MESSAGE_TOTALS = 'vp/messages/GET_MESSAGE_TOTALS';
const GET_MESSAGE_TOTALS_SUCCESS = 'vp/messages/GET_MESSAGE_TOTALS_SUCCESS';
const GET_MESSAGE_TOTALS_FAIL = 'vp/messages/GET_MESSAGE_TOTALS_FAIL';
// Set prop
const SET_PROP = 'vp/messages/SET_PROP';
// Select a message
const SELECT_MESSAGE = 'vp/messages/SELECT_MESSAGE';
// Mark message as read
const MARK_AS_READ = 'vp/messages/MARK_AS_READ';
const MARK_AS_READ_SUCCESS = 'vp/messages/MARK_AS_READ_SUCCESS';
const MARK_AS_READ_FAIL = 'vp/messages/MARK_AS_READ_FAIL';
// Mark all messages as read
const MARK_ALL_AS_READ = 'vp/messages/MARK_ALL_AS_READ';
const MARK_ALL_AS_READ_SUCCESS = 'vp/messages/MARK_ALL_AS_READ_SUCCESS';
const MARK_ALL_AS_READ_FAIL = 'vp/messages/MARK_ALL_AS_READ_FAIL';
// Send reply to message
const REPLY = 'vp/messages/REPLY';
const REPLY_SUCCESS = 'vp/messages/REPLY_SUCCESS';
const REPLY_FAIL = 'vp/messages/REPLY_FAIL';

import Immutable from 'immutable';
import moment from 'moment';

import {setProp as setPropInherited} from 'helpers/genericFunctions';

const initialState = Immutable.fromJS({
  // messages list
  messages: [],
  // Checked messages
  selectedMessages: [],
  // If select all is checked
  selectAll: false,
  // totals for the messages
  totals: {},
  // Inline reply to message value
  inlineReply: ''
});

export default function reducer(state = initialState, action = {}) {
  switch (action.type) {
    case GET_MESSAGES:
      return state
        .remove('getMessagesSuccess')
        .set('gettingMessages', true);
    case GET_MESSAGES_SUCCESS:
      let messagesByDate = Immutable.Map();
      const messages = Immutable.fromJS(action.result.data);
      // Organize by date
      messages.forEach(message => {
        const thisDate = moment(message.get('createdAt')).format('YYYY-MM-DD');
        if (!messagesByDate.get(thisDate)) {
          messagesByDate = messagesByDate.set(thisDate, Immutable.List().push(message));
        } else {
          const modifiedDate = messagesByDate.get(thisDate).push(message);
          messagesByDate = messagesByDate.set(thisDate, modifiedDate);
        }
      });
      return state
        .set('getMessagesSuccess', true)
        .set('messages', messages)
        .set('messagesByDate', messagesByDate)
        .remove('gettingMessages');
    case GET_MESSAGES_FAIL:
      return state
        .set('getMessagesSuccess', false)
        .remove('gettingMessages');
    /**
     * Get message totals
     */
    case GET_MESSAGE_TOTALS:
      return state
        .remove('getMessageTotalSuccess')
        .set('gettingMessageTotals');
    case GET_MESSAGE_TOTALS_SUCCESS:
      return state
        .set('getMessagesSuccess', true)
        .set('totals', Immutable.fromJS(action.result))
        .remove('getMessageTotals');
    case GET_MESSAGE_TOTALS_FAIL:
      return state
        .set('getMessageTotalSuccess', false)
        .remove('gettingMessageTotals');

    /**
     * Select a message
     */
    case SELECT_MESSAGE:
      const currentlySelected = state.get('selectedMessages');
      const index = currentlySelected.indexOf(action.messageId);
      let newlySelected;
      // Select
      if (index === -1) {
        newlySelected = currentlySelected.push(action.messageId);
        // Deselect
      } else {
        newlySelected = currentlySelected.delete(index);
      }
      return state
        .set('selectedMessages', newlySelected);
    /**
     * Mark a message as read
     */
    case MARK_AS_READ:
      return state
        .set('markingAsRead', true)
        .remove('markAsReadSuccess');
    case MARK_AS_READ_SUCCESS:
      return state
        // Remove messages
        .set('selectAll', false)
        .remove('markingAsRead')
        .set('markAsReadSuccess', true);
    case MARK_AS_READ_FAIL:
      return state
        .remove('markingAsRead')
        .set('markAsReadSuccess', false);
    /**
     * Mark all messages as read
     */
    case MARK_ALL_AS_READ:
      return state
        .set('markingAllAsRead', true)
        .remove('markAllReadSuccess');
    case MARK_ALL_AS_READ_SUCCESS:
      return state
      // Remove messages
        .set('selectAll', false)
        .remove('markingAllAsRead')
        .set('markAllReadSuccess', true);
    case MARK_ALL_AS_READ_FAIL:
      return state
        .remove('markingAllAsRead')
        .set('markAllReadSuccess', false);
    /**
     * Reply to message
     */
    case REPLY:
      return state
        .set('markingAllAsRead', true)
        .remove('markAllReadSuccess');
    case REPLY_SUCCESS:
      return state
      // Remove messages
        .set('selectAll', false)
        .remove('markingAllAsRead')
        .set('markAllReadSuccess', true);
    case REPLY_FAIL:
      return state
        .remove('markingAllAsRead')
        .set('markAllReadSuccess', false);
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
 * Retrieve messages for a user
 */
export function getMessages(user, selectedAppraiser) {
  let url = `dev:/${user.get('type')}s/${user.get('id')}/messages?orderBy=createdAt:desc&filter[isRead]=false`;
  let headers = {};
  if (user.get('type') === 'customer') {
    url = `dev:/customers/${user.get('id')}/appraisers/${selectedAppraiser}/messages?orderBy=createdAt:desc&filter[isRead]=false`;
    headers = {
      'Act-As-Assignee': selectedAppraiser
    };
  }
  return {
    types: [GET_MESSAGES, GET_MESSAGES_SUCCESS, GET_MESSAGES_FAIL],
    promise: client => client.get(url, {
      data: {
        headers
      }
    })
  };
}

/**
 * Retrieve message totals for a user
 */
export function getMessageTotals(user, selectedAppraiser) {
  let url = `dev:/${user.get('type')}s/${user.get('id')}/messages/total`;
  let headers = {};
  if (user.get('type') === 'customer') {
    url = `dev:/customers/${user.get('id')}/appraisers/${selectedAppraiser}/messages/total`;
    headers = {
      'Act-As-Assignee': selectedAppraiser
    };
  }
  return {
    types: [GET_MESSAGE_TOTALS, GET_MESSAGE_TOTALS_SUCCESS, GET_MESSAGE_TOTALS_FAIL],
    promise: client => client.get(url, {
      data: {
        headers
      }
    })
  };
}

/**
 * Select a message checkbox
 * @param messageId
 */
export function selectMessage(messageId) {
  return {
    type: SELECT_MESSAGE,
    messageId
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
 * Mark message as read
 * @param user
 * @param messageIds Array of message IDs
 */
export function markAsRead(user, messageIds) {
  return {
    types: [MARK_AS_READ, MARK_AS_READ_SUCCESS, MARK_AS_READ_FAIL],
    promise: client => client.post(`dev:/${user.get('type')}s/${user.get('id')}/messages/mark-as-read`, {
      data: {
        messages: messageIds
      }
    })
  };
}

/**
 * Mark all messages as read
 * @param user
 */
export function markAllAsRead(user) {
  return {
    types: [MARK_ALL_AS_READ, MARK_ALL_AS_READ_SUCCESS, MARK_ALL_AS_READ_FAIL],
    promise: client => client.post(`dev:/${user.get('type')}s/${user.get('id')}/messages/mark-all-as-read`)
  };
}

/**
 * Reply to a message
 * @param user
 * @param orderId
 * @param content Message content
 */
export function sendReply(user, orderId, content) {
  return {
    types: [REPLY, REPLY_SUCCESS, REPLY_FAIL],
    promise: client => client.post(`dev:/${user.get('type')}s/${user.get('id')}/orders/${orderId}/messages`, {
      data: {content}
    })
  };
}
