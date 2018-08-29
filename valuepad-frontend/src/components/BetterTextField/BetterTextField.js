import React, {Component, PropTypes} from 'react';

import {inputGroupClass} from 'helpers/styleHelpers';
import formatters from 'helpers/formatters';

/**
 * Starting over on text fields without all of the bells and whistles
 */
export default class BetterTextField extends Component {
  static propTypes = {
    // Value
    value: PropTypes.oneOfType([
      PropTypes.string,
      PropTypes.number
    ]),
    // Label
    label: PropTypes.string,
    // Name
    name: PropTypes.string,
    // On change function
    onChange: PropTypes.func,
    // Placeholder
    placeholder: PropTypes.string,
    // Errors
    error: PropTypes.string,
    // Tab index
    tabIndex: PropTypes.oneOfType([
      PropTypes.string,
      PropTypes.number
    ]),
    // Enter function
    enterFunction: PropTypes.func,
    // Disabled
    disabled: PropTypes.bool,
    // Type of text field to generate
    type: PropTypes.string,
    // Number of lines
    multiLine: PropTypes.bool,
    // required field
    required: PropTypes.bool,
    // minimum number of rows (multiline only)
    minRows: PropTypes.number,
    // maximum number of rows (multiline only)
    maxRows: PropTypes.number,
    // set the formatter
    formatter: PropTypes.string,
    // Label class
    labelClass: PropTypes.string,
    // Set prop directly
    setProp: PropTypes.func,
    // Path for set prop
    propPath: PropTypes.array,
    // Parent div class
    parentClass: PropTypes.string,
    // Auto complete
    autocomplete: PropTypes.string,
    // Input class
    inputClass: PropTypes.string,
    // Hides error element
    hideError: PropTypes.bool,
    // Enables tool tip
    dataTip: PropTypes.bool,
    // Tool tip target
    dataTipFor: PropTypes.string,
    // Append additional content after the input element
    appendAddon: PropTypes.element
  };

  /**
   * Listen for enter key, trigger enter event
   * @param e
   */
  listenForEnter(e) {
    if (e.key === 'Enter') {
      const {
        enterFunction = () => {},
      } = this.props;
      enterFunction(e);
    }
  }

  /**
   * Handle direct set prop
   * @param event
   */
  handleSetProp(event) {
    const {setProp, propPath} = this.props;
    setProp(event.target.value, ...propPath);
  }

  /**
   * Handles value changes
   *
   * @param event
   */
  handleOnChange(event) {
    const {setProp, propPath, onChange} = this.props;
    let callback;
    callback = typeof setProp === 'function' && Array.isArray(propPath) ? ::this.handleSetProp : onChange;
    return callback(event);
  }

  render() {
    const {
      error,
      tabIndex,
      name,
      label,
      placeholder = '',
      disabled = false,
      type = 'text',
      required = false,
      labelClass = 'control-label',
      formatter,
      autocomplete = 'on',
      parentClass = null,
      inputClass = null,
      hideError = false,
      dataTip = false,
      dataTipFor = '',
      appendAddon = null
    } = this.props;
    let value = this.props.value || '';
    // Number to string
    if (typeof value === 'number') {
      value = value.toString();
    }

    // run this through the formatter
    if (formatter && formatters[formatter]) {
      value = formatters[formatter](value);
    }
    const parentDivClass = typeof parentClass === 'string' ? parentClass : inputGroupClass(error);
    const finalInputClass = typeof inputClass === 'string' ? inputClass : 'form-control';
    return (
      <div className={`${parentDivClass} ${required ? 'required' : ''}`}>
        {!!label && <label className={labelClass}>{label}</label>}
        <input
          type={type}
          name={name}
          className={finalInputClass}
          placeholder={placeholder}
          onKeyPress={::this.listenForEnter}
          value={value}
          onChange={::this.handleOnChange}
          tabIndex={tabIndex}
          disabled={disabled}
          autoComplete={autocomplete}
          data-tip={dataTip}
          data-for={dataTipFor}
        />
        {appendAddon}
        {!hideError &&
         <p className="help-block">{error}</p>
        }
      </div>
    );
  }
}
