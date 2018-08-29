import React, {Component, PropTypes} from 'react';
import {FlatButton} from 'material-ui';
import classNames from 'classnames';

// Sub menu items
const subMenu = ['order-details'];
/**
 * Orders details pane submenu
 */
export default class OrdersDetailsPaneSubmenu extends Component {
  static propTypes = {
    selectedTab: PropTypes.string.isRequired
  };

  /**
   * Get menu styles
   * @param selectedTab
   * @returns {{}}
   */
  createMenuStyles(selectedTab) {
    const activeStyle = {
      fontWeight: 'bold'
    };
    const menuClasses = {};
    subMenu.forEach((menuItem) => {
      menuClasses[menuItem] = {};
      if (menuItem === selectedTab) {
        Object.assign(menuClasses[menuItem], activeStyle);
      }
    });
    return menuClasses;
  }

  render() {
    const {selectedTab} = this.props;
    const menuClasses = this.createMenuStyles(selectedTab);
    return (
      <ul className={classNames('nav', 'navbar-nav')}>
        <li>
          <FlatButton
            linkButton
            label="Order Details"
            labelPosition="after"
            style={menuClasses['order-details']}
          />
        </li>
      </ul>
    );
  }
}
