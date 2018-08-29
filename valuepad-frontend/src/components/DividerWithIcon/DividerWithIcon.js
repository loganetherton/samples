import React, {Component, PropTypes} from 'react';

export default class DividerWithIcon extends Component {
  static propTypes = {
    // Label
    label: PropTypes.string.isRequired,
    // Icon
    icon: PropTypes.string,
    // Include row
    row: PropTypes.bool,
    // Button
    button: PropTypes.object
  };

  render() {
    const {label, icon, button} = this.props;
    return (
      <div className="row-content text-center">
        <div className="row">
          <div className="col-md-2">
            {button}
          </div>
          <div className="col-md-8">
            <h4 className="list-group-item-heading">
              {icon &&
               <i style={{ position: 'relative', top: '4px' }} className="material-icons">{icon}</i>
              }
              <span>{label}</span>
            </h4>
          </div>
        </div>
      </div>
    );
  }
}
