/*eslint-disable */

import React, {PropTypes} from 'react';
import Immutable from 'immutable';
import {Popover} from 'material-ui';

import {VpTextField, ActionButton} from 'components';

const styles = {
  results: {border: '1px solid #ddd', maxHeight: '500px', overflowY: 'auto', cursor: 'pointer'},
  result: {padding: '5px'},
  even: {
    backgroundColor: '#e1e8fa'
  },
  component: {padding: '8px'},
  container: { display: 'flex', width: '100%', justifyContent: 'space-between'},
  containerSelected: { display: 'flex', width: '100%', justifyContent: 'space-between', borderBottom: '#dfdfdf solid 3px'}
};

/**
 * Allows managers to switch appraisers for profile view
 */
export default class ManagerSwitchAppraiser extends React.Component {
  static propTypes = {
    // Select an appraiser from list
    selectAppraiser: PropTypes.func.isRequired,
    // Company reducer
    company: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Current profile step
    step: PropTypes.number
  };

  constructor() {
    super();
    this.state = {
      filteredResults: Immutable.List(),
      searchVal: '',
      switchAppraiserOpen: false,
      switchAppraiserNode: null
    };
    this.searchTimeout = null;
    this.changeAppraiserSearch = ::this.changeAppraiserSearch;
    this.toggleSwitchAppraiser = ::this.toggleSwitchAppraiser;
  }

  /**
   * Change appraiser search input
   */
  changeAppraiserSearch(event) {
    const {company} = this.props;
    const results = company.get('staff');
    const {value} = event.target;
    const query = new RegExp(value, 'i');
    // Filter based on query
    const filteredResults = results.filter(result => {
      return result.getIn(['user', 'type']) === 'appraiser' &&
             (query.test(result.getIn(['user', 'displayName'])) ||
              query.test(result.getIn(['user', 'email'])));
    }).map(appraiser => appraiser.get('user'));
    this.setState({
      filteredResults,
      searchVal: value
    });
  }

  /**
   * Select an appraiser from the list
   * @param appraiser Selected appraiser
   */
  selectAppraiser(appraiser) {
    const {selectAppraiser} = this.props;
    selectAppraiser(appraiser);
    this.toggleSwitchAppraiser();
  }

  /**
   * Toggle switch appraiser dropdown
   * @param event
   */
  toggleSwitchAppraiser(event) {
    const newState = {
      switchAppraiserOpen: !this.state.switchAppraiserOpen
    };
    if (event && event.currentTarget) {
      newState.switchAppraiserNode = event.currentTarget;
    }
    this.setState(newState);
  }

  /**
   * Heading text
   * @param step Current profile step
   * @param displayName Appraiser display name
   * @return {string}
   */
  getHeadingText(step, displayName) {
    let headingText = '';
    switch (step) {
      case 2:
        headingText = `Viewing ${displayName}'s Profile`;
        break;
      case 3:
        headingText = `Viewing ${displayName}'s Company Information`;
        break;
      case 4:
        headingText = `Viewing ${displayName}'s E&O Information`;
        break;
      case 5:
        headingText = `Viewing ${displayName}'s Certifications & Qualifications`;
        break;
      case 6:
        headingText = `Viewing ${displayName}'s Sample Reports`;
        break;
    }
    return headingText;
  }

  render() {
    const {filteredResults, searchVal, switchAppraiserOpen, switchAppraiserNode} = this.state;
    const {company, step} = this.props;
    const selectedAppraiser = company.get('profileSelectedAppraiser');
    const isSelected = !!selectedAppraiser.get('id');
    const headingText = this.getHeadingText(step, selectedAppraiser.get('displayName'));

    return (
      <div className="row">
        <div className="col-md-12" style={isSelected ? styles.containerSelected : styles.container}>
          <div onClick={this.toggleSwitchAppraiser}>
            <ActionButton
              type="reset"
              text="Select Appraiser"
              onClick={() => {}}
            />
          </div>
          {!!selectedAppraiser.get('id') && <h3>{headingText}</h3>}
          <div>&nbsp;</div>
        </div>
        <Popover
          open={switchAppraiserOpen}
          anchorEl={switchAppraiserNode}
          anchorOrigin={{
            horizontal: 'left',
            vertical: 'bottom'
          }}
          targetOrigin={{
            horizontal: 'left',
            vertical: 'top'
          }}
          onRequestClose={this.toggleSwitchAppraiser}
          autoCloseWhenOffScreen={false}
          canAutoPosition={false}
          style={{
            width: 250
          }}
        >
          <div style={styles.component}>
            <VpTextField
              value={searchVal}
              label="Search Appraisers"
              name="searchAppraiserVal"
              onChange={::this.changeAppraiserSearch}
              autoFocus
            />
            {!!filteredResults.count() &&
             <div style={styles.results}>
               {filteredResults.map((appraiser, index) => {
                 return (
                   <div style={index % 2 ? styles.even : {}} key={index}>
                     <div style={styles.result} onClick={this.selectAppraiser.bind(this, appraiser)}
                          className="appraiser-search-row">
                       {appraiser.get('displayName')}
                     </div>
                   </div>
                 );
               })}
             </div>
            }
          </div>
        </Popover>
      </div>
    );
  }
}
