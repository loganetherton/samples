import React, {Component, PropTypes} from 'react';
import Textarea from 'react-textarea-autosize';

import {inputGroupClass} from 'helpers/styleHelpers';
import formatters from 'helpers/formatters';

import pureRender from 'pure-render-decorator';

// Wait time before invoking the callback function
const delay = 125;

const textAreaStyle = {
  background: 'rgb(245, 245, 245)',
  padding: '5px'
};

/**
 * Generic text field property to replace the old MUI one
 */
@pureRender
export default class VpTextField extends Component {
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
    // No timeout for onChange
    noTimeout: PropTypes.bool,
    // Auto focus
    autoFocus: PropTypes.bool
  };

  constructor(props) {
    super(props);

    this.handleOnChange = ::this.handleOnChange;
    this.listenForEnter = ::this.listenForEnter;
    this.handleSetProp = ::this.handleSetProp;
  }

  /**
   * Listen for enter key, trigger enter event
   * @param e
   */
  listenForEnter(e) {
    if (e.key === 'Enter') {
      const {
        enterFunction = () => {},
      } = this.props;
      enterFunction();
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

  componentWillReceiveProps(nextProps) {
    const {value: nextValue} = nextProps;

    if (typeof nextValue !== 'undefined') {
      // While this if statement may look somewhat useless at first glance, it
      // actually plays a somewhat significant role in using the setTimeout hack.
      // Without the following check, user might end up losing their selection
      // on the input field because of the value assignment, so we want to be sure
      // to only rewrite the value when necessary.
      if (this.refs.vpInput.value !== nextValue) {
        this.refs.vpInput.value = nextValue;
      }
    }
  }

  /**
   * Handles value changes
   *
   * @param event
   */
  handleOnChange(event) {
    const {multiLine = false, setProp, propPath, onChange, noTimeout} = this.props;
    let callback;
    if (multiLine) {
      callback = onChange;
    } else {
      callback = typeof setProp === 'function' && Array.isArray(propPath) ? this.handleSetProp : onChange;
    }
    if (noTimeout) {
      return callback(event);
    }
    event.persist();
    // Cancel search if it's already in timeout
    if (this.textFieldTimeout) {
      clearTimeout(this.textFieldTimeout);
    }
    // Provide for cancelling on type
    this.textFieldTimeout = setTimeout(() => {
      callback(event);
      this.textFieldTimeout = null;
    }, delay);
  }

  render() {
    const {
      error,
      tabIndex,
      name,
      label,
      multiLine = false,
      placeholder = '',
      disabled = false,
      type = 'text',
      required = false,
      minRows = -Infinity,
      maxRows = Infinity,
      labelClass = 'control-label',
      formatter,
      autocomplete = 'on',
      parentClass = null,
      inputClass = null,
      hideError = false,
      dataTip = false,
      dataTipFor = '',
      autoFocus = false
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
        {multiLine &&
          <Textarea
            style={textAreaStyle}
            name={name}
            className={finalInputClass}
            defaultValue={value}
            placeholder={placeholder}
            onChange={this.handleOnChange}
            disabled={disabled}
            minRows={minRows}
            maxRows={maxRows}
            ref="vpInput"
            data-tip={dataTip}
            data-for={dataTipFor}
            autoFocus={autoFocus}
          />
        }
        {!multiLine &&
         <input
           type={type}
           name={name}
           className={finalInputClass}
           placeholder={placeholder}
           defaultValue={value}
           onChange={this.handleOnChange}
           tabIndex={tabIndex}
           onKeyPress={this.listenForEnter}
           disabled={disabled}
           autoComplete={autocomplete}
           ref="vpInput"
           data-tip={dataTip}
           data-for={dataTipFor}
           autoFocus={autoFocus}
         />
        }
        {!hideError &&
          <p className="help-block">{error}</p>
        }
      </div>
    );
  }
}
