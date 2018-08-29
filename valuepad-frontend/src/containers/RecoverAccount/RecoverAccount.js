import React, {PropTypes, Component} from 'react';
import {connect} from 'react-redux';
import Immutable from 'immutable';

import {
  ActionButton,
  Void,
  VpTextField,
} from 'components';

import {
  recoverAccount,
  setProp,
} from 'redux/modules/auth';
import {Link} from 'react-router';

const styles = require('./RecoverAccount.scss');

@connect(
  state => ({
    auth: state.auth
  }),
  {
    recoverAccount,
    setProp,
  })
export default class RecoverAccount extends Component {
  static propTypes = {
    auth: PropTypes.object.isRequired,
    recoverAccount: PropTypes.func.isRequired,
    setProp: PropTypes.func.isRequired
  };

  componentWillUnmount() {
    const {setProp} = this.props;
    setProp(Immutable.Map(), 'recoverAccount');
    setProp(Immutable.Map(), 'recoverAccountErrors');
    setProp(null, 'recoverAccountSuccess');
    setProp(null, 'sendingRecoverAccount');
  }

  formSubmit() {
    const {auth, recoverAccount} = this.props;
    if (!auth.get('sendingRecoverAccount', false)) {
      recoverAccount(auth.getIn(['recoverAccount', 'email']));
    }
  }

  emailChanged(event) {
    this.props.setProp(event.target.value, 'recoverAccount', 'email');
  }

  render() {
    const {auth} = this.props;
    // Auth Bootstrap errors
    const form = auth.get('recoverAccount');
    const formErrors = auth.get('recoverAccountErrors');

    return (
      <div>
        <div className="container-fluid">
          <h4 className="text-center">Recover Username/Password</h4>

          {!auth.get('recoverAccountSuccess') &&
            <div>
              <VpTextField
                type="text"
                name="email"
                value={form.get('email')}
                label="Enter your email address to recover your username or reset your password"
                placeholder="Email"
                onChange={::this.emailChanged}
                enterFunction={::this.formSubmit}
                error={formErrors.get('email')}
              />
              <Void pixels={10}/>
              <div className="text-center">
                <ActionButton
                  type="submit"
                  onClick={::this.formSubmit}
                  disabled={auth.get('sendingRecoverAccount', false)}
                />
              </div>
            </div>
          }

          {auth.get('recoverAccountSuccess') &&
            <div>
              <div className="text-center">
                <p>You should receive an email shortly with instructions to recover your account.</p>
                <Link to="/">
                  <button className="btn btn-raised btn-info">Return to Login</button>
                </Link>
              </div>
            </div>
          }

          <Void pixels={10}/>
          <p className={styles['need-help']}>
            support@appraisalscope.com
            <br />
            800-321-0123
          </p>
        </div>
      </div>
    );
  }
}
