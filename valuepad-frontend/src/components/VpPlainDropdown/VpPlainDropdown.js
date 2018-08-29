import React, {Component, PropTypes} from 'react';
import {inputGroupClass} from 'helpers/styleHelpers';

import Immutable from 'immutable';

/**
 * Yes/no default
 */
const yesNo = Immutable.fromJS([
  {value: 'yes', name: 'Yes'},
  {value: 'no', name: 'No'}
]);

/**
 * Render a plain ol' select field
 */
export default class VpPlainDropdown extends Component {
  static propTypes = {
    // Options
    options: PropTypes.instanceOf(Immutable.List),
    // Value
    value: PropTypes.oneOfType([
      PropTypes.string,
      PropTypes.number,
      PropTypes.bool
    ]),
    // On change handler
    onChange: PropTypes.func,
    // Set prop
    setProp: PropTypes.func,
    // Prop path
    propPath: PropTypes.array,
    // Label
    label: PropTypes.string,
    // Disabled
    disabled: PropTypes.bool,
    // Set default
    defaultValue: PropTypes.oneOfType([
      PropTypes.string,
      PropTypes.number,
      PropTypes.bool
    ]),
    // Error
    error: PropTypes.string,
    // Name
    name: PropTypes.string,
    // Required field
    required: PropTypes.bool,
    // Display inline
    inline: PropTypes.bool,
    // Label class
    labelClass: PropTypes.string,
    // Value property of options object
    valueProp: PropTypes.string,
    // Name property of options object
    nameProp: PropTypes.string
  };

  /**
   * set a default for dropdown
   */
  componentDidMount() {
    const {defaultValue, value, setProp, propPath} = this.props;
    if (typeof value === 'undefined' && defaultValue) {
      setProp(defaultValue, ...propPath);
    }
  }

  /**
   * On change handler
   * @param args
   */
  onChange(...args) {
    const {setProp, onChange, propPath} = this.props;
    // On change
    if (onChange) {
      return onChange(...args);
    }
    // Set prop directly
    if (setProp && propPath) {
      return setProp(args[0].target.value, ...propPath);
    }
    throw new Error('Neither onChange nor setProp defined for VpPlainDropdown');
  }

  render() {
    const {
      label,
      disabled = false,
      error,
      name = '',
      required = false,
      inline = false,
      labelClass = 'control-label',
      valueProp = 'value',
      nameProp = 'name'
    } = this.props;
    let {value, options} = this.props;

    // Options, either passed in or yes/no
    options = options ? options : yesNo;

    // Set value to string for yes/no
    if (Immutable.is(options, yesNo) && typeof value === 'boolean') {
      value = value ? 'yes' : 'no';
    }
    // Default to null
    if (typeof value === 'undefined') {
      value = null;
    }
    // Display blank option if null
    if (value === null) {
      options = options.unshift(Immutable.fromJS({value: null, name: ''}));
    }
    let className = `${inputGroupClass(error)} ${required ? 'required' : ''}`;
    let element;
    // Display inline
    if (inline) {
      className = className.replace('form-group', 'form-inline');
      element = (
        <div className={className}>
          <label style={{minHeight: '5px', width: '33%', textAlign: 'right'}}>{label}</label>
          <select
            className="form-control"
            value={value}
            onChange={::this.onChange}
            disabled={disabled}
            name={name}
            style={{width: '66%'}}
          >
            {options.map((option, key) => {
              return (<option key={key} value={option.get(valueProp)}>{option.get(nameProp)}</option>);
            })}
          </select>
        </div>
      );
      // Normal display
    } else {
      element = (
        <div className={className}>
          <label className={labelClass}>{label}</label>
          <div>
            <select
              className="form-control"
              value={value}
              onChange={::this.onChange}
              disabled={disabled}
              name={name}
            >
              {options.map((option, key) => {
                return (<option key={key} value={option.get(valueProp)}>{option.get(nameProp)}</option>);
              })}
            </select>
          </div>
          <p className="help-block">{error}</p>
        </div>
      );
    }
    return element;
  }
}
