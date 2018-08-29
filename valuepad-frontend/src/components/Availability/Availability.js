import React, {Component, PropTypes} from 'react';
import Immutable from 'immutable';
import {
  ActionButton,
  Void,
  VpPlainDropdown,
  VpTextField,
  DateTimePicker
} from 'components';

import {Dialog} from 'material-ui';

// Availability options
const availability = Immutable.fromJS([
  {name: 'Available', value: false},
  {name: 'On Vacation', value: true}
]);

// Input field props for datetimepicker
const dateTimeInputProps = {
  readOnly: true,
  style: {
    cursor: 'default'
  }
};

// General date format
const dateTimeFormat = 'MM/DD/YYYY h:mm A';
import moment from 'moment';

export default class Availability extends Component {
  static propTypes = {
    // Auth
    auth: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Form
    form: PropTypes.instanceOf(Immutable.Map),
    // Errors
    errors: PropTypes.instanceOf(Immutable.Map),
    // Set property
    setProp: PropTypes.func.isRequired,
    // Get ACH info
    getAvailability: PropTypes.func.isRequired,
    // Set availability settings
    setAvailability: PropTypes.func.isRequired,
    // Settings
    settings: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Selected appraiser
    selectedAppraiser: PropTypes.number,
    // Selected customer
    selectedCustomer: PropTypes.number.isRequired
  };

  /**
   * Start with dialog closed
   */
  constructor(props) {
    super(props);
    this.state = {
      successDialog: false,
      failDialog: false
    };

    this.changeDropdown = ::this.changeDropdown;
    this.formChange = ::this.formChange;
    this.submit = ::this.submit;
    this.changeFromDate = this.changeDate.bind(this, 'from');
    this.changeToDate = this.changeDate.bind(this, 'to');
    this.closeAvailabilityUpdateSuccess = this.closeAvailabilityUpdate.bind(this, 'successDialog');
    this.closeAvailabilityUpdateFail = this.closeAvailabilityUpdate.bind(this, 'failDialog');
    this.inputPropsFrom = {...dateTimeInputProps, onClick: () => {
      this.refs.fromPicker.onClick();
    }};
    this.inputPropsTo = {...dateTimeInputProps, onClick: () => {
      this.refs.toPicker.onClick();
    }};
  }

  /**
   * Retrieve existing ACH info on load
   */
  componentDidMount() {
    const {auth, getAvailability, selectedAppraiser, selectedCustomer} = this.props;
    const user = auth.get('user');
    const userType = user.get('type');

    if (userType !== 'customer' || selectedAppraiser) {
      getAvailability(selectedAppraiser || user, selectedCustomer);
    }
  }

  /**
   * Get availability if on load here
   * @param nextProps
   */
  componentWillReceiveProps(nextProps) {
    const {settings, selectedAppraiser, auth, getAvailability, selectedCustomer} = this.props;
    const {settings: nextSettings, selectedCustomer: nextCustomer} = nextProps;
    const user = auth.get('user');

    // Failed to update availability
    if (typeof settings.get('setAvailabilitySuccess') === 'undefined' && nextSettings.get('setAvailabilitySuccess') === false) {
      this.setState({
        failDialog: true,
        successDialog: false
      });
    }

    if (typeof nextCustomer !== 'undefined' && selectedCustomer !== nextCustomer) {
      getAvailability(user.get('type') === 'customer' ? selectedAppraiser : user, nextCustomer);
    }
  }

  /**
   * Change dropdown
   * @param event SyntheticEvent
   */
  changeDropdown(event) {
    const {form, setProp} = this.props;
    const isOnVacation = event.target.value === 'true';
    setProp(isOnVacation, 'availability', 'form', 'isOnVacation');
    // If on vacation, see if we have default values set
    if (isOnVacation) {
      if (!form.get('from')) {
        setProp(moment().format(), 'availability', 'form', 'from');
      }
      if (!form.get('to')) {
        setProp(moment().format(), 'availability', 'form', 'to');
      }
    }
  }

  /**
   * Update form value
   * @param event
   */
  formChange(event) {
    const {name, value} = event.target;
    this.props.setProp(value, 'availability', 'form', name);
  }

  /**
   * Change a date picker value
   * @param fromOrTo Whether from or to time
   * @param date Incoming date property
   */
  changeDate(fromOrTo, date) {
    const formatDate = moment(date, dateTimeFormat).format();
    this.props.setProp(formatDate, 'availability', 'form', fromOrTo);

    this.validateDate(fromOrTo, date);
  }

  /**
   * Validate date input
   * @param fromOrTo Whether from or to time
   * @param date Incoming date property
   */
  validateDate(fromOrTo, date) {
    const newDate = moment(date, dateTimeFormat);
    const {form} = this.props;

    if (form.get(fromOrTo === 'from' ? 'to' : 'from')) {
      // We're using newDate here because the form constant holds a reference to the
      // old input that we don't care about
      const fromDate = fromOrTo === 'to' ? moment(form.get('from')) : newDate;
      const toDate = fromOrTo === 'from' ? moment(form.get('to')) : newDate;

      if (toDate <= fromDate) {
        this.props.setProp(Immutable.List().push('The away date must be before the return date.'), 'availability', 'errors', 'from');
      } else {
        this.props.setProp(Immutable.List(), 'availability', 'errors', 'from');
      }
    }
  }

  /**
   * Determine if form disabled
   */
  isDisabled() {
    const form = this.props.form;
    // On vacation, make sure date inputs set
    if (form.get('isOnVacation')) {
      if (!form.get('from') || !form.get('to')) {
        return true;
      }

      const fromDate = moment(form.get('from'));
      const toDate = moment(form.get('to'));

      if (toDate <= fromDate) {
        return true;
      }
    }
    return false;
  }

  /**
   * Close availability update dialog
   */
  closeAvailabilityUpdate(type) {
    this.setState({
      [type]: false
    });
  }

  /**
   * Submit availability
   */
  submit() {
    const {settings, setAvailability, auth, selectedCustomer} = this.props;
    // Show availability updated dialog
    this.setState({
      successDialog: true
    });

    let availability = settings.getIn(['availability', 'form']);
    // Don't send times if not on vacation
    if (availability.get('isOnVacation') === false) {
      availability = Immutable.fromJS({
        isOnVacation: false
      });
    }
    setAvailability(auth.get('user'), selectedCustomer, availability.toJS());
  }

  render() {
    const {form, errors, settings, selectedAppraiser} = this.props;
    const {successDialog, failDialog} = this.state;
    const disabled = this.isDisabled();
    const fromDate = form.get('from') ? moment(form.get('from')).format(dateTimeFormat) : moment().format(dateTimeFormat);
    const toDate = form.get('to') ? moment(form.get('to')).format(dateTimeFormat) : moment().format(dateTimeFormat);
    // Is on vacation settings
    const isOnVacation = settings.getIn(['availability', 'form', 'isOnVacation']);

    return (
      <div>
        <div className="row">
          <div className="col-md-4">
            <VpPlainDropdown
              options={availability}
              value={form.get('isOnVacation')}
              onChange={this.changeDropdown}
              label="Availability"
              disabled={!!selectedAppraiser}
            />
          </div>
          {form.get('isOnVacation') &&
            <span>
              <div className="col-md-4">
                <div className="form-group">
                  <label className="control-label">From</label>
                  <DateTimePicker
                    ref="fromPicker"
                    onChange={this.changeFromDate}
                    dateTime={fromDate}
                    format={dateTimeFormat}
                    inputFormat={dateTimeFormat}
                    inputProps={this.inputPropsFrom}
                    minDate={moment()}
                    mode="datetime"
                    error={errors.getIn(['from', 0])}
                    disabled={!!selectedAppraiser}
                  />
                </div>
              </div>
              <div className="col-md-4">
                <div className="form-group">
                  <label className="control-label">To</label>
                  <DateTimePicker
                    ref="toPicker"
                    onChange={this.changeToDate}
                    dateTime={toDate}
                    format={dateTimeFormat}
                    inputFormat={dateTimeFormat}
                    inputProps={this.inputPropsTo}
                    minDate={moment()}
                    mode="datetime"
                    error={errors.getIn(['to', 0])}
                    disabled={!!selectedAppraiser}
                  />
                </div>
              </div>
              <div className="col-md-12">
                <VpTextField
                  name="message"
                  value={form.get('message')}
                  label="Response Message"
                  onChange={this.formChange}
                  error={errors.get('message')}
                  disabled={!!selectedAppraiser}
                  fullWidth
                  multiLine
                  noTimeout
                />
              </div>
            </span>
          }
          {!selectedAppraiser &&
            <div>
              <div className="col-md-12">
                <Void pixels={10}/>
                <ActionButton type="submit" onClick={this.submit} text="Update Availability" disabled={disabled}/>
              </div>
            </div>
          }
        </div>
        {/*Update availability success*/}
        <Dialog
          open={successDialog}
          actions={
            <button className="btn btn-raised btn-info"
              onClick={this.closeAvailabilityUpdateSuccess}>Close</button>
          }
          title="Your availability has been updated"
        >
          <h4>{isOnVacation ? 'You have been set to unavailable' : 'You have been set to available'}</h4>
        </Dialog>
        {/*Update availability failure*/}
        <Dialog
          open={failDialog}
          actions={
            <button className="btn btn-raised btn-info"
              onClick={this.closeAvailabilityUpdateFail}>Close</button>
          }
          title="Failed to update availability"
        >
          <h4>An error has occurred, and your availability has not been updated.</h4>
          {settings.getIn(['availability', 'errors', 'from']) &&
            <p>{settings.getIn(['availability', 'errors', 'from'])}</p>
          }
          {settings.getIn(['availability', 'errors', 'to']) &&
            <p>{settings.getIn(['availability', 'errors', 'to'])}</p>
          }
        </Dialog>
      </div>
    );
  }
}
