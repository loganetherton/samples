import React, {Component, PropTypes} from 'react';
import {connect} from 'react-redux';
import Immutable from 'immutable';
import {setProp, getNotifications, resetCounter} from 'redux/modules/notifications';
import {createNotificationByType} from 'helpers/genericFunctions';

import {NoData} from 'components';

const fullDisplayFormat = 'MM/DD/YYYY h:mm A';
const shortDisplayFormat = 'MM/YYYY';

const styles = require('./NotificationLog.scss');

@connect(
  state => ({
    auth: state.auth,
    customer: state.customer,
    notifications: state.notifications
  }), {
    setProp,
    getNotifications,
    resetCounter,
  })
export default class NotificationLog extends Component {
  static propTypes = {
    // Auth
    auth: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Customer
    customer: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Notifications
    notifications: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Set a property
    setProp: PropTypes.func.isRequired,
    // Retrieve notifications
    getNotifications: PropTypes.func.isRequired,
    // reset the counter
    resetCounter: PropTypes.func.isRequired,
  };

  static contextTypes = {
    pusher: PropTypes.object
  };

  /**
   * Retrieve notifications on mount if authenticated
   */
  componentDidMount() {
    // Already have a user, get notifications
    const {auth, customer, getNotifications, resetCounter, setProp} = this.props;
    const user = auth.get('user');
    if (user) {
      if (user.get('type') !== 'customer') {
        getNotifications(user);
      } else if (customer.get('selectedAppraiser')) {
        getNotifications(user, customer.get('selectedAppraiser'));
      }
    }

    // set the panel to open
    setProp(true, 'panelOpen');

    // reset the counter
    resetCounter();
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
  }

  componentWillMount() {
    // listen for pusher events
    this.pusherBind();
  }

  componentWillUnmount() {
    this.props.setProp(false, 'panelOpen');

    // remove pusher subscriptions
    this.pusherUnbind();
  }

  pusherBind() {
    const {channel} = this.context.pusher;
    if (channel) {
      // bind to tall of the events and attach the context
      channel.bind('order:create-log', this.logCreated.bind(this, 'order:create-log'), this);
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
  logCreated() {
    const {auth, getNotifications} = this.props;
    const user = auth.get('user');

    getNotifications(user);
  }

  /**
   * Retrieve notifications if authenticating here
   * @param nextProps
   */
  componentWillReceiveProps(nextProps) {
    const {getNotifications, customer} = this.props;
    const {auth: nextAuth, customer: nextCustomer} = nextProps;
    const nextUser = nextAuth.get('user');
    const nextSelectedAppraiser = nextCustomer.get('selectedAppraiser');
    // Retrieve notifications if authenticating on this state
    if (customer.get('selectedAppraiser') !== nextSelectedAppraiser) {
      getNotifications(nextUser, nextSelectedAppraiser);
    }
  }

  render() {
    const notifications = this.props.notifications;

    return (
      <div className="container-fluid">
        <div className="row">
          <div className={`col-md-12 ${styles.header}`}>
            <h4>NOTIFICATIONS</h4>
          </div>
        </div>

        <div className="row">
          <div className={`col-md-12 ${styles['divider-wrapper']}`}>
            <hr className={styles.divider} />
          </div>
        </div>

        {/*Notifications to display*/}
        {!!notifications.get('notifications').count() &&
         <div>
           <div className="row">
             <div className={`col-md-12 ${styles['notification-wrapper']}`}>
               {!!notifications.get('notifications') && notifications.get('notifications').map(notification => {
                 return createNotificationByType(notification, null, {
                   shortDisplayFormat,
                   fullDisplayFormat
                 });
               })}
             </div>
           </div>
         </div>
        }

        {/*No notifications*/}
        {!notifications.get('notifications').count() &&
         <NoData
           text="No notifications available"
         />
        }
      </div>
    );
  }
}
