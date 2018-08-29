import React, {Component, PropTypes} from 'react';

import {VpPlainDropdown} from 'components';
import Immutable from 'immutable';

const months = Immutable.fromJS([
  {name: 'January', value: 1},
  {name: 'February', value: 2},
  {name: 'March', value: 3},
  {name: 'April', value: 4},
  {name: 'May', value: 5},
  {name: 'June', value: 6},
  {name: 'July', value: 7},
  {name: 'August', value: 8},
  {name: 'September', value: 9},
  {name: 'October', value: 10},
  {name: 'November', value: 11},
  {name: 'December', value: 12}
]);

/**
 * Months dropdown
 */
export default class VpMonths extends Component {
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
    // Required
    required: PropTypes.bool,
    // Disabled
    disabled: PropTypes.bool
  };

  constructor(props) {
    super(props);

    this.changeHandler = ::this.changeHandler;
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
    const {value, label, required = false, disabled, propPath} = this.props;
    return (
      <VpPlainDropdown
        value={value}
        label={label}
        options={months}
        setProp={this.changeHandler}
        required={required}
        disabled={disabled}
        propPath={propPath ? propPath : null}
      />
    );
  }
}
