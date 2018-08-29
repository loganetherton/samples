import React, {Component, PropTypes} from 'react';
import {VpPlainDropdown} from 'components';
import ReactPaginate from 'react-paginate';
import Immutable from 'immutable';

export default class Pagination extends Component {
  static propTypes = {
    // Show pagination
    show: PropTypes.bool,
    // Current page
    pageNum: PropTypes.number.isRequired,
    // Number of pages to display
    paginationDisplay: PropTypes.number,
    // Pages displayed in each margin
    marginPages: PropTypes.number,
    // Labels for either side
    previousLabel: PropTypes.string,
    nextLabel: PropTypes.string,
    // Override page selection
    forceSelected: PropTypes.number,
    // Change page
    onClickPage: PropTypes.func.isRequired,
    // Container class
    containerClass: PropTypes.string,
    // Class for internal container
    subContainerClass: PropTypes.string,
    // Value for per page dropdown
    perPageValue: PropTypes.number.isRequired,
    // options for per page
    perPageOptions: PropTypes.instanceOf(Immutable.List),
    // Function to change per page
    changePerPage: PropTypes.func.isRequired
  };

  render() {
    const {
      show = true,
      pageNum,
      paginationDisplay = 5,
      marginPages = 2,
      previousLabel = 'Previous',
      nextLabel = 'Next',
      forceSelected,
      onClickPage,
      containerClass = '',
      subContainerClass = '',
      perPageValue,
      perPageOptions = Immutable.fromJS([
        {value: 10, name: 10},
        {value: 25, name: 25},
        {value: 50, name: 50},
        {value: 100, name: 100}
      ]),
      changePerPage
    } = this.props;
    return (
      <div className="row">
        <div className="col-md-10">
          {show &&
           <ReactPaginate
             pageNum={pageNum}
             pageRangeDisplayed={paginationDisplay}
             marginPagesDisplayed={marginPages}
             previousLabel={previousLabel}
             nextLabel={nextLabel}
             breakLabel={<a href="">...</a>}
             forceSelected={forceSelected}
             clickCallback={onClickPage}
             containerClassName={`pagination ${containerClass}`}
             subContainerClassName={subContainerClass}
             activeClassName={"active"}
           />
          }
        </div>
        <div className="col-md-2 pull-right">
          <VpPlainDropdown
            onChange={changePerPage}
            value={perPageValue}
            options={perPageOptions}
            label="Per Page"
          />
        </div>
      </div>
    );
  }
}
