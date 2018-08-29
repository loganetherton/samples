import React, {Component, PropTypes} from 'react';
import Immutable from 'immutable';

// Generic default prop name for orders searching
const defaultPropName = 'filter[processStatus]';

// Process statuses
const statuses = Immutable.fromJS([
  {name: 'New', code: 'new'},
  {name: 'Request For Bid', code: 'request-for-bid'},
  {name: 'Accepted', code: 'accepted'},
  {name: 'Inspection Scheduled', code: 'inspection-scheduled'},
  {name: 'Inspection Complete', code: 'inspection-completed'},
  {name: 'Ready For Review', code: 'ready-for-review'},
  {name: 'Late', code: 'late'},
  {name: 'On Hold', code: 'on-hold'},
  {name: 'Revision Pending', code: 'revision-pending'},
  {name: 'Revision In Review', code: 'revision-in-review'},
  {name: 'Reviewed', code: 'reviewed'},
  {name: 'Completed', code: 'completed'}
]);

/**
 * Selector for process statuses
 */
export default class FilterProcessStatusDropdown extends Component {
  static propTypes = {
    form: PropTypes.object,
    // Handle selection
    changeHandler: PropTypes.func.isRequired,
    // Label
    label: PropTypes.string,
    // Prop name
    name: PropTypes.oneOfType([
      PropTypes.string,
      PropTypes.array
    ]),
    // Disable status selection
    disabled: PropTypes.bool,
    // Full width
    fullWidth: PropTypes.bool
  };

  /**
   * Change process status
   * @param event
   * @param id
   * @param value Value
   */
  selectStatus(event) {
    this.props.changeHandler(event.target.value);
  }

  render() {
    const {form, name = defaultPropName, disabled} = this.props;
    let value = Array.isArray(name) ? form.getIn(name) : form.get(name);
    // Don't display for multiple process statuses
    if (value && value.indexOf(',') !== -1) {
      value = '';
    }
    return (
      <select
        name={name}
        style={{ width: '100%', border: 'none', fontSize: '12px', margin: 0, padding: 0, left: '-7px', position: 'relative', background: 'none' }}
        selected={value}
        onChange={::this.selectStatus}
        disabled={disabled}
      >
        <option value="">Search Status</option>
        {statuses.map(status => <option value={status.get('code')} key={status.get('code')}>{status.get('name')}</option>)}
      </select>
    );
  }
}
