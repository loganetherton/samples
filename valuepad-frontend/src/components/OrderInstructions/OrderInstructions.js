import React, {Component, PropTypes} from 'react';
import Immutable from 'immutable';

/**
 * Instructions shown before submitting a bid
 */
export default class OrderInstructions extends Component {
  static propTypes = {
    // Selected record
    selectedRecord: PropTypes.instanceOf(Immutable.Map),
    // Set property
    setProp: PropTypes.func.isRequired
  };

  /**
   * Change an input
   */
  changeInput(event) {
    const {name, value} = event.target;
    this.props.setProp(value, 'instructions', name);
  }

  /**
   * Show tech fee
   * @param selectedRecord Selected order
   * @returns {XML}
   */
  techFee(selectedRecord) {
    if (selectedRecord.get('techFee')) {
      return (
        <div>
          <h5>Technology Fee</h5>

          <p>There is a third party technology fee of ${selectedRecord.get('techFee').toFixed(2)} associated with this order. You will be required to pay this fee via credit card when you upload the order.</p>
        </div>
      );
    }
  }

  /**
   * Instruction documents
   * @param selectedRecord Selected order
   */
  instructionDocuments(selectedRecord) {
    const documents = selectedRecord.get('instructionDocuments');
    if (documents && documents.count()) {
      return (
        <div>
          <h5>Instruction {documents.count() === 1 ? 'Document' : 'Documents'}</h5>
          <ul>
            {
              documents.map((document, index) =>
                <li key={index}>
                  <a href={document.get('url')} target="_blank">{document.get('name')}</a>
                </li>
              )
            }
          </ul>
        </div>
      );
    }
  }

  /**
   * Show instructions
   * @param selectedRecord Selected order
   * @returns {XML}
   */
  instructions(selectedRecord) {
    return (
      <div>
        <h5>Client Instructions</h5>
        <p dangerouslySetInnerHTML={{
          __html: selectedRecord.get('instruction') ? selectedRecord.get('instruction') : 'N/A'
        }}></p>
      </div>
    );
  }

  render() {
    const {selectedRecord} = this.props;
    return (
      <div>
        {this.techFee(selectedRecord)}
        {this.instructionDocuments(selectedRecord)}
        {this.instructions(selectedRecord)}
      </div>
    );
  }
}
