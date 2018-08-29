import React, {Component, PropTypes} from 'react';
import Immutable from 'immutable';

import {AscSearch, VpPlainDropdown, BetterTextField, PhoneNumber} from 'components';

const phonePropPath = ['phone'];

export default class InviteAppraiser extends Component {
  static propTypes = {
    // Invite form
    form: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Errors
    errors: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Asc results
    ascSearchResults: PropTypes.instanceOf(Immutable.List).isRequired,
    // Appraiser selected
    ascSelected: PropTypes.bool,
    // Set prop
    setProp: PropTypes.func.isRequired,
    // Search ASC
    ascSearch: PropTypes.func.isRequired,
    // Branches
    branches: PropTypes.instanceOf(Immutable.List).isRequired,
    // Select asc
    selectAscAppraiser: PropTypes.func.isRequired
  };

  constructor() {
    super();
    this.changeAsc = ::this.changeAsc;
    this.changeAscState = ::this.changeAscState;
    this.selectAscAppraiser = ::this.selectAscAppraiser;
    this.setValue = ::this.setValue;
    this.setBranch = ::this.setBranch;
    this.setPhone = ::this.setPhone;
    this.setRequirements = ::this.setRequirements;
  }

  componentDidMount() {
    const {setProp, branches} = this.props;
    setProp(branches.getIn([0, 'id']), 'inviteForm', 'branch');
  }

  /**
   * Do ASC search
   * @param event
   */
  changeAsc(event) {
    const {target: {value}} = event;
    this.props.setProp(value, 'inviteForm', 'licenseNumber');
    this.performSearch();
  }

  /**
   * Change ASC state
   * @param stateVal
   * @param state
   */
  changeAscState(stateVal, state) {
    this.props.setProp(state, 'inviteForm', 'licenseState');
    this.performSearch();
  }

  setValue(event) {
    const {target: {value, name}} = event;
    this.props.setProp(value, 'inviteForm', name);
  }

  /**
   * Set phone number
   * @param value Incoming value
   */
  setPhone(value) {
    this.props.setProp(value, 'inviteForm', 'phone');
  }

  /**
   * Set requirements for accepting the invitation
   *
   * @param event
   */
  setRequirements(event) {
    this.props.setProp(event.target.checked, 'inviteForm', 'requirements', event.target.name);
  }

  /**
   * Set a branch
   * @param event
   */
  setBranch(event) {
    const {target: {value}} = event;
    this.props.setProp(parseInt(value, 10), 'inviteForm', 'branch');
  }

  /**
   * Perform ASC search
   */
  performSearch() {
    const {ascSearch, form} = this.props;
    if (form.get('licenseState') && form.get('licenseNumber')) {
      ascSearch(form);
    }
  }

  /**
   * Select ASC
   * @param appraiser
   */
  selectAscAppraiser(appraiser) {
    this.props.selectAscAppraiser(appraiser);
  }

  render() {
    const {form, ascSearchResults, ascSelected, errors, branches} = this.props;
    return (
      <div>
        <div className="row">
          <div className="col-md-12">
            <AscSearch
              form={form}
              formChange={this.changeAsc}
              stateChange={this.changeAscState}
              ascSelected={ascSelected}
              results={ascSearchResults}
              stateProp="licenseState"
              licenseProp="licenseNumber"
              withDividers
              selectFunction={this.selectAscAppraiser}
              withHeader={false}
              error={typeof errors.get('ascAppraiser') === 'string' ? errors.get('ascAppraiser') : ''}
            />
          </div>
        </div>
        <div className="row">
          <div className="col-md-6">
            <BetterTextField
              value={form.get('firstName')}
              error={errors.get('firstName')}
              label="First Name"
              name="firstName"
              disabled
              required
            />
          </div>
          <div className="col-md-6">
            <BetterTextField
              value={form.get('lastName')}
              error={errors.get('lastName')}
              label="Last Name"
              name="lastName"
              disabled
              required
            />
          </div>
        </div>
        <div className="row">
          <div className="col-md-6">
            <PhoneNumber
              form={form}
              errors={errors}
              propPath={phonePropPath}
              label="Phone Number"
              name="phone"
              setProp={this.setPhone}
              required
              noTimeout
              disabled={!ascSelected}
            />
          </div>
          <div className="col-md-6">
            <BetterTextField
              value={form.get('email')}
              error={errors.get('email')}
              label="Email"
              name="email"
              onChange={this.setValue}
              required
              disabled={!ascSelected}
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
              disabled={!ascSelected}
            />
          </div>
        </div>
        <div className="row">
          <div className="col-md-12 form-group">
            <div className="col-md-4">
              <label className="control-label">
                <input type="checkbox" name="ach" checked={form.getIn(['requirements', 'ach'], false)} onChange={this.setRequirements} /> ACH Required
              </label>
            </div>
            <div className="col-md-4">
              <label className="control-label">
                <input type="checkbox" name="sample-reports" checked={form.getIn(['requirements', 'sample-reports'], false)} onChange={this.setRequirements} /> Sample reports required
              </label>
            </div>
            <div className="col-md-4">
              <label className="control-label">
                <input type="checkbox" name="resume" checked={form.getIn(['requirements', 'resume'], false)} onChange={this.setRequirements} /> Resume required
              </label>
            </div>
          </div>
        </div>
      </div>
    );
  }
}
