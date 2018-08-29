import React, {PropTypes, Component} from 'react';
import { connect } from 'react-redux';
import {Link} from 'react-router';
import {login, authFormChange} from 'redux/modules/auth';
import {
  TROUBLE_SIGNING_IN
} from 'redux/modules/urls';
import {
  ActionButton,
  LoginForm,
  Void,
} from 'components';
import {
  getFormErrorsImmutable
} from 'helpers/validation';
import {
  submitOnEnter
} from 'helpers/genericFunctions';

@connect(
  state => ({
    auth: state.auth
  }),
  {
    login,
    authFormChange
  })
export default class Login extends Component {
  static propTypes = {
    /**
     * Auth
     */
    // Auth reducer
    auth: PropTypes.object.isRequired,
    // onChange for auth form
    authFormChange: PropTypes.func.isRequired,
    // Login user
    login: PropTypes.func.isRequired
  };

  // Keep the user logged in or not
  constructor() {
    super();
    this.state = {
      keepLoggedIn: false
    };
  }

  /**
   * Handle login form input change
   * @param event
   */
  loginFormChange(event) {
    const {name, value} = event.target;
    this.props.authFormChange({name, value});
  }

  /**
   * Submit form
   */
  submitLoginForm() {
    const {auth, login} = this.props;
    const form = auth.get('form').toJS();
    login(form, this.state.keepLoggedIn);
  }

  /**
   * Keep user logged in
   */
  keepLoggedIn() {
    this.setState({
      keepLoggedIn: !this.state.keepLoggedIn
    });
  }

  render() {
    const {auth} = this.props;
    // Auth Bootstrap errors
    const form = auth.get('form');
    const formErrors = auth.get('formErrors');
    // Form props
    const errors = getFormErrorsImmutable(formErrors);

    return (
      <div>
        <Void pixels={15}/>

        <div className="container-fluid">
          <h4 className="text-center">Sign into your ValuePad account</h4>

          <LoginForm
            form={form}
            formChange={::this.loginFormChange}
            submitOnEnter={submitOnEnter.bind(this, ::this.submitLoginForm)}
            errors={errors}
            keepLoggedIn={::this.keepLoggedIn}
          />

          <Void pixels={25}/>

          <div className="row">
            <div className="col-md-4">
              {/*
              Don't have an account?<br/>
              <Link to={APPRAISER_SIGNUP_URL} className="link"><strong>SIGN UP TODAY</strong></Link>
              */}
            </div>
            <div className="col-md-4 text-center">
              <ActionButton type="submit" text="Sign In" onClick={::this.submitLoginForm} />
            </div>
            <div className="col-md-4 text-right">
              <Link to={TROUBLE_SIGNING_IN} className="link">
                <spen className="text-uppercase"><strong>Having problems signing in?</strong></spen>
              </Link>
            </div>
          </div>

          <Void pixels={15}/>
        </div>
      </div>
    );
  }
}
