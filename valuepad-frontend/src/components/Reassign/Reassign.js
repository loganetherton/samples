import React, {Component, PropTypes} from 'react';

import {
  Confirm,
  BetterTextField,
  ReassignSearchResults
} from 'components';

import Immutable from 'immutable';

const styles = {
  container: {}
};

const rowHeight = 52;

/**
 * Reassign an order
 */
export default class Reassign extends Component {
  static propTypes = {
    // Whether to show the dialog
    show: PropTypes.bool.isRequired,
    // Hide dialog
    hide: PropTypes.func.isRequired,
    // Selected record
    record: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Submit
    submit: PropTypes.func.isRequired,
    // Set property
    setProp: PropTypes.func.isRequired,
    // Reassign props
    reassign: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Search company appraisers
    searchCompanyAppraisers: PropTypes.func.isRequired,
    // Company ID
    companyId: PropTypes.number.isRequired,
    // Order ID
    orderId: PropTypes.number.isRequired,
    // Company appraisers
    searchResults: PropTypes.instanceOf(Immutable.List).isRequired,
    // Manager ID
    managerId: PropTypes.number.isRequired,
    // Set prop company reducer

  };

  constructor() {
    super();
    this.hideModal = ::this.hideModal;
    this.dialogBody = ::this.dialogBody;
    this.changeInput = ::this.changeInput;
    this.selectAppraiser = ::this.selectAppraiser;
  }

  componentDidMount() {
    this.props.setProp(Immutable.List(), 'companyAppraisers');
  }

  /**
   * Inputs
   */
  changeInput(event) {
    const {value, name} = event.target;
    const {setProp, searchCompanyAppraisers, companyId, orderId, reassign} = this.props;
    // Cancel search if it's already in timeout
    if (this.searchTimeout) {
      clearTimeout(this.searchTimeout);
    }
    // Search vals
    const nameSearch = name === 'name' ? value : reassign.get('name');
    const distanceSearch = name !== 'name' ? value : reassign.get('distance');
    // Provide for cancelling on type
    this.searchTimeout = setTimeout(() => {
      searchCompanyAppraisers(companyId, orderId, nameSearch, distanceSearch);
      this.searchTimeout = null;
    }, 250);
    // Hide results
    setProp(value, 'reassign', name);
  }

  /**
   * Create dialog body
   */
  dialogBody() {
    const {reassign, searchResults} = this.props;
    // Set height based on search results
    const searchResultCount = searchResults.count();
    const thisStyle = Object.assign({}, styles, {
      container: {
        height: ((searchResultCount + 2) * rowHeight) + 'px'
      }
    });
    return (
      <div style={thisStyle.container}>
        <div className="row">
          <div className="col-md-6">
            <BetterTextField
              value={reassign.get('name')}
              label="Search appraiser name"
              name="name"
              onChange={this.changeInput}
              error={reassign.get('nameError')}
            />
          </div>
          <div className="col-md-6">
            <BetterTextField
              value={reassign.get('distance')}
              label="Search distance"
              name="distance"
              onChange={this.changeInput}
              error={reassign.get('distanceError')}
            />
          </div>
        </div>
        <div className="row">
          <div className="col-md-12">
            <ReassignSearchResults
              results={searchResults}
              selectFunction={this.selectAppraiser}
            />
          </div>
        </div>
      </div>
    );
  }

  hideModal() {
    this.props.hide();
  }

  /**
   * Handle reassign
   * @param appraiser
   */
  selectAppraiser(appraiser) {
    const {submit, orderId, managerId} = this.props;
    submit(appraiser, orderId, managerId);
  }

  render() {
    const {show} = this.props;
    const errors = Immutable.Map();
    const submitError = Immutable.Map();

    return (
      <Confirm
        body={this.dialogBody(errors, submitError)}
        title="Reassign Order"
        show={show}
        hide={this.hideModal}
        submitHide
        bodyStyle={{height: '750px'}}
        autoDetectWindowHeight
      />
    );
  }
}
