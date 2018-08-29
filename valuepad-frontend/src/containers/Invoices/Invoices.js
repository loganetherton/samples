import React, {Component, PropTypes} from 'react';
import {connect} from 'react-redux';
import Immutable from 'immutable';
import {InvoiceList} from 'components';

import {
  setProp,
  getInvoices,
  sortInvoices,
  getSettings,
  payInvoice
} from 'redux/modules/invoices';

import {
  getAchInfo,
  getCcInfo
} from 'redux/modules/settings';

@connect(
  state => ({
    auth: state.auth,
    invoices: state.invoices,
    settings: state.settings
  }), {
    setProp,
    getInvoices,
    sortInvoices,
    getSettings,
    payInvoice,
    getAchInfo,
    getCcInfo
  })
export default class Invoices extends Component {
  static propTypes = {
    // Auth
    auth: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Invoices
    invoices: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Settings
    settings: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Route params
    params: PropTypes.object.isRequired,
    // Set property
    setProp: PropTypes.func.isRequired,
    // Get invoices
    getInvoices: PropTypes.func.isRequired,
    // Sort invoices
    sortInvoices: PropTypes.func.isRequired,
    // Get ACH info
    getAchInfo: PropTypes.func.isRequired,
    // Get CC info
    getCcInfo: PropTypes.func.isRequired,
    // Pay invoice
    payInvoice: PropTypes.func.isRequired
  };

  /**
   * Get invoices on mount
   */
  componentDidMount() {
    const {auth, getInvoices, getCcInfo, getAchInfo} = this.props;
    const user = auth.get('user');
    const userId = user.get('id');
    if (userId) {
      getInvoices(userId);
      getCcInfo(user);
      getAchInfo(user);
    }
  }

  componentWillReceiveProps(nextProps) {
    const {auth, getInvoices, getCcInfo, getAchInfo} = this.props;
    const {auth: nextAuth} = nextProps;
    const user = nextAuth.get('user');
    const nextUserId = user.get('id');
    // If auth on this state, get invoices
    if (!auth.get('user') && nextUserId) {
      getInvoices(nextUserId);
      getCcInfo(user);
      getAchInfo(user);
    }
  }

  /**
   * Query invoices by changing page or perPage
   * @param page Selected page
   * @param perPage Per page value
   * @param orderBy Prop to order by
   */
  getInvoices(page, perPage, orderBy) {
    const {getInvoices, auth} = this.props;
    getInvoices(auth.getIn(['user', 'id']), page, perPage, orderBy);
  }

  render() {
    const {auth, invoices, sortInvoices, setProp, payInvoice, settings} = this.props;
    return (
      <InvoiceList
        amcId={auth.getIn(['user', 'id'])}
        invoices={invoices.get('invoices')}
        sorts={invoices.get('sorts')}
        sortInvoices={sortInvoices}
        paymentMethod={invoices.get('paymentMethod')}
        setProp={setProp}
        payInvoice={payInvoice}
        settings={settings}
        total={invoices.getIn(['meta', 'pagination', 'total'], 0)}
        pages={invoices.getIn(['meta', 'pagination', 'totalPages'], 0)}
        getInvoices={::this.getInvoices}
      />
    );
  }
}
