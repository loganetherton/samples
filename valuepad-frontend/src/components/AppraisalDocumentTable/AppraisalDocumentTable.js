import React, {Component, PropTypes} from 'react';
import {Link} from 'react-router';
import {Dialog} from 'material-ui';

import {
  ActionButton,
  MyDropzone,
  Void,
  UploadDialog
} from 'components';

import Immutable from 'immutable';
import moment from 'moment';

import {SETTINGS_URL} from 'redux/modules/urls';

// Styles
const styles = {
  linkColor: {
    color: '#1976D2'
  },
  linkCursor: {
    cursor: 'pointer'
  },
  marginLeft10: { marginLeft: '10px' },
  dataTable: { width: '100%', marginTop: '10px' },
  margin0: { margin: 0 },
  normalFontWeight: {fontWeight: 'normal'}
};

/**
 * After appraisal document has been uploaded, show upload for ACI, ZAP, ENV, and ZOO files
 */
export default class AppraisalDocumentTable extends Component {
  static propTypes = {
    // Orders reducer
    orders: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Most recently uploaded appraisal doc
    uploadedAppraisalDocs: PropTypes.instanceOf(Immutable.List),
    // Sub docs
    uploadedAppraisalSubDocs: PropTypes.instanceOf(Immutable.Map),
    // Document formats
    formats: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Selected record
    selectedRecord: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Credit card number
    ccNumber: PropTypes.string,
    // Upload appraisal doc
    onDropAppraisalDocs: PropTypes.func.isRequired,
    // Tech fee is paid
    techFeePaid: PropTypes.bool,
    // Pay tech fee
    payTechFee: PropTypes.func.isRequired,
    // Credit card was rejected
    creditCardRejection: PropTypes.oneOfType([
      PropTypes.bool,
      PropTypes.string
    ]),
    // Set prop
    setProp: PropTypes.func.isRequired,
    // Auth
    auth: PropTypes.instanceOf(Immutable.Map),
    // Selected appraiser (customer view)
    selectedAppraiser: PropTypes.number,
    // Get docs after upload
    getAppraisalDoc: PropTypes.func.isRequired
  };

  /**
   * Pay tech fee
   */
  constructor() {
    super();
    this.state = {
      // Hide pay tech fee modal
      showPayTechFee: false,
      // Upload doc failure model
      addDocErrorShow: false,
      // Document uploading dialog
      documentUploading: false
    };

    this.uploadButton = ::this.uploadButton;
    this.tableDocNotVisible = ::this.tableDocNotVisible;
    this.tableDocVisible = ::this.tableDocVisible;
    this.toggleAddDocError = ::this.toggleAddDocError;
    this.toggleTechFeeDialog = ::this.toggleTechFeeDialog;
    this.extraDocCells = ::this.extraDocCells;
    this.getPrimaryFormats = ::this.getPrimaryFormats;
    this.closeTechFeeFailedDialog = ::this.closeTechFeeFailedDialog;
  }

  /**
   * Hide the pay tech fee pop up when tech fee is paid
   * @param nextProps
   */
  componentWillReceiveProps(nextProps) {
    const {techFeePaid, orders, getAppraisalDoc} = this.props;
    const {techFeePaid: nextTechFeePaid, orders: nextOrders, auth: nextAuth, selectedRecord: nextSelectedRecord} = nextProps;
    const newState = {};
    // Hide tech fee paid dialog
    if (!techFeePaid && nextTechFeePaid) {
      newState.showPayTechFee = false;
    }
    // Display file upload error dialog
    if (!orders.getIn(['errors', 'addDoc']) && nextOrders.getIn(['errors', 'addDoc'])) {
      newState.addDocErrorShow = true;
    }
    // Uploading notification
    if (!orders.get('uploadingFile') && nextOrders.get('uploadingFile')) {
      newState.documentUploading = true;
    }
    // Doc finished uploading
    if (typeof orders.get('addDocSuccess') === 'undefined' && typeof nextOrders.get('addDocSuccess') !== 'undefined') {
      setTimeout(() => {
        this.setState({
          documentUploading: false
        });
      }, 1000);
      // Retrieve appraisal docs after upload
      getAppraisalDoc(nextAuth.get('user'), nextSelectedRecord.get('id'));
    }
    // Doc failed to push to AS successfully
    if (typeof orders.get('addDocSuccess') === 'undefined' && nextOrders.get('addDocSuccess') === false) {
      this.toggleAddDocError();
    }
    if (Object.keys(newState).length) {
      this.setState(newState);
    }
  }

  /**
   * Get most recently uploaded appraisal doc
   * @param uploadedAppraisalDocs
   */
  getMostRecentDoc(uploadedAppraisalDocs) {
    try {
      return uploadedAppraisalDocs.slice(-1).get(0);
    } catch (e) {
      return null;
    }
  }

  /**
   * Tech fee dialog buttons
   */
  techFeeDialogButtons() {
    const {orders, payTechFee} = this.props;

    return (
      [
        <ActionButton
          type="cancel"
          text="Cancel"
          onClick={this.toggleTechFeeDialog}
        />,
        <ActionButton
          style={styles.marginLeft10}
          type="submit"
          text="Pay Tech Fee"
          onClick={payTechFee}
          disabled={orders.get('payingTechFee')}
        />
      ]
    );
  }

  /**
   * Failed to pay tech fee dialog buttons
   */
  failedToPayTechFeeButtons() {
    return (
      [
        <ActionButton
          type="cancel"
          text="Close"
          onClick={this.closeTechFeeFailedDialog}
        />
      ]
    );
  }

  /**
   * Show tech fee dialog
   */
  toggleTechFeeDialog() {
    this.setState({
      showPayTechFee: !this.state.showPayTechFee
    });
  }

  /**
   * Close tech fee failed dialog
   */
  closeTechFeeFailedDialog() {
    this.props.setProp(false, 'creditCardRejection');
    this.toggleTechFeeDialog();
  }

  /**
   * Upload appraisal doc/pay tech fee
   */
  uploadButton(orderHasTechFee, techFeePaid) {
    let button;
    const primaryFormats = this.getPrimaryFormats();
    const {selectedRecord, auth, selectedAppraiser = null, onDropAppraisalDocs} = this.props;
    const userType = auth.getIn(['user', 'type']);
    // No uploaded doc, but order has tech fee and it is not paid
    if (orderHasTechFee && !techFeePaid && userType !== 'amc') {
      button = (
        <button className="btn btn-raised btn-success" onClick={this.toggleTechFeeDialog}>
          Upload appraisal document ({primaryFormats.join(', ')})
        </button>
      );
    // Tech fee bypassed, but no doc
    } else if (!this.docExists(selectedRecord)) {
      button = (
        <MyDropzone
          instructions={`Upload appraisal document (${primaryFormats.join(', ')})`}
          onDrop={onDropAppraisalDocs.bind(this, 'appraisalDoc')}
          uploadedFiles={Immutable.List()}
          acceptedFileTypes={['ANY']}
        />
      );
    // Replace current doc
    } else {
      button = (
        <MyDropzone
          instructions={`Upload another appraisal document (${primaryFormats.join(', ')})`}
          onDrop={onDropAppraisalDocs.bind(this, 'appraisalDoc')}
          uploadedFiles={Immutable.List()}
          acceptedFileTypes={['ANY']}
        />
      );
    }

    // Don't display for customer
    if (selectedAppraiser) {
      return <div/>;
    }

    return (
      <div className="pull-right">
        {button}
        <Void pixels={10}/>
      </div>
    );
  }

  /**
   * Toggle add document error dialog
   */
  toggleAddDocError() {
    this.setState({
      addDocErrorShow: !this.state.addDocErrorShow
    });
    this.props.setProp(false, 'errors', 'addDoc');
  }

  /**
   * Button for add doc error dialog
   */
  addErrorDocActions() {
    return (
      <button className="btn btn-raised btn-info" onClick={this.toggleAddDocError}>
        Close
      </button>
    );
  }

  /**
   * Appraisal doc exists (whether visible or not)
   * @param selectedRecord
   */
  docExists(selectedRecord) {
    return typeof selectedRecord.get('docVisible') === 'boolean';
  }

  /**
   * Appraisal doc exists but is not visible to the appraiser
   */
  docVisible(selectedRecord) {
    return selectedRecord.get('docVisible') !== false;
  }

  /**
   * Table for when doc exists but is not visible
   */
  tableDocNotVisible() {
    const {selectedRecord} = this.props;
    if (this.docExists(selectedRecord) && !this.docVisible(selectedRecord)) {
      return (
        <tr>
          <td>Appraisal Document Attached</td>
          {this.extraDocCells()}
        </tr>
      );
    }
  }

  /**
   * Get primary document accepted types
   */
  getPrimaryFormats() {
    return this.props.orders.getIn(['appraisalDocs', 'formats', 'primary']).map(format => format.toUpperCase());
  }

  /**
   * Table for when doc exists and is visible
   */
  tableDocVisible() {
    const {
      selectedRecord,
      uploadedAppraisalDocs,
      orders,
    } = this.props;
    const mostRecentDoc = this.getMostRecentDoc(uploadedAppraisalDocs);
    let uploadedAt;
    if (mostRecentDoc) {
      // Uploaded date
      uploadedAt = orders.getIn(['appraisalDocs', 'createdAt']) ?
                   moment(orders.getIn(['appraisalDocs', 'createdAt'])).format('MM-DD-YYYY h:mmA') : '';
    }
    if (!this.docExists(selectedRecord) || this.docVisible(selectedRecord)) {
      return (
        <tr>
          <td>
            {mostRecentDoc &&
             uploadedAppraisalDocs.takeLast(1).map((doc, index) => {
               const docUrl = doc.get('urlEncoded') || doc.get('url');
               return (
                 <p key={index}>
                   <a href={docUrl} target="_blank" style={styles.linkColor}>
                     {doc.get('name') ? doc.get('name') : `Appraisal Document`}
                   </a>
                 </p>
               );
             })
            }
          </td>
          <td>{mostRecentDoc ? uploadedAt : 'N/A'}</td>
          {this.extraDocCells()}
        </tr>
      );
    }
  }

  /**
   * Extra format document cells
   */
  extraDocCells() {
    const {
      selectedRecord,
      formats,
      uploadedAppraisalSubDocs = Immutable.Map(),
      onDropAppraisalDocs,
      selectedAppraiser
    } = this.props;
    const docVisible = this.docVisible(selectedRecord);
    // Extra formats
    const extraFormats = formats.get('extra');
    return (
      this.docExists(selectedRecord) && extraFormats.map((format, index) => {
        const thisDoc = uploadedAppraisalSubDocs.get(format);
        const docUrl = thisDoc ? (thisDoc.get('urlEncoded') || thisDoc.get('url')) : '';
        return (
          <td key={index}>
            {!!thisDoc &&
             <div>
               {docVisible &&
                 <a href={docUrl} target="_blank" style={styles.linkColor}>
                   {thisDoc ? thisDoc.get('name') : `N/A`}
                 </a>
               }
               {!docVisible && (thisDoc ? 'Document Uploaded' : `N/A`)}
               &nbsp;&nbsp;
               {!selectedAppraiser &&
                 <MyDropzone
                   instructions="[replace]"
                   hideButton
                   onDrop={onDropAppraisalDocs.bind(this, ['subDoc', format])}
                   uploadedFiles={Immutable.List()}
                   acceptedFileTypes={[format.toUpperCase()]}
                   displayDownload={false}
                 />
               }
             </div>
            }
            {!thisDoc && !selectedAppraiser &&
             <MyDropzone
               instructions="Upload"
               hideButton
               onDrop={onDropAppraisalDocs.bind(this, ['subDoc', format])}
               uploadedFiles={thisDoc ? Immutable.List().push(thisDoc) : Immutable.List()}
               acceptedFileTypes={[format.toUpperCase()]}
               displayDownload={false}
             />
            }
          </td>
        );
      })
    );
  }

  /**
   * Whether to display the date column
   */
  displayDate(selectedRecord) {
    return (!this.docExists(selectedRecord) || this.docVisible(selectedRecord));
  }

  render() {
    const {
      selectedRecord,
      ccNumber,
      techFeePaid,
      formats,
      creditCardRejection,
      orders
    } = this.props;
    const {addDocErrorShow, showPayTechFee, documentUploading} = this.state;
    // This order requires a tech fee to be paid
    const orderHasTechFee = selectedRecord.get('techFee') > 0;
    // Extra formats
    const extraFormats = formats.get('extra');
    // Add doc error message
    const addDocError = orders.getIn(['errors', 'addDoc']) || '';
    const extraFormatCount = extraFormats.count();
    const displayDate = this.displayDate(selectedRecord);
    const width = (100 / (extraFormatCount + displayDate + 1));

    return (
      <div>
        {/*Upload appraiser doc/pay tech fee button*/}
        {this.uploadButton(orderHasTechFee, techFeePaid)}
        <table className="data-table" style={styles.dataTable}>
          <thead>
            <tr>
              <th width={width + '%'}><label className="control-label" style={styles.margin0}>Document</label></th>
              {this.displayDate(selectedRecord) &&
                <th width={width + '%'}><label className="control-label" style={styles.margin0}>Date</label></th>
              }
              {this.docExists(selectedRecord) && extraFormats.map((format, index) => {
                return (
                  <th key={index} width={width + '%'}><label className="control-label" style={styles.margin0}>{format.toUpperCase()}</label></th>
                );
              })}
            </tr>
          </thead>
          <tbody>
            {/*Doc exists, but not visible*/}
            {this.tableDocNotVisible()}
            {this.tableDocVisible()}
          </tbody>
        </table>

        {/*Pay tech fee dialog*/}
        <Dialog
          title="Pay Tech Fee"
          actions={this.techFeeDialogButtons()}
          modal
          open={showPayTechFee && !creditCardRejection}
        >
          {/*Pay tech fee if cc number is on file*/}
          {!!ccNumber &&
           <div>
             <p style={styles.normalFontWeight}>By clicking "Pay Tech Fee," you agree to pay the tech fee of ${selectedRecord.get('techFee')} using the
               credit card on file which ends in {ccNumber}.</p>
             <p style={styles.normalFontWeight}>After this fee is paid, appraisal documents may be uploaded
               for this order.</p>
           </div>
          }
          {/*No CC is on file, go to settings page*/}
          {!ccNumber &&
           <p>You must have a credit card on file to pay the tech fee associated with this order. Enter a credit card
             number in <Link to={`${SETTINGS_URL}`}>settings</Link> before proceeding.</p>
          }
        </Dialog>
        {/*Failed to pay tech fee dialog*/}
        <Dialog
          title="Unable to pay tech fee"
          actions={this.failedToPayTechFeeButtons()}
          modal
          open={!!creditCardRejection}
        >
          <p>The tech fee associated with this order has not been paid. Correct the following error and try again:</p>
          <p>{creditCardRejection}</p>
        </Dialog>
        {/*Upload doc error*/}
        <Dialog
          title="Unable to upload document"
          actions={this.addErrorDocActions()}
          modal
          open={addDocErrorShow}
        >
          {addDocError}
        </Dialog>
        {/*Upload document dialog*/}
        <UploadDialog
          message="Your appraisal document is uploading. When it is finished, this dialog will close automatically."
          documentUploading={documentUploading}
        />
      </div>
    );
  }
}
