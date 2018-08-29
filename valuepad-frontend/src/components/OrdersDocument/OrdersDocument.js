import React, {Component, PropTypes} from 'react';

import {
  MyDropzone,
  Void,
  OrdersActionButtons,
  OrdersDetailsHeader,
  AppraisalDocumentTable,
  SmartTextField,
  DocumentsTable,
  DividerWithIcon,
  VpPlainDropdown,
  VpTextField
} from 'components';
import Immutable from 'immutable';
import {
  Snackbar,
  FlatButton,
  Divider,
  Dialog
} from 'material-ui';

const snackDuration = 4000;

const styles = {
  mb4: { marginBottom: '4px' },
  mb10: { marginBottom: '10px' }
};

export default class OrdersDocument extends Component {
  static propTypes = {
    // Upload file function
    uploadFile: PropTypes.func.isRequired,
    // Orders
    orders: PropTypes.instanceOf(Immutable.Map),
    // Set a property explicitly
    setProp: PropTypes.func.isRequired,
    // Auth
    auth: PropTypes.instanceOf(Immutable.Map),
    // Retrieve additional documents
    getAdditionalDocuments: PropTypes.func.isRequired,
    // Retrieve additional document types
    getAdditionalDocumentTypes: PropTypes.func.isRequired,
    // Currently selected record
    selectedRecord: PropTypes.instanceOf(Immutable.Map).isRequired,
    // If in fullscreen view
    fullScreen: PropTypes.bool,
    // Close details pane
    closeDetailsPane: PropTypes.func.isRequired,
    // URL params
    params: PropTypes.object.isRequired,
    // Send email
    sendEmail: PropTypes.func.isRequired,
    // Add document
    addDoc: PropTypes.func.isRequired,
    // Get existing appraisal doc
    getAppraisalDoc: PropTypes.func.isRequired,
    // Get sub doc formats
    getDocFormats: PropTypes.func.isRequired,
    // Pay tech fee
    payTechFee: PropTypes.func.isRequired,
    // Selected appraiser (customer view)
    selectedAppraiser: PropTypes.number,
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

  static contextTypes = {
    pusher: PropTypes.object
  };

  /**
   * Snack bar state
   */
  constructor(props) {
    super(props);
    this.state = {
      snackOpen: false,
      snackMessage: '',
      // Display email dialog
      showEmailDialog: false,
      // Other type label dialog
      otherLabel: false,
      // Other label error
      otherLabelError: false
    };
  }

  /**
   * Retrieve additional document types if authenticating on this page
   * @param nextProps
   */
  componentWillReceiveProps(nextProps) {
    const {orders, addDoc} = this.props;
    const {auth: nextAuth, selectedRecord, orders: nextOrders} = nextProps;
    const nextUser = nextAuth.get('user');
    // Document emailed
    if (!orders.get('emailDocSuccess') && nextOrders.get('emailDocSuccess')) {
      this.setState({
        showEmailDialog: false
      });
    }
    // Document uploaded
    if (!orders.get('fileUploadSuccess') && nextOrders.get('fileUploadSuccess')) {
      const appraisalDocs = orders.get('appraisalDocs');
      const nextAppraisalDocs = nextOrders.get('appraisalDocs');
      const mostRecentAppraisalDoc = nextAppraisalDocs.get('appraisalDoc') ?
                                     nextAppraisalDocs.get('appraisalDoc').last() : Immutable.Map();
      // Appraisal doc uploaded
      if (appraisalDocs.get('appraisalDoc').count() !== nextAppraisalDocs.get('appraisalDoc').count()) {
        addDoc(nextUser, selectedRecord.get('id'), mostRecentAppraisalDoc.get('id'),
          mostRecentAppraisalDoc.get('token'), 'appraisalDoc');
      }
      // Sub doc uploaded
      if (!Immutable.is(appraisalDocs.get('appraisalSubDocs'), nextAppraisalDocs.get('appraisalSubDocs'))) {
        this.addSubDoc.call(this, nextAppraisalDocs, nextUser, selectedRecord);
      }
      // Additional doc uploaded
      if (nextProps.orders.get('addingAdditionalDoc') && !nextProps.orders.get('addingDoc')) {
        const additionalDoc = nextProps.orders.get('addingAdditionalDoc');
        let name = additionalDoc.get('name');
        // Set customer label for other doc types
        if (this.checkIfDocIsOther.call(this, orders.getIn(['appraisalDocs', 'selectedDocType']))) {
          name = orders.getIn(['appraisalDocs', 'otherLabel']);
        }
        // add the new document
        addDoc(
          nextUser,
          selectedRecord.get('id'),
          additionalDoc.get('id'),
          additionalDoc.get('token'),
          'additionalDoc',
          {
            type: nextProps.orders.getIn(['appraisalDocs', 'selectedDocType']),
            label: name,
            document: additionalDoc.get('id')
          }
        ).then(() => {
          this.props.getAdditionalDocuments(nextUser, selectedRecord.get('id'));
          this.props.setProp(0, 'appraisalDocs', 'selectedDocType');
        });
      }
    }
  }

  /**
   * Upload appraisal documents
   * @param type Upload type (appraisal doc, sub doc, additional doc)
   * @param files Array of files being uploaded
   */
  onDropAppraisalDocs(type, files) {
    const {orders} = this.props;
    const primaryFormats = orders.getIn(['appraisalDocs', 'formats', 'primary']);
    let acceptedTypes, message;
    if (type === 'appraisalDoc') {
      acceptedTypes = primaryFormats.toJS();
    // Sub doc
    } else if (Array.isArray(type) && type.length === 2 && type[0] === 'subDoc') {
      acceptedTypes = [type[1]];
      type = type[0];
    } else {
      acceptedTypes = ['png', 'doc', 'docx', 'jpeg', 'jpg', 'xls', 'xlsx', 'gif', 'txt', 'pdf'];
      // Make sure a doc type is selected
      if (!orders.getIn(['appraisalDocs', 'selectedDocType'])) {
        return this.setState({
          snackOpen: true,
          snackMessage: 'You must select a document type'
        });
      }
      message = 'Only png, doc, docx, jpeg, jpg, xls, xlsx, gif, txt, pdf file types allowed.';
    }
    this.uploadAppraisalDoc.call(this, files, type, acceptedTypes, message);
  }

  /**
   * Add an appraisal sub doc
   * @param docs Appraisal docs
   * @param user
   * @param selectedRecord
   */
  addSubDoc(docs, user, selectedRecord) {
    const subDocs = docs.get('appraisalSubDocs');
    const uploadedType = docs.get('mostRecentType');
    const docList = subDocs.toList().map(doc => {
      if (doc.get('format') !== uploadedType) {
        return doc.get('id');
      }
      return doc;
    });
    this.props.addDoc(user, selectedRecord.get('id'), subDocs.getIn([uploadedType, 'id']), docList.toJS(), 'subDoc');
  }

  /**
   * Upload appraisal document
   * @param files
   * @param type Document type
   * @param acceptedTypes Array of accepted file types
   * @param message Customer snackbar message
   */
  uploadAppraisalDoc(files, type, acceptedTypes, message) {
    files = Array.isArray(files) ? files : [files];
    files.forEach(file => {
      // Get extension
      const split = file.name.split('.');
      const fileType = split[split.length - 1].toLowerCase();
      // Check if extension is allowed
      if (acceptedTypes.length && acceptedTypes.indexOf(fileType) === -1) {
        this.setState({
          snackOpen: true,
          snackMessage: message ? message : `File type .${fileType} is not allowed`
        });
      } else {
        this.props.uploadFile('document', file, type);
      }
    });
  }

  /**
   * Close snackbar
   */
  closeSnackbar() {
    this.setState({
      snackOpen: false
    });
  }

  /**
   * Toggle one of the dialogs
   */
  toggleDialog(stateType, propType) {
    // Currently open
    const open = this.state[stateType];
    // Clear input if opening
    if (!open && propType && typeof propType === 'string') {
      this.props.setProp('', 'additionalDocs', propType);
    }
    this.setState({
      [stateType]: !open
    });
  }

  /**
   * Email appraisal document
   */
  emailAppraisalDocument() {
    const {orders, auth, sendEmail} = this.props;
    sendEmail(auth.getIn(['user', 'id']), orders.getIn(['selectedRecord', 'id']));
  }

  /**
   * Enter email address for recipient of email doc
   */
  changeEmailAddress(event) {
    this.props.setProp(event.target.value, 'appraisalDocs', 'appraisalDocEmailRecipient', 'validate');
  }

  /**
   * Change selected document type
   */
  changeSelectedDocumentType(event) {
    const {setProp} = this.props;
    const value = parseInt(event.target.value, 10);
    // Open label if other selected
    if (this.checkIfDocIsOther.call(this, value)) {
      this.setState({
        otherLabel: true,
        otherLabelError: false
      });
      setProp('', 'appraisalDocs', 'otherLabel');
    }
    setProp(value, 'appraisalDocs', 'selectedDocType');
  }

  checkIfDocIsOther(value) {
    const docTypes = this.formatDocTypes(this.props.orders.getIn(['appraisalDocs', 'additionalDocTypes']));
    // See which type selected
    const selectedDocType = docTypes.filter(docType => docType.get('value') === value).get(0);
    return selectedDocType.get('name') === 'Other';
  }

  /**
   * Email appraisal doc dialog
   */
  emailDialog() {
    const orders = this.props.orders;
    // Email recipient
    const appraisalDocEmailRecipient = orders.getIn(['appraisalDocs', 'appraisalDocEmailRecipient']);

    // Email recipient error
    const emailRecipientError = orders.getIn(['errors', 'appraisalDocs', 'appraisalDocEmailRecipient']);
    let emailError = emailRecipientError ? emailRecipientError[0] : '';
    // Invalid, frontend error
    if (emailError && /valid email/.test(emailError)) {
      emailError = 'Enter a valid email address';
    }

    // Email dialog actions
    const actions = [
      <FlatButton
        label="Cancel"
        secondary
        onTouchTap={this.toggleDialog.bind(this, 'showEmailDialog', 'appraisalDocEmailRecipient')}
      />,
      <FlatButton
        label="Email report"
        primary
        onTouchTap={::this.emailAppraisalDocument}
        disabled={!!(emailError || !appraisalDocEmailRecipient)}
      />
    ];
    return (
    <Dialog
      title="Email appraisal document"
      actions={actions}
      modal
      open={this.state.showEmailDialog}
    >

      <SmartTextField
        value={appraisalDocEmailRecipient}
        floatingLabelText="Email address"
        onChange={::this.changeEmailAddress}
        onEnterKeyDown={::this.emailAppraisalDocument}
        fullWidth
        errorText={emailError}
      />
    </Dialog>
    );
  }

  /**
   * Template to create upload for a sub document
   * @param subDocs Current sub-docs
   * @param type Doc type
   * @param index Iterator index
   */
  uploadSubDocTemplate(subDocs, type, index) {
    let subDoc = subDocs.get(type);
    // Make sure we have a list
    if (Immutable.Map.isMap(subDoc)) {
      subDoc = Immutable.List().push(subDoc);
    }
    return (
      <div className="row" key={index}>
        {index !== 0 &&
         <div>
           <Void pixels="10"/>
           <Divider/>
           <Void pixels="10"/>
         </div>
        }
        <div className="col-md-12">
          <div className="text-center">
            <p><strong>Upload {type.toUpperCase()} file</strong></p>
            <MyDropzone
              onDrop={this.onDropAppraisalDocs.bind(this, ['subDoc', type])}
              uploadedFiles={subDoc || Immutable.List()}
              acceptedFileTypes={[type.toUpperCase()]}
              noInstructions
              displayDownload={false}
            />
          </div>
        </div>
      </div>
    );
  }

  /**
   * Pay a tech fee associated with an order
   */
  payTechFee() {
    const {selectedRecord, payTechFee, auth} = this.props;
    payTechFee(auth.getIn(['user', 'id']), selectedRecord.get('id'));
  }

  /**
   * Format doc types for VpPlainDropdown
   */
  formatDocTypes(docTypes) {
    if (!docTypes || !this.props.orders.get('getAdditionalDocumentTypesSuccess')) {
      return Immutable.List();
    }
    return docTypes.map(docType => docType.set('value', docType.get('id')).set('name', docType.get('title'))).unshift(Immutable.fromJS({
      value: 0, name: 'Select a document type'
    }));
  }

  /**
   * Change "other" additional doc label
   */
  changeOtherTypeLabel(event) {
    const {setProp, orders} = this.props;
    const value = event.target.value;
    // Set error if no label selected
    if (orders.getIn(['appraisalDocs', 'otherLabel']) && !value) {
      this.setState({
        otherLabelError: true
      });
      // Remove error
    } else if (this.state.otherLabelError) {
      this.setState({
        otherLabelError: false
      });
    }
    setProp(event.target.value, 'appraisalDocs', 'otherLabel');
  }

  /**
   * Close the "other" type label dialog
   */
  submitOtherTypeLabel() {
    if (this.props.orders.getIn(['appraisalDocs', 'otherLabel'])) {
      this.setState({
        otherLabel: false
      });
    }
  }

  /**
   * Extract appraisal docs from the current reducer
   */
  extractAppraisalDocs(appraisalDocs) {
    let docs = [];
    const {orders} = this.props;
    // Appraisal doc
    if (orders.get('getAppraisalDocSuccess')) {
      docs = docs.concat([appraisalDocs.get('appraisalDoc'), appraisalDocs.get('appraisalSubDocs')]);
    } else {
      docs = docs.concat([Immutable.List(), Immutable.Map()]);
    }
    // Additional doc
    if (orders.get('getAdditionalDocumentsSuccess')) {
      docs.push(appraisalDocs.get('additionalDoc'));
    } else {
      docs.push(Immutable.List());
    }
    return docs;
  }

  render() {
    const {snackOpen, snackMessage, otherLabel, otherLabelError} = this.state;
    const {
      orders,
      fullScreen,
      closeDetailsPane,
      params,
      selectedRecord,
      setProp,
      selectedAppraiser = null,
      toggleAcceptDialog,
      toggleAcceptWithConditionsDialog,
      toggleDeclineDialog,
      toggleSubmitBid,
      toggleScheduleInspection,
      toggleInspectionComplete,
      toggleReassign,
      getAppraisalDoc,
      companyManagement,
      auth} = this.props;
    // Docs
    const appraisalDocs = orders.get('appraisalDocs');
    const [uploadedAppraisalDocs, uploadedAppraisalSubDocs, uploadedAdditionalDocs] = this.extractAppraisalDocs.call(
      this, appraisalDocs);
    // Formats
    const formats = appraisalDocs.get('formats');
    // Doc types
    const docTypes = this.formatDocTypes(appraisalDocs.get('additionalDocTypes'));
    // Selected doc type
    const selectedDocType = this.props.orders.getIn(['appraisalDocs', 'selectedDocType']);

    return (
      <div className="container-fluid details-cont">
        <OrdersDetailsHeader
          fullScreen={fullScreen}
          closeDetailsPane={closeDetailsPane || function() {}}
          selectedRecord={selectedRecord}
          params={params}
        />
        <OrdersActionButtons
          toggleAcceptDialog={toggleAcceptDialog}
          toggleAcceptWithConditionsDialog={toggleAcceptWithConditionsDialog}
          toggleDeclineDialog={toggleDeclineDialog}
          toggleSubmitBid={toggleSubmitBid}
          toggleScheduleInspection={toggleScheduleInspection}
          toggleInspectionComplete={toggleInspectionComplete}
          toggleReassign={toggleReassign}
          order={selectedRecord}
          auth={auth}
          withLabels
          companyManagement={companyManagement}
          wrapper={(data) => {
            return (
              <div className="row">
                <div className="col-md-12 text-center" style={styles.mb4}>
                  {data}
                </div>
              </div>
            );
          }}
        />

        <div className="row">
          <div className="col-md-12">
            <DividerWithIcon
              label="Appraisal Documents"
              icon="description"
            />
          </div>
        </div>

        <div className="row">
          <div className="col-md-12" style={styles.mb10}>
            <AppraisalDocumentTable
              orders={orders}
              uploadedAppraisalDocs={uploadedAppraisalDocs}
              uploadedAppraisalSubDocs={uploadedAppraisalSubDocs}
              formats={formats}
              selectedRecord={selectedRecord}
              ccNumber={orders.get('ccNumber')}
              onDropAppraisalDocs={::this.onDropAppraisalDocs}
              techFeePaid={selectedRecord.get('isTechFeePaid')}
              payTechFee={::this.payTechFee}
              creditCardRejection={orders.get('creditCardRejection')}
              setProp={setProp}
              auth={auth}
              selectedAppraiser={selectedAppraiser}
              getAppraisalDoc={getAppraisalDoc}
            />
          </div>
        </div>

        <div className="row">
          <div className="col-md-12">
            <DividerWithIcon
              label="Additional Documents"
              icon="description"
            />
          </div>
        </div>

        {!selectedAppraiser &&
          <div className="row">
            <div className="col-md-7">
              <VpPlainDropdown
                options={docTypes}
                value={selectedDocType}
                onChange={::this.changeSelectedDocumentType}
                label="Additional Document Type"
              />
            </div>
            <div className="col-md-5">
              <Void pixels="19"/>
              {selectedDocType !== 0 &&
                <MyDropzone
                  refName="additional-docs"
                  onDrop={this.onDropAppraisalDocs.bind(this, 'additionalDoc')}
                  uploadedFiles={uploadedAdditionalDocs.slice(0, 1)}
                  acceptedFileTypes={['ANY']}
                  instructions="Upload Document"
                  displayDownload={false}
                />
              }
            </div>
          </div>
        }
        {!!uploadedAdditionalDocs.count() &&
          <div className="row">
            <div className="col-md-12" style={styles.mb10}>
              <DocumentsTable
                uploadedAdditionalDocs={uploadedAdditionalDocs}
              />
            </div>
          </div>
        }
        {/*Error snackbar*/}
        <Snackbar
          open={snackOpen}
          message={snackMessage}
          autoHideDuration={snackDuration}
          onRequestClose={::this.closeSnackbar}
        />
        {/*Email dialog*/}
        {/*this.emailDialog.call(this)*/}
        {/*Create label for additional doc type "other"*/}
        <Dialog
          open={otherLabel}
          actions={<FlatButton
            label="Submit"
            primary
            keyboardFocused
            disabled={!appraisalDocs.get('otherLabel')}
            onTouchTap={::this.submitOtherTypeLabel}
          />}
          title="Document Label"
        >
          <VpTextField
            name="bankName"
            value={appraisalDocs.get('otherLabel')}
            label="Label"
            onChange={::this.changeOtherTypeLabel}
            enterFunction={::this.submitOtherTypeLabel}
            error={otherLabelError ? 'A label must be specified' : ''}
          />
        </Dialog>
      </div>
    );
  }
}
