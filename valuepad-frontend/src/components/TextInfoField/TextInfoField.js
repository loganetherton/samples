import React, {Component, PropTypes} from 'react';

const styles = {
  main: {
    didFlip: true,
    fontSize: '16px',
    lineHeight: '24px',
    width: '100%',
    height: '72px',
    display: 'inline-block',
    position: 'relative',
    backgroundColor: 'transparent',
    fontFamily: 'Roboto, sans-serif',
    transition: 'height 200ms cubic-bezier(0.23, 1, 0.32, 1) 0ms'
  },
  label: {
    didFlip: true,
    position: 'absolute',
    lineHeight: '22px',
    top: '38px',
    transition: 'all 450ms cubic-bezier(0.23, 1, 0.32, 1) 0ms',
    zIndex: 1,
    transform: 'perspective(1px) scale(0.75) translate3d(2px, -28px, 0)',
    transformOrigin: 'left top',
    pointerEvents: 'none',
    color: 'rgba(0,0,0,0.5)',
    textTransform: 'uppercase',
  },
  value: {
    didFlip: true,
    padding: 0,
    position: 'relative',
    width: '100%',
    height: '100%',
    border: 'none',
    outline: 'none',
    backgroundColor: 'transparent',
    color: 'rgba(0, 0, 0, 0.87)',
    font: 'inherit',
    boxSizing: 'border-box',
    marginTop: '14px',
    top: '24px'
  },
  hr1: {
    didFlip: true,
    border: 'none',
    borderBottom: 'solid 1px',
    borderColor: '#e0e0e0',
    bottom: '8px',
    boxSizing: 'content-box',
    margin: 0,
    position: 'absolute',
    width: '100%'
  },
  hr2: {
    borderStyle: 'none none solid',
    borderBottomWidth: '2px',
    borderColor: 'rgb(23, 161, 229)',
    bottom: '8px',
    boxSizing: 'content-box',
    margin: '0px',
    position: 'absolute',
    width: '100%',
    transform: 'scaleX(0)',
    transition: 'all 450ms cubic-bezier(0.23, 1, 0.32, 1) 0ms'
  }
};

/**
 * Simulate normal text field, only displaying text instead of text manipulation
 */
export default class TextInfoField extends Component {
  static propTypes = {
    // Label
    label: PropTypes.string.isRequired,
    // Value
    value: PropTypes.string
  };

  render() {
    const {label, value} = this.props;
    return (
      <div
        style={styles.main}>
        <label
          style={styles.label}>{label}</label>
        <div style={styles.value}
             type="text" id="mui-id-141"> {value}</div>
        <div >
          <hr
            style={styles.hr1} />
          <hr
            style={styles.hr2} />
        </div>
      </div>
    );
  }
}
