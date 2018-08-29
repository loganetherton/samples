import React, {Component, PropTypes} from 'react';
import { connect } from 'react-redux';
import {TaxClassification, States, SmartTextField} from 'components';
import moment from 'moment';
import Immutable from 'immutable';

import {
  RaisedButton,
  Dialog,
  Checkbox
} from 'material-ui';

import {changeCheckboxImmutable} from 'helpers/genericFunctions';
import {getFormErrorsImmutable} from 'helpers/validation';

import {
  formChange as formChange,
  removeFromForm,
  setDefault,
  getW9,
  updateW9,
  setProp
} from 'redux/modules/w9';

import {Void} from 'components';

/**
 * Appraiser W9 component
 */
@connect(
  state => ({
    auth: state.auth,
    w9: state.w9
  }),
  {
    formChange,
    removeFromForm,
    setDefault,
    getW9,
    updateW9,
    setProp
  })
export default class W9 extends Component {
  static propTypes = {
    // W9
    w9: PropTypes.instanceOf(Immutable.Map),
    // Auth
    auth: PropTypes.instanceOf(Immutable.Map),
    // Change form
    formChange: PropTypes.func.isRequired,
    // Remove a form item (such as when LLC is deselected)
    removeFromForm: PropTypes.func.isRequired,
    // Set a default prop
    setDefault: PropTypes.func.isRequired,
    // Get existing w9
    getW9: PropTypes.func.isRequired,
    // Update existing w9
    updateW9: PropTypes.func.isRequired,
    // If updating via profile
    profile: PropTypes.bool,
    // Set a property explicitly
    setProp: PropTypes.func.isRequired,
    // Sign up
    signUp: PropTypes.bool
  };

  /**
   * Init date picker
   * @param props
   */
  constructor(props) {
    super(props);
    this.state = {
      date: moment().format('MM-DD-YYYY')
    };
  }

  /**
   * Init dropdowns
   */
  componentDidMount() {
    // Set tax classifications
    this.props.setDefault('federalTaxClassification', 'individual');
    // Set state
    this.props.setDefault('state', 'AL');
    // Set cross out item 2
    this.props.setDefault('isNotifiedByIRS', false);
    const userId = this.props.auth.getIn(['user', 'id']);
    // Get w9
    if (this.props.profile && userId) {
      this.props.getW9(userId);
    }
  }

  /**
   * Load on this state, get w9
   * @param nextProps
   */
  componentWillReceiveProps(nextProps) {
    const thisUserId = this.props.auth.getIn(['user', 'id']);
    const nextUserId = nextProps.auth.getIn(['user', 'id']);
    if (nextProps.profile && !thisUserId && nextUserId) {
      this.props.getW9(nextUserId);
    }
  }

  /**
   * Set a value directly
   * @param name
   * @param value
   */
  setValue(name, value) {
    this.props.formChange({name, value});
  }

  /**
   * Change select field
   * @param name Form attribute name
   * @param event
   * @param id Select key
   * @param value Select value
   */
  changeSelect(name, event, id, value) {
    this.props.formChange({name, value});
  }

  /**
   * AMC sign up form change
   * @param event
   */
  formChange(event) {
    const {name, type: targetType} = event.target;
    let {value} = event.target;
    const {formChange} = this.props;
    if (targetType === 'checkbox') {
      value = changeCheckboxImmutable.call(this, 'w9', 'w9', name);
    }
    formChange({name, value});
  }

  /**
   * Submit w9 function
   */
  submitW9() {
    const {w9, updateW9, auth, signUp} = this.props;
    updateW9(auth.getIn(['user', 'id']), w9.get('w9').toJS(), w9.get('w9Exists'), signUp);
  }

  /**
   * Close the update success dialog
   */
  closeUpdateDialog() {
    this.props.setProp(false, 'updateW9Success');
  }

  render() {
    const {
      w9,
      removeFromForm,
      profile,
      signUp
      } = this.props;
    const w9Errors = w9.get('w9Errors');
    const form = w9.get('w9');
    // Errors
    const errors = getFormErrorsImmutable(w9Errors);
    const {date: currentDate} = this.state;
    return (
      <div>
        <Void pixels={15}/>

        <div className="container-fluid">
          <h4 className="text-center">W9</h4>

          <div className="row">
            <div className="col-md-12">
              Please provide your W9 information.
              <a target="_blank" href="https://www.irs.gov/pub/irs-pdf/fw9.pdf">View W9 on IRS site</a>
            </div>
          </div>
          <div className="row">
            <div className="col-md-12">
              <SmartTextField
                name="name"
                value={form.get('name')}
                floatingLabelText="Name (as shown on income tax return)"
                fullWidth
                onChange={::this.formChange}
                onEnterKeyDown={::this.submitW9}
                errorText={errors.get('name')}
              />
            </div>
          </div>
          <div className="row">
            <div className="col-md-12">
              <SmartTextField
                name="businessName"
                value={form.get('businessName')}
                floatingLabelText="Business name/disregarded entity name, if different from above"
                fullWidth
                onChange={::this.formChange}
                onEnterKeyDown={::this.submitW9}
                errorText={errors.get('businessName')}
              />
            </div>
          </div>
          <div className="row">
            <div className="col-md-12">
              <TaxClassification form={form}
                                 label={'Federal Tax Classification'}
                                 errors={errors}
                                 changeHandler={::this.changeSelect}
                                 removeFormItem={removeFromForm}/>
            </div>
          </div>
          <div className="row">
            <div className="col-md-12">
              <div>
                <strong>
                  Exemption</strong> (codes apply only to certain entities, not individuals):
              </div>
            </div>
          </div>
          <div className="row">
            <div className="col-md-12">
              <SmartTextField
                name="exemptionPayeeCode"
                value={form.get('exemptionPayeeCode')}
                floatingLabelText="Exempt payee code (if any)"
                fullWidth
                onChange={::this.formChange}
                onEnterKeyDown={::this.submitW9}
                errorText={errors.get('exemptionPayeeCode')}
              />
            </div>
          </div>
          <div className="row">
            <div className="col-md-12">
              <SmartTextField
                name="exemptionFATCAReporting"
                value={form.get('exemptionFATCAReporting')}
                floatingLabelText="Exemption from FATCA reporting code (if any)"
                fullWidth
                onChange={::this.formChange}
                onEnterKeyDown={::this.submitW9}
                errorText={errors.get('exemptionFATCAReporting')}
              />
            </div>
          </div>
          <div className="row">
            <div className="col-md-12">
              <SmartTextField
                name="accountNumbers"
                value={form.get('accountNumbers')}
                floatingLabelText="List account number(s) here, separated by commas (optional)"
                fullWidth
                onChange={::this.formChange}
                onEnterKeyDown={::this.submitW9}
                errorText={errors.get('accountNumbers')}
              />
            </div>
          </div>
          <div className="row">
            <div className="col-md-12">
              <SmartTextField
                name="address"
                value={form.get('address')}
                floatingLabelText="Address (number, street, and apt. or suite no.)"
                fullWidth
                onChange={::this.formChange}
                onEnterKeyDown={::this.submitW9}
                errorText={errors.get('address')}
              />
            </div>
          </div>
          <div className="row">
            <div className="col-md-4">
              <SmartTextField
                name="city"
                value={form.get('city')}
                floatingLabelText="City"
                fullWidth
                onChange={::this.formChange}
                onEnterKeyDown={::this.submitW9}
                errorText={errors.get('city')}
              />
            </div>
            <div className="col-md-4">
              <States form={form}
                      changeHandler={this.setValue.bind(this, 'state')}/>
            </div>
            <div className="col-md-4">
              <SmartTextField
                name="zip"
                value={form.get('zip')}
                floatingLabelText="Zip"
                fullWidth
                onChange={::this.formChange}
                onEnterKeyDown={::this.submitW9}
                errorText={errors.get('zip')}
              />
            </div>
          </div>

          <Void pixels={35}/>

          <h5><strong>Requester's Name and Address</strong> (optional):</h5>

          <div className="row">
            <div className="col-md-6">
              <SmartTextField
                name="requesterName"
                value={form.get('requesterName')}
                floatingLabelText="Name"
                fullWidth
                onChange={::this.formChange}
                onEnterKeyDown={::this.submitW9}
                errorText={errors.get('requesterName')}
              />
            </div>
            <div className="col-md-6">
              <SmartTextField
                name="requesterAddress"
                value={form.get('requesterAddress')}
                floatingLabelText="Address"
                fullWidth
                onChange={::this.formChange}
                onEnterKeyDown={::this.submitW9}
                errorText={errors.get('requesterAddress')}
              />
            </div>
          </div>
          <div className="row">
            <div className="col-md-12">
              <SmartTextField
                name="taxIdentificationNumber"
                value={form.get('taxIdentificationNumber')}
                floatingLabelText="Taxpayer Identification Number (TIN)"
                fullWidth
                onChange={::this.formChange}
                onEnterKeyDown={::this.submitW9}
                errorText={errors.get('taxIdentificationNumber')}
              />
            </div>
          </div>
          <div className="row">
            <div className="col-md-12">
              <p>
                Enter your TIN in the appropriate box. The TIN provided must match the name given on line 1 to avoid
                backup withholding. For individuals, this is your social security number (SSN). However, for a resident
                alien, sole proprietor, or disregarded entity, see the Part 1 instructions on page 3. For other
                entities, it is your employer identification number (EIN). if you do not have a number, see How to get a
                TIN on page 3.
              </p>

              <p>
                <strong>
                  Note: If the account is in more than one name, see the chart on page 4 for guidelines on whose
                  Employer identification number to enter.
                </strong>
              </p>
            </div>
          </div>

          <Void pixels={15}/>

          <h5><strong>Certification</strong></h5>

          <div className="row">
            <div className="col-md-12">
              <div>Under penalties of perjury, I certify that:</div>
              <ol>
                <li>
                  The number shown on this form is my correct taxpayer identification number (or I am waiting for a
                  number
                  to be issued to me), and
                </li>
                <li>
                  I am not subject to backup withholding because: (a) I am exempt from backup withholding, or(b) I have
                  not been notified by the Internal Revenue Service (IRS) that I am subject to backup withholding as a
                  result of a failure to report all interest or dividends, or (c) the IRS has notified me that I am no
                  longer subject to backup withholding, and
                </li>
                <li>
                  I am a U.S citizen or other U.S person (defined below).
                </li>
                <li>
                  The FATCA code(s) entered on this form (if any) indicating that I am exempt from FATCA reporting is
                  correct.
                </li>
              </ol>
            </div>
          </div>
          <div className="row">
            <div className="col-md-12">
              <Checkbox
                name="isNotifiedByIRS"
                checked={form.get('isNotifiedByIRS')}
                label="Cross out item 2 per certification instructions below"
                fullWidth
                onCheck={::this.formChange}
              />
            </div>
          </div>

          <Void pixels={15}/>

          <div className="row">
            <div className="col-md-12">
              <h5><strong>Certification instructions</strong></h5>
              <div>You must cross out item 2 above if you have been notified by the IRS that you are currently subject
                to backup withholding because you have failed to report all interest and dividends on your tax return.
                For real estate transactions, item 2 does not apply. For mortgage interest paid, acquisition or
                abandonment of secured property, cancellation of debt, contributions to an individual retirement
                arrangement (IRA), and generally, payment other than interest and dividends, you are not required to
                sign the Certification, but you must provide your correct TIN. See the instructions on page 3.
              </div>
            </div>
          </div>
          <div className="row">
            <div className="col-md-12">
              <div>By entering my name I acknowledge my consent to do business electronically with Demo. I accept the
                terms of the agreements provided above and confirm the information provided is complete and accurate.
              </div>
            </div>
          </div>
          <div className="row">
            <div className="col-md-12">
              <SmartTextField
                name="signature"
                value={form.get('signature')}
                floatingLabelText="Signature of U.S. Person (typing your name here constitutes an electronic signature)"
                fullWidth
                onChange={::this.formChange}
                onEnterKeyDown={::this.submitW9}
                errorText={errors.get('signature')}
              />
            </div>
          </div>
          <div className="row">
            <div className="col-md-12">
              <SmartTextField
                name="initials"
                value={form.get('initials')}
                floatingLabelText="Applicant initials"
                fullWidth
                onChange={::this.formChange}
                onEnterKeyDown={::this.submitW9}
                errorText={errors.get('initials')}
              />
            </div>
          </div>
          <div className="row">
            <div className="col-md-12">
              <SmartTextField
                name="signedAt"
                value={currentDate}
                floatingLabelText="Date"
                fullWidth
                disabled
              />
            </div>
          </div>
        </div>
        {profile &&
         <div className="row">
           <div className="col-md-12">
             <RaisedButton
               secondary
               label="Update W9"
               onClick={::this.submitW9}
             />
           </div>
         </div>
        }
        {signUp &&
         <div className="row">
           <div className="col-md-12 text-center">
             <RaisedButton
               secondary
               label="Submit W9"
               onClick={::this.submitW9}
             />
           </div>
         </div>
        }
        <Dialog
          title="W9 successfully updated"
          actions={<RaisedButton
               secondary
               label="Close"
               onClick={::this.closeUpdateDialog}
             />}
          modal
          open={w9.get('updateW9Success')}
        />
      </div>
    );
  }
}
