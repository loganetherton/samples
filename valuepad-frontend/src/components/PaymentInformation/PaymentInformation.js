import React, {Component, PropTypes} from 'react';
import Immutable from 'immutable';
import {
  ActionButton,
  Void,
  VpTextField,
  MonthSelect,
  YearSelect,
  AchForm
} from 'components';

import moment from 'moment';
import {Dialog} from 'material-ui';

import {validateSignUpForm} from 'helpers/genericFunctions';
// Style for headers
const headingStyle = {
  marginTop: '9px',
  paddingBottom: '6px'
};
const boldStyle = {
  fontWeight: 'bold'
};

// Required fields
const fields = [['ach', 'accountType'], ['ach', 'bankName'], ['ach', 'accountNumber'], ['ach', 'routing']];

export default class PaymentInformation extends Component {
  static propTypes = {
    // Appraiser (needed for validation during signup)
    appraiser: PropTypes.instanceOf(Immutable.Map),
    // Auth
    auth: PropTypes.instanceOf(Immutable.Map),
    // Set settings
    settings: PropTypes.instanceOf(Immutable.Map),
    // ACH form and errors
    ach: PropTypes.instanceOf(Immutable.Map),
    // CC form and errors
    cc: PropTypes.instanceOf(Immutable.Map),
    // Set property
    setProp: PropTypes.func.isRequired,
    // Get ACH info
    getAchInfo: PropTypes.func,
    // Submit ACH info
    submitAchInfo: PropTypes.func.isRequired,
    // Get credit card info
    getCcInfo: PropTypes.func.isRequired,
    // Update credit card info
    submitCcInfo: PropTypes.func.isRequired,
    // Path to ACH form props
    formPath: PropTypes.array,
    // ACH during sign up
    signUp: PropTypes.bool,
    // Selected appraiser (customer view)
    selectedAppraiser: PropTypes.number,
    // Enable/disable the next button (during signup)
    setNextButtonDisabled: PropTypes.func
  };

  /**
   * Initialize success indicators to closed
   */
  constructor(props) {
    super(props);
    // Success indicators
    this.state = {
      achUpdate: false,
      achUpdateSuccess: null,
      ccUpdate: false,
      ccUpdateSuccess: null
    };

    this.achFormChange = this.formChange.bind(this, 'ach');
    this.changeAchDropdown = this.changeDropdown.bind(this, 'ach');
    this.toggleAchForm = this.toggleForm.bind(this, 'showAchForm');
    this.achFormSubmit = this.formSubmit.bind(this, 'ach');
    this.ccFormChange = this.formChange.bind(this, 'cc');
    this.changeCcMonthDropdown = this.changeDropdown.bind(this, 'cc', ['expiresAt', 'month']);
    this.changeCcYearDropdown = this.changeDropdown.bind(this, 'cc', ['expiresAt', 'year']);
    this.toggleCcForm = this.toggleForm.bind(this, 'showCcForm');
    this.ccFormSubmit = this.formSubmit.bind(this, 'cc');
    this.closeAchUpdateDialog = this.closeUpdateDialog.bind(this, 'achUpdate');
    this.closeCcUpdateDialog = this.closeUpdateDialog.bind(this, 'ccUpdate');
  }

  /**
   * Retrieve existing ACH info on load
   */
  componentDidMount() {
    // Retrieve ACH and CC info if it exists
    const {auth, getAchInfo, signUp, getCcInfo, selectedAppraiser} = this.props;
    const user = auth.get('user');
    if (user) {
      getAchInfo(user, selectedAppraiser);
      getCcInfo(user, selectedAppraiser);
    }
    // Check disable next button for signUp
    if (signUp) {
      validateSignUpForm(this.props, false, fields);
    }
  }

  /**
   * Check if button is disabled during sign up
   */
  componentWillReceiveProps(nextProps) {
    const {auth, getAchInfo, getCcInfo, settings} = this.props;
    const {settings: nextSettings, selectedAppraiser} = nextProps;
    // User
    const nextUser = nextProps.auth.get('user');

    // Disable next button on signUp
    if (this.props.signUp) {
      validateSignUpForm(this.props, nextProps, fields);
    }

    // Determine whether or not to retrieve order statuses
    if (!auth.getIn(['user', 'id']) && nextUser && !nextProps.settings.get('getAch')) {
      getAchInfo(nextUser, selectedAppraiser);
      getCcInfo(nextUser, selectedAppraiser);
    }

    // Ach update
    if (typeof settings.get('submitAchSuccess') === 'undefined' &&
        typeof nextSettings.get('submitAchSuccess') === 'boolean') {
      this.setState({
        achUpdate: true,
        achUpdateSuccess: nextSettings.get('submitAchSuccess')
      });
    }
    // Credit card update
    if (typeof settings.get('submitCcInfoSuccess') === 'undefined' &&
        typeof nextSettings.get('submitCcInfoSuccess') === 'boolean') {
      this.setState({
        ccUpdate: true,
        ccUpdateSuccess: nextSettings.get('submitCcInfoSuccess')
      });
    }
  }

  /**
   * Get path to form
   */
  getFormPath() {
    return this.props.formPath ? this.props.formPath : ['achInfo', 'form'];
  }

  /**
   * Change dropdown
   */
  changeDropdown(type, event) {
    const {setProp} = this.props;
    let name;
    // Handle 3 args for cc expiration
    if (arguments.length === 3) {
      event = arguments[2];
      name = Array.isArray(arguments[1]) ? arguments[1] : [arguments[1]];
    }
    // ACH bank account type
    if (type === 'ach') {
      setProp(event.target.value, ...this.getFormPath.call(this), 'accountType');
    // CC expiration month and year
    } else if (type === 'cc') {
      setProp(parseInt(event.target.value, 10), 'ccInfo', 'form', ...name);
    }
  }

  /**
   * Update form value
   * @param type ach or cc
   * @param event
   */
  formChange(type, event) {
    const {name, value} = event.target;
    const {setProp} = this.props;
    // ACH
    if (type === 'ach') {
      setProp(value, ...this.getFormPath.call(this), name);
    // Credit card
    } else if (type === 'cc') {
      setProp(value, 'ccInfo', 'form', name);
    }
  }

  /**
   * Submit ACH
   */
  formSubmit(type) {
    const {submitAchInfo, submitCcInfo, auth, ach, cc} = this.props;
    const user = auth.get('user');

    // Submit ACH
    if (type === 'ach') {
      submitAchInfo(user, ach.get('form').toJS());
    }
    // Submit CC
    if (type === 'cc') {
      submitCcInfo(user, cc.get('form').toJS());
    }
  }

  /**
   * Show either ACH or CC form
   */
  toggleForm(type) {
    const {setProp, settings} = this.props;
    // Show/hide ach or cc form
    setProp(!settings.get(type), type);
  }

  /**
   * Close update dialog
   */
  closeUpdateDialog(type) {
    this.setState({
      [type]: false
    });
  }

  render() {
    const {ach, cc, signUp, settings, auth, selectedAppraiser} = this.props;
    const {achUpdate, achUpdateSuccess, ccUpdate, ccUpdateSuccess} = this.state;
    // ACH
    const achForm = ach.get('form');
    const achErrors = ach.get('errors');
    // CC
    const ccForm = cc.get('form');
    const ccErrors = cc.get('errors') || Immutable.Map();
    // Show forms
    const showAch = settings.get('showAchForm');
    const showCc = settings.get('showCcForm');
    const userType = auth.getIn(['user', 'type'], '');
    return (
      <div className="row">
        {/*ACH*/}
        <div className="col-md-6">
          <div style={headingStyle} className="text-center">
            <span style={boldStyle}>ACH Information</span>
          </div>
          <div>
            {showAch &&
              <AchForm
                form={achForm}
                formChange={this.achFormChange}
                changeDropdown={this.changeAchDropdown}
                submit={this.achFormSubmit}
                errors={achErrors}
                isAmc={userType === 'amc'}
              />
            }
            {!showAch &&
             <div className="text-center">
               <p>Bank account ending in {settings.getIn(['achInfo', 'accountNumber'])}</p>
             </div>
            }
            {/*ACH*/}
            {showAch &&
             <div className="row">
               <div className="col-md-12 text-center">
                 <Void pixels={10}/>
                 <ActionButton onClick={this.achFormSubmit} text="Update" type="submit" />&nbsp;&nbsp;
                 <ActionButton onClick={this.toggleAchForm} text="Cancel" type="cancel" />
               </div>
             </div>
            }
            {/*Change existing ACH info*/}
            {!showAch && !selectedAppraiser &&
             <div className="row">
               <div className="col-md-12 text-center">
                 <Void pixels={10}/>
                 <ActionButton onClick={this.toggleAchForm} text="Change" type="submit" />
               </div>
             </div>
            }
          </div>
        </div>

        {/*Credit card*/}
        <div className="col-md-6">
          <div style={headingStyle} className="text-center">
            <span style={boldStyle}>Credit Card Information</span>
          </div>
          {showCc &&
           <div>
             <div className="row">
               <div className="col-md-6">
                 <VpTextField
                   name="number"
                   value={ccForm.get('number', '')}
                   label="Credit Card Number"
                   onChange={this.ccFormChange}
                   enterFunction={this.ccFormSubmit}
                   error={ccErrors.getIn(['number', 'message']) ? 'A valid card number between 13 and 16 digits is required' : ''}
                 />
               </div>
               <div className="col-md-6">
                 <VpTextField
                   name="code"
                   value={ccForm.get('code', '')}
                   label="Security Code"
                   onChange={this.ccFormChange}
                   enterFunction={this.ccFormSubmit}
                   error={ccErrors.getIn(['code', 'message']) ? 'A valid security code between 3 and 4 digits is required' : ''}
                 />
               </div>
             </div>
             <div className="row">
               <div className="col-md-6">
                 <MonthSelect
                   selectedMonth={ccForm.getIn(['expiresAt', 'month'], '')}
                   label="Expiration Month"
                   changeValue={this.changeCcMonthDropdown}
                   error={ccErrors.getIn(['expiresAt', 'message'], '')}
                 />
               </div>
               <div className="col-md-6">
                 <YearSelect
                   selectedYear={ccForm.getIn(['expiresAt', 'year']) || parseInt(moment().format('YYYY'), 10)}
                   label="Expiration Year"
                   changeValue={this.changeCcYearDropdown}
                   maxYear={10}
                   minYear={0}
                   error={ccErrors.getIn(['expiresAt', 'message'], '')}
                   reverse
                 />
               </div>
             </div>
           </div>
          }
          {!showCc &&
           <div className="text-center">
             <p>Credit card ending in {settings.getIn(['ccInfo', 'number'])}</p>
           </div>
          }
          {/*Display button only on profile*/}
          {showCc &&
           <div className="row">
             <div className="col-md-12 text-center">
               <Void pixels={10}/>
               <ActionButton onClick={this.ccFormSubmit} text="Update" type="submit" />&nbsp;&nbsp;
               <ActionButton onClick={this.toggleCcForm} text="Cancel" type="cancel" />
             </div>
           </div>
          }
          {/*Change existing credit card info*/}
          {!showCc && !selectedAppraiser &&
           <div className="row">
             <div className="col-md-12 text-center">
               <Void pixels={10}/>
               <ActionButton onClick={this.toggleCcForm} text="Change" type="submit" />
             </div>
           </div>
          }
          {/*Update ACH*/}
          {!signUp &&
            <Dialog
              open={achUpdate}
              actions={
                <button className="btn btn-raised btn-info" onClick={this.closeAchUpdateDialog}>
                  Close
                </button>
              }
              title={achUpdateSuccess ? 'ACH Updated' : 'ACH Update Failed'}
            >
              <h4>
                {achUpdateSuccess ? 'Your ACH information has been updated' :
                 'An error has occurred. Your ACH information has not been updated'}
              </h4>
            </Dialog>
          }
          {/*Update bank account*/}
          {!signUp &&
            <Dialog
              open={ccUpdate}
              actions={
                <button className="btn btn-raised btn-info" onClick={this.closeCcUpdateDialog}>
                  Close
                </button>
              }
              title={ccUpdateSuccess ? 'Credit Card Updated' : 'Credit Card Update Failed'}
            >
              <h4>
                {ccUpdateSuccess ? 'Your credit card information has been updated' :
                 'An error has occurred. Your credit card information has not been updated'}
              </h4>
            </Dialog>
          }
        </div>
      </div>
    );
  }
}
