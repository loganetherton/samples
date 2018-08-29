import React, { Component, PropTypes } from 'react';
import {TextField} from 'material-ui';

/**
 * Check to make sure that a TextField value has changed before re-rendering
 * @param ComposedComponent
 * @constructor
 */
const CheckBeforeUpdate = ComposedComponent => class extends Component {
  static propTypes = {
    // Form value
    value: PropTypes.string,
    // Error text
    errorText: PropTypes.string
  };
  shouldComponentUpdate(nextProps) {
    // Check if form value has changed
    if (this.props.value !== nextProps.value) {
      return true;
    }
    // Check if errorText has changed
    return this.props.errorText !== nextProps.errorText;
  }
  render() {
    return <ComposedComponent {...this.props} />;
  }
};

export default CheckBeforeUpdate(TextField);
