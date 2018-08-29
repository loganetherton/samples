/**
 * The authentication screens navbar.
 */

import React, {Component} from 'react';

export default class AuthNav extends Component {
  render() {
    const logo = require('./logo.png');

    return (
      <div className="text-center">
        <nav className="navbar auth-navbar" role="navigation">
          <ul className="nav">
            <li><img className="center-block" src={logo}/></li>
          </ul>
        </nav>
      </div>
    );
  }
}
