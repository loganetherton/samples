import React, {Component, PropTypes} from 'react';

import Immutable from 'immutable';

/**
 * ASC.gov search results
 */
export default class AscSearchResults extends Component {
  static propTypes = {
    // On click function to select a result
    selectFunction: PropTypes.func.isRequired,
    // Results for display
    results: PropTypes.instanceOf(Immutable.List)
  };

  render() {
    const styles = require('./SearchBar.scss');
    const {
      selectFunction,
      results
    } = this.props;
    let resultList;
    if (results && results.count()) {
      resultList = (
        <ul className={styles.results}>
          {results.map((result, index) => {
            return (
              <li key={index} onClick={selectFunction.bind(this, result)}>
                <a>{result.get('firstName')} {result.get('lastName')}<br />
                  <span>License: {result.get('licenseNumber')}</span>
                  <span style={{ position: 'absolute', right: '5px', top: '14px' }}>
                    <i className="material-icons" style={{ position: 'absolute', right: 0, fontSize: '22px' }}>panorama_fish_eye</i>
                    <i className="material-icons" style={{ position: 'absolute', right: '2px', top: '3px', fontSize: '16px' }}>forward</i>
                  </span>
                </a>
              </li>
            );
          })}
        </ul>
      );
    } else {
      resultList = <div/>;
    }
    return resultList;
  }
}
