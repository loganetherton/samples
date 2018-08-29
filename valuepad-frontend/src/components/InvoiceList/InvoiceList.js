import React, {Component, PropTypes} from 'react';
import Immutable from 'immutable';
import moment from 'moment';
import {capitalizeWords} from 'helpers/string';
import {Dialog} from 'material-ui';
import {Link} from 'react-router';
import {VpPlainDropdown, ActionButton, Pagination} from 'components';
import {SETTINGS_URL} from 'redux/modules/urls';
import {emptySort} from 'redux/modules/invoices';

export default class InvoiceList extends Component {

  static propTypes = {
    // Settings
    settings: PropTypes.instanceOf(Immutable.Map).isRequired,
    // ID
    amcId: PropTypes.number,
    // Invoices
    invoices: PropTypes.instanceOf(Immutable.List).isRequired,
    // Sorts
    sorts: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Sort function
    sortInvoices: PropTypes.func.isRequired,
    // Selected payment method
    paymentMethod: PropTypes.string,
    // Set prop
    setProp: PropTypes.func.isRequired,
    // Pay invoice
    payInvoice: PropTypes.func.isRequired,
    // Total number of invoices
    total: PropTypes.number.isRequired,
    // Total number of pages
    pages: PropTypes.number.isRequired,
    // Get invoices with params
    getInvoices: PropTypes.func.isRequired
  };

  constructor() {
    super();
    this.state = {
      showPayInvoice: false,
      selectedInvoice: Immutable.Map(),
      // Current page
      page: 1,
      // Results per page
      perPage: 10,
    };
    // Format money
    this.formatMoney = new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD',
      minimumFractionDigits: 2,
    });
  }

  /**
   * Render prop name headers
   * @param prop Filter prop
   */
  renderHeader(prop) {
    return (
      <th>
        <div>
          <label className="control-label" style={{ margin: 0 }}>
            <span>{capitalizeWords(prop)}</span>
          </label>
        </div>
      </th>
    );
  }

  /**
   * Render sort
   * @param prop
   */
  renderSort(prop) {
    const {sorts} = this.props;
    const sorted = sorts.get(prop);
    let buttons = [
      <i className="material-icons" style={{color: '#000000'}} key={0}>keyboard_arrow_up</i>,
      <i className="material-icons" style={{color: '#000000'}} key={1}>keyboard_arrow_down</i>
    ];
    // Remove buttons as necessary
    if (sorted === 1) {
      buttons.splice(0, 1);
    } else if (sorted === -1) {
      buttons.splice(1, 1);
    }
    // Don't allow sorting by overdue
    if (prop === 'overdue') {
      buttons = [];
    }
    return (
      <th>
        <span role="button" onClick={this.sort.bind(this, prop)}>
          {buttons}
        </span>
      </th>
    );
  }

  /**
   * Toggle invoice dialog
   */
  toggleInvoice(invoice) {
    this.setState({
      showPayInvoice: !this.state.showPayInvoice,
      selectedInvoice: invoice || Immutable.Map()
    });
  }

  /**
   * Get unpaid text
   * @param invoice
   */
  getUnpaid(invoice) {
    return (
      <div>
        <a target="_blank" style={{color: '#1976D2', cursor: 'pointer'}} onClick={this.toggleInvoice.bind(this, invoice)}>
          Unpaid: pay now
        </a>
      </div>
    );
  }

  /**
   * Change selected payment method
   * @param event
   */
  changePaymentMethod(event) {
    const {value} = event.target;
    this.props.setProp(value, 'paymentMethod');
  }

  /**
   * Pay invoice
   */
  payInvoice() {
    const {payInvoice, amcId, paymentMethod} = this.props;
    const {selectedInvoice} = this.state;
    payInvoice(amcId, selectedInvoice.get('id'), paymentMethod);
    // Mocked until we have endpoints
    this.setState({
      showPayInvoice: false
    });
  }

  /**
   * Create payment method options
   */
  paymentMethodOptions() {
    const {settings, invoices, setProp} = this.props;
    const selectedPaymentMethod = invoices.get('paymentMethod');
    const achAvailable = settings.getIn(['achInfo', 'accountNumber']);
    const ccAvailable = settings.get(['ccInfo', 'number']);
    // Determine payment options
    const paymentOptions = [];
    if (achAvailable) {
      paymentOptions.push({value: 'bank-account', name: 'Bank account'});
      // ACH only, select it
      if (!ccAvailable && selectedPaymentMethod === 'credit-card') {
        setProp('bank-account', 'paymentMethod');
      }
    }
    if (settings.get('getCcInfoSuccess')) {
      paymentOptions.push({value: 'credit-card', name: 'Credit card'});
      // CC only, select it
      if (!ccAvailable && selectedPaymentMethod === 'bank-account') {
        setProp('credit-card', 'paymentMethod');
      }
    }
    return Immutable.fromJS(paymentOptions);
  }

  /**
   * Change current page
   */
  changePage(page) {
    const {perPage} = this.state;
    const {getInvoices, sorts} = this.props;
    const nextPage = page.selected + 1;
    getInvoices(nextPage, perPage, sorts);
    this.setState({
      page: nextPage
    });
  }

  /**
   * Change per page value
   * @param event
   */
  changePerPage(event) {
    let {value: perPage} = event.target;
    perPage = parseInt(perPage, 10);
    const {getInvoices, sorts} = this.props;
    getInvoices(1, perPage, sorts);
    this.setState({
      perPage,
      page: 1
    });
  }

  /**
   * Sort based on prop
   * @param prop
   */
  sort(prop) {
    const {perPage, page} = this.state;
    const {getInvoices, sorts, setProp} = this.props;
    const originalSortVal = sorts.get(prop);
    let newSortVal;
    if (originalSortVal === -1) {
      newSortVal = 1;
    } else {
      newSortVal = -1;
    }
    let initialSort = emptySort;
    if (prop !== 'from') {
      initialSort = initialSort.set('from', 0);
    }
    // Set new sort
    const newSort = initialSort.set(prop, newSortVal);
    setProp(newSort, 'sorts');
    // Retrieve invoices
    getInvoices(page, perPage, newSort);
  }

  render() {
    const {invoices, paymentMethod, total, pages} = this.props;
    const {showPayInvoice, selectedInvoice, page, perPage} = this.state;
    const showPagination = total > perPage;

    const paymentOptions = this.paymentMethodOptions.call(this);
    return (
      <div>
        <div className="row">
          <div className="col-md-12">
            <table className="data-table" style={{ width: '100%', marginTop: '10px' }}>
              <thead>
              <tr>
                {this.renderHeader.call(this, 'date')}
                {this.renderHeader.call(this, 'paid')}
                {this.renderHeader.call(this, 'amount')}
                {this.renderHeader.call(this, 'overdue')}
              </tr>
              <tr>
                {this.renderSort.call(this, 'from')}
                {this.renderSort.call(this, 'isPaid')}
                {this.renderSort.call(this, 'amount')}
                {this.renderSort.call(this, 'overdue')}
              </tr>
              </thead>
              <colgroup>
                <col width="25%" />
                <col width="25%" />
                <col width="25%" />
                <col width="25%" />
              </colgroup>
              <tbody>
              {invoices.map((invoice, index) => {
                // We need to keep this UTC, since it's not a human creating it, it's the server based in UTC, and converting
                // will make things goofy
                const startDate = moment.utc(invoice.get('from'));
                const endDate = moment.utc(invoice.get('to'));
                return (
                  <tr key={index}>
                    <td>
                      <a target="_blank" style={{color: '#1976D2', cursor: 'pointer'}} href={invoice.getIn(['document', 'urlEncoded'])}>
                        {`${startDate.format('MMM DD, YYYY')} - ${endDate.format('MMM DD, YYYY')}`}
                      </a>
                    </td>
                    <td>{invoice.get('isPaid') ? 'Paid' : this.getUnpaid.call(this, invoice)}</td>
                    <td>{this.formatMoney.format(invoice.get('amount'))}</td>
                    <td>{invoice.get('overdue')}</td>
                  </tr>
                );
              })}
              </tbody>
            </table>
          </div>
        </div>
        <Pagination
          show={showPagination}
          pageNum={pages}
          forceSelected={Number(page) - 1}
          onClickPage={::this.changePage}
          containerClass="invoices-pagination-container"
          subContainerClass={"pages pagination"}
          activeClassName={"active"}
          changePerPage={::this.changePerPage}
          perPageValue={perPage}
        />
        <Dialog
          open={showPayInvoice}
          actions={[
            <ActionButton
              type="cancel"
              text="Close"
              onClick={::this.toggleInvoice}
            />,
            <ActionButton
              type="submit"
              text="Pay now"
              onClick={::this.payInvoice}
              style={{marginLeft: '10px'}}
            />
          ]}
          title="Invoice"
        >
          {!paymentOptions.count() &&
            <div className="row">
              <div className="col-md-12">
                <p>There are no available payment methods associated with your account.</p>
                <p>Visit your account <Link to={SETTINGS_URL}>settings</Link> page to add a payment method.
                </p>
              </div>
            </div>
          }
          {!!paymentOptions.count() &&
            <div className="row">
              <div className="col-md-12">
                <VpPlainDropdown
                  options={Immutable.fromJS(paymentOptions)}
                  value={paymentMethod}
                  onChange={this.changePaymentMethod.bind(this)}
                  label="Payment method"
                />
              </div>
              <div className="col-md-12">
                <p>
                  By clicking "Pay now," the total amount of the invoice, ${selectedInvoice.get('amount')}, will be billed to your {paymentMethod === 'credit-card' ? 'credit card' : 'bank account'}.
                </p>
              </div>
            </div>
          }
        </Dialog>
      </div>
    );
  }
}
