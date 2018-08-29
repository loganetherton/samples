import React, {Component, PropTypes} from 'react';
import {Dialog} from 'material-ui';
import {ActionButton} from 'components';

const styles = require('./WelcomeMessage.scss');

export default class WelcomeMessage extends Component {
  static propTypes = {
    // auth
    auth: PropTypes.object.isRequired,
    // Hide initial display
    hideInitialDisplay: PropTypes.func.isRequired
  }

  componentWillMount() {
    const {auth} = this.props;

    this.setState({
      showModal: auth.getIn(['user', 'showInitialDisplay']) || false,
      hide: false
    });
  }

  /**
   * Close the modal dialog
   */
  closeModal() {
    const {auth, hideInitialDisplay} = this.props;

    this.setState({
      showModal: false
    });

    if (this.state.hide) {
      hideInitialDisplay(auth.getIn(['user', 'id']));
    }
  }

  /**
   * Returns a button for the modal
   */
  closeButton() {
    return (
      <ActionButton type="submit" text="OK" onClick={::this.closeModal} />
    );
  }

  /**
   * Toggles the "hide initial display forever" state
   */
  hideForever() {
    this.setState({
      hide: !this.state.hide
    });
  }

  render() {
    return (
      <Dialog
        modal
        open={this.state.showModal}
        className={styles['welcome-message']}
        actions={this.closeButton()}
      >
        <img className={styles.logo} src="/img/logo.png" />
        <h1 className={styles['main-text']}>Welcome to ValuePad!</h1>
        <p className={styles['main-text']}>
          We're glad you're here. Before getting started, a couple of things...
        </p>
        <ol className={styles['to-do-list']}>
          <li>
            Update your bookmarks - <a href="https://app.valuepad.com" className="link">app.valuepad.com</a> is now your destination
          </li>
          <li>
            Click on My Account in the upper right, and go through Settings
          </li>
          <li>
            Click on My Account in the upper right, and go through Profile
          </li>
        </ol>
        <label className={styles['dont-show-label']}>
          <input type="checkbox" name="welcomeMessageSeen" onChange={::this.hideForever} />
          <span className={styles['dont-show']}>Don't show me this again</span>
        </label>
      </Dialog>
    );
  }
}
