import React, {Component, PropTypes} from 'react';

import {VpMultiselect, VpTextField} from 'components';
import Immutable from 'immutable';

// Available areas of expertise
const expertise = Immutable.fromJS([
  { name: 'Agricultural/Poultry Farm', value: 'agricultural' },
  { name: 'Hospitality', value: 'hospitality' },
  { name: 'Industrial', value: 'industrial' },
  { name: 'Land', value: 'land' },
  { name: 'Multi Family', value: 'multi-family' },
  { name: 'Office', value: 'office' },
  { name: 'Retail', value: 'retail' },
  { name: 'Other', value: 'other' },
  { name: 'Self-Storage', value: 'self-storage' },
  { name: 'Winery/Vineyard', value: 'winery' },
]);

/**
 * Select from list of available expertise
 */
export default class Expertise extends Component {

  static propTypes = {
    // Selected values
    selected: PropTypes.instanceOf(Immutable.List).isRequired,
    // Update value
    updateValue: PropTypes.func.isRequired,
    // Set prop
    setProp: PropTypes.func.isRequired,
    // form
    form: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Enter function
    enterFunction: PropTypes.func,
    // Validation errors
    errors: PropTypes.instanceOf(Immutable.Map)
  };

  /**
   * Handle click
   * @param prop
   */
  onClick(prop) {
    const {updateValue} = this.props;
    updateValue('append', ['qualifications', 'commercialExpertise'], prop);
  }

  /**
   * Set text for other commercial expertise explanation
   * @param event
   */
  setText(event) {
    const value = event.target.value;
    this.props.setProp(value, 'signUpForm', 'qualifications', 'otherCommercialExpertise');
  }

  render() {
    const {selected, form, enterFunction, errors} = this.props;
    const showOther = selected.indexOf('other') !== -1;
    const disabled = form.getIn(['qualifications', 'primaryLicense', 'certifications', 0]) !== 'certified-general';
    let formatted;
    if (disabled) {
      try {
        formatted = expertise.map(item => {
          if (selected.includes(item.get('value'))) {
            return item.get('name');
          }
        }).filter(a => a).toJS().join(', ');
      } catch (e) {
        formatted = '';
      }
    }
    return (
      <div>
        {disabled &&
          <VpTextField
            value={formatted}
            label="Commercial Expertise"
            disabled={disabled}
            multiLine
          />
        }
        {!disabled &&
         <VpMultiselect
           options={expertise}
           selected={selected}
           onClick={::this.onClick}
           label="Select Commercial Expertise"
         />
        }
        {showOther &&
         <VpTextField
           value={form.getIn(['qualifications', 'otherCommercialExpertise'])}
           label="Explain your other commercial expertise"
           name="otherCommercialExpertise"
           onChange={::this.setText}
           enterFunction={enterFunction ? enterFunction : () => ({})}
           error={errors.getIn(['otherCommercialExpertise'])}
           disabled={disabled}
         />
        }
      </div>
    );
  }
}
