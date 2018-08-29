import React, {Component, PropTypes} from 'react';
import moment from 'moment';
import Immutable from 'immutable';
import {NoData} from 'components';
import {Link} from 'react-router';
import {ORDERS_DETAILS} from 'redux/modules/urls';

// Table functions
import {getContact} from 'helpers/genericFunctions';

// Columns
const columnsTemplate = ['fileNumber', 'submittedBy', 'borrower', 'address', 'orderDate', 'completedDate', 'jobType',
                         'taxId', 'appFee', 'techFee'];

// AMC
const amcUnpaid = ['fileNumber', 'clientName', 'borrower', 'address', 'orderDate', 'completedDate', 'clientFee', 'amountPaid', 'balanceDue', 'appFee', 'plAmount', 'invoiceNumber', 'loanNumber', 'markPaid'];
const amcPaid = ['fileNumber', 'clientName', 'borrower', 'address', 'orderDate', 'completedDate', 'paidDate', 'clientFee', 'appFee', 'plAmount', 'checkNumber', 'invoiceNumber', 'loanNumber', 'status'];

const styles = {
  pointer: {whiteSpace: 'nowrap', cursor: 'pointer'},
  m0: { margin: 0 },
  table: { width: '100%', marginTop: '10px' }
};

export default class AccountingTable extends Component {
  static propTypes = {
    // Records
    records: PropTypes.instanceOf(Immutable.List),
    // header display
    headDisplay: PropTypes.func.isRequired,
    // Paid or unpaid
    type: PropTypes.string.isRequired,
    // Auth
    auth: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Mark order paid
    markOrderPaid: PropTypes.func.isRequired,
    // Change order status
    changeOrderStatus: PropTypes.func.isRequired,
    // Set prop for orders
    setPropOrders: PropTypes.func.isRequired,
    // Selected company
    selectedCompany: PropTypes.string
  };

  /**
   * Get current row
   * @param rowIndex
   */
  getRow(rowIndex) {
    return this.props.records.get(rowIndex);
  }

  /**
   * File number column
   * @returns {XML};
   */
  fileNumber() {
    return this.renderHeaderCell('File Number');
  }

  /**
   * File number
   * @returns {XML}
   */
  fileNumberCell(rowIndex) {
    const record = this.getRow(rowIndex);
    // No link for total
    if (record.get('total')) {
      return (
        <div><strong>{this.props.records.getIn([rowIndex, 'fileNumber'])}</strong></div>
      );
    }
    const orderId = record.get('id');
    return (
      <Link className="link block" to={`${ORDERS_DETAILS}/${orderId}`} onClick={::this.resetOrderInState}>
        {this.props.records.getIn([rowIndex, 'fileNumber'])}
      </Link>
    );
  }

  /**
   * Get rid of the current order in memory and hide all tabs
   */
  resetOrderInState() {
    this.props.setPropOrders(Immutable.Map().set('processStatus', 'new'), 'selectedRecord');
  }

  /**
   * Company name column
   * @returns {XML}
   */
  submittedBy() {
    return this.renderHeaderCell('Submitted By');
  }

  /**
   * Company name
   * @returns {XML}
   */
  submittedByCell(rowIndex) {
    return (
      <div>{this.props.records.getIn([rowIndex, 'customer', 'name'])}</div>
    );
  }

  /**
   * Borrower column
   * @returns {XML}
   */
  borrower() {
    return this.renderHeaderCell('Borrower');
  }

  /**
   * Borrower
   * @returns {XML}
   */
  borrowerCell(rowIndex) {
    // Get borrower
    const borrower = getContact(this.props.records.getIn([rowIndex]));
    if (!borrower) {
      return null;
    } else {
      return (
        <div>{borrower.get('firstName')} {borrower.get('lastName')}</div>
      );
    }
  }

  /**
   * Property address
   * @returns {XML}
   */
  address() {
    return this.renderHeaderCell('Address');
  }

  /**
   * Property address
   * @returns {XML}
   */
  addressCell(rowIndex) {
    return (
      <div>{this.props.records.getIn([rowIndex, 'property', 'address1'])}</div>
    );
  }

  /**
   * Order date
   * @returns {XML}
   */
  orderDate() {
    return this.renderHeaderCell('Order Date');
  }

  /**
   * Ordered date
   * @returns {XML}
   */
  orderDateCell(rowIndex) {
    const record = this.props.records.get(rowIndex);
    if (this.isTotalRow(record)) {
      return <div/>;
    }
    const date = record.get('orderedAt');
    return (
      <div>{date ? moment(date).format('MM/DD/YYYY') : '--'}</div>
    );
  }

  /**
   * Completed date
   * @returns {XML}
   */
  completedDate() {
    return this.renderHeaderCell('Completed Date');
  }

  /**
   * Completed date
   * @returns {XML}
   */
  completedDateCell(rowIndex) {
    const record = this.props.records.get(rowIndex);
    if (this.isTotalRow(record)) {
      return <div/>;
    }
    const date = record.get('completedAt');
    return (
      <div>{ date ? moment(date).format('MM/DD/YYYY') : '--'}</div>
    );
  }

  /**
   * Job type
   * @returns {XML}
   */
  jobType() {
    return this.renderHeaderCell('Job Type');
  }

  /**
   * Job type
   * @returns {XML}
   */
  jobTypeCell(rowIndex) {
    return (
      <div>{this.props.records.getIn([rowIndex, 'jobType', 'title'])}</div>
    );
  }

  /**
   * Application fee
   * @returns {XML}
   */
  appFee() {
    return this.renderHeaderCell('App Fee');
  }

  /**
   * Application fee
   * @returns {XML}
   */
  appFeeCell(rowIndex) {
    const record = this.props.records.get(rowIndex);
    const fee = record.get('fee') || 0;
    return (
      <div>${fee.toFixed(2)}</div>
    );
  }

  /**
   * Tech fee
   * @returns {XML}
   */
  techFee() {
    return this.renderHeaderCell('Tech Fee');
  }

  /**
   * Tech fee
   * @returns {XML}
   */
  techFeeCell(rowIndex) {
    const record = this.props.records.get(rowIndex);
    const fee = record.get('techFee') || 0;
    return (
      <div>${fee.toFixed(2)}</div>
    );
  }

  /**
   * Tax ID column
   * @returns {XML}
   */
  taxId() {
    return this.renderHeaderCell('Tax ID');
  }

  /**
   * Tax ID cell
   * @returns {XML}
   */
  taxIdCell(rowIndex) {
    const record = this.props.records.get(rowIndex);
    return (
      <div>{record.getIn(['company', 'taxId']) || record.getIn(['assignee', 'taxIdentificationNumber'])}</div>
    );
  }

  /**
   * Paid at header
   * @returns {XML}
   */
  paidAt() {
    return this.renderHeaderCell('Paid Date');
  }

  /**
   * Paid date cell
   * @returns {XML}
   */
  paidAtCell(rowIndex) {
    const record = this.props.records.get(rowIndex);
    if (record.get('total')) {
      return <div/>;
    }
    return (
      <div>{moment(record.get('paidAt')).format('MM/DD/YYYY')}</div>
    );
  }

  /*******************
   * AMC-only columns
   ******************/
  /**
   * Client name column
   */
  clientName() {
    return this.renderHeaderCell('Client Name');
  }

  /**
   * Client name
   */
  clientNameCell(rowIndex) {
    const record = this.props.records.get(rowIndex);
    if (record.get('total')) {
      return <div/>;
    }
    // Get clientName
    return <div>{record.get('clientName', '--')}</div>;
  }

  /**
   * Client fee column
   */
  clientFee() {
    return this.renderHeaderCell('Client Fee');
  }

  /**
   * Client fee
   */
  clientFeeCell(rowIndex) {
    const record = this.props.records.get(rowIndex);
    if (record.get('total')) {
      return <div/>;
    }
    const fee = record.get('fee', '');
    if (fee) {
      // Get client fee
      return <div>$ {fee.toFixed(2)}</div>;
    } else {
      return <div>--</div>;
    }
  }

  /**
   * Amount paid column
   */
  amountPaid() {
    return this.renderHeaderCell('Amount Paid');
  }

  /**
   * Amount paid
   */
  amountPaidCell(rowIndex) {
    const record = this.props.records.get(rowIndex);
    if (record.get('total')) {
      return <div/>;
    }
    // Get amount paid
    return <div>{record.get('NEED DATA', 'NEED DATA')}</div>;
  }

  /**
   * Balance due column
   */
  balanceDue() {
    return this.renderHeaderCell('Balance due');
  }

  /**
   * Balance due
   */
  balanceDueCell(rowIndex) {
    const record = this.props.records.get(rowIndex);
    if (record.get('total')) {
      return <div/>;
    }
    // Get amount paid
    return <div>{record.get('NEED DATA', 'NEED DATA')}</div>;
  }

  /**
   * PL amount column
   */
  plAmount() {
    return this.renderHeaderCell('P/L Amount');
  }

  /**
   * PL amount
   */
  plAmountCell(rowIndex) {
    const row = this.props.records.get(rowIndex);
    if (row.get('total')) {
      return <div/>;
    }
    return <div>{row.get('NEED DATA', 'NEED DATA')}</div>;
  }

  /**
   * Invoice column
   */
  invoiceNumber() {
    return this.renderHeaderCell('Invoice Number');
  }

  /**
   * Invoice number
   */
  invoiceNumberCell(rowIndex) {
    const row = this.props.records.get(rowIndex);
    if (row.get('total')) {
      return <div/>;
    }
    return <div>{row.get('NEED DATA', 'NEED DATA')}</div>;
  }

  /**
   * Loan column
   */
  loanNumber() {
    return this.renderHeaderCell('Loan Number');
  }

  /**
   * Loan number
   */
  loanNumberCell(rowIndex) {
    const row = this.props.records.getIn([rowIndex]);
    if (row.get('total')) {
      return <div/>;
    }
    return <div>{row.get('NEED DATA', 'NEED DATA')}</div>;
  }

  /**
   * Mark paid column
   */
  markPaid() {
    return this.renderHeaderCell('Mark Paid');
  }

  /**
   * Mark paid
   */
  markPaidCell(rowIndex) {
    const id = this.props.records.getIn([rowIndex, 'id']);
    if (!id) {
      return <div/>;
    }
    return (
      <a className="link block" onClick={this.props.markOrderPaid.bind(this, id)} style={styles.pointer}>Mark paid</a>
    );
  }

  /**
   * Paid date column
   */
  paidDate() {
    return this.renderHeaderCell('Paid Date');
  }

  /**
   * Paid date
   */
  paidDateCell(rowIndex) {
    const row = this.props.records.get(rowIndex);
    if (row.get('total')) {
      return <div/>;
    }
    return <div>{row.get('NEED DATA', 'NEED DATA')}</div>;
  }

  /**
   * Check number column
   */
  checkNumber() {
    return this.renderHeaderCell('Check Number');
  }

  /**
   * Check number
   */
  checkNumberCell(rowIndex) {
    const record = this.props.records.get(rowIndex);
    if (record.get('total')) {
      return <div/>;
    }
    return <div>{record.get('NEED DATA', 'NEED DATA')}</div>;
  }

  /**
   * Appraiser column
   */
  appraiser() {
    return this.renderHeaderCell('Appraiser');
  }

  /**
   * Appraiser
   */
  appraiserCell(rowIndex) {
    const record = this.props.records.get(rowIndex);
    if (record.get('total')) {
      return <div/>;
    }
    return <div>{record.getIn(['assignee', 'displayName'])}</div>;
  }

  /**
   * Paid status column
   */
  status() {
    return this.renderHeaderCell('Status');
  }

  /**
   * Paid status cell
   */
  statusCell(rowIndex) {
    const id = this.props.records.getIn([rowIndex, 'id']);
    if (!id) {
      return <div/>;
    }
    return (
      <a className="link block" onClick={this.props.changeOrderStatus.bind(this, id)} style={styles.pointer}>Change status</a>
    );
  }

  /**
   * Check if a row is a total or grand total row
   * @param record
   * @returns {boolean}
   */
  isTotalRow(record) {
    return record.get('total') === true;
  }

  renderHeaderCell(label) {
    return (
      <div key={label.toLowerCase().replace(new RegExp(/\s/, 'g'), '-')}>
        <label className="control-label" style={styles.m0}>
          {label}
        </label>
      </div>
    );
  }

  /**
   * Appraiser user columns
   * @param columns Columns template
   * @param type View type
   */
  appraiserColumns(columns, type) {
    if (type === 'paid') {
      columns.push('paidAt');
    }
    return columns;
  }

  /**
   * AMC user columns
   * @param type
   */
  amcColumns(type) {
    return type === 'unpaid' ? amcUnpaid : amcPaid;
  }

  /**
   * Adds the assignee column if the user is a "boss" (⌐■_■)
   *
   * @param {Object} user
   * @param {string} selectedCompany
   * @param {Array} columns
   * @return {Array}
   */
  addAssigneeColumn(user, selectedCompany, columns) {
    if (user.get('isBoss') && selectedCompany) {
      columns.splice(columns.indexOf('borrower'), 0, 'assignee');
    }

    return columns;
  }

  /**
   * Assignee column
   */
  assignee() {
    return this.renderHeaderCell('Assignee');
  }

  /**
   * Assignee cell
   *
   * @param {Number} rowIndex
   */
  assigneeCell(rowIndex) {
    const record = this.props.records.get(rowIndex);
    if (record.getIn(['assignee', 'displayName'])) {
      return (
        <div>
          {record.getIn(['assignee', 'displayName'])}
        </div>
      );
    }

    return <div/>;
  }

  render() {
    const {records, type, auth, selectedCompany} = this.props;
    const columns = columnsTemplate.slice();
    let tableColumns = this.appraiserColumns(columns, type);
    tableColumns = this.addAssigneeColumn(auth.get('user'), selectedCompany, columns);

    if (auth.getIn(['user', 'type']) === 'manager') {
      tableColumns.push('appraiser');
    }

    const columnCount = tableColumns.length;

    // No data
    if (!records) {
      return <NoData text="No accounting records available"/>;
    }

    return (
      <div>
        <table className="data-table" style={styles.table}>
          <thead>
            { this.props.headDisplay(columnCount) }
            <tr key="columns">
              {tableColumns.map((column) => {
                return (
                  <th key={column}>
                    {this[column]()}
                  </th>
                );
              })}
            </tr>
          </thead>
          <tbody>
            {records.map((record, rowIndex) => {
              return (
                <tr key={`row_${rowIndex}`}>
                  {tableColumns.map((column) => {
                    return (
                      <td key={ 'column_' + rowIndex + '_' + column}>{this[`${column}Cell`](rowIndex)}</td>
                    );
                  })}
                </tr>
              );
            })}
          </tbody>
        </table>
      </div>
    );
  }
}
