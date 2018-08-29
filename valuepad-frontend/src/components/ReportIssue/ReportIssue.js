import React, {Component, PropTypes} from 'react';
import {
  ActionButton,
  VpTextField
} from 'components';

import {Dialog} from 'material-ui';

export default class ReportIssue extends Component {
  static propTypes = {
    // Open
    open: PropTypes.bool.isRequired,
    // Close dialog
    closeDialog: PropTypes.func.isRequired,
    // Send issue
    sendIssue: PropTypes.func.isRequired,
    // Form
    value: PropTypes.string.isRequired,
    // Change value
    changeValue: PropTypes.func.isRequired
  };

  render() {
    const {open, closeDialog, sendIssue, value, changeValue} = this.props;
    const actions = [
      <ActionButton
        type="cancel"
        text="Cancel"
        onClick={closeDialog}
      />,
      <ActionButton
        style={{ marginLeft: '10px' }}
        type="submit"
        text="Send Report"
        onClick={sendIssue}
        disabled={!(typeof value === 'string' && value.length)}
      />
    ];
    return (
      <Dialog
        title="Report an Issue"
        actions={actions}
        modal
        open={open}
      >
        <p>Help us help you. Try to explain the precise steps that make the problem occur.</p>

        <VpTextField
          name="description"
          placeholder="Tell us what is happening"
          value={value}
          onChange={changeValue}
          enterFunction={sendIssue}
          fullWidth
          multiLine
          minRows={3}
          maxRows={3}
        />
      </Dialog>
    );
  }
}
