import React, {Component, PropTypes} from 'react';
import {
  GoogleMap,
  OrdersDetails,
  OrdersEmailOffice,
  OrdersAdditionalStatuses,
  OrdersDocument,
  OrdersNotificationLog,
  OrdersRevisions,
} from 'components';

import {
  ORDERS_DETAILS
} from 'redux/modules/urls';

import SyntheticEvent from 'react/lib/SyntheticEvent';
import Immutable from 'immutable';
import {Tabs, Tab} from 'material-ui';

export default class OrdersDetailsPane extends Component {
  static propTypes = {
    // Currently selected record
    selectedRecord: PropTypes.instanceOf(Immutable.Map),
    // Move to details view
    goToDetailsView: PropTypes.func,
    // Full screen view
    fullScreen: PropTypes.bool,
    // Close details pane
    closeDetailsPane: PropTypes.func.isRequired,
    // Push state
    pushState: PropTypes.func.isRequired,
    // Location
    location: PropTypes.object.isRequired,
    // Set prop
    setProp: PropTypes.func.isRequired,
    // Orders
    orders: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Params for order ID
    params: PropTypes.object.isRequired,
    // change url
    replaceState: PropTypes.func.isRequired,
    // Toggle instructions dialog
    toggleInstructions: PropTypes.func.isRequired,
    // Selected appraiser (customer view)
    selectedAppraiser: PropTypes.number,
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
    // Upload file function
    uploadFile: PropTypes.func.isRequired,
    // Retrieve additional documents
    getAdditionalDocuments: PropTypes.func.isRequired,
    // Retrieve additional document types
    getAdditionalDocumentTypes: PropTypes.func.isRequired,
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
    // Company management
    companyManagement: PropTypes.object.isRequired
  };

  /**
   * Select tabs on initial mount
   */
  componentWillMount() {
    // Tabs to display
    this.tabs = this.determineTabs.call(this, this.props.selectedRecord);
  }

  componentDidMount() {
    const defaultTab = this.getDefaultTab();

    // check for tab in url
    const newTab = (this.props.params.tab) ? this.props.params.tab : defaultTab.get('value');

    if (newTab !== this.props.orders.get('detailsSelectedTab')) {
      this.setTab(this.props, newTab);
    }
  }

  /**
   * Make sure we're displaying the right tab if loading on this state
   */
  componentWillReceiveProps(nextProps) {
    const {selectedRecord: thisRecord} = this.props;
    const {selectedRecord: nextRecord} = nextProps;
    // Record ID
    const thisRecordId = Immutable.Map.isMap(thisRecord) ? thisRecord.get('id') : 0;
    const nextRecordId = Immutable.Map.isMap(nextRecord) ? nextRecord.get('id') : 0;
    const defaultTab = this.getDefaultTab();

    // Record selected
    if (
      (!thisRecord && nextRecord) ||
      // Selected order changes while details tab is still open
      (thisRecordId !== nextRecordId)
    ) {
      // Tabs to display
      this.tabs = this.determineTabs.call(this, nextProps.selectedRecord);

      // reset the tab on change
      if (nextProps.fullScreen) {
        // no tab so let's assume the first one here
        if (!nextProps.params.tab) {
          this.setTab(nextProps, defaultTab.get('value'));
        }

        // tab changed
        if (this.props.params.tab !== nextProps.params.tab) {
          this.setTab(nextProps, nextProps.params.tab);
        }
      } else {
        this.setTab(nextProps, defaultTab.get('value'));
      }
    }
  }

  getDefaultTab() {
    const allowedTabs = this.tabs.filter(tab => !tab.get('disabled'));
    return allowedTabs.get(0);
  }

  /**
   * Get the proper details pane inputs depending on status type
   */
  getDetailsValues() {
    const {
      selectedRecord,
      closeDetailsPane,
      goToDetailsView,
      params,
      fullScreen,
      toggleInstructions,
      orders,
      pushState,
      setPrintContent,
      removePrintContent,
      auth,
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
      companyManagement
    } = this.props;
    return (
      <OrdersDetails
        uiState={orders.get('uiState')}
        selectedRecord={selectedRecord}
        closeDetailsPane={closeDetailsPane}
        goToDetailsView={goToDetailsView}
        params={params}
        fullScreen={fullScreen}
        toggleInstructions={toggleInstructions}
        orders={orders}
        pushState={pushState}
        setPrintContent={setPrintContent}
        removePrintContent={removePrintContent}
        auth={auth}
        toggleOnHold={toggleOnHold}
        toggleResume={toggleResume}
        userType={userType}
        toggleAcceptDialog={toggleAcceptDialog}
        toggleAcceptWithConditionsDialog={toggleAcceptWithConditionsDialog}
        toggleDeclineDialog={toggleDeclineDialog}
        toggleSubmitBid={toggleSubmitBid}
        toggleScheduleInspection={toggleScheduleInspection}
        toggleInspectionComplete={toggleInspectionComplete}
        toggleReassign={toggleReassign}
        companyManagement={companyManagement}
      />
    );
  }

  /**
   * Documents tab
   * @returns {XML}
   */
  getDocumentsView() {
    const {
      uploadFile,
      getAdditionalDocuments,
      getAdditionalDocumentTypes,
      sendEmail,
      addDoc,
      getAppraisalDoc,
      getDocFormats,
      payTechFee,
      toggleAcceptDialog,
      toggleAcceptWithConditionsDialog,
      toggleDeclineDialog,
      toggleSubmitBid,
      toggleScheduleInspection,
      toggleInspectionComplete,
      toggleReassign,
      orders,
      setProp,
      auth,
      selectedRecord,
      fullScreen,
      closeDetailsPane,
      params,
      companyManagement
  } = this.props;
    return (
      <OrdersDocument
        selectedAppraiser={this.props.selectedAppraiser}
        uploadFile={uploadFile}
        orders={orders}
        setProp={setProp}
        auth={auth}
        getAdditionalDocuments={getAdditionalDocuments}
        getAdditionalDocumentTypes={getAdditionalDocumentTypes}
        selectedRecord={selectedRecord}
        fullScreen={fullScreen}
        closeDetailsPane={closeDetailsPane}
        params={params}
        sendEmail={sendEmail}
        addDoc={addDoc}
        getAppraisalDoc={getAppraisalDoc}
        getDocFormats={getDocFormats}
        payTechFee={payTechFee}
        toggleAcceptDialog={toggleAcceptDialog}
        toggleAcceptWithConditionsDialog={toggleAcceptWithConditionsDialog}
        toggleDeclineDialog={toggleDeclineDialog}
        toggleSubmitBid={toggleSubmitBid}
        toggleScheduleInspection={toggleScheduleInspection}
        toggleInspectionComplete={toggleInspectionComplete}
        toggleReassign={toggleReassign}
        companyManagement={companyManagement}
      />
    );
  }

  /**
   * Messages tab
   * @returns {XML}
   */
  getMessagesView() {
    return (
      <OrdersEmailOffice
        {...this.props}
        selectedAppraiser={this.props.selectedAppraiser}
        companyManagement={this.props.companyManagement}
      />
    );
  }

  /**
   * Notifications tab
   * @returns {XML}
   */
  getNotificationsView() {
    return (
      <OrdersNotificationLog
        {...this.props}
        selectedAppraiser={this.props.selectedAppraiser}
      />
    );
  }

  /**
   * Statuses tab
   * @returns {XML}
   */
  getStatusesView() {
    return (
      <OrdersAdditionalStatuses
        {...this.props}
        selectedAppraiser={this.props.selectedAppraiser}
        // Company management
        companyManagement={this.props.companyManagement}
      />
    );
  }

  /**
   * Map tab
   * @returns {XML}
   */
  getMapView() {
    return (
      <GoogleMap
        {...this.props}
        marker={this.props.orders.getIn(['selectedRecord', 'property'], Immutable.Map())}
      />
    );
  }

  /**
   * Revisions tab
   * @returns {XML}
   */
  getRevisionsView() {
    return (
      <OrdersRevisions
        {...this.props}
      />
    );
  }

  /**
   * Set tab on click
   * @param tabNumber Tab number
   */
  setTab(nextProps, tabValue) {
    // setup mui
    if (tabValue instanceof SyntheticEvent) return;

    nextProps.setProp(tabValue, 'detailsSelectedTab');
    if (nextProps.fullScreen) {
      nextProps.replaceState(`${ORDERS_DETAILS}/${nextProps.params.orderId}/${tabValue}`);
    }
  }

  /**
   * Determine which tabs should be shown based on process status
   * @param selectedRecord Current order
   * @returns {*[]}
   */
  determineTabs(selectedRecord) {
    const processStatus = selectedRecord ? selectedRecord.get('processStatus') : 'new';

    return Immutable.fromJS([{
      value: 'details',
      label: 'Details',
      className: 'my-tab',
      disabled: this.props.fullScreen ? true : false,
      fn: this.getDetailsValues.bind(this, selectedRecord)
    }, {
      value: 'documents',
      label: 'Documents',
      className: 'my-tab',
      disabled: ['new', 'request-for-bid'].indexOf(processStatus) !== -1,
      fn: this.getDocumentsView.bind(this, selectedRecord)
    }, {
      value: 'messages',
      label: 'Messages',
      className: 'my-tab',
      disabled: ['new', 'request-for-bid'].indexOf(processStatus) !== -1,
      fn: this.getMessagesView.bind(this, selectedRecord)
    }, {
      value: 'statuses',
      label: 'Statuses',
      className: 'my-tab',
      disabled: ['new', 'request-for-bid', 'on-hold', 'cancelled'].indexOf(processStatus) !== -1,
      fn: this.getStatusesView.bind(this, selectedRecord)
    }, {
      value: 'map',
      label: 'Map',
      className: 'my-tab',
      disabled: this.props.fullScreen ? false : true,
      fn: this.getMapView.bind(this, selectedRecord)
    }, {
      value: 'notifications',
      label: 'History',
      className: 'my-tab',
      disabled: ['new', 'request-for-bid'].indexOf(processStatus) !== -1,
      fn: this.getNotificationsView.bind(this, selectedRecord)
    }, {
      value: 'revisions',
      label: 'Revisions',
      className: 'my-tab',
      disabled: ['new', 'request-for-bid'].indexOf(processStatus) !== -1,
      fn: this.getRevisionsView.bind(this, selectedRecord)
    }]);
  }

  renderFullScreen() {
    const {selectedRecord} = this.props;
    if (!selectedRecord) {
      return <div/>;
    }

    // Hide disabled tabs
    const displayTabs = this.tabs.filter(tab => !tab.get('disabled'));
    const hasTabs = displayTabs.count() > 0;

    // get the selected tab
    const selectedTab = this.tabs.filter((tab) => {
      return tab.get('value') === this.props.orders.get('detailsSelectedTab');
    }).get(0);

    return (
      <div className="row details-full-screen">
        <div className={ !hasTabs ? 'col-md-12' : 'col-md-6' }>
          <div style={{ borderTop: '1px solid #DDDDDD' }}>
            { this.tabs.getIn([0, 'fn'])() }
          </div>
        </div>
        {hasTabs && displayTabs.count() > 1 &&
          <div className="col-md-6">
            <div data-details-pane>
              {/*Pane view*/}
              <Tabs justified value={selectedTab.get('value')} className="my-tabs" inkBarStyle={{ display: 'none' }} onChange={this.setTab.bind(this, this.props)}>
                {displayTabs.map((displayTab, index) => {
                  let className = displayTab.get('className');
                  if (selectedTab.get('value') === displayTab.get('value')) {
                    className += ' my-active-tab';
                  }
                  return (
                    <Tab
                      value={displayTab.get('value')}
                      label={displayTab.get('label')}
                      className={className}
                      key={index}
                      disabled={displayTab.get('disabled')}
                    >
                      {displayTab.get('fn')()}
                    </Tab>
                  );
                })}
              </Tabs>
            </div>
          </div>
        }
        {hasTabs && displayTabs.count() === 1 &&
          <div className="col-md-6">
            <div data-details-pane>
              {displayTabs.getIn([0, 'fn'])()}
            </div>
          </div>
        }
      </div>
    );
  }

  renderPane() {
    const {selectedRecord} = this.props;

    if (!selectedRecord) {
      return <div/>;
    }

    // Hide disabled tabs
    const displayTabs = this.tabs.filter(tab => !tab.get('disabled'));

    // get the selected tab
    const selectedTab = this.tabs.filter((tab) => {
      return tab.get('value') === this.props.orders.get('detailsSelectedTab');
    }).get(0);

    return (
      <div data-details-pane className="details-pane">
        {/*Pane view*/}
        {displayTabs.count() > 1 &&
          <Tabs justified value={selectedTab.get('value')} className="my-tabs" inkBarStyle={{ display: 'none' }} onChange={this.setTab.bind(this, this.props)}>
            {displayTabs.map((displayTab, index) => {
              let className = displayTab.get('className');
              if (displayTab.get('value') === selectedTab.get('value')) {
                className += ' my-active-tab';
              }
              return (
                <Tab
                  value={displayTab.get('value')}
                  label={displayTab.get('label')}
                  className={className}
                  key={index}
                  disabled={displayTab.get('disabled')}
                >
                  {displayTab.get('fn')()}
                </Tab>
              );
            })}
          </Tabs>
        }
        {displayTabs.count() === 1 &&
          <span>{displayTabs.getIn([0, 'fn'])()}</span>
        }
      </div>
    );
  }

  render() {
    if (this.props.fullScreen) {
      return this.renderFullScreen();
    } else {
      return this.renderPane();
    }
  }
}
