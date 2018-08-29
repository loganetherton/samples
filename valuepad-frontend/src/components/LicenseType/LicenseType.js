import React, {Component, PropTypes} from 'react';

import {VpPlainDropdown, VpTextField} from 'components';

import Immutable from 'immutable';

const licenseTypes = Immutable.fromJS([
  {name: 'Licensed Residential', value: 'licensed'},
  {name: 'Certified Residential', value: 'certified-residential'},
  {name: 'Certified General', value: 'certified-general'},
  {name: 'Transitional License', value: 'transitional-license'}
]);

export default class LicenseType extends Component {
  static propTypes = {
    // Change value
    setProp: PropTypes.func.isRequired,
    // Appraiser record
    form: PropTypes.instanceOf(Immutable.Map),
    // Prop path
    propPath: PropTypes.array.isRequired,
    // Disabled
    disabled: PropTypes.bool,
    // Required
    required: PropTypes.bool
  };
  // Resume should not be required during sign up when not connected to a client
  render() {
    const {form, propPath, disabled = false, setProp, required = false} = this.props;
    let formatted;
    if (disabled) {
      const value = form.getIn(propPath);
      try {
        licenseTypes.forEach(item => {
          if (item.get('value') === value) {
            formatted = item.get('name');
            return false;
          }
        });
      } catch (e) {
        formatted = '';
      }
    }
    return (
      <div>
        {disabled &&
         <VpTextField
           value={formatted}
           label="License Type"
           disabled={disabled}
         />
        }
        {!disabled &&
         <VpPlainDropdown
           setProp={setProp}
           propPath={propPath}
           value={form.getIn(propPath)}
           label="License Type"
           options={licenseTypes}
           required={required}
         />
        }
      </div>
    );
  }
}
