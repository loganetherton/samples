import React from 'react';
import {IndexRoute, Route} from 'react-router';
import {
  Accounting,
  App,
  AmcSignUp,
  Company,
  Coverage,
  RecoverAccount,
  Invitations,
  Invoices,
  JobTypes,
  Login,
  LoginConnect,
  Main,
  NotFound,
  NotificationLog,
  Orders,
  Profile,
  ResetPassword,
  Schedule,
  Settings,
  SignUp,
} from 'containers';

import {AppraiserSignUp} from 'components';

import {APPRAISER_SIGNUP_URL} from 'redux/modules/auth';
import {ORDERS_NEW_URL} from 'redux/modules/orders';
import {TROUBLE_SIGNING_IN} from 'redux/modules/urls';

/*eslint-disable */
export default (store) => {
  // Transition away from protected state
  function replaceUrl(url) {
    return (nextState, replaceState, cb) => {
      const { auth } = store.getState();
      if (!auth.get('user')) {
        replaceState(null, url);
      }
      cb();
    };
  }

  /**
   * Prevent the user from transitioning to an auth state after logging in
   * @returns {Function}
   */
  function preventAuthAccess() {
    return (nextState, replaceState, cb) => {
      const { auth } = store.getState();
      if (auth.get('user') && !auth.get('signingUp')) {
        replaceState(null, `${ORDERS_NEW_URL}`);
      }
      cb();
    };
  }

  // Disallow access to protected states during appraiser sign up when not logged in
  const requireLoginAppraiserSignUp = replaceUrl(APPRAISER_SIGNUP_URL);

  /**
   * Please keep routes in alphabetical order
   */
  return (
    <Route path="/" component={App}>
      { /* Sign up/login routes */ }
      <IndexRoute component={SignUp}/>

      {/*<Route component={SignUp} onEnter={preventAuthAccess}>*/}
      <Route component={SignUp}>
        <Route path="login" component={Login}/>
        {/*<Route path="forgot-password" component={ForgotPassword}/>*/}
        <Route path="reset-password" component={ResetPassword}/>
        <Route path={TROUBLE_SIGNING_IN} component={RecoverAccount}/>
        <Route path="amc-sign-up" component={AmcSignUp}/>
        <Route path="appraiser-sign-up" component={AppraiserSignUp}>
          {/*<Route onEnter={requireLoginAppraiserSignUp}>*/}
          <Route>
            <Route path="profile"/>
            <Route path="company"/>
            <Route path="eo"/>
            <Route path="certification"/>
            <Route path="samples"/>
            <Route path="licenses"/>
            <Route path="fees"/>
            <Route path="terms"/>
            <Route path="ach"/>
          </Route>
        </Route>
        <Route path="connect" component={LoginConnect}/>
      </Route>

      { /* Routes requiring login */ }
      <Route>
        <Route component={Main}>
          <Route path="orders/" component={Orders}>
            <Route path=":type"/>
            <Route path="details/:orderId">
              <Route path=":tab"/>
            </Route>
          </Route>
          <Route path="company" component={Company}/>
          <Route path="coverage" component={Coverage}/>
          <Route path="profile" component={Profile}>
            <Route path="profile"/>
            <Route path="company"/>
            <Route path="eo"/>
            <Route path="certification"/>
            <Route path="samples"/>
            <Route path="appraiser-company"/>
          </Route>
          <Route path="products" component={JobTypes}/>
          <Route path="schedule" component={Schedule}/>
          <Route path="invitations" component={Invitations}/>
          <Route path="accounting/" component={Accounting}>
            <Route path=":type"/>
          </Route>
          <Route path="invoices" component={Invoices} />
          <Route path="settings" component={Settings}/>
          <Route path="notifications" component={NotificationLog}/>
        </Route>
      </Route>

      { /* Catch all route */ }
      <Route path="*" component={NotFound} status={404}/>
    </Route>
  );
};

/*eslint-enable */
