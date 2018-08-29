import React, {Component, PropTypes} from 'react';
import Immutable from 'immutable';
import {
  ActionButton
} from 'components';
import {Void} from 'components';
import {Dialog} from 'material-ui';

const styles = {pointer: {cursor: 'pointer'}};

export default class EnableNotifications extends Component {
  static propTypes = {
    // Selected customers
    selected: PropTypes.instanceOf(Immutable.Map),
    // Get notification settings
    getNotification: PropTypes.func.isRequired,
    // Set notification settings
    setNotification: PropTypes.func.isRequired,
    // Customers
    customers: PropTypes.instanceOf(Immutable.List),
    // Select a customer
    selectCustomer: PropTypes.func.isRequired,
    // Remove a customer
    removeCustomer: PropTypes.func.isRequired,
    // User
    user: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Settings
    settings: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Selected appraiser (customer view)
    selectedAppraiser: PropTypes.number
  };

  /**
   * State for dialogs
   */
  constructor(props) {
    super(props);
    this.state = {
      setNotifications: false,
      setNotificationsSuccess: null
    };

    this.closeNotificationUpdate = ::this.closeNotificationUpdate;
    this.saveNotifications = ::this.saveNotifications;
    this.selectCustomer = ::this.selectCustomer;
  }

  /**
   * Retrieve existing ACH info on load
   */
  componentDidMount() {
    const {user, getNotification, selectedAppraiser} = this.props;
    // Retrieve notifications
    if (user.get('type') !== 'customer') {
      getNotification(user);
    } else if (selectedAppraiser) {
      getNotification(user, selectedAppraiser);
    }
  }

  componentWillReceiveProps(nextProps) {
    // Auth this screen
    const {settings, selectedAppraiser, getNotification} = this.props;
    const {settings: nextSettings, selectedAppraiser: nextSelectedAppraiser, user} = nextProps;
    // Switch appraiser on this view
    if (selectedAppraiser !== nextSelectedAppraiser) {
      getNotification(user, nextSelectedAppraiser);
    }
    // Display update message
    if (typeof settings.get('setNotificationSuccess') === 'undefined' &&
        typeof nextSettings.get('setNotificationSuccess') === 'boolean') {
      this.setState({
        setNotifications: true,
        setNotificationsSuccess: nextSettings.get('setNotificationSuccess')
      });
    }
  }

  /**
   * Select/deselect a customer
   */
  selectCustomer(event) {
    if (event.target.checked) {
      this.props.selectCustomer(event.target.value);
    } else {
      this.props.removeCustomer(event.target.value);
    }
  }

  saveNotifications() {
    const {user, selected} = this.props;
    this.props.setNotification(user.get('id'), selected);
  }

  /**
   * Close update dialog
   */
  closeNotificationUpdate() {
    this.setState({
      setNotifications: false
    });
  }

  render() {
    const {customers, selectedAppraiser} = this.props;
    const {setNotifications, setNotificationsSuccess} = this.state;

    return (
      <div>
        <h3>Enable Email Notifications</h3>
        <div className="row">
          <div className="col-md-12">
            {customers.map((customer, index) =>
              <div className="col-md-3" key={index}>
                <label>
                  <input style={styles.pointer} type="checkbox" defaultValue={customer.getIn(['customer', 'id'])} defaultChecked={customer.get('email')} onClick={this.selectCustomer} disabled={!!selectedAppraiser}/>
                  <span style={styles.pointer}> {customer.getIn(['customer', 'name'])}</span>
                </label>
              </div>
            )}
          </div>
        </div>
        {!selectedAppraiser &&
         <div className="row">
           <div className="col-md-12">
             <Void pixels={10}/>

             <ActionButton onClick={this.saveNotifications} text="Update Notifications" type="submit" />
           </div>
         </div>
        }
        {/*Update email notifications*/}
        <Dialog
          open={setNotifications}
          actions={
            <button className="btn btn-raised btn-info"
              onClick={this.closeNotificationUpdate}>Close</button>
          }
          title={setNotificationsSuccess ? 'Email Notifications Updated' : 'Failed To Update Email Notifications'}
        >
          <h4>
            {setNotificationsSuccess ? 'Your email notifications have been updated' :
             'An error has occurred, and your email notifications have not been updated'}
          </h4>
        </Dialog>
      </div>
    );
  }
}
