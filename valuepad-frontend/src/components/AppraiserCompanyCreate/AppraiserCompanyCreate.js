import React, {Component, PropTypes} from 'react';
import Immutable from 'immutable';
import {
  VpTextField,
  Address,
  PhoneNumber,
  TaxClassification,
  MyDropzone,
  UploadDialog,
  ActionButton,
  AchForm,
  CompanyEo
} from 'components';

import {newCompany} from 'redux/modules/company';

// Create prop paths
const propPaths = {};
function createPropPaths(incoming, parent) {
  incoming.forEach((val, prop) => {
    if (Immutable.Iterable.isIterable(val)) {
      createPropPaths(val, prop);
    }
    const immediatePath = [prop];
    if (parent) {
      immediatePath.unshift(parent);
    }
    propPaths[prop] = [...immediatePath];
  });
}
createPropPaths(Immutable.fromJS(newCompany));

import {numberOnly, downloadW9Form} from 'helpers/genericFunctions';
import {inputGroupClass} from 'helpers/styleHelpers';

import pureRender from 'pure-render-decorator';

/**
 * Create/update appraiser company
 */
@pureRender
export default class AppraiserCompanyCreate extends Component {
  static propTypes = {
    // Company creation form
    company: PropTypes.instanceOf(Immutable.Map).isRequired, // Errors
    errors: PropTypes.instanceOf(Immutable.Map).isRequired, // Submission disabled
    disabled: PropTypes.bool, // Set prop
    setProp: PropTypes.func.isRequired, // Create company function
    createCompany: PropTypes.func, // Remove prop
    removeProp: PropTypes.func.isRequired, // Upload file
    uploadFile: PropTypes.func.isRequired, // Prefills company's info with appraiser's
    prefill: PropTypes.func, // Prefill with appraiser or company info
    getAchInfo: PropTypes.func, // Current user
    user: PropTypes.instanceOf(Immutable.Map), // Set ACH default after it's retrieved
    setAppraiserAchDefaults: PropTypes.func,
    // Patch existing company
    patchCompany: PropTypes.func,
    // Hide W9 section
    creatingCompany: PropTypes.bool,
    // Company path prop (newCompany or selectedCompany)
    companyProp: PropTypes.string,
    // Style
    style: PropTypes.object
  };

  state = {
    documentUploading: false,
    w9: Immutable.List()
  };

  constructor(props) {
    super(props);
    const {createCompany, patchCompany} = this.props;

    this.uploadW9 = ::this.uploadW9;
    this.uploadEoDoc = ::this.uploadEoDoc;
    this.setEoDate = ::this.setEoDate;
    this.setEoValue = ::this.setEoValue;
    this.setEoNumber = ::this.setEoNumber;
    this.setAchValue = ::this.setAchValue;
    this.changeCompanyName = this.changeInput.bind(this, 'name');
    this.changeFirstName = this.changeInput.bind(this, 'firstName');
    this.changeLastName = this.changeInput.bind(this, 'lastName');
    this.changeEmail = this.changeInput.bind(this, 'email');
    this.changeAssignmentZip = this.changeInput.bind(this, 'assignmentZip');
    this.changeAddress = ::this.changeInputAddress;
    this.changeState = ::this.changeState;
    // Update or patch function
    this.submitFunction = createCompany || patchCompany;
  }

  /**
   * Get initial appraiser data
   */
  componentDidMount() {
    const {prefill, getAchInfo, user, setAppraiserAchDefaults} = this.props;
    // Appraiser data
    prefill();

    if (getAchInfo) {
      // Appraiser ACH data
      getAchInfo(user)
        .then(action => {
          setAppraiserAchDefaults(action.result);
        });
    }
  }

  /**
   * Change state dropdown
   * @param state
   */
  changeState(state) {
    const {companyProp, setProp} = this.props;
    setProp(state, companyProp, 'state');
  }

  /**
   * Change an input value
   * @param name Prop name
   * @param event Synthetic event
   */
  changeInput(name, event) {
    const {companyProp, setProp} = this.props;
    setProp(event.target.value, companyProp, name);
  }

  /**
   * Change address field
   * @param event Synthetic event
   */
  changeInputAddress(event) {
    const {companyProp, setProp} = this.props;
    const {target: {name, value}} = event;
    setProp(value, companyProp, name);
  }

  /**
   * Change the E&O expiry date
   * @param date
   */
  setEoDate(date) {
    const {companyProp, setProp} = this.props;
    setProp(date, companyProp, 'eo', 'expiresAt');
  }

  /**
   * @param files
   */
  uploadW9(files) {
    const {companyProp, uploadFile} = this.props;
    uploadFile([companyProp, 'w9'], files[0]);
  }

  /**
   * @param files
   */
  uploadEoDoc(files) {
    const {uploadFile, companyProp} = this.props;
    uploadFile([companyProp, 'eo', 'document'], files[0]);
  }

  /**
   * Set E&O info
   * @param event SyntheticEvent
   */
  setEoValue(event) {
    const {companyProp, setProp} = this.props;
    const {target: {name, value}} = event;

    setProp(value, companyProp, 'eo', name);
  }

  /**
   * Set E&O numbers
   * @param event
   */
  @numberOnly
  setEoNumber(event) {
    this.setEoValue(event);
  }

  /**
   * Set ACH info
   * @param event
   */
  setAchValue(event) {
    const {companyProp, setProp} = this.props;
    const {target: {name, value}} = event;

    setProp(value, companyProp, 'ach', name);
  }

  render() {
    const {
      company, companyProp, errors, disabled, setProp, removeProp, creatingCompany = true, style
    } = this.props;
    // Waiting to load
    if (company.keys().next().done) {
      return <div/>;
    }

    return (
      <div style={style}>
        <div className="row">
          <div className="col-md-6">
            <VpTextField
              value={company.getIn(propPaths.name, '')}
              error={errors.getIn(propPaths.name)}
              label="Company name"
              name="name"
              placeholder="Company name"
              onChange={this.changeCompanyName}
              enterFunction={this.submitFunction}
              disabled={disabled}
              noTimeout
              required
            />
          </div>
          <div className="col-md-6">
            <VpTextField
              value={company.getIn(propPaths.taxId, '')}
              error={errors.getIn(propPaths.taxId, '')}
              name="taxId"
              label="Tax ID"
              disabled
            />
          </div>
        </div>
        <div className="row">
          <div className="col-md-6">
            <VpTextField
              value={company.getIn(propPaths.firstName, '')}
              error={errors.getIn(propPaths.firstName)}
              label="First name"
              name="firstName"
              placeholder="Company owner first name"
              onChange={this.changeFirstName}
              enterFunction={this.submitFunction}
              disabled={disabled}
              required
              noTimeout
            />
          </div>
          <div className="col-md-6">
            <VpTextField
              value={company.getIn(propPaths.lastName, '')}
              error={errors.getIn(propPaths.lastName, '')}
              label="Last name"
              name="lastName"
              placeholder="Company owner last name"
              onChange={this.changeLastName}
              enterFunction={this.submitFunction}
              disabled={disabled}
              required
              noTimeout
            />
          </div>
        </div>
        <div className="row">
          <div className="col-md-6">
            <VpTextField
              value={company.getIn(propPaths.email, '')}
              error={errors.getIn(propPaths.email)}
              label="Email"
              name="email"
              placeholder="Email"
              onChange={this.changeEmail}
              enterFunction={this.submitFunction}
              disabled={disabled}
              required
              noTimeout
            />
          </div>
          <div className="col-md-3">
            <PhoneNumber
              propPath={propPaths.phone}
              errors={errors}
              form={company}
              setProp={setProp}
              label="Phone number"
              enterFunction={this.submitFunction}
              noTimeout
              required
              prepend={companyProp}
            />
          </div>
          <div className="col-md-3">
            <PhoneNumber
              propPath={propPaths.fax}
              errors={errors}
              form={company}
              setProp={setProp}
              label="Fax number"
              enterFunction={this.submitFunction}
              noTimeout
              required
              prepend={companyProp}
            />
          </div>
        </div>
        <Address
          form={company}
          formChange={this.changeAddress}
          changeState={this.changeState}
          errors={errors}
          enterFunction={this.submitFunction}
          disabled={disabled}
          required
        />
        <div className="row">
          <div className="col-md-6">
            <VpTextField
              value={company.getIn(propPaths.assignmentZip, '')}
              error={errors.getIn(propPaths.assignmentZip, '')}
              label="Zip code for assignments"
              name="assignmentZip"
              placeholder="Zip code for assignments"
              onChange={this.changeAssignmentZip}
              enterFunction={this.submitFunction}
              disabled={disabled}
              required
            />
          </div>
          <div className="col-md-6">
            <TaxClassification
              propPath={propPaths.type}
              otherPropPath={propPaths.otherType}
              prependPath={companyProp}
              setProp={setProp}
              removeProp={removeProp}
              value={company.getIn(propPaths.type)}
              otherValue={company.getIn(propPaths.otherType)}
              otherError={errors.getIn(propPaths.otherType)}
              required
            />
          </div>
        </div>
        {creatingCompany &&
          <div className="row">
            <div className="col-md-12 text-center">
              <ActionButton
                type="submit"
                text="Download Fillable IRS W-9"
                onClick={downloadW9Form}
              />
              <MyDropzone
                refName="w9"
                onDrop={this.uploadW9}
                uploadedFiles={company.getIn(propPaths.w9) ? Immutable.List().push(company.getIn(propPaths.w9)) :
                               Immutable.List()}
                acceptedFileTypes={['ANY']}
                instructions={company.getIn(propPaths.w9) ? 'Upload new w-9' : 'Upload w-9 form'}
                hideButton={disabled}
                hideInstructions={disabled}
                error={errors.getIn(propPaths.w9)}
                required
              />
              {!!errors.getIn(propPaths.w9) && <div className={inputGroupClass(true)}>
                <p className="help-block">
                  {errors.getIn(propPaths.w9)}
                </p>
              </div>
              }
            </div>
          </div>
        }
        <CompanyEo
          company={company}
          errors={errors}
          propPaths={propPaths}
          disabled={disabled}
          uploadEoDoc={this.uploadEoDoc}
          setEoValue={this.setEoValue}
          setEoDate={this.setEoDate}
          setEoNumber={this.setEoNumber}
        />
        {creatingCompany &&
          <div className="row">
            <div className="col-md-12">
              <AchForm
                form={company.getIn(propPaths.ach)}
                formChange={this.setAchValue}
                changeDropdown={this.setAchValue}
                submit={this.submitFunction}
                errors={errors.getIn(propPaths.ach)}
                isAmc={false}
                isOptional
                validateJs={false}
              />
            </div>
          </div>
        }

        <UploadDialog
          message="Your W-9 document is uploading. When it is finished, this dialog will close automatically."
          documentUploading={this.state.documentUploading}
        />
      </div>
    );
  }
}
