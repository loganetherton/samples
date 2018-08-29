import React, {Component, PropTypes} from 'react';

import {ActionButton} from 'components';

import {Dialog} from 'material-ui';

import pureRender from 'pure-render-decorator';

@pureRender
export default class UserGuide extends Component {
  static propTypes = {
    // Close user guide modal
    closeUserGuide: PropTypes.func.isRequired,
    // Whether or not the modal should be opened
    open: PropTypes.bool.isRequired,
  }

  closeButton() {
    const {closeUserGuide} = this.props;

    return (
      <ActionButton
        type="cancel"
        text="Close"
        onClick={closeUserGuide}
      />
    );
  }

  render() {
    const {open} = this.props;

    return (
      <Dialog
        title="User Guide"
        actions={this.closeButton()}
        modal
        open={open}
      >
        <h1>Coming soon</h1>
      </Dialog>
    );
  }
}
