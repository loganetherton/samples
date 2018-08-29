import React, {Component, PropTypes} from 'react';
import Immutable from 'immutable';

import {CustomerSelector} from 'components';
import {DEFAULT_CUSTOMER} from 'redux/modules/jobType';

const styles = {
  icon: {color: '#e5e5e5', fontSize: '30px'},
  iconNonActive: {fontSize: '30px'},
  defaultNameInactive: {fontWeight: 'normal', position: 'absolute', top: '14px', fontSize: '16px'},
  feesActive: {float: 'right', position: 'relative', top: '7px', left: '4px', color: '#f2ffff', borderRadius: '20px 20px 20px 20px', MozBorderRadius: '20px 20px 20px 20px', WebkitBorderRadius: '20px 20px 20px 20px', border: '1px solid #ffffff', padding: '1px 8px 1px 8px', backgroundColor: '#6db8ec', fontSize: '12px'},
  feesInactive: {float: 'right', position: 'relative', fontSize: '12px', top: '6px'},
  customerList: {color: 'rgba(0, 0, 0, 0.54)', fontSize: '22px', fontWeight: 500, lineHeight: '48px', paddingLeft: '16px'},
  textActive: {fontWeight: 'normal', color: '#efefef', position: 'absolute', left: '45px', marginLeft: 0, top: '14px', fontSize: '16px'},
  inactive: {fontWeight: 'normal', position: 'absolute', left: '45px', marginLeft: 0, fontSize: '10px'},
  customerNameInactive: {fontWeight: 'bold', position: 'absolute', left: '45px', marginLeft: 0, fontSize: '14px', top: '8px'},
  customerNameActive: {fontWeight: 'normal', color: '#efefef', position: 'absolute', left: '45px', marginLeft: 0, top: '6px', fontSize: '16px'},
  customerTypeInactive: {fontWeight: 'normal', position: 'absolute', left: '45px', marginLeft: 0, fontSize: '10px', bottom: '3px'},
  customerTypeActive: {fontWeight: 'normal', color: '#efefef', position: 'absolute', left: '45px', marginLeft: 0, bottom: '2px', fontSize: '12px'}
};

export default class JobTypeAmcSelector extends Component {
  static propTypes = {
    // List of available customers
    customers: PropTypes.instanceOf(Immutable.List),
    // Selected AMC (0 for defaults)
    selectedCustomer: PropTypes.number,
    // Select customer
    selectCustomer: PropTypes.func.isRequired,
    // Total selected per customer
    totals: PropTypes.instanceOf(Immutable.Map),
    // Search input change
    changeSearchValue: PropTypes.func.isRequired,
    // Reset sorting when changing customer
    resetSort: PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);

    this.handleChange = ::this.handleChange;
    this.renderBadge = ::this.renderBadge;
  }

  getIcon(customer) {
    switch (customer.get('companyType')) {
      case 'bank-lender':
        return 'account_balance';
      case 'credit-union':
        return 'account_balance';
      case 'mortgage-broker':
        return 'domain';
      case 'appraisal-management-company':
        return 'location_city';
    }
  }

  /**
   * Change selected customer
   * @param customerId Customer ID
   */
  handleChange(customerId) {
    const {selectCustomer, changeSearchValue, selectedCustomer, resetSort} = this.props;
    selectCustomer(customerId);
    changeSearchValue('industryFormSearch', '', selectedCustomer !== DEFAULT_CUSTOMER);
    changeSearchValue('customerFormSearch', '', selectedCustomer !== DEFAULT_CUSTOMER);
    resetSort();
  }

  /**
   * Renders a badge that counts enabled job types for a specific customer
   *
   * @param customer
   */
  renderBadge(customer) {
    const {totals, selectedCustomer} = this.props;

    if (typeof totals.get(customer.get('id')) === 'undefined') {
      return <span />;
    }

    return (
      <span style={customer.get('id') === selectedCustomer ? styles.feesActive : styles.feesInactive}>
        {`${totals.get(customer.get('id'))} ENABLED`}
      </span>
    );
  }

  render() {
    const {selectedCustomer = DEFAULT_CUSTOMER, customers = Immutable.List()} = this.props;

    return (
      <CustomerSelector
        customers={customers}
        selectedCustomer={selectedCustomer}
        selectCustomer={this.handleChange}
        badge={this.renderBadge}
      />
    );
  }
}
