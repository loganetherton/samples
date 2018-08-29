import React, {Component, PropTypes} from 'react';

import {
  Languages,
  FirstLastName,
  Password,
  Void,
  VpTextField
} from 'components';

import {validateSignUpForm} from 'helpers/genericFunctions';
import Immutable from 'immutable';

// Required fields
const fields = ['firstName', 'lastName', 'email', 'username', 'password', 'confirm', 'languages'];

export default class SignUpProfile extends Component {
  static propTypes = {
    // Form receiving input
    form: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Form change function
    formChange: PropTypes.func.isRequired,
    // Form errors
    errors: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Update languages
    updateValue: PropTypes.func.isRequired,
    // If on profile (not sign up)
    profile: PropTypes.bool.isRequired,
    // Appraiser
    appraiser: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Set next button enabled/disabled
    setNextButtonDisabled: PropTypes.func.isRequired,
    // Enter function on profile
    enterFunctionProfile: PropTypes.func.isRequired,
    // Available languages
    languages: PropTypes.instanceOf(Immutable.List).isRequired,
    // Disabled (customer view)
    disabled: PropTypes.bool,
    // Viewing as manager
    isManager: PropTypes.bool
  };

  constructor(props) {
    super(props);

    this.updateLanguages = props.updateValue.bind(this, 'append', 'languages');
  }

  /**
   * Default next button false
   */
  componentDidMount() {
    // Validate form
    validateSignUpForm(this.props, false, fields);
  }

  /**
   * Check if the next button should be disabled
   * @param nextProps
   */
  componentWillReceiveProps(nextProps) {
    // Validate form
    validateSignUpForm(this.props, nextProps, fields);
  }

  render() {
    const {
      form,
      formChange,
      errors,
      profile,
      enterFunctionProfile,
      languages,
      disabled = false,
      isManager = false
    } = this.props;
    return (
      <div>
        {!isManager &&
         <h3 className="no-top-spacing text-center">{profile ? 'Update Your Profile' : 'Create Your Profile'}</h3>
        }

        <FirstLastName
          form={form}
          formChange={formChange}
          errors={errors}
          tabIndexStart={1}
          enterFunction={enterFunctionProfile}
          disabled={disabled}
          required
        />
        <div className="row">
          <div className="col-md-6">
            <VpTextField
              value={form.get('email')}
              label="Your email address"
              name="email"
              placeholder="Your email address"
              onChange={formChange}
              tabIndex={3}
              error={errors.get('email')}
              enterFunction={enterFunctionProfile}
              disabled={disabled}
              required
            />
          </div>
          <div className="col-md-6">
            {!profile &&
             <VpTextField
               value={form.get('username')}
               label="Username"
               name="username"
               onChange={formChange}
               tabIndex={4}
               error={errors.get('username')}
               enterFunction={enterFunctionProfile}
               required
             />
            }
            {profile &&
              <Languages
                changeHandler={this.updateLanguages}
                multiSelectable
                form={form}
                error={errors.get('languages')}
                required
                languages={languages}
                disabled={disabled}
              />
            }
          </div>
        </div>
        <div className="row">
          {!profile &&
           <Password
            form={form}
            formChange={formChange}
            errors={errors}
            tabIndexStart={5}
            required
          />
          }
        </div>

        <Void pixels={15}/>

        {!profile &&
          <div className="row">
            <div className="col-md-12">
              <Languages
                changeHandler={this.updateLanguages}
                multiSelectable
                form={form}
                error={errors.get('languages')}
                languages={languages}
                required
              />
            </div>
          </div>
        }
      </div>
    );
  }
}
