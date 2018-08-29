import React, {Component, PropTypes} from 'react';
import {VpPlainDropdown} from 'components';
import Immutable from 'immutable';

// US states
import states from './statesList';

/**
 * US states as select input
 */
export default class States extends Component {
  static propTypes = {
    form: PropTypes.object,
    // Handle selection
    changeHandler: PropTypes.func.isRequired,
    // Label
    label: PropTypes.string,
    // Prop name
    name: PropTypes.oneOfType([
      PropTypes.string,
      PropTypes.array
    ]),
    // Disable state selection
    disabled: PropTypes.bool,
    // Required field
    required: PropTypes.bool,
    // Remove states
    statesToRemove: PropTypes.instanceOf(Immutable.List)
  };

  constructor() {
    super();
    this.getValue = ::this.getValue;
    this.selectState = ::this.selectState;
  }

  /**
   * Get current state value
   */
  getValue() {
    const {name, form} = this.props;
    return Array.isArray(name) ? form.getIn(name) : form.get(name || 'state');
  }

  /**
   * State select changes
   * @param event
   */
  selectState(event) {
    const value = typeof event === 'string' ? event : event.target.value;
    this.props.changeHandler(value);
  }

  render() {
    const {label, disabled, required = false, statesToRemove = Immutable.List()} = this.props;
    const finalStates = states.filter(state => !statesToRemove.includes(state.get('value')));
    return (
      <VpPlainDropdown
        options={finalStates}
        value={this.getValue()}
        onChange={::this.selectState}
        label={typeof label !== 'undefined' ? label : ''}
        disabled={disabled}
        required={required}
      />
    );
  }
}
