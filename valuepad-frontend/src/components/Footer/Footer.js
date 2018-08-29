import React, {Component} from 'react';
import {Divider} from 'material-ui';
import {Void} from 'components';
import MuiThemeProvider from 'material-ui/styles/MuiThemeProvider';

export default class Footer extends Component {
  render() {
    // Sticky footer styles
    const footerStyle = {
      position: 'absolute',
      bottom: 0,
      width: '100%',
      /* Set the fixed height of the footer here */
      height: '60px'
    };

    return (
      <MuiThemeProvider>
        <footer style={footerStyle}>
          <Void pixels={10} />
          <Divider />
          <Void pixels={10} />

          <div className="container-fluid">
            <div className="row">
              <div className="col-md-6">Copyright &copy; 2016 ValuePad, Inc. All rights reserved.</div>
              <div className="col-md-6 footer-links hidden-print">
                <ul className="list-inline">
                  <li><a href="https://valuepad.com/terms/">Terms of Service</a></li>
                  <li><a href="https://valuepad.com/privacy/">Privacy Policy</a></li>
                </ul>
              </div>
            </div>
          </div>
        </footer>
      </MuiThemeProvider>
    );
  }
}
