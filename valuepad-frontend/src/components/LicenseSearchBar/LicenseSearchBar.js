import React, {Component, PropTypes} from 'react';
import {AscSearchResults} from 'components';
import Immutable from 'immutable';
import QueryInput from './QueryInput';
// Amount of time it takes to blur results
const blurTime = 250;

/**
 * ASC.gov license number search bar
 */
export default class LicenseSearchBar extends Component {

  static propTypes = {
    name: PropTypes.string,
    value: PropTypes.string,
    // Change function
    onChange: PropTypes.func.isRequired,
    // Label
    label: PropTypes.string,
    // Results from search
    results: PropTypes.instanceOf(Immutable.List),
    // Disable search bar
    disabled: PropTypes.bool,
    // If no results are found
    noResultsFound: PropTypes.bool,
    // Function to select an ASC result
    selectFunction: PropTypes.func.isRequired,
    // Don't perform an actual search
    noSearch: PropTypes.bool,
    // Error
    error: PropTypes.string
  };

  constructor(props) {
    super(props);
    this.state = {
      showResults: true
    };
  }

  /**
   * Prevent blur actions after unmount
   */
  componentWillUnmount() {
    // Cancel blur actions on unmount
    if (this.blurring) {
      clearTimeout(this.blurring);
    }
  }

  /**
   * Hide results on blur
   */
  hideResults() {
    this.blurring = setTimeout(() => {
      this.setState({
        showResults: false
      });
      this.blurring = null;
    }, blurTime);
  }

  /**
   * Show results on focus
   */
  showResults() {
    this.setState({
      showResults: true
    });
  }

  render() {
    const styles = require('./SearchBar.scss');
    const {
      name,
      value = '',
      onChange,
      label,
      results,
      selectFunction,
      disabled,
      noResultsFound,
      noSearch = false,
      error
    } = this.props;
    return (
      <div className={styles.searchBar}>
        <form className={styles.search}>
          <QueryInput
            name={name}
            value={value}
            label={label}
            onChange={onChange}
            disabled={disabled}
            hideResults={::this.hideResults}
            showResults={::this.showResults}
            noResultsFound={noResultsFound}
            error={error}
          />
          {!noSearch &&
            <AscSearchResults
              results={results}
              selectFunction={selectFunction}
            />
          }
        </form>
      </div>
    );
  }
}
