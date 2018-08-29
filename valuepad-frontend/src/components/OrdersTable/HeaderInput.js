import React, {Component, PropTypes} from 'react';
import ReactTooltip from 'react-tooltip';
import {VpTextField} from 'components';

const styles = require('./style.scss');

export default class HeaderInput extends Component {
  static propTypes = {
    // Input label
    label: PropTypes.string.isRequired,
    // Input value
    value: PropTypes.string.isRequired,
    // Input name
    prop: PropTypes.string.isRequired,
    // Event handler when input value changes
    onChange: PropTypes.func.isRequired
  }

  shouldComponentUpdate(nextProps) {
    return this.props.value !== nextProps.value;
  }

  render() {
    const {label, value, prop, onChange} = this.props;

    return (
      <div>
        <label className="control-label" style={{ margin: 0 }}>
          <span>{label}</span>
        </label>
        <VpTextField
          value={value}
          name={prop}
          placeholder="Search"
          onChange={onChange}
          dataTip
          dataTipFor={prop}
          inputClass={styles['filter-input']}
          parentClass=""
          hideError
        />
        <ReactTooltip id={prop} place="bottom" type="dark" effect="solid" offset={{top: 12}}>
          <span>Enter text to filter</span>
        </ReactTooltip>
      </div>
    );
  }

}
