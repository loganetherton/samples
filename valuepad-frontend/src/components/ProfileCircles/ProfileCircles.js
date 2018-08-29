import React, {Component, PropTypes} from 'react';

import {Void, VpAvatar} from 'components';

/**
 * Stepper for profile
 */
export default class ProfileCircles extends Component {
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

    Array(5).fill().forEach((v, i) => {
      const idx = i + 2;
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
        className="signup-avatar">
        {step}
      </VpAvatar>
    );
  }

  changeStep(step) {
    const {changeStep, disableSteps} = this.props;

    changeStep.call(this, step, disableSteps);
  }

  render() {
    // Step that sign up is on
    const {signUpStep: step} = this.props;

    return (
      <div>
        <div className="row">
          <ul className="signup-circles">
            <li className={step === 2 ? 'active' : ''} onClick={this.changeStep2}>
              {this.createNumber(1)} Profile
            </li>
            <li className={step === 3 ? 'active' : ''} onClick={this.changeStep3}>
              {this.createNumber(2)} Company
            </li>
            <li className={step === 4 ? 'active' : ''} onClick={this.changeStep4}>
              {this.createNumber(3)} E&O
            </li>
            <li className={step === 5 ? 'active' : ''} onClick={this.changeStep5}>
              {this.createNumber(4)} Certification
            </li>
            <li className={step === 6 ? 'active' : ''} onClick={this.changeStep6}>
              {this.createNumber(5)} Samples
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
