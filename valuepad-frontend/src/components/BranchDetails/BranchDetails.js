import React, {Component, PropTypes} from 'react';
import Immutable from 'immutable';

import {Address, BetterTextField, CompanyEo} from 'components';

export default class BranchDetails extends Component {
  static propTypes = {
    form: PropTypes.instanceOf(Immutable.Map).isRequired,
    changeForm: PropTypes.func.isRequired,
    errors: PropTypes.instanceOf(Immutable.Map).isRequired,
    update: PropTypes.func.isRequired,
    changeEoDetails: PropTypes.func.isRequired,
    uploadEoDoc: PropTypes.func.isRequired,
    changeEoDate: PropTypes.func.isRequired
  };

  render() {
    const {form, changeForm, errors, update, changeEoDate, changeEoDetails, uploadEoDoc} = this.props;
    return (
      <div>
        <div className="row">
          <div className="col-md-6">
            <BetterTextField
              value={form.get('name')}
              error={errors.get('name')}
              label="Branch Name"
              name="name"
              onChange={changeForm}
              required
            />
          </div>
          <div className="col-md-6">
            <BetterTextField
              value={form.get('taxId')}
              error={errors.get('taxId')}
              label="Tax ID"
              name="taxId"
              onChange={changeForm}
            />
          </div>
        </div>
        <Address
          form={form}
          formChange={changeForm}
          changeState={changeForm}
          errors={errors}
          enterFunction={update}
          required
        />
        <div className="row">
          <div className="col-md-12">
            <BetterTextField
              value={form.get('assignmentZip')}
              label="Zip Code for Assignments"
              name="assignmentZip"
              onChange={changeForm}
              required
            />
          </div>
        </div>
        <CompanyEo
          company={form.get('eo') || Immutable.Map()}
          errors={errors.get('eo') || Immutable.Map()}
          setEoDate={changeEoDate}
          setEoNumber={changeEoDetails}
          setEoValue={changeEoDetails}
          uploadEoDoc={uploadEoDoc}
        />
      </div>
    );
  }
}
