import React, {Component, PropTypes} from 'react';
import DatePicker from 'react-datepicker';

import moment from 'moment';

export default class VpDateRange extends Component {
  static propTypes = {
    hintText: PropTypes.string,
    // Min date (expects moment.js object)
    minDate: PropTypes.instanceOf(moment),
    // Max date accepted
    maxDate: PropTypes.instanceOf(moment),
    // Change function
    changeHandler: PropTypes.func.isRequired,
    // Selected date
    date: PropTypes.oneOfType([
      PropTypes.instanceOf(moment),
      PropTypes.string
    ]),
    // Disabled
    disabled: PropTypes.bool,
    // Label
    label: PropTypes.string.isRequired,
    // Class for text field
    textFieldClass: PropTypes.string,
    // Date format
    dateFormat: PropTypes.string,
    // Required field
    required: PropTypes.bool,
    // Error string
    error: PropTypes.string
  };

  /**
   * Initialize the date range
   * @param props
   */
  constructor(props) {
    super(props);
    const {
      hintText,
      minDate,
      maxDate,
      } = this.props;

    this.state = {
      hintText,
      minDate: minDate ? minDate : null,
      maxDate: maxDate ? maxDate : null
    };
  }

  /**
   * Handle selecting a date
   * @param value Value
   */
  handleSelect(value) {
    this.props.changeHandler(value ? value.format() : null);
  }

  render() {
    const {
      date,
      disabled = false,
      minDate,
      maxDate,
      textFieldClass = 'form-control',
      label,
      dateFormat = 'M/D/YYYY',
      required = false,
      error
    } = this.props;

    const datePickerProps = {
      disabled,
      dateFormat,
      onChange: ::this.handleSelect,
      minDate,
      maxDate,
      className: textFieldClass,
      required
    };

    if (date) {
      datePickerProps.selected = date;
    }

    return (
      <div className={`${required ? 'required' : ''}`}>
        <div className={`form-group ${error ? 'has-error is-focused required' : ''}`}>
          {!!label && <label className="control-label">{label}</label>}
          <DatePicker
            {...datePickerProps}
          />
          <p className="help-block">{error}</p>
        </div>
      </div>
    );
  }
}
