import React, {Component, PropTypes} from 'react';

import Immutable from 'immutable';

import {
  ActionButton,
  Void,
  Address,
  PhoneNumber,
  BusinessType,
  TaxClassification,
  MyDropzone,
  VpTextField,
  UploadDialog
} from 'components';

import {validateSignUpForm} from 'helpers/genericFunctions';

// Required fields
const fields = ['companyName', 'address1', 'city', 'state', 'zip', 'assignmentAddress1', 'assignmentCity', 'assignmentState',
                'assignmentZip', 'phone', 'cell', 'companyType', 'taxIdentificationNumber', 'w9', 'businessTypes'];

const styles = {
  businessTypeWrapper: {marginTop: '20px'}
};

const locationOverrides = {
  address1: 'assignmentAddress1',
  address2: 'assignmentAddress2',
  state: 'assignmentState',
  city: 'assignmentCity',
  zip: 'assignmentZip'
};

const propPaths = {
  phone: ['signUpForm', 'phone'],
  cell: ['signUpForm', 'cell'],
  fax: ['signUpForm', 'fax'],
  companyType: ['signUpForm', 'companyType'],
  otherCompanyType: ['signUpForm', 'otherCompanyType'],
  tin: ['signUpForm', 'taxIdentificationNumber']
};

const managerPropPaths = {
  phone: ['profileSelectedAppraiser', 'phone'],
  cell: ['profileSelectedAppraiser', 'cell'],
  fax: ['profileSelectedAppraiser', 'fax'],
  companyType: ['profileSelectedAppraiser', 'companyType'],
  otherCompanyType: ['profileSelectedAppraiser', 'otherCompanyType'],
  tin: ['profileSelectedAppraiser', 'taxIdentificationNumber']
};

const acceptedFileTypes = ['ANY'];

export default class SignUpCompany extends Component {
  static propTypes = {
    // Appraiser
    appraiser: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Sign up form
    form: PropTypes.instanceOf(Immutable.Map),
    // Change form
    formChange: PropTypes.func.isRequired,
    // Errors
    errors: PropTypes.instanceOf(Immutable.Map),
    // Update a specific value
    updateValue: PropTypes.func.isRequired,
    // Set a prop explicitly
    setProp: PropTypes.func.isRequired,
    // File upload
    fileUpload: PropTypes.func.isRequired,
    // Set next button to disabled
    setNextButtonDisabled: PropTypes.func.isRequired,
    // Profile
    profile: PropTypes.bool,
    // Enter function profile view
    enterFunctionProfile: PropTypes.func,
    // Remove prop
    removeProp: PropTypes.func.isRequired,
    // Disabled (customer view)
    disabled: PropTypes.bool,
    // Manager viewing appraiser profile
    isManager: PropTypes.bool
  };

  state = {
    documentUploading: false
  };

  constructor(props) {
    super(props);

    this.updateBusinessTypes = props.updateValue.bind(this, 'append', 'businessTypes');
    this.updateState = props.updateValue.bind(this, 'state');
    this.updateAssignmentState = props.updateValue.bind(this, 'assignmentState');
    this.uploadW9 = props.fileUpload.bind(this, 'w9');
  }

  /**
   * Disable next button by default
   */
  componentDidMount() {
    // Validate form
    validateSignUpForm(this.props, false, fields);
  }

  /**
   * Check if the next button should be disabled
   * @param nextProps
   */
  componentWillReceiveProps(nextProps) {
    const {appraiser} = this.props;
    const {appraiser: nextAppraiser} = nextProps;

    // Validate form
    validateSignUpForm(this.props, nextProps, fields);

    if (!appraiser.getIn(['fileUpload', 'uploading']) && nextAppraiser.getIn(['fileUpload', 'uploading'])) {
      this.setState({documentUploading: true});
    }

    if (!appraiser.getIn(['fileUpload', 'success']) && nextAppraiser.getIn(['fileUpload', 'success'])) {
      this.setState({documentUploading: false});
    }
  }

  downloadFillableW9() {
    window.open('https://www.irs.gov/pub/irs-pdf/fw9.pdf', '_blank');
  }

  render() {
    const {
      form,
      formChange,
      errors,
      setProp,
      enterFunctionProfile,
      appraiser,
      removeProp,
      disabled = false,
      isManager = false
    } = this.props;

    const {documentUploading = false} = this.state;

    const idText = appraiser.getIn(['signUpForm', 'companyType']) === 'individual-ssn'
      ? 'Social Security Number (SSN)'
      : 'Taxpayer Identification Number (TIN)';
    const taxClass = appraiser.getIn(['signUpForm', 'companyType']) === 'individual-ssn' ? 'ssn' : 'tin';
    // Tax classification value
    const taxClassValue = isManager ? form.get('companyType') : appraiser.getIn(propPaths.companyType);
    const otherValue = isManager ? form.get('otherCompanyType') : appraiser.getIn(propPaths.otherCompanyType);

    return (
      <div>
        {!isManager &&
          <h3 className="no-top-spacing text-center">Your Company Information</h3>
        }

        <div className="row">
          <div className="col-md-12">
            <VpTextField
              value={form.get('companyName')}
              label="Company name"
              name="companyName"
              placeholder="Company name"
              onChange={formChange}
              tabIndex={1}
              error={errors.get('companyName')}
              enterFunction={enterFunctionProfile}
              disabled={disabled}
              required
            />
          </div>
        </div>

        <div className="row">
          <div className="col-md-12" style={styles.businessTypeWrapper}>
            <BusinessType
              changeHandler={this.updateBusinessTypes}
              selected={form.get('businessTypes') || Immutable.List()}
              error={errors.get('businessTypes')}
              disabled={disabled}
            />
          </div>
        </div>

        <Void pixels={15}/>
        <h4 className="text-center">Address</h4>

        <Address
          form={form}
          formChange={formChange}
          changeState={this.updateState}
          errors={errors}
          enterFunction={enterFunctionProfile}
          tabIndexStart={2}
          disabled={disabled}
          required
        />

        <Void pixels={15}/>
        <h4 className="text-center">Address for Assignments</h4>

        <Address
          form={form}
          formChange={formChange}
          changeState={this.updateAssignmentState}
          nameOverrides={locationOverrides}
          errors={errors}
          tabIndexStart={6}
          enterFunction={enterFunctionProfile}
          disabled={disabled}
          required
        />

        <Void pixels={15}/>
        <h4 className="text-center">Contact Information</h4>

        <div className="row">
          <div className="col-md-4">
            <PhoneNumber
              form={form}
              propPath={isManager ? managerPropPaths.phone : propPaths.phone}
              setProp={setProp}
              label="Office phone number"
              errors={errors}
              tabIndex={10}
              enterFunction={enterFunctionProfile}
              disabled={disabled}
              required
            />
          </div>
          <div className="col-md-4">
            <PhoneNumber
              form={form}
              propPath={isManager ? managerPropPaths.cell : propPaths.cell}
              setProp={setProp}
              label="Cell phone number"
              errors={errors}
              tabIndex={11}
              enterFunction={enterFunctionProfile}
              disabled={disabled}
              required
            />
          </div>
          <div className="col-md-4">
            <PhoneNumber
              form={form}
              propPath={isManager ? managerPropPaths.fax : propPaths.fax}
              setProp={setProp}
              label="Fax number"
              errors={errors}
              tabIndex={12}
              enterFunction={enterFunctionProfile}
              disabled={disabled}
            />
          </div>
        </div>

        <div className="row">
          <div className="col-md-6">
            <TaxClassification
              label="Federal Tax Classification"
              propPath={isManager ? managerPropPaths.companyType : propPaths.companyType}
              otherPropPath={isManager ? managerPropPaths.otherCompanyType : propPaths.otherCompanyType}
              errors={errors}
              setProp={setProp}
              removeProp={removeProp}
              disabled={disabled}
              value={taxClassValue}
              otherValue={otherValue}
              required
            />
          </div>
          <div className="col-md-6">
            <VpTextField
              value={form.get('taxIdentificationNumber')}
              label={idText}
              placeholder={idText}
              error={errors.get('taxIdentificationNumber')}
              enterFunction={enterFunctionProfile}
              formatter={taxClass}
              disabled={disabled}
              setProp={setProp}
              propPath={isManager ? managerPropPaths.tin : propPaths.tin}
              noTimeout
              required
            />
          </div>
        </div>

        <div className="row">
          <div className="col-md-12">
            <div className="text-center">
              {!disabled &&
                <ActionButton
                  type="submit"
                  text="Download Fillable IRS W-9"
                  onClick={this.downloadFillableW9}
                />
              }
              <MyDropzone
                refName="w9"
                onDrop={this.uploadW9}
                uploadedFiles={form.get('w9') ? Immutable.List().push(form.get('w9')) : Immutable.List()}
                acceptedFileTypes={acceptedFileTypes}
                instructions={form.get('w9') ? 'Upload new w-9' : 'Upload w-9 form'}
                hideButton={disabled}
                hideInstructions={disabled}
                required
              />
            </div>
          </div>
        </div>
        <UploadDialog
          message="Your W-9 document is uploading. When it is finished, this dialog will close automatically."
          documentUploading={documentUploading}
        />
      </div>
    );
  }
}
