import React, {Component, PropTypes} from 'react';

import classNames from 'classnames';
import {Confirm} from 'components';
import {Link} from 'react-router';
import {ORDERS_DETAILS} from 'redux/modules/urls';
import Immutable from 'immutable';

const styles = {confirmBody: {padding: '24px'}};

export default class InvitationDecline extends Component {
  static propTypes = {
    // Auth reducer
    auth: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Invitations reducer
    invitations: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Orders reducer
    orders: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Hide decline modal
    hideDecline: PropTypes.func.isRequired,
    // Decline invitation
    declineInvitation: PropTypes.func.isRequired,
    // Show decline modal
    showDecline: PropTypes.bool.isRequired,
    // Get order queue
    getOrderQueue: PropTypes.func.isRequired
  };

  state = {
    orders: Immutable.List()
  }

  constructor(props) {
    super(props);

    this.hideDecline = ::this.hideDecline;
  }

  componentWillReceiveProps(nextProps) {
    const {auth, invitations, getOrderQueue} = this.props;
    const {invitations: nextInvitations, orders: nextOrders} = nextProps;
    const selectedInvitation = invitations.get('selectedInvitation');
    const orders = nextOrders.get('orders');

    if (!invitations.get('selectedInvitation') && nextInvitations.get('selectedInvitation')) {
      if (nextInvitations.getIn(['selectedInvitation', 'customer'])) {
        getOrderQueue(auth.get('user'), 'new', {
          'search[customer][name]': nextInvitations.getIn(['selectedInvitation', 'customer', 'name'])
        });
      }
    }

    const pagination = nextOrders.getIn(['meta', 'pagination']);

    if (selectedInvitation) {
      if (pagination) {
        if (pagination.get('page') < pagination.get('totalPages')) {
          getOrderQueue(auth.get('user'), 'new', {
            'search[customer][name]': selectedInvitation.getIn(['customer', 'name']),
            page: pagination.get('page') + 1
          });
        }
      }

      if (orders.count && orders.count()) {
        const customerId = selectedInvitation.getIn(['customer', 'id']);
        const relatedOrders = nextOrders.get('orders').filter(order => {
          return order.getIn(['customer', 'id']) === customerId;
        });

        this.setState({
          orders: this.state.orders.merge(relatedOrders)
        });
      }
    }

  }

  hideDecline() {
    this.setState({orders: Immutable.List()});
    this.props.hideDecline();
  }

  render() {
    const {invitations, showDecline, declineInvitation} = this.props;
    const error = invitations.get('declineInvitationError');
    const orders = this.state.orders;

    return (
      <Confirm
        show={showDecline}
        hide={this.hideDecline}
        submit={declineInvitation}
        title="Decline invitation"
        bodyStyle={styles.confirmBody}
      >
        <div className={classNames({'has-error': !!error})}>
          <div>Decline invitation?</div>
          <div>
            {error &&
              <p className="help-block">{error}</p>
            }
            {!!orders.count() &&
              <div>
                <p>The following orders will be declined automatically:</p>
                <ul>
                  {orders.map((order, index) => {
                    return (
                      <li key={index}>
                        <Link className="link block" to={`${ORDERS_DETAILS}/${order.get('id')}`} onClick={this.hideDecline}>
                          {order.get('fileNumber')}
                        </Link>
                      </li>
                    );
                  })}
                </ul>
              </div>
            }
          </div>
        </div>
      </Confirm>
    );
  }
}
