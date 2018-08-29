import React, {Component, PropTypes} from 'react';
import {connect} from 'react-redux';
import {Link} from 'react-router';

import Immutable from 'immutable';

import {formChange, setProp, createAmc, getAmc, updateAmc} from 'redux/modules/amc';
import {getFormErrorsImmutable} from 'helpers/validation';
import {LOGIN_URL} from 'redux/modules/urls';

import {
  VpTextField,
  Password,
  Address,
  PhoneNumber,
  ActionButton
} from 'components';

const styles = {
  actionButton: {marginBottom: '30px'},
  errorList: {listStyle: 'none'}
};

const propPaths = {
  phone: ['signUpForm', 'phone'],
  fax: ['signUpForm', 'fax']
};

@connect(
  state => ({
    amc: state.amc,
    auth: state.auth
  }),
  {
    formChange,
    setProp,
    createAmc,
    getAmc,
    updateAmc
  })
export default class AmcSignUp extends Component {
  static propTypes = {
    // AMC reducer
    amc: PropTypes.object.isRequired,
    // Form change handler
    formChange: PropTypes.func.isRequired,
    // Set reducer prop
    setProp: PropTypes.func.isRequired,
    // Create AMC
    createAmc: PropTypes.func.isRequired,
    // Open update dialog
    openUpdateDialog: PropTypes.func,
    // Open update fail dialog
    openUpdateFailDialog: PropTypes.func,
    // Does profile exist?
    profile: PropTypes.bool,
    // Auth reducer
    auth: PropTypes.instanceOf(Immutable.Map),
    // Get AMC
    getAmc: PropTypes.func,
    // Update AMC
    updateAmc: PropTypes.func
  }

  constructor(props) {
    super(props);

    this.changeState = ::this.changeState;
    this.createAmc = ::this.createAmc;
    this.updateAmc = ::this.updateAmc;
  }

  componentDidMount() {
    const {profile, getAmc, auth} = this.props;

    if (profile) {
      getAmc(auth.getIn(['user', 'id']));
    }
  }

  /**
   * Change state dropdown
   */
  changeState(value) {
    this.props.formChange({target: {name: 'state', value}});
  }

  createAmc() {
    this.props.createAmc(this.props.amc.get('signUpForm').toJS());
  }

  updateAmc() {
    const {auth, updateAmc, openUpdateDialog, openUpdateFailDialog} = this.props;

    updateAmc(
      auth.getIn(['user', 'id']),
      this.props.amc.get('signUpForm').toJS()
    ).then(res => {
      if (! res.error) {
        openUpdateDialog();
      } else {
        openUpdateFailDialog(this.renderErrors(Immutable.fromJS(res.error.errors)));
      }
    });
  }

  /**
   * Renders error messages
   *
   * @param errors
   * @returns {React.Element}
   */
  renderErrors(errors) {
    return (
      <ul style={styles.errorList}>
        {errors.map((error, index) => (
          <li key={index}>
            <strong>{error.get('message')}</strong>
          </li>
        )).toList()}
      </ul>
    );
  }

  /**
   * Returns true if the submit button should be disabled
   *
   * @returns {boolean}
   */
  submitDisabled() {
    const {amc} = this.props;
    const signUpForm = amc.get('signUpForm');
    const errors = amc.get('signUpFormErrors');

    // We're checking whether each of the required field has been filled.
    // This is mainly because the submit button wouldn't be disabled on initial
    // page load. Additionally, it's also possible that after the initial page
    // load, the user can fill certain fields without triggering an error and
    // the submit button would still be enabled even though the form might not
    // be completely filled out.
    const required = [
      'companyName', 'email', 'username', 'password', 'confirm', 'address1', 'city', 'state', 'zip', 'phone'
    ];

    let isDisabled = false;

    required.forEach(field => {
      if (!signUpForm.get(field)) {
        isDisabled = true;
      }
    });

    if (isDisabled) {
      return true;
    }

    // Make sure to disable it if we're in the process of creating an AMC as well.
    if (amc.get('signingUpAmc')) {
      return true;
    }

    // Finally, if all of the required fields have been filled in, we'll check
    // the error object instead.
    return !!errors.toList().filter(error => error).count();
  }

  /**
   * Returns true if the submit button should be disabled
   *
   * @returns {boolean}
   */
  updateSubmitDisabled() {
    const {amc} = this.props;
    const errors = amc.get('signUpFormErrors');

    // Disables submit button if we're in the process of updating an AMC.
    if (amc.get('updatingAmc')) {
      return true;
    }

    return !!errors.toList().filter(error => error).count();
  }

  render() {
    const {amc, formChange, setProp, profile} = this.props;
    const form = amc.get('signUpForm');
    const errors = getFormErrorsImmutable(amc.get('signUpFormErrors'));

    return (
      <div className="container-fluid">
        {/* Signup form */}
        {!amc.get('signUpAmcSuccess') &&
          <div>
            <div className="row">
              <div className="col-md-12 text-center">
                <h4>Account Details</h4>
              </div>
            </div>
            <div className="row">
              <div className="col-md-12">
                <VpTextField
                  onChange={formChange}
                  value={form.get('companyName')}
                  error={errors.get('companyName')}
                  name="companyName"
                  label="Company Name"
                  placeholder="Company Name"
                  required
                />
              </div>
            </div>
            <div className="row">
              <div className={profile ? 'col-md-12' : 'col-md-6'}>
                <VpTextField
                  onChange={formChange}
                  value={form.get('email')}
                  error={errors.get('email')}
                  name="email"
                  label="Email address"
                  placeholder="Email address"
                  required
                />
              </div>
              {!profile &&
                <div className="col-md-6">
                  <VpTextField
                    onChange={formChange}
                    value={form.get('username')}
                    error={errors.get('username')}
                    name="username"
                    label="Username"
                    placeholder="Username"
                    required
                  />
                </div>
              }
            </div>
            {!profile &&
              <div className="row">
                <Password
                  formChange={formChange}
                  form={form}
                  errors={errors}
                  required
                />
              </div>
            }
            <h4 className="text-center">Contact Information</h4>
            <Address
              formChange={formChange}
              form={form}
              changeState={this.changeState}
              errors={errors}
              required
            />
            <div className="row">
              <div className="col-md-6">
                <PhoneNumber
                  errors={errors}
                  form={form}
                  propPath={propPaths.phone}
                  setProp={setProp}
                  label="Phone number"
                  required
                />
              </div>
              <div className="col-md-6">
                <PhoneNumber
                  errors={errors}
                  form={form}
                  propPath={propPaths.fax}
                  setProp={setProp}
                  label="Fax number"
                />
              </div>
            </div>
            {!profile &&
              <div className="row">
                <div className="col-md-12">
                  <VpTextField
                    multiLine
                    onChange={formChange}
                    name="lenders"
                    label="Wholesaler/Investors"
                    placeholder="Wholesaler/Investors"
                    minRows={3}
                    value={form.get('lenders')}
                  />
                </div>
              </div>
            }
            <div className="row">
              <div className="col-md-12 text-center">
                {!profile &&
                  <ActionButton
                    type="submit"
                    text="Create AMC"
                    onClick={this.createAmc}
                    style={styles.actionButton}
                    disabled={this.submitDisabled()}
                  />
                }
                {profile &&
                  <ActionButton
                    type="submit"
                    text="Update profile"
                    onClick={this.updateAmc}
                    style={styles.actionButton}
                    disabled={this.updateSubmitDisabled()}
                  />
                }
              </div>
            </div>
          </div>
        }
        {/* Success message */}
        {amc.get('signUpAmcSuccess') &&
          <div className="row">
            <div className="col-md-12 text-center">
              <p>
                Thank your for signing up with ValuePad! We will review your account information, and be in touch shortly.
              </p>
              <p>
                Thank you,
                <br />
                The ValuePad Team
              </p>
              <p>
                <Link className="link" to={LOGIN_URL}>ValuePad.com</Link>
              </p>
            </div>
          </div>
        }
      </div>
    );
  }
}
