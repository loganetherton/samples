import React, {Component, PropTypes} from 'react';
import {connect} from 'react-redux';

import Immutable from 'immutable';

import {NoAppraiserSelected} from 'components';
import {AppraiserSettings, AmcSettings, ManagerSettings} from 'containers';

@connect(
  state => ({
    auth: state.auth,
    customer: state.customer
  }))
export default class Settings extends Component {
  static propTypes = {
    // Auth reducer
    auth: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Customer
    customer: PropTypes.instanceOf(Immutable.Map).isRequired
  };

  render() {
    const {auth, customer} = this.props;
    const userType = auth.getIn(['user', 'type']);

    // No customer selected for current appraiser
    if (userType === 'customer' && !customer.get('selectedAppraiser')) {
      return <NoAppraiserSelected/>;
    }

    return (
      <div>
        {/* Appraiser settings */}
        {(userType === 'appraiser' || userType === 'customer') &&
          <AppraiserSettings
            selectedAppraiser={customer.get('selectedAppraiser')}
          />
        }
        {/* AMC settings */}
        {userType === 'amc' &&
          <AmcSettings
            auth={auth}
          />
        }
        {userType === 'manager' &&
          <ManagerSettings
            auth={auth}
          />
        }
      </div>
    );
  }
}
