import React, {PropTypes, Component} from 'react';
import {connect} from 'react-redux';
import { push } from 'redux-router';
import Immutable from 'immutable';

import {
  ActionButton,
  Void,
  VpTextField,
} from 'components';

import {
  autoLoginUpdate,
  login,
  removeProp,
  setProp,
} from 'redux/modules/auth';
import {setProp as setPropCustomer} from 'redux/modules/customer';

import {
  ORDERS_NEW_URL,
} from 'redux/modules/urls';

@connect(
  state => ({
    auth: state.auth,
    router: state.router,
  }),
  {
    autoLoginUpdate,
    login,
    pushState: push,
    removeProp,
    setProp,
    setPropCustomer
  })
export default class LoginConnect extends Component {
  static propTypes = {
    auth: PropTypes.object.isRequired,
    autoLoginUpdate: PropTypes.func.isRequired,
    login: PropTypes.func.isRequired,
    pushState: PropTypes.func.isRequired,
    router: PropTypes.object.isRequired,
    setProp: PropTypes.func.isRequired,
    setPropCustomer: PropTypes.func.isRequired,
    removeProp: PropTypes.func.isRequired,
  };

  constructor(props) {
    super(props);

    this.state = {
      loading: true,
    };
  }

  componentDidMount() {
    const {login, router, setPropCustomer} = this.props;
    const {token, redirect, appraiser} = router.location.query;
    // if the user already has a token lets redirect them to their dashboard
    if (localStorage.getItem('token')) {
      this.redirectToDashboard();
    } else {
      login({
        autoLoginToken: token,
        signingUp: !appraiser,
      }).then(data => {
        if (data.error) {
          return this.setState({
            loading: false
          });
        }
        // Signing up from AS
        if (data.result && appraiser) {
          const selectedAppraiser = parseInt(appraiser, 10);
          setPropCustomer(selectedAppraiser, 'selectedAppraiser');
          localStorage.setItem('selectedAppraiser', selectedAppraiser);
          if (redirect) {
            return this.handleRedirect(redirect);
          }
          this.redirectToDashboard();
        }
      });
    }
  }

  componentWillReceiveProps(nextProps) {
    const {setProp} = this.props;
    const {auth} = nextProps;
    // redirect
    if (nextProps.auth.get('autoLoginUpdateSuccess')) {
      this.redirectToDashboard();
      return;
    }

    const form = auth.get('autoLoginForm');
    const message = 'Your password do not match';
    if (form.get('password') !== form.get('password_confirm')) {
      setProp(message, 'autoLoginErrors', 'password');
      setProp(message, 'autoLoginErrors', 'password_confirm');
    } else if (auth.get(['autoLoginErrors', 'password']) === message) {
      removeProp('autoLoginErrors', 'password');
      removeProp('autoLoginErrors', 'password_confirm');
    }
  }

  componentWillUnmount() {
    this.props.setProp(Immutable.Map(), 'autoLoginForm');
    this.props.setProp(Immutable.Map(), 'autoLoginErrors');
  }

  redirectToDashboard() {
    this.props.setProp(false, 'signingUp');
    this.props.pushState(ORDERS_NEW_URL);
  }

  /**
   * Handle direct to a dynamic URL
   * @param url Url
   */
  handleRedirect(url) {
    const {setProp, pushState} = this.props;
    setProp(false, 'signingUp');
    pushState(url);
  }

  formSubmit() {
    if (!this.submitDisabled()) {
      const form = this.props.auth.get('autoLoginForm');
      this.props.autoLoginUpdate(this.props.auth.getIn(['user', 'id']), {
        email: form.get('email'),
        username: form.get('username'),
        password: form.get('password')
      });
    }
  }

  fieldChanged(field, event) {
    this.props.setProp(event.target.value, 'autoLoginForm', field);
  }

  submitDisabled() {
    const form = this.props.auth.get('autoLoginForm');
    return (
      this.props.auth.get('autoLoginUpdate') ||
      !form.get('username') ||
      !form.get('email') ||
      !form.get('password') ||
      !form.get('password_confirm') ||
      (form.get('password') !== form.get('password_confirm'))
    );
  }

  render() {
    const {auth} = this.props;
    // Auth Bootstrap errors
    const form = auth.get('autoLoginForm');
    const formErrors = auth.get('autoLoginErrors');
    const formDisabled = this.submitDisabled();

    return (
      <div className="container-fluid" style={{ paddingBottom: '10px' }}>
        {this.state.loading &&
          <div>
            <h3 className="text-center">Loading...</h3>
          </div>
        }
        {!this.state.loading &&
          <div>
            <h3 className="text-center">Welcome to ValuePad!</h3>
            <div style={{ padding: '0 10%' }}>
              <h4 className="text-center">
                From now on, you'll come to <a href="https://app.valuepad.com" className="link">app.valuepad.com</a> to manage all your orders from all your Appraisal Scope customers. ValuePad is about to make your life a little easier.
                <br />Please create your new Master password below.
              </h4>
            </div>
          </div>
        }
        {auth.get('loginSuccess') &&
          <div>
            <div className="row">
              <div className="col-md-6">
                <VpTextField
                  type="text"
                  name="username"
                  value={form.get('username')}
                  label="Username"
                  placeholder="Enter a username"
                  onChange={this.fieldChanged.bind(this, 'username')}
                  enterFunction={::this.formSubmit}
                  error={formErrors.get('username')}
                />
              </div>
              <div className="col-md-6">
                <VpTextField
                  type="text"
                  name="email"
                  value={form.get('email')}
                  label="Email Address"
                  placeholder="Enter your email"
                  onChange={this.fieldChanged.bind(this, 'email')}
                  enterFunction={::this.formSubmit}
                  error={formErrors.get('email')}
                />
              </div>
            </div>
            <div className="row">
              <div className="col-md-6">
                <VpTextField
                  type="password"
                  name="password"
                  value={form.get('password')}
                  label="Password"
                  placeholder="Enter your new password"
                  onChange={this.fieldChanged.bind(this, 'password')}
                  enterFunction={::this.formSubmit}
                  error={formErrors.get('password')}
                />
              </div>
              <div className="col-md-6">
                <VpTextField
                  type="password"
                  name="password_confirm"
                  value={form.get('password_confirm')}
                  label="Confirm Password"
                  placeholder="Confirm your new password"
                  onChange={this.fieldChanged.bind(this, 'password_confirm')}
                  enterFunction={::this.formSubmit}
                  error={formErrors.get('password_confirm')}
                />
              </div>
            </div>
            <Void pixels={10}/>
            <div className="text-center">
              <ActionButton
                type="submit"
                onClick={::this.formSubmit}
                disabled={formDisabled}
              />
            </div>
          </div>
        }
        {auth.getIn(['formErrors', 'autoLoginToken']) &&
          <div className="error-display text-center">The token you have provided is invalid or has expired.</div>
        }
      </div>
    );
  }
}
