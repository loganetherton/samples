import React, {Component, PropTypes} from 'react';
import Immutable from 'immutable';
import globalStyles from '../../theme/global';
import ReactTooltip from 'react-tooltip';

// setup displayed for unique keys
let displayed = 0;

/**
 * Action buttons for orders, whether displayed in the table, in the details pane, or in the full details view
 */
export default class OrdersActionButtons extends Component {
  static propTypes = {
    // Order
    order: PropTypes.instanceOf(Immutable.Map),
    // Toggle accept dialog
    toggleAcceptDialog: PropTypes.func.isRequired,
    // Toggle accept with conditions dialog
    toggleAcceptWithConditionsDialog: PropTypes.func.isRequired,
    // Toggle the decline dialog
    toggleDeclineDialog: PropTypes.func.isRequired,
    // Toggle submit bid
    toggleSubmitBid: PropTypes.func.isRequired,
    // Toggle schedule inspection
    toggleScheduleInspection: PropTypes.func.isRequired,
    // Toggle inspection complete
    toggleInspectionComplete: PropTypes.func.isRequired,
    // Toggle reassign dialog
    toggleReassign: PropTypes.func.isRequired,
    // Draw IconButton or FlatButton
    withLabels: PropTypes.bool,
    // render before
    wrapper: PropTypes.func,
    // Auth
    auth: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Company manager/RFP manager
    companyManagement: PropTypes.object.isRequired
  };

  static defaultProps = {
    withLabels: false
  };

  defaultWrapper(data) {
    return data;
  }

  /**
   * Render an action button
   */
  renderActionButton(action, order, icon, actionText) {
    const { withLabels } = this.props;
    const boundAction = action.bind(this, order);
    const actionFor = 'order_' + order.get('id') + '_' + icon;

    if (withLabels) {
      return (
        <span key={displayed++} className="order-actions-button" role="button" onClick={boundAction}>
          <i className="material-icons" style={styles.icon}>{icon}</i>
          <span>{actionText}</span>
        </span>
      );
    } else {
      return (
        <div key={displayed++} className="orders-action-icon pull-left" role="button" onClick={boundAction}>
          <div data-tip data-for={actionFor}>
            <i className="material-icons" style={styles.icon}>{icon}</i>
            <ReactTooltip id={actionFor} place="bottom" type="dark" effect="solid" offset={{top: 18}}>
              <span>{actionText}</span>
            </ReactTooltip>
          </div>
        </div>
      );
    }
  }

  /**
   * Show reassign button if company order and user can manage it
   * @param actions Current actions
   * @param showReassign Show buttons
   * @param toggleReassign Button function
   * @param order Current order
   * @return {*}
   */
  showReassign(actions, showReassign, toggleReassign, order) {
    if (showReassign) {
      actions.push(this.renderActionButton(toggleReassign, order, 'cached', `Reassign`));
    }
    return actions;
  }

  /**
   * Order doesn't belong to current user, but can be managed
   * @param belongsToCurrentUser Belongs to current user
   * @param canManagerOthers Current user can manage others
   * @param orderCanBeManaged Order has a company ID
   * @return {boolean|*}
   */
  orderCanBeManagedForOthers(belongsToCurrentUser, canManagerOthers, orderCanBeManaged) {
    return !belongsToCurrentUser && canManagerOthers && orderCanBeManaged;
  }

  render() {
    const {
      order,
      toggleAcceptDialog,
      toggleAcceptWithConditionsDialog,
      toggleDeclineDialog,
      toggleSubmitBid,
      toggleScheduleInspection,
      toggleInspectionComplete,
      toggleReassign,
      wrapper = this.defaultWrapper,
      auth,
      companyManagement
    } = this.props;
    const assignee = order.get('assignee', Immutable.Map());

    let actions = [];
    const user = auth.get('user');
    const userType = user.get('type');
    const userId = user.get('id');
    const orderCompanyId = order.getIn(['company', 'id']);
    const orderManager = companyManagement.managerOfCompanies.indexOf(orderCompanyId) > -1;
    const orderRfpManager = companyManagement.rfpManagerOfCompanies.indexOf(orderCompanyId) > -1;
    const orderIsCommercial = order.getIn(['jobType', 'isCommercial']);
    const bidSubmitted = order.getIn(['bid', 'amount']);
    // RFP bid
    let showRfpBid = orderManager && bidSubmitted;
    if (orderIsCommercial) {
      showRfpBid = !!(showRfpBid || (bidSubmitted && orderRfpManager));
    }
    // Show reassign button
    const canManagerOthers = userType === 'manager' || (userType === 'appraiser' && user.get('isBoss') && orderCompanyId);
    const assigneeId = assignee.get('id');
    const belongsToCurrentUser = userId === assigneeId;
    const orderCanBeManagedForOthers = this.orderCanBeManagedForOthers(belongsToCurrentUser, canManagerOthers, orderManager);

    // Submit RFP
    const toggleSubmitRfpBid = toggleSubmitBid.bind(this, true);

    if (userType !== 'customer') {
      switch (order.get('processStatus')) {
        case 'new':
          if (userType === 'manager' || orderCanBeManagedForOthers) {
            actions = [
              this.renderActionButton(toggleAcceptDialog, order, 'check', `Accept for ${assignee.get('displayName')}`),
              this.renderActionButton(toggleAcceptWithConditionsDialog, order, 'add', `Accept with Conditions for ${assignee.get('displayName')}`),
              this.renderActionButton(toggleDeclineDialog, order, 'clear', `Decline for ${assignee.get('displayName')}`),
              this.renderActionButton(toggleReassign, order, 'cached', `Reassign`)
            ];
          } else {
            actions = [
              this.renderActionButton(toggleAcceptDialog, order, 'check', 'Accept'),
              this.renderActionButton(toggleAcceptWithConditionsDialog, order, 'add', 'Accept with Conditions'),
              this.renderActionButton(toggleDeclineDialog, order, 'clear', 'Decline')
            ];
            actions = this.showReassign(actions, canManagerOthers, toggleReassign, order);
          }
          if (showRfpBid) {
            actions.push(this.renderActionButton(toggleSubmitRfpBid, order, 'money_off', `Submit RFP Bid for ${assignee.get('displayName')}`));
          }
          break;
        case 'request-for-bid':
          if (userType === 'manager' || orderCanBeManagedForOthers) {
            actions = [
              this.renderActionButton(toggleDeclineDialog, order, 'clear', `Decline for ${assignee.get('displayName')}`),
              this.renderActionButton(toggleReassign, order, 'cached', `Reassign`)
            ];
            if (showRfpBid) {
              actions.push(this.renderActionButton(toggleSubmitRfpBid, order, 'money_off', `Submit RFP Bid for ${assignee.get('displayName')}`));
            }
          } else {
            actions = [
              this.renderActionButton(toggleDeclineDialog, order, 'clear', 'Decline')
            ];
            actions = this.showReassign(actions, canManagerOthers, toggleReassign, order);
            if (showRfpBid) {
              actions.push(this.renderActionButton(toggleSubmitRfpBid, order, 'money_off', `Submit RFP Bid for ${assignee.get('displayName')}`));
            }
          }
          // Add submit bid
          if (typeof bidSubmitted === 'undefined') {
            if (userType === 'manager' || orderCanBeManagedForOthers) {
              actions.unshift(this.renderActionButton(toggleSubmitBid, order, 'attach_money', `Submit Bid for ${assignee.get('displayName')}`));
            } else {
              actions.unshift(this.renderActionButton(toggleSubmitBid, order, 'attach_money', 'Submit Bid'));
            }
          }
          break;
        case 'accepted':
        case 'inspection-scheduled':
          actions = [
            this.renderActionButton(toggleScheduleInspection, order, 'schedule', 'Schedule Inspection'),
            this.renderActionButton(toggleInspectionComplete, order, 'search', 'Inspection Complete')
          ];
          actions = this.showReassign(actions, canManagerOthers, toggleReassign, order);
          if (showRfpBid) {
            actions.push(this.renderActionButton(toggleSubmitRfpBid.bind(this, true), order, 'money_off', `Submit RFP Bid for ${assignee.get('displayName')}`));
          }
      }
    }

    if (!actions.length) {
      return <div />;
    } else {
      const displayData = actions.map((action) => {
        return action;
      });
      return (
        <div>
          {wrapper(displayData)}
        </div>
      );
    }
  }
}

const styles = {
  icon: {
    color: globalStyles.actionButtonColor,
  }
};
