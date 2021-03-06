import React, {Component, PropTypes} from 'react';

export const STATE = {
  LOADING: 'loading',
  DISABLED: 'disabled',
  SUCCESS: 'success',
  ERROR: 'error',
  NOTHING: ''
};

// Button padding
const padding = 30;

export default class ProgressButton extends Component {
  static propTypes = {
    classNamespace: PropTypes.string,
    durationError: PropTypes.number,
    durationSuccess: PropTypes.number,
    form: PropTypes.string,
    onClick: PropTypes.func,
    onError: PropTypes.func,
    onSuccess: PropTypes.func,
    state: PropTypes.oneOf(Object.keys(STATE).map(k => STATE[k])),
    type: PropTypes.string,
    shouldAllowClickOnLoading: PropTypes.bool,
    children: PropTypes.object,
    className: PropTypes.string,
    applyDefaultStyle: PropTypes.bool
  };

  static defaultProps = {
    classNamespace: 'pb-',
    durationError: 1200,
    durationSuccess: 500,
    shouldAllowClickOnLoading: false
  };

  constructor(props) {
    super(props);
    this.handleClick = ::this.handleClick;
    this.loading = ::this.loading;
    this.notLoading = ::this.notLoading;
    this.enable = ::this.enable;
    this.disable = ::this.disable;
    this.success = ::this.success;
    this.error = ::this.error;
    this.setButtonWidth = ::this.setButtonWidth;
    this.state = {
      currentState: this.props.state || STATE.NOTHING,
      buttonWidth: 0,
      initialWidth: '100%'
    };
  }

  componentWillReceiveProps(nextProps) {
    if (nextProps.state === this.props.state) {
      return;
    }
    switch (nextProps.state) {
      case STATE.SUCCESS:
        this.success();
        return;
      case STATE.ERROR:
        this.error();
        return;
      case STATE.LOADING:
        this.loading();
        return;
      case STATE.DISABLED:
        this.disable();
        return;
      case STATE.NOTHING:
        this.notLoading();
        return;
      default:
        return;
    }
  }

  componentWillUnmount() {
    clearTimeout(this._timeout);
  }

  handleClick(e) {
    if ((this.props.shouldAllowClickOnLoading || this.state.currentState !== 'loading') &&
        this.state.currentState !== 'disabled') {
      const ret = this.props.onClick(e);
      this.loading(ret);
    } else {
      e.preventDefault();
    }
  }

  loading(promise) {
    this.setState({currentState: 'loading'});
    if (promise && promise.then && promise.catch) {
      promise
      .then(() => {
        this.success();
      })
      .catch(() => {
        this.error();
      });
    }
  }

  notLoading() {
    this.setState({currentState: STATE.NOTHING});
  }

  enable() {
    this.setState({currentState: STATE.NOTHING});
  }

  disable() {
    this.setState({currentState: STATE.DISABLED});
  }

  success(callback, dontRemove) {
    this.setState({currentState: STATE.SUCCESS});
    this._timeout = setTimeout(() => {
      if (!dontRemove) {
        this.setState({currentState: STATE.NOTHING});
      }
      callback = callback || this.props.onSuccess;
      if (typeof callback === 'function') {
        callback();
      }
    }, this.props.durationSuccess);
  }

  error(callback) {
    this.setState({currentState: STATE.ERROR});
    this._timeout = setTimeout(() => {
      this.setState({currentState: STATE.NOTHING});
      callback = callback || this.props.onError;
      if (typeof callback === 'function') {
        callback();
      }
    }, this.props.durationError);
  }

  /**
   * Set button width
   * @param width
   */
  setButtonWidth(width) {
    const currentWidth = this.state.buttonWidth;
    const newWidth = width + (padding * 2);
    if (currentWidth !== newWidth) {
      this.setState({
        buttonWidth: newWidth
      });
    }
  }

  render() {
    const {
      className, classNamespace, children, type, form,
      durationError, // eslint-disable-line no-unused-vars
      durationSuccess, // eslint-disable-line no-unused-vars
      onClick, // eslint-disable-line no-unused-vars
      onError, // eslint-disable-line no-unused-vars
      onSuccess, // eslint-disable-line no-unused-vars
      state, // eslint-disable-line no-unused-vars
      shouldAllowClickOnLoading, // eslint-disable-line no-unused-vars,
      applyDefaultStyle = false,
      ...containerProps
    } = this.props;

    const {buttonWidth, initialWidth, currentState} = this.state;

    containerProps.className = classNamespace + 'container ' + currentState + ' ' + className;
    containerProps.onClick = this.handleClick;
    return (
      <div {...containerProps} style={{width: initialWidth}}>
        <button type={type} form={form} className={classNamespace + 'button ' + (applyDefaultStyle ? 'apply-default' : '')} style={{width: buttonWidth + 'px'}}>
          <span ref={span => {
            if (span) {
              this.setButtonWidth(span.offsetWidth);
              if (initialWidth === '100%') {
                this.setState({initialWidth: (span.offsetWidth + padding * 2) + 'px'});
              }
            }
          }}>{children}</span>
          <svg className={classNamespace + 'progress-circle'} viewBox="0 0 41 41">
            <path d="M38,20.5 C38,30.1685093 30.1685093,38 20.5,38"/>
          </svg>
          <svg className={classNamespace + 'checkmark'} viewBox="0 0 70 70">
            <path d="m31.5,46.5l15.3,-23.2"/>
            <path d="m31.5,46.5l-8.5,-7.1"/>
          </svg>
          <svg className={classNamespace + 'cross'} viewBox="0 0 70 70">
            <path d="m35,35l-9.3,-9.3"/>
            <path d="m35,35l9.3,9.3"/>
            <path d="m35,35l-9.3,9.3"/>
            <path d="m35,35l9.3,-9.3"/>
          </svg>
        </button>
      </div>
    );
  }
}
