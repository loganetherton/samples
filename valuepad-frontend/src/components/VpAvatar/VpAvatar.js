import React, {Component, PropTypes} from 'react';

const defaultStyle = {
  height: '20px',
  width: '20px',
  borderRadius: '50%',
  display: 'inline-block',
  textAlign: 'center',
  lineHeight: '20px',
  fontSize: '14px',
  color: 'white'
};

export default class VpAvatar extends Component {
  static propTypes = {
    /**
     * Affects the size of the avatar (width & height) and the line height as well
     */
    size: PropTypes.number,
    /**
     * Content to display inside the avatar element
     */
    children: PropTypes.node,
    /**
     * Text color inside the avatar element
     */
    color: PropTypes.string,
    /**
     * The avatar background color
     */
    backgroundColor: PropTypes.string,
    /**
     * Class name to attach to the avatar element
     */
    className: PropTypes.string,
    /**
     * If true, removes border-radius
     */
    squareEdges: PropTypes.bool
  }

  /**
   * Merge the passed in styling with the default one
   *
   * @param {object} overrides An object that overrides the default styling
   * @returns {object}
   */
  getStyle(overrides) {
    const newStyle = Object.assign(defaultStyle, overrides);

    return newStyle;
  }

  render() {
    const {size, backgroundColor, color, className, children, squareEdges} = this.props;

    const inlineStyle = this.getStyle({
      height: size + 'px',
      width: size + 'px',
      lineHeight: size + 'px',
      backgroundColor: backgroundColor,
      color: color
    });

    if (squareEdges) {
      delete inlineStyle.borderRadius;
    }

    return (
      <div style={inlineStyle} className={className}>
        {children}
      </div>
    );
  }
}
