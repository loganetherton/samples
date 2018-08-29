import React, {Component, PropTypes} from 'react';
import Immutable from 'immutable';

import {VpTextField} from 'components';

import {formatStringOnType} from 'helpers/genericFunctions';

export default class PhoneNumber extends Component {
  static propTypes = {
    // Form from which the value is drawn
    form: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Path to prop
    propPath: PropTypes.array.isRequired,
    // Value label
    label: PropTypes.string.isRequired,
    // Form errors
    errors: PropTypes.instanceOf(Immutable.Map),
    // Enter key function
    enterFunction: PropTypes.func,
    // Tab index
    tabIndex: PropTypes.oneOfType([
      PropTypes.string,
      PropTypes.number
    ]),
    // Set prop
    setProp: PropTypes.func.isRequired,
    // Required field
    required: PropTypes.bool,
    // Disabled
    disabled: PropTypes.bool,
    // Don't timeout before sending to reducer
    noTimeout: PropTypes.bool,
    // Prepend to path
    prepend: PropTypes.string
  };

  constructor() {
    super();
    this.getNameVal = ::this.getNameVal;
  }

  /**
   * Handle form change and format input
   * @param event
   */
  formChange(event) {
    const {setProp, form, prepend} = this.props;
    let {propPath} = this.props;
    let value = event.target.value;
    // Remove non-digits
    value = value.replace(/[^\d]*/g, '');
    // Make sure we begin with open parenthesis
    if (value && typeof value === 'string') {
      value = '(' + value;
      // Close parenthesis
      value = formatStringOnType(value, 4, ') ');
      // Dash
      value = formatStringOnType(value, 9, '-');
      // Total length restriction
      if (value.length > 14) {
        value = value.substr(0, 14);
      }
    }
    // If something is filtered out, make sure a render happens so it doesn't display
    if (value === form.get(this.getNameVal())) {
      this.forceUpdate();
    }
    if (prepend) {
      propPath = propPath.slice();
      propPath.unshift(prepend);
    }
    setProp(value, ...propPath);
    this.forceUpdate();
  }

  /**
   * Get name value for
   * @return {*}
   */
  getNameVal() {
    const {propPath} = this.props;
    return propPath[propPath.length - 1];
  }

  render() {
    const {
      form,
      label,
      errors,
      enterFunction,
      tabIndex = 0,
      required = false,
      disabled = false,
      noTimeout = false
    } = this.props;
    // Name of this component
    const nameVal = this.getNameVal();
    return (
      <VpTextField
        value={form.get(nameVal)}
        label={label}
        name={nameVal}
        placeholder={label}
        onChange={::this.formChange}
        error={errors.get(nameVal)}
        enterFunction={enterFunction || (() => {})}
        tabIndex={tabIndex}
        required={required}
        disabled={disabled}
        noTimeout={noTimeout}
      />
    );
  }
}
