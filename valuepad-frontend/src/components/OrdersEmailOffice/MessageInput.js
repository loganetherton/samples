import React, {Component, PropTypes} from 'react';

import {VpTextField} from 'components';

const styles = require('./styles.scss');

export default class MessageInput extends Component {
  static propTypes = {
    value: PropTypes.string.isRequired,
    onChange: PropTypes.func.isRequired,
  }

  shouldComponentUpdate(nextProps) {
    return this.props.value !== nextProps.value;
  }

  render() {
    const {value, onChange} = this.props;

    return (
      <VpTextField
        parentClass={`col-md-12 ${styles['textarea-wrapper']}`}
        inputClass={`focusable ${styles.textarea}`}
        maxRows={3}
        minRows={3}
        multiLine
        name="comments"
        value={value}
        placeholder="Enter a new message"
        onChange={onChange}
      />
    );
  }
}
