import React, {PropTypes, Component} from 'react';
import Immutable from 'immutable';

import {
  ActionButton,
  DividerWithIcon,
  ExistingEmailOfficeMessages,
  OrdersActionButtons,
  OrdersDetailsHeader,
} from 'components';

import MessageInput from './MessageInput';

/**
 * Email office in orders details pane
 */
export default class OrdersEmailOffice extends Component {
  static propTypes = {
    // Retrieve messages on load
    getMessages: PropTypes.func.isRequired,
    // Orders
    orders: PropTypes.instanceOf(Immutable.Map),
    // Submit new message
    newMessage: PropTypes.func.isRequired,
    // Set a property
    setProp: PropTypes.func.isRequired,
    // Full screen
    fullScreen: PropTypes.bool,
    // Close details pane
    closeDetailsPane: PropTypes.func.isRequired,
    // Selected order
    selectedRecord: PropTypes.instanceOf(Immutable.Map),
    // URL params
    params: PropTypes.object.isRequired,
    // Auth
    auth: PropTypes.instanceOf(Immutable.Map),
    // Set print content
    setPrintContent: PropTypes.func.isRequired,
    // Remove print content
    removePrintContent: PropTypes.func.isRequired,
    // Toggle instruction
    toggleInstructions: PropTypes.func.isRequired,
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

  constructor(props) {
    super(props);

    this.state = {
      error: false,
    };

    this.changeMessage = ::this.changeMessage;
    this.pusherMessage = ::this.pusherMessage;
    this.sendMessage = ::this.sendMessage;
    this.getPrintContent = ::this.getPrintContent;
  }

  componentWillReceiveProps(nextProps) {
    const {selectedAppraiser} = this.props;
    const {selectedAppraiser: nextSelectedAppraiser} = nextProps;

    if (selectedAppraiser !== nextSelectedAppraiser) {
      if (selectedAppraiser) {
        this.pusherUnbind();
      }

      this.pusherBind();
    }

    if (!nextSelectedAppraiser) {
      this.pusherUnbind();
    }
  }

  componentDidMount() {
    this.pusherBind();
  }

  componentWillUnmount() {
    this.pusherUnbind();
  }

  /**
   * Retrieve existing messages on mount
   */
  pusherBind() {
    const {channel} = this.context.pusher;
    // Pusher message
    if (channel) {
      channel.bind('order:send-message', this.pusherMessage, this);
    }
  }

  /**
   * Unbind pusher
   */
  pusherUnbind() {
    const {channel} = this.context.pusher;
    // since we have a context we can remove all the events in that context
    if (channel) {
      channel.unbind('order:send-message', this.pusherMessage, this);
    }
  }

  /**
   * Set message prop
   * @param event
   */
  changeMessage(event) {
    this.setState({
      error: false,
    });

    this.props.setProp(event.target.value, 'emailOffice', 'message');
  }

  /**
   * Clear the current message
   */
  clearMessage() {
    this.props.setProp('', 'emailOffice', 'message');
  }

  /**
   * Pusher message update event
   * @param update
   */
  pusherMessage(update) {
    const {getMessages, auth} = this.props;
    getMessages(auth.get('user'), update.order.id);
  }

  /**
   * Send a new message related to this order
   */
  sendMessage() {
    const {newMessage, auth, selectedRecord, orders} = this.props;
    if (!orders.getIn(['emailOffice', 'message'])) {
      this.setState({
        error: true,
      });
    } else {
      newMessage(auth.get('user'), selectedRecord.get('id'), orders.getIn(['emailOffice', 'message']));
    }
  }

  /**
   * Returns the tab content without the header
   */
  tabContent(forPrinting = false) {
    const {orders, selectedAppraiser = null} = this.props;
    const emailOffice = orders.get('emailOffice');
    const message = emailOffice.get('message');

    return (
      <div>
        {!forPrinting &&
          <div>
            {!selectedAppraiser &&
              <div>
                <MessageInput
                  value={message}
                  onChange={this.changeMessage}
                />
                <div className="row" style={{ paddingBottom: '10px' }}>
                  <div className="col-md-12">
                    <div className="error-display pull-left" style={{ display: (this.state.error ? '' : 'none') }}>
                      You must enter a message to send.
                    </div>
                    <ActionButton
                      type="submit"
                      text="Send Message"
                      onClick={this.sendMessage}
                      additionalClasses="pull-right"
                      disabled={!!orders.get('submittingNewMessage') || (typeof message === 'string' ? !message.trim() : false)}
                    />
                  </div>
                </div>
              </div>
            }
          </div>
        }
        <div className="row">
          <div className="col-md-12">
            <DividerWithIcon
              label="Messages"
              icon="message"
            />
          </div>
        </div>
        <ExistingEmailOfficeMessages
          messages={emailOffice.get('messages')}
        />
      </div>
    );
  }

  /**
   * Returns the content that should be renderd for printing
   */
  getPrintContent() {
    return this.tabContent(true);
  }

  render() {
    const {
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
    return (
      <div className="container-fluid details-cont">
        <OrdersDetailsHeader
          fullScreen={fullScreen}
          closeDetailsPane={closeDetailsPane}
          selectedRecord={selectedRecord}
          params={params}
          mapPrint
          setPrintContent={setPrintContent}
          getPrintContent={this.getPrintContent}
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
        {this.tabContent()}
      </div>
    );
  }
}
