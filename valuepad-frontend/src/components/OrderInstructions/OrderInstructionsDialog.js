import React, {Component, PropTypes} from 'react';

import {
  Confirm,
  OrderInstructions,
} from 'components';
import Immutable from 'immutable';

const submitPlaceholder = () => ({});

/**
 * Instructions shown before submitting a bid
 */
export default class OrderInstructionsDialog extends Component {
  static propTypes = {
    // Whether to show the dialog
    show: PropTypes.bool.isRequired,
    // Hide dialog
    hide: PropTypes.func.isRequired,
    // Selected record
    selectedRecord: PropTypes.instanceOf(Immutable.Map),
    // Submit
    submit: PropTypes.func,
    // Set property
    setProp: PropTypes.func.isRequired,
    // Hide submit button (for reviewing instructions)
    submitHide: PropTypes.bool,
    // Button text for confirm dialog
    buttonText: PropTypes.object,
    // Set print content
    setPrintContent: PropTypes.func,
    // Defer setting print content
    deferSettingPrintContent: PropTypes.bool
  };

  dialogBody() {
    return (
      <OrderInstructions selectedRecord={this.props.selectedRecord || Immutable.Map()} setProp={this.props.setProp} />
    );
  }

  render() {
    const {
      show,
      hide,
      submit = submitPlaceholder,
      submitHide = false,
      buttonText = {},
      setPrintContent,
      deferSettingPrintContent = false
    } = this.props;

    return (
      <Confirm
        body={this.dialogBody.call(this)}
        title="Requirements"
        show={show}
        hide={hide}
        submit={submit}
        submitHide={submitHide}
        buttonText={buttonText}
        enablePrint
        deferSettingPrintContent={deferSettingPrintContent}
        setPrintContent={setPrintContent}
      />
    );
  }
}
