import React, {Component, PropTypes} from 'react';

import {
  Confirm,
  DateTimePicker
} from 'components';

import moment from 'moment';
import Immutable from 'immutable';

// Input field props for datetimepicker
const dateTimeInputProps = {
  readOnly: true,
  style: {
    cursor: 'default'
  }
};

// General date format
const dateTimeFormat = 'MM/DD/YYYY h:mm A';
const dateFormat = 'MM/DD/YYYY';

/**
 * Schedule an inspection on an accepted order
 */
export default class Inspection extends Component {
  static propTypes = {
    // Whether to show the dialog
    show: PropTypes.bool.isRequired,
    // Hide dialog
    hide: PropTypes.func.isRequired,
    // Submit
    submit: PropTypes.func.isRequired,
    // Set property
    setProp: PropTypes.func.isRequired,
    // Orders
    orders: PropTypes.instanceOf(Immutable.Map),
    // If scheduling an inspection, rather than completing one
    schedule: PropTypes.bool
  };

  constructor(props) {
    super(props);

    this.hideModal = ::this.hideModal;
    this.handleSubmit = ::this.handleSubmit;
    this.inspectionDateInputProps = {
      ...dateTimeInputProps,
      onClick: () => {
        this.refs.inspectionDate.onClick();
      }
    };
    this.estimatedCompletionDateInputProps = {
      ...dateTimeInputProps,
      onClick: () => {
        this.refs.estimatedCompletionDate.onClick();
      }
    };
    this.changeScheduledAt = this.changeDate.bind(this, 'scheduledAt');
    this.changeCompletedAt = this.changeDate.bind(this, 'completedAt');
    this.changeEstimatedCompletionDate = this.changeDate.bind(this, 'estimatedCompletionDate');
    this.dialogBody = ::this.dialogBody;
    this.submitDisabled = ::this.submitDisabled;
    this.checkErrors = ::this.checkErrors;
    this.getCustomerSettings = ::this.getCustomerSettings;
    this.getDueDate = ::this.getDueDate;
    this.triggerError = ::this.triggerError;
    this.inspectionDatePicker = ::this.inspectionDatePicker;
  }

  /**
   * Set initial inspection date and estimated completion time
   */
  componentDidMount() {
    const {setProp, orders, schedule} = this.props;
    const inspectionProp = schedule ? 'scheduleInspection' : 'inspectionComplete';
    const selectedRecord = orders.get('selectedRecord');
    const scheduledAt = selectedRecord.get('inspectionScheduledAt');
    const estimatedCompletionDate = selectedRecord.get('estimatedCompletionDate');
    // If we have records already, use those
    if (scheduledAt) {
      setProp(moment(scheduledAt).format(dateTimeFormat), inspectionProp, 'scheduledAt');
    } else {
      setProp(moment().format(dateTimeFormat), inspectionProp, 'scheduledAt');
    }
    if (estimatedCompletionDate) {
      setProp(moment(estimatedCompletionDate).format(dateFormat), inspectionProp, 'estimatedCompletionDate');
    } else {
      setProp(moment().format(dateFormat), inspectionProp, 'estimatedCompletionDate');
    }
  }

  /**
   * Remove errors
   */
  componentWillUnmount() {
    const {setProp, schedule} = this.props;
    const prop = this.getScheduleProp(schedule);
    setProp(Immutable.Map(), 'errors', prop);
  }

  /**
   * Get the due date for this order
   */
  getDueDate() {
    return this.props.orders.getIn(['selectedRecord', 'dueDate']);
  }

  /**
   * Get customer settings to determine days prior to due date that inspection date and ECD must be within
   */
  getCustomerSettings() {
    return this.props.orders.getIn(['selectedRecord', 'customer', 'settings']);
  }

  /**
   * Get prop that corresponds with this view (either schedule or complete)
   * @param schedule Bool
   */
  getScheduleProp(schedule) {
    return schedule ? 'scheduleInspection' : 'inspectionComplete';
  }

  /**
   * Retrieve date errors for the currently shown pickers
   * @param orders
   * @param scheduleProp
   */
  getDateErrors(orders, scheduleProp) {
    // Base error object
    const errorObject = {
      scheduledAtError: null,
      estimatedCompletionDateError: null,
      completedAtError: null
    };
    // Return null values if disabled
    if (this.getViolationValue(orders) === 'disabled') {
      return Immutable.fromJS(errorObject);
    }
    // Return actual errors
    return Immutable.fromJS({
      // Errors
      scheduledAtError: orders.getIn(['errors', scheduleProp, 'scheduledAt']),
      estimatedCompletionDateError: orders.getIn(['errors', scheduleProp, 'estimatedCompletionDate']),
      completedAtError: orders.getIn(['errors', scheduleProp, 'completedAt'])
    });
  }

  /**
   * Get the current customer violate date setting
   * @param orders Orders reducer
   */
  getViolationValue(orders) {
    return orders.getIn(['selectedRecord', 'customer', 'settings', 'preventViolationOfDateRestrictions']);
  }

  /**
   * Change estimated completion date
   * @param name Prop name
   * @param date Incoming date string
   */
  changeDate(name, date) {
    const {setProp, schedule} = this.props;
    const prop = this.getScheduleProp(schedule);
    // Check to see if the change introduces any errors
    this.checkErrors(prop, name, date);
    setProp(date, prop, name);
  }

  /**
   * Check for any errors that occur when setting a date
   * @param prop
   * @param name
   * @param date
   */
  checkErrors(prop, name, date) {
    // Customer settings
    const customerSettings = this.getCustomerSettings();
    const daysBeforeInspection = customerSettings.get('daysPriorInspectionDate');
    const daysBeforeEcd = customerSettings.get('daysPriorEstimatedCompletionDate');
    const preventViolations = customerSettings.get('preventViolationOfDateRestrictions');
    // Check to make sure we're not violating anything from the AS settings
    if (preventViolations !== 'disabled') {
      // Due date, rounded down to day
      const dueDate = moment(this.getDueDate(name));
      // No due date
      if (!dueDate) {
        return;
      }
      if (name === 'estimatedCompletionDate') {
        // Trigger error, invalid ECD
        this.triggerError(date, dateFormat, dueDate, daysBeforeEcd, preventViolations, [prop, name]);
      } else if (name === 'scheduledAt') {
        // Trigger error, invalid scheduled at
        this.triggerError(date, dateTimeFormat, dueDate, daysBeforeInspection, preventViolations,
          [prop, name]);
      } else if (name === 'completedAt') {
        this.triggerError(date, dateTimeFormat, dueDate, daysBeforeInspection, preventViolations,
          [prop, name]);
      }
    }
  }

  /**
   * Trigger error on date not being far enough before due date
   * @param date Input date
   * @param format Date format
   * @param dueDate Due date for order
   * @param subtraction Number of days before
   * @param preventViolation Setting for preventing date violations
   * @param errorPropPath
   */
  triggerError(date, format, dueDate, subtraction, preventViolation, errorPropPath) {
    const input = moment(date, format);
    dueDate = dueDate.subtract(subtraction, 'days');
    let message;
    if (subtraction === 0) {
      message = preventViolation === 'hardstop' ?
                `Your customer requires this date to be set prior to the due date.` :
                `Your customer requests this date be set prior to the due date.`;
    } else {
      message = preventViolation === 'hardstop' ?
                `Your customer requires this date to be at least ${subtraction} days prior to the due date.` :
                `Your customer requests this date be at least ${subtraction} days prior to the due date.`;
    }
    // Add/remove error
    if (dueDate.isBefore(input)) {
      this.props.setProp(message, 'errors', ...errorPropPath);
    } else {
      this.props.setProp(null, 'errors', ...errorPropPath);
    }
  }

  /**
   * Configure datepicker
   * @param schedule Whether scheduling or completing
   * @param inspectionDate Inspection date
   * @param completedDate Completion date
   */
  inspectionDatePicker(schedule, inspectionDate, completedDate) {
    let picker;
    // Schedule inspection
    if (schedule) {
      picker = (
        <DateTimePicker
          ref="inspectionDate"
          onChange={this.changeScheduledAt}
          dateTime={inspectionDate}
          format={dateTimeFormat}
          inputFormat={dateTimeFormat}
          inputProps={this.inspectionDateInputProps}
          mode="datetime"
        />
      );
    // complete inspection
    } else {
      picker = (
        <DateTimePicker
          ref="inspectionDate"
          onChange={this.changeCompletedAt}
          dateTime={completedDate}
          format={dateTimeFormat}
          inputFormat={dateTimeFormat}
          inputProps={this.inspectionDateInputProps}
          maxDate={moment(this.getDueDate())}
        />
      );
    }
    return picker;
  }

  /**
   * Inspection/ECD warning message
   * @param violationValue Hardstop or warning
   * @param daysPrior Days prior to due date
   * @param inspection If inspection (if false, ECD)
   */
  daysPriorWarning(violationValue, daysPrior, inspection = true) {
    let message;
    const messageBegin = inspection ? 'Inspection date' : 'Estimated completion date';
    if (daysPrior === 0) {
      message = <small>{messageBegin} {violationValue === 'hardstop' ? 'must' : 'should'} be set to before the due date</small>;
    } else {
      message = <small>{messageBegin} {violationValue === 'hardstop' ? 'must' : 'should'} be {daysPrior} days before due date</small>;
    }
    return message;
  }

  /**
   * Create dialog body
   * @returns {XML}
   */
  dialogBody() {
    const {orders, schedule} = this.props;
    // Get correct props
    const scheduleProp = this.getScheduleProp(schedule);
    const inspectionDate = orders.getIn([scheduleProp, 'scheduledAt']) || moment().format(dateTimeFormat);
    const completedDate = orders.getIn([scheduleProp, 'completedAt']) || inspectionDate;
    const estimatedCompletionDate = orders.getIn([scheduleProp, 'estimatedCompletionDate'],
      moment().format(dateFormat));
    // Errors
    const errors = this.getDateErrors(orders, scheduleProp);
    const customerSettings = orders.getIn(['selectedRecord', 'customer', 'settings']);
    // Violate value
    const violationValue = customerSettings.get('preventViolationOfDateRestrictions');
    // Classes (error and not error)
    let scheduledClasses;
    if (schedule) {
      scheduledClasses = errors.get('scheduledAtError') ? 'form-group has-error is-focused' : 'form-group';
    } else {
      scheduledClasses = errors.get('completedAtError') ? 'form-group has-error is-focused' : 'form-group';
    }
    const completionClasses = errors.get('estimatedCompletionDateError') ? 'form-group has-error is-focused' : 'form-group';
    // Due date
    const dueDate = this.getDueDate();
    return (
      <div>
        <div className="row">
          <div className="col-md-4">
            <label className="control-label"><strong>Order Due Date</strong></label>
            <p>{dueDate ? moment(dueDate).format('MM/DD/YYYY hh:mm A') : 'No due date set'}</p>
          </div>
        </div>
        <div className="row">
          <div className="col-md-6">
            <div className={scheduledClasses}>
              <label className="control-label">Inspection Date & Time</label>
              {/*Inspection scheduled/completed date*/}
              {this.inspectionDatePicker(schedule, inspectionDate, completedDate)}
              <p className="help-block">
                {schedule ? errors.get('scheduledAtError') : errors.get('completedAtError')}
              </p>
              {violationValue !== 'disabled' &&
                this.daysPriorWarning(violationValue, customerSettings.get('daysPriorInspectionDate'))
              }
            </div>
          </div>
          <div className="col-md-6">
            <div className={completionClasses}>
              <label className="control-label">Estimated Completion Date</label>
              <DateTimePicker
                ref="estimatedCompletionDate"
                onChange={this.changeEstimatedCompletionDate}
                dateTime={estimatedCompletionDate}
                format={dateFormat}
                inputFormat={dateFormat}
                inputProps={this.estimatedCompletionDateInputProps}
                mode="date"
              />
              <p className="help-block">{errors.get('estimatedCompletionDateError')}</p>
              {violationValue !== 'disabled' &&
                this.daysPriorWarning(violationValue, customerSettings.get('daysPriorEstimatedCompletionDate'), false)
              }
            </div>
          </div>
        </div>
      </div>
    );
  }

  /**
   * Don't allow the user to submit until all values are filled in
   * @returns {boolean}
   */
  submitDisabled() {
    const {orders, schedule} = this.props;
    const errors = this.getDateErrors(orders, this.getScheduleProp(schedule));
    // Violate date settings
    const disableValue = this.getViolationValue(orders);
    return !!(disableValue === 'hardstop' && errors.toList().filter(error => error).count());
  }

  /**
   * Hide the modal
   */
  hideModal() {
    this.props.hide();
  }

  /**
   * Handle submitting inspection
   */
  handleSubmit() {
    const {submit, orders, schedule} = this.props;
    const scheduleProp = this.getScheduleProp(schedule);
    // Get inputs dates
    const inspectionDate = orders.getIn([scheduleProp, 'scheduledAt']);
    const estimatedCompletionDate = orders.getIn([scheduleProp, 'estimatedCompletionDate']);
    const completedAt = orders.getIn([scheduleProp, 'completedAt']);
    // Format
    const inspectionDateFormatted = inspectionDate ? moment(inspectionDate, dateTimeFormat).format() :
                                    moment().format();
    const completedAtFormatted = completedAt ? moment(completedAt, dateTimeFormat).format() :
                                 moment().format();
    const estimatedCompletionDateFormatted = estimatedCompletionDate ?
                                             moment(estimatedCompletionDate, dateFormat).format() :
                                             moment().add(1, 'days').format();
    // Handle submit
    submit(schedule ? inspectionDateFormatted : completedAtFormatted, estimatedCompletionDateFormatted);
  }

  render() {
    const {show, schedule} = this.props;
    const disableSubmit = this.submitDisabled();

    return (
      <Confirm
        bodyClassName="overflow-visible"
        body={this.dialogBody()}
        title={schedule ? 'Schedule Inspection' : 'Inspection Complete'}
        show={show}
        hide={this.hideModal}
        submit={this.handleSubmit}
        submitDisabled={disableSubmit}
      />
    );
  }
}
