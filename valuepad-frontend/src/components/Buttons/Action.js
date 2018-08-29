import React, {Component, PropTypes} from 'react';

const buttonTypes = {
  'submit': { defaultText: 'Submit', classes: 'bttn-submit' },
  'cancel': { defaultText: 'Cancel', classes: 'bttn-cancel' },
  'reset': { defaultText: 'Reset', classes: 'bttn-reset'},
};

export default class ActionButton extends Component {
  static propTypes = {
    // whether this is disabled or not
    disabled: PropTypes.bool,
    // icon to use
    icon: PropTypes.string,
    // onclick event
    onClick: PropTypes.func.isRequired,
    // object
    style: PropTypes.object,
    // Text for the button
    text: PropTypes.string,
    // type of button
    type: PropTypes.string.isRequired,
    // additional classes to apply
    additionalClasses: PropTypes.string,
  };

  onClick() {
    if (!this.props.disabled) {
      this.props.onClick.call(this);
    }
  }

  render() {
    const type = buttonTypes[this.props.type];

    const {
      additionalClasses,
      disabled,
      icon,
      style = {},
      text = type.defaultText
    } = this.props;

    let classes = `bttn ${type.classes}`;

    // if disabled add the extra class
    if (disabled) {
      classes += ' bttn-disabled';
    }

    // add any additional classes
    if (additionalClasses) {
      classes += ' ' + additionalClasses;
    }

    return (
      <button className={classes} style={style} onClick={::this.onClick}>
        {icon && <i className="material-icons">{icon}</i>}
        {text}
      </button>
    );
  }
}
