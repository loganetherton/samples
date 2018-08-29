import React, {Component, PropTypes} from 'react';

import {Confirm, VpTextField, VpPlainDropdown, VpDateRange} from 'components';

import Immutable from 'immutable';
import moment from 'moment';

const conditions = Immutable.fromJS([
  {value: 'fee-increase', name: 'Request fee increase'},
  {value: 'due-date-extension', name: 'Request due date extension'},
  {value: 'fee-increase-and-due-date-extension', name: 'Request fee increase and due date extension'},
  {value: 'other', name: 'Other'}
]);

/**
 * Accept with conditions for an order
 */
export default class AcceptOrderWithConditions extends Component {
  static propTypes = {
    // Whether to show the dialog
    show: PropTypes.bool.isRequired,
    // Set property
    setProp: PropTypes.func.isRequired,
    // Hide dialog
    hide: PropTypes.func.isRequired,
    // Accept with conditions property
    acceptWithConditions: PropTypes.instanceOf(Immutable.Map),
    // Submit
    submit: PropTypes.func.isRequired,
    // Orders
    orders: PropTypes.instanceOf(Immutable.Map)
  };

  constructor(props) {
    super(props);

    this.changeInput = ::this.changeInput;
    this.changeDate = ::this.changeDate;
    this.hideModal = ::this.hideModal;
    this.changeReason = ::this.changeReason;
  }

  /**
   * Default accept with conditions reason to fee increase
   */
  componentDidMount() {
    this.props.setProp('fee-increase', 'acceptWithConditions', 'request');
  }

  /**
   * Change the condition reason
   * @param event Synthetic event
   */
  changeReason(event) {
    this.props.setProp(event.target.value, 'acceptWithConditions', 'request');
  }

  /**
   * Change an input
   */
  changeInput(event) {
    const {name, value} = event.target;
    this.props.setProp(value, 'acceptWithConditions', name, 'validate');
  }

  /**
   * Change due date
   * @param date Incoming date string
   */
  changeDate(date) {
    this.props.setProp(date, 'acceptWithConditions', 'dueDate');
  }

  /**
   * Create dialog body
   * @returns {XML}
   */
  dialogBody() {
    const {acceptWithConditions, orders} = this.props;
    const reason = acceptWithConditions.get('request');
    const reasonCols = reason === 'due-date-extension' || reason === 'other' ? 12 : 8;
    const date = orders.getIn(['acceptWithConditions', 'dueDate']) ?
                 moment(orders.getIn(['acceptWithConditions', 'dueDate'])) : moment();

    return (
      <div>
        <div className="row">
          <div className={'col-md-' + reasonCols}>
            <VpPlainDropdown
              options={conditions}
              value={reason}
              onChange={this.changeReason}
              label="Reason"
            />
          </div>
          {reasonCols === 8 && <div className="col-md-4">
            {this.feeInput(reason, acceptWithConditions, (orders.getIn(['errors', 'acceptWithConditions', 'fee']) ||
                                                          orders.getIn(['errors', 'acceptWithConditions', 'fee', 'message'])))}
          </div> }
        </div>
        <div className="row">
          <div className="col-md-12">
            {this.dueDate(reason, acceptWithConditions, date, orders.getIn(['errors', 'acceptWithConditions', 'dueDate', 'message']))}
          </div>
        </div>
        <div className="row">
          <div className="col-md-12">{this.explanationInput(acceptWithConditions, orders.getIn(['errors', 'acceptWithConditions', 'explanation', 'message']))}</div>
        </div>
      </div>
    );
  }

  /**
   * If a fee input is necessary, create the input
   * @param reason The reason for the conditions
   * @param acceptWithConditions Accept with conditions object
   * @param errors Validation errors
   */
  feeInput(reason, acceptWithConditions, errors) {
    // Show if fee increase requested
    if (reason === 'fee-increase' || reason === 'fee-increase-and-due-date-extension') {
      return (
        <VpTextField
          name="fee"
          value={acceptWithConditions.get('fee')}
          label="Total Requested Fee"
          onChange={this.changeInput}
          error={errors ? 'A fee greater than 0.00 must be set.' : ''}
          defaultValue="0.00"
        />
      );
    }
  }

  /**
   * Provide explanation
   * @param acceptWithConditions Accept with conditions record
   * @param errors Validation errors
   * @returns {XML}
   */
  explanationInput(acceptWithConditions, errors) {
    return (
      <VpTextField
        name="explanation"
        value={acceptWithConditions.get('explanation')}
        label="Provide Explanation"
        onChange={this.changeInput}
        error={errors}
        multiLine
      />
    );
  }

  /**
   * Set due date for accept with conditions
   * @param reason Conditions reason
   * @param acceptWithConditions Accept with conditions form
   * @param date Selected date
   * @param errors Validation errors
   */
  dueDate(reason, acceptWithConditions, date, errors) {
    if (reason === 'due-date-extension' || reason === 'fee-increase-and-due-date-extension') {
      return (
        <VpDateRange
          hintText="Select a Due Date"
          minDate={moment()}
          changeHandler={this.changeDate}
          date={date}
          label="Due Date"
          error={errors}
        />
      );
    }
  }

  /**
   * Checks whether or not the submit button should be disabled
   *
   * @returns {boolean}
   */
  submitDisabled() {
    const {orders, acceptWithConditions} = this.props;
    const errors = orders.getIn(['errors', 'acceptWithConditions']);
    const reason = acceptWithConditions.get('request');

    if (errors && errors.toList().filter(error => error).count()) {
      return true;
    }

    if (reason === 'fee-increase') {
      return !acceptWithConditions.get('fee');
    }

    if (reason === 'due-date-extension') {
      return !acceptWithConditions.get('dueDate');
    }

    if (reason === 'fee-increase-and-due-date-extension') {
      return !acceptWithConditions.get('fee') || !acceptWithConditions.get('dueDate');
    }

    if (reason === 'other') {
      return !acceptWithConditions.get('explanation');
    }

    return false;
  }

  hideModal() {
    this.props.setProp(Immutable.Map(), 'acceptWithConditions');
    this.props.hide();
  }

  render() {
    const {show, submit} = this.props;

    return (
      <Confirm
        body={this.dialogBody.call(this)}
        title="Accept with Conditions"
        show={show}
        hide={this.hideModal}
        submit={submit}
        submitDisabled={this.submitDisabled.call(this)}
      />
    );
  }
}
