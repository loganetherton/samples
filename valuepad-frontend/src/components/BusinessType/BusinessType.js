import React, {Component, PropTypes} from 'react';

import {VpMultiselect} from 'components';
import Immutable from 'immutable';

// Available business types
const businessTypes = Immutable.fromJS([
  { name: 'N/A', value: 'not-applicable'},
  { name: 'Certified Minority', value: 'certified-minority'},
  { name: 'HUB Zone Small Business', value: 'hub-zone-small-business'},
  { name: 'Large Business', value: 'large-business'},
  { name: 'Small Business', value: 'small-business'},
  { name: 'Small Disadvantaged Business', value: 'small-disadvantaged-business'},
  { name: 'Veteran Owned Business', value: 'veteran-owned-business'},
  { name: 'Women Owned Business', value: 'women-owned-business'}
]);

/**
 * Business type
 */
export default class BusinessType extends Component {

  static propTypes = {
    // Selected
    selected: PropTypes.instanceOf(Immutable.List).isRequired,
    // Change handler
    changeHandler: PropTypes.func.isRequired,
    // Error string
    error: PropTypes.string,
    // Disabled
    disabled: PropTypes.bool
  };

  render() {
    const {selected, changeHandler, error, disabled = false} = this.props;
    return (
      <VpMultiselect
        options={businessTypes}
        selected={selected}
        onClick={changeHandler}
        label="Select Business Type"
        required
        error={error}
        disabled={disabled}
      />
    );
  }
}
