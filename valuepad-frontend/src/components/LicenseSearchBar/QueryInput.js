import React, {Component, PropTypes} from 'react';

import {VpTextField} from 'components';

export default class QueryInput extends Component {
  static propTypes = {
    name: PropTypes.string.isRequired,
    value: PropTypes.string.isRequired,
    label: PropTypes.string.isRequired,
    onChange: PropTypes.func.isRequired,
    disabled: PropTypes.bool.isRequired,
    hideResults: PropTypes.func.isRequired,
    showResults: PropTypes.func.isRequired,
    noResultsFound: PropTypes.bool.isRequired,
    error: PropTypes.string
  };

  shouldComponentUpdate(nextProps) {
    return this.props.value !== nextProps.value || this.props.noResultsFound !== nextProps.noResultsFound;
  }

  render() {
    const {
      name,
      value,
      label,
      onChange,
      disabled,
      hideResults,
      showResults,
      noResultsFound,
      error
    } = this.props;
    let displayError = '';
    // Display error
    if (error) {
      displayError = error;
    } else if (noResultsFound) {
      displayError = 'License not found';
    }

    return (
      <VpTextField
        autocomplete="off"
        name={name}
        value={value}
        label={label}
        fullWidth
        onChange={onChange}
        disabled={disabled}
        onBlur={hideResults}
        onFocus={showResults}
        error={displayError}
      />
    );
  }
}
