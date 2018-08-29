import React, {Component, PropTypes} from 'react';

import Immutable from 'immutable';
import {Checkbox} from 'material-ui';

/**
 * @todo THIS IS DEPRECATED, REMOVE ONCE I SEE ITS NOT LONGER NEEDED
 * @type {*|any}
 */

// Certification types
const certifications = Immutable.fromJS([
  { name: 'Certified Residential Appraiser', code: 'certified-residential'},
  { name: 'Licensed Appraiser', code: 'licensed'},
  { name: 'Certified General Appraiser', code: 'certified-general'},
  { name: 'Transitional License', code: 'transitional-license'}
]);

/**
 * Appraiser certification types dropdown
 */
export default class Certifications extends Component {
  static propTypes = {
    // onChange function
    changeHandler: PropTypes.func.isRequired,
    // Form
    form: PropTypes.instanceOf(Immutable.Map),
    // Form prop
    formProp: PropTypes.oneOfType([
      PropTypes.string,
      PropTypes.array
    ]).isRequired,
    // Label
    label: PropTypes.string,
    // Disabled
    disabled: PropTypes.bool
  };

  shouldComponentUpdate(nextProps) {
    const formProp = this.props.formProp;
    const thisSelected = this.getSelected(this.props.form, formProp);
    const nextSelected = this.getSelected(nextProps.form, formProp);
    // Only update if changed
    return thisSelected !== nextSelected;
  }

  /**
   * Get selected items
   * @param form
   * @param formProp Property of selected items
   * @returns {any|*}
   */
  getSelected(form, formProp) {
    formProp = Array.isArray(formProp) ? formProp : [formProp];
    return form.getIn(formProp);
  }

  render() {
    const {
      changeHandler,
      form,
      disabled
    } = this.props;
    // Get selected items
    const selected = this.getSelected.call(this, form, this.props.formProp);
    return (
      <div>
        {certifications.map((certification, index) =>
          <div key={index}>
            <Checkbox
              checked={selected.includes(certification.get('code'))}
              label={certification.get('name')}
              fullWidth
              onCheck={changeHandler.bind(this, certification.get('code'))}
              disabled={disabled}
            />
          </div>
        )}
      </div>
    );
  }
}
