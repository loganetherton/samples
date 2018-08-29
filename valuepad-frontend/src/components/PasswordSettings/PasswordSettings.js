import React, {Component, PropTypes} from 'react';
import Immutable from 'immutable';

import {Password} from 'components';

import {Dialog} from 'material-ui';

/**
 * Password/confirm password
 */
export default class PasswordSettings extends Component {
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
    // Settings
    settings: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Selected appraiser
    selectedAppraiser: PropTypes.number
  };

  state = {
    updatePassword: false,
    updatePasswordSuccess: null
  }

  constructor(props) {
    super(props);

    this.closePasswordUpdate = ::this.closePasswordUpdate;
  }

  /**
   * Display dialog on success/failure
   */
  componentWillReceiveProps(nextProps) {
    const {settings} = this.props;
    const {settings: nextSettings} = nextProps;
    if (typeof settings.get('passwordResetSuccess') === 'undefined' &&
        typeof nextSettings.get('passwordResetSuccess') === 'boolean') {
      this.setState({
        updatePassword: true,
        updatePasswordSuccess: nextSettings.get('passwordResetSuccess')
      });
    }
  }

  /**
   * Close feedback dialog
   */
  closePasswordUpdate() {
    this.setState({
      updatePassword: false
    });
  }

  render() {
    const {updatePassword, updatePasswordSuccess} = this.state;
    const {
      form,
      formChange,
      errors,
      enterFunction,
      passwordText,
      confirmText,
      tabIndexStart,
      required,
      selectedAppraiser
    } = this.props;

    return (
      <div>
        <Password
          form={form}
          formChange={formChange}
          errors={errors}
          enterFunction={enterFunction}
          passwordText={passwordText}
          confirmText={confirmText}
          tabIndexStart={tabIndexStart}
          required={required}
          disabled={!!selectedAppraiser}
        />
        {/*Update password feedback*/}
        <Dialog
          open={updatePassword}
          actions={
            <button className="btn btn-raised btn-info"
              onClick={this.closePasswordUpdate}>Close</button>
          }
          title={updatePasswordSuccess ? 'Password Updated' : 'Failed To Update Password'}
        >
          <h4>
            {updatePasswordSuccess ? 'Your password has been updated' :
             'An error has occurred, and your password has not been updated'}
          </h4>
        </Dialog>
      </div>
    );
  }
}
