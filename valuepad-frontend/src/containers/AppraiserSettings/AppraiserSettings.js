import React, {Component, PropTypes} from 'react';
import { connect } from 'react-redux';
import Immutable from 'immutable';
import {Tabs, Tab} from 'material-ui';
import {PaymentInformation, EnableNotifications, Availability, PasswordSettings, CustomerSelector} from 'components';
import {
  ActionButton,
  Void
} from 'components';

import {
  removeItem,
  setProp,
  getAchInfo,
  submitAchInfo,
  getAvailability,
  setAvailability,
  getNotification,
  setNotification,
  selectCustomer,
  removeCustomer,
  updatePassword,
  getCcInfo,
  submitCcInfo,
  getCustomers
} from 'redux/modules/settings';

// Minimum password length
const minPasswordLength = 5;

const styles = {hide: {display: 'none'}};

@connect(
  state => ({
    auth: state.auth,
    settings: state.settings
  }),
  {
    removeItem,
    setProp,
    getAchInfo,
    submitAchInfo,
    getAvailability,
    setAvailability,
    getNotification,
    setNotification,
    selectCustomer,
    removeCustomer,
    updatePassword,
    getCcInfo,
    submitCcInfo,
    getCustomers
  })
export default class AppraiserSettings extends Component {
  static propTypes = {
    // Settings reducer
    settings: PropTypes.instanceOf(Immutable.Map),
    // Auth
    auth: PropTypes.instanceOf(Immutable.Map),
    // Remove item from form
    removeItem: PropTypes.func.isRequired,
    // Set a property explicitly
    setProp: PropTypes.func.isRequired,
    // Get availability
    getAvailability: PropTypes.func.isRequired,
    // Set availability
    setAvailability: PropTypes.func.isRequired,
    // Select/deselect a customer
    selectCustomer: PropTypes.func.isRequired,
    // Set password
    updatePassword: PropTypes.func.isRequired,
    // Selected appraiser (customer view)
    selectedAppraiser: PropTypes.number,
    // Get ACH info
    getAchInfo: PropTypes.func.isRequired,
    // Submit ACH info
    submitAchInfo: PropTypes.func.isRequired,
    // Get CC info
    getCcInfo: PropTypes.func.isRequired,
    // Submit CC info
    submitCcInfo: PropTypes.func.isRequired,
    // Remove customer
    removeCustomer: PropTypes.func.isRequired,
    // Get notification
    getNotification: PropTypes.func.isRequired,
    // Set notification
    setNotification: PropTypes.func.isRequired,
    // Get customers
    getCustomers: PropTypes.func.isRequired,
  };

  constructor(props) {
    super(props);
    this.state = {
      tabValue: 1
    };

    this.submitPassword = ::this.submitPassword;
    this.passwordFormChange = ::this.passwordFormChange;
    this.changeTabValue = ::this.changeTabValue;
    this.changeCustomer = ::this.changeCustomer;
  }

  componentDidMount() {
    const {getCustomers, auth} = this.props;

    if (auth.getIn(['user', 'type']) !== 'customer') {
      getCustomers(auth.get('user'));
    }
  }

  /**
   * Change the tab
   * @param tabValue
   */
  changeTabValue(tabValue) {
    // Prevent unnecessary firing
    if (isNaN(tabValue)) {
      return;
    }
    this.setState({
      tabValue
    });
  }

  /**
   * Update password form
   */
  passwordFormChange(event) {
    const {name, value} = event.target;
    this.props.setProp(value, 'password', 'form', name);
  }

  /**
   * Submit new password
   */
  submitPassword() {
    const {auth, updatePassword, settings} = this.props;
    const appraiserId = auth.getIn(['user', 'id']);
    const password = settings.getIn(['password', 'form', 'password']);
    if (!this.isPasswordDisabled()) {
      updatePassword(appraiserId, {
        password
      });
    }
  }

  /**
   * Determine if submit new password is disabled
   */
  isPasswordDisabled() {
    const form = this.props.settings.getIn(['password', 'form']);
    const password = form.get('password');
    const confirm = form.get('confirm');
    return !password || !confirm || password.length < minPasswordLength || password !== confirm;
  }

  /**
   * Changes selected customer
   *
   * @param {number} newCustomerId
   */
  changeCustomer(newCustomerId) {
    const {setProp} = this.props;
    setProp(newCustomerId, 'selectedCustomer');
  }

  render() {
    const {tabValue} = this.state;
    const {
      settings,
      auth,
      selectedAppraiser = null,
      setProp,
      getAchInfo,
      submitAchInfo,
      getCcInfo,
      submitCcInfo,
      getAvailability,
      selectCustomer,
      removeCustomer,
      getNotification,
      setNotification,
      setAvailability
    } = this.props;
    const passwordDisabled = this.isPasswordDisabled();
    const customers = settings.get('customers', Immutable.List());
    const selectedCustomer = settings.get('selectedCustomer');

    return (
        <div>
          {<Tabs justified value={tabValue} className="my-tabs" inkBarStyle={styles.hide} onChange={this.changeTabValue}>
             {/* ACH Information */}
             <Tab value={1} label="Payment Information" className={'my-tab' + (tabValue === 1 ? ' my-active-tab' : '')}>
              <div className={tabValue !== 1 ? 'tab-hide-content' : ''}>
               <PaymentInformation
                 auth={auth}
                 settings={settings}
                 ach={settings.get('achInfo')}
                 cc={settings.get('ccInfo')}
                 selectedAppraiser={selectedAppraiser}
                 setProp={setProp}
                 getAchInfo={getAchInfo}
                 submitAchInfo={submitAchInfo}
                 getCcInfo={getCcInfo}
                 submitCcInfo={submitCcInfo}
               />
              </div>
             </Tab>
             {/* Availability */}
             <Tab value={2} label="Availability" className={'my-tab' + (tabValue === 2 ? ' my-active-tab' : '')}>
              <div className={'row ' + (tabValue !== 2 ? 'tab-hide-content' : '')}>
               {auth.getIn(['user', 'type']) !== 'customer' &&
                 <div className="col-md-3">
                  <CustomerSelector
                    customers={customers}
                    selectedCustomer={selectedCustomer}
                    selectCustomer={this.changeCustomer}
                  />
                 </div>
               }
               <div className={auth.getIn(['user', 'type']) !== 'customer' ? 'col-md-9' : 'col-md-12'}>
                 <Availability
                   auth={auth}
                   form={settings.getIn(['availability', 'form'])}
                   errors={settings.getIn(['availability', 'errors'])}
                   setProp={setProp}
                   getAvailability={getAvailability}
                   setAvailability={setAvailability}
                   settings={settings}
                   selectedAppraiser={selectedAppraiser}
                   selectedCustomer={selectedCustomer}
                 />
               </div>
              </div>
             </Tab>
             {/* Notifications */}
             <Tab value={3} label="Email Notifications" className={'my-tab' + (tabValue === 3 ? ' my-active-tab' : '')}>
               <EnableNotifications
                 selected={settings.getIn(['notification', 'selected'])}
                 getNotification={getNotification}
                 setNotification={setNotification}
                 customers={settings.getIn(['notification', 'customers'])}
                 selectCustomer={selectCustomer}
                 removeCustomer={removeCustomer}
                 user={auth.get('user')}
                 settings={settings}
                 selectedAppraiser={selectedAppraiser}
               />
             </Tab>
             {/* Password */}
             <Tab value={4} label="Password" className={'my-tab' + (tabValue === 4 ? ' my-active-tab' : '')}>
              <div className={tabValue !== 4 ? 'tab-hide-content' : ''}>
               <PasswordSettings
                 form={settings.getIn(['password', 'form'])}
                 formChange={this.passwordFormChange}
                 errors={settings.getIn(['password', 'errors'])}
                 enterFunction={this.submitPassword}
                 passwordText="New Password"
                 confirmText="Confirm Password"
                 settings={settings}
                 selectedAppraiser={selectedAppraiser}
               />
               {!selectedAppraiser &&
                 <div className="col-md-12">
                   <Void pixels={10}/>

                   <ActionButton
                     onClick={this.submitPassword}
                     text="Update Password"
                     disabled={passwordDisabled}
                     type="submit"
                   />
                 </div>
               }
              </div>
             </Tab>
           </Tabs>
          }
        </div>
    );
  }
}
