import React, {Component, PropTypes} from 'react';
import moment from 'moment';
import Immutable from 'immutable';

import {
  VpTextField,
  MyDropzone,
  VpDateRange,
} from 'components';

export default class CompanyEo extends Component {
  static propTypes = {
    // Company object
    company: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Form disabled
    disabled: PropTypes.bool,
    // Errors
    errors: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Set values
    setEoDate: PropTypes.func.isRequired,
    setEoNumber: PropTypes.func.isRequired,
    setEoValue: PropTypes.func.isRequired,
    // Paths for EO values in reducer
    propPaths: PropTypes.object,
    // Upload EO doc
    uploadEoDoc: PropTypes.func.isRequired
  };

  render() {
    const {company, errors, disabled = false, uploadEoDoc, setEoValue, setEoDate, setEoNumber} = this.props;
    const {propPaths = {
      aggregateAmount: ['aggregateAmount'],
      carrier: ['carrier'],
      claimAmount: ['claimAmount'],
      deductible: ['deductible'],
      document: ['document'],
      expiresAt: ['expiresAt'],
    }} = this.props;
    return (
      <div>
        <div className="row">
          <div className="col-md-6">
            <VpTextField
              value={company.getIn(propPaths.carrier)}
              label="E&O Carrier"
              name="carrier"
              placeholder="E&O Carrier"
              onChange={setEoValue}
              error={errors.getIn(propPaths.carrier)}
            />
          </div>
          <div className="col-md-6">
            <VpDateRange
              minDate={moment().add(1, 'days')}
              date={company.getIn(propPaths.expiresAt) ? moment(company.getIn(propPaths.expiresAt)) : null}
              changeHandler={setEoDate}
              label="E&O expiration date"
              disabled={disabled}
              error={errors.getIn(propPaths.expiresAt)}
            />
          </div>
        </div>
        <div className="row">
          <div className="col-md-4">
            <VpTextField
              value={company.getIn(propPaths.claimAmount)}
              label="E&O per claim amount"
              name="claimAmount"
              placeholder="E&O per claim amount"
              onChange={setEoNumber}
              error={errors.getIn(propPaths.claimAmount)}
              disabled={disabled}
            />
          </div>
          <div className="col-md-4">
            <VpTextField
              value={company.getIn(propPaths.aggregateAmount)}
              label="E&O aggregate amount"
              name="aggregateAmount"
              placeholder="E&O aggregate amount"
              onChange={setEoNumber}
              error={errors.getIn(propPaths.aggregateAmount)}
              disabled={disabled}
            />
          </div>
          <div className="col-md-4">
            <VpTextField
              value={company.getIn(propPaths.deductible)}
              label="Deductible"
              name="deductible"
              placeholder="Deductible"
              onChange={setEoNumber}
              error={errors.getIn(propPaths.deductible)}
              disabled={disabled}
            />
          </div>
        </div>
        <div className="row">
          <div className="col-md-12 text-center">
            <MyDropzone
              refName="eo-insurance"
              onDrop={uploadEoDoc}
              uploadedFiles={company.getIn(propPaths.document) ? Immutable.List().push(company.getIn(propPaths.document)) : Immutable.List()}
              acceptedFileTypes={['ANY']}
              instructions="Upload document"
              label={disabled ? 'E&O insurance document' : 'Upload your E&O insurance document'}
              hideButton={disabled}
              hideInstructions={disabled}
              error={errors.getIn(propPaths.document)}
            />
          </div>
        </div>
      </div>
    );
  }
}
