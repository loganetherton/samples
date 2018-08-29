import React, {Component, PropTypes} from 'react';
import classnames from 'classnames';
import DateTimePickerDate from './DateTimePickerDate.js';
import DateTimePickerTime from './DateTimePickerTime.js';
import Constants from './Constants.js';

export default class DateTimePicker extends Component {
  static propTypes = {
    showDatePicker: PropTypes.bool,
    showTimePicker: PropTypes.bool,
    subtractMonth: PropTypes.func.isRequired,
    addMonth: PropTypes.func.isRequired,
    viewDate: PropTypes.object.isRequired,
    selectedDate: PropTypes.object.isRequired,
    showToday: PropTypes.bool,
    viewMode: PropTypes.oneOfType([PropTypes.string, PropTypes.number]),
    mode: PropTypes.oneOf([Constants.MODE_DATE, Constants.MODE_DATETIME, Constants.MODE_TIME]),
    daysOfWeekDisabled: PropTypes.array,
    setSelectedDate: PropTypes.func.isRequired,
    subtractYear: PropTypes.func.isRequired,
    addYear: PropTypes.func.isRequired,
    setViewMonth: PropTypes.func.isRequired,
    setViewYear: PropTypes.func.isRequired,
    subtractHour: PropTypes.func.isRequired,
    addHour: PropTypes.func.isRequired,
    subtractMinute: PropTypes.func.isRequired,
    addMinute: PropTypes.func.isRequired,
    addDecade: PropTypes.func.isRequired,
    subtractDecade: PropTypes.func.isRequired,
    togglePeriod: PropTypes.func.isRequired,
    minDate: PropTypes.object,
    maxDate: PropTypes.object,
    widgetClasses: PropTypes.object,
    widgetStyle: PropTypes.object,
    togglePicker: PropTypes.func,
    setSelectedHour: PropTypes.func,
    setSelectedMinute: PropTypes.func
  }

  renderDatePicker() {
    return (
      <DateTimePickerDate
        {...this.props}
      />
    );
  }

  renderTimePicker() {
    return (
      <DateTimePickerTime
        {...this.props}
      />
    );
  }

  /**
   * Render datetime
   */
  renderDateTime() {
    return (
      <li>
        <span className="btn picker-switch" onClick={this.props.togglePicker} style={{width: '100%'}}>
          <span
            className={classnames('glyphicon', this.props.showTimePicker ? 'glyphicon-calendar' : 'glyphicon-time')}/>
        </span>
      </li>
    );
  }

  render() {
    const {mode, widgetClasses, widgetStyle} = this.props;
    return (
      <div className={classnames(widgetClasses)} style={widgetStyle}>

        <div className="row">
          <div className={mode === 'datetime' ? 'col-md-6' : 'col-md-12'}>
            {this.renderDatePicker()}
          </div>
          {mode === 'datetime' &&
            <div className="col-md-6">
              {this.renderTimePicker()}
            </div>
          }
        </div>

      </div>

    );
  }
}

