import React, {Component, PropTypes} from 'react';
import {connect} from 'react-redux';
import Immutable from 'immutable';
import {Tabs, Tab} from 'material-ui';

import {PasswordSettings, ActionButton, EnableNotifications, Availability, CustomerSelector} from 'components';

import {updateManager, setProp, getNotifications, setNotifications} from 'redux/modules/company';
import {getAvailability, setAvailability, setProp as setPropSettings, getCustomers} from 'redux/modules/settings';

const minimumPasswordLength = 5;

const styles = {hide: {display: 'none'}};

@connect(
  state => ({
    company: state.company,
    settings: state.settings
  }),
  {
    getAvailability,
    updateManager,
    setProp,
    setPropSettings,
    getNotifications,
    setAvailability,
    setNotifications,
    getCustomers
  })
export default class ManagerSettings extends Component {
  static propTypes = {
    // Auth reducer
    auth: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Company reducer
    company: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Settings reducer
    settings: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Update manager
    updateManager: PropTypes.func.isRequired,
    // Set prop on company reducer
    setProp: PropTypes.func.isRequired,
    // Set prop settings reducer
    setPropSettings: PropTypes.func.isRequired,
    // Get notifications
    getNotifications: PropTypes.func.isRequired,
    // Set notifications
    setNotifications: PropTypes.func.isRequired,
    // Get availability
    getAvailability: PropTypes.func.isRequired,
    // Set availability
    setAvailability: PropTypes.func.isRequired,
    // Get customers
    getCustomers: PropTypes.func.isRequired
  };

  state = {
    activeTab: 1,
    // Mock the settings "reducer" that's consumed by PasswordSettings control
    // the password update dialog, in order to avoid passing large state tree
    // around as props.
    fakeSettings: Immutable.Map()
  };

  constructor(props) {
    super(props);

    this.changeTab = ::this.changeTab;
    this.submitPassword = ::this.submitPassword;
    this.submitPasswordDisabled = ::this.submitPasswordDisabled;
    this.changePassword = ::this.changePassword;
    this.selectCustomer = ::this.selectCustomer;
    this.removeCustomer = ::this.removeCustomer;
    this.saveNotificationSettings = ::this.saveNotificationSettings;
    this.getNotificationSettings = ::this.getNotificationSettings;
    this.changeCustomer = ::this.changeCustomer;
  }

  componentWillUnmount() {
    const {setProp} = this.props;
    // Clean up the state so that it doesn't mess with other components that
    // consume the same key/value pairs.
    setProp(Immutable.Map(), 'updateManager');
    setProp(Immutable.Map(), 'updateManagerErrors');
  }

  componentDidMount() {
    this.props.getCustomers(this.props.auth.getIn(['user']));
  }

  /**
   * Submit password change
   */
  submitPassword() {
    const {auth, company, updateManager} = this.props;
    this.setState({fakeSettings: this.state.fakeSettings.remove('passwordResetSuccess')});

    updateManager(auth.getIn(['user', 'id']), company.get('updateManager')).then(res => {
      this.setState({fakeSettings: this.state.fakeSettings.set('passwordResetSuccess', ! res.error)});
    });
  }

  /**
   * Determines whether the submit password button should be disabled
   *
   * @return {Boolean}
   */
  submitPasswordDisabled() {
    const {company} = this.props;
    const password = company.getIn(['updateManager', 'password'], '');
    const confirm = company.getIn(['updateManager', 'confirm'], '');

    if (password < minimumPasswordLength) {
      return true;
    }

    if (password !== confirm) {
      return true;
    }

    return !!company.get('updateManagerErrors').toList().filter(error => error).count();
  }

  /**
   * Change the currently active tab
   *
   * @param {Number} newTab
   */
  changeTab(newTab) {
    if (isNaN(newTab)) {
      return;
    }

    this.setState({activeTab: newTab});
  }

  /**
   * Get the classes for each tab
   *
   * @param {Number} tab
   * @return {String}
   */
  getTabClasses(tab) {
    let classes = 'my-tab';

    if (tab === this.state.activeTab) {
      classes += ' my-active-tab';
    }

    return classes;
  }

  /**
   * Change password value
   *
   * @param {Object} event
   */
  changePassword(event) {
    const {setProp} = this.props;
    const {target: {name, value}} = event;
    setProp(value, 'updateManager', name);
  }

  /**
   * Enable notifications from the specified customer
   *
   * @param {Number} customerId
   */
  selectCustomer(customerId) {
    this.props.setProp(
      Immutable.fromJS({customer: customerId, email: true}), 'notification', 'selected', customerId
    );
  }

  /**
   * Disable notifications from the specified customer
   *
   * @param {Number} customerId
   */
  removeCustomer(customerId) {
    this.props.setProp(
      Immutable.fromJS({customer: customerId, email: false}), 'notification', 'selected', customerId
    );
  }

  /**
   * Save notification settings
   *
   * @param {Number} managerId
   * @param {Immutable.Map} selected
   */
  saveNotificationSettings(managerId, selected) {
    this.setState({fakeSettings: this.state.fakeSettings.remove('setNotificationSuccess')});

    this.props.setNotifications(managerId, selected.toList().toJS()).then(res => {
      this.setState({fakeSettings: this.state.fakeSettings.set('setNotificationSuccess', ! res.error)});
    });
  }

  /**
   * Retrieve notification settings
   *
   * @param {Immutable.Map} manager
   */
  getNotificationSettings(manager) {
    this.props.getNotifications(manager.get('id'));
  }

  /**
   * Change customer when configuring availability
   *
   * @param {Number} customerId
   */
  changeCustomer(customerId) {
    this.props.setPropSettings(customerId, 'selectedCustomer');
  }

  render() {
    const {activeTab} = this.state;
    const {auth, company, getAvailability, setAvailability, setPropSettings, settings} = this.props;
    const selectedCustomer = settings.get('selectedCustomer');
    const customers = settings.get('customers', Immutable.List());

    return (
      <div>
        <Tabs justified inkBarStyle={styles.hide} className="my-tabs" onChange={this.changeTab}>
          <Tab value={1} label="Availability" className={this.getTabClasses(1)}>
            <div className={'row ' + (activeTab !== 1 ? 'tab-hide-content' : '')}>
              <div className="col-md-3">
                <CustomerSelector
                  customers={customers}
                  selectedCustomer={selectedCustomer}
                  selectCustomer={this.changeCustomer}
                />
              </div>
              <div className="col-md-9">
                <Availability
                  auth={auth}
                  form={settings.getIn(['availability', 'form'])}
                  errors={settings.getIn(['availability', 'errors'])}
                  setProp={setPropSettings}
                  getAvailability={getAvailability}
                  setAvailability={setAvailability}
                  settings={settings}
                  selectedCustomer={selectedCustomer}
                />
              </div>
            </div>
          </Tab>
          <Tab value={2} label="Email Notifications" className={this.getTabClasses(2)}>
            <div className={activeTab !== 2 ? 'tab-hide-content' : ''}>
              <EnableNotifications
                selected={company.getIn(['notification', 'selected'])}
                getNotification={this.getNotificationSettings}
                setNotification={this.saveNotificationSettings}
                customers={company.getIn(['notification', 'customers'])}
                selectCustomer={this.selectCustomer}
                removeCustomer={this.removeCustomer}
                user={auth.get('user')}
                settings={this.state.fakeSettings}
              />
            </div>
          </Tab>
          <Tab value={3} label="Password" className={this.getTabClasses(3)}>
            <div className={activeTab !== 3 ? 'tab-hide-content' : ''}>
              <PasswordSettings
                form={company.get('updateManager')}
                errors={company.get('updateManagerErrors')}
                formChange={this.changePassword}
                enterFunction={this.submitPassword}
                passwordText="New Password"
                confirmText="Confirm Password"
                settings={this.state.fakeSettings}
                required={false}
              />
              <div className="col-md-12">
                <ActionButton
                  onClick={this.submitPassword}
                  text="Update password"
                  disabled={this.submitPasswordDisabled()}
                  type="submit"
                />
              </div>
            </div>
          </Tab>
        </Tabs>
      </div>
    );
  }
}
