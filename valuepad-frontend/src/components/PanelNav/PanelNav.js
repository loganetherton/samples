/**
 * The panel screens navbar.
 */
import React, {Component, PropTypes} from 'react';
import {Link} from 'react-router';
import classNames from 'classnames';
import Immutable from 'immutable';
import _ from 'lodash';
import {
  IconMenu,
  MenuItem,
  Divider,
  Popover
} from 'material-ui';
import {NotificationLog, Messages} from 'containers';
import {SwitchAppraiser, VpAvatar} from 'components';
import {
  ORDERS_NEW_URL,
  JOB_TYPE_URL,
  COVERAGE_URL,
  INVITATIONS_URL,
  ACCOUNTING_UNPAID_URL,
  SETTINGS_URL,
  PROFILE_PROFILE_URL,
  INVOICES_URL,
  COMPANY_URL
} from 'redux/modules/urls';


import {initialSearchState, initialPageState} from 'redux/modules/accounting';
// Imported here because it's used in createActive()
const styles = require('./PanelNav.scss');

export default class PanelNav extends Component {
  static propTypes = {
    logout: PropTypes.func.isRequired,
    location: PropTypes.object.isRequired,
    // Customer
    customer: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Open/close the report issue dialog
    toggleReportIssue: PropTypes.func.isRequired,
    // Open/close request feature dialog
    toggleRequestFeature: PropTypes.func.isRequired,
    // Settings
    settings: PropTypes.instanceOf(Immutable.Map),
    // Push state
    pushState: PropTypes.func.isRequired,
    // Set props in orders
    setPropOrders: PropTypes.func.isRequired,
    // Set props in accounting
    setPropAccounting: PropTypes.func.isRequired,
    // auth
    auth: PropTypes.object.isRequired,
    // messages
    messages: PropTypes.object.isRequired,
    // notifications
    notifications: PropTypes.object.isRequired,
    // get the totals for messages
    getMessageTotals: PropTypes.func.isRequired,
    // increment the counter
    incrementCounter: PropTypes.func.isRequired,
    // Current user
    user: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Set prop in appraiser search
    customerSetProp: PropTypes.func.isRequired,
    // Search for appraisers
    searchAppraisers: PropTypes.func.isRequired,
    // Select an appraiser for customer view
    selectAppraiser: PropTypes.func.isRequired,
    // Set prop for messages and notifications
    notificationsSetProp: PropTypes.func.isRequired,
    messagesSetProp: PropTypes.func.isRequired,
    // Invitations reducer
    invitations: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Get total number of pending invitations
    getPendingInvitationsTotal: PropTypes.func.isRequired,
    // Get total number of pending company invitations
    getCompanyInvitations: PropTypes.func.isRequired,
    // Open user guide modal
    openUserGuide: PropTypes.func.isRequired,
    // Show company nav item
    showCompanyNav: PropTypes.bool
  };

  // Receive user
  static contextTypes = {
    user: PropTypes.object,
    pusher: PropTypes.object,
  };

  constructor(props) {
    super(props);
    this.state = {
      open: false,
      messagesOpen: false,
      notificationsOpen: false,
      switchAppraiserOpen: false
    };
  }

  componentWillMount() {
    // listen for pusher events
    this.pusherBind();
  }

  componentWillUnmount() {
    // remove pusher subscriptions
    this.pusherUnbind();
  }

  componentDidMount() {
    const {auth, getPendingInvitationsTotal, getCompanyInvitations} = this.props;
    const user = auth.get('user');
    if (user.get('type') === 'appraiser') {
      const userId = user.get('id');
      getPendingInvitationsTotal(userId)
      .then(res => {
        if (!res.error) {
          getCompanyInvitations(userId, true);
        }
      });
    }
  }

  pusherBind() {
    const {channel} = this.context.pusher;
    if (channel) {
      // bind to tall of the events and attach the context
      channel.bind('order:send-message', this.messageCreated.bind(this, 'order:send-message'), this);
      channel.bind('order:create-log', this.notificationLogCreated.bind(this, 'order:create-log'), this);
    }
  }

  pusherUnbind() {
    const {channel} = this.context.pusher;
    if (channel) {
      // since we have a context we can remove all the events in that context
      channel.unbind(null, null, this);
    }
  }

  /**
   * Messages have been updated with pusher
   */
  messageCreated() {
    const {auth, getMessageTotals} = this.props;
    const user = auth.get('user');

    getMessageTotals(user);
  }

  /**
   * Logs have been updated with pusher
   */
  notificationLogCreated(event, data) {
    const {auth} = this.props;

    if (data.user.id !== auth.getIn(['user', 'id'])) {
      this.props.incrementCounter();
    }
  }

  /**
   * Handle dialog states after reporting issue or requesting feature
   * @param nextProps
   */
  componentWillReceiveProps(nextProps) {
    const {customer} = this.props;
    const {customer: nextCustomer} = nextProps;
    const selectedAppraiser = customer.get('selectedAppraiser');
    const nextSelectedAppraiser = nextCustomer.get('selectedAppraiser');

    // Issue sent
    if (!this.props.settings.get('sendIssueSuccess') && nextProps.settings.get('sendIssueSuccess')) {
      this.props.toggleReportIssue(false);
    }
    // Feature request sent
    if (!this.props.settings.get('requestFeatureSuccess') && nextProps.settings.get('requestFeatureSuccess')) {
      this.props.toggleRequestFeature(false);
    }

    // changed url so lets close
    if (this.props.location !== nextProps.location) {
      this.closePopover('notifications');
      this.closePopover('messages');
    }

    if (selectedAppraiser !== nextSelectedAppraiser) {
      if (selectedAppraiser) {
        this.pusherUnbind();
      }

      this.pusherBind();
    }

    if (!nextSelectedAppraiser) {
      this.pusherUnbind();
    }
  }

  handleToggle = () => this.setState({open: !this.state.open});

  handleClose = () => this.setState({open: false});

  /**
   * Return if the matched path is active
   * @param path Current path
   * @param match Matching string
   * @returns {string}
   */
  createActive(path, match) {
    return path.indexOf(match) !== -1 ? styles.active : '';
  }

  /**
   * Log the user out from backend, remove token from frontend
   * @param event
   */
  handleLogout(event) {
    event.preventDefault();
    this.props.logout();
  }

  /**
   * Open popover for either notifications or messages
   * @param type Either notifications or messages
   * @param event Synthetic event
   */
  openPopover(type, event) {
    if (type === 'notifications') {
      this.setState({
        notificationsOpen: true,
        notificationNode: event.currentTarget
      });
    } else if (type === 'messages') {
      this.setState({
        messagesOpen: true,
        messagesNode: event.currentTarget
      });
    } else if (type === 'switchAppraiser') {
      this.setState({
        switchAppraiserOpen: true,
        switchAppraiserNode: event.currentTarget
      });
    }
  }

  /**
   * Close notifications or messages popover
   * @param type Notifications or messages
   */
  closePopover(type) {
    // Close notifications
    if (type === 'notifications') {
      this.setState({
        notificationsOpen: false
      });
      // Close messages
    } else if (type === 'messages') {
      this.setState({
        messagesOpen: false
      });
    } else if (type === 'switchAppraiser') {
      this.setState({
        switchAppraiserOpen: false
      });
    }
  }

  /**
   * Go to orders when clicking on the logo
   */
  goToOrders() {
    this.props.pushState(null, ORDERS_NEW_URL);
  }

  /**
   * Go to accounting section
   */
  goToAccounting() {
    const {pushState, setPropAccounting} = this.props;
    // Set tab and page
    setPropAccounting('unpaid', 'tab');
    setPropAccounting(Immutable.fromJS(initialPageState), 'page');
    // Set search and results
    setPropAccounting(Immutable.fromJS(initialSearchState), 'search');
    pushState(null, ACCOUNTING_UNPAID_URL);
  }

  render() {
    const {
      toggleReportIssue,
      toggleRequestFeature,
      setPropOrders,
      auth,
      customer,
      customerSetProp,
      searchAppraisers,
      selectAppraiser,
      messagesSetProp,
      notificationsSetProp,
      invitations,
      openUserGuide,
      showCompanyNav = false
    } = this.props;
    const userType = auth.getIn(['user', 'type']);
    // Create active classes
    const subMenu = ['orders', 'schedule', 'profile', 'products', 'coverage', 'accounting', 'invitations', 'settings'];
    // Remove invitations
    if (userType === 'amc') {
      subMenu.splice(subMenu.indexOf('invitations'), 1);
      subMenu.push('invoices');
    }
    if (showCompanyNav) {
      subMenu.push('company');
    }
    // Remove coverage for managers
    if (userType === 'manager') {
      subMenu.splice(subMenu.indexOf('coverage'), 1);
      subMenu.splice(subMenu.indexOf('products'), 1);
    }
    const menuClasses = {};
    const iconClasses = {};
    subMenu.forEach((menuItem) => {
      menuClasses[menuItem] = {
        active: this.props.location.pathname.indexOf(`/${menuItem}`) !== -1
      };
      iconClasses[menuItem] = {};
    });

    const iconStyle = {
      color: '#fff',
      display: 'block',
      fontSize: 30,
      fontWeight: 'none'
      //opacity: 0.5
    };

    const buttonStyle = {
      color: '#fff',
      minWidth: 120,
      margin: 0,
      paddingTop: 10,
      fontWeight: 'none',
      textTransform: 'capitalize',
      height: 69,
      borderTop: '2px solid #5F7076', // This may seem redundant but it's not! It eliminates icon/label move when setting a tab as active
      borderRadius: 0,
      lineHeight: 1.4
      //opacity: 0.5
    };

    const buttonStyleActive = {
      backgroundColor: '#48555b',
      borderBottom: '3px solid #17A1E5',
      opacity: 1,
      borderTopColor: '#48555b'
    };

    const iconStyleActive = {
      opacity: 1
    };

    const buttonStyleBlue = {
      backgroundColor: '#17A1E5',
      minWidth: 60,
      height: 67
      //opacity: 0.9
    };

    // Merge together all of the styles that we need for each button
    _.forEach(menuClasses, (menu, menuName) => {
      Object.assign(menu, buttonStyle);
      Object.assign(iconClasses[menuName], iconStyle);
      if (menu.active) {
        Object.assign(menu, buttonStyleActive);
        Object.assign(iconClasses[menuName], iconStyleActive);
      }
    });

    return (
      <div>
        <nav className={classNames('navbar', 'navbar-default', styles.navbar)}>
          <div className={classNames('container-fluid', styles['container-navbar'])}>
            <div className={classNames('navbar-header', styles['nav-light'])}>
              <span className={classNames('navbar-brand', styles['navbar-brand'])} onClick={::this.goToOrders}>
                <div style={{paddingLeft: 15, cursor: 'pointer'}}>
                  <img src="/img/logo.png"/>
                </div>
              </span>
            </div>
            <div id="navbar" className="collapse navbar-collapse text-center">
              <ul className={classNames('nav', 'navbar-nav', styles['nav-light'])}>
                <li style={{borderLeft: '0.1em solid rgba(255,255,255,0.25)', borderRight: '0.1em solid rgba(255,255,255,0.25)'}}>
                  <button
                    ref="notifications-nav"
                    style={Object.assign({}, buttonStyle, buttonStyleActive, buttonStyleBlue, {paddingTop: 0, border: 'none'})}
                    onClick={this.openPopover.bind(this, 'notifications')}
                  >
                    <div>
                      <i className="valuepad vp-notifications vp-head-icon">
                        <span className="path1" />
                        <span className="path2" />
                      </i>
                      <span>{this.props.notifications.get('counter')}</span>
                    </div>
                  </button>
                  <Popover
                    open={this.state.notificationsOpen}
                    anchorEl={this.state.notificationNode}
                    anchorOrigin={{horizontal: 'left', vertical: 'bottom'}}
                    targetOrigin={{horizontal: 'left', vertical: 'top'}}
                    onRequestClose={this.closePopover.bind(this, 'notifications')}
                    autoCloseWhenOffScreen={false}
                    canAutoPosition={false}
                    style={{width: 400, height: 525}}
                  >
                    <div>
                      <NotificationLog/>
                    </div>
                  </Popover>
                </li>
                <li>
                  <button
                    ref="notifications-nav"
                    style={Object.assign({}, buttonStyle, buttonStyleActive, buttonStyleBlue, {paddingTop: 0, border: 'none'})}
                    onClick={this.openPopover.bind(this, 'messages')}
                  >
                    <div>
                      <i className="valuepad vp-chat vp-head-icon">
                        <span className="path1" />
                        <span className="path2" />
                      </i>
                      <span>{this.props.messages.getIn(['totals', 'unread'], 0)}</span>
                    </div>
                  </button>
                  <Popover
                    open={this.state.messagesOpen}
                    anchorEl={this.state.messagesNode}
                    anchorOrigin={{horizontal: 'left', vertical: 'bottom'}}
                    targetOrigin={{horizontal: 'left', vertical: 'top'}}
                    onRequestClose={this.closePopover.bind(this, 'messages')}
                    autoCloseWhenOffScreen={false}
                    canAutoPosition={false}
                    style={{width: 400, height: 575}}
                  >
                    <div>
                      <Messages
                        closeMessages={this.closePopover.bind(this, 'messages')}
                        setPropOrders={setPropOrders}
                      />
                    </div>
                  </Popover>
                </li>
              </ul>
              <ul className={classNames('nav', 'navbar-nav', styles['nav-dark'])}>
                <li>
                  <Link
                    to={`${ORDERS_NEW_URL}`}
                    className={menuClasses.orders.active ? styles['active-section'] : 'main-nav'}
                    style={menuClasses.orders}
                  >
                    <div>
                      <i className="valuepad vp-orders vp-head-icon"></i>
                      <span>Orders</span>
                    </div>
                  </Link>
                </li>
                {userType !== 'manager' &&
                  <li>
                    <Link
                      to={JOB_TYPE_URL}
                      className={menuClasses.products.active ? styles['active-section'] : 'main-nav'}
                      style={menuClasses.products}
                    >
                      <div>
                        <i className="valuepad vp-jobtype vp-head-icon"></i>
                        <span>Products</span>
                      </div>
                    </Link>
                  </li>
                }
                {userType !== 'manager' &&
                  <li>
                    <Link
                      to={COVERAGE_URL}
                      className={menuClasses.coverage.active ? styles['active-section'] : 'main-nav'}
                      style={menuClasses.coverage}
                    >
                      <div>
                        <i className="valuepad vp-coverage vp-head-icon"></i>
                        <span>Coverage</span>
                      </div>
                    </Link>
                  </li>
                }
                {userType === 'appraiser' &&
                  <li>
                    {/* Wraps the badge inside Link so that navigation works even with higher z-index */}
                    {!!invitations.get('pendingInvitationsTotal') &&
                      <Link to={INVITATIONS_URL} style={{ padding: '0px' }}>
                        <VpAvatar size={20} backgroundColor="#f1504d" squareEdges className={styles['invite-badge']}>
                          <span>{invitations.get('pendingInvitationsTotal')}</span>
                        </VpAvatar>
                      </Link>
                    }
                    <Link
                      to={INVITATIONS_URL}
                      className={menuClasses.invitations.active ? styles['active-section'] : 'main-nav'}
                      style={menuClasses.invitations}
                    >
                      <div>
                        <i className="valuepad vp-invitations vp-head-icon"></i>
                        <span>Invitations</span>
                      </div>
                    </Link>
                  </li>
                }
                <li>
                  <Link
                    to={ACCOUNTING_UNPAID_URL}
                    className={menuClasses.accounting.active ? styles['active-section'] : 'main-nav'}
                    style={menuClasses.accounting}
                  >
                    <div>
                      <i className="valuepad vp-accounting vp-head-icon"></i>
                      <span>Accounting</span>
                    </div>
                  </Link>
                </li>
                {showCompanyNav &&
                  <li>
                    <Link
                      to={COMPANY_URL}
                      className={menuClasses.company.active ? styles['active-section'] : 'main-nav'}
                      style={menuClasses.company}
                    >
                      <div>
                        <i className="valuepad vp-accounting vp-head-icon"></i>
                        <span>Company</span>
                      </div>
                    </Link>
                  </li>
                }
                {userType === 'amc' &&
                 <li>
                   <Link
                     to={INVOICES_URL}
                     className={menuClasses.invoices.active ? styles['active-section'] : 'main-nav'}
                     style={menuClasses.invoices}
                   >
                     <div>
                       <i className="valuepad vp-invitations vp-head-icon"></i>
                       <span>Invoices</span>
                     </div>
                   </Link>
                 </li>
                }
              </ul>
              <ul className="nav navbar-nav navbar-right">
                <li>
                  <IconMenu anchorOrigin={{vertical: 'bottom', horizontal: 'middle'}} iconButtonElement={
                      <button className="main-nav" style={Object.assign({}, buttonStyle, {paddingTop: 0})}>
                        <div>
                          <i className="valuepad vp-help vp-head-icon"></i>
                          <span>Help</span>
                        </div>
                      </button>
                    }
                  >
                    <MenuItem primaryText="Report an Issue"
                              onTouchTap={toggleReportIssue.bind(this, true)}
                              leftIcon={<i className="material-icons">bug_report</i>}/>
                    <MenuItem primaryText="Request a New Feature"
                              onTouchTap={toggleRequestFeature.bind(this, true)}
                              leftIcon={<i className="material-icons">lightbulb_outline</i>}/>
                    <MenuItem primaryText="User Guide"
                              onTouchTap={openUserGuide}
                              leftIcon={<i className="material-icons">help</i>} />
                  </IconMenu>
                </li>
                {userType === 'customer' &&
                  <li>
                    <button
                      style={Object.assign({}, buttonStyle, buttonStyleActive, {
                        paddingTop: 0,
                        border: 'none',
                        backgroundColor: '#5f7076'
                      })}
                      onClick={this.openPopover.bind(this, 'switchAppraiser')}
                    >
                      <div>
                        <i className="valuepad vp-switch-appraiser vp-head-icon">
                        </i>
                        <span>Switch Appraiser</span>
                      </div>
                    </button>
                    <Popover
                      open={this.state.switchAppraiserOpen}
                      anchorEl={this.state.switchAppraiserNode}
                      anchorOrigin={{
                        horizontal: 'right',
                        vertical: 'bottom'
                      }}
                      targetOrigin={{
                        horizontal: 'right',
                        vertical: 'top'
                      }}
                      onRequestClose={this.closePopover.bind(this, 'switchAppraiser')}
                      autoCloseWhenOffScreen={false}
                      canAutoPosition={false}
                      style={{
                        width: 250
                      }}
                    >
                      <div>
                        <SwitchAppraiser
                          searchValue={customer.get('searchAppraiserVal')}
                          setProp={customerSetProp}
                          notificationsSetProp={notificationsSetProp}
                          messagesSetProp={messagesSetProp}
                          searchAppraisers={searchAppraisers}
                          results={customer.get('searchResults')}
                          selectAppraiser={selectAppraiser}
                          closePopover={this.closePopover.bind(this, 'switchAppraiser')}
                          customerId={auth.getIn(['user', 'id'])}
                        />
                      </div>
                    </Popover>
                  </li>
                }
                <li>
                  <IconMenu anchorOrigin={{vertical: 'bottom', horizontal: 'left'}} iconButtonElement={
                      <button className="main-nav" style={Object.assign({}, buttonStyle, {paddingTop: 0})}>
                        <div>
                          <i className="valuepad vp-account vp-head-icon"></i>
                          <span>My Account</span>
                        </div>
                      </button>
                    }
                  >
                    <MenuItem primaryText={`${auth.getIn(['user', 'username'])}`}
                              disabled
                              style={{color: 'rgba(0, 0, 0, 0.870588)'}}
                              leftIcon={<i className={classNames('valuepad', 'vp-account', 'vp-head-icon', styles['vp-move-head-icon'])}></i>}/>
                    <MenuItem primaryText="Settings"
                              containerElement={<Link to={SETTINGS_URL}/>}
                              leftIcon={<i className="material-icons">build</i>}/>
                    <MenuItem primaryText="Profile"
                              containerElement={<Link to={PROFILE_PROFILE_URL}/>}
                              leftIcon={<i className="material-icons">face</i>}/>
                    <Divider />
                    <MenuItem primaryText="Logout" onTouchTap={e => ::this.handleLogout(e)}
                              leftIcon={<i className="material-icons">exit_to_app</i>}/>
                  </IconMenu>
                </li>
              </ul>
            </div>
            {/* /.nav-collapse */}
          </div>
        </nav>
      </div>
    );
  }
}
