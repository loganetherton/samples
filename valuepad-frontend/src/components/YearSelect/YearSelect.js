import React, {Component, PropTypes} from 'react';
import Immutable from 'immutable';
import moment from 'moment';

import {VpPlainDropdown} from 'components';

/**
 * Drop down of years
 */
export default class YearSelect extends Component {
  static propTypes = {
    // Selected
    selectedYear: PropTypes.number,
    // Change value func
    changeValue: PropTypes.func.isRequired,
    // Max year (int for years ahead of the current year in time)
    maxYear: PropTypes.number,
    // Min year (int for years behind of the current year in time)
    minYear: PropTypes.number,
    // Label
    label: PropTypes.string,
    // Error
    error: PropTypes.string,
    // Reverse year order
    reverse: PropTypes.bool
  };

  render() {
    const {
      selectedYear,
      changeValue,
      maxYear = 0,
      minYear = 10,
      label = 'Year',
      error,
      reverse = false
    } = this.props;
    const thisYear = parseInt(moment().add(maxYear, 'years').format('YYYY'), 10);
    const tenYearsAgo = parseInt(moment().subtract(minYear, 'years').format('YYYY'), 10);
    let yearRange;
    if (reverse) {
      yearRange = Immutable.Range(tenYearsAgo, thisYear).map(value => Immutable.fromJS({name: value, value})).toList();
    } else {
      yearRange = Immutable.Range(thisYear, tenYearsAgo).map(value => Immutable.fromJS({name: value, value})).toList();
    }
    return (
      <VpPlainDropdown
        options={yearRange}
        value={selectedYear}
        onChange={changeValue}
        label={label}
        error={error}
      />
    );
  }
}
