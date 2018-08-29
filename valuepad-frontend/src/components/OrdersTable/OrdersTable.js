import React, {Component, PropTypes} from 'react';
import Immutable from 'immutable';
import moment from 'moment';
import {
  OrdersActionButtons,
  FilterStates,
  FilterDatePicker,
  FilterDue,
} from 'components';
import HeaderInput from './HeaderInput.js';
import _ from 'lodash';
import ReactPaginate from 'react-paginate';
import ReactTooltip from 'react-tooltip';

/* pagination params */
const paginationDisplay = 5;
const marginPages = 2;

/* default column mappings */
const ColumnMappings = Immutable.fromJS({
  acceptedDateColumn: { sort: 'acceptedAt' },
  addressColumn: { sort: 'property.address' },
  submittedByColumn: { sort: 'customer.name' },
  cityColumn: { sort: 'property.city' },
  completedAtColumn: { sort: 'completedAt' },
  dueDateColumn: { sort: 'dueDate' },
  fileNumberColumn: { sort: 'fileNumber' },
  estimatedCompletionDateColumn: { sort: 'estimatedCompletionDate' },
  inpsectionCompleteColumn: { sort: 'inspectionCompletedAt' },
  inpsectionScheduledColumn: { sort: 'inspectionScheduledAt' },
  nameColumn: { sort: 'borrowerName' },
  orderedDateColumn: { sort: 'orderedAt' },
  revisionReceivedColumn: { sort: 'revisionReceivedAt' },
  stateColumn: { sort: 'property.state.name' },
  statusColumn: { sort: 'processStatus' },
  whenPutOnHoldColumn: { sort: 'putOnHoldAt' },
  zipColumn: { sort: 'property.zip' },
});

const styles = {
  orderTable: {
    border: '1px solid #CCC',
  },
  sortIcon: {
    color: '#000000'
  },
  pointer: {cursor: 'pointer'},
  blank: {},
  sortColumns: {padding: 0},
  searchCell: {border: 'none'},
  table: {width: '100%', marginTop: '10px'},
  sortIconsWrapper: {textAlign: 'center', height: '22px'},
  headerLabel: {margin: 0},
  headerTooltip: {top: 12}
};

export default class OrdersTable extends Component {
  static propTypes = {
    // Orders to display
    orders: PropTypes.instanceOf(Immutable.Map),
    // Columns to display
    columns: PropTypes.array.isRequired,
    // Set prop
    setProp: PropTypes.func.isRequired,
    // URL params
    params: PropTypes.object,
    // Push state
    pushState: PropTypes.func.isRequired,
    // Orders including a sort cell
    ordersWithSort: PropTypes.instanceOf(Immutable.List).isRequired,
    // header display
    headDisplay: PropTypes.func.isRequired,
    // Open details pane
    openFileDetails: PropTypes.func.isRequired,
    // Selected appraiser (customer view)
    selectedAppraiser: PropTypes.number,
    // Toggle accept dialog
    toggleAcceptDialog: PropTypes.func.isRequired,
    // Toggle accept with conditions dialog
    toggleAcceptWithConditionsDialog: PropTypes.func.isRequired,
    // Toggle the decline dialog
    toggleDeclineDialog: PropTypes.func.isRequired,
    // Toggle submit bid
    toggleSubmitBid: PropTypes.func.isRequired,
    // Toggle schedule inspection
    toggleScheduleInspection: PropTypes.func.isRequired,
    // Toggle inspection complete
    toggleInspectionComplete: PropTypes.func.isRequired,
    // Toggle reassign dialog
    toggleReassign: PropTypes.func.isRequired,
    // Auth
    auth: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Show company column
    showCompanyColumn: PropTypes.bool,
    // Manager/RFP manager of companies
    companyManagement: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);

    this.changePage = ::this.changePage;
    this.setColumnSearchValue = ::this.setColumnSearchValue;
    this.setStateFilter = this.setSearchValueNonInput.bind(this, 'filter[property][state]');
    this.setDueDateFilter = this.setSearchValueNonInput.bind(this, 'filter[due]');
  }

  /**
   * Make sure the correct UI state is set for the back button
   */
  componentDidMount() {
    const {orders, openFileDetails} = this.props;
    const uiState = orders.get('uiState');
    if (uiState.get('record')) {
      if (uiState.get('detailsPaneOpen')) {
        openFileDetails(uiState.get('record'));
      }
      if (uiState.get('page') !== 1) {
        this.changePage.call(this, {
          selected: uiState.get('page') - 1
        });
      }
    }
  }

  /**
   * Set a column search value
   */
  setColumnSearchValue(event) {
    const {name, value} = event.target;
    this.props.setProp(1, 'search', 'page');
    this.props.setProp(value, 'search', name);
  }

  /**
   * Set a value for state column search
   * @param propName Search property name
   * @param value Search value to set
   */
  setSearchValueNonInput(propName, value) {
    this.props.setProp(1, 'search', 'page');
    this.props.setProp(value, 'search', propName);
  }

  /**
   * Sets a value for a date column
   */
  setDateInput(propName, date) {
    this.props.setProp(1, 'search', 'page');
    if (date) {
      this.props.setProp(date.format('YYYY-MM-DD'), 'search', propName);
    } else {
      this.props.setProp(null, 'search', propName);
    }
  }

  /**
   * Get the current order for a cell
   * @param rowIndex Index of row
   */
  getThisOrder(rowIndex) {
    return this.props.ordersWithSort.get(rowIndex);
  }

  /**
   * File number header input search
   */
  headerInput(prop, label) {
    const {orders} = this.props;
    const value = orders.getIn(['search', prop]);

    return (
      <HeaderInput
        value={value}
        prop={prop}
        onChange={this.setColumnSearchValue}
        label={label}
      />
    );
  }

  /**
   * State header
   */
  stateHeader() {
    const {orders} = this.props;
    const propName = 'filter[property][state]';
    return (
      <div>
        <label className="control-label" style={styles.headerLabel}>
          <span>State</span>
        </label>
        <div data-tip data-for={propName}>
          <FilterStates
            form={orders.get('search') || Immutable.List()}
            changeHandler={this.setStateFilter}
            name={propName}
            fullWidth={false}
          />
          <ReactTooltip id={propName} place="bottom" type="dark" effect="solid" offset={styles.headerTooltip}>
            <span>Select a state to filter</span>
          </ReactTooltip>
        </div>
      </div>
    );
  }

  /**
   * Process status header
   */
  statusHeader() {
    return (
      <div>
        <label className="control-label" style={styles.headerLabel}>
          <span>Status</span>
        </label>
      </div>
    );

    // const {orders} = this.props;
    // const propName = 'filter[processStatus]';
    // return (
    //   <div>
    //     <label className="control-label" style={{ margin: 0 }}>
    //       <span>Status</span>
    //     </label>
    //     <div data-tip data-for={propName}>
    //       <FilterProcessStatusDropdown
    //         form={orders.get('search') || Immutable.List()}
    //         changeHandler={this.setSearchValueNonInput.bind(this, propName)}
    //         name={propName}
    //         fullWidth={false}
    //       />
    //       <ReactTooltip id={propName} place="bottom" type="dark" effect="solid" offset={{top: 12}}>
    //         <span>Select a status to filter</span>
    //       </ReactTooltip>
    //     </div>
    //   </div>
    // );
  }

  /**
   * Date header datepicker
   */
  dateHeader(propName, label) {
    const {orders} = this.props;
    return (
      <div>
        <label className="control-label" style={styles.headerLabel}>
          <span>{label}</span>
        </label>
        <div data-tip data-for={propName}>
          <FilterDatePicker
            form={orders.get('search') || Immutable.List()}
            changeHandler={this.setDateInput.bind(this, propName)}
            name={propName}
          />
          <ReactTooltip id={propName} place="bottom" type="dark" effect="solid" offset={styles.headerTooltip}>
            <span>Select a date to filter</span>
          </ReactTooltip>
        </div>
      </div>
    );
  }

  /**
   * Due date header
   */
  dueDateHeader() {
    const {orders} = this.props;
    const propName = 'filter[due]';

    return (
      <div>
        <label className="control-label" style={styles.headerLabel}>
          <span>Due Date</span>
        </label>
        <div data-tip data-for={propName}>
          <FilterDue
            form={orders.get('search') || Immutable.List()}
            changeHandler={this.setDueDateFilter}
            name={propName}
          />
          <ReactTooltip id={propName} place="bottom" type="dark" effect="solid" offset={styles.headerTooltip}>
            <span>Select a due date to filter</span>
          </ReactTooltip>
        </div>
      </div>
    );
  }

  /**
   * Actions field header
   * @returns {XML}
   */
  actionsHeader() {
    return (
      <div>
        <label className="control-label" style={styles.headerLabel}>
          <p>Actions</p>
        </label>
      </div>
    );
  }

  /**
   * Sort orders by a property descending
   * @param prop
   *
   * @todo This is not available in the backend yet. It's been requested in Jira
   * @link https://appraisalscope.atlassian.net/browse/VP-48
   */
  sortCell(prop) {
    this.props.setProp(prop, 'search', 'orderBy');
  }

  /**
   * Create sort cell at the top of the table
   * @param sortProp
   */
  createSortCell(sortProp) {
    if (!sortProp) return null;

    // Current sorting property
    const orderBy = this.props.orders.getIn(['search', 'orderBy']);

    return (
      <div style={styles.sortIconsWrapper}>
        {(!orderBy || orderBy.indexOf(sortProp) || orderBy === sortProp + ':desc') &&
          <span style={styles.sortIconContainer} role="button" onClick={this.sortCell.bind(this, sortProp + ':asc')}>
            <i className="material-icons" style={styles.sortIcon}>keyboard_arrow_up</i>
          </span>
        }
        {(!orderBy || orderBy.indexOf(sortProp) || orderBy === sortProp + ':asc') &&
          <span style={styles.sortIconContainer} role="button" onClick={this.sortCell.bind(this, sortProp + ':desc')}>
            <i className="material-icons" style={styles.sortIcon}>keyboard_arrow_down</i>
          </span>
        }
      </div>
    );
  }

  /**
   * Format a date cell
   * @param thisOrder Record
   * @param prop Prop to find in record
   * @returns {string}
   */
  formatDateCell(thisOrder, prop) {
    const recordDate = thisOrder.get(prop);
    return recordDate ? moment(recordDate).format('MM/DD/YYYY') : '--';
  }

  /**
   * File number column
   * @returns {XML}
   */
  fileNumberColumn() {
    return this.headerInput.call(this, 'search[fileNumber]', 'File Number');
  }

  /**
   * File number
   * @returns {XML}
   */
  fileNumberCell(rowIndex) {
    const prop = 'fileNumber';
    const thisOrder = this.getThisOrder.call(this, rowIndex);

    return (
      <div className="link">{thisOrder.get(prop)}</div>
    );
  }

  /**
   * Name column
   * @returns {XML}
   */
  nameColumn() {
    return this.headerInput.call(this, 'search[borrowerName]', 'Borrower Name');
  }

  /**
   * Display full name in table
   * @param contact
   */
  fullName(contact) {
    if (!contact) {
      return '';
    }
    return `${contact.get('firstName') ? contact.get('firstName') : ''} ${contact.get('middleName') ? contact.get('middleName') + ' ' : ''}${contact.get('lastName') ? contact.get('lastName') : ''}`;
  }

  /**
   * Name
   * @returns {XML}
   */
  nameCell(rowIndex) {
    const thisOrder = this.getThisOrder.call(this, rowIndex);
    const contacts = thisOrder.getIn(['property', 'contacts']);
    const borrower = contacts.filter(contact => {
      return contact.get('type') === 'borrower';
    });

    // No borrower
    if (!borrower) {
      return <div />;
    } else {
      return <div>{this.fullName(borrower.get(0))}</div>;
    }
  }

  /**
   * Submitted by column (displays customer name)
   * @returns {XML}
   */
  submittedByColumn() {
    return this.headerInput.call(this, 'search[customer][name]', 'Submitted by');
  }

  /**
   * Submitted by cell (displays customer name)
   * @returns {XML}
   */
  submittedByCell(rowIndex) {
    const thisOrder = this.getThisOrder.call(this, rowIndex);
    return (
      <div>{thisOrder.getIn(['customer', 'name'])}</div>
    );
  }

  /**
   * Address column
   * @returns {XML}
   */
  addressColumn() {
    return this.headerInput.call(this, 'search[property][address]', 'Address');
  }

  /**
   * Address
   * @returns {XML}
   */
  addressCell(rowIndex) {
    const thisOrder = this.getThisOrder.call(this, rowIndex);
    return (
      <div>{thisOrder.getIn(['property', 'address1'])}</div>
    );
  }

  /**
   * City column
   * @returns {XML}
   */
  cityColumn() {
    return this.headerInput.call(this, 'search[property][city]', 'City');
  }

  /**
   * City
   * @returns {XML}
   */
  cityCell(rowIndex) {
    const thisOrder = this.getThisOrder.call(this, rowIndex);
    return (
      <div>{thisOrder.getIn(['property', 'city'])}</div>
    );
  }

  /**
   * State column
   * @returns {XML}
   */
  stateColumn() {
    return this.stateHeader.call(this);
  }

  /**
   * State
   * @returns {XML}
   */
  stateCell(rowIndex) {
    const thisOrder = this.getThisOrder.call(this, rowIndex);
    return (
      <div>{thisOrder.getIn(['property', 'state', 'name'])}</div>
    );
  }

  /**
   * Zip column
   * @returns {XML}
   */
  zipColumn() {
    return this.headerInput.call(this, 'filter[property][zip]', 'Zip');
  }

  /**
   * Zip
   * @returns {XML}
   */
  zipCell(rowIndex) {
    const thisOrder = this.getThisOrder.call(this, rowIndex);
    return (
      <div>{thisOrder.getIn(['property', 'zip'])}</div>
    );
  }

  /**
   * Client name column
   * @returns {XML}
   */
  clientNameColumn() {
    return this.headerInput.call(this, 'filter[client][name]', 'Client Name');
  }

  /**
   * Client name cell
   * @returns {XML}
   */
  clientNameCell(rowIndex) {
    const thisOrder = this.getThisOrder.call(this, rowIndex);
    return (
      <div>{thisOrder.get('clientName')}</div>
    );
  }

  /**
   * Status column
   * @returns {XML}
   */
  statusColumn() {
    return this.statusHeader.call(this);
  }

  /**
   * Status
   * @returns {XML}
   */
  statusCell(rowIndex) {
    const thisOrder = this.getThisOrder.call(this, rowIndex);
    return (
      <div>{_.capitalize(thisOrder.get('processStatus').replace(/-/g, ' '))}</div>
    );
  }

  /**
   * Due date column
   * @returns {XML}
   */
  dueDateColumn() {
    return this.dueDateHeader.call(this);
  }

  /**
   * Accepted date column
   * @returns {XML}
   */
  acceptedDateColumn() {
    return this.dateHeader.call(this, 'filter[acceptedAt]', 'Accepted Date');
  }

  /**
   * Accepted date
   * @returns {XML}
   */
  acceptedDateCell(rowIndex) {
    const thisOrder = this.getThisOrder.call(this, rowIndex);
    return (
      <div>{this.formatDateCell(thisOrder, 'acceptedAt')}</div>
    );
  }

  /**
   * Inspection scheduled date column
   * @returns {XML}
   */
  inspectionScheduledAtColumn() {
    return this.dateHeader.call(this, 'filter[inspectionScheduledAt]', 'Inspection Schd.');
  }

  /**
   * Accepted date
   * @returns {XML}
   */
  inspectionScheduledAtCell(rowIndex) {
    const thisOrder = this.getThisOrder.call(this, rowIndex);
    return (
      <div>{this.formatDateCell(thisOrder, 'inspectionScheduledAt')}</div>
    );
  }

  /**
   * Inspection completed date column
   * @returns {XML}
   */
  inspectionCompletedAtColumn() {
    return this.dateHeader.call(this, 'filter[inspectionCompletedAt]', 'Inspection Complete');
  }

  /**
   * Inspection complete cell
   * @returns {XML}
   */
  inspectionCompletedAtCell(rowIndex) {
    const thisOrder = this.getThisOrder.call(this, rowIndex);
    return (
      <div>{this.formatDateCell(thisOrder, 'inspectionCompletedAt')}</div>
    );
  }

  /**
   * Estimated completion date column
   * @returns {XML}
   */
  estimatedCompletionDateColumn() {
    return this.dateHeader.call(this, 'filter[estimatedCompletionDate]', 'Est. Completion');
  }

  /**
   * Estimated completion date cell
   * @returns {XML}
   */
  estimatedCompletionDateCell(rowIndex) {
    const thisOrder = this.getThisOrder.call(this, rowIndex);
    return (
      <div>{this.formatDateCell(thisOrder, 'estimatedCompletionDate')}</div>
    );
  }

  /**
   * When put on hold column
   * @returns {XML}
   */
  whenPutOnHoldColumn() {
    return this.dateHeader.call(this, 'filter[putOnHoldAt]', 'When Put On Hold');
  }

  /**
   * When put on hold
   * @returns {XML}
   */
  whenPutOnHoldCell(rowIndex) {
    const thisOrder = this.getThisOrder.call(this, rowIndex);
    return (
      <div>{this.formatDateCell(thisOrder, 'putOnHoldAt')}</div>
    );
  }

  /**
   * Revision received column
   * @returns {XML}
   */
  revisionReceivedColumn() {
    return this.dateHeader.call(this, 'filter[revisionReceivedAt]', 'Revision Received');
  }

  /**
   * Revision received
   * @returns {XML}
   */
  revisionReceivedCell(rowIndex) {
    const thisOrder = this.getThisOrder.call(this, rowIndex);
    return (
      <div>{this.formatDateCell(thisOrder, 'revisionReceivedAt')}</div>
    );
  }

  /**
   * Completed at column
   * @returns {XML}
   */
  completedAtColumn() {
    return this.dateHeader.call(this, 'filter[completedAt]', 'Completed Date');
  }

  /**
   * Completed date cell
   * @returns {XML}
   */
  completedAtCell(rowIndex) {
    const thisOrder = this.getThisOrder.call(this, rowIndex);
    return (
      <div>{this.formatDateCell(thisOrder, 'completedAt')}</div>
    );
  }

  /**
   * Company name column
   * @returns {XML}
   */
  companyColumn() {
    return this.headerInput.call(this, 'search[companyName]', 'Company Name');
  }

  /**
   * Company name cell
   * @returns {XML}
   */
  companyCell(rowIndex) {
    const thisOrder = this.getThisOrder.call(this, rowIndex);
    return (
      <div>{thisOrder.getIn(['company', 'name']) || 'N/A'}</div>
    );
  }

  /**
   * Due date
   * @returns {XML}
   */
  dueDateCell(rowIndex) {
    const thisOrder = this.getThisOrder.call(this, rowIndex);
    return (
      <div>{this.formatDateCell(thisOrder, 'dueDate')}</div>
    );
  }

  /**
   * Ordered date column
   * @returns {XML}
   */
  orderedDateColumn() {
    return this.dateHeader.call(this, 'filter[orderedAt]', 'Ordered Date');
  }

  /**
   * Ordered date
   * @returns {XML}
   */
  orderedDateCell(rowIndex) {
    const thisOrder = this.getThisOrder.call(this, rowIndex);
    return (
      <div>{this.formatDateCell(thisOrder, 'orderedAt')}</div>
    );
  }

  /**
   * Actions column
   * @returns {XML}
   */
  actionsColumn() {
    return this.actionsHeader.call(this);
  }

  /**
   * Actions cell
   * @returns {XML}
   */
  actionsCell(rowIndex) {
    const thisOrder = this.getThisOrder.call(this, rowIndex);
    const {
      toggleAcceptDialog,
      toggleAcceptWithConditionsDialog,
      toggleDeclineDialog,
      toggleSubmitBid,
      toggleScheduleInspection,
      toggleInspectionComplete,
      toggleReassign,
      auth,
      companyManagement
    } = this.props;

    return (
      <OrdersActionButtons
        order={thisOrder}
        toggleAcceptDialog={toggleAcceptDialog}
        toggleAcceptWithConditionsDialog={toggleAcceptWithConditionsDialog}
        toggleDeclineDialog={toggleDeclineDialog}
        toggleSubmitBid={toggleSubmitBid}
        toggleScheduleInspection={toggleScheduleInspection}
        toggleInspectionComplete={toggleInspectionComplete}
        toggleReassign={toggleReassign}
        auth={auth}
        companyManagement={companyManagement}
      />
    );
  }

  /**
   * Change page within a view
   * @param pagination Pagination object
   */
  changePage(pagination) {
    const {setProp} = this.props;
    const page = Number(pagination.selected + 1);
    setProp(page, 'search', 'page');
    setProp(page, 'uiState', 'page');
  }

  render() {
    const {orders, ordersWithSort, selectedAppraiser} = this.props;
    const columns = this.props.columns;
    if (selectedAppraiser && columns.indexOf('actionsColumn') !== -1) {
      columns.splice(columns.indexOf('actionsColumn'), 1);
    }
    const columnCount = columns.length;

    // Total pages
    const totalPages = orders.getIn(['meta', 'pagination', 'totalPages']) || 0;
    return (
      <div className="row">
        <div className="col-md-12">
          <table className="data-table" style={styles.table}>
            <thead>
              <tr key="search">
                <td style={styles.searchCell} colSpan={ columnCount }>{ this.props.headDisplay() }</td>
              </tr>
              <tr key="columns">
                {columns.map((column) => {
                  return (
                    <th key={column}>
                      {this[column]()}
                    </th>
                  );
                })}
              </tr>
              <tr key="sort_columns">
                {columns.map((column, index) => {
                  return (
                    <td key={ 'sort_column' + index } style={styles.sortColumns}>
                      {this.createSortCell.call(this, ColumnMappings.getIn([column, 'sort']))}
                    </td>
                  );
                })}
              </tr>
            </thead>
            <tbody>
              {!!ordersWithSort.count() && ordersWithSort.map((order, rowIndex) => {
                return (
                  <tr key={ 'row_' + rowIndex }>
                    {columns.map((column) => {
                      return (
                        <td
                          key={ 'column_' + rowIndex + '_' + column}
                          onClick={this.props.openFileDetails.bind(this, order, column)}
                          style={column !== 'actionsColumn' ? styles.pointer : styles.blank}
                        >
                          {this[column.replace('Column', 'Cell')](rowIndex)}
                        </td>
                      );
                    })}
                  </tr>
                );
              })}

              {!ordersWithSort.count() &&
                <tr key={'empty-results'}>
                  <td colSpan={columnCount}>There are currently no orders for this status.</td>
                </tr>
              }
            </tbody>
          </table>
        </div>

        {totalPages > 1 &&
         <div className="row">
           <div className="col-md-12">
             <ReactPaginate
               pageNum={orders.getIn(['meta', 'pagination', 'totalPages'])}
               pageRangeDisplayed={paginationDisplay}
               marginPagesDisplayed={marginPages}
               previousLabel={"Previous"}
               nextLabel={"Next"}
               breakLabel={<a href="">...</a>}
               forceSelected={Number(orders.getIn(['search', 'page'])) - 1}
               clickCallback={this.changePage}
               containerClassName={"pagination orders-pagination-container"}
               subContainerClassName={"pages pagination"}
               activeClassName={"active"}/>
           </div>
         </div>
        }
      </div>
    );
  }
}
