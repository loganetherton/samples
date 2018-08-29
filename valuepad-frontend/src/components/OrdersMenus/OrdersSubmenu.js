import React, {Component, PropTypes} from 'react';
import Immutable from 'immutable';

import {
  ORDERS_NEW_URL,
  ORDERS_ACCEPTED_URL,
  ORDERS_INSPECTION_SCHEDULED_URL,
  ORDERS_INSPECTION_COMPLETE_URL,
  ORDERS_ON_HOLD_URL,
  ORDERS_DUE_URL,
  ORDERS_LATE_URL,
  ORDERS_IN_REVIEW_URL,
  ORDERS_COMPLETED_URL,
  ORDERS_REVISION_URL,
  ORDERS_OPEN_URL
} from 'redux/modules/urls';

// Tabs
const tabs = [
  {
    label: 'New',
    status: 'new',
    url: ORDERS_NEW_URL
  },
  {
    label: 'Accepted',
    status: 'accepted',
    url: ORDERS_ACCEPTED_URL
  },
  {
    label: 'Scheduled',
    status: 'scheduled',
    url: ORDERS_INSPECTION_SCHEDULED_URL
  },
  {
    label: 'Inspected',
    status: 'inspected',
    url: ORDERS_INSPECTION_COMPLETE_URL
  },
  {
    label: 'In Review',
    status: 'ready-for-review',
    statusField: 'readyForReview',
    url: ORDERS_IN_REVIEW_URL,
  },
  {
    label: 'Revision',
    status: 'revision',
    url: ORDERS_REVISION_URL
  },
  {
    label: 'Due',
    status: 'due',
    url: ORDERS_DUE_URL
  },
  {
    label: 'Late',
    status: 'late',
    url: ORDERS_LATE_URL
  },
  {
    label: 'Completed',
    status: 'completed',
    url: ORDERS_COMPLETED_URL
  },
  {
    label: 'On Hold',
    status: 'on-hold',
    statusField: 'onHold',
    url: ORDERS_ON_HOLD_URL
  },
  {
    label: 'All Open',
    status: 'open',
    url: ORDERS_OPEN_URL
  }
];

/**
 * React submenu
 */
export default class OrdersSubmenu extends Component {
  static propTypes = {
    // Selected menu
    selectedMenu: PropTypes.string.isRequired,
    // Change order type
    changeType: PropTypes.func.isRequired,
    // statuses
    statuses: PropTypes.instanceOf(Immutable.Map),
    // clears orders
    clearOrders: PropTypes.func.isRequired,
  };

  renderTabs() {
    const {selectedMenu, changeType, statuses} = this.props;

    return tabs.map((tab, index) => {
      let tabClass = 'btn-tab';

      if (tab.status === selectedMenu) {
        tabClass += ' btn-tab-active';
      }

      // the previous tab
      if (index < tabs.length && tabs[index + 1] !== undefined && tabs[index + 1].status === selectedMenu) {
        tabClass += ' btn-tab-before-active';
      }

      return (
        <div key={tab.status} className="btn-tab-col col-md-1 col-sm-2 col-xs-4" role="button">
          <div className={tabClass} onClick={changeType.bind(this, tab.url, tab.status)}>
            <div>
              <div style={styles.tabCounter}>{statuses.get(tab.statusField ? tab.statusField : tab.status)}</div>
              <div><small style={{ textTransform: 'uppercase' }}>{tab.label}</small></div>
            </div>
          </div>
        </div>
      );
    });
  }

  render() {
    return (
      <div className="container-fluid">
        <div className="row btn-tab-row">
          { this.renderTabs() }
        </div>
      </div>
    );
  }
}

const styles = {
  tabCounter: {
    fontWeight: 'bold',
    fontSize: '20px'
  }
};
