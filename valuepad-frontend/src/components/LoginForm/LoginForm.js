import React, {Component, PropTypes} from 'react';
import Immutable from 'immutable';
import {VpTextField} from 'components';

export default class LoginForm extends Component {
  static propTypes = {
    // Form
    form: PropTypes.instanceOf(Immutable.Map),
    // Form change
    formChange: PropTypes.func.isRequired,
    // Submit when enter key is pressed
    submitOnEnter: PropTypes.func.isRequired,
    // Errors
    errors: PropTypes.instanceOf(Immutable.Map),
    // Keep user logged in
    keepLoggedIn: PropTypes.func.isRequired
  };

  render() {
    const {form, submitOnEnter, errors, formChange, keepLoggedIn} = this.props;
    return (
      <form>
        {/*This is a trick to force Chrome to turn off autocomplete, since Chrome tries to only autocomplete the
        first password field it encounters in a form
        */}
        <div style={{display: 'none'}}>
          <VpTextField
            type="password"
            name="password"
            value={form.get('password')}
            label="Password"
            onChange={formChange}
            enterFunction={submitOnEnter}
            error={errors.get('password')}
            autoComplete="off"
          />
        </div>
        <div className="row">
          <div className="col-md-6">
            <VpTextField
              name="username"
              value={form.get('username')}
              label="Username"
              placeholder="Username"
              onChange={formChange}
              enterFunction={submitOnEnter}
              error={errors.get('username') || errors.get('credentials')}
              autoComplete="off"
            />
          </div>
          <div className="col-md-6">
            <VpTextField
              type="password"
              name="password"
              value={form.get('password')}
              label="Password"
              placeholder="Password"
              onChange={formChange}
              enterFunction={submitOnEnter}
              error={errors.get('password')}
              autoComplete="off"
            />
          </div>
          <div className="col-md-12">
            <label>
              <input type="checkbox" name="keepLoggedIn" onClick={keepLoggedIn} style={{marginRight: '5px'}} />
              <span>Keep me logged in</span>
            </label>
          </div>
        </div>
      </form>
    );
  }
}
