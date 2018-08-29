import React, {Component, PropTypes} from 'react';

import {Void, VpAvatar} from 'components';
import classNames from 'classnames';

/**
 * Stepper for appraiser sign up process
 */
export default class SignUpCircles extends Component {
  static propTypes = {
    // Step that sign up is on
    signUpStep: PropTypes.number.isRequired,
    // Steps which are enabled
    enabledStep: PropTypes.number.isRequired,
    // Change step using the circles
    changeStep: PropTypes.func.isRequired,
    // Disable steps if any validation errors exist
    disableSteps: PropTypes.bool.isRequired
  };

  constructor(props) {
    super(props);

    Array(10).fill().forEach((v, i) => {
      const idx = i + 1;
      this['changeStep' + idx] = this.changeStep.bind(this, idx);
    });
  }

  /**
   * Create number of step
   * @param step
   */
  createNumber(step) {
    return (
      <VpAvatar
        size={20}
        color="white"
        className={classNames({'signup-avatar': true, 'disabled': this.props.enabledStep < step})}>
        {step}
      </VpAvatar>
    );
  }

  /**
   * Change between steps of the sign up process
   */
  changeStep(step) {
    const {enabledStep: enabled, changeStep, disableSteps} = this.props;

    if (enabled >= step) {
      changeStep(step, disableSteps);
    }
  }

  render() {
    // Step that sign up is on
    const {signUpStep: step, enabledStep: enabled} = this.props;

    return (
      <div>
        <div className="row">
          <ul className="signup-circles">
            <li className={step === 1 ? 'active' : (enabled >= 1 ? '' : 'disabled')} onClick={this.changeStep1}>
              {this.createNumber(1)} ASC
            </li>
            <li className={step === 2 ? 'active' : (enabled >= 2 ? '' : 'disabled')} onClick={this.changeStep2}>
              {this.createNumber(2)} Profile
            </li>
            <li className={step === 3 ? 'active' : (enabled >= 3 ? '' : 'disabled')} onClick={this.changeStep3}>
              {this.createNumber(3)} Company
            </li>
            <li className={step === 4 ? 'active' : (enabled >= 4 ? '' : 'disabled')} onClick={this.changeStep4}>
              {this.createNumber(4)} E&O
            </li>
            <li className={step === 5 ? 'active' : (enabled >= 5 ? '' : 'disabled')} onClick={this.changeStep5}>
              {this.createNumber(5)} Certification
            </li>
            <li className={step === 6 ? 'active' : (enabled >= 6 ? '' : 'disabled')} onClick={this.changeStep6}>
              {this.createNumber(6)} Samples
            </li>
            <li className={step === 7 ? 'active' : (enabled >= 7 ? '' : 'disabled')} onClick={this.changeStep7}>
              {this.createNumber(7)} Terms
            </li>
            <li className={step === 8 ? 'active' : (enabled >= 8 ? '' : 'disabled')} onClick={this.changeStep8}>
              {this.createNumber(8)} Licenses
            </li>
            <li className={step === 9 ? 'active' : (enabled >= 9 ? '' : 'disabled')} onClick={this.changeStep9}>
              {this.createNumber(9)} Fees
            </li>
            <li className={step === 10 ? 'active' : (enabled >= 10 ? '' : 'disabled')} onClick={this.changeStep10}>
              {this.createNumber(10)} ACH
            </li>
          </ul>
        </div>
        {/*Push down extra small*/}
        <div className="row visible-xs">
          <Void pixels="15"/>
        </div>
      </div>
    );
  }
}
