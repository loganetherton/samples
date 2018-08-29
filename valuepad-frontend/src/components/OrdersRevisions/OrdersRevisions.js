import React, {Component, PropTypes} from 'react';
import Immutable from 'immutable';
import moment from 'moment';

import {
  DividerWithIcon,
  Confirm,
  OrdersActionButtons,
  OrdersDetailsHeader,
  DocumentsTable
} from 'components';

import {getContact} from 'helpers/genericFunctions';

const dateFormat = 'MM/DD/YYYY h:mm A';

export default class OrdersRevisions extends Component {
  static propTypes = {
    // orders
    orders: PropTypes.instanceOf(Immutable.Map),
    // Set prop
    setProp: PropTypes.func.isRequired,
    // Retrieve revisions
    getRevisions: PropTypes.func.isRequired,
    // Displaying in fullscreen
    fullScreen: PropTypes.bool,
    // Close details pane
    closeDetailsPane: PropTypes.func.isRequired,
    // Selected order
    selectedRecord: PropTypes.instanceOf(Immutable.Map).isRequired,
    // URL params
    params: PropTypes.object.isRequired,
    // Auth
    auth: PropTypes.instanceOf(Immutable.Map),
    // Set print content
    setPrintContent: PropTypes.func.isRequired,
    // Remove print content
    removePrintContent: PropTypes.func.isRequired,
    // Toggle instructions
    toggleInstructions: PropTypes.func.isRequired,
    // Toggle accept
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
    // Toggle reassign
    toggleReassign: PropTypes.func.isRequired,
    // Company management
    companyManagement: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);

    this.state = {
      reconsiderationDialog: false,
      reconsideration: null,
    };
  }

  getPropertyAddress(property) {
    let address = property.get('address1') + ' ';
    if (property.get('address2')) {
      address += property.get('address2') + ', ';
    } else {
      address += ', ';
    }
    return address + property.get('city') + ', ' + property.getIn(['state', 'code']) + ' ' + property.get('zip');
  }

  revisionDisplay(revision) {
    const checklist = revision.getIn(['data', 'checklist']);
    const message = revision.getIn(['data', 'message']);
    return (
      <div>
        {!!checklist.count() &&
          <ol style={{ paddingLeft: 15 }}>
            {checklist.map((item, key) => {
              return (
                <li key={key}>
                  <div dangerouslySetInnerHTML={{ __html: item.replace(/\n/g, '<br/>') }} />
                </li>
              );
            })}
          </ol>
        }
        {!checklist.count() &&
          <div dangerouslySetInnerHTML={{ __html: message.replace(/\n/g, '<br/>') }} />
        }
      </div>
    );
  }

  reconsiderationDisplay(revision) {
    return (
      <a className="link" onClick={this.showReconsiderationDialog.bind(this, revision)}>Click here to view Reconsideration Request</a>
    );
  }

  /**
   * Get complete borrower name for reconsideration request
   * @param borrower
   */
  getBorrowerName(borrower) {
    let name = '';
    name += borrower.get('firstName');
    if (borrower.get('middleName')) {
      name += ' ' + borrower.get('middleName');
    }
    return name + ' ' + borrower.get('lastName');
  }

  reconsiderationDialogDisplay(revision = null) {
    // set it at state if we don't pass one
    if (!revision) {
      revision = this.state.reconsideration;
    }

    // in case its not loaded in state yet
    if (!revision) {
      return null;
    }

    const {selectedRecord} = this.props;
    const comparables = revision.getIn(['data', 'comparables']);
    const borrower = getContact(selectedRecord);
    const comments = [];
    const documents = revision.getIn(['data', 'documents']);
    return (
      <div id="reconsideration-display">
        <div className="row">
          <div className="col-md-4" style={{ paddingLeft: 0 }}>
            <strong>Borrower:</strong> {this.getBorrowerName(borrower)}
          </div>
          <div className="col-md-8" style={{ paddingLeft: 0 }}>
            <strong>Property Address:</strong> {this.getPropertyAddress(selectedRecord.get('property'))}
          </div>
        </div>
        {!!comparables.count() &&
          <div className="row" style={{ marginTop: 10}}>
            <table className="data-table" style={{ width: '100%' }}>
              <thead>
                <tr>
                  <th></th>
                  <th>Address</th>
                  <th>Sales Price</th>
                  <th>Closed Date</th>
                  <th>Living Area</th>
                  <th>Site Size</th>
                  <th>Actual Age</th>
                  <th>Distance to Subject</th>
                  <th>Source Data</th>
                </tr>
              </thead>
              <tbody>
                {comparables.map((comparable, index) => {
                  const closedDate = comparable.get('closedDate') ?
                                     moment(comparable.get('closedDate')).format('MM/DD/YYYY') : 'N/A';
                  comments.push(comparable.get('comment'));
                  return (
                    <tr key={index}>
                      <td>Comp {index + 1}</td>
                      <td>{comparable.get('address')}</td>
                      <td>{comparable.get('salesPrice')}</td>
                      <td>{closedDate}</td>
                      <td>{comparable.get('livingArea')}</td>
                      <td>{comparable.get('siteSize')}</td>
                      <td>{comparable.get('actualAge')}</td>
                      <td>{comparable.get('distanceToSubject')}</td>
                      <td>{comparable.get('sourceData')}</td>
                    </tr>
                  );
                })}
              </tbody>
            </table>
          </div>
        }

        {!!comments.length &&
          <div className="row" style={{ marginTop: 10 }}>
            <div><strong>Comments</strong></div>
            {comments.map((comment, index) => {
              return (
                <div key={index}><strong>Comparable Sale #{index + 1}:</strong> {comment}</div>
              );
            })}
          </div>
        }

        {revision.getIn(['data', 'comment']) &&
          <div className="row" style={{ marginTop: 10 }}>
            <div><strong>Additional Comments</strong></div>
            <div>{revision.getIn(['data', 'comment'])}</div>
          </div>
        }

        {!!documents.count() &&
         <div className="row" style={{ marginTop: 10 }}>
           <div><strong>Documents</strong></div>
           <DocumentsTable uploadedAdditionalDocs={documents} hideDate/>
         </div>
        }
      </div>
    );
  }

  showReconsiderationDialog(reconsideration) {
    this.setState({
      reconsiderationDialog: true,
      reconsideration: reconsideration,
    });

    // set the print content
    const printContent = this.reconsiderationDialogDisplay.call(this, reconsideration);
    this.props.setPrintContent(printContent);
  }

  hideReconsiderationDialog() {
    this.setState({
      reconsiderationDialog: false
    });

    // remove the content from print
    this.props.removePrintContent();
  }

  printReconsideration() {
    window.print();
  }

  /**
   * Returns the content that should be rendered for printing
   */
  getPrintContent() {
    const {orders} = this.props;
    const revisions = orders.get('revisions');

    return (
      <div>
        <div className="row">
          <div className="col-md-12 text-center">
            <DividerWithIcon
              label="Revisions"
              icon="autorenew"
            />
          </div>
        </div>
        {revisions.filter(revision => revision.get('type') === 'revision').map((revision, index) => {
          const dateDisplay = moment(revision.get('date')).format(dateFormat);
          return (
            <div key={index} className="row" style={{ paddingTop: 5, paddingBottom: 5 }}>
              <div className="col-md-12">
                <div className="col-md-6" style={{ fontWeight: 'bold', padding: 0 }}>
                  <span>Revision Request</span>
                </div>
                <div className="col-md-6 text-right" style={{ fontWeight: 'bold', padding: 0 }}>
                  {dateDisplay}
                </div>
              </div>
              <div className="col-md-12">
                <span>{this.revisionDisplay.call(this, revision)}</span>
              </div>
            </div>
          );
        })}
      </div>
    );
  }

  render() {
    const {
      orders,
      fullScreen,
      closeDetailsPane,
      selectedRecord,
      params,
      setPrintContent,
      removePrintContent,
      toggleInstructions,
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
    const revisions = orders.get('revisions');
    return (
      <div className="container-fluid details-cont">
        <OrdersDetailsHeader
          fullScreen={fullScreen}
          closeDetailsPane={closeDetailsPane}
          selectedRecord={selectedRecord}
          params={params}
          mapPrint
          setPrintContent={setPrintContent}
          getPrintContent={::this.getPrintContent}
          removePrintContent={removePrintContent}
          toggleInstructions={toggleInstructions}
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
                <div className="col-md-12 text-center" style={{ marginBottom: '4px' }}>
                  {data}
                </div>
              </div>
            );
          }}
        />
        <div className="row">
          <div className="col-md-12 text-center">
            <DividerWithIcon
              label="Revisions"
              icon="autorenew"
            />
          </div>
        </div>
        {revisions.map((revision, index) => {
          const revisionType = revision.get('type');
          const dateDisplay = moment(revision.get('date')).format(dateFormat);
          return (
            <div key={index} className="row" style={{ paddingTop: 5, paddingBottom: 5 }}>
              <div className="col-md-12">
                <div className="col-md-6" style={{ fontWeight: 'bold', padding: 0 }}>
                  {revisionType === 'revision' &&
                    <span>Revision Request</span>
                  }
                  {revisionType === 'reconsideration' &&
                    <span>Reconsideration Request</span>
                  }
                </div>
                <div className="col-md-6 text-right" style={{ fontWeight: 'bold', padding: 0 }}>
                  {dateDisplay}
                </div>
              </div>
              <div className="col-md-12">
                {revisionType === 'revision' &&
                  <span>{this.revisionDisplay.call(this, revision)}</span>
                }
                {revisionType === 'reconsideration' &&
                  <span>{this.reconsiderationDisplay.call(this, revision)}</span>
                }
              </div>
            </div>
          );
        })}

        <Confirm
          body={this.reconsiderationDialogDisplay.call(this)}
          title="Reconsideration Request"
          show={this.state.reconsiderationDialog}
          hide={::this.hideReconsiderationDialog}
          submit={::this.printReconsideration}
          buttonText={{ submit: 'Print', cancel: 'Close' }}
          contentStyle={{ width: '90%', maxWidth: 'auto' }}
        />
      </div>
    );
  }
}
