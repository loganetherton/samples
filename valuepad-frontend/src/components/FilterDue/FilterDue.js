import React, {Component, PropTypes} from 'react';
import Immutable from 'immutable';

const options = Immutable.fromJS([
  {label: 'TODAY', value: 'today'},
  {label: 'TOMORROW', value: 'tomorrow'},
  {label: 'NEXT 7 DAYS', value: 'next-7-days'}
]);

export default class FilterDue extends Component {
  static propTypes = {
    form: PropTypes.object,
    // Handle selection
    changeHandler: PropTypes.func.isRequired,
    // Prop name
    name: PropTypes.oneOfType([
      PropTypes.string,
      PropTypes.array
    ]),
  };

  /**
   * Change due filter
   * @param event
   */
  selectDue(event) {
    this.props.changeHandler(event.target.value);
  }

  render() {
    const {form, name} = this.props;
    let selected = Array.isArray(name) ? form.getIn(name) : form.get(name || 'due');
    if (selected === undefined || !selected) {
      selected = '';
    }

    return (
      <select
        className={ selected ? 'select-filter selected' : 'select-filter' }
        name={name}
        value={selected}
        style={{ width: '100%', border: 'none', fontSize: '12px', margin: 0, padding: 0, left: '-7px', position: 'relative', background: 'none' }}
        onChange={::this.selectDue}
      >
        <option value="">DUE FILTER</option>
        {options.map(option => <option value={option.get('value')} key={option.get('value')}>{option.get('label')}</option>)}
      </select>
    );
  }
}
