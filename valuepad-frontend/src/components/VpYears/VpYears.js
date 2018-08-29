import React, {Component, PropTypes} from 'react';

import {VpPlainDropdown} from 'components';
import Immutable from 'immutable';
import moment from 'moment';

/**
 * Year dropdown
 */
export default class VpYears extends Component {
  static propTypes = {
    // Set prop function
    setProp: PropTypes.func.isRequired,
    // Prop path for setting prop
    propPath: PropTypes.array.isRequired,
    // Value
    value: PropTypes.oneOfType([
      PropTypes.string,
      PropTypes.number
    ]),
    // Label
    label: PropTypes.string.isRequired,
    // Begin year
    beginYear: PropTypes.number,
    // End year
    endYear: PropTypes.number,
    // Required field
    required: PropTypes.bool,
    // Disabled
    disabled: PropTypes.bool
  };

  constructor(props) {
    super(props);

    this.changeHandler = ::this.changeHandler;
  }

  /**
   * Create range of years
   */
  componentDidMount() {
    const {beginYear = 1951, endYear = moment().add(1, 'years').year()} = this.props;
    this.years = Immutable.Range(beginYear, endYear)
      .map(year => Immutable.fromJS({
        value: year,
        name: year
      })).toList();
  }

  /**
   * Parse int before putting into reducer
   * @param value
   */
  changeHandler(value) {
    const {setProp, propPath} = this.props;
    setProp(parseInt(value, 10), ...propPath);
  }

  render() {
    const {required = false, value, label, disabled, propPath} = this.props;
    return (
      <VpPlainDropdown
        value={value}
        label={label}
        options={this.years}
        setProp={this.changeHandler}
        required={required}
        disabled={disabled}
        propPath={propPath ? propPath : null}
      />
    );
  }
}
