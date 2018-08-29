import React, {Component, PropTypes} from 'react';
import {connect} from 'react-redux';
import {push} from 'redux-router';
import {Link} from 'react-router';
import Immutable from 'immutable';
import {
  setProp,
  getMessages,
  getMessageTotals,
  selectMessage,
  markAsRead,
  markAllAsRead,
  sendReply
} from 'redux/modules/messages';
import {
  ORDERS_DETAILS,
  ORDERS_MESSAGES
} from 'redux/modules/urls';
import {ORDERS_URL} from 'redux/modules/urls';
import {
  Divider,
  IconButton,
  FontIcon
} from 'material-ui';
import moment from 'moment';
import {
  ActionButton,
  VpTextField
} from 'components';

const styles = require('./Messages.scss');

@connect(
  state => ({
    auth: state.auth,
    customer: state.customer,
    messages: state.messages
  }), {
    setProp,
    getMessages,
    getMessageTotals,
    selectMessage,
    pushState: push,
    markAsRead,
    markAllAsRead,
    sendReply
  })
export default class Messages extends Component {
  static propTypes = {
    // Auth
    auth: PropTypes.instanceOf(Immutable.Map),
    // Messages
    messages: PropTypes.instanceOf(Immutable.Map),
    // Customer reducer
    customer: PropTypes.instanceOf(Immutable.Map),
    // Set a property
    setProp: PropTypes.func.isRequired,
    // Retrieve messages
    getMessages: PropTypes.func.isRequired,
    // get message totals
    getMessageTotals: PropTypes.func.isRequired,
    // Select message
    selectMessage: PropTypes.func.isRequired,
    // Push state
    pushState: PropTypes.func.isRequired,
    // Close messages dialog
    closeMessages: PropTypes.func.isRequired,
    // Mark a message as read
    markAsRead: PropTypes.func.isRequired,
    // Mark all messages as read
    markAllAsRead: PropTypes.func.isRequired,
    // Set prop in orders
    setPropOrders: PropTypes.func.isRequired,
    // Reply
    sendReply: PropTypes.func.isRequired
  };

  static contextTypes = {
    pusher: PropTypes.object
  };

  constructor(props) {
    super(props);
    this.state = {
      // Reply inline
      showReply: null
    };

    this.markAllAsRead = ::this.markAllAsRead;
    this.goToRecord = ::this.goToRecord;
    this.updateInlineReply = ::this.updateInlineReply;
    this.markAsReadBindings = Immutable.Map();
    this.showReplyBindings = Immutable.Map();
    this.sendReplyBindings = Immutable.Map();
  }

  /**
   * Retrieve messages on mount if authenticated
   */
  componentDidMount() {
    // Should always have a user
    const {auth, getMessages, customer, getMessageTotals} = this.props;
    const user = auth.get('user');
    const selectedAppraiser = customer.get('selectedAppraiser');
    if (user.get('type') !== 'customer') {
      getMessages(user);
      getMessageTotals(user);
    } else if (selectedAppraiser) {
      getMessages(user, selectedAppraiser);
      getMessageTotals(user, selectedAppraiser);
    }

    this.createActionBindings('markAsReadBindings');
    this.createActionBindings('showReplyBindings');
    this.createActionBindings('sendReplyBindings');
  }

  componentWillReceiveProps(nextProps) {
    const {customer} = this.props;
    const {customer: nextCustomer} = nextProps;
    const selectedAppraiser = customer.get('selectedAppraiser');
    const nextSelectedAppraiser = nextCustomer.get('selectedAppraiser');

    if (selectedAppraiser !== nextSelectedAppraiser) {
      if (selectedAppraiser) {
        this.pusherUnbind();
      }

      this.pusherBind();
    }

    if (!nextSelectedAppraiser) {
      this.pusherUnbind();
    }

    if (nextProps.messages) {
      this.createActionBindings('markAsReadBindings', nextProps.messages.get('messages'));
      this.createActionBindings('showReplyBindings', nextProps.messages.get('messages'));
      this.createActionBindings('sendReplyBindings', nextProps.messages.get('messages'));
    }
  }

  createActionBindings(type, messages) {
    let action = '';
    switch (type) {
      case 'markAsReadBindings':
        action = 'markAsRead';
        break;
      case 'showReplyBindings':
        action = 'showReply';
        break;
      case 'sendReplyBindings':
        action = 'sendReply';
        break;
    }

    if (! messages) {
      messages = this.props.messages.get('messages');
    }

    this[type] = Immutable.Map();

    messages.forEach(message => {
      this[type] = this[type].set(
        message.get('id'),
        this[action].bind(this, type === 'sendReplyBindings' ? message : message.get('id'))
      );
    });
  }

  componentWillMount() {
    // listen for pusher events
    this.pusherBind();
  }

  componentWillUnmount() {
    // remove pusher subscriptions
    this.pusherUnbind();
  }

  pusherBind() {
    const {channel} = this.context.pusher;
    if (channel) {
      // bind to tall of the events and attach the context
      channel.bind('order:send-message', this.messageCreated.bind(this, 'order:send-message'), this);
    }
  }

  pusherUnbind() {
    const {channel} = this.context.pusher;
    if (channel) {
      // since we have a context we can remove all the events in that context
      channel.unbind(null, null, this);
    }
  }

  /**
   * Orders have been updated with pusher
   */
  messageCreated() {
    const {auth, getMessages} = this.props;
    const user = auth.get('user');

    getMessages(user);
  }

  /**
   * Select a message
   * @param id
   */
  selectMessage(id) {
    this.props.selectMessage(id);
  }

  /**
   * Reply to a message
   * @param orderId Order ID
   */
  reply(orderId) {
    const {closeMessages, pushState, setPropOrders} = this.props;
    // Revert to first tab in orders display
    setPropOrders(3, 'detailsTab');
    setPropOrders(orderId, 'detailsOrder');
    // Close dialog
    closeMessages();
    // Go to order in question
    pushState(`${ORDERS_URL}/${orderId}${ORDERS_MESSAGES}`);
  }

  /**
   * Mark a message as read
   * @param messageId
   */
  markAsRead(messageId) {
    this.markMessagesAsRead([messageId]);
  }

  /**
   * Mark selected messages as read
   */
  markAllAsRead() {
    const {auth, markAllAsRead, closeMessages, getMessages, getMessageTotals} = this.props;
    // Close dialog
    closeMessages();
    const user = auth.get('user');
    markAllAsRead(user)
      .then(() => {
        getMessages(user);
        getMessageTotals(user);
      });
  }

  markMessagesAsRead(messages) {
    const user = this.props.auth.get('user');
    this.props.markAsRead(user, messages).then(() => {
      this.props.getMessages(user);
      this.props.getMessageTotals(user);
    });
  }

  /**
   * Go to a selected message
   * @param message Message record
   */
  goToRecord() {
    this.props.closeMessages();
  }

  /**
   * Show inline reply input
   * @param messageId
   */
  showReply(messageId) {
    const showReplyState = this.state.showReply;
    let show = messageId;
    // Close, clear reply text
    if (showReplyState === messageId) {
      show = null;
      this.props.setProp('', 'inlineReply');
    }
    // Display
    this.setState({
      showReply: show
    });
  }

  /**
   * Type in reply inline input
   * @param event
   */
  updateInlineReply(event) {
    this.props.setProp(event.target.value, 'inlineReply');
  }

  /**
   * Send a reply
   */
  sendReply(message) {
    const {messages, sendReply, auth} = this.props;
    const reply = messages.get('inlineReply').trim();

    if (!reply) {
      return;
    }
    // Send and close
    sendReply(auth.get('user'), message.getIn(['order', 'id']), reply)
      .then(() => {
        this.showReply.call(this, message.get('id'));
      });
  }

  render() {
    const {messages, auth} = this.props;
    const userType = auth.getIn(['user', 'type']);
    const {showReply} = this.state;

    return (
      <div className="container-fluid">
        <div className="row">
          <div className={`col-md-12 ${styles.header}`}>
            <h4>MESSAGES</h4>
          </div>
        </div>

        <div className="row">
          <div className={`col-md-12 ${styles.divider}`}>
            <Divider/>
          </div>
        </div>

        <div className="row">
          <div className={`col-md-12 ${styles['messages-container']}`}>
            {!!messages.get('messagesByDate') && messages.get('messagesByDate').entrySeq().map((messageCollection, index) => {
              return (
                <div key={index}>
                  <div className="row">
                    <div className="col-md-12">
                      <Divider/>
                    </div>
                  </div>

                  <div className="row">
                    <div className="col-md-9">
                      <h6>{moment(messageCollection[0], 'YYYY-MM-DD').format('MMM Do, YYYY')}</h6>
                    </div>

                    <div className={`col-md-3 text-right ${styles['messages-total-container']}`}>
                      <span
                        className={`label label-default ${styles['messages-total']}`}
                      >
                        {messageCollection[1].count()}
                      </span>
                    </div>
                  </div>

                  <div className="row">
                    <div className={`col-md-12 ${styles['message-divider-container']}`}>
                      <Divider/>
                    </div>
                  </div>

                  {messageCollection[1].map((thisMessage, index) => {
                    const messageFrom = thisMessage.getIn(['sender', 'type']) === 'customer'
                      ? thisMessage.getIn(['sender', 'name'])
                      : thisMessage.getIn(['sender', 'firstName']) + ' ' + thisMessage.getIn(['sender', 'lastName']);

                    return (
                      <div key={index} className={styles['message-container']}>
                        <div className="row">
                          <div className={`col-md-5 ${styles['order-link-container']}`}>
                            <Link to={`${ORDERS_DETAILS}/${thisMessage.getIn(['order', 'id'])}/documents`}
                                  onClick={this.goToRecord}>
                              {thisMessage.getIn(['order', 'fileNumber'])}
                            </Link>
                          </div>
                          {userType !== 'customer' &&
                            <div className="col-md-7 text-right">
                              <IconButton
                                tooltip="Mark as Read"
                                tooltipPosition="bottom-center"
                                className={styles['action-button']}
                                onTouchTap={this.markAsReadBindings.get(thisMessage.get('id'))}
                              >
                                <FontIcon className="material-icons">check_circle</FontIcon>
                              </IconButton>
                              <IconButton
                                tooltip="Reply"
                                tooltipPosition="bottom-center"
                                className={styles['action-button']}
                                onTouchTap={this.showReplyBindings.get(thisMessage.get('id'))}
                              >
                                <FontIcon className="material-icons">reply</FontIcon>
                              </IconButton>
                            </div>
                          }
                        </div>
                        <div className="row">
                          {showReply === thisMessage.get('id') &&
                           <div>
                             <VpTextField
                              inputClass={`focusable ${styles['message-input']}`}
                              parentClass="col-md-12"
                              minRows={3}
                              maxRows={3}
                              multiLine
                              name="comments"
                              value={messages.get('inlineReply')}
                              placeholder="Enter a new message"
                              onChange={this.updateInlineReply}
                             />
                             <div className={`col-md-12 ${styles['reply-action-container']}`}>
                               <ActionButton
                                 type="cancel"
                                 text="Cancel"
                                 onClick={this.showReplyBindings.get(thisMessage.get('id'))}
                                 additionalClasses={`pull-right ${styles['cancel-reply']}`}
                               />
                               <ActionButton
                                 type="submit"
                                 text="Send Message"
                                 onClick={this.sendReplyBindings.get(thisMessage.get('id'))}
                                 additionalClasses="pull-right"
                                 disabled={!messages.get('inlineReply').trim()}
                               />
                             </div>
                           </div>
                          }
                        </div>
                        <div className="row">
                          <div className="col-md-12">
                            <div dangerouslySetInnerHTML={{ __html: thisMessage.get('content').replace(new RegExp('\n', 'g'), '<br />') }}></div>
                          </div>
                        </div>
                        <div className="row">
                          <div className={`col-md-12 text-right ${styles['message-sent-container']}`}>
                            <span
                              className={`label label-default ${styles['message-sent']}`}
                            >
                              {moment(thisMessage.get('createdAt')).fromNow()} from <strong>{messageFrom}</strong>
                            </span>
                          </div>
                        </div>
                      </div>
                    );
                  })}
                </div>
              );
            })}
          </div>
        </div>
        {userType !== 'customer' &&
          <div className="row">
            <div className={`col-md-12 ${styles['mark-all-as-read-container']}`}>
              <ActionButton
                additionalClasses={styles['mark-all-as-read']}
                text="MARK ALL AS READ"
                type="submit"
                onClick={this.markAllAsRead}
              />
            </div>
          </div>
        }
      </div>
    );
  }
}
