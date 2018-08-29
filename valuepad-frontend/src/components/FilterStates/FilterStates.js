import React, {Component, PropTypes} from 'react';
import Immutable from 'immutable';

// US states
const states = Immutable.fromJS([
  {name: 'ALABAMA', code: 'AL'},
  {name: 'ALASKA', code: 'AK'},
  {name: 'ARIZONA', code: 'AZ'},
  {name: 'ARKANSAS', code: 'AR'},
  {name: 'CALIFORNIA', code: 'CA'},
  {name: 'COLORADO', code: 'CO'},
  {name: 'CONNECTICUT', code: 'CT'},
  {name: 'DELAWARE', code: 'DE'},
  {name: 'DISTRICT OF COLUMBIA', code: 'DC'},
  {name: 'FLORIDA', code: 'FL'},
  {name: 'GEORGIA', code: 'GA'},
  {name: 'HAWAII', code: 'HI'},
  {name: 'IDAHO', code: 'ID'},
  {name: 'ILLINOIS', code: 'IL'},
  {name: 'INDIANA', code: 'IN'},
  {name: 'IOWA', code: 'IA'},
  {name: 'KANSAS', code: 'KS'},
  {name: 'KENTUCKY', code: 'KY'},
  {name: 'LOUISIANA', code: 'LA'},
  {name: 'MAINE', code: 'ME'},
  {name: 'MARYLAND', code: 'MD'},
  {name: 'MASSACHUSETTS', code: 'MA'},
  {name: 'MICHIGAN', code: 'MI'},
  {name: 'MINNESOTA', code: 'MN'},
  {name: 'MISSISSIPPI', code: 'MS'},
  {name: 'MISSOURI', code: 'MO'},
  {name: 'MONTANA', code: 'MT'},
  {name: 'NEBRASKA', code: 'NE'},
  {name: 'NEVADA', code: 'NV'},
  {name: 'NEW HAMPSHIRE', code: 'NH'},
  {name: 'NEW JERSEY', code: 'NJ'},
  {name: 'NEW MEXICO', code: 'NM'},
  {name: 'NEW YORK', code: 'NY'},
  {name: 'NORTH CAROLINA', code: 'NC'},
  {name: 'NORTH DAKOTA', code: 'ND'},
  {name: 'OHIO', code: 'OH'},
  {name: 'OKLAHOMA', code: 'OK'},
  {name: 'OREGON', code: 'OR'},
  {name: 'PENNSYLVANIA', code: 'PA'},
  {name: 'RHODE ISLAND', code: 'RI'},
  {name: 'SOUTH CAROLINA', code: 'SC'},
  {name: 'SOUTH DAKOTA', code: 'SD'},
  {name: 'TENNESSEE', code: 'TN'},
  {name: 'TEXAS', code: 'TX'},
  {name: 'UTAH', code: 'UT'},
  {name: 'VERMONT', code: 'VT'},
  {name: 'VIRGINIA', code: 'VA'},
  {name: 'WASHINGTON', code: 'WA'},
  {name: 'WEST VIRGINIA', code: 'WV'},
  {name: 'WISCONSIN', code: 'WI'},
  {name: 'WYOMING', code: 'WY'}
]);

/**
 * US states as select input
 */
export default class FilterStates extends Component {
  static propTypes = {
    form: PropTypes.object,
    // Handle selection
    changeHandler: PropTypes.func.isRequired,
    // Label
    label: PropTypes.string,
    // BS attributes to display errors
    bsAttrs: PropTypes.object,
    // Prop name
    name: PropTypes.oneOfType([
      PropTypes.string,
      PropTypes.array
    ]),
    // Disable state selection
    disabled: PropTypes.bool,
    // Full width
    fullWidth: PropTypes.bool
  };

  /**
   * State select changes
   * @param event
   * @param id
   * @param value Value
   */
  selectState(event) {
    this.props.changeHandler(event.target.value);
  }

  render() {
    const {form, name, disabled} = this.props;
    let selected = Array.isArray(name) ? form.getIn(name) : form.get(name || 'state');
    if (selected === undefined || !selected) {
      selected = '';
    }

    return (
      <select
        className={ selected ? 'select-filter selected' : 'select-filter' }
        name={name}
        value={selected}
        style={{ width: '100%', border: 'none', fontSize: '12px', margin: 0, padding: 0, left: '-7px', position: 'relative', background: 'none' }}
        onChange={::this.selectState}
        disabled={disabled}
      >
        <option value="">SEARCH STATE</option>
        {states.map(state => <option value={state.get('code')} key={state.get('code')}>{state.get('name')}</option>)}
      </select>
    );
  }
}
