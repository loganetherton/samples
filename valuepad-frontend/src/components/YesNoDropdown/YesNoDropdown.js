import React, {Component, PropTypes} from 'react';

import {Void} from 'components';

import {
  SelectField,
  MenuItem
} from 'material-ui';

import Immutable from 'immutable';

const values = Immutable.fromJS([
  {name: 'Yes', value: 'yes'},
  {name: 'No', value: 'no'}
]);

/**
 * Create a simple yes/no dropdown
 */
export default class YesNoDropdown extends Component {
  static propTypes = {
    // Function to set a property
    setProp: PropTypes.func.isRequired,
    // Prop path
    propPath: PropTypes.array.isRequired,
    // Label
    label: PropTypes.string,
    // Currently selected value
    value: PropTypes.oneOfType([
      PropTypes.bool,
      PropTypes.string
    ]),
    // No floating label
    noFloat: PropTypes.bool,
    // Set a default value on mount
    setDefault: PropTypes.oneOfType([
      PropTypes.bool,
      PropTypes.string
    ])
  };

  /**
   * Set a default value on mount
   */
  componentDidMount() {
    const {setDefault, setProp, propPath} = this.props;
    // Set a prop default
    if (typeof setDefault !== 'undefined') {
      if (!this.props.value) {
        setProp(setDefault, propPath);
      }
    }
  }

  /**
   * Change dropdown
   */
  changeDropdown(event, key, value) {
    const {setProp, propPath} = this.props;
    setProp(value, propPath);
  }

  render() {
    const {label, noFloat = false} = this.props;
    let value = this.props.value;
    // Make sure we have a string value
    if (typeof value === 'boolean') {
      value = value === true ? 'yes' : 'no';
    }
    return (
      <div>
        {/*Typically for very long labels, make a separate label element*/}
        {noFloat &&
         <div>
           <Void pixels="15"/>
           <p>{label}</p>
           <SelectField
             value={value}
             onChange={::this.changeDropdown}
             fullWidth
           >
             {values.map((value, index) => <MenuItem value={value.get('value')} key={index} primaryText={value.get('name')}/>)}
           </SelectField>
         </div>
        }
        {/*Floating label*/}
        {!noFloat &&
         <SelectField
           value={value}
           onChange={::this.changeDropdown}
           floatingLabelText={label}
           fullWidth
         >
           {values.map((value, index) => <MenuItem value={value.get('value')} key={index} primaryText={value.get('name')}/>)}
         </SelectField>
        }
      </div>
    );
  }
}
