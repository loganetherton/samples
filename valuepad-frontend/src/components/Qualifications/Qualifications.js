import React, {Component, PropTypes} from 'react';

import {VpMultiselect} from 'components';
import Immutable from 'immutable';

// Available qualifications
const qualifications = Immutable.fromJS([
  { name: 'VA Qualified', value: 'vaQualified'},
  { name: 'FHA Qualified', value: 'fhaQualified'},
  { name: 'Relocation Qualified', value: 'relocationQualified'},
  { name: 'USDA Qualified', value: 'usdaQualified'},
  { name: 'Co-Op Qualified', value: 'coopQualified'},
  { name: 'Jumbo Qualified', value: 'jumboQualified'},
  { name: 'New Construction Qualified', value: 'newConstructionQualified'},
  { name: '203K Qualified', value: 'loan203KQualified'},
  { name: 'Manufactured Home Qualified', value: 'manufacturedHomeQualified'},
  { name: 'REO Qualified', value: 'reoQualified'},
  { name: 'Desk Review Qualified', value: 'deskReviewQualified'},
  { name: 'Field Review Qualified', value: 'fieldReviewQualified'},
  { name: 'ENV Capable', value: 'envCapable'}
]);

/**
 * Select from list of available qualifications
 */
export default class Qualifications extends Component {
  static propTypes = {
    // Value container -- This is because the backend isn't structured as it is in other places here. In most
    // place we're get an array of values. For some reason, it's a key -> value pair here
    valueContainer: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Set prop
    setProp: PropTypes.func.isRequired,
    // Appraiser
    form: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Disabled
    disabled: PropTypes.bool,
    // Prop path
    propPath: PropTypes.array.isRequired
  };

  /**
   * Handle a select
   */
  onClick(prop) {
    const {setProp, form, propPath} = this.props;
    const currentValue = form.getIn(['qualifications', prop]);
    setProp(!currentValue, ...propPath, prop);
  }

  render() {
    const {valueContainer, disabled = false} = this.props;
    // Determine the possible entries for this dropdown
    const possibleEntries = qualifications.map(qualification => qualification.get('value'));
    // Get the entries that are selected in a list
    const selectedEntries = valueContainer
      .filter((entry, key) => entry && typeof entry === 'boolean' && possibleEntries.indexOf(key) !== -1)
      .map((v, k) => k)
      .toList();

    return (
      <VpMultiselect
        options={qualifications}
        selected={selectedEntries}
        onClick={::this.onClick}
        label="Qualifications"
        disabled={disabled}
      />
    );
  }
}
