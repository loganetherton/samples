import React, {Component, PropTypes} from 'react';
import Immutable from 'immutable';
import {capitalizeWords} from 'helpers/string';

export default class CoverageCounties extends Component {
  static propTypes = {
    // County list
    counties: PropTypes.instanceOf(Immutable.List),
    // whether this is shown or not
    show: PropTypes.bool,
  };

  render() {
    const {
      counties,
      show = false,
    } = this.props;

    if (!show) return null;

    if (counties.count()) {
      const ct = counties.sort();
      return (
        <div style={ styles.outerContainer }>
          <div style={ styles.container }>
            <ul style={ styles.list }>
              {ct.map((county, index) => {
                return (
                  <li key={index} style={ styles.listItem }>{capitalizeWords(county.toLowerCase())}</li>
                );
              })}
            </ul>
          </div>
        </div>
      );
    } else {
      return (
        <div style={ styles.outerContainer }>
          <div style={ styles.container }>
            No counties selected.
          </div>
        </div>
      );
    }
  }
}

const styles = {
  container: {
    background: '#FFFFFF',
    border: '1px solid #DDDDDD',
    maxHeight: '300px',
    overflow: 'auto',
    padding: '10px',
    position: 'absolute',
    zIndex: 1000,
  },
  list: {
    margin: 0,
    padding: 0,
    listStyle: 'none',
  },
  outerContainer: {
    position: 'relative',
    textAlign: 'left',
  },
};
