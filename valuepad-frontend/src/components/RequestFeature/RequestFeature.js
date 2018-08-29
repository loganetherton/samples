import React, {Component, PropTypes} from 'react';
import {
  ActionButton,
  VpTextField
} from 'components';

import {Dialog} from 'material-ui';

export default class RequestFeature extends Component {
  static propTypes = {
    // Open
    open: PropTypes.bool.isRequired,
    // Close dialog
    closeDialog: PropTypes.func.isRequired,
    // Send feature
    sendFeature: PropTypes.func.isRequired,
    // Form
    value: PropTypes.string.isRequired,
    // Change value
    changeValue: PropTypes.func.isRequired
  };

  render() {
    const {open, closeDialog, sendFeature, value, changeValue} = this.props;
    const actions = [
      <ActionButton
        type="cancel"
        text="Cancel"
        onClick={closeDialog}
      />,
      <ActionButton
        style={{ marginLeft: '10px' }}
        type="submit"
        text="Send Feature"
        onClick={sendFeature}
        disabled={!(typeof value === 'string' && value.length)}
      />
    ];
    return (
      <Dialog
        title="Request a Feature"
        actions={actions}
        modal
        open={open}
      >
        <p>
          Thank you for your interest in sharing your ideas with us.
          Use this form to request new features or suggest modifications to existing features.
        </p>

        <VpTextField
          name="description"
          placeholder="Enter you request"
          value={value}
          fullWidth
          multiLine
          onChange={changeValue}
          enterFunction={sendFeature}
          minRows={3}
          maxRows={3}
        />
      </Dialog>
    );
  }
}
