import React, {Component, PropTypes} from 'react';
import Immutable from 'immutable';

import {
  OrdersActionButtons,
  TableTextInfoField,
  DividerWithIcon,
  OrdersDetailsHeader
} from 'components';

import {getContact} from 'helpers/genericFunctions';
import {capitalizeWords} from 'helpers/string';

import moment from 'moment';

const styles = {
  marginBottom4: { marginBottom: '4px' },
  relative: {position: 'relative'},
  relativeTop1: {top: '1px', position: 'relative'},
  currency: { style: 'currency', currency: 'USD' }
};

export default class OrdersDetails extends Component {
  static propTypes = {
    // Currently selected record
    selectedRecord: PropTypes.instanceOf(Immutable.Map),
    // Close details pane
    closeDetailsPane: PropTypes.func,
    // Go to details view
    goToDetailsView: PropTypes.func,
    // URL params
    params: PropTypes.object,
    // Full screen view
    fullScreen: PropTypes.bool,
    // Toggle instructions dialog
    toggleInstructions: PropTypes.func.isRequired,
    // UI state
    uiState: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Orders
    orders: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Push router state
    pushState: PropTypes.func.isRequired,
    // Set print content
    setPrintContent: PropTypes.func.isRequired,
    // Remove print content
    removePrintContent: PropTypes.func.isRequired,
    // Auth
    auth: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Toggle on hold
    toggleOnHold: PropTypes.func.isRequired,
    // Toggle resume after being placed on hold
    toggleResume: PropTypes.func.isRequired,
    // User type
    userType: PropTypes.string.isRequired,
    // Toggle accept dialog
    toggleAcceptDialog: PropTypes.func.isRequired,
    // Toggle accept with conditions
    toggleAcceptWithConditionsDialog: PropTypes.func.isRequired,
    // Toggle decline
    toggleDeclineDialog: PropTypes.func.isRequired,
    // Toggle submit bid
    toggleSubmitBid: PropTypes.func.isRequired,
    // Toggle schedule inspection
    toggleScheduleInspection: PropTypes.func.isRequired,
    // Toggle inspection complete
    toggleInspectionComplete: PropTypes.func.isRequired,
    // Toggle reassign dialog
    toggleReassign: PropTypes.func.isRequired,
    // Company management
    companyManagement: PropTypes.object.isRequired
  };

  constructor() {
    super();

    this.headerInformation = ::this.headerInformation;
    this.generalInformation = ::this.generalInformation;
    this.fdicInformation = ::this.fdicInformation;
    this.propertyInformation = ::this.propertyInformation;
    this.propertyDescription = ::this.propertyDescription;
    this.inspectionContacts = ::this.inspectionContacts;
    this.additionalComments = ::this.additionalComments;
    this.getDetailsValues = ::this.getDetailsValues;
    this.valuesNewRequestForBid = ::this.valuesNewRequestForBid;
    this.valuesOnHoldCancelled = ::this.valuesOnHoldCancelled;
    this.valuesOtherProcessStatus = ::this.valuesOtherProcessStatus;
    this.getPrintContent = ::this.getPrintContent;
    this.backToOrdersTable = ::this.backToOrdersTable;
    this.contactFields = ::this.contactFields;
  }

  /**
   * Get the proper details pane inputs depending on status type
   */
  getDetailsValues(selectedRecord, forPrinting = false) {
    switch (selectedRecord.get('processStatus')) {
      // New and request for bid
      case 'new':
      case 'request-for-bid':
        return this.valuesNewRequestForBid(selectedRecord, forPrinting);
      // On hold and cancelled
      case 'on-hold':
      case 'cancelled':
        return this.valuesOnHoldCancelled(selectedRecord, forPrinting);
      // All others
      default:
        return this.valuesOtherProcessStatus(selectedRecord, forPrinting);
    }
  }

  /**
   * Create client address
   */
  getClientAddress(record) {
    let address = '';
    if (record.get('clientAddress1')) {
      address += record.get('clientAddress1') + ' ';
    }
    if (record.get('clientAddress2')) {
      address += record.get('clientAddress2') + ', ';
    } else if (record.get('clientAddress1')) {
      address += ', ';
    }
    if (record.get('clientCity')) {
      address += record.get('clientCity') + ', ';
    }
    if (record.getIn(['clientState', 'name'])) {
      address += record.getIn(['clientState', 'name']) + ' ';
    }
    if (record.get('clientZip')) {
      address += record.get('clientZip');
    }
    return address.replace(/^[\s,]+/, '').replace(/[\s,]+$/, '');
  }

  /**
   * Create client on report address
   */
  getClientOnReportAddress(record) {
    let address = record.get('clientDisplayedOnReportAddress1') + ' ';
    if (record.get('clientDisplayedOnReportAddress2')) {
      address += record.get('clientDisplayedOnReportAddress2') + ', ';
    } else {
      address += ', ';
    }
    return address + record.get('clientDisplayedOnReportCity') + ', ' +
           record.getIn(['clientDisplayedOnReportState', 'name']) + ' ' + record.get('clientDisplayedOnReportZip');
  }

  /**
   * Returns content that should be rendered for printing
   */
  getPrintContent() {
    return this.getDetailsValues(this.props.selectedRecord, true);
  }

  /**
   * Header information
   * @param selectedRecord
   * @returns {XML}
   */
  headerInformation(selectedRecord) {
    let {goToDetailsView} = this.props;
    const {
      fullScreen,
      params,
      toggleInstructions,
      closeDetailsPane,
      setPrintContent,
      removePrintContent,
      toggleOnHold,
      toggleResume,
      userType,
      toggleAcceptDialog,
      toggleAcceptWithConditionsDialog,
      toggleDeclineDialog,
      toggleSubmitBid,
      toggleScheduleInspection,
      toggleInspectionComplete,
      toggleReassign,
      auth,
      companyManagement
    } = this.props;
    goToDetailsView = goToDetailsView ? goToDetailsView.bind(this, selectedRecord) :
                            function() {};

    return (
      <div>
        <OrdersDetailsHeader
          fullScreen={fullScreen}
          closeDetailsPane={closeDetailsPane}
          selectedRecord={selectedRecord}
          goToDetailsView={goToDetailsView}
          params={params}
          mapPrint
          toggleInstructions={toggleInstructions}
          setPrintContent={setPrintContent}
          getPrintContent={this.getPrintContent}
          removePrintContent={removePrintContent}
          toggleOnHold={toggleOnHold}
          toggleResume={toggleResume}
          userType={userType}
        />
        <OrdersActionButtons
          toggleAcceptDialog={toggleAcceptDialog}
          toggleAcceptWithConditionsDialog={toggleAcceptWithConditionsDialog}
          toggleDeclineDialog={toggleDeclineDialog}
          toggleSubmitBid={toggleSubmitBid}
          toggleScheduleInspection={toggleScheduleInspection}
          toggleInspectionComplete={toggleInspectionComplete}
          toggleReassign={toggleReassign}
          auth={auth}
          order={selectedRecord}
          withLabels
          companyManagement={companyManagement}
          wrapper={(data) => {
            return (
              <div className="row">
                <div className="col-md-12 text-center" style={styles.marginBottom4}>
                  {data}
                </div>
              </div>
            );
          }}
        />
      </div>
    );
  }

  /**
   * Format process status string
   * @param status Process status
   */
  formatProcessStatus(status) {
    // Edge case, easier than changing the whole backend
    if (status === 'inspection-completed') {
      status = 'inspection-complete';
    }
    return status ? capitalizeWords(status.replace(/-/g, ' ')) : '';
  }

  /**
   * Property information
   * @param selectedRecord Current record
   */
  propertyInformation(selectedRecord) {
    return (
      <div>
        <div className="row">
          <div className="col-md-12">
            <DividerWithIcon
              label="Property Information"
            />
          </div>
        </div>

        {/*Property type*/}
        <div className="row">
          <div className="col-md-4 border-right">
            <TableTextInfoField
              label="Property Type"
              value={selectedRecord.getIn(['property', 'type'])}
            />
          </div>
          <div className="col-md-4 border-right">
            <TableTextInfoField
              label="Property View Type"
              value={selectedRecord.getIn(['property', 'viewType'])}
            />
          </div>
          <div className="col-md-4">
            <TableTextInfoField
              label="Legal"
              value={selectedRecord.getIn(['property', 'legal'])}
            />
          </div>
        </div>
        {/*Property address*/}
        <div className="row">
          <div className="col-md-6 border-right">
            <TableTextInfoField
              label="Property Address 1"
              value={selectedRecord.getIn(['property', 'address1'])}
            />
          </div>
          <div className="col-md-6">
            <TableTextInfoField
              label="Property Address 2"
              value={selectedRecord.getIn(['property', 'address2'])}
            />
          </div>
        </div>
        {/*Property address cont*/}
        <div className="row">
          <div className="col-md-3 border-right">
            <TableTextInfoField
              label="Property City"
              value={selectedRecord.getIn(['property', 'city'])}
            />
          </div>
          <div className="col-md-3 border-right">
            <TableTextInfoField
              value={selectedRecord.getIn(['property', 'state', 'name'])}
              label="Property State"
              fullWidth
            />
          </div>
          <div className="col-md-3 border-right">
            <TableTextInfoField
              value={selectedRecord.getIn(['property', 'zip'])}
              label="Property Zip"
              fullWidth
            />
          </div>
          <div className="col-md-3">
            <TableTextInfoField
              value={selectedRecord.getIn(['property', 'county', 'title']) ?
                capitalizeWords(selectedRecord.getIn(['property', 'county', 'title']).toLowerCase()) : ''}
              label="Property County"
              fullWidth
            />
          </div>
        </div>
        {selectedRecord.getIn(['acceptedConditions', 'additionalComments']) &&
          <div className="row">
            <div className="col-md-12">
              <TableTextInfoField
                value={selectedRecord.getIn(['acceptedConditions', 'additionalComments'])}
                label="Additional Comments"
                fullWidth
              />
            </div>
          </div>
        }
      </div>
    );
  }

  /**
   * Commercial property description
   * @param selectedRecord Current record
   */
  propertyDescription(selectedRecord) {
    if (!selectedRecord.getIn(['jobType', 'isCommercial'])) {
      return <div />;
    }
    return (
      <div>
        <div className="row">
          <div className="col-md-12">
            <DividerWithIcon
              label="Property Description"
              icon="business"
            />
          </div>
        </div>

        <div className="row">
          <div className="col-md-6 border-right">
            <TableTextInfoField
              label="Approximate Building Size"
              value={selectedRecord.getIn(['property', 'approxBuildingSize']) ? String(selectedRecord.getIn(['property', 'approxBuildingSize'])) : ''}
            />
          </div>
          <div className="col-md-6">
            <TableTextInfoField
              label="Approximate Land Size"
              value={selectedRecord.getIn(['property', 'approxLandSize']) ? String(selectedRecord.getIn(['property', 'approxLandSize'])) : ''}
            />
          </div>
        </div>
        <div className="row">
          <div className="col-md-6 border-right">
            <TableTextInfoField
              label="Building Age"
              value={selectedRecord.getIn(['property', 'buildingAge'])}
            />
          </div>
          <div className="col-md-6">
            <TableTextInfoField
              label="Number of Stories"
              value={selectedRecord.getIn(['property', 'numberOfStories'])}
            />
          </div>
        </div>
        <div className="row">
          <div className="col-md-6 border-right">
            <TableTextInfoField
              label="Number of Units"
              value={selectedRecord.getIn(['property', 'numberOfUnits'])}
            />
          </div>
          <div className="col-md-6">
            <TableTextInfoField
              value={selectedRecord.getIn(['property', 'grossRentalIncome'])}
              label="Gross Rental Income"
              fullWidth
            />
          </div>
        </div>
        <div className="row">
          <div className="col-md-6 border-right">
            <TableTextInfoField
              label="Income Sales Cost"
              value={selectedRecord.getIn(['property', 'incomeSalesCost'])}
            />
          </div>
          <div className="col-md-6">
            <TableTextInfoField
              value={selectedRecord.getIn(['property', 'valueType'])}
              label="Value Type"
              fullWidth
            />
          </div>
        </div>
        <div className="row">
          <div className="col-md-6 border-right">
            <TableTextInfoField
              label="Value Qualifier"
              value={selectedRecord.getIn(['property', 'valueQualifier'])}
            />
          </div>
          <div className="col-md-6">
            <TableTextInfoField
              value={selectedRecord.getIn(['property', 'ownerInterest'])}
              label="Owner Interest"
              fullWidth
            />
          </div>
        </div>
      </div>
    );
  }

  /**
   * Go back to orders table
   */
  backToOrdersTable() {
    const {uiState, pushState} = this.props;
    if (uiState.get('record')) {
      pushState(uiState.get('url'));
    }
  }

  /**
   * General information
   *
   */
  generalInformation(selectedRecord, specialCases, forPrinting) {
    const {params, orders, auth} = this.props;
    let backButton;
    // Back button
    if (params.orderId && orders.getIn(['uiState', 'record']) && !forPrinting) {
      backButton = (
        <button style={styles.relative} className="btn btn-blue" onClick={this.backToOrdersTable}>
          <i className="material-icons">keyboard_backspace</i>
          <span style={styles.relativeTop1}>Back</span>
        </button>
      );
    } else {
      backButton = <div/>;
    }
    const isCommercial = selectedRecord.getIn(['jobType', 'isCommercial']);

    // get the job type(s)
    let jobType = selectedRecord.getIn(['jobType', 'title']);
    const additionalJobTypes = selectedRecord.get('additionalJobTypes');
    let jobTypeLabel = 'Job Type';

    if (additionalJobTypes && additionalJobTypes.count() > 0) {
      // append an s to make it plural
      jobTypeLabel += 's';

      // set the job type counter
      let jobTypeCounter = 1;

      // set the counter for the original job type
      jobType = jobTypeCounter + '. ' + jobType;

      // loop through the job types
      additionalJobTypes.map(additionalJobType => {
        // increment the counter
        jobTypeCounter++;

        // add the additional job type
        jobType += '<br />' + jobTypeCounter + '. ' + additionalJobType.get('title');
      });
    }

    return (
      <div>
        <div className="row">
          <div className="col-md-12">
            <DividerWithIcon
              label="General Information"
              button={backButton}
            />
          </div>
        </div>

        {/*Client row*/}
        {(auth.getIn(['user', 'type']) === 'amc' ||
          selectedRecord.getIn(['customer', 'settings', 'showClientToAppraiser']) !== false) &&
          <div className="row">
            <div className="col-md-6 border-right">
              <TableTextInfoField
                value={selectedRecord.get('clientName')}
                label="Client Name"
                fullWidth
              />
            </div>
            <div className="col-md-6">
              <TableTextInfoField
                value={this.getClientAddress(selectedRecord)}
                label="Client Address"
                fullWidth
              />
            </div>
          </div>
        }
        {/*Client on report row*/}
        <div className="row">
          <div className="col-md-6 border-right">
            <TableTextInfoField
              value={selectedRecord.get('clientDisplayedOnReportName')}
              label="Client Displayed on Report"
              fullWidth
            />
          </div>
          <div className="col-md-6">
            <TableTextInfoField
              value={this.getClientOnReportAddress(selectedRecord)}
              label="Client Displayed on Report Address"
              fullWidth
            />
          </div>
        </div>
        {/*Submitted by row*/}
        {auth.getIn(['user', 'type']) !== 'amc' &&
          <div className="row">
            <div className="col-md-6 border-right">
              <TableTextInfoField
                value={selectedRecord.getIn(['customer', 'name'])}
                label="Submitted By"
                fullWidth
              />
            </div>
            <div className="col-md-6">
              <TableTextInfoField
                value={selectedRecord.getIn(['customer', 'phone'])}
                label="Submitted By Phone #"
                fullWidth
              />
            </div>
          </div>
        }
        {/*AMC row*/}
        {specialCases !== 'noAmc' &&
         <div className="row">
           <div className="col-md-6 border-right">
             <TableTextInfoField
               value={selectedRecord.get('amcLicenseNumber')}
               label="AMC License Number"
               fullWidth
             />
           </div>
           <div className="col-md-6">
             <TableTextInfoField
               value={selectedRecord.get('amcLicenseExpiresAt') ?
                 moment(selectedRecord.get('amcLicenseExpiresAt')).format('MM/DD/YYYY') : ''}
               label="AMC License Expiration Date"
               fullWidth
             />
           </div>
         </div>}
        {/*Job type row*/}
        <div className="row">
          <div className="col-md-12 border-right">
            <TableTextInfoField
              value={jobType}
              label={jobTypeLabel}
              fullWidth
              dangerously
            />
          </div>
        </div>
        {/*Fees and purchase price row*/}
        <div className="row">
          <div className="col-md-3 border-right">
            <TableTextInfoField
              value={selectedRecord.get('techFee') ? '$' + selectedRecord.get('techFee').toFixed(2) : ''}
              label="Technology Fee"
              fullWidth
            />
          </div>
          <div className="col-md-3 border-right">
            <TableTextInfoField
              value={selectedRecord.get('fee') ? '$' + selectedRecord.get('fee').toFixed(2) : ''}
              label="Appraiser Fee"
              fullWidth
            />
          </div>
          <div className="col-md-3 border-right">
            <TableTextInfoField
              value={selectedRecord.get('purchasePrice') ? '$' + selectedRecord.get('purchasePrice').toFixed(2) : ''}
              label="Purchase Price"
              fullWidth
            />
          </div>
          <div className="col-md-3">
            <TableTextInfoField
              value={selectedRecord.get('loanAmount') ? '$' + selectedRecord.get('loanAmount').toFixed(2) : ''}
              label="Loan Amount"
              fullWidth
            />
          </div>
        </div>
        {/*Loan information row*/}
        {isCommercial &&
          <div className="row">
            <div className="col-md-4 border-right">
              <TableTextInfoField
                value={selectedRecord.get('fhaNumber') ? selectedRecord.get('fhaNumber').toString() : ''}
                label="FHA Number"
                fullWidth
              />
            </div>
            <div className="col-md-4 border-right">
              <TableTextInfoField
                value={selectedRecord.get('loanNumber') ? selectedRecord.get('loanNumber').toString() : ''}
                label="Loan Number"
                fullWidth
              />
            </div>
            <div className="col-md-4">
              <TableTextInfoField
                value={selectedRecord.get('intendedUse') ? selectedRecord.get('intendedUse') : ''}
                label="Intended Use"
                fullWidth
              />
            </div>
          </div>
        }
        {!isCommercial &&
         <div className="row">
           <div className="col-md-3 border-right">
             <TableTextInfoField
               value={selectedRecord.get('fhaNumber') ? selectedRecord.get('fhaNumber').toString() : ''}
               label="FHA Number"
               fullWidth
             />
           </div>
           <div className="col-md-3 border-right">
             <TableTextInfoField
               value={selectedRecord.get('loanNumber') ? selectedRecord.get('loanNumber').toString() : ''}
               label="Loan Number"
               fullWidth
             />
           </div>
           <div className="col-md-3 border-right">
             <TableTextInfoField
               value={selectedRecord.get('intendedUse') ? selectedRecord.get('intendedUse') : ''}
               label="Intended Use"
               fullWidth
             />
           </div>
           <div className="col-md-3">
             <TableTextInfoField
               value={selectedRecord.get('loanType') ? selectedRecord.get('loanType') : ''}
               label="Loan Type"
               fullWidth
             />
           </div>
         </div>
        }
        {/*Dates row*/}
        <div className="row">
          <div className="col-md-4 border-right">
            <TableTextInfoField
              value={selectedRecord.get('dueDate') ? moment(selectedRecord.get('dueDate')).format('MM/DD/YYYY') : ''}
              label="Due Date"
              fullWidth
            />
          </div>
          <div className="col-md-4 border-right">
            <TableTextInfoField
              value={selectedRecord.get('orderedAt') ? moment(selectedRecord.get('orderedAt')).format('MM/DD/YYYY') : ''}
              label="Ordered Date"
              fullWidth
            />
          </div>
          <div className="col-md-4">
            <TableTextInfoField
              value={selectedRecord.get('assignedAt') ? moment(selectedRecord.get('assignedAt')).format('MM/DD/YYYY') : ''}
              label="Assigned Date"
              fullWidth
            />
          </div>
        </div>
        {/*Process status and approaches row*/}
        <div className="row">
          <div className="col-md-4 border-right">
            <TableTextInfoField
              value={this.formatProcessStatus(selectedRecord.get('processStatus'))}
              label="Process Status"
              fullWidth
            />
          </div>
          <div className="col-md-4 border-right">
            <TableTextInfoField
              value={selectedRecord.get('isRush') ? 'Yes' : 'No'}
              label="Rush Order"
              fullWidth
            />
          </div>
          <div className="col-md-4">
            <TableTextInfoField
              value={selectedRecord.get('approachesToBeIncluded') ? _.capitalize(selectedRecord.get('approachesToBeIncluded').join(', ')) : ''}
              label="Approaches to be Included"
              fullWidth
            />
          </div>
        </div>
        {/*File and reference number row*/}
        <div className="row">
          <div className="col-md-6 border-right">
            <TableTextInfoField
              value={selectedRecord.get('fileNumber') ? selectedRecord.get('fileNumber').toString() : ''}
              label="File Number"
              fullWidth
            />
          </div>
          <div className="col-md-6">
            <TableTextInfoField
              value={selectedRecord.get('referenceNumber') ? selectedRecord.get('referenceNumber').toString() : ''}
              label="Reference Number"
              fullWidth
            />
          </div>
        </div>
        {/*Inspection row*/}
        <div className="row">
          <div className="col-md-4 border-right">
            <TableTextInfoField
              value={selectedRecord.get('inspectionScheduledAt') ? moment(selectedRecord.get('inspectionScheduledAt')).format('MM/DD/YYYY h:mm A') : ''}
              label="Inspection Date"
              fullWidth
            />
          </div>
          <div className="col-md-4 border-right">
            <TableTextInfoField
              value={selectedRecord.get('estimatedCompletionDate') ? moment(selectedRecord.get('estimatedCompletionDate')).format('MM/DD/YYYY') : ''}
              label="Est. Completion Date"
              fullWidth
            />
          </div>
          <div className="col-md-4">
            <TableTextInfoField
              value={selectedRecord.get('completedAt') ? moment(selectedRecord.get('completedAt')).format('MM/DD/YYYY') : ''}
              label="Completion Date"
              fullWidth
            />
          </div>
        </div>
        {/*Bid info*/}
        {selectedRecord.getIn(['bid', 'amount']) &&
          <div className="row">
            <div className="col-md-12">
              <TableTextInfoField
                value={'$' + (selectedRecord.getIn(['bid', 'amount'])).toLocaleString(styles.currency)}
                label="Bid Amount"
                fullWidth
              />
            </div>
          </div>
        }
      </div>
    );
  }

  /**
   * Display FDIC information
   * @param selectedRecord
   * @returns {XML}
   */
  fdicInformation(selectedRecord) {
    const fdic = selectedRecord.get('fdic');
    if (!fdic) {
      return <div />;
    }
    return (
      <div>
        <div className="row">
          <div className="col-md-12">
            <DividerWithIcon
              label="FDIC Information"
              icon="business"
            />
          </div>
        </div>

        <div className="row">
          <div className="col-md-6 border-right">
            <TableTextInfoField
              label="FIN #"
              value={fdic.get('fin')}
            />
          </div>
          <div className="col-md-6">
            <TableTextInfoField
              label="Task Order"
              value={fdic.get('taskOrder')}
            />
          </div>
        </div>
        <div className="row">
          <div className="col-md-6 border-right">
            <TableTextInfoField
              label="Asset Type"
              value={fdic.get('assetType')}
            />
          </div>
          <div className="col-md-6">
            <TableTextInfoField
              label="Asset #"
              value={fdic.get('assetNumber')}
            />
          </div>
        </div>
        <div className="row">
          <div className="col-md-6 border-right">
            <TableTextInfoField
              label="Line #"
              value={fdic.get('line') ? String(fdic.get('line')) : ''}
            />
          </div>
          <div className="col-md-6">
            <TableTextInfoField
              label="Contractor"
              value={fdic.get('contractor')}
            />
          </div>
        </div>
      </div>
    );
  }

  /**
   * Create the full name display for each contact
   * @param contact
   */
  contactFullName(contact) {
    return `${contact.get('firstName') || ''} ${contact.get('middleName') ? contact.get('middleName') + ' ' : '' }${contact.get('lastName') || ''}`;
  }

  /**
   * Mutate label text if best person to contact is realtor
   * @param text Original test
   * @param isRealtor If best person to contact is realtor
   * @return {string}
   */
  labelForRealtor(text, isRealtor) {
    return isRealtor ? text.replace(/other/i, 'Realtor') : text;
  }

  /**
   * Create contact fields
   * @param contact Contact record
   * @param labelBegin Label beginning value
   * @param isRealtor Used to determine label for "other" types (which may actually be realtor)
   */
  contactFields(contact, labelBegin, isRealtor) {
    return (
      <div>
        {/*Borrower*/}
        <div className="row">
          <div className="col-md-6 border-right">
            <TableTextInfoField
              value={this.contactFullName(contact)}
              label={typeof isRealtor !== 'undefined' ? this.labelForRealtor('Other', isRealtor) : labelBegin}
              fullWidth
            />
          </div>
          <div className="col-md-6">
            <TableTextInfoField
              value={contact.get('email')}
              label={typeof isRealtor !== 'undefined' ? this.labelForRealtor('Other Email', isRealtor) : `${labelBegin} Email`}
              fullWidth
            />
          </div>
        </div>
        {/*Borrower Phones*/}
        <div className="row">
          <div className="col-md-4 border-right">
            <TableTextInfoField
              value={contact.get('workPhone')}
              label={typeof isRealtor !== 'undefined' ? this.labelForRealtor('Other Work #', isRealtor) : `${labelBegin} Work #`}
              fullWidth
            />
          </div>
          <div className="col-md-4 border-right">
            <TableTextInfoField
              value={contact.get('homePhone')}
              label={typeof isRealtor !== 'undefined' ? this.labelForRealtor('Other Home #', isRealtor) : `${labelBegin} Home #`}
              fullWidth
            />
          </div>
          <div className="col-md-4">
            <TableTextInfoField
              value={contact.get('cellPhone')}
              label={typeof isRealtor !== 'undefined' ? this.labelForRealtor('Other Cell #', isRealtor) : `${labelBegin} Cell #`}
              fullWidth
            />
          </div>
        </div>
      </div>
    );
  }

  /**
   * Inspection contacts
   * @param selectedRecord Current record
   */
  inspectionContacts(selectedRecord) {
    // Get borrower
    const borrower = getContact(selectedRecord);
    const coBorrower = getContact(selectedRecord, 'co-borrower');
    const owner = getContact(selectedRecord, 'owner');
    const realtor = getContact(selectedRecord, 'realtor');
    const other = getContact(selectedRecord, 'other');
    const assistant = getContact(selectedRecord, 'assistant');
    const sellingAgent = getContact(selectedRecord, 'selling-agent');
    const listingAgent = getContact(selectedRecord, 'listing-agent');
    const bestToContact = selectedRecord.getIn(['property', 'bestPersonToContact']);
    const isRealtor = /realtor/i.test(bestToContact.toLowerCase());
    return (
      <div>
        <div className="row">
          <div className="col-md-12">
            <DividerWithIcon
              label="Inspection Contacts and Access Information"
            />
          </div>
        </div>

        {/*Contact*/}
        <div className="row">
          <div className="col-md-6 border-right">
            <TableTextInfoField
              value={_.capitalize(bestToContact)}
              label="Best Person to Contact"
              fullWidth
            />
          </div>
          <div className="col-md-6">
            <TableTextInfoField
              value={_.capitalize(selectedRecord.getIn(['property', 'occupancy']))}
              label="Occupancy"
              fullWidth
            />
          </div>
        </div>
        {/*Borrower*/}
        {this.contactFields(borrower, 'Borrower')}
        {/*Co-Borrower*/}
        {!!coBorrower.size &&
          this.contactFields(coBorrower, 'Co-Borrower')
        }
        {/*Owner*/}
        {!!owner.size &&
          this.contactFields(owner, 'Owner')
        }
        {/*Realtor*/}
        {!!realtor.size &&
          this.contactFields(realtor, 'Realtor')
        }
        {/*Other*/}
        {!!other.size &&
          this.contactFields(other, 'Other', isRealtor)
        }
        {/*Assistant*/}
        {!!assistant.size &&
         this.contactFields(assistant, 'Assistant')
        }
        {/*Listing agent*/}
        {!!listingAgent.size &&
         this.contactFields(listingAgent, 'Listing Agent')
        }
        {/*Selling agent*/}
        {!!sellingAgent.size &&
         this.contactFields(sellingAgent, 'Selling Agent')
        }
      </div>
    );
  }

  /**
   * Displays additional comments
   *
   * @param selectedRecord Current record
   */
  additionalComments(selectedRecord) {
    return (
      <div className="row">
        <div className="col-md-12">
          <TableTextInfoField
            value={selectedRecord.getIn(['property', 'additionalComments'])}
            label="Additional Comments"
            fullWidth
            dangerously
          />
        </div>
      </div>
    );
  }

  /**
   * New and request for bid process status
   */
  valuesNewRequestForBid(selectedRecord, forPrinting) {
    return (
      <div className="container-fluid details-cont">
        {/*Header details*/}
        {!forPrinting && this.headerInformation(selectedRecord)}
        {/*General information*/}
        {this.generalInformation(selectedRecord, 'noAmc', forPrinting)}
        {/*FDIC*/}
        {this.fdicInformation(selectedRecord)}
        {/*Property information*/}
        {this.propertyInformation(selectedRecord)}
        {/*Commercial property description*/}
        {this.propertyDescription(selectedRecord)}
        {/*Additional comments*/}
        {this.additionalComments(selectedRecord)}
      </div>
    );
  }

  /**
   * Process status values for records which do not fit into one of the other categories
   */
  valuesOtherProcessStatus(selectedRecord, forPrinting) {
    return (
      <div className="container-fluid details-cont">
        {!forPrinting && this.headerInformation(selectedRecord)}
        {/*General information*/}
        {this.generalInformation(selectedRecord, null, forPrinting)}
        {/*FDIC*/}
        {this.fdicInformation(selectedRecord)}
        {/*Property information*/}
        {this.propertyInformation(selectedRecord)}
        {/*Commercial property description*/}
        {this.propertyDescription(selectedRecord)}
        {/*Inspection contacts*/}
        {this.inspectionContacts(selectedRecord)}
        {/*Additional comments*/}
        {this.additionalComments(selectedRecord)}
      </div>
    );
  }

  /**
   * Values for process status of on hold or cancelled
   */
  valuesOnHoldCancelled(selectedRecord, forPrinting) {
    return (
      <div className="container-fluid details-cont">
        {!forPrinting && this.headerInformation(selectedRecord)}
        {/*General information*/}
        {this.generalInformation(selectedRecord, null, forPrinting)}
        {/*FDIC*/}
        {this.fdicInformation(selectedRecord)}
        {/*Property information*/}
        {this.propertyInformation(selectedRecord)}
        {/*Commercial property description*/}
        {this.propertyDescription(selectedRecord)}
        {/*Inspection contacts*/}
        {this.inspectionContacts(selectedRecord)}
        {/*Additional comments*/}
        {this.additionalComments(selectedRecord)}
      </div>
    );
  }

  render() {
    return this.getDetailsValues(this.props.selectedRecord);
  }
}
