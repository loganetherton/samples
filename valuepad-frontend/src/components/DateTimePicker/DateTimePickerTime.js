import React, { Component, PropTypes } from 'react';
import Constants from './Constants.js';

export default class DateTimePickerTime extends Component {
  static propTypes = {
    setSelectedHour: PropTypes.func.isRequired,
    setSelectedMinute: PropTypes.func.isRequired,
    subtractHour: PropTypes.func.isRequired,
    addHour: PropTypes.func.isRequired,
    subtractMinute: PropTypes.func.isRequired,
    addMinute: PropTypes.func.isRequired,
    viewDate: PropTypes.object.isRequired,
    selectedDate: PropTypes.object.isRequired,
    togglePeriod: PropTypes.func.isRequired,
    mode: PropTypes.oneOf([Constants.MODE_DATE, Constants.MODE_DATETIME, Constants.MODE_TIME])
  };

  render() {
    const {addHour, addMinute, selectedDate, togglePeriod, subtractHour, subtractMinute} = this.props;
    return (
      <div className="timepicker" style={{position: 'relative', top: '18px', right: '30px'}}>
        <div className="timepicker-picker">
          <table style={{tableLayout: 'fixed', width: '210px'}}>
            <tbody>
            <tr>
              <td>
                <a className="btn" onClick={addHour}><span className="glyphicon glyphicon-chevron-up"/></a>
              </td>

              <td className="separator"/>

              <td>
                <a className="btn" onClick={addMinute}><span className="glyphicon glyphicon-chevron-up"/></a>
              </td>

              <td className="separator"/>
            </tr>

            <tr>
              <td><span className="timepicker-hour" style={{marginLeft: '30px'}}>
              {selectedDate.format('h')}</span>
              </td>

              <td className="separator"><span style={{marginLeft: '30px'}}>:</span></td>

              <td>
                <span className="timepicker-minute" style={{marginLeft: '30px'}}>{selectedDate.format('mm')}</span>
              </td>

              <td>
                <button className="btn btn-primary" onClick={togglePeriod} type="button" style={{backgroundColor: 'transparent'}}>
                  {selectedDate.format('A')}
                </button>
              </td>
            </tr>

            <tr>
              <td><a className="btn" onClick={subtractHour}><span
                className="glyphicon glyphicon-chevron-down"/></a></td>

              <td className="separator"/>

              <td><a className="btn" onClick={subtractMinute}><span
                className="glyphicon glyphicon-chevron-down"/></a></td>

              <td className="separator"/>
            </tr>
            </tbody>
          </table>
        </div>
      </div>
    );
  }
}
