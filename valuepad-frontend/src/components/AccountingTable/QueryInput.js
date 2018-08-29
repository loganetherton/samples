import React, {Component, PropTypes} from 'react';

import {VpTextField} from 'components';

const styles = require('./style.scss');

export default class QueryInput extends Component {
  static propTypes = {
    // Search query
    value: PropTypes.string.isRequired,
    // Input change callback
    editSearch: PropTypes.func.isRequired,
  }

  shouldComponentUpdate(nextProps) {
    return this.props.value !== nextProps.value;
  }

  render() {
    const {value, editSearch} = this.props;

    return (
      <VpTextField
        name="search"
        placeholder="Search"
        value={value}
        inputClass={styles['query-input']}
        parentClass=""
        onChange={editSearch}
        hideError
      />
    );
  }
}
