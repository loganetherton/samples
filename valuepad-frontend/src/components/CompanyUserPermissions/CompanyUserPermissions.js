import React, {Component, PropTypes} from 'react';
import Immutable from 'immutable';

const styles = {
  marginRight6: {marginRight: '6px'}
};

export default class CompanyUserPermissions extends Component {
  static propTypes = {
    // Staff organized by branch
    branchesWithStaff: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Selected user
    selectedUser: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Selected staff members for whom permissions are set
    permissions: PropTypes.instanceOf(Immutable.List).isRequired,
    // Set prop
    setProp: PropTypes.func.isRequired
  };

  constructor() {
    super();

    this.checkBranch = ::this.checkBranch;
    this.checkUser = ::this.checkUser;
    this.createCheckBoxes = ::this.createCheckBoxes;

    this.branchChecks = [];
    this.userChecks = [];
    this.flatStaffArray = Immutable.List();
  }

  componentDidMount() {
    const {branchesWithStaff, permissions} = this.props;
    this.createCheckBoxes(branchesWithStaff, permissions);
  }

  /**
   * Recreate checkboxes on staff change
   * @param nextProps
   */
  componentWillReceiveProps(nextProps) {
    const {branchesWithStaff, permissions} = this.props;
    const {branchesWithStaff: nextStaff, permissions: nextPermissions} = nextProps;
    if (!Immutable.is(branchesWithStaff, nextStaff) || !Immutable.is(permissions, nextPermissions)) {
      this.createCheckBoxes(nextStaff, nextPermissions);
    }
  }

  /**
   * Sort branches by name
   * @param branches
   */
  sortBranches(branches) {
    // Sort branches
    return branches.sort((curr, next) => {
      const thisBranch = curr.getIn([0, 'branch', 'name']).toLowerCase();
      const nextBranch = next.getIn([0, 'branch', 'name']).toLowerCase();
      if (thisBranch === nextBranch) {
        return 0;
      }
      return thisBranch < nextBranch ? -1 : 1;
    });
  }

  /**
   * Sort staff by name
   * @param staff
   */
  sortStaff(staff) {
    return staff.sort((curr, next) => {
      curr = curr.getIn(['user', 'displayName']).toLowerCase();
      next = next.getIn(['user', 'displayName']).toLowerCase();
      if (curr === next) {
        return 0;
      }
      return curr < next ? -1 : 1;
    });
  }

  /**
   * Create branch checkboxes
   * @param branches Branches
   * @param permissions Staff
   */
  createBranchChecks(branches, permissions) {
    return branches.map((branch, index) => {
      const userIdsThisBranch = branch.map(branch => branch.get('id')).filter(id => !permissions.includes(id));
      const thisBranch = branch.getIn([0, 'branch']);
      const checked = !userIdsThisBranch.count();
      return (
        <div key={index}>
          <label style={{ cursor: 'pointer' }}>
            <input type="checkbox" checked={checked} onChange={this.checkBranch.bind(this, thisBranch, checked)} style={styles.marginRight6}/>
            {thisBranch.get('name')}
          </label>
        </div>
      );
    }).toList();
  }

  /**
   * Create user checkboxes
   * @param branches Branches
   * @param permissions Selected users
   */
  createUserChecks(branches, permissions) {
    this.flatStaffArray = Immutable.List();
    // Extract staff
    branches.forEach(branch => {
      return branch.forEach(user => {
        this.flatStaffArray = this.flatStaffArray.push(user);
      });
    });
    // Create checkboxes
    return this.sortStaff(this.flatStaffArray).map((staff, index) => {
      return (
        <div key={index}>
          <label style={{ cursor: 'pointer' }}>
            <input type="checkbox" checked={permissions.includes(staff.get('id'))} onChange={this.checkUser.bind(this, staff)} style={styles.marginRight6}/>
            {staff.getIn(['user', 'displayName'])}
          </label>
        </div>
      );
    });
  }

  /**
   * Create user and branch checkboxes
   * @param branches Branches with users
   * @param permissions Staff for whom permissions are set
   */
  createCheckBoxes(branches, permissions) {
    branches = this.sortBranches(branches);
    // Branches list
    this.branchChecks = this.createBranchChecks(branches, permissions);
    // Users list
    this.userChecks = this.createUserChecks(branches, permissions);
  }

  /**
   * Check/uncheck branch
   * @param branch Branch
   * @param checked Currently checked
   */
  checkBranch(branch, checked) {
    const {branchesWithStaff, setProp} = this.props;
    let permissions = this.props.permissions;
    const staffThisBranch = this.flatStaffArray
      .filter(staff => {
        return staff.getIn(['branch', 'id']) === branch.get('id');
      })
      .map(staff => {
        return staff.get('id');
      });
    // Uncheck all staff
    if (checked) {
      permissions = permissions.filter(id => !staffThisBranch.includes(id));
    // Check all staff
    } else {
      permissions = permissions.concat(staffThisBranch).toSet().toList();
    }
    setProp(permissions, 'permissions');
    this.createCheckBoxes(branchesWithStaff, permissions);
  }

  /**
   * Check/uncheck user
   * @param user User
   */
  checkUser(user) {
    const {setProp, branchesWithStaff} = this.props;
    let permissions = this.props.permissions;
    const userId = user.get('id');
    // If user ID in the array, remove
    if (permissions.includes(userId)) {
      permissions = permissions.filter(id => id !== userId);
    } else {
      permissions = permissions.push(userId);
    }
    // Update
    setProp(permissions, 'permissions');
    this.createCheckBoxes(branchesWithStaff, permissions);
  }

  render() {
    const {selectedUser} = this.props;
    const thisUser = selectedUser.get('user');
    return (
      <div>
        <div className="row">
          <div className="col-md-12">
            {thisUser.get('displayName')} can see orders assigned to the branches and users below.
          </div>
        </div>
        <div className="row">
          <div className="col-md-6">
            <h2>Branches</h2>
            {this.branchChecks}
          </div>
          <div className="col-md-6">
            <h2>Users</h2>
            {this.userChecks}
          </div>
        </div>
      </div>
    );
  }
}
