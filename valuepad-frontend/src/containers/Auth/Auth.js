import React, {PropTypes, Component} from 'react';

export default class Auth extends Component {
  static propTypes = {
    children: PropTypes.object.isRequired
  };

  render() {
    return (
      <div>
        Auth
        {this.props.children}
      </div>
    );
  }
}
