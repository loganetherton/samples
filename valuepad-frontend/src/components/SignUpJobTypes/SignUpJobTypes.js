import React, {Component} from 'react';

import {JobTypes} from 'containers';

/**
 * Instantiate job types during sign up
 */
export default class SignUpJobTypes extends Component {
  render() {
    return (
      <div>
        <h3 className="no-top-spacing signup-heading text-center">Fees</h3>
        <JobTypes
          signUp
        />
      </div>
    );
  }
}
