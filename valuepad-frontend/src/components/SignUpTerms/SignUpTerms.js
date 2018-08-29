import React, {Component, PropTypes} from 'react';

import {VpTextField} from 'components';

import Immutable from 'immutable';
import moment from 'moment';

import {validateSignUpForm} from 'helpers/genericFunctions';

// Required fields
const fields = ['signature', 'agreeTerms'];

/**
 * Sign up terms and conditions
 */
export default class SignUpTerms extends Component {
  static propTypes = {
    // Appraiser (needed for validation)
    appraiser: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Update checkbox function
    updateCheckbox: PropTypes.func.isRequired,
    // Sign up form
    form: PropTypes.instanceOf(Immutable.Map),
    // Set prop
    setProp: PropTypes.func.isRequired,
    // Errors
    errors: PropTypes.instanceOf(Immutable.Map),
    // Set next button to disabled
    setNextButtonDisabled: PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);

    this.changeSignature = ::this.changeSignature;
    this.updateCheckbox = props.updateCheckbox.bind(this, 'agreeTerms');
  }

  /**
   * Default signed at to now
   */
  componentDidMount() {
    this.props.setProp(new Date(), 'signUpForm', 'signedAt');
    // Validate form
    validateSignUpForm(this.props, false, fields);
  }

  /**
   * Enable next button when all required items complete
   * @param nextProps
   */
  componentWillReceiveProps(nextProps) {
    // Validate form
    validateSignUpForm(this.props, nextProps, fields);
  }

  /**
   * Update signature field
   * @param event
   */
  changeSignature(event) {
    this.props.setProp(event.target.value, 'signUpForm', 'signature');
  }

  render() {
    const {form, errors} = this.props;
    return (
      <div>
        <h3 className="no-top-spacing signup-heading text-center">Terms & Conditions</h3>
        <div className="row">
          <div className="col-md-12">
            <p>By entering my name I acknowledge my consent to do business electronically with Staging. I accept the terms of the agreements provided above and confirm the information provided is complete and accurate.</p>
          </div>
        </div>
        <input
          type="checkbox"
          name="terms"
          checked={form.get('agreeTerms')}
          onClick={this.updateCheckbox}
        />
        <label>&nbsp;I agree to these terms and conditions</label>

        <div className="row">
          <div className="col-md-6">
            <VpTextField
              name="signature"
              value={form.get('signature')}
              label="Signature (Enter Your Name)"
              fullWidth
              onChange={this.changeSignature}
              error={errors.get('signature')}
            />
          </div>
          <div className="col-md-6">
            <VpTextField
              name="signedAt"
              value={moment(form.get('signedAt')).format('MMM D, YYYY')}
              label="Date"
              fullWidth
              disabled
            />
          </div>
        </div>
      </div>
    );
  }
}
