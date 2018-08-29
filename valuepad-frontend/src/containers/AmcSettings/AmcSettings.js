import React, {Component, PropTypes} from 'react';
import {connect} from 'react-redux';

import classNames from 'classnames';
import {Tabs, Tab} from 'material-ui';

import Immutable from 'immutable';

import {PasswordSettings, ActionButton, PaymentInformation} from 'components';

import {getFormErrorsImmutable} from 'helpers/validation';

import {updatePassword, formChange} from 'redux/modules/amc';
import {setProp, getAchInfo, getCcInfo, submitAchInfo, submitCcInfo} from 'redux/modules/settings';

// Minimum password length
const minPasswordLength = 5;

const styles = {hide: {display: 'none'}};

@connect(
  state => ({
    amc: state.amc,
    settings: state.settings
  }),
  {
    updatePassword,
    formChange,
    setProp,
    getAchInfo,
    getCcInfo,
    submitAchInfo,
    submitCcInfo
  })
export default class AmcSettings extends Component {
  static propTypes = {
    // Auth reducer
    auth: PropTypes.instanceOf(Immutable.Map).isRequired,
    // AMC reducer
    amc: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Update AMC's password
    updatePassword: PropTypes.func.isRequired,
    // Form change handler
    formChange: PropTypes.func.isRequired,
    // Settings reducer
    settings: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Set settings prop
    setProp: PropTypes.func.isRequired,
    // Get ACH info
    getAchInfo: PropTypes.func.isRequired,
    // Get CC Info
    getCcInfo: PropTypes.func.isRequired,
    // Submit ACH info
    submitAchInfo: PropTypes.func.isRequired,
    // Submit CC Info
    submitCcInfo: PropTypes.func.isRequired
  };

  constructor() {
    super();
    this.state = {
      tabValue: 1
    };

    this.submitPassword = ::this.submitPassword;
    this.changeTabValue = ::this.changeTabValue;
  }

  /**
   * Submits the new password
   */
  submitPassword() {
    const {auth, updatePassword, amc} = this.props;
    if (!this.submitPasswordDisabled()) {
      updatePassword(auth.getIn(['user', 'id']), amc.get('signUpForm').get('password'));
    }
  }

  /**
   * Returns true if the submit button should be disabled
   *
   * @return {boolean}
   */
  submitPasswordDisabled() {
    const {amc} = this.props;
    const password = amc.get('signUpForm').get('password');
    const confirm = amc.get('signUpForm').get('confirm');

    if (!password) {
      return true;
    }

    if (!confirm) {
      return true;
    }

    if (password < minPasswordLength) {
      return true;
    }

    if (password !== confirm) {
      return true;
    }

    return !!amc.get('signUpFormErrors').toList().filter(error => error).count();
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

  render() {
    const {
      amc,
      formChange,
      auth,
      settings,
      setProp,
      getAchInfo,
      getCcInfo,
      submitAchInfo,
      submitCcInfo
    } = this.props;

    return (
      <div>
        <Tabs justified inkBarStyle={styles.hide} className="my-tabs" onChange={this.changeTabValue}>
          <Tab value={1} label="Payment Information" className={classNames('my-tab', {'my-active-tab': this.state.tabValue === 1})}>
            <div className={this.state.tabValue !== 1 ? 'tab-hide-content' : ''}>
              <PaymentInformation
                auth={auth}
                settings={settings}
                ach={settings.get('achInfo')}
                cc={settings.get('ccInfo')}
                setProp={setProp}
                getAchInfo={getAchInfo}
                submitAchInfo={submitAchInfo}
                getCcInfo={getCcInfo}
                submitCcInfo={submitCcInfo}
              />
            </div>
          </Tab>
          <Tab value={2} label="Password" className={classNames('my-tab', {'my-active-tab': this.state.tabValue === 2})}>
            <div className={this.state.tabValue !== 2 ? 'tab-hide-content' : ''}>
              <PasswordSettings
                form={amc.get('signUpForm')}
                errors={getFormErrorsImmutable(amc.get('signUpFormErrors'))}
                formChange={formChange}
                enterFunction={this.submitPassword}
                passwordText="New Password"
                confirmText="Confirm Password"
                settings={amc}
                required={false}
              />
              <div className="col-md-12">
                <ActionButton
                  onClick={this.submitPassword}
                  text="Update Password"
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
