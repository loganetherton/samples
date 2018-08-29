import React, {Component, PropTypes} from 'react';
import Immutable from 'immutable';

import {BetterTextField, VpPlainDropdown, PhoneNumber} from 'components';

const styles = {
  checkbox: { cursor: 'pointer', opacity: 0.8}
};
const paths = {
  phone: ['user', 'phone']
};

export default class CompanyAddManager extends Component {
  static propTypes = {
    // Form
    form: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Errors
    errors: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Change form
    changeForm: PropTypes.func.isRequired,
    // Branches
    branches: PropTypes.instanceOf(Immutable.List).isRequired,
    // Is in edit mode
    edit: PropTypes.bool
  };

  constructor() {
    super();

    this.setBranch = ::this.setBranch;
    this.selectOption = ::this.selectOption;
    this.setValue = ::this.setValue;
    this.changePhone = ::this.changePhone;
  }

  componentDidMount() {
    const {form, branches} = this.props;
    // Select first branch by default
    if (!form.get('branch')) {
      this.setBranch(branches.getIn([0, 'id']));
    }
  }

  /**
   * Set branch value
   * @param event
   */
  setBranch(event) {
    let branchId;
    if (typeof event === 'number') {
      branchId = event;
    } else {
      branchId = parseInt(event.target.value, 10);
    }
    this.props.changeForm(branchId, ['branch']);
  }

  /**
   * Select checkbox
   * @param event
   */
  selectOption(event) {
    const {changeForm, form} = this.props;
    const {target: {name}} = event;
    changeForm(!form.get(name), [name]);
  }

  /**
   * Set value of input
   * @param event
   */
  setValue(event) {
    const {changeForm} = this.props;
    const {target: {name, value}} = event;
    changeForm(value, ['user', name]);
  }

  /**
   * Change phone number
   * @param value
   */
  changePhone(value) {
    this.props.changeForm(value, ['user', 'phone']);
  }

  render() {
    const {form, errors, branches} = this.props;
    return (
      <div>
        <div className="row">
          <div className="col-md-6">
            <BetterTextField
              value={form.getIn(['user', 'firstName'])}
              error={errors.getIn(['user', 'firstName'])}
              label="First Name"
              name="firstName"
              onChange={this.setValue}
              required
            />
          </div>
          <div className="col-md-6">
            <BetterTextField
              value={form.getIn(['user', 'lastName'])}
              error={errors.getIn(['user', 'lastName'])}
              label="Last Name"
              name="lastName"
              onChange={this.setValue}
              required
            />
          </div>
        </div>
        <div className="row">
          <div className="col-md-6">
            <BetterTextField
              value={form.getIn(['user', 'username'])}
              error={errors.getIn(['user', 'username'])}
              label="Username"
              name="username"
              onChange={this.setValue}
              required
            />
          </div>
          <div className="col-md-6">
            <BetterTextField
              value={form.getIn(['user', 'password'])}
              error={errors.getIn(['user', 'password'])}
              label="Password"
              name="password"
              onChange={this.setValue}
              type="password"
              required
            />
          </div>
        </div>
        <div className="row">
          <div className="col-md-6">
            <PhoneNumber
              form={form.get('user')}
              errors={errors.get('user')}
              label="Phone Number"
              name="phone"
              propPath={paths.phone}
              setProp={this.changePhone}
              required
              noTimeout
            />
          </div>
          <div className="col-md-6">
            <BetterTextField
              value={form.getIn(['user', 'email'])}
              error={errors.getIn(['user', 'email'])}
              label="Email"
              name="email"
              onChange={this.setValue}
              required
            />
          </div>
        </div>
        <div className="row">
          <div className="col-md-12">
            <VpPlainDropdown
              options={branches}
              valueProp="id"
              onChange={this.setBranch}
              value={form.get('branch')}
              label="Branch"
              required
            />
          </div>
        </div>
        <div className="row">
          <div className="col-md-4">
            <label style={styles.checkbox}>
              <input name="notifyUser" type="checkbox" checked={form.get('notifyUser')} onChange={this.selectOption}/> Notify User
            </label>
          </div>
          <div className="col-md-4">
            <label style={styles.checkbox}>
              <input name="isRManager" type="checkbox" checked={form.get('isRManager')} onChange={this.selectOption}/> Set RFP Manager
            </label>
          </div>
          <div className="col-md-4">
            <label style={styles.checkbox}>
              <input name="isAdmin" type="checkbox" checked={form.get('isAdmin')} onChange={this.selectOption}/> Set Admin
            </label>
          </div>
        </div>
      </div>
    );
  }
}
