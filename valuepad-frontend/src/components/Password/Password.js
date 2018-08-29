import React, {Component, PropTypes} from 'react';
import Immutable from 'immutable';

import {BetterTextField} from 'components';

/**
 * Password/confirm password
 */
export default class Password extends Component {
  static propTypes = {
    // Main form
    form: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Change form inputs
    formChange: PropTypes.func.isRequired,
    // Errors
    errors: PropTypes.instanceOf(Immutable.Map),
    // Enter function
    enterFunction: PropTypes.func,
    // Password text
    passwordText: PropTypes.string,
    // Confirm text
    confirmText: PropTypes.string,
    // Tab indexes begin
    tabIndexStart: PropTypes.number,
    // Required
    required: PropTypes.bool,
    // Disabled
    disabled: PropTypes.bool
  };

  render() {
    const {
      form,
      formChange,
      enterFunction,
      passwordText = 'Create password',
      confirmText = 'Confirm password',
      tabIndexStart = 0,
      required = false,
      errors,
      disabled = false
    } = this.props;

    let passwordError, confirmError;
    if (errors.get('password')) {
      passwordError = errors.get('password');
    } else if (form.get('password') && form.get('password').length < 5) {
      passwordError = 'Password must be at least 5 characters';
    }

    if (errors.get('confirm')) {
      confirmError = errors.get('confirm');
    } else if (form.get('confirm') && form.get('password') !== form.get('confirm')) {
      confirmError = 'Password and confirm password do not match';
    }

    return (
      <div>
        <div className="col-md-6">
          <BetterTextField
            name="password"
            type="password"
            value={form.get('password')}
            label={passwordText}
            placeholder="Password"
            onChange={formChange}
            enterFunction={enterFunction}
            tabIndex={tabIndexStart ? tabIndexStart : 0}
            required={required}
            error={passwordError}
            disabled={disabled}
          />
        </div>
        <div className="col-md-6">
          <BetterTextField
            name="confirm"
            type="password"
            value={form.get('confirm')}
            label={confirmText}
            placeholder="Confirm password"
            onChange={formChange}
            enterFunction={enterFunction}
            tabIndex={tabIndexStart ? tabIndexStart + 1 : 0}
            required={required}
            error={confirmError}
            disabled={disabled}
          />
        </div>
      </div>
    );
  }
}
