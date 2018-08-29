import React, {Component, PropTypes} from 'react';

import classNames from 'classnames';
import Immutable from 'immutable';

import {inputGroupClass} from 'helpers/styleHelpers';

export default class VpMultiselect extends Component {
  static propTypes = {
    // Multiselect options
    options: PropTypes.instanceOf(Immutable.List).isRequired,
    // Selected options
    selected: PropTypes.instanceOf(Immutable.List).isRequired,
    // Click function
    onClick: PropTypes.func.isRequired,
    // Button label
    label: PropTypes.string.isRequired,
    // Error text
    error: PropTypes.string,
    // Required field
    required: PropTypes.bool,
    // Disabled
    disabled: PropTypes.bool
  };

  /**
   * Hide by default
   */
  constructor() {
    super();
    this.state = {
      display: false
    };
  }

  /**
   * Toggle the dropdown
   */
  toggleDisplay() {
    if (!this.props.disabled) {
      this.setState({
        display: !this.state.display
      });
    }
  }

  render() {
    const {options, selected, onClick, label, error = '', required = false, disabled = false} = this.props;
    // Create basic map of options
    let optionsMap = Immutable.Map();
    options.forEach(option => {
      optionsMap = optionsMap.set(option.get('value'), option.get('name'));
    });
    // Create array of display names
    const optionsDisplay = [];
    selected.forEach(item => {
      optionsDisplay.push(optionsMap.get(item));
    });
    // Join them on together
    let selectedOptionsDisplay = '';
    if (optionsDisplay.length > 4) {
      selectedOptionsDisplay = optionsDisplay.slice(0, 4).join(', ') + '...';
    } else {
      selectedOptionsDisplay = optionsDisplay.join(', ');
    }

    return (
      <div className={`${inputGroupClass(error)} ${required ? 'required' : ''}`}>
        <label className="control-label">{label}</label>
        <div className="input-group has-feedback">
          <input type="text" className="form-control" placeholder={label} value={selectedOptionsDisplay}
                 onClick={::this.toggleDisplay} readOnly disabled={disabled}/>
          <i className="material-icons form-control-feedback">keyboard_arrow_down</i>
        </div>
        {/*Dropdown*/}
        <div className={classNames('btn-group-vertical dropdown-menu select-container', {hidden: !this.state.display})}>
          <ul style={{ padding: 0, margin: 0 }}>
            {options.map((item, index) => {
              const isChecked = selected.indexOf(item.get('value')) !== -1;
              return (
                <li
                  style={{ paddingLeft: '5px', paddingRight: '5px' }}
                  key={index}
                  className="btn btn-raised no-shadow"
                  onClick={onClick.bind(this, item.get('value'))}
                >
                  <a style={{cursor: 'pointer'}}>
                    <input
                      type="checkbox"
                      checked={isChecked}
                      readOnly/>&nbsp;
                    <label style={{left: '5px'}}>{item.get('name')}</label>
                  </a>
                </li>
              );
            })}
          </ul>
        </div>
        <p className="help-block">{error}</p>

        {this.state.display &&
          <div
            style={{ position: 'fixed', top: 0, left: 0, right: 0, bottom: 0, width: '100%', height: '100%' }}
            onClick={::this.toggleDisplay}
          >
          </div>
        }
      </div>
    );
  }
}
