import React, {Component, PropTypes} from 'react';
import Immutable from 'immutable';
import {Link} from 'react-router';
import {ORDERS_DETAILS} from 'redux/modules/urls';
import {Confirm} from 'components';

export default class OrdersDetailsHeader extends Component {
  static propTypes = {
    // Full screen
    fullScreen: PropTypes.bool,
    // Close details pane func
    closeDetailsPane: PropTypes.func.isRequired,
    // Selected record
    selectedRecord: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Go to details view func
    goToDetailsView: PropTypes.func,
    // URL params
    params: PropTypes.object,
    // Display map/print functions
    mapPrint: PropTypes.bool,
    // Toggle instructions
    toggleInstructions: PropTypes.func,
    // Set print content
    setPrintContent: PropTypes.func,
    // Get print content
    getPrintContent: PropTypes.func,
    // Remove print content
    removePrintContent: PropTypes.func,
    // Toggle on hold
    toggleOnHold: PropTypes.func,
    // Toggle resume after being placed on hold
    toggleResume: PropTypes.func,
    // User type
    userType: PropTypes.string
  };

  state = {
    showPrintPreview: false
  }

  /**
   * Close the print preview modal
   */
  hidePrintPreview() {
    this.setState({showPrintPreview: false});
  }

  /**
   * Shows the print preview modal
   */
  showPrintPreview() {
    this.setState({showPrintPreview: true});
  }

  /**
   * Decorates the actual content that should be printed
   */
  getPrintContent() {
    const {getPrintContent, selectedRecord} = this.props;
    return (
      <div>
        <div style={{textAlign: 'center'}}>{selectedRecord.get('fileNumber')} - {selectedRecord.getIn(['property', 'address1'])}</div>
        {getPrintContent()}
      </div>
    );
  }

  render() {
    const {
      fullScreen,
      closeDetailsPane = () => ({}),
      selectedRecord,
      mapPrint = false,
      toggleInstructions,
      setPrintContent,
      toggleOnHold,
      toggleResume,
      userType
    } = this.props;
    return (
      <div>
        <div className="row">
          {!fullScreen &&
            <div className="col-md-2">
              <div className="order-details-icon-container hover-red" role="button" onClick={closeDetailsPane}>
                <i className="material-icons">highlight_off</i>
                <span>Close</span>
              </div>
           </div>
          }
          <div className={ !fullScreen ? 'col-md-4' : 'col-md-6' }>
            <div style={{ marginTop: '9px', paddingBottom: '6px' }}><span style={{ fontWeight: 'bold' }}>FILE NUMBER:</span> {selectedRecord.get('fileNumber')}</div>
          </div>
          <div className="col-md-6">
            {mapPrint &&
              <div className="order-details-icon-container hover-blue pull-right" role="button" onClick={::this.showPrintPreview}>
                <i className="material-icons">print</i>
                <span>Print</span>
              </div>
            }
            {!fullScreen &&
              <div className="order-details-icon-container hover-blue pull-right" role="button">
                <Link to={`${ORDERS_DETAILS}/${selectedRecord.get('id')}`} style={{ textDecoration: 'none' }}>
                  <i className="material-icons">insert_drive_file</i>
                  <span>Record</span>
                </Link>
              </div>
            }
            {mapPrint && ['new', 'request-for-bid'].indexOf(selectedRecord.get('processStatus')) === -1 &&
              <div className="instructions-button order-details-icon-container hover-blue pull-right" role="button"
                   onClick={toggleInstructions}>
                <i className="material-icons">my_library_books</i>
                <span>Requirements</span>
              </div>
            }
          </div>
        </div>
        {userType === 'amc' && ['on-hold', 'completed'].indexOf(selectedRecord.get('processStatus')) === -1 &&
          <div className="row">
            <div className="col-md-12">
              <div className="order-details-icon-container hover-blue" role="button" onClick={toggleOnHold}>
                <i className="material-icons">feedback</i>
                <span>Place on hold</span>
              </div>
            </div>
          </div>
        }
        {userType === 'amc' && selectedRecord.get('processStatus') === 'on-hold' &&
         <div className="row">
           <div className="col-md-12">
             <div className="order-details-icon-container hover-blue" role="button" onClick={toggleResume}>
               <i className="material-icons">done</i>
               <span>Resume</span>
             </div>
           </div>
         </div>
        }
        {(mapPrint && this.state.showPrintPreview) &&
          <Confirm
            body={this.getPrintContent()}
            title="Print Preview"
            show={this.state.showPrintPreview}
            hide={::this.hidePrintPreview}
            submitHide
            enablePrint
            submit={::window.print}
            buttonText={{ cancel: 'Close' }}
            contentStyle={{ width: '90%', maxWidth: 'auto' }}
            setPrintContent={setPrintContent}
          />
        }
      </div>
    );
  }
}
