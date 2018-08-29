import React, {Component, PropTypes} from 'react';
import {connect} from 'react-redux';
import Immutable from 'immutable';
import moment from 'moment';
import ReactTooltip from 'react-tooltip';

import {
  NoData,
  InvitationAccept,
  InvitationDecline
} from 'components';

import {
  getInvitations,
  getCompanyInvitations,
  setProp,
  acceptInvitation,
  declineInvitation,
  getAppraiser,
  getAch,
  getCustomerJobTypes,
  getJobTypes,
  getCustomerFees,
  selectJobType,
  setFeeValue,
  saveJobTypeFees,
  submitAch,
  uploadFile,
  updateAppraiser,
  getDefaultFees,
  applyDefaultFees,
  createJobTypeRequest,
  removeProp,
  getPendingInvitationsTotal
} from 'redux/modules/invitations';

import {getOrderQueue, setProp as setOrdersProp} from 'redux/modules/orders';

import {updateAppraiserForInvitation, submitJobTypesInvitations} from 'helpers/genericFunctions';

import {changeSearchValue, sortColumn} from 'redux/modules/invitations';

import ReactPaginate from 'react-paginate';

const pageRange = 5;
const marginPages = 2;

const styles = {table: { width: '100%', marginTop: '10px' }, tooltipOffset: {top: 18}};

@connect(
  state => ({
    invitations: state.invitations,
    auth: state.auth,
    orders: state.orders,
    appraiser: state.appraiser
  }), {
    getInvitations,
    getCompanyInvitations,
    setProp,
    acceptInvitation,
    declineInvitation,
    getAppraiser,
    getAch,
    getCustomerJobTypes,
    getCustomerFees,
    getJobTypes,
    selectJobType,
    setFeeValue,
    saveJobTypeFees,
    submitAch,
    uploadFile,
    updateAppraiser,
    getDefaultFees,
    applyDefaultFees,
    removeProp,
    changeSearchValue,
    sortColumn,
    getOrderQueue,
    setOrdersProp,
    getPendingInvitationsTotal
  })
export default class Invitations extends Component {
  static propTypes = {
    // Invitations
    invitations: PropTypes.instanceOf(Immutable.Map),
    // Auth
    auth: PropTypes.instanceOf(Immutable.Map),
    // Retrieve invitations on load
    getInvitations: PropTypes.func.isRequired,
    // Retrieve company invitations
    getCompanyInvitations: PropTypes.func.isRequired,
    // Set a property explicitly
    setProp: PropTypes.func.isRequired,
    // Accept an invitation
    acceptInvitation: PropTypes.func.isRequired,
    // Decline an invitation
    declineInvitation: PropTypes.func.isRequired,
    // Get ACH
    getAch: PropTypes.func.isRequired,
    // Get appraiser
    getAppraiser: PropTypes.func.isRequired,
    // Get job types this customer
    getCustomerJobTypes: PropTypes.func.isRequired,
    // Get customer fees
    getCustomerFees: PropTypes.func.isRequired,
    // Get default fees
    getDefaultFees: PropTypes.func.isRequired,
    // Apply default fees
    applyDefaultFees: PropTypes.func.isRequired,
    // Get job types
    getJobTypes: PropTypes.func.isRequired,
    // Set job type fee values
    saveJobTypeFees: PropTypes.func.isRequired,
    // Submit ACH
    submitAch: PropTypes.func.isRequired,
    // Update appraiser
    updateAppraiser: PropTypes.func.isRequired,
    // Remove prop (for samples)
    removeProp: PropTypes.func.isRequired,
    // Change job type filter
    changeSearchValue: PropTypes.func.isRequired,
    // Sort job type columns
    sortColumn: PropTypes.func.isRequired,
    // Orders
    orders: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Get order queue
    getOrderQueue: PropTypes.func.isRequired,
    // Set prop on orders reducer
    setOrdersProp: PropTypes.func.isRequired,
    // Appraiser reducer
    appraiser: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Get total number of pending invitations
    getPendingInvitationsTotal: PropTypes.func.isRequired,
    // Select job type
    selectJobType: PropTypes.func.isRequired,
    // Set fee value for job type
    setFeeValue: PropTypes.func.isRequired,
    // Upload file (either sample report or resume)
    uploadFile: PropTypes.func.isRequired,
  };

  /**
   * Initialize state in the constructor
   * @param props
   */
  constructor(props) {
    super(props);
    // Hide modal by default
    this.state = {
      // Show accept dialog
      showAccept: false,
      // Show decline dialog
      showDecline: false
    };

    this.hideAccept = ::this.hideAccept;
    this.submitJobTypesInvitations = submitJobTypesInvitations.bind(this, createJobTypeRequest);
    this.acceptInvitation = ::this.acceptInvitation;
    this.submitAch = ::this.submitAch;
    this.updateAppraiserForInvitation = updateAppraiserForInvitation.bind(this);
    this.hideDecline = ::this.hideDecline;
    this.declineInvitation = ::this.declineInvitation;
    this.changePage = ::this.changePage;
  }

  /**
   * Retrieve invitations on mount
   */
  componentDidMount() {
    const {auth} = this.props;
    const userId = auth.getIn(['user', 'id']);
    // If already authenticated
    if (userId) {
      this.handleInit.call(this, userId);
    }
  }

  /**
   * Get invitations
   */
  componentWillReceiveProps(nextProps) {
    const {auth, invitations, getCustomerJobTypes, getJobTypes, getCustomerFees} = this.props;
    const {invitations: nextInvitations} = nextProps;
    const nextUserId = nextProps.auth.getIn(['user', 'id']);
    const nextSelectedInvitation = nextInvitations.get('selectedInvitation');
    // If authenticated in this state
    if (!auth.getIn(['user', 'id']) && nextUserId) {
      this.handleInit.call(this, nextUserId);
    }
    // Get data for accepting invite
    if (!invitations.get('selectedInvitation') && nextSelectedInvitation && !nextInvitations.get('declining')) {
      const nextCustomerId = nextSelectedInvitation.getIn(['customer', 'id']);
      if (nextCustomerId) {
        Promise.all([
          getCustomerJobTypes(nextUserId, nextCustomerId),
          getJobTypes(),
          getCustomerFees(nextUserId, nextCustomerId)
        ])
        .then(() => {
          // Show dialog
          this.setState({
            showAccept: true
          });
        });
      } else {
        // If it's a company invite, don't bother with the job types
        if (nextSelectedInvitation.get('branch')) {
          this.setState({showAccept: true});
        }
      }
    }
    // Hide accept dialog
    if (!invitations.get('acceptInvitationSuccess') && nextInvitations.get('acceptInvitationSuccess')) {
      this.setState({
        showAccept: false
      });
    }
    // Hide decline dialog
    if (!invitations.get('declineInvitationSuccess') && nextInvitations.get('declineInvitationSuccess')) {
      this.hideDecline.call(this);
    }
  }

  /**
   * Retrieve invitations, determine which requirements have already been met by the appraiser
   */
  handleInit(userId) {
    const {
      getInvitations,
      getCompanyInvitations,
      getAch,
      getAppraiser,
      getDefaultFees,
      getPendingInvitationsTotal
    } = this.props;
    // Get invitations
    getInvitations(userId);
    // Get company invitations
    getCompanyInvitations(userId);
    // Get ACH
    getAch(userId);
    // Get appraiser
    getAppraiser(userId);
    // Get default job type fees
    getDefaultFees(userId);
    // This is mostly important to fix invite badge in PanelNav just in case
    // the appraiser receives another invite after the component (i.e. PanelNav) is loaded
    // and navigates to the invitations page (even though there's only 0.89671298763% chance of it happening)
    getPendingInvitationsTotal(userId);
  }

  /**
   * Keep reference to selected invitation
   * @param row Table row
   * @param declining If declining an invitation
   */
  selectInvitation(row, declining) {
    const {setProp} = this.props;
    if (declining) {
      setProp(true, 'declining');
    }
    // Keep reference to selected invitation
    setProp(row, 'selectedInvitation');
  }

  /**
   * Keep reference to selected invitation, show dialog
   * @param row
   */
  confirmAcceptInvitation(row) {
    // Keep reference to invitation
    this.selectInvitation.call(this, row);
  }

  /**
   * Select invitation, show the confirm decline invitation dialog
   * @param row
   */
  confirmDeclineInvitation(row) {
    // Show dialog
    this.setState({
      showDecline: true
    });
    // Keep reference to invitation
    this.selectInvitation.call(this, row, true);
  }

  /**
   * Accept selected invitation
   */
  acceptInvitation() {
    const {acceptInvitation, invitations, auth} = this.props;
    const selectedInvitation = invitations.get('selectedInvitation');
    const isCompany = !!selectedInvitation.get('branch');
    acceptInvitation(auth.getIn(['user', 'id']), selectedInvitation.get('id'), isCompany);
  }

  /**
   * Hide the accept dialog
   */
  hideAccept() {
    this.setState({
      showAccept: false
    });
    this.props.setProp(null, 'selectedInvitation');
  }

  /**
   * Decline selected invitation
   */
  declineInvitation() {
    const {declineInvitation, invitations, auth} = this.props;
    const selectedInvitation = invitations.get('selectedInvitation');
    const isCompany = !!selectedInvitation.get('branch');
    declineInvitation(auth.getIn(['user', 'id']), selectedInvitation.get('id'), isCompany);
  }

  /**
   * Hide the decline dialog
   */
  hideDecline() {
    // Remove declining invite prop
    this.props.setProp(false, 'declining');
    // Remove selected
    this.props.setProp(null, 'selectedInvitation');
    // Clear error message
    this.props.setProp(null, 'declineInvitationError');
    // Clear orders
    this.props.setOrdersProp([], 'orders');
    this.props.setOrdersProp({}, 'meta');
    this.setState({
      showDecline: false
    });
  }

  /**
   * Submit ACH form
   */
  submitAch() {
    const {invitations, submitAch, auth} = this.props;
    submitAch(auth.getIn(['user', 'id']), invitations.getIn(['ach', 'form']).toJS());
  }

  /**
   * Switches to a different page
   *
   * @param {selected: number} pagination
   */
  changePage(pagination) {
    const {auth, getInvitations} = this.props;
    getInvitations(auth.getIn(['user', 'id']), pagination.selected + 1);
  }

  render() {
    const {
      invitations,
      removeProp,
      changeSearchValue,
      sortColumn,
      orders,
      auth,
      getOrderQueue,
      appraiser,
      selectJobType,
      setFeeValue,
      setProp,
      uploadFile,
      applyDefaultFees
    } = this.props;
    const {showAccept, showDecline} = this.state;

    const totalPages = invitations.getIn(['meta', 'pagination', 'totalPages'], 0);
    const combinedInvitations = invitations.get('invitations').concat(invitations.get('companyInvitations'));

    return (
      <div>
        {!!combinedInvitations.count() &&
          <div>
            <table className="data-table" style={styles.table}>
              <thead>
                <tr key="head">
                  <th>Company</th>
                  <th>Invited On</th>
                  <th>Actions</th>
                </tr>
                {combinedInvitations.map((invitation) => {
                  const companyName = invitation.getIn(['customer', 'name']) || invitation.getIn(['branch', 'company', 'name']);
                  return (
                    <tr key={invitation.get('id')}>
                      <td>{companyName}</td>
                      <td>{moment(invitation.get('createdAt')).format('MM/DD/YYYY')}</td>
                      <td>
                        <div className="pull-left" role="button" onClick={this.confirmAcceptInvitation.bind(this, invitation)}>
                          <div data-tip data-for="accept">
                            <i className="material-icons">check</i>
                            <ReactTooltip id="accept" place="bottom" type="dark" effect="solid" offset={styles.tooltipOffset}>
                              <span>Accept</span>
                            </ReactTooltip>
                          </div>
                        </div>
                        <div className="pull-left" role="button" onClick={this.confirmDeclineInvitation.bind(this, invitation)}>
                          <div data-tip data-for="decline">
                            <i className="material-icons">clear</i>
                            <ReactTooltip id="decline" place="bottom" type="dark" effect="solid" offset={styles.tooltipOffset}>
                              <span>Decline</span>
                            </ReactTooltip>
                          </div>
                        </div>
                      </td>
                    </tr>
                  );
                })}
              </thead>
            </table>
            {totalPages > 1 &&
              <div className="row">
                <div className="col-md-12">
                  <ReactPaginate
                    pageNum={totalPages}
                    pageRangeDisplayed={pageRange}
                    marginPagesDisplayed={marginPages}
                    breakLabel={<a href="">...</a>}
                    forceSelected={invitations.getIn(['meta', 'pagination', 'current']) - 1}
                    clickCallback={this.changePage}
                    containerClassName={"pagination orders-pagination-container"}
                    subContainerClassName={"pages pagination"}
                    activeClassName={"active"}
                  />
                </div>
              </div>
            }
          </div>
        }
        {!combinedInvitations.count() &&
         <NoData
           text="No pending invitations"
         />
        }
        {/*Accept invitation*/}
        <InvitationAccept
          applyDefaultFees={applyDefaultFees}
          uploadFile={uploadFile}
          setProp={setProp}
          setFeeValue={setFeeValue}
          selectJobType={selectJobType}
          invitations={invitations}
          show={showAccept}
          hide={this.hideAccept}
          acceptInvitation={this.acceptInvitation}
          saveJobTypeFees={this.submitJobTypesInvitations}
          selectedInvitation={invitations.get('selectedInvitation') || Immutable.Map()}
          requirements={invitations.getIn(['selectedInvitation', 'requirements']) || Immutable.List()}
          metRequirements={invitations.get('metRequirements') || Immutable.List()}
          title="Accept invitation"
          submitAch={this.submitAch}
          updateAppraiser={this.updateAppraiserForInvitation}
          removeProp={removeProp}
          changeSearchValue={changeSearchValue}
          sortColumn={sortColumn}
          appraiser={appraiser}
        />
        {/*Decline invitation*/}
        <InvitationDecline
          invitations={invitations}
          orders={orders}
          hideDecline={this.hideDecline}
          declineInvitation={this.declineInvitation}
          showDecline={showDecline}
          auth={auth}
          getOrderQueue={getOrderQueue}
        />
      </div>
    );
  }
}
