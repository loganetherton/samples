import React, {Component, PropTypes} from 'react';

import {connect} from 'react-redux';
import Immutable from 'immutable';

import {AppraiserSignUpForm, AmcSignUp, ManagerProfile} from 'containers';

import {ActionButton, NoAppraiserSelected} from 'components';

import {
  getAppraiserProfile,
  selectAppraiser,
  updateAppraiserProfile,
  appraiserValueChange,
  removeProp,
  setProp,
  updateProfileValue,
  uploadFile
} from 'redux/modules/company';

import {Dialog} from 'material-ui';

@connect(
  state => ({
    auth: state.auth,
    company: state.company,
    customer: state.customer,
    w9: state.w9
  }), {
    getAppraiserProfile,
    selectAppraiser,
    updateAppraiserProfile,
    appraiserValueChange,
    removeProp,
    setProp,
    updateProfileValue,
    uploadFile
  })
export default class Profile extends Component {
  static propTypes = {
    // Auth
    auth: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Company reducer
    company: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Customer
    customer: PropTypes.instanceOf(Immutable.Map).isRequired,
    // w9
    w9: PropTypes.instanceOf(Immutable.Map).isRequired,
    // URL
    location: PropTypes.object.isRequired,
    // Get appraiser profile after selection by manager
    getAppraiserProfile: PropTypes.func.isRequired,
    // Select appraiser as manager
    selectAppraiser: PropTypes.func.isRequired,
    // Update appraiser as manager
    updateAppraiserProfile: PropTypes.func.isRequired,
    // Update appraiser form values
    appraiserValueChange: PropTypes.func.isRequired,
    // Company remove prop
    removeProp: PropTypes.func.isRequired,
    // Company set prop
    setProp: PropTypes.func.isRequired,
    // Company update appraiser profile value
    updateProfileValue: PropTypes.func.isRequired,
    // Company upload file
    uploadFile: PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);

    this.closeUpdateFailDialog = ::this.closeUpdateFailDialog;
    this.closeUpdateDialog = ::this.closeUpdateDialog;
    this.openUpdateFailDialog = ::this.openUpdateFailDialog;
    this.openUpdateDialog = ::this.openUpdateDialog;
    this.managerSelectAppraiser = ::this.managerSelectAppraiser;

    this.state = {
      openUpdateDialog: false,
      openUpdateFailDialog: false,
      updateFailErrors: null
    };
  }

  openUpdateDialog() {
    this.setState({openUpdateDialog: true});
  }

  openUpdateFailDialog(errors) {
    this.setState({
      openUpdateFailDialog: true,
      updateFailErrors: errors
    });
  }

  closeUpdateDialog() {
    this.setState({openUpdateDialog: false});
  }

  closeUpdateFailDialog() {
    this.setState({
      openUpdateFailDialog: false,
      updateFailErrors: null
    });
  }

  /**
   * Select appraiser and query for that appraiser's info
   * @param appraiser
   */
  managerSelectAppraiser(appraiser) {
    const {getAppraiserProfile, company, selectAppraiser} = this.props;
    selectAppraiser(appraiser);
    getAppraiserProfile(company.getIn(['updateManager', 'staff', 'company', 'id']), appraiser.get('id'));
  }

  render() {
    const {
      auth,
      company,
      customer,
      location,
      updateAppraiserProfile,
      appraiserValueChange,
      removeProp,
      setProp,
      updateProfileValue,
      uploadFile
    } = this.props;
    const userType = auth.getIn(['user', 'type']);

    // No customer selected for current appraiser
    if (userType === 'customer' && !customer.get('selectedAppraiser')) {
      return <NoAppraiserSelected/>;
    }
    const companySelectedAppraiser = company.get('profileSelectedAppraiser');

    return (
      <div>
        {/* Appraiser profile */}
        {(userType === 'appraiser' || userType === 'customer' || companySelectedAppraiser.get('id')) &&
          <AppraiserSignUpForm
            location={location}
            profile
            openUpdateDialog={this.openUpdateDialog}
            openUpdateFailDialog={this.openUpdateFailDialog}
            customer={customer}
            isDisabled={userType === 'customer'}
            managerSelectedAppraiser={companySelectedAppraiser}
            isManager={userType === 'manager'}
            updateAsManager={updateAppraiserProfile}
            updateAppraiserValuesAsManager={appraiserValueChange}
            removePropCompany={removeProp}
            setPropCompany={setProp}
            updateProfileValue={updateProfileValue}
            uploadFileCompany={uploadFile}
          />
        }
        {/* AMC profile */}
        {userType === 'amc' &&
          <AmcSignUp
            profile
            openUpdateDialog={this.openUpdateDialog}
            openUpdateFailDialog={this.openUpdateFailDialog}
          />
        }
        {/* Manager profile */}
        {userType === 'manager' && !companySelectedAppraiser.get('id') &&
          <ManagerProfile
            openUpdateDialog={this.openUpdateDialog}
            openUpdateFailDialog={this.openUpdateFailDialog}
            managerSelectAppraiser={this.managerSelectAppraiser}
          />
        }
        {/*Update successful*/}
        <Dialog
          open={this.state.openUpdateDialog}
          actions={<ActionButton
            text="Close"
            type="submit"
            onClick={this.closeUpdateDialog}
          />}
          title="Account successfully updated"
        />
        {/*Update unsuccessful*/}
        <Dialog
          open={this.state.openUpdateFailDialog}
          actions={<ActionButton
            text="Close"
            type="cancel"
            onClick={this.closeUpdateFailDialog}
          />}
          title="Failed to update account"
        >
          {this.state.updateFailErrors}
        </Dialog>
      </div>
    );
  }
}
