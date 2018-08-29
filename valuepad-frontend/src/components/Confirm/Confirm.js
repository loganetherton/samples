import React, {Component, PropTypes} from 'react';
import {Dialog} from 'material-ui';
import {
  ActionButton
} from 'components';

/**
 * Confirmation modal
 */
export default class Confirm extends Component {
  static propTypes = {
    // Show modal prop
    show: PropTypes.bool.isRequired,
    // Hide modal (required by React Bootstrap)
    hide: PropTypes.func.isRequired,
    // Submit functions
    submit: PropTypes.func,
    // Hide submit button
    submitHide: PropTypes.bool,
    // Submit disabled
    submitDisabled: PropTypes.bool,
    // Button text
    buttonText: PropTypes.object,
    // Modal body, either function or JSX
    body: PropTypes.oneOfType([
      PropTypes.func,
      PropTypes.object
    ]),
    // Modal body as children
    children: PropTypes.object,
    // Modal title (text only)
    title: PropTypes.string.isRequired,
    bodyClassName: PropTypes.string,
    contentStyle: PropTypes.object,
    bodyStyle: PropTypes.object,
    // Shows print button
    enablePrint: PropTypes.bool,
    // Set print content (must be passed if enablePrint is true)
    setPrintContent: PropTypes.func,
    // Defer setting print content
    deferSettingPrintContent: PropTypes.bool,
    // Detect window height
    autoDetectWindowHeight: PropTypes.bool
  };

  state = {
    printContentSet: false
  };

  constructor() {
    super();
    this.print = ::window.print;
  }

  componentDidMount() {
    // There's an issue with the instruction dialog when accepting an order
    // with a condition that causes an error if setPrintContent is called
    // in componentDidMount. So we defer the invocation and instead invoke
    // the function in componentWillReceiveProps. Please remove this hack, once
    // the dialog doesn't generate an error anymore.
    if (!this.props.deferSettingPrintContent) {
      this.setPrintContent(this.props);
    }
  }

  componentWillReceiveProps(nextProps) {
    this.setPrintContent(nextProps);
  }

  setPrintContent(props) {
    const {enablePrint, setPrintContent} = this.props;
    const {body, children} = props;

    if (!this.state.printContentSet && enablePrint) {
      setPrintContent(body || children);
      this.setState({printContentSet: true});
    }
  }

  /**
   * Get button actions
   * @param submit Submit
   * @param hide Hide
   * @param submitDisabled Disable submit
   * @param submitHide Do not display submit button
   * @param buttonText Button text
   */
  getActions(submit, hide, submitDisabled, submitHide, buttonText = {}) {
    const cancelButton = (
      <ActionButton
        type="cancel"
        text={buttonText.cancel || 'Cancel'}
        onClick={hide}
      />
    );
    let submitButton;
    // Only return cancel if hiding submit
    if (submitHide) {
      return [cancelButton];
    }
    submitButton = (
      <ActionButton
        type="submit"
        text={buttonText.submit || 'Submit'}
        onClick={submit}
        style={{ marginLeft: '10px' }}
        disabled={submitDisabled}
      />
    );
    // Both submit and cancel
    return [cancelButton, submitButton];
  }

  render() {
    const {
      show,
      hide,
      submit,
      submitDisabled,
      submitHide,
      buttonText,
      body,
      title,
      children,
      contentStyle = {},
      bodyStyle = {},
      enablePrint = false,
      autoDetectWindowHeight = false
    } = this.props;

    const {bodyClassName = 'confirm-dialog'} = this.props;

    const actions = this.getActions(submit, hide, submitDisabled, submitHide, buttonText);

    if (enablePrint) {
      actions.push(
        <ActionButton
          type="submit"
          text="Print"
          onClick={this.print}
          style={{ marginLeft: '10px' }}
        />
      );
    }

    return (
      <Dialog
        bodyClassName={bodyClassName}
        open={show}
        className="print-preview-dialog"
        onRequestClose={hide}
        actions={actions}
        modal
        title={title}
        autoScrollBodyContent
        contentStyle={contentStyle}
        bodyStyle={Object.assign({fontSize: null, color: null}, bodyStyle)}
        autoDetectWindowHeight={autoDetectWindowHeight}
      >
        {body || children}
      </Dialog>
    );
  }
}
