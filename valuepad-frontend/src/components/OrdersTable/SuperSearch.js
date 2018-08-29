import React, {Component, PropTypes} from 'react';

import {VpTextField} from 'components';

const styles = require('./style.scss');

export default class SuperSearch extends Component {
  static propTypes = {
    // Super search query
    value: PropTypes.string.isRequired,
    // Handles query change
    onChange: PropTypes.func.isRequired
  };

  shouldComponentUpdate(nextProps) {
    return this.props.value !== nextProps.value;
  }

  render() {
    const {value, onChange} = this.props;

    return (
      <VpTextField
        value={value}
        onChange={onChange}
        placeholder="Search for order details"
        parentClass={styles['super-search-wrapper']}
        inputClass={styles['super-search-input']}
        hideError
      />
    );
  }
}
