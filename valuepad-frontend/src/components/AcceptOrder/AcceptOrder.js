import React, {Component, PropTypes} from 'react';
import {
  Confirm,
  OrderInstructions,
} from 'components';
import Immutable from 'immutable';

export default class AcceptOrder extends Component {

  static propTypes = {
    // Show dialog
    show: PropTypes.bool.isRequired,
    // Hide function
    hide: PropTypes.func.isRequired,
    // Selected order
    selectedRecord: PropTypes.instanceOf(Immutable.Map),
    // Submit function
    submit: PropTypes.func.isRequired,
    // set prop
    setProp: PropTypes.func.isRequired,
    // Set print content
    setPrintContent: PropTypes.func.isRequired
  };

  /**
   * Create accept order body
   * @returns {XML}
   */
  getBody() {
    return (
      <OrderInstructions
        setProp={this.props.setProp}
        selectedRecord={this.props.selectedRecord}
      />
    );
  }

  render() {
    const {show, hide, submit, setPrintContent} = this.props;

    return (
      <Confirm
        show={show}
        hide={hide}
        body={this.getBody.call(this)}
        title="Accept Order"
        enablePrint
        submit={submit}
        setPrintContent={setPrintContent}
      />
    );
  }
}
