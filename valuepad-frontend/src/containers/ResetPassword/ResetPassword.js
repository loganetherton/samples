import React, {PropTypes, Component} from 'react';
import {connect} from 'react-redux';
import {Link} from 'react-router';
import Immutable from 'immutable';

import {
  ActionButton,
  Void,
  VpTextField,
} from 'components';

import {
  LOGIN_URL,
} from 'redux/modules/urls';

import {
  resetPassword,
  setProp,
} from 'redux/modules/auth';

@connect(
  state => ({
    auth: state.auth,
    router: state.router,
  }),
  {
    resetPassword,
    setProp,
  })
export default class ForgotPassword extends Component {
  static propTypes = {
    auth: PropTypes.object.isRequired,
    router: PropTypes.object.isRequired,
    resetPassword: PropTypes.func.isRequired,
    setProp: PropTypes.func.isRequired,
    params: PropTypes.object.isRequired,
  };

  constructor(props) {
    super(props);

    this.passwordChanged = ::this.passwordChanged;
    this.formSubmit = ::this.formSubmit;
    this.passwordConfirmChanged = ::this.passwordConfirmChanged;
  }

  componentWillUnmount() {
    this.props.setProp(Immutable.Map(), 'resetPassword');
    this.props.setProp(Immutable.Map(), 'resetPasswordErrors');
    this.props.setProp(null, 'resetPasswordSuccess');
    this.props.setProp(null, 'sendingResetPassword');
  }

  formSubmit() {
    const password = this.props.auth.getIn(['resetPassword', 'password']);
    const passwordConfirm = this.props.auth.getIn(['resetPassword', 'password_confirm']);

    if (!this.props.auth.get('sendingResetPassword', false)) {
      // if the passwords don't match let the user know
      if (password !== passwordConfirm) {
        this.props.setProp(Immutable.fromJS({ password: 'Your passwords do not match.' }), 'resetPasswordErrors');
      } else {
        this.props.resetPassword(
          this.props.router.location.query.token,
          password
        );
      }
    }
  }

  passwordChanged(event) {
    this.props.setProp(event.target.value, 'resetPassword', 'password');
  }

  passwordConfirmChanged(event) {
    this.props.setProp(event.target.value, 'resetPassword', 'password_confirm');
  }

  render() {
    const {auth, router} = this.props;
    // Auth Bootstrap errors
    const form = auth.get('resetPassword');
    const formErrors = auth.get('resetPasswordErrors');
    const token = router.location.query.token;

    return (
      <div>
        <div className="container-fluid">
          <h4 className="text-center">Reset Password</h4>

          {!token &&
            <div className="text-center text-danger">
              There was an error processing your request.
            </div>
          }

          {token && formErrors.get('token') &&
            <div className="text-center text-danger">
              {formErrors.get('token')}
            </div>
          }

          {token && !auth.get('resetPasswordSuccess') &&
            <div>
              <VpTextField
                type="password"
                name="password"
                value={form.get('password')}
                label="Enter your new password"
                placeholder="Password"
                onChange={this.passwordChanged}
                enterFunction={this.formSubmit}
                error={formErrors.get('password')}
              />
              <VpTextField
                type="password"
                name="password_confirm"
                value={form.get('password_confirm')}
                label="Confirm your new password"
                placeholder="Confirm Password"
                onChange={this.passwordConfirmChanged}
                enterFunction={this.formSubmit}
                error={formErrors.get('password_confirm')}
              />
              <Void pixels={10}/>
              <div className="text-center">
                <ActionButton
                  type="submit"
                  onClick={this.formSubmit}
                  disabled={auth.get('sendingResetPassword', false)}
                />
              </div>
            </div>
          }

          {token && auth.get('resetPasswordSuccess') &&
            <div>
              <div className="text-center">Your password has been reset. <Link to={LOGIN_URL} className="link">Click here</Link> to login.</div>
            </div>
          }

          <Void pixels={10}/>
        </div>
      </div>
    );
  }
}
