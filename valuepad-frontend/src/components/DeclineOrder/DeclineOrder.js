import React, {Component, PropTypes} from 'react';

import {
  Confirm,
  VpPlainDropdown,
  VpTextField,
} from 'components';

import Immutable from 'immutable';

const conditions = Immutable.fromJS([
  {value: 'too-busy', name: 'Too busy'},
  {value: 'out-of-coverage-area', name: 'Out of coverage area'},
  {value: 'other', name: 'Other'}
]);

/**
 * Decline an order
 */
export default class DeclineOrder extends Component {
  static propTypes = {
    // Whether to show the dialog
    show: PropTypes.bool.isRequired,
    // Set property
    setProp: PropTypes.func.isRequired,
    // Hide dialog
    hide: PropTypes.func.isRequired,
    // Decline order property
    decline: PropTypes.instanceOf(Immutable.Map),
    // Submit
    submit: PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);

    this.hideModal = ::this.hideModal;
    this.changeInput = ::this.changeInput;
    this.changeReason = ::this.changeReason;
    this.dialogBody = ::this.dialogBody;
  }

  /**
   * Default accept with conditions reason to fee increase
   */
  componentDidMount() {
    this.props.setProp('too-busy', 'decline', 'reason');
  }

  /**
   * Change the condition reason
   * @param event Synthetic event
   */
  changeReason(event) {
    this.props.setProp(event.target.value, 'decline', 'reason');
  }

  /**
   * Change an input
   */
  changeInput(event) {
    const {name, value} = event.target;
    this.props.setProp(value, 'decline', name);
  }

  /**
   * Create dialog body
   * @returns {XML}
   */
  dialogBody() {
    const {decline} = this.props;
    const reason = decline.get('reason');
    return (
      <div>
        <VpPlainDropdown
          options={conditions}
          value={reason}
          onChange={this.changeReason}
          label="Reason"
        />
        {this.explanationInput(decline)}
      </div>
    );
  }

  /**
   * Provide explanation
   * @param decline Decline record
   * @returns {XML}
   */
  explanationInput(decline) {
    return (
      <VpTextField
        name="message"
        value={decline.get('message')}
        label="Provide explanation"
        fullWidth
        multiLine
        onChange={this.changeInput}
      />
    );
  }

  hideModal() {
    this.props.setProp(Immutable.Map(), 'decline');
    this.props.hide();
  }

  render() {
    const {show, submit} = this.props;

    return (
      <Confirm
        body={this.dialogBody()}
        title="Decline"
        show={show}
        hide={this.hideModal}
        submit={submit}
      />
    );
  }
}
