import React, {Component, PropTypes} from 'react';
import Immutable from 'immutable';
import moment from 'moment';

export default class ExistingEmailOfficeMessages extends Component {
  static propTypes = {
    // Messages
    messages: PropTypes.instanceOf(Immutable.List)
  };

  /**
   * Conditionally render
   * @param nextProps
   */
  shouldComponentUpdate(nextProps) {
    return this.props.messages !== nextProps.messages;
  }

  render() {
    const {messages} = this.props;
    // Sort messages by newest to oldest
    const sortedMessages = messages.count() ? messages.sort((thisMessage, nextMessage) => {
      if (thisMessage.get('createdAt') === nextMessage.get('createdAt')) {
        return 0;
      }
      return thisMessage.get('createdAt') < nextMessage.get('createdAt') ? 1 : -1;
    }) : Immutable.List();
    return (
      <div>
        {!!sortedMessages.count() && sortedMessages.map((message, index) => {
          let nameDisplay = '';

          switch (message.getIn(['sender', 'type'])) {
            case 'customer':
              nameDisplay = (message.get('employee') ? message.get('employee') + ' - ' : '') + message.getIn(['sender', 'name']);
              break;
            case 'amc':
              nameDisplay = message.getIn(['sender', 'displayName']);
              break;
            default:
              nameDisplay = message.getIn(['sender', 'firstName']) + ' ' + message.getIn(['sender', 'lastName']) + ' - ' + message.getIn(['sender', 'companyName']);
              break;
          }

          return (
            <div key={index}>
              <div className="row">
                <div className="col-md-1 text-center" style={{ padding: '5px' }}>
                  <i className="material-icons" style={{ background: '#CCCCCC', color: '#FFFFFF', borderRadius: '14px', fontSize: '30px' }}>person</i>
                </div>
                <div className="col-md-8" style={{ padding: '5px' }}>
                  <div style={{ fontWeight: 'bold' }}>{nameDisplay}</div>
                  <div dangerouslySetInnerHTML={{ __html: message.get('content').replace(new RegExp('\n', 'g'), '<br />') }}></div>
                </div>
                <div className="col-md-3" style={{ textAlign: 'right', fontWeight: 'bold', padding: '5px' }}>
                  {moment(message.get('createdAt')).format('MM/DD/YYYY h:mm A')}
                </div>
              </div>
            </div>
          );
        })}
        {!sortedMessages.count() &&
         <div className="row">
            <div className="col-md-12">No new messages are available</div>
          </div>
        }
      </div>
    );
  }
}
