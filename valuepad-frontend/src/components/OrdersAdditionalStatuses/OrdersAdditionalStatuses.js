import React, {Component, PropTypes} from 'react';
import Immutable from 'immutable';

import {
  ActionButton,
  DividerWithIcon,
  OrdersActionButtons,
  OrdersDetailsHeader,
  VpTextField
} from 'components';

const styles = {
  marginBottom4: { marginBottom: '4px' },
  padding10: { padding: '10px' },
  select: { width: '100%', border: 'none', outline: '1px solid #CCC', background: 'none', height: '34px', position: 'relative' }
};

export default class OrdersAdditionalStatuses extends Component {
  static propTypes = {
    // Auth
    auth: PropTypes.instanceOf(Immutable.Map),
    // Retrieve messages on load
    getAdditionalStatuses: PropTypes.func.isRequired,
    // Orders
    orders: PropTypes.instanceOf(Immutable.Map),
    // Submit additional status
    submitAdditionalStatus: PropTypes.func.isRequired,
    // Set a property
    setProp: PropTypes.func.isRequired,
    // If in fullscreen view
    fullScreen: PropTypes.bool,
    // Close details pane
    closeDetailsPane: PropTypes.func.isRequired,
    // Selected order
    selectedRecord: PropTypes.instanceOf(Immutable.Map).isRequired,
    // URL params
    params: PropTypes.object.isRequired,
    // get the notifications
    getNotifications: PropTypes.func.isRequired,
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

  constructor(props) {
    super(props);

    this.state = {
      error: false,
    };

    this.changeStatus = ::this.changeStatus;
    this.submitStatus = ::this.submitStatus;
    this.changeMessage = ::this.changeMessage;
  }

  /**
   * Set message prop
   * @param event
   */
  changeMessage(event) {
    this.setState({
      error: false
    });

    this.props.setProp(event.target.value, 'additionalStatus', 'message');
  }

  /**
   * Change status
   */
  changeStatus(event) {
    this.setState({
      error: false
    });

    // find the selected status
    const previous = this.props.orders.getIn(['additionalStatus', 'statuses']).filter((status) => status.get('id') === Number(this.props.orders.getIn(['additionalStatus', 'selectedStatus'])));
    const selected = this.props.orders.getIn(['additionalStatus', 'statuses']).filter((status) => status.get('id') === Number(event.target.value));
    const currentComment = this.props.orders.getIn(['additionalStatus', 'message']);
    const comment = selected.getIn([0, 'comment'], '');
    const previousComment = previous.getIn([0, 'comment'], '');

    // reset the message but only if its not set and its not the default for the last selection
    if (!event.target.value || !currentComment || currentComment === previousComment) {
      this.props.setProp(comment, 'additionalStatus', 'message');
    }
    this.props.setProp(event.target.value, 'additionalStatus', 'selectedStatus');
  }

  submitStatus() {
    const {auth, submitAdditionalStatus, orders, selectedRecord, getNotifications, selectedAppraiser} = this.props;
    const additionalStatuses = orders.get('additionalStatus');

    if (!additionalStatuses.get('selectedStatus')) {
      this.setState({
        error: true
      });
    } else {
      this.setState({
        error: false
      });

      // submit the status
      submitAdditionalStatus(auth.get('user'), selectedRecord.get('id'), additionalStatuses.get('selectedStatus'),
        additionalStatuses.get('message'), selectedAppraiser).then(() => {
          getNotifications(auth.get('user'), selectedRecord.get('id'), selectedAppraiser);
        });
    }
  }

  render() {
    const {
      orders,
      fullScreen,
      closeDetailsPane,
      selectedRecord,
      params,
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
    const additionalStatuses = orders.get('additionalStatus');
    return (
      <div className="container-fluid details-cont">
        <OrdersDetailsHeader
          fullScreen={fullScreen}
          closeDetailsPane={closeDetailsPane}
          selectedRecord={selectedRecord}
          params={params}
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
                <div className="col-md-12 text-center" style={styles.marginBottom4}>
                  {data}
                </div>
              </div>
            );
          }}
        />
        <div className="row">
          <div className="col-md-12 text-center">
            <DividerWithIcon
              label="Statuses"
              icon="stars"
            />
          </div>
        </div>
        <div className="row" style={styles.padding10}>
          <div className="col-md-9">
            <select
              className="focusable"
              style={styles.select}
              onChange={this.changeStatus}
              value={additionalStatuses.get('selectedStatus')}
            >
              <option key="default" value="">Select Status</option>
              {additionalStatuses.get('statuses').map((status, index) => {
                return (
                  <option key={index} value={status.get('id')}>{status.get('title')}</option>
                );
              })}
            </select>
            <VpTextField
              name="message"
              value={additionalStatuses.get('message')}
              label="Message"
              onChange={this.changeMessage}
              multiLine
              noTimeout
            />
          </div>
          <div className="col-md-3">
            <ActionButton
              type="submit"
              text="Update Status"
              onClick={this.submitStatus}
              additionalClasses="pull-right"
              disabled={!additionalStatuses.get('selectedStatus')}
            />
          </div>
        </div>
      </div>
    );
  }
}
