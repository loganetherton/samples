import React, {Component, PropTypes} from 'react';
import Immutable from 'immutable';

import {DEFAULT_CUSTOMER} from 'redux/modules/jobType';
const defaultCustomerRecord = Immutable.Map().set('id', DEFAULT_CUSTOMER).set('name', 'Default');

const styles = {
  icon: {color: '#e5e5e5', fontSize: '30px'},
  iconNonActive: {fontSize: '30px'},
  defaultNameInactive: {fontWeight: 'normal', position: 'absolute', top: '14px', fontSize: '16px'},
  customerList: {color: 'rgba(0, 0, 0, 0.54)', fontSize: '22px', fontWeight: 500, lineHeight: '48px', paddingLeft: '16px'},
  textActive: {fontWeight: 'normal', color: '#efefef', position: 'absolute', left: '45px', marginLeft: 0, top: '14px', fontSize: '16px'},
  inactive: {fontWeight: 'normal', position: 'absolute', left: '45px', marginLeft: 0, fontSize: '10px'},
  customerNameInactive: {fontWeight: 'bold', position: 'absolute', left: '45px', marginLeft: 0, fontSize: '14px', top: '8px'},
  customerNameActive: {fontWeight: 'normal', color: '#efefef', position: 'absolute', left: '45px', marginLeft: 0, top: '6px', fontSize: '16px'},
  customerTypeInactive: {fontWeight: 'normal', position: 'absolute', left: '45px', marginLeft: 0, fontSize: '10px', bottom: '3px'},
  customerTypeActive: {fontWeight: 'normal', color: '#efefef', position: 'absolute', left: '45px', marginLeft: 0, bottom: '2px', fontSize: '12px'}
};

export default class CustomerSelector extends Component {
  static propTypes = {
    // List of available customers
    customers: PropTypes.instanceOf(Immutable.List),
    // Selected AMC (0 for defaults)
    selectedCustomer: PropTypes.number,
    // Select customer
    selectCustomer: PropTypes.func.isRequired,
    // Badge renderer
    badge: PropTypes.func
  };

  selectors = {};

  bindSelectorFunction(customers) {
    this.selectors = {};

    Immutable.List([defaultCustomerRecord]).concat(customers).forEach(customer => {
      this.selectors[customer.get('id')] = this.props.selectCustomer.bind(this, customer.get('id'));
    });
  }

  componentDidMount() {
    this.bindSelectorFunction(this.props.customers);
  }

  componentWillReceiveProps(nextProps) {
    if (!nextProps.customers.equals(this.props.customers)) {
      this.bindSelectorFunction(nextProps.customers);
    }
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
      default:
        return 'business';
    }
  }

  render() {
    const {selectedCustomer = DEFAULT_CUSTOMER, badge = () => {}} = this.props;

    const customers = Immutable.List([defaultCustomerRecord]).concat(this.props.customers);

    return (
      <div>
        <div style={styles.customerList}>
          Customer List
        </div>
        <ul className="job-type-selector-ul">
          {customers.map((customer) => {
            let nameStyles;

            if (customer.get('id') === DEFAULT_CUSTOMER) {
              nameStyles = [styles.defaultNameInactive, styles.textActive];
            } else {
              nameStyles = [styles.customerNameInactive, styles.customerNameActive];
            }

            const nameStyle = nameStyles[Number(customer.get('id') === selectedCustomer)];

            return (
              <li key={customer.get('id')}
                onClick={this.selectors[customer.get('id')]}
                className={customer.get('id') === selectedCustomer ? 'list-item-active' : 'list-item-inactive'}
              >
                <i className="material-icons"
                   style={customer.get('id') === selectedCustomer ? styles.icon : styles.iconNonActive}>{this.getIcon(customer)}</i>
                <span style={nameStyle}>
                  {customer.get('name')}
                </span>
                {badge(customer)}
                {customer.get('id') !== DEFAULT_CUSTOMER &&
                  <p
                    style={customer.get('id') === selectedCustomer ? styles.customerTypeActive : styles.customerTypeInactive}>
                    {customer.get('companyType').replace(/-/g, ' ').toUpperCase()}
                  </p>
                }
              </li>
            );
          })}
        </ul>
      </div>
    );
  }
}
