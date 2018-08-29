import React, {Component, PropTypes} from 'react';

/**
 * Main class for all logged-in components
 */
export default class Main extends Component {
  static propTypes = {
    children: PropTypes.object.isRequired,
    // React router location
    location: PropTypes.object.isRequired
  };

  static contextTypes = {
    user: PropTypes.object
  };

  render() {
    return (
      <div>
        {/* Begin page content */}
        {this.context.user &&
          <div>
            {this.props.children}
          </div>
        }
      </div>
    );
  }
}
