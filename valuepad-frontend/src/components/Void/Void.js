/**
 * Adds void space.
 *
 * Usage:
 * <Void pixels={number} />
 * <Void /> - Default is 60 pixels
 */

import React, {PropTypes, Component} from 'react';

export default class Void extends Component {

  static propTypes = {
    pixels: PropTypes.oneOfType([
      PropTypes.string,
      PropTypes.number
    ]).isRequired,
    clear: PropTypes.bool
  };

  constructor(props) {
    super(props);
  }

  render() {
    const {pixels = 60, clear = false} = this.props;

    const devStyle = {
      paddingBottom: parseInt(pixels, 10) + 'px'
    };

    if (clear) {
      devStyle.clear = 'both';
    }

    return (
      <div style={devStyle}></div>
    );
  }
}
