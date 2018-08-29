import React, {Component, PropTypes} from 'react';
import Immutable from 'immutable';
import moment from 'moment';

import {
  DividerWithIcon,
  OrdersActionButtons,
  OrdersDetailsHeader,
  VpTextField,
} from 'components';

import {createNotificationByType} from 'helpers/genericFunctions';

const fullDisplayFormat = 'MM/DD/YYYY h:mm A';
const shortDisplayFormat = 'MMM YYYY';

const styles = require('./style.scss');

export default class OrdersNotificationLog extends Component {
  static propTypes = {
    // orders
    orders: PropTypes.instanceOf(Immutable.Map),
    // Set prop
    setProp: PropTypes.func.isRequired,
    // Retrieve notifications
    getNotifications: PropTypes.func.isRequired,
    // Search notifications
    searchNotifications: PropTypes.func.isRequired,
    // Displaying in fullscreen
    fullScreen: PropTypes.bool,
    // Close details pane
    closeDetailsPane: PropTypes.func.isRequired,
    // Selected order
    selectedRecord: PropTypes.instanceOf(Immutable.Map).isRequired,
    // URL params
    params: PropTypes.object.isRequired,
    // Auth
    auth: PropTypes.instanceOf(Immutable.Map),
    // Set print content
    setPrintContent: PropTypes.func.isRequired,
    // Remove print content
    removePrintContent: PropTypes.func.isRequired,
    // Toggle instructions
    toggleInstructions: PropTypes.func.isRequired,
    // Selected appraiser (customer view)
    selectedAppraiser: PropTypes.number,
    // Toggle accept dialog
    toggleAcceptDialog: PropTypes.func.isRequired,
    // Toggle accept with conditions
    toggleAcceptWithConditionsDialog: PropTypes.func.isRequired,
    // Toggle decline
    toggleDeclineDialog: PropTypes.func.isRequired,
    // Toggle submit bid
    toggleSubmitBid: PropTypes.func.isRequired,
    // Toggle schedule inspection
    toggleScheduleInspection: PropTypes.func.isRequired,
    // Toggle inspection complete
    toggleInspectionComplete: PropTypes.func.isRequired,
    // Toggle reassign dialog
    toggleReassign: PropTypes.func.isRequired,
    // Company management
    companyManagement: PropTypes.object.isRequired
  };

  static contextTypes = {
    pusher: PropTypes.object
  };

  constructor() {
    super();

    this.changeSearchValue = ::this.changeSearchValue;
    this.tabContent = ::this.tabContent;
    this.getNotifications = ::this.getNotifications;
  }

  componentWillReceiveProps(nextProps) {
    const {selectedAppraiser} = this.props;
    const {selectedAppraiser: nextSelectedAppraiser} = nextProps;

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

  componentDidMount() {
    this.pusherBind();
  }

  componentWillUnmount() {
    this.pusherUnbind();
  }

  /**
   * Retrieve notifications on mount
   */
  pusherBind() {
    const {channel} = this.context.pusher;
    // Pusher message
    if (channel) {
      channel.bind('order:create-log', this.getNotifications, this);
    }
  }

  /**
   * Unbind pusher
   */
  pusherUnbind() {
    const {channel} = this.context.pusher;
    // since we have a context we can remove all the events in that context
    if (channel) {
      channel.unbind('order:create-log', this.getNotifications, this);
    }
  }

  /**
   * Retrieve notifications, either normally or via pusher event
   */
  getNotifications() {
    const {getNotifications, auth, selectedRecord, selectedAppraiser} = this.props;
    const user = auth.get('user');
    if (user && selectedRecord) {
      getNotifications(user, selectedRecord.get('id'), selectedAppraiser);
    }
  }

  /**
   * Set search value
   * @param event
   */
  changeSearchValue(event) {
    const {value} = event.target;
    this.props.setProp(value, 'notificationLog', 'search');
  }

  /**
   * Sort notifications by date, group by month
   * @param notifications
   */
  sortNotifications(notifications) {
    // Sort notifications by date
    const sortedNotifications = notifications.sort((current, next) => {
      return current.get('createdAt') < next.get('createdAt') ? 1 : -1;
    });

    // Set dates on notifications
    const notificationsWithDate = sortedNotifications.map(notification => {
      const parsed = moment(notification.get('createdAt'));
      return notification.set('month', parsed.get('month')).set('year', parsed.get('year'));
    });

    // Get months, create map with empty array for each month
    let notificationGroupings = notificationsWithDate.map(notification => {
      return notification.get('year') + '-' + notification.get('month');
    })
    .toSetSeq()
    .toSet()
    .toOrderedMap()
    .map(() => {
      return Immutable.List();
    });
    // Push notifications into months list
    notificationsWithDate.forEach(notification => {
      const groupIndex = notification.get('year') + '-' + notification.get('month');
      notificationGroupings = notificationGroupings.set(groupIndex, notificationGroupings.get(groupIndex).push(notification));
    });
    return notificationGroupings;
  }

  /**
   * Returns the tab content without the header
   */
  tabContent() {
    const {orders} = this.props;
    const notificationLog = orders.get('notificationLog');
    const selectedOrder = orders.get('selectedRecord');
    // Notifications
    const notifications = this.sortNotifications(notificationLog.get('notifications'));
    const search = false;

    return (
      <div>
        <div className="row">
          <div className="col-md-12 text-center">
            <DividerWithIcon
              label="History"
              icon="history"
            />
          </div>
        </div>
        {search &&
          <div className="row">
            <div className="col-md-12">
              <VpTextField
                placeholder="Search notification log"
                value={notificationLog.get('search')}
                onChange={this.changeSearchValue}
                className={styles['filter-logs']}
              />
            </div>
          </div>
        }
        {/*Convert to entrySeq to not lost k/v relationship*/}
        {notifications.entrySeq().map((notificationGroup, index) => {
          const dates = notificationGroup[0].split('-');
          return (
            <div key={index}>
              <div className="row">
                <div className="col-md-6" style={{ paddingTop: '5px', paddingBottom: '5px' }}>
                  <strong>{moment().year(parseInt(dates[0], 10)).month(parseInt(dates[1], 10)).format(shortDisplayFormat)}</strong>
                </div>
                <div className="col-md-6" style={{ paddingTop: '5px', paddingBottom: '5px' }}>
                  <div className="pull-right">{notificationGroup[1].count()} TOTAL</div>
                </div>
              </div>
              {notificationGroup[1].map((notification, index) => {
                return (
                  <div key={index}>
                    {createNotificationByType(notification, selectedOrder, {
                      shortDisplayFormat,
                      fullDisplayFormat
                    })}
                  </div>
                );
              })}
            </div>
          );
        })}
      </div>
    );
  }

  render() {
    const {
      fullScreen,
      closeDetailsPane,
      selectedRecord,
      params,
      setPrintContent,
      removePrintContent,
      toggleInstructions,
      toggleAcceptDialog,
      toggleAcceptWithConditionsDialog,
      toggleDeclineDialog,
      toggleSubmitBid,
      toggleScheduleInspection,
      toggleInspectionComplete,
      toggleReassign,
      auth,
      companyManagement
    } = this.props;
    return (
      <div className="container-fluid details-cont">
        <OrdersDetailsHeader
          fullScreen={fullScreen}
          closeDetailsPane={closeDetailsPane}
          selectedRecord={selectedRecord}
          params={params}
          mapPrint
          setPrintContent={setPrintContent}
          getPrintContent={this.tabContent}
          removePrintContent={removePrintContent}
          toggleInstructions={toggleInstructions}
        />
        <OrdersActionButtons
          toggleAcceptDialog={toggleAcceptDialog}
          toggleAcceptWithConditionsDialog={toggleAcceptWithConditionsDialog}
          toggleDeclineDialog={toggleDeclineDialog}
          toggleSubmitBid={toggleSubmitBid}
          toggleScheduleInspection={toggleScheduleInspection}
          toggleInspectionComplete={toggleInspectionComplete}
          toggleReassign={toggleReassign}
          auth={auth}
          order={selectedRecord}
          withLabels
          companyManagement={companyManagement}
          wrapper={(data) => {
            return (
              <div className="row">
                <div className="col-md-12 text-center" style={{ marginBottom: '4px' }}>
                  {data}
                </div>
              </div>
            );
          }}
        />
        {this.tabContent()}
      </div>
    );
  }
}
