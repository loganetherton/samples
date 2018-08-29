import React, {Component, PropTypes} from 'react';
import Immutable from 'immutable';

import {VpPlainDropdown} from 'components';

const months = Immutable.fromJS([
  {value: 1, name: 'January'},
  {value: 2, name: 'February'},
  {value: 3, name: 'March'},
  {value: 4, name: 'April'},
  {value: 5, name: 'May'},
  {value: 6, name: 'June'},
  {value: 7, name: 'July'},
  {value: 8, name: 'August'},
  {value: 9, name: 'September'},
  {value: 10, name: 'October'},
  {value: 11, name: 'November'},
  {value: 12, name: 'December'}
]);

/**
 * Drop down of months
 */
export default class MonthSelect extends Component {
  static propTypes = {
    // Selected
    selectedMonth: PropTypes.number,
    // Change value
    changeValue: PropTypes.func.isRequired,
    // label
    label: PropTypes.string,
    // Error
    error: PropTypes.string
  };

  render() {
    const {selectedMonth, changeValue, label = 'Month', error} = this.props;
    return (
      <VpPlainDropdown
        options={months}
        value={selectedMonth}
        onChange={changeValue}
        label={label}
        error={error}
      />
    );
  }
}
