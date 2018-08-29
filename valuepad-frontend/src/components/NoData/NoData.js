import React, {Component, PropTypes} from 'react';

import {FontIcon} from 'material-ui';

const style = {
  noData: {
    top: 0,
    bottom: 0,
    left: 0,
    right: 0,
    margin: 'auto'
  },
  iconAdjustDown: {top: '3px'}
};

export default class NoData extends Component {
  static propTypes = {
    // Text to display
    text: PropTypes.string.isRequired
  };

  render() {
    const {text} = this.props;

    return (
      <div className="text-center" style={style.noData}>
        <h3>
          <FontIcon className="material-icons" style={style.iconAdjustDown}>warning</FontIcon>
          <span> {text} </span>
        </h3>
      </div>
    );
  }
}
