import React, {Component, PropTypes} from 'react';
import {States, LicenseSearchBar} from 'components';
import {Divider} from 'material-ui';
import {
  DividerWithIcon
} from 'components';
import {Void} from 'components';
import Immutable from 'immutable';

/**
 * ASC search component
 */
export default class AscSearch extends Component {

  static propTypes = {
    form: PropTypes.object.isRequired,
    // Update form
    formChange: PropTypes.func.isRequired,
    // Update state
    stateChange: PropTypes.func.isRequired,
    // If an appraiser from ASC results has been selected
    ascSelected: PropTypes.bool,
    // Appraiser
    appraiser: PropTypes.object,
    // Results
    results: PropTypes.instanceOf(Immutable.List),
    // Results property
    resultsProp: PropTypes.string,
    // State prop
    stateProp: PropTypes.oneOfType([
      PropTypes.string,
      PropTypes.array
    ]),
    // License prop
    licenseProp: PropTypes.string,
    // withDividers prop
    withDividers: PropTypes.bool,
    // Function to select a result
    selectFunction: PropTypes.func.isRequired,
    // with the header
    withHeader: PropTypes.bool,
    // Don't perform a search, accept any license
    noSearch: PropTypes.bool,
    // Remove states from search
    statesToRemove: PropTypes.instanceOf(Immutable.List),
    // Error
    error: PropTypes.string
  };

  constructor(props) {
    super(props);

    this.stateChange = props.stateChange.bind(this, props.stateProp || 'licenseState');
  }

  render() {
    const {
      appraiser,
      formChange,
      form,
      ascSelected,
      results,
      stateProp = 'licenseState',
      licenseProp = 'licenseNumber',
      resultsProp = 'ascResults',
      withDividers,
      selectFunction,
      withHeader = true,
      noSearch = false,
      statesToRemove = Immutable.List(),
      error
    } = this.props;
    // Display no results found
    const displayResults = results || appraiser.get(resultsProp);
    // No results found
    const noResultsFound = !!(form.get(stateProp) && form.get(licenseProp) && !displayResults.count() && !ascSelected);
    return (
      <div>
        {withHeader && !noSearch &&
         <div className="row">
           {withDividers &&
            <div className="col-md-12">
              <Divider/>
            </div>
           }
           <div className="col-md-12 text-center">
             <DividerWithIcon
               label="ASC.GOV Registry Search"
               icon="search"
             />
           </div>
           {withDividers &&
            <div className="col-md-12">
              <Divider/>
            </div>
           }
         </div>
        }
        <div className="row">
          <div className="col-md-6">
            <States
              form={form}
              label="State licensed"
              changeHandler={this.stateChange}
              name={stateProp}
              disabled={ascSelected && !noSearch}
              statesToRemove={statesToRemove}
            />
          </div>
          <div className="col-md-6">
            <LicenseSearchBar
              name={licenseProp}
              value={form.get(licenseProp)}
              label="License number"
              results={displayResults}
              onChange={formChange}
              disabled={ascSelected && !noSearch}
              noResultsFound={noResultsFound}
              selectFunction={selectFunction}
              noSearch={noSearch}
              error={error}
            />
          </div>
        </div>

        <Void pixels={15}/>
      </div>
    );
  }
}
