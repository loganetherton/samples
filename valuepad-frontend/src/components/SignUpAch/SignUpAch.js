import React, {Component, PropTypes} from 'react';

import {PaymentInformation} from 'components';

import Immutable from 'immutable';

const formPath = ['achInfo', 'form'];

/**
 * Instantiate ACH during sign up
 */
export default class SignUpAch extends Component {
  static propTypes = {
    // Appraiser (needed for validation)
    appraiser: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Auth
    auth: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Form
    form: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Get CC info
    getCcInfo: PropTypes.func.isRequired,
    // Submit CC info
    submitCcInfo: PropTypes.func.isRequired,
    // Settings reducer
    settings: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Get ACH info
    getAchInfo: PropTypes.func.isRequired,
    // Set prop
    setProp: PropTypes.func.isRequired,
    // Enable/disable next button
    setNextButtonDisabled: PropTypes.func.isRequired,
    // Submit ACH info
    submitAchInfo: PropTypes.func.isRequired
  };

  componentDidMount() {
    this.props.setNextButtonDisabled(false);
  }

  render() {
    const {
      getCcInfo,
      submitCcInfo,
      settings,
      getAchInfo,
      setProp,
      auth,
      submitAchInfo,
      appraiser,
      setNextButtonDisabled
    } = this.props;

    return (
      <div>
        <h3 className="no-top-spacing signup-heading text-center">ACH Information</h3>
        <PaymentInformation
          auth={auth}
          appraiser={appraiser}
          submitAchInfo={submitAchInfo}
          signUp
          formPath={formPath}
          form={this.props.form.get('ach') || Immutable.Map()}
          getCcInfo={getCcInfo}
          submitCcInfo={submitCcInfo}
          settings={settings}
          getAchInfo={getAchInfo}
          ach={settings.get('achInfo')}
          cc={settings.get('ccInfo')}
          setProp={setProp}
          setNextButtonDisabled={setNextButtonDisabled}
        />
      </div>
    );
  }
}
