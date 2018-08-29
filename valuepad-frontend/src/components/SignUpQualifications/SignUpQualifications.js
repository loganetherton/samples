import React, {Component, PropTypes} from 'react';

import {
  Void,
  LicenseType,
  MyDropzone,
  Qualifications,
  Expertise,
  VpTextField,
  VpPlainDropdown,
  VpYears,
  VpMonths,
  UploadDialog
} from 'components';

import {numberOnly} from 'helpers/genericFunctions';

import Immutable from 'immutable';

import {validateSignUpForm} from 'helpers/genericFunctions';

// All qualifications
const allQualifications = ['vaQualified', 'fhaQualified', 'relocationQualified', 'usdaQualified', 'coopQualified',
                           'jumboQualified', 'newConstructionQualified', 'loan203KQualified',
                           'manufacturedHomeQualified', 'reoQualified', 'deskReviewQualified',
                           'fieldReviewQualified', 'envCapable', 'commercialQualified'];

// Required fields
const fields = [
  ['qualifications', 'yearsLicensed'],
  ['qualifications', 'primaryLicense', 'certifications'],
  ['qualifications', 'commercialQualified'],
  ['qualifications', 'resume']
];

const acceptedFileTypes = ['ANY'];

function doNothing() {}

export default class SignUpQualifications extends Component {
  static propTypes = {
    // Set a property
    setProp: PropTypes.func.isRequired,
    // Update certification
    updateCertification: PropTypes.func.isRequired,
    // Appraiser
    appraiser: PropTypes.instanceOf(Immutable.Map).isRequired,
    // File upload
    fileUpload: PropTypes.func.isRequired,
    // Sign up form
    form: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Error display object
    errors: PropTypes.instanceOf(Immutable.Map),
    // Set next button to disabled
    setNextButtonDisabled: PropTypes.func.isRequired,
    // On profile
    profile: PropTypes.bool,
    // Enter function on profile
    enterFunctionProfile: PropTypes.func,
    // Update value
    updateValue: PropTypes.func.isRequired,
    // Disabled (customer view)
    disabled: PropTypes.bool,
    // Viewing as manager
    isManager: PropTypes.bool,
    // Prepend to create propPat
    prepend: PropTypes.string.isRequired
  };

  /**
   * PropPaths for props used in this component
   */
  propPaths = Immutable.fromJS({
    monthCertified: ['qualifications', 'certifiedAt', 'month'],
    yearCertified: ['qualifications', 'certifiedAt', 'year'],
    licenseType: ['qualifications', 'primaryLicense', 'certifications', 0],
    commercialQualified: ['qualifications', 'commercialQualified'],
    constructionCourse: ['qualifications', 'isNewConstructionCourseCompleted'],
    newConstruction: ['qualifications', 'isFamiliarWithFullScopeInNewConstruction'],
    qualifications: ['qualifications']
  });

  /**
   * Both modals hidden on load
   */
  constructor(props) {
    super(props);
    this.state = {
      showQualifications: false,
      showExpertise: false,
      documentUploading: false
    };

    this.setPropNumbers = ::this.setPropNumbers;
    this.handleUpload = ::this.handleUpload;
    this.uploadResume = props.fileUpload.bind(this, ['qualifications', 'resume']);
  }

  /**
   * Default qualifications to false
   */
  componentDidMount() {
    const {appraiser, setNextButtonDisabled, form, setProp, prepend} = this.props;
    // Default next button disabled
    setNextButtonDisabled(true);
    // Get current qualifications
    const qualifications = appraiser.getIn([prepend, 'qualifications']) || Immutable.Map();
    // Default those not set to false
    allQualifications.forEach(thisQualification => {
      if (!qualifications.has(thisQualification) && !form.getIn(['qualifications', thisQualification])) {
        setProp(false, prepend, 'qualifications', thisQualification);
      }
    });
    // Set other expertise to false
    if (!form.getIn(['qualifications', 'otherCommercialExpertise'])) {
      setProp(null, prepend, 'qualifications', 'otherCommercialExpertise');
    }
    // Validate form
    validateSignUpForm(this.props, false, fields);
  }

  /**
   * Enable next button when all required items complete
   * @param nextProps
   */
  componentWillReceiveProps(nextProps) {
    // Validate form
    validateSignUpForm(this.props, nextProps, fields);
  }

  /**
   * Set a property and accept only numbers
   * @param event Value to set it as
   */
  @numberOnly
  setPropNumbers(event) {
    const {setProp, prepend} = this.props;
    setProp(event.target.value, prepend, 'qualifications', event.target.name);
  }

  /**
   * Handle file upload
   */
  handleUpload(...args) {
    this.setState({documentUploading: true});
    this.uploadResume(...args)
    .then(() => {
      this.setState({documentUploading: false});
    });
  }

  render() {
    const {
      form,
      enterFunctionProfile,
      profile,
      setProp,
      updateValue,
      errors,
      disabled = false,
      isManager = false
      } = this.props;

    const {documentUploading = false} = this.state;
    const propPaths = this.propPaths.toJS();

    const commercialDisabled = form.getIn(['qualifications', 'primaryLicense', 'certifications', 0]) !== 'certified-general' || disabled;

    // Modal states
    return (
      <div>
        {!isManager &&
          <h3 className="no-top-spacing signup-heading text-center">Your Certifications & Qualifications</h3>
        }

        <div className="row">
          <div className="col-md-6">
            <VpTextField
              value={form.getIn(['qualifications', 'yearsLicensed']) ? form.getIn(['qualifications', 'yearsLicensed']).toString() : ''}
              label="Total Years Licensed"
              name="yearsLicensed"
              placeholder="Total Years Licensed"
              onChange={this.setPropNumbers}
              tabIndex={1}
              error={errors.getIn(['yearsLicensed'])}
              enterFunction={profile ? enterFunctionProfile : doNothing}
              disabled={disabled}
              required
            />
          </div>
          <div className="col-md-3">
            <VpMonths
              label="Month Certified"
              setProp={setProp}
              propPath={propPaths.monthCertified}
              value={form.getIn(['qualifications', 'certifiedAt', 'month'])}
              disabled={disabled}
              required
            />
          </div>
          <div className="col-md-3">
            <VpYears
              label="Year Certified"
              setProp={setProp}
              propPath={propPaths.yearCertified}
              value={form.getIn(['qualifications', 'certifiedAt', 'year'])}
              disabled={disabled}
              required
            />
          </div>
        </div>

        {/*@todo This should be a SINGLE type. Still in the backend as multiple values allowed*/}
        <div className="row first-td-checkbox">
          <div className="col-md-6">
            <LicenseType
              setProp={setProp}
              form={form}
              propPath={propPaths.licenseType}
              required
              disabled={!!profile || disabled}
            />
          </div>
          <div className="col-md-6">
            {commercialDisabled &&
              <VpTextField
                value={form.getIn(['qualifications', 'commercialQualified']) ? 'Yes' : 'No'}
                label="Commercial Qualified"
                disabled={commercialDisabled}
              />
            }
            {!commercialDisabled &&
              <VpPlainDropdown
                setProp={setProp}
                propPath={propPaths.commercialQualified}
                value={form.getIn(['qualifications', 'commercialQualified'])}
                label="Commercial Qualified"
                defaultValue="no"
                required
                disabled={commercialDisabled}
              />
            }
          </div>
        </div>

        <div className="row">
          <div className="col-md-6">
            <div>
              <Qualifications
                form={form}
                valueContainer={form.getIn(['qualifications']) || Immutable.Map()}
                setProp={setProp}
                disabled={disabled}
                propPath={propPaths.qualifications}
              />
            </div>
          </div>
          <div className="col-md-6">
            <Expertise
              updateValue={updateValue}
              selected={form.getIn(['qualifications', 'commercialExpertise']) || Immutable.List()}
              form={form}
              setProp={setProp}
              enterFunction={profile ? enterFunctionProfile : doNothing}
              errors={errors}
              disabled={disabled}
            />
          </div>
        </div>

        {/*New construction qualified questions*/}
        {form.getIn(['qualifications', 'newConstructionQualified']) &&
         <div>
           <Void pixels="15"/>
           <div className="row first-td-checkbox">
             <div className="col-md-6">
               <VpTextField
                 value={form.getIn(['qualifications', 'newConstructionExperienceInYears']) ? form.getIn(['qualifications', 'newConstructionExperienceInYears']).toString() : ''}
                 label="How many years' experience do you have completing proposed new construction appraisals?"
                 name="newConstructionExperienceInYears"
                 onChange={this.setPropNumbers}
                 tabIndex={2}
                 error={errors.getIn(['qualifications', 'newConstructionExperienceInYears'])}
                 enterFunction={profile ? enterFunctionProfile : doNothing}
                 disabled={disabled}
               />
             </div>
             <div className="col-md-6">
               <VpTextField
                 value={form.getIn(['qualifications', 'numberOfNewConstructionCompleted']) ? form.getIn(['qualifications', 'numberOfNewConstructionCompleted']).toString() : ''}
                 label="How many proposed new construction appraisals have you completed in the past 3 years?"
                 name="numberOfNewConstructionCompleted"
                 onChange={this.setPropNumbers}
                 tabIndex={3}
                 error={errors.getIn(['qualifications', 'numberOfNewConstructionCompleted'])}
                 enterFunction={profile ? enterFunctionProfile : doNothing}
                 disabled={disabled}
               />
             </div>
           </div>

           <Void pixels="15"/>
           <div className="row">
             <div className="col-md-12">
               <VpPlainDropdown
                 setProp={setProp}
                 propPath={propPaths.constructionCourse}
                 value={form.getIn(['qualifications', 'isNewConstructionCourseCompleted'])}
                 label="Have you completed a course about appraising New Construction and appraising from Blueprints, Plans and Specifications?"
                 defaultValue="no"
                 disabled={disabled}
               />
             </div>
             <div className="col-md-12">
               <VpPlainDropdown
                 setProp={setProp}
                 propPath={propPaths.newConstruction}
                 value={form.getIn(['qualifications', 'isFamiliarWithFullScopeInNewConstruction'])}
                 label="Are you familiar with the full scope of sales comparison for new construction orders?  Ex: obtaining upgrades, premiums and options for the comparable sales and adjusting accordingly?"
                 defaultValue="no"
                 disabled={disabled}
               />
             </div>
           </div>
         </div>
        }

        <Void pixels="15"/>

        <div className="row">
          <div className="col-md-12">
            <div className="text-center">
              <MyDropzone
                refName="resume"
                onDrop={this.handleUpload}
                uploadedFiles={form.getIn(['qualifications', 'resume']) ? Immutable.List().push(form.getIn(['qualifications', 'resume'])) : Immutable.List()}
                acceptedFileTypes={acceptedFileTypes}
                instructions="Upload your resume"
                required
                hideButton={disabled}
                hideInstructions={disabled}
              />
            </div>
          </div>
        </div>
        <UploadDialog
          message="Your resume document is uploading. When it is finished, this dialog will close automatically."
          documentUploading={documentUploading}
        />
      </div>
    );
  }
}
