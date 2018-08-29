import React, {Component, PropTypes} from 'react';
import Immutable from 'immutable';

import {States, VpTextField} from 'components';

import {checkFormInequality} from 'helpers/genericFunctions';

/**
 * Render a basic address form piece consisting of address, additional address, city, state, and zip
 */
export default class Address extends Component {
  static propTypes = {
    // Main form
    form: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Change form inputs
    formChange: PropTypes.func.isRequired,
    // Change state function
    changeState: PropTypes.func.isRequired,
    // Errors
    errors: PropTypes.instanceOf(Immutable.Map),
    // Enter function
    enterFunction: PropTypes.func,
    // Name of state property
    stateName: PropTypes.string,
    // Overrides for name property
    nameOverrides: PropTypes.object,
    // Tab indexes begin
    tabIndexStart: PropTypes.number,
    // Component is required (such as during sign up_
    required: PropTypes.bool,
    // Error message overrides
    errorMessages: PropTypes.object,
    // Disabled
    disabled: PropTypes.bool
  };

  /**
   * Only update if specific values in this component are updated
   * @param nextProps
   */
  shouldComponentUpdate(nextProps) {
    const {form, errors, nameOverrides = {}} = this.props;
    const {form: nextForm, errors: nextErrors} = nextProps;
    const checkFormItemInequality = checkFormInequality.bind(this, form, nextForm);
    const checkErrorInequality = checkFormInequality.bind(this, errors, nextErrors);
    // Check if any form values have changed
    if (checkFormItemInequality(nameOverrides.address1 || 'address1') ||
        checkFormItemInequality(nameOverrides.address2 || 'address2') ||
        checkFormItemInequality(nameOverrides.city || 'city') ||
        checkFormItemInequality(nameOverrides.state || 'state') ||
        checkFormItemInequality(nameOverrides.zip || 'zip')
    ) {
      return true;
    }
    // Check if any form errors have changed
    return (checkErrorInequality(nameOverrides.address1 || 'address1') ||
            checkErrorInequality(nameOverrides.address2 || 'address2') ||
            checkErrorInequality(nameOverrides.city || 'city') ||
            checkErrorInequality(nameOverrides.state || 'state') ||
            checkErrorInequality(nameOverrides.zip || 'zip')
    );
  }

  zipChange(event) {
    const {formChange, nameOverrides} = this.props;
    const zip = nameOverrides && nameOverrides.zip ? nameOverrides.zip : 'zip';
    let value = event.target.value;

    value = value.replace(/[^\d]*/g, '');
    if (value && typeof value === 'string') {
      if (value.length > 5) {
        value = value.substr(0, 5);
      }
    }
    formChange(Object.assign(event, {target: {name: zip, value}}));
  }

  /**
   * Get error for any given input
   * @param prop
   */
  getError(prop) {
    const {errors, errorMessages = {}} = this.props;
    return errors.get(prop) && errorMessages[prop] ? errorMessages[prop] : errors.get(prop);
  }

  render() {
    const {
      form,
      formChange,
      changeState,
      enterFunction,
      nameOverrides = {},
      tabIndexStart = 0,
      required,
      disabled = false
    } = this.props;
    const address1 = nameOverrides.address1 || 'address1';
    const address2 = nameOverrides.address2 || 'address2';
    const city = nameOverrides.city || 'city';
    const state = nameOverrides.state || 'state';
    const zip = nameOverrides.zip || 'zip';
    return (
      <div>
        <div className="row">
          <div className="col-md-6">
            <VpTextField
              value={form.get(address1)}
              label="Address"
              name={address1}
              placeholder="Address"
              onChange={formChange}
              tabIndex={tabIndexStart ? tabIndexStart : 0}
              error={this.getError.call(this, address1)}
              enterFunction={enterFunction}
              required={required}
              disabled={disabled}
            />
          </div>
          <div className="col-md-6">
            <VpTextField
              value={form.get(address2)}
              label="Additional address"
              name={address2}
              placeholder="Additional address"
              onChange={formChange}
              tabIndex={tabIndexStart ? tabIndexStart + 1 : 0}
              error={this.getError.call(this, address2)}
              enterFunction={enterFunction}
              disabled={disabled}
            />
          </div>
        </div>
        <div className="row">
          <div className="col-md-4">
            <VpTextField
              value={form.get(city)}
              label="City"
              name={city}
              placeholder="City"
              onChange={formChange}
              tabIndex={tabIndexStart ? tabIndexStart + 2 : 0}
              error={this.getError.call(this, city)}
              enterFunction={enterFunction}
              required={required}
              disabled={disabled}
            />
          </div>
          <div className="col-md-4">
            <States
              form={form}
              changeHandler={changeState}
              label="State"
              name={state}
              required={required}
              disabled={disabled}
            />
          </div>
          <div className="col-md-4">
            <VpTextField
              value={form.get(zip)}
              label="Zip"
              name={zip}
              placeholder="Zip"
              onChange={::this.zipChange}
              tabIndex={tabIndexStart ? tabIndexStart + 3 : 0}
              error={this.getError.call(this, zip)}
              enterFunction={enterFunction}
              required={required}
              disabled={disabled}
            />
          </div>
        </div>
      </div>
    );
  }
}
