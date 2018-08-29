import React, {Component, PropTypes} from 'react';

import {VpPlainDropdown, VpTextField} from 'components';

import Immutable from 'immutable';

// Tax classifications
const classifications = Immutable.fromJS([
  { name: 'Individual/Sole proprietor - SSN', value: 'individual-ssn'},
  { name: 'Individual/Sole proprietor - Tax ID', value: 'individual-tax-id'},
  { name: 'C Corporation', value: 'c-corporation'},
  { name: 'S Corporation', value: 's-corporation'},
  { name: 'Partnership', value: 'partnership'},
  { name: 'Limited Liability Company C', value: 'llc-c'},
  { name: 'Limited Liability Company S', value: 'llc-s'},
  { name: 'Limited Liability Company P', value: 'llc-p'},
  { name: 'Other', value: 'other'}
]);

/**
 * Business tax classification
 */
export default class TaxClassification extends Component {
  static propTypes = {
    // Property path in form
    propPath: PropTypes.array,
    // "Other" company text explanation prop path
    otherPropPath: PropTypes.array,
    // Prepend value to path
    prependPath: PropTypes.string,
    // Handle selection
    setProp: PropTypes.func.isRequired,
    // Label
    label: PropTypes.string,
    // Disable state selection
    disabled: PropTypes.bool,
    // Help text
    help: PropTypes.string,
    // Remove prop
    removeProp: PropTypes.func.isRequired,
    // Required
    required: PropTypes.bool,
    // Selected classification
    value: PropTypes.string,
    // "Other" clarification
    otherValue: PropTypes.string,
    // Other error value
    otherError: PropTypes.string
  };

  /**
   * Default prop paths
   */
  static defaultProps = {
    propPath: ['federalTaxClassification'],
    otherPropPath: ['otherCompanyType']
  };

  /**
   * Don't display on load
   */
  constructor() {
    super();
    this.state = {
      display: false
    };
  }

  /**
   * Remove other explanation if transitioning away form "other" selection
   * @param nextProps
   */
  componentWillReceiveProps(nextProps) {
    const {value, removeProp, otherPropPath} = this.props;
    // Remove other explanation
    if (value === 'other' && nextProps.value !== 'other') {
      removeProp(...otherPropPath);
    }
  }

  /**
   * Change tax classification
   * @param event
   */
  onChange(event) {
    const {setProp, prependPath} = this.props;
    let {propPath, otherPropPath} = this.props;
    const {value, name} = event.target;
    // Prepend any values to paths
    if (prependPath) {
      otherPropPath = otherPropPath.slice();
      propPath = propPath.slice();
      otherPropPath.unshift(prependPath);
      propPath.unshift(prependPath);
    }
    // Other company type explanation
    if (name === 'otherCompanyType') {
      setProp(value, ...otherPropPath);
    } else {
      setProp(value, ...propPath);
    }
  }

  render() {
    const {
      label,
      value,
      otherValue,
      required = false,
      disabled = false,
      otherError = ''
    } = this.props;
    return (
      <div>
        <VpPlainDropdown
          onChange={::this.onChange}
          value={value}
          options={classifications}
          label={label || 'Federal tax classification'}
          required={required}
          disabled={disabled}
        />
        {value === 'other' &&
          <VpTextField
            value={otherValue}
            label="Explanation of tax classification"
            name="otherCompanyType"
            onChange={::this.onChange}
            disabled={disabled}
            error={otherError}
          />
        }
      </div>
    );
  }
}
