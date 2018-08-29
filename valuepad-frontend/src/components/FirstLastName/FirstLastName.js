import React, {Component, PropTypes} from 'react';
import Immutable from 'immutable';

import {VpTextField} from 'components';
import {checkFormInequality} from 'helpers/genericFunctions';

/**
 * First and last name inputs
 */
export default class FirstLastName extends Component {
  static propTypes = {
    // Main form
    form: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Change form inputs
    formChange: PropTypes.func.isRequired,
    // Errors
    errors: PropTypes.instanceOf(Immutable.Map),
    // Enter function
    enterFunction: PropTypes.func,
    // Tab indexes begin
    tabIndexStart: PropTypes.number,
    // Required fields
    required: PropTypes.bool,
    // Disabled
    disabled: PropTypes.bool
  };

  /**
   * Only update if specific values in this component are updated
   * @param nextProps
   */
  shouldComponentUpdate(nextProps) {
    const {form, errors} = this.props;
    const {form: nextForm, errors: nextErrors} = nextProps;
    const checkFormItemInequality = checkFormInequality.bind(this, form, nextForm);
    const checkErrorInequality = checkFormInequality.bind(this, errors, nextErrors);
    // Check if any form values have changed
    if (checkFormItemInequality('firstName') ||
        checkFormItemInequality('lastName')
    ) {
      return true;
    }
    // Check if any form errors have changed
    return (checkErrorInequality('firstName') ||
        checkErrorInequality('lastName')
    );
  }

  render() {
    const {
      form,
      formChange,
      errors,
      tabIndexStart = 0,
      enterFunction,
      required = false,
      disabled = false
      } = this.props;
    return (
      <div className="row">
        <div className="col-md-6">
          <VpTextField
            value={form.get('firstName')}
            label="First name"
            name="firstName"
            placeholder="First name"
            onChange={formChange}
            tabIndex={tabIndexStart}
            error={errors.get('firstName')}
            enterFunction={enterFunction}
            required={required}
            disabled={disabled}
          />
        </div>
        <div className="col-md-6">
          <VpTextField
            value={form.get('lastName')}
            label="Last name"
            name="lastName"
            placeholder="Last name"
            onChange={formChange}
            tabIndex={tabIndexStart + 1}
            error={errors.get('lastName')}
            enterFunction={enterFunction}
            required={required}
            disabled={disabled}
          />
        </div>
      </div>
    );
  }
}
