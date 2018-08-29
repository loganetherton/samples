import React, {Component, PropTypes} from 'react';

import {InvitationAccept} from 'components';

import Immutable from 'immutable';

import {updateAppraiserForInvitation, submitJobTypesInvitations} from 'helpers/genericFunctions';

/**
 * Dialog for accepting an invitation with an order
 */
export default class OrderInvitation extends Component {
  static propTypes = {
    // Invitations
    invitations: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Whether to show the dialog
    show: PropTypes.bool.isRequired,
    // Hide dialog
    hide: PropTypes.func.isRequired,
    // Set property
    setProp: PropTypes.func.isRequired,
    // Orders
    orders: PropTypes.instanceOf(Immutable.Map),
    // Submit ACH
    submitAch: PropTypes.func.isRequired,
    // User id
    userId: PropTypes.number.isRequired,
    // Upload file
    uploadFile: PropTypes.func.isRequired,
    // Update appraiser
    updateAppraiser: PropTypes.func.isRequired,
    // Select job type
    selectJobType: PropTypes.func.isRequired,
    // Create job type request
    createJobTypeRequest: PropTypes.func.isRequired,
    // Set fee value
    setFeeValue: PropTypes.func.isRequired,
    // Apply default fees
    applyDefaultFees: PropTypes.func.isRequired,
    // Save job type fees
    saveJobTypeFees: PropTypes.func.isRequired,
    // Accept invitation
    acceptInvitation: PropTypes.func.isRequired,
    // Currently selected invitation
    selectedInvitation: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Remove prop (for sign up samples)
    removeProp: PropTypes.func.isRequired,
    // Job type reducer
    jobType: PropTypes.instanceOf(Immutable.Map).isRequired,
    sortColumn: PropTypes.func.isRequired,
    changeSearchValue: PropTypes.func.isRequired
  };

  /**
   * Submit ACH form
   */
  submitAch() {
    const {invitations, submitAch, userId} = this.props;
    submitAch(userId, invitations.getIn(['ach', 'form']).toJS());
  }

  /**
   * Accept selected invitation
   */
  acceptInvitation() {
    const {acceptInvitation, selectedInvitation, userId} = this.props;
    acceptInvitation(userId, selectedInvitation.get('id'));
  }

  render() {
    const {
      show,
      hide,
      selectedInvitation = Immutable.Map(),
      invitations,
      setProp,
      uploadFile,
      selectJobType,
      setFeeValue,
      applyDefaultFees,
      createJobTypeRequest,
      removeProp,
      jobType,
      sortColumn,
      changeSearchValue
    } = this.props;

    return (
      <InvitationAccept
        requirements={selectedInvitation.get('requirements') || Immutable.List()}
        metRequirements={invitations.get('metRequirements') || Immutable.Map()}
        selectedInvitation={selectedInvitation}
        selectJobType={selectJobType}
        show={show}
        hide={hide}
        saveJobTypeFees={submitJobTypesInvitations.bind(this, createJobTypeRequest)}
        invitations={invitations}
        setFeeValue={setFeeValue}
        setProp={setProp}
        submitAch={::this.submitAch}
        uploadFile={uploadFile}
        updateAppraiser={updateAppraiserForInvitation.bind(this)}
        acceptInvitation={::this.acceptInvitation}
        applyDefaultFees={applyDefaultFees}
        title="Accept invitation"
        removeProp={removeProp}
        jobType={jobType}
        sortColumn={sortColumn}
        changeSearchValue={changeSearchValue}
      />
    );
  }
}
