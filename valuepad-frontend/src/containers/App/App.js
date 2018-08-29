import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import DocumentMeta from 'react-document-meta';
import { push, replace } from 'redux-router';
import pureRender from 'pure-render-decorator';
// Listen for mui events
import injectTapEventPlugin from 'react-tap-event-plugin';
injectTapEventPlugin();
import MyRawTheme from '../../theme/mui-theme';
import {Dialog} from 'material-ui';
import getMuiTheme from 'material-ui/styles/getMuiTheme';
import MuiThemeProvider from 'material-ui/styles/MuiThemeProvider';
import Immutable from 'immutable';
import moment from 'moment';

import {
  validateUser,
  removeSession,
  refreshSession,
  logout,
  hideInitialDisplay,
  setProp as setPropAuth
} from 'redux/modules/auth';
import {
  sendIssue,
  setProp,
  requestFeature
} from 'redux/modules/settings';
import {setProp as setPropOrders} from 'redux/modules/orders';
import {setProp as setPropAccounting} from 'redux/modules/accounting';
import {setProp as setPropCustomer} from 'redux/modules/customer';
import {
  ROOT_URL,
  LOGIN_URL,
  AMC_SIGNUP_URL,
  APPRAISER_SIGNUP_URL,
  APPRAISER_SIGNUP_W9_URL,
  APPRAISER_SIGNUP_COVERAGE_URL,
  ORDERS_NEW_URL,
  FORGOT_PASSWORD_URL,
  RESET_PASSWORD_URL,
  LOGIN_CONNECT_URL,
} from 'redux/modules/urls';
import {
  addFeature
} from 'redux/modules/features';
import {getMessageTotals, setProp as messagesSetProp} from 'redux/modules/messages';
import {incrementCounter, setProp as notificationsSetProp} from 'redux/modules/notifications';
import {getPendingInvitationsTotal, getCompanyInvitations} from 'redux/modules/invitations';
import {setProp as customerSetProp, searchAppraisers, selectAppraiser} from 'redux/modules/customer';
import {getCompanies, getManager} from 'redux/modules/company';
import config from '../../config';
import {PanelNav, ReportIssue, RequestFeature, AuthNav, WelcomeMessage, ActionButton, UserGuide} from 'components';

import {devPath} from 'helpers/ApiClient';

const muiTheme = getMuiTheme(MyRawTheme, { userAgent: 'all' });

@pureRender
@connect(
  state => ({
    auth: state.auth,
    customer: state.customer,
    features: state.features,
    messages: state.messages,
    notifications: state.notifications,
    settings: state.settings,
    invitations: state.invitations
  }),
  {
    pushState: push,
    replaceState: replace,
    validateUser,
    refreshSession,
    removeSession,
    sendIssue,
    logout,
    setProp,
    requestFeature,
    setPropOrders,
    setPropAccounting,
    getMessageTotals,
    incrementCounter,
    hideInitialDisplay,
    addFeature,
    setPropAuth,
    customerSetProp,
    searchAppraisers,
    selectAppraiser,
    messagesSetProp,
    notificationsSetProp,
    getPendingInvitationsTotal,
    getCompanyInvitations,
    getCompanies,
    setPropCustomer,
    getManager
  })
export default class App extends Component {
  static propTypes = {
    children: PropTypes.object.isRequired,
    auth: PropTypes.object,
    // messages
    messages: PropTypes.object,
    // notifications
    notifications: PropTypes.object,
    // Features reducer
    features: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Customer reducer
    customer: PropTypes.instanceOf(Immutable.Map).isRequired,
    // get the totals for messages
    getMessageTotals: PropTypes.func,
    // increment the counter
    incrementCounter: PropTypes.func,
    // Settings
    settings: PropTypes.instanceOf(Immutable.Map),
    pushState: PropTypes.func.isRequired,
    replaceState: PropTypes.func.isRequired,
    location: PropTypes.object,
    validateUser: PropTypes.func.isRequired,
    // Refresh a session that has expired
    refreshSession: PropTypes.func.isRequired,
    // Remove session
    removeSession: PropTypes.func.isRequired,
    // Send issue
    sendIssue: PropTypes.func.isRequired,
    // Request feature
    requestFeature: PropTypes.func.isRequired,
    // Logout
    logout: PropTypes.func.isRequired,
    // Set a settings reducer value
    setProp: PropTypes.func.isRequired,
    // Set prop for orders
    setPropOrders: PropTypes.func.isRequired,
    // Set prop for accounting
    setPropAccounting: PropTypes.func.isRequired,
    // Set prop in auth reducer
    setPropAuth: PropTypes.func.isRequired,
    // Hide initial display
    hideInitialDisplay: PropTypes.func.isRequired,
    // Adds a feature checker
    addFeature: PropTypes.func.isRequired,
    // Set prop in appraiser search
    customerSetProp: PropTypes.func.isRequired,
    // Search for appraisers
    searchAppraisers: PropTypes.func.isRequired,
    // Select an appraiser for the customer view
    selectAppraiser: PropTypes.func.isRequired,
    // Set prop for messages and notifications
    notificationsSetProp: PropTypes.func.isRequired,
    messagesSetProp: PropTypes.func.isRequired,
    // Invitations reducer
    invitations: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Get the total number of pending invitations
    getPendingInvitationsTotal: PropTypes.func.isRequired,
    // Get total number of pending company invitations
    getCompanyInvitations: PropTypes.func.isRequired,
    // Get companies to see if user is company admin
    getCompanies: PropTypes.func.isRequired,
    // Set prop for customer reducer
    setPropCustomer: PropTypes.func.isRequired,
    // Get manager on load
    getManager: PropTypes.func.isRequired
  };

  static contextTypes = {
    store: PropTypes.object.isRequired
  };

  // Pass down user
  static childContextTypes = {
    user: PropTypes.object,
    pusher: PropTypes.object,
    muiTheme: React.PropTypes.object
  };

  /**
   * Start with request feature, report issue closed
   */
  constructor(props) {
    super(props);

    this.state = {
      reportIssueOpen: false,
      requestFeatureOpen: false,
      showSessionExpires: false,
      userGuideOpen: false
    };

    this.pusher = null;

    this.closeUserGuide = this.toggleUserGuide.bind(this, false);
    this.openUserGuide = this.toggleUserGuide.bind(this, true);
  }

  // Pass user in context
  getChildContext() {
    const {auth, customer} = this.props;
    const user = auth.get('user');
    if (user) {
      const userType = user.get('type');
      const previouslySelected = this.pusher && this.pusher.selectedAppraiser ? this.pusher.selectedAppraiser : null;
      if (!this.pusher || (userType === 'customer' && previouslySelected !== customer.get('selectedAppraiser'))) {
        // require this here to prevent ssr from failing :)
        const PusherClient = require('pusher-js');

        // pusher key
        const pusherKey = (window.location.hostname === 'app.valuepad.com') ? 'a38e0d14ea464c133b86' : '283dfe25c7398b52c5fc';

        // init pusher with the auth endpoints
        const pusher = new PusherClient(pusherKey, {
          auth: {
            headers: {
              token: localStorage.getItem('token')
            }
          },
          authEndpoint: `${devPath}/live/auth`
        });

        let channel;
        // listen on the users channel
        if (user.get('type') !== 'customer') {
          channel = pusher.subscribe(`private-user-${user.get('id')}`);
        } else if (customer.get('selectedAppraiser')) {
          channel = pusher.subscribe(`private-user-${user.get('id')}-as-${customer.get('selectedAppraiser')}`);
          if (previouslySelected) {
            pusher.unsubscribe(`private-user-${user.get('id')}-as-${previouslySelected}`);
          }
        }

        // expose pusher and the channel to children
        this.pusher = {
          pusher,
          channel,
          selectedAppraiser: customer.get('selectedAppraiser') ? customer.get('selectedAppraiser') : previouslySelected
        };
      }
    }

    return {
      user: user,
      pusher: this.pusher,
    };
  }

  componentWillMount() {
    const {addFeature} = this.props;
    if (typeof window === 'undefined') {
      return;
    }
    // Add feature checkers
    addFeature('isDevelopment', window => {
      return window && window.isDevelopment;
    });
    addFeature('isStaging', window => {
      return window && window.isStaging;
    });

    addFeature('amc', (user, isDevelopment) => {
      return user && user.get('username') && isDevelopment;
    });
  }

  /**
   * Check to see if we have a token before mounting
   */
  componentDidMount() {
    const {auth, validateUser, replaceState, location, setPropCustomer} = this.props;

    // Check if we have a token
    if (localStorage.getItem('token') && localStorage.getItem('sessionId')) {
      // Refresh token
      if (!auth.get('validating')) {
        validateUser({token: localStorage.getItem('token'), sessionId: localStorage.getItem('sessionId')})
        .then(data => {
          // Auto select customer on page refresh
          if (data.result && data.result.user && data.result.user.type === 'customer') {
            const selectedAppraiser = localStorage.getItem('selectedAppraiser');
            if (selectedAppraiser) {
              setPropCustomer(parseInt(selectedAppraiser, 10), 'selectedAppraiser');
            }
          }
        });
      }
    } else {
      // Redirect to login if not in a login URL
      if ([ROOT_URL, LOGIN_URL, AMC_SIGNUP_URL, APPRAISER_SIGNUP_URL, APPRAISER_SIGNUP_W9_URL,
          APPRAISER_SIGNUP_COVERAGE_URL, FORGOT_PASSWORD_URL, RESET_PASSWORD_URL, LOGIN_CONNECT_URL].indexOf(location.pathname) === -1) {
        replaceState(LOGIN_URL + '?redirect=' + location.pathname);
      }
    }
  }

  /**
   * Watch for user on register, login
   * @param nextProps
   */
  componentWillReceiveProps(nextProps) {
    const {auth, pushState, replaceState, removeSession, location, setPropAuth, getCompanies, getManager} = this.props;
    const {auth: nextAuth} = nextProps;
    // Redirect to orders on normal login
    if (!auth.get('loginSuccess') && nextAuth.get('loginSuccess') && !nextAuth.get('signingUp')) {
      const redirect = location.query && location.query.redirect ? location.query.redirect : '';
      if (redirect) {
        pushState(redirect);
      } else {
        pushState(`${ORDERS_NEW_URL}`);
      }
    }
    // Removing user
    if (auth.get('user') && !nextAuth.get('user')) {
      removeSession();
      // Go back to login
      pushState(LOGIN_URL);
    }
    // Check for features, keep logged in
    if (!auth.get('user') && nextAuth.get('user')) {
      // Get companies to see if user is admin
      // @todo This is causing a flashing before company shows up in the nav bar. See if I can reduce that a bit
      const user = nextAuth.get('user');

      if (user.get('type') !== 'manager') {
        getCompanies(user);
      } else {
        getManager(user.get('id'));
      }
      // Auto-refresh session before it expires
      if (nextAuth.get('keepLoggedIn')) {
        this.startAutoRefreshClock.call(this);
      } else {
        // Set last action to now
        setPropAuth(Date.now(), 'lastActionTime');
        this.startSessionClock.call(this);
      }
    }
    // If validation failed, remove tokens and return to login
    if (auth.get('validating') && nextAuth.get('validateSuccess') === false) {
      removeSession();
      // Return to login
      pushState(LOGIN_URL);
    }
    // If session required refresh
    if (!auth.get('sessionRequiresRefresh') && nextAuth.get('sessionRequiresRefresh')) {
      refreshSession();
    }

    // Check URL, redirect to login if necessary
    if (location.pathname === '/' || nextProps.location.pathname === '/') {
      replaceState(LOGIN_URL);
    }
  }

  /**
   * Start the session auto-logout clock
   */
  startSessionClock() {
    const expiresMs = moment().add(1, 'hours').diff(moment());
    if (this.sessionClock) {
      clearTimeout(this.sessionClock);
    }
    // Set to expire in 1 hour
    this.sessionClock = setTimeout(() => {
      this.sessionClock = null;
      this.checkIfSessionExpired.call(this);
    }, expiresMs);
  }

  /**
   * Start clock to auto-refresh session
   */
  startAutoRefreshClock() {
    const expiresMs = moment().add(23, 'hours').diff(moment());
    if (this.autoRefreshClock) {
      clearTimeout(this.autoRefreshClock);
    }
    // Refresh session, then restart clock
    this.autoRefreshClock = setTimeout(() => {
      this.autoRefreshClock = null;
      this.props.refreshSession();
      this.startAutoRefreshClock();
    }, expiresMs);
  }

  /**
   * Reset inactivity timer
   */
  resetSessionClock() {
    const {setPropAuth} = this.props;
    clearTimeout(this.autoLogoutClock);
    // Set last activity to now
    setPropAuth(moment(), 'lastActionTime');
    this.closeSessionExpired.call(this);
    // Restart clock
    this.startSessionClock();
  }

  closeSessionExpired() {
    this.setState({
      showSessionExpires: false
    });
  }

  /**
   * Close session expired dialog and logout
   */
  closeSessionExpiredAndLogout() {
    this.closeSessionExpired.call(this);
    this.props.logout();
  }

  /**
   * See if the current session has expired
   */
  checkIfSessionExpired() {
    const {auth} = this.props;
    const lastActionTime = moment(auth.get('lastActionTime'));
    const now = moment();
    // Indeed been inactive within the last 59 minutes
    if (now.diff(lastActionTime) > now.diff(moment().subtract(55, 'minutes'))) {
      this.setState({
        showSessionExpires: true
      });
      // One minute until auto logout
      this.autoLogoutClock = setTimeout(() => {
        this.closeSessionExpiredAndLogout.call(this);
      }, 60000);
    } else {
      this.startSessionClock.call(this);
    }
  }

  /**
   * Open or close report issue dialog
   * @param open Whether to open or close report issue dialog
   */
  toggleReportIssue(open) {
    this.props.setProp('', 'reportIssueValue');
    this.setState({
      reportIssueOpen: open
    });
  }

  /**
   * Change report an issue value
   */
  changeReportIssueText(event) {
    this.props.setProp(event.target.value, 'reportIssueValue');
  }

  /**
   * Submit report an issue dialog
   */
  sendIssue() {
    const {sendIssue, settings} = this.props;
    sendIssue(settings.get('reportIssueValue'));
  }

  /**
   * Open/close report issue dialog
   * @param open Whether to open or close request issue dialog
   */
  toggleRequestFeature(open) {
    this.props.setProp('', 'requestFeatureValue');
    this.setState({
      requestFeatureOpen: open
    });
  }

  /**
   * Change request feature value
   */
  changeRequestFeatureText(event) {
    this.props.setProp(event.target.value, 'requestFeatureValue');
  }

  /**
   * Submit request a feature dialog
   */
  sendFeature() {
    const {requestFeature, settings} = this.props;
    requestFeature(settings.get('requestFeatureValue'));
  }

  /**
   * Toggles the user guide modal
   *
   * @param {bool} open
   */
  toggleUserGuide(open) {
    this.setState({
      userGuideOpen: open
    });
  }

  render() {
    const {
      auth,
      customer,
      logout,
      location,
      settings,
      pushState,
      setPropOrders,
      setPropAccounting,
      messages,
      notifications,
      getMessageTotals,
      incrementCounter,
      hideInitialDisplay,
      customerSetProp,
      searchAppraisers,
      selectAppraiser,
      messagesSetProp,
      notificationsSetProp,
      invitations,
      getPendingInvitationsTotal,
      getCompanyInvitations
    } = this.props;
    const {reportIssueOpen, requestFeatureOpen, userGuideOpen, showSessionExpires} = this.state;
    const user = auth.get('user') && !auth.get('signingUp');
    const styles = require('./App.scss');

    return (
      <MuiThemeProvider muiTheme={muiTheme}>
        <div className={styles['app-container']}>
          <DocumentMeta {...config.app}/>

          {!user && <AuthNav /> }

          {user &&
           <PanelNav
            auth={auth}
            customer={customer}
            messages={messages}
            notifications={notifications}
            location={location}
            logout={logout}
            getMessageTotals={getMessageTotals}
            incrementCounter={incrementCounter}
            toggleReportIssue={::this.toggleReportIssue}
            toggleRequestFeature={::this.toggleRequestFeature}
            settings={settings}
            pushState={pushState}
            setPropOrders={setPropOrders}
            setPropAccounting={setPropAccounting}
            user={auth.get('user')}
            customerSetProp={customerSetProp}
            searchAppraisers={searchAppraisers}
            selectAppraiser={selectAppraiser}
            notificationsSetProp={notificationsSetProp}
            messagesSetProp={messagesSetProp}
            invitations={invitations}
            getPendingInvitationsTotal={getPendingInvitationsTotal}
            getCompanyInvitations={getCompanyInvitations}
            openUserGuide={this.openUserGuide}
            showCompanyNav={auth.getIn(['user', 'isBoss']) && auth.getIn(['user', 'type']) !== 'manager'}
           /> }

          {/*Report issue dialog*/}
          <ReportIssue
            sendIssue={::this.sendIssue}
            open={reportIssueOpen}
            closeDialog={this.toggleReportIssue.bind(this, false)}
            value={settings.get('reportIssueValue')}
            changeValue={::this.changeReportIssueText}
          />

          {/*Request feature dialog*/}
          <RequestFeature
            sendFeature={::this.sendFeature}
            open={requestFeatureOpen}
            closeDialog={this.toggleRequestFeature.bind(this, false)}
            value={settings.get('requestFeatureValue')}
            changeValue={::this.changeRequestFeatureText}
          />

          <UserGuide
            open={userGuideOpen}
            closeUserGuide={this.closeUserGuide}
          />

          <div className={'no-print container-fluid ' + styles['children-container']} style={{ paddingTop: 0 }}>
            {this.props.children}
          </div>

          {user &&
            <WelcomeMessage auth={auth} hideInitialDisplay={hideInitialDisplay} />
          }

          {auth.get('printWindow') &&
            <div id="print-window">{auth.get('printWindow')}</div>
          }
          <Dialog
            open={showSessionExpires}
            onRequestClose={::this.resetSessionClock}
            actions={[
              <ActionButton
                type="submit"
                text="Stay Active"
                onClick={::this.resetSessionClock}
                style={{marginLeft: '5px'}}
              />,
              <ActionButton
                type="cancel"
                text="Logout"
                onClick={::this.closeSessionExpiredAndLogout}
                style={{marginLeft: '5px'}}
              />
            ]}
            modal
            title="Your session is about to expire"
          >
            <p>Your session is about to be closed due to inactivity. You can logout or choose to remain active.</p>
          </Dialog>
        </div>
      </MuiThemeProvider>
    );
  }
}
