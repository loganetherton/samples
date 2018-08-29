import React, {Component, PropTypes} from 'react';
import Immutable from 'immutable';

import {VpTextField} from 'components';

const styles = {
  results: {border: '1px solid #ddd', maxHeight: '500px', overflowY: 'auto', cursor: 'pointer'},
  result: {padding: '5px'},
  even: {
    backgroundColor: '#e1e8fa'
  },
  component: {padding: '8px'}
};

/**
 * Allow the customer to switch which appraiser is selected
 */
export default class SwitchAppraiser extends Component {
  static propTypes = {
    // Search input value
    searchValue: PropTypes.string.isRequired,
    // Set prop
    setProp: PropTypes.func.isRequired,
    // Search appraisers
    searchAppraisers: PropTypes.func.isRequired,
    // Appraiser search results
    results: PropTypes.instanceOf(Immutable.List).isRequired,
    // Select an appraiser from list
    selectAppraiser: PropTypes.func.isRequired,
    // Close popover
    closePopover: PropTypes.func.isRequired,
    // Customer ID
    customerId: PropTypes.number.isRequired,
    // Set prop for messages and notifications
    notificationsSetProp: PropTypes.func.isRequired,
    messagesSetProp: PropTypes.func.isRequired
  };

  constructor() {
    super();
    this.state = {
      showResults: false
    };
    this.searchTimeout = null;
  }

  componentDidMount() {
    const {results, searchValue} = this.props;
    if (results.count() && searchValue) {
      this.setState({
        showResults: true
      });
    }
  }

  /**
   * Change appraiser search input
   */
  changeAppraiserSearch(event) {
    const {setProp, searchAppraisers, customerId} = this.props;
    let newVal = event.target.value;
    const {showResults} = this.state;
    if (newVal) {
      newVal = newVal.trim();
    }
    setProp(newVal, 'searchAppraiserVal');
    let showResultsAfter;
    // Perform the search
    if (newVal) {
      showResultsAfter = true;
      // Cancel search if it's already in timeout
      if (this.searchTimeout) {
        clearTimeout(this.searchTimeout);
      }
      // Provide for cancelling on type
      this.searchTimeout = setTimeout(() => {
        searchAppraisers(customerId, newVal);
        this.searchTimeout = null;
      }, 250);
      // Hide results
    } else {
      showResultsAfter = false;
    }
    if (showResultsAfter !== showResults) {
      this.setState({
        showResults: showResultsAfter
      });
    }
  }

  /**
   * Select an appraiser from the list
   */
  selectAppraiser(appraiserId) {
    const {selectAppraiser, closePopover, messagesSetProp, notificationsSetProp} = this.props;
    selectAppraiser(appraiserId);
    closePopover();
    // Reset messages and notifications
    messagesSetProp(Immutable.Map(), 'messagesByDate');
    messagesSetProp(Immutable.List(), 'messages');
    messagesSetProp(Immutable.Map(), 'totals');
    notificationsSetProp(Immutable.List(), 'notifications');
    notificationsSetProp(0, 'counter');
  }

  render() {
    const {
      searchValue,
      results
    } = this.props;
    const {showResults} = this.state;
    return (
      <div style={styles.component}>
        <VpTextField
          value={searchValue}
          label="Search Appraisers"
          name="searchAppraiserVal"
          onChange={::this.changeAppraiserSearch}
        />
        {showResults &&
          <div style={styles.results}>
            {results.map((appraiser, index) => {
              return (
                <div style={index % 2 ? styles.even : {}} key={index}>
                  <div style={styles.result} onClick={this.selectAppraiser.bind(this, appraiser.get('id'))}
                       className="appraiser-search-row">
                    {appraiser.get('firstName').toUpperCase()} {appraiser.get('lastName').toUpperCase()}
                    </div>
                </div>
              );
            })}
          </div>
        }
      </div>
    );
  }
}
