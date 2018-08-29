import React, {Component, PropTypes} from 'react';

const styles = {
  main: {
    paddingTop: '4px',
    paddingBottom: '4px',
  },
  label: {
    opacity: 1,
    textTransform: 'uppercase',
    fontSize: '12px',
  },
  text: {
    color: '#666666',
  },
  value: {
    color: '#777',
  }
};

/**
 * Simulate normal text field, only displaying text instead of text manipulation
 */
export default class TableTextInfoField extends Component {
  static propTypes = {
    // Label
    label: PropTypes.string.isRequired,
    // Value
    value: PropTypes.string,
    // Dangerously set inner html
    dangerously: PropTypes.bool
  };

  /**
   * Check to make sure we have something worth displaying, else show N/A
   * @param value Incoming value
   */
  testValue(value) {
    if (typeof value === 'string') {
      // Remove null strings
      const nullRemoved = value.replace(/\bnull\b/, '');
      // If no other letters or number remain, return empty
      if (!/[a-zA-Z0-9]/.test(nullRemoved)) {
        value = '';
      }
    } else {
      value = '';
    }
    return value && value.toString().trim() ? value : 'N/A';
  }

  render() {
    const {label, dangerously = false} = this.props;
    const value = this.testValue(this.props.value);
    return (
      <div style={styles.main}>
        <label style={styles.label}>{label}</label>
        {dangerously &&
          <div style={styles.value} dangerouslySetInnerHTML={{__html: value}} />
        }
        {!dangerously &&
          <div style={styles.value}>{value}</div>
        }
      </div>
    );
  }
}
