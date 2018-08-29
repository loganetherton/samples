import React, {Component, PropTypes} from 'react';
import Immutable from 'immutable';
import {Dialog} from 'material-ui';
import classNames from 'classnames';

import {
  ActionButton,
  BranchDetails,
  CompanyAddManager,
  InviteAppraiser,
  CompanyUserPermissions,
  Confirm,
  BetterTextField
} from 'components';

import {addManagerInterface, inviteFormInterface} from 'redux/modules/company';

const externalStyles = require('./CompanyUsers.scss');

const columns = ['username', 'name', 'phone', 'email', 'branch', 'user type', 'set view permissions', 'set company manager', 'set rfp manager', 'set admin', 'action'];
const styles = {
  marginTop20: {marginTop: '20px'},
  marginRight10: {marginRight: '10px'},
  branchInfo: {display: 'inline', marginLeft: '10px'},
  branchName: {textTransform: 'uppercase', fontSize: '2em', display: 'inline'},
  branchDropdown: {width: '100%', border: 'none', fontSize: '12px', margin: 0, padding: 0, position: 'relative', background: 'none'},
  // === Table headers styles ===
  usernameHeader: {width: '8%'},
  nameHeader: {width: '12%'},
  phoneHeader: {width: '12%'},
  emailHeader: {width: '16%'},
  branchHeader: {width: '16%'},
  usertypeHeader: {width: '7%'},
  setviewpermissionsHeader: {width: '7%'},
  setcompanymanagerHeader: {width: '7%'},
  setrfpmanagerHeader: {width: '5%'},
  setadminHeader: {width: '5%'},
  actionHeader: {width: '5%'}
  // === /Table headers styles ===
};

export default class CompanyUsers extends Component {
  static propTypes = {
    // Branches
    branches: PropTypes.instanceOf(Immutable.List).isRequired,
    // Branch errors
    branchErrors: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Selected branch
    selectedBranch: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Set prop
    setProp: PropTypes.func.isRequired,
    // Staff
    staff: PropTypes.instanceOf(Immutable.List).isRequired,
    // Current user
    staffUser: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Upload a file
    uploadFile: PropTypes.func.isRequired,
    // Company ID
    companyId: PropTypes.number.isRequired,
    // Create branch
    createBranch: PropTypes.func.isRequired,
    // Patch branch
    patchBranch: PropTypes.func.isRequired,
    // Get branches
    getBranches: PropTypes.func.isRequired,
    // Reset branch input
    resetBranch: PropTypes.func.isRequired,
    // Update staff
    updateStaff: PropTypes.func.isRequired,
    // Add manager form
    addManagerForm: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Add manager errors
    addManagerErrors: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Add manager
    addManager: PropTypes.func.isRequired,
    // Invite form
    inviteForm: PropTypes.instanceOf(Immutable.Map).isRequired,
    // ASC search results
    ascSearchResults: PropTypes.instanceOf(Immutable.List).isRequired,
    // Invite asc selected
    ascSelected: PropTypes.bool,
    // Search ASC
    ascSearch: PropTypes.func.isRequired,
    // Invite errors
    inviteErrors: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Select asc
    selectAscAppraiser: PropTypes.func.isRequired,
    // Invite appraiser
    inviteAppraiser: PropTypes.func.isRequired,
    // Get user permissions
    getUserPermissions: PropTypes.func.isRequired,
    // Set permissions
    setUserPermissions: PropTypes.func.isRequired,
    // User viewing permissions
    permissionsSelectedUser: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Selected staff for whom permissions are enabled
    permissions: PropTypes.instanceOf(Immutable.List).isRequired,
    // Delete staff
    deleteStaff: PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);

    this.state = {
      showDetails: false,
      showAddManager: false,
      showInviteAppraiser: false,
      showPermissions: false,
      showConfirmation: false,
      selectedStaff: Immutable.Map(),
      selectedBranch: Immutable.Map(),
      phoneInputsDisabled: Immutable.Map(),
      emailInputsDisabled: Immutable.Map()
    };

    this.toggleInviteAppraiser = ::this.toggleInviteAppraiser;
    this.inviteAppraiser = ::this.inviteAppraiser;
    this.toggleRfpManager = ::this.toggleRfpManager;
    this.toggleCompanyManager = ::this.toggleCompanyManager;
    this.toggleAdmin = ::this.toggleAdmin;
    this.changeBranch = ::this.changeBranch;
    this.toggleBranchDetails = ::this.toggleBranchDetails;
    this.submitBranchDetails = ::this.submitBranchDetails;
    this.changeBranchDetails = ::this.changeBranchDetails;
    this.changeBranchDetailsEo = this.changeBranchDetails.bind(this, 'eo');
    this.changeDate = ::this.changeDate;
    this.changeDateEo = this.changeDate.bind(this, ['eo', 'expiresAt']);
    this.uploadBranchEoDoc = ::this.uploadBranchEoDoc;
    this.toggleAddManager = ::this.toggleAddManager;
    this.changeManagerForm = ::this.changeManagerForm;
    this.addManager = ::this.addManager;
    this.togglePermissions = ::this.togglePermissions;
    this.setPermissions = ::this.setPermissions;
    this.hideConfirmation = ::this.hideConfirmation;
    this.changeBranchDropdowns = Immutable.Map();
    this.phoneInputs = Immutable.Map();
    this.emailInputs = Immutable.Map();
  }

  /**
   * Organize staff by branch for display
   */
  componentDidMount() {
    this.organizedStaff = this.organizeStaffByBranch(this.props.staff);
    this.createChangeBranchDropdowns(this.props.staff);
    this.createInputs(this.props.staff, 'email');
    this.createInputs(this.props.staff, 'phone');
    this.createDeleteBindings(this.props.staff);
  }

  componentWillReceiveProps(nextProps) {
    if (nextProps.staff) {
      this.organizedStaff = this.organizeStaffByBranch(nextProps.staff);
      this.createChangeBranchDropdowns(nextProps.staff);
      this.createInputs(nextProps.staff, 'email');
      this.createInputs(nextProps.staff, 'phone');
      this.createDeleteBindings(nextProps.staff);
    }
  }

  /**
   * Creates the dropdown elements for moving a staff to a different branch
   *
   * @param {Immutable.List} staff
   */
  createChangeBranchDropdowns(staff) {
    this.changeBranchDropdowns = Immutable.Map();
    const {branches} = this.props;

    if (! staff) {
      staff = this.props.staff;
    }

    const branchOptions = Immutable.fromJS(branches.map(branch => {
      return Immutable.fromJS({value: branch.get('id'), name: branch.get('name')});
    }));

    staff.forEach(staff => {
      this.changeBranchDropdowns = this.changeBranchDropdowns.set(staff.get('id'), (
        <select
          onChange={this.updateConfirmationModal.bind(this, staff)}
          value={staff.getIn(['branch', 'id'])}
          style={styles.branchDropdown}
        >
          {branchOptions.map(branch => {
            return <option key={branch.get('value')} value={branch.get('value')}>{branch.get('name')}</option>;
          })}
        </select>
      ));
    });
  }

  /**
   * Creates input element for each staff
   *
   * @param {Immutable.List} staff
   * @param {String} inputType
   */
  createInputs(staff, inputType) {
    const key = inputType + 'Inputs';

    this[key] = Immutable.Map();

    if (! staff) {
      staff = this.props.staff;
    }

    staff.forEach((staff, index) => {
      this[key] = this[key].set(
        staff.get('id'),
        this.createTableInput(staff, index, inputType, this.state[key + 'Disabled'].get(staff.get('id'), true))
      );
    });
  }

  /**
   * Create an input element for a staff
   *
   * @param {Immutable.Map} staff
   * @param {Number} staffIndex The index of the staff object inside the list
   * @param {String} inputType 'email' or 'phone'
   * @param {Boolean} disabled
   * @returns {React.Element}
   */
  createTableInput(staff, staffIndex, inputType, disabled) {
    return (
      <form className="form-inline">
        <BetterTextField
          parentClass={'form-group ' + externalStyles['table-input-wrapper']}
          inputClass={'form-control ' + externalStyles['table-input']}
          value={staff.get(inputType)}
          onChange={this.updateStaffProp.bind(this, staffIndex, inputType)}
          enterFunction={this.submitTableInput.bind(this, staff, staffIndex, inputType)}
          disabled={disabled}
          appendAddon={
            <span
              className={classNames({
                'input-group-addon': true,
                'fa': true,
                'fa-edit': disabled,
                [externalStyles['table-input-addon']]: true
              })}
              onClick={this.enableTableInput.bind(this, staff, staffIndex, inputType)}
            >
            </span>
          }
        />
      </form>
    );
  }

  /**
   * Creates callbacks for deleting staff
   *
   * @param {Immutable.List} staff
   */
  createDeleteBindings(staff) {
    this.deleteStaff = {};

    staff.forEach(staff => {
      this.deleteStaff[staff.get('id')] = this.props.deleteStaff.bind(this, staff);
    });
  }

  /**
   * Updates the staff object inside the state tree
   *
   * @param {Number} staffIndex The index of the staff object inside the list
   * @param {String} inputType 'email' or 'phone'
   * @param {Object} event
   */
  updateStaffProp(staffIndex, inputType, event) {
    const {setProp} = this.props;
    setProp(event.target.value, 'staff', staffIndex, inputType);
  }

  /**
   * Enables an input element for editing
   *
   * @param {Number} staffId
   * @param {String} inputType 'email' or 'phone'
   */
  enableTableInput(staff, staffIndex, inputType) {
    const stateKey = [inputType + 'InputsDisabled'];
    this.setState({
      [stateKey]: this.state[stateKey].set(staff.get('id'), false)
    });

    this[inputType + 'Inputs'] = this[inputType + 'Inputs'].set(
      staff.get('id'), this.createTableInput(staff, staffIndex, inputType, false)
    );
  }

  /**
   * Submits the new input value to the server
   *
   * @param {Number} staffId
   * @param {String} inputType 'email' or 'phone'
   * @param {Object} event
   */
  submitTableInput(staff, staffIndex, inputType, event) {
    event.preventDefault();
    const {updateStaff} = this.props;
    updateStaff(staff.get('id'), {[inputType]: event.target.value});
    const stateKey = [inputType + 'InputsDisabled'];
    this.setState({
      [stateKey]: this.state[stateKey].set(staff.get('id'), true)
    });

    this[inputType + 'Inputs'] = this[inputType + 'Inputs'].set(
      staff.get('id'), this.createTableInput(staff, staffIndex, inputType, true)
    );
  }

  /**
   * Updates the content of the confirmation modal
   *
   * @param {Immutable.Map} staff
   * @param {Object} event
   */
  updateConfirmationModal(staff, event) {
    this.setState({
      selectedStaff: staff,
      selectedBranch: Immutable.Map({
        id: event.target.value,
        name: event.target.options[event.target.selectedIndex].innerHTML
      }),
      showConfirmation: true
    });
  }

  /**
   * Table header
   */
  header(label) {
    return (
      <div className="text-center">
        <label className="control-label" style={{ margin: 0 }}>
          <span>{label}</span>
        </label>
      </div>
    );
  }

  /**
   * Cell
   */
  cell(user, column, index) {
    const {staffUser} = this.props;

    const isAdmin = user.get('isAdmin');
    const isManager = user.get('isManager');
    const isRfpManager = user.get('isRfpManager');
    const isAppraiser = user.getIn(['user', 'type']) === 'appraiser';
    // If user is a manager and not an appraiser, cannot deselect manager. Also, only admins can set managers
    const setManagerDisabled = !staffUser.get('isAdmin') || (isManager && !isAppraiser);
    let cell = <div>Cell</div>;
    switch (column) {
      case 'username':
        cell = <div>{user.getIn(['user', 'username'])}</div>;
        break;
      case 'name':
        cell = <div>{user.getIn(['user', 'firstName'])} {user.getIn(['user', 'lastName'])}</div>;
        break;
      case 'phone':
        cell = this.phoneInputs.get(user.get('id'));
        break;
      case 'email':
        cell = this.emailInputs.get(user.get('id'));
        break;
      case 'branch':
        cell = (
          <div>
            {this.changeBranchDropdowns.get(user.get('id'))}
          </div>
        );
        break;
      case 'user type':
        let type;
        const thisUser = user.get('user');
        type = 'Appraiser';
        // Determine manager
        if (user.get('isManager')) {
          type = 'Manager';
        }
        // Determine appraiser or appraiser manager
        if (thisUser.get('type') === 'appraiser') {
          if (isAdmin || user.get('isManager')) {
            type = 'Appraiser Manager';
          } else {
            type = 'Appraiser';
          }
        }
        cell = <div>{type}</div>;
        break;
      case 'set view permissions':
        cell = isAppraiser && !isManager ? <div/> : <div className="link" onClick={this.togglePermissions.bind(this, user)}>Edit</div>;
        break;
      case 'set company manager':
        cell = <div><input type="checkbox" defaultChecked={isManager} disabled={setManagerDisabled} defaultValue={user.get('id')} onChange={this.toggleCompanyManager}/></div>;
        break;
      case 'set rfp manager':
        cell = <div><input type="checkbox" defaultChecked={isRfpManager} disabled={!staffUser.get('isAdmin')} defaultValue={user.get('id')} onChange={this.toggleRfpManager}/></div>;
        break;
      case 'set admin':
        cell = <div><input type="checkbox" defaultChecked={isAdmin} disabled={!staffUser.get('isAdmin')} defaultValue={user.get('id')} onChange={this.toggleAdmin}/></div>;
        break;
      case 'action':
        cell = <div className="link" onClick={this.deleteStaff[user.get('id')]}>Delete</div>;
        break;
    }
    return (
      <td key={index}>
        <div className="text-center">
          {cell}
        </div>
      </td>
    );
  }

  /**
   * Toggle invite dialog
   */
  toggleInviteAppraiser() {
    const newState = !this.state.showInviteAppraiser;
    this.setState({
      showInviteAppraiser: newState
    });
    // Close and clear
    if (!newState) {
      const {setProp} = this.props;
      setProp(Immutable.fromJS(inviteFormInterface), 'inviteForm');
      setProp(Immutable.List(), 'ascSearchResults');
      setProp(false, 'ascSelected');
      setProp(Immutable.fromJS(inviteFormInterface), 'inviteErrors');
    }
  }

  /**
   * Invite an appraiser
   */
  inviteAppraiser() {
    const {inviteAppraiser, inviteForm, companyId, setProp} = this.props;
    const data = {
      ascAppraiser: inviteForm.get('ascAppraiser'),
      email: inviteForm.get('email'),
      phone: inviteForm.get('phone'),
      requirements: inviteForm.get('requirements')
    };
    // Clear errors
    setProp(Immutable.fromJS(inviteFormInterface), 'inviteErrors');
    // Invite
    inviteAppraiser(companyId, inviteForm.get('branch'), data)
      .then(res => {
        if (!res.error) {
          this.toggleInviteAppraiser();
        }
      });
  }

  /**
   * Toggle permissions
   */
  togglePermissions(user) {
    const newState = !this.state.showPermissions;
    this.setState({
      showPermissions: newState
    });
    if (newState && user) {
      const {getUserPermissions, companyId, setProp} = this.props;
      setProp(user, 'permissionsSelectedUser');
      // Get permissions
      getUserPermissions(companyId, user.get('id'));
    }
  }

  /**
   * Reads from the state object, the selected staff and move the person into the
   * selected branch
   */
  changeBranch() {
    const {updateStaff} = this.props;
    const {selectedStaff, selectedBranch} = this.state;
    updateStaff(selectedStaff.get('id'), {branch: parseInt(selectedBranch.get('id'), 10)});
    this.setState({
      showConfirmation: false
    });
  }


  /**
   * Set permissions
   */
  setPermissions() {
    const {setUserPermissions, permissions, permissionsSelectedUser, companyId} = this.props;
    // Get permissions
    setUserPermissions(companyId, permissionsSelectedUser.get('id'), permissions.toJS())
    .then(res => {
      if (!res.error) {
        this.togglePermissions();
      }
    });
  }

  /**
   * Toggles RFP manager
   *
   * @param {Object} event
   */
  toggleRfpManager(event) {
    const {updateStaff} = this.props;
    updateStaff(event.target.value, {isRfpManager: event.target.checked});
  }

  /**
   * Toggles company manager
   *
   * @param {Object} event
   */
  toggleCompanyManager(event) {
    const {updateStaff} = this.props;
    updateStaff(event.target.value, {isManager: event.target.checked});
  }

  /**
   * Toggles admin
   *
   * @param {Object} event
   */
  toggleAdmin(event) {
    const {updateStaff} = this.props;
    updateStaff(event.target.value, {isAdmin: event.target.checked});
  }

  /**
   * Toggle company details dialog
   */
  toggleBranchDetails(branchId) {
    const {branches, setProp, resetBranch} = this.props;
    const newState = {
      showDetails: !this.state.showDetails
    };

    if (newState.showDetails) {
      resetBranch();
    }

    if (branchId) {
      setProp(branches.filter(branch => branch.get('id') === branchId).get(0), 'selectedBranch');
    } else {
      resetBranch();
    }
    this.setState(newState);
  }

  /**
   * Format data for posting
   */
  formatBranchData(branch) {
    const data = branch.toJS();
    if (data.eo) {
      data.eo.claimAmount = parseFloat(data.eo.claimAmount);
      data.eo.aggregateAmount = parseFloat(data.eo.aggregateAmount);
      data.eo.deductible = parseFloat(data.eo.deductible);
    }
    return data;
  }

  /**
   * Patch or create branch
   */
  submitBranchDetails() {
    const {selectedBranch, createBranch, companyId, patchBranch, getBranches} = this.props;
    // Patch
    if (selectedBranch.get('id')) {
      patchBranch(companyId, selectedBranch.get('id'), this.formatBranchData(selectedBranch)).then(res => {
        if (! res.error) {
          this.toggleBranchDetails();
          getBranches(companyId);
        }
      });
    // Create
    } else {
      createBranch(companyId, selectedBranch.toJS()).then(res => {
        if (! res.error) {
          this.toggleBranchDetails();
          getBranches(companyId);
        }
      });
    }
  }

  /**
   * Organize staff into branches for display
   * @param staff Staff list
   */
  organizeStaffByBranch(staff) {
    let branchMap = Immutable.Map();
    staff.forEach(staffMember => {
      const branchId = staffMember.getIn(['branch', 'id']);
      // Add to list
      if (branchMap.get(branchId)) {
        branchMap = branchMap.setIn([branchId], branchMap.get(branchId).push(staffMember));
      // Create list
      } else {
        branchMap = branchMap.set(branchId, Immutable.List().push(staffMember));
      }
    });
    return branchMap;
  }

  /**
   * Change branch details
   * @param prepend Value to prepend to propPath
   * @param event SyntheticEvent
   */
  changeBranchDetails(prepend, event) {
    const {selectedBranch, setProp} = this.props;
    if (typeof prepend !== 'string') {
      event = Object.assign({}, prepend);
      prepend = null;
    }

    if (typeof prepend === 'string' && ! event) {
      // Handles state change
      setProp(prepend, 'selectedBranch', 'state');
    } else {
      const {target: {name, value}} = event;
      if (prepend) {
        // Init the map if one doesn't exist
        if (!selectedBranch.get(prepend)) {
          setProp(Immutable.Map(), 'selectedBranch', prepend);
        }
        setProp(value, 'selectedBranch', prepend, name);
      } else {
        setProp(value, 'selectedBranch', name);
      }
    }
  }

  /**
   * Upload branch EO document
   * @param files
   */
  uploadBranchEoDoc(files) {
    this.props.uploadFile(['selectedBranch', 'eo', 'document'], files[0]);
  }

  /**
   * Change date value
   * @param path Prop path
   * @param value Value
   */
  changeDate(path, value) {
    path = path.slice();
    path.unshift('selectedBranch');
    this.props.setProp(value, ...path);
  }

  /**
   * Toggle add manager dialog
   */
  toggleAddManager() {
    const showAddManager = !this.state.showAddManager;
    this.setState({
      showAddManager
    });
    // Reset form on hide
    if (!showAddManager) {
      this.props.setProp(Immutable.fromJS(addManagerInterface), 'addManagerForm');
    }
  }

  /**
   * Change manager form values
   * @param value New value
   * @param propPath Prop path array
   */
  changeManagerForm(value, propPath) {
    this.props.setProp(value, 'addManagerForm', ...propPath);
  }

  /**
   * Add manager then close the form
   */
  addManager() {
    this.props.addManager()
    .then(res => {
      if (!res.error) {
        this.toggleAddManager();
      }
    });
  }

  /**
   * Hides the confirmation modal
   */
  hideConfirmation() {
    this.setState({
      showConfirmation: false
    });
  }

  /**
   * Get the confirmation modal body
   *
   * @returns {React.Element}
   */
  getConfirmationBody() {
    return (
      <p>
        Are you sure you want to move&nbsp;
        <strong>{this.state.selectedStaff.getIn(['user', 'displayName'])}</strong>&nbsp;
        to <strong>{this.state.selectedBranch.get('name')}</strong>?
      </p>
    );
  }

  render() {
    const {
      branches,
      branchErrors,
      selectedBranch,
      staffUser,
      addManagerForm,
      addManagerErrors,
      inviteForm,
      ascSearchResults,
      ascSelected,
      setProp,
      ascSearch,
      inviteErrors,
      selectAscAppraiser,
      permissionsSelectedUser,
      permissions
    } = this.props;
    const {showDetails, showAddManager, showInviteAppraiser, showPermissions} = this.state;
    // Permissions
    const isAdmin = staffUser.get('isAdmin');
    const isManager = staffUser.get('isManager');
    let staffForPermissions = Immutable.Map();
    if (this.organizedStaff) {
      staffForPermissions = this.organizedStaff.map(branch => {
        return branch.filter(user => {
          return user.getIn(['user', 'type']) === 'appraiser' && user.get('id') !== permissionsSelectedUser.get('id');
        });
      }).filter(branch => branch.count());
    }
    return (
      <div style={styles.marginTop20}>
        <div className="row">
          <div className="col-md-12">
            <div className="pull-right">
              <div>
                {(isManager || isAdmin) &&
                 <ActionButton
                   type="submit"
                   text="Invite Appraiser"
                   onClick={this.toggleInviteAppraiser}
                   style={styles.marginRight10}
                 />
                }
                {isAdmin &&
                 <ActionButton
                   type="submit"
                   text="Add Manager"
                   onClick={this.toggleAddManager}
                   style={styles.marginRight10}
                 />
                }
                {isAdmin &&
                 <ActionButton
                   type="submit"
                   text="Add Branch"
                   onClick={this.toggleBranchDetails}
                 />
                }
              </div>
            </div>
          </div>
        </div>
        {this.organizedStaff && branches.map((branch, index) => {
          const staffThisBranch = this.organizedStaff.get(branch.get('id'));
          return (
            <div key={index} style={{marginTop: '25px'}}>
              <div className="row">
                <div className="col-md-12">
                  <div style={styles.branchName}>{branch.get('name')}</div>
                  <div className="link" style={styles.branchInfo} onClick={this.toggleBranchDetails.bind(this, branch.get('id'))}>Branch Info</div>
                </div>
              </div>
              <div className="row">
                <div className="col-md-12">
                  <table className="data-table" style={{ width: '100%', marginTop: '10px' }}>
                    <thead>
                    <tr key="columns">
                      {columns.map((column) => {
                        return (
                          <th key={column} style={styles[column.replace(/\s/g, '') + 'Header']}>
                            {this.header(column)}
                          </th>
                        );
                      })}
                    </tr>
                    </thead>
                    <tbody>
                    {Immutable.List.isList(staffThisBranch) && staffThisBranch.map((user, rowIndex) => {
                      return (
                        <tr key={ 'row_' + rowIndex}>
                          {columns.map((column, colIndex) => this.cell(user, column, colIndex))}
                        </tr>
                      );
                    })}
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          );
        })}
        {/*Update branch*/}
        <Dialog
          open={showDetails}
          actions={[
            <ActionButton
              type="cancel"
              text="Close"
              onClick={this.toggleBranchDetails}
            />,
            <ActionButton
              type="submit"
              text={selectedBranch.get('id') ? 'Update' : 'Create'}
              onClick={this.submitBranchDetails}
              style={{marginLeft: '10px'}}
            />
          ]}
          title="Branch Info"
          autoScrollBodyContent
        >
          <BranchDetails
            form={selectedBranch}
            changeForm={this.changeBranchDetails}
            errors={branchErrors}
            update={this.submitBranchDetails}
            changeEoDate={this.changeDateEo}
            changeEoDetails={this.changeBranchDetailsEo}
            uploadEoDoc={this.uploadBranchEoDoc}
          />
        </Dialog>
        {/*Add manager*/}
        <Dialog
          open={showAddManager}
          actions={[
            <ActionButton
              type="cancel"
              text="Close"
              onClick={this.toggleAddManager}
            />,
            <ActionButton
              type="submit"
              text="Create"
              onClick={this.addManager}
              style={{marginLeft: '10px'}}
            />
          ]}
          title="Add Manager"
          autoScrollBodyContent
        >
          <CompanyAddManager
            form={addManagerForm}
            errors={addManagerErrors}
            changeForm={this.changeManagerForm}
            branches={branches}
          />
        </Dialog>
        {/*Invite appraiser*/}
        <Dialog
          open={showInviteAppraiser}
          actions={[
            <ActionButton
              type="cancel"
              text="Close"
              onClick={this.toggleInviteAppraiser}
            />,
            <ActionButton
              type="submit"
              text="Create"
              onClick={this.inviteAppraiser}
              style={{marginLeft: '10px'}}
            />
          ]}
          title="Invite Appraiser"
          autoScrollBodyContent
        >
          <InviteAppraiser
            form={inviteForm}
            errors={inviteErrors}
            ascSearchResults={ascSearchResults}
            ascSelected={ascSelected}
            setProp={setProp}
            ascSearch={ascSearch}
            branches={branches}
            selectAscAppraiser={selectAscAppraiser}
          />
        </Dialog>
        {/*Permissions*/}
        {staffForPermissions.count() &&
          <Dialog
            open={showPermissions}
            actions={[<ActionButton
                        type="cancel"
                        text="Close"
                        onClick={this.togglePermissions}
                      />, <ActionButton
                        type="submit"
                        text="Submit"
                        onClick={this.setPermissions}
                        style={{marginLeft: '10px'}}
                      />]}
            title="Set View Permissions"
            autoScrollBodyContent
          >
            <CompanyUserPermissions
              branchesWithStaff={staffForPermissions}
              selectedUser={permissionsSelectedUser}
              permissions={permissions}
              setProp={setProp}
            />
          </Dialog>
        }
        <Confirm
          title="Move to a Different Branch"
          show={this.state.showConfirmation}
          hide={this.hideConfirmation}
          submit={this.changeBranch}
          body={this.getConfirmationBody()}
        />
      </div>
    );
  }
}
