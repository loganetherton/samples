import React, {Component} from 'react';

import {Coverage} from 'containers';

/**
 * Instantiate coverage during sign up
 */
export default class SignUpCoverage extends Component {
  render() {
    return (
      <div>
        <h3 className="no-top-spacing signup-heading text-center">Licenses</h3>
        <Coverage />
      </div>
    );
  }
}
