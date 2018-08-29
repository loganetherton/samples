import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import {Link} from 'react-router';
import {
  login,
  //removeSession,
  changeTabHomePage,
  LOGIN_TAB,
  AMC_TAB,
  APPRAISER_TAB
} from 'redux/modules/auth';
import {
  LOGIN_URL,
  AMC_SIGNUP_URL,
  APPRAISER_SIGNUP_URL,
  APPRAISER_SIGNUP_PROFILE_URL,
  APPRAISER_SIGNUP_COMPANY_URL,
  APPRAISER_SIGNUP_EO_URL,
  APPRAISER_SIGNUP_CERTIFICATIONS_URL,
  APPRAISER_SIGNUP_SAMPLES_URL,
  APPRAISER_SIGNUP_LICENSES_URL,
  APPRAISER_SIGNUP_FEES_URL,
  APPRAISER_SIGNUP_ACH_URL,
  APPRAISER_SIGNUP_TERMS_URL,
  ORDERS_NEW_URL,
  FORGOT_PASSWORD_URL,
  RESET_PASSWORD_URL,
  LOGIN_CONNECT_URL,
} from 'redux/modules/urls';
import {
  backToStepOne,
  setDefault
} from 'redux/modules/appraiser';
import {isFeatureEnabled} from 'redux/modules/features';
import Immutable from 'immutable';
import {push} from 'redux-router';
import pureRender from 'pure-render-decorator';
import classNames from 'classnames';

// Tabs for auth
let tabs = Immutable.fromJS([
  {value: 1, name: 'Sign In', link: LOGIN_URL},
  // {value: 2, name: 'AMC Sign Up', link: AMC_SIGNUP_URL},
  // {value: 3, name: 'Appraiser Sign Up', link: APPRAISER_SIGNUP_URL}
]);

@pureRender
@connect(
  state => ({
    auth: state.auth,
    amc: state.amc,
    appraiser: state.appraiser,
    w9: state.w9,
    coverage: state.coverage,
    features: state.features
  }),
  {
    login,
    pushState: push,
    backToStepOne,
    changeTabHomePage,
    setDefault
  })
export default class SignUp extends Component {

  static propTypes = {
    children: PropTypes.object,
    /**
     * Auth
     */
    // Authenticated user
    user: PropTypes.object,
    // Auth
    auth: PropTypes.instanceOf(Immutable.Map),
    // Push to a new state on login/registration
    pushState: PropTypes.func.isRequired,
    // Current location
    location: PropTypes.object.isRequired,
    // Change tabs
    changeTabHomePage: PropTypes.func.isRequired,
    // Login
    login: PropTypes.func.isRequired,
    /**
     * Appraiser
     */
    // Appraiser reducer
    appraiser: PropTypes.object.isRequired,
    // Return appraiser to step one
    backToStepOne: PropTypes.func.isRequired,
    // Set a default value for appraiser
    setDefault: PropTypes.func.isRequired,
    /**
     * W9
     */
    w9: PropTypes.instanceOf(Immutable.Map),
    /**
     * Coverage
     */
    coverage: PropTypes.instanceOf(Immutable.Map),
    // Features
    features: PropTypes.object.isRequired
  };

  /**
   * If loading here with a user, redirect to orders
   */
  componentDidMount() {
    const pathName = this.props.location.pathname;
    // Make sure that the user isn't already logged in
    if (this.checkAlreadyLoggedIn(this.props)) {
      return this.props.pushState(`${ORDERS_NEW_URL}`);
    }
    // Update auth tab on first load
    this.updateAuthTab(pathName);
    // Keep reference to sign up container
    if (this.refs && this.refs['sign-up-container']) {
      this.setState({
        signUpContainerWidth: this.refs['sign-up-container'].offsetWidth
      });
    }

    const {features} = this.props;

    if (isFeatureEnabled(features.get('isDevelopment'), window)) {
      if (!tabs.find(v => v.get('value') === 3)) {
        tabs = tabs.push(Immutable.fromJS({value: 3, name: 'Appraiser Sign Up', link: APPRAISER_SIGNUP_URL}));
      }
    }
  }

  /**
   * If we have a user, redirect to orders
   */
  componentWillReceiveProps(nextProps) {
    const {location, pushState} = this.props;
    // Make sure user isn't already logged in and trying to visit auth states
    if (this.checkAlreadyLoggedIn(nextProps)) {
      const redirect = location.query && location.query.redirect ? location.query.redirect : '';
      if (redirect) {
        return pushState(redirect);
      } else {
        return pushState(`${ORDERS_NEW_URL}`);
      }
    }
    // Update auth tab
    if (this.props.location.pathname !== nextProps.location.pathname) {
      this.updateAuthTab(nextProps.location.pathname);
    }
  }

  /**
   * Make sure that the current user isn't already logged in
   */
  checkAlreadyLoggedIn(props) {
    if (/\/(amc-sign-up|appraiser-sign-up|login)/.test(this.props.location.pathname)) {
      return props.auth.get('user') && !props.auth.get('signingUp');
    }
  }

  /**
   * Update auth tab to display the right one
   */
  updateAuthTab(pathName) {
    let tab;
    // Set tab on load
    switch (pathName) {
      case AMC_SIGNUP_URL:
        tab = AMC_TAB;
        break;
      case APPRAISER_SIGNUP_URL:
      case APPRAISER_SIGNUP_PROFILE_URL:
      case APPRAISER_SIGNUP_COMPANY_URL:
      case APPRAISER_SIGNUP_EO_URL:
      case APPRAISER_SIGNUP_CERTIFICATIONS_URL:
      case APPRAISER_SIGNUP_SAMPLES_URL:
      case APPRAISER_SIGNUP_LICENSES_URL:
      case APPRAISER_SIGNUP_FEES_URL:
      case APPRAISER_SIGNUP_ACH_URL:
      case APPRAISER_SIGNUP_TERMS_URL:
        tab = APPRAISER_TAB;
        break;
      case FORGOT_PASSWORD_URL:
      case RESET_PASSWORD_URL:
      case LOGIN_CONNECT_URL:
        tab = null;
        break;
      default:
        tab = LOGIN_TAB;
    }
    this.props.changeTabHomePage(tab);
  }

  /**
   * Select tab
   */
  selectTab(tab, event) {
    const {auth, changeTabHomePage} = this.props;
    // Don't continue if the user is already on this tab
    if (tab === auth.get('authTab') || isNaN(tab)) {
      event.preventDefault();
      return;
    }
    // Change tab state
    changeTabHomePage(tab);
  }

  render() {
    const styles = require('./SignUp.scss');
    // Sign up container
    const signUpContainerWidth = this.state ? this.state.signUpContainerWidth : null;
    // Store reference to sign up container
    const childrenWithProps = React.Children.map(this.props.children, child => {
      return React.cloneElement(child, { signUpContainerWidth });
    });
    const {auth} = this.props;
    // Auth tab
    const authTab = auth.get('authTab');
    // Number of columns
    const numColumns = 12 / tabs.count();
    // When not logged in
    const signUp = (
      <div className="container">
        <div className="row">
          <div className="col-md-10 col-md-offset-1">
            <div className={classNames('panel', 'panel-default', styles['panel-auth'])}>
              <div className={classNames('panel-body', styles['panel-body'])} ref="sign-up-container">
                {tabs.count() > 1 && tabs.map((tab, index) => {
                  return (
                    <div
                      className={classNames(`btn-tab-col col-md-${numColumns}`, {'btn-tab-active large-border': authTab === tab.get('value')})}
                      key={index}
                      role="button">
                      <div className={classNames('btn-tab', {'btn-tab-active': authTab === tab.get('value')})} style={{ padding: '0 !important' }}>
                        <Link to={tab.get('link')} className="btn-tab-text" onClick={this.selectTab.bind(this, tab.get('value'))}>
                          <div className="text-center">
                            <div>{tab.get('name')}</div>
                          </div>
                        </Link>
                      </div>
                    </div>
                  );
                })}
              </div>
              {childrenWithProps}
            </div>
          </div>
        </div>
      </div>
    );
    return (
      <div>
        {signUp}
      </div>
    );
  }
}
