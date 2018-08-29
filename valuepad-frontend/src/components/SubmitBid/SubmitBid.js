import React, {Component, PropTypes} from 'react';

import {
  Confirm,
  VpDateRange,
  VpTextField,
  VpPlainDropdown
} from 'components';

import {initialState} from 'redux/modules/orders';

import moment from 'moment';
import Immutable from 'immutable';

/**
 * Submit a bid on an order
 */
export default class SubmitBid extends Component {
  static propTypes = {
    // Whether to show the dialog
    show: PropTypes.bool.isRequired,
    // Hide dialog
    hide: PropTypes.func.isRequired,
    // Submit bid property
    submitBid: PropTypes.instanceOf(Immutable.Map),
    // Submit
    submit: PropTypes.func.isRequired,
    // Set property
    setProp: PropTypes.func.isRequired,
    // Orders
    orders: PropTypes.instanceOf(Immutable.Map),
    // RFP
    rfpBid: PropTypes.bool,
    // Search company appraisers
    searchCompanyAppraisers: PropTypes.func,
    // Selected record
    selectedRecord: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Appraiser results
    companyAppraisers: PropTypes.instanceOf(Immutable.List)
  };

  constructor(props) {
    super(props);

    this.hideModal = ::this.hideModal;
    this.changeInput = ::this.changeInput;
    this.changeDate = ::this.changeDate;
    this.dialogBody = ::this.dialogBody;
    this.changeSelectAppraiser = ::this.changeSelectAppraiser;

    this.state = {
      appraiser1: null,
      appraiser2: null
    };
  }

  componentDidMount() {
    const {rfpBid, searchCompanyAppraisers, selectedRecord} = this.props;
    if (rfpBid) {
      searchCompanyAppraisers(selectedRecord.getIn(['company', 'id']), selectedRecord.get('id'), '');
    }
  }

  /**
   * Change an input
   */
  changeInput(event) {
    const {name, value} = event.target;
    if (name === 'amount') {
      this.props.setProp(value, 'submitBid', name, 'validate');
    } else {
      this.props.setProp(value, 'submitBid', name);
    }
  }

  /**
   * Change estimated completion date
   * @param date Incoming date string
   */
  changeDate(date) {
    this.props.setProp(moment(date).format('M/D/YYYY 23:59:59'), 'submitBid', 'estimatedCompletionDate');
  }

  /**
   * Change selected appraiser for RFP
   * @param whichAppraiser Appraiser 1 or 2
   * @param event Synthetic event
   */
  changeSelectAppraiser(whichAppraiser, event) {
    const {value} = event.target;
    this.setState({
      ['appraiser' + whichAppraiser]: parseInt(value, 10)
    });
  }

  /**
   * Create dialog body
   */
  dialogBody(errors, submitError) {
    const {submitBid, rfpBid, companyAppraisers} = this.props;
    const date = submitBid.get('estimatedCompletionDate') ? moment(submitBid.get('estimatedCompletionDate'), 'MM/DD/YYYY hh:mm:ss') : null;

    return (
      <div>
        <div className="row">
          <div className="col-md-6">
            <VpTextField
              name="amount"
              value={submitBid.get('amount')}
              label="Amount"
              fullWidth
              onChange={this.changeInput}
              error={errors && errors.get('amount') ? errors.get('amount')[0] : ''}
            />
          </div>
          <div className="col-md-6">
            <VpDateRange
              hintText="Estimated Completion Date"
              minDate={moment()}
              date={date}
              changeHandler={this.changeDate}
              label="Estimated Completion Date"
              error={errors && errors.get('estimatedCompletionDate') ? errors.get('estimatedCompletionDate')[0] : ''}
            />
          </div>
        </div>
        <div className="row">
          <div className="col-md-12">
            <VpTextField
              name="comments"
              value={submitBid.get('comments')}
              label="Comments"
              multiLine
              fullWidth
              onChange={this.changeInput}
              error={submitError}
            />
          </div>
        </div>
        {rfpBid &&
          <div className="row">
            <div className="col-md-6">
              <VpPlainDropdown
                options={companyAppraisers}
                value={this.state.appraiser1}
                onChange={this.changeSelectAppraiser.bind(this, 1)}
                label="Appraiser 1"
                valueProp="id"
                nameProp="displayName"
                required
              />
            </div>
            <div className="col-md-6">
              <VpPlainDropdown
                options={companyAppraisers}
                value={this.state.appraiser2}
                onChange={this.changeSelectAppraiser.bind(this, 2)}
                label="Appraiser 2"
                valueProp="id"
                nameProp="displayName"
              />
            </div>
          </div>
        }
      </div>
    );
  }

  /**
   * Check if submit button is disabled
   */
  disabledSubmit(errors) {
    const {submitBid} = this.props;
    // If errors, or any value not completed, disable
    return (errors && errors.getIn(['amount']) && errors.getIn(['amount'])[0]) || !submitBid.get('amount') ||
           !submitBid.get('estimatedCompletionDate') || !submitBid.get('comments');
  }

  /**
   * Close modal
   */
  hideModal() {
    this.props.setProp(initialState.get('submitBid'), 'submitBid');
    this.props.hide();
  }

  render() {
    const {show, orders, rfpBid} = this.props;
    let submit = this.props.submit;
    const errors = orders.getIn(['errors', 'submitBid']);
    const submitError = orders.getIn(['submitBid', 'error']);
    let disabledSubmit = this.disabledSubmit(errors, submit);

    // Appraisers in RFP bid
    if (rfpBid) {
      const {appraiser1, appraiser2} = this.state;
      if (!appraiser1) {
        disabledSubmit = true;
      }
      const appraisers = [appraiser1];
      if (appraiser2) {
        appraisers.push(appraiser2);
      }
      submit = submit.bind(this, appraisers);
    }

    return (
      <Confirm
        body={this.dialogBody(errors, submitError)}
        title="Submit Bid"
        show={show}
        hide={this.hideModal}
        submit={submit}
        submitDisabled={!!disabledSubmit}
      />
    );
  }
}
