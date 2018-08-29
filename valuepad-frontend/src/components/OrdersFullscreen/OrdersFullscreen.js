import React, {Component, PropTypes} from 'react';
import Immutable from 'immutable';

import {OrdersDetailsPane, NoData} from 'components';

/**
 * Orders details component
 */
export default class OrdersFullscreen extends Component {
  static propTypes = {
    // Orders
    orders: PropTypes.instanceOf(Immutable.Map),
    // Auth
    auth: PropTypes.instanceOf(Immutable.Map),
    // Params for order ID
    params: PropTypes.object.isRequired,
    // Query for a single order
    getOrder: PropTypes.func.isRequired,
    // Set prop
    setProp: PropTypes.func.isRequired,
    // Invitations reducer
    invitations: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Toggle instructions dialog
    toggleInstructions: PropTypes.func.isRequired,
    // Selected appraiser (customer view)
    selectedAppraiser: PropTypes.number,
    // Company management
    companyManagement: PropTypes.object.isRequired
  };

  /**
   * Retrieve selected order
   */
  componentDidMount() {
    const {auth, orders, params, setProp, getOrder} = this.props;
    const user = auth.get('user');
    // const orderId = this.props.params.orderId;
    let orderId = orders.getIn(['selectedRecord', 'id']);
    // // No order ID in props on mount, get from params
    if (!orderId) {
      orderId = parseInt(params.orderId, 10);
      setProp(orderId, 'detailsOrder');
    }

    // If authenticated, get order
    if (auth.getIn(['user', 'id'])) {
      getOrder(user, orderId);
    }
  }

  /**
   * Get loaded order, validate user if not validated
   * @param nextProps
   */
  componentWillReceiveProps(nextProps) {
    const {getOrder, setProp, auth, invitations, orders} = this.props;
    const {auth: nextAuth, invitations: nextInvitations, orders: nextOrders} = nextProps;

    // User received
    const nextUser = nextAuth.get('user');

    // Order ID
    const orderId = parseInt(this.props.params.orderId, 10);
    const nextOrderId = parseInt(nextProps.params.orderId, 10);

    // Auth on this state
    if (!auth.getIn(['user', 'id']) && nextUser && nextOrderId) {
      getOrder(nextUser, nextOrderId);
      setProp(nextOrderId, 'detailsOrder');
    }
    // Order number changes while on page
    if (nextUser && nextOrderId &&
        (orderId !== nextOrderId || (nextOrders.getIn(['selectedRecord', 'id']) &&
                                     nextOrders.getIn(['selectedRecord', 'id']) !== nextOrderId)) &&
        !nextOrders.get('gettingOrder')) {
      getOrder(nextUser, nextOrderId);
    }
    // Invitation is accepted, so refresh the order
    if (!invitations.get('acceptInvitationSuccess') && nextInvitations.get('acceptInvitationSuccess')) {
      getOrder(nextUser, nextOrderId);
    }
    // Reloads order after accepting
    if (!orders.get('acceptOrderSuccess') && nextOrders.get('acceptOrderSuccess')) {
      getOrder(nextUser, nextOrderId);
    }
  }

  render() {
    const {orders, toggleInstructions, selectedAppraiser = null} = this.props;
    const orderQueried = typeof orders.get('getOrderSuccess') !== 'undefined';
    return (
      <div>
        {orders.get('selectedRecord') &&
          <OrdersDetailsPane
            ref="detailsPane"
            {...this.props}
            selectedTab={orders.get('detailsSelectedTab')}
            selectedRecord={orders.get('selectedRecord')}
            fullScreen
            toggleInstructions={toggleInstructions}
            selectedAppraiser={selectedAppraiser}
          />
        }
        {orderQueried && !orders.get('selectedRecord') &&
         <NoData text="Order could not be found" />
        }
      </div>
    );
  }
}
