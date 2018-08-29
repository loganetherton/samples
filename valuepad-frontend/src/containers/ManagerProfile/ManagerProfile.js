import React, {Component, PropTypes} from 'react';
import {connect} from 'react-redux';
import Immutable from 'immutable';

import {ActionButton, BetterTextField, PhoneNumber, ManagerSwitchAppraiser} from 'components';

import {getManager, getStaff, updateManager, setProp} from 'redux/modules/company';

const phonePath = ['updateManager', 'phone'];

const style = {
  errorList: {listStyle: 'none'}
};

@connect(
  state => ({
    auth: state.auth,
    company: state.company
  }),
  {
    getManager,
    getStaff,
    setProp,
    updateManager,
  }
)
export default class ManagerProfile extends Component {
  static propTypes = {
    // Auth reducer
    auth: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Company reducer
    company: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Open successful update dialog
    openUpdateDialog: PropTypes.func.isRequired,
    // Open failed update dialog
    openUpdateFailDialog: PropTypes.func.isRequired,
    // Get manager
    getManager: PropTypes.func.isRequired,
    // Get staff for a company
    getStaff: PropTypes.func.isRequired,
    // Update manager
    updateManager: PropTypes.func.isRequired,
    // Set prop on company reducer
    setProp: PropTypes.func.isRequired,
    // Select appraiser manager
    managerSelectAppraiser: PropTypes.func.isRequired
  };

  constructor() {
    super();

    this.updateManager = ::this.updateManager;
    this.setValue = ::this.setValue;
  }

  componentDidMount() {
    const {auth, getManager, getStaff} = this.props;

    // Get manager and then company staff
    getManager(auth.getIn(['user', 'id']))
    .then(res => {
      getStaff(res.result.staff.company.id);
    });
  }

  /**
   * Save changes to the manager profile
   */
  updateManager() {
    const {auth, company, updateManager, openUpdateDialog, openUpdateFailDialog} = this.props;

    updateManager(auth.getIn(['user', 'id']), company.get('updateManager').toJS()).then(res => {
      if (!res.error) {
        openUpdateDialog();
      } else {
        openUpdateFailDialog(this.renderErrors(Immutable.fromJS(res.error.errors)));
      }
    });
  }

  /**
   * Change input value
   *
   * @param {Object} event
   */
  setValue(event) {
    const {setProp} = this.props;
    const {target: {name, value}} = event;
    setProp(value, 'updateManager', name);
  }

  /**
   * Render error messages from the back-end
   *
   * @param {Immutable.List} errors
   * @return {React.Element}
   */
  renderErrors(errors) {
    return (
      <ul style={style.errorList}>
        {errors.map((error, index) => (
          <li key={index}>
            <strong>{error.get('message')}</strong>
          </li>
        ))}
      </ul>
    );
  }

  render() {
    const {company, setProp, managerSelectAppraiser} = this.props;

    return (
      <div>
        <ManagerSwitchAppraiser
          selectAppraiser={managerSelectAppraiser}
          company={company}
        />
        <div className="row">
          <div className="col-md-6">
            <BetterTextField
              value={company.getIn(['updateManager', 'firstName'])}
              error={company.getIn(['updateManagerErrors', 'firstName'])}
              label="First Name"
              placeholder="First Name"
              name="firstName"
              onChange={this.setValue}
              enterFunction={this.updateManager}
              required
            />
          </div>
          <div className="col-md-6">
            <BetterTextField
              value={company.getIn(['updateManager', 'lastName'])}
              error={company.getIn(['updateManagerErrors', 'lastName'])}
              label="Last Name"
              placeholder="Last Name"
              name="lastName"
              onChange={this.setValue}
              enterFunction={this.updateManager}
              required
            />
          </div>
        </div>
        <div className="row">
          <div className="col-md-6">
            <PhoneNumber
              form={company.get('updateManager')}
              errors={company.get('updateManagerErrors')}
              label="Phone Number"
              name="phone"
              propPath={phonePath}
              setProp={setProp}
              enterFunction={this.updateManager}
              required
              noTimeout
            />
          </div>
          <div className="col-md-6">
            <BetterTextField
              value={company.getIn(['updateManager', 'email'])}
              error={company.getIn(['updateManagerErrors', 'email'])}
              label="Email"
              placeholder="Email"
              name="email"
              onChange={this.setValue}
              enterFunction={this.updateManager}
              required
            />
          </div>
        </div>
        <div className="row">
          <div className="col-md-12 text-center">
            <ActionButton
              type="submit"
              text="Update profile"
              onClick={this.updateManager}
            />
          </div>
        </div>
      </div>
    );
  }
}
