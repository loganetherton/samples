import React, {Component, PropTypes} from 'react';
import {Dialog} from 'material-ui';

import {
  VpDateRange,
  MyDropzone,
  EoExplanation,
  VpTextField,
  VpPlainDropdown,
  UploadDialog,
  ActionButton
} from 'components';

import {numberOnly} from 'helpers/genericFunctions';

import moment from 'moment';
import Immutable from 'immutable';

import {validateSignUpForm} from 'helpers/genericFunctions';

// Required fields
const fields = [
  ['eo', 'carrier'], ['eo', 'expiresAt'], ['eo', 'claimAmount'], ['eo', 'aggregateAmount'], ['eo', 'deductible'],
  ['eo', 'document'], ['eo', 'question1'], ['eo', 'question2'], ['eo', 'question3'], ['eo', 'question4'],
  ['eo', 'question5'], ['eo', 'question6'], ['eo', 'question7'],
];

function doNothing() {}

const propPaths = {
  question1: ['signUpForm', 'eo', 'question1'],
  question2: ['signUpForm', 'eo', 'question2'],
  question3: ['signUpForm', 'eo', 'question3'],
  question4: ['signUpForm', 'eo', 'question4'],
  question5: ['signUpForm', 'eo', 'question5'],
  question6: ['signUpForm', 'eo', 'question6'],
  question7: ['signUpForm', 'eo', 'question7']
};

const acceptedFileTypes = ['ANY'];

/**
 * E&O step during appraiser sign up
 */
export default class SignUpEo extends Component {
  static propTypes = {
    // Form being interacted with
    form: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Error map
    errors: PropTypes.instanceOf(Immutable.Map),
    // Map of attributes for error display
    bsAttrs: PropTypes.object,
    // File upload function
    fileUpload: PropTypes.func.isRequired,
    // Appraiser reducer
    appraiser: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Set prop
    setProp: PropTypes.func.isRequired,
    // Set next button to disabled
    setNextButtonDisabled: PropTypes.func.isRequired,
    // Enter function on profile view
    enterFunctionProfile: PropTypes.func.isRequired,
    // If on profile view
    profile: PropTypes.bool,
    // Disabled (customer view)
    disabled: PropTypes.bool,
    // Viewing appraiser profile as manager
    isManager: PropTypes.bool,
    // Company reducer
    company: PropTypes.instanceOf(Immutable.Map),
    // Prepend value for creating prop path
    prepend: PropTypes.string.isRequired
  };

  state = {
    documentUploading: false,
    documentUploadFail: false,
    documentUploadFailMessage: null
  };

  constructor(props) {
    super(props);

    this.setEoValue = ::this.setEoValue;
    this.setEoNumber = ::this.setEoNumber;
    this.setEoDate = ::this.setEoDate;
    this.setDropdown = ::this.setDropdown;
    this.closeUpdateFailDialog = ::this.closeUpdateFailDialog;
    this.uploadEoInsuranceDoc = props.fileUpload.bind(this, ['eo', 'document']);
    this.uploadQuestion1Doc = props.fileUpload.bind(this, ['eo', 'question1Document']);
  }

  /**
   * Default next button false
   */
  componentDidMount() {
    this.errors = this.props.errors;
    // Validate form
    validateSignUpForm(this.props, false, fields);
    // Default eo expiration date
    if (!this.props.form.getIn(['eo', 'expiresAt'])) {
      this.props.setProp(moment().add(1, 'days').format(), 'signUpForm', 'eo', 'expiresAt');
    }
  }

  /**
   * Check if the next button should be disabled
   * @param nextProps
   */
  componentWillReceiveProps(nextProps) {
    const {fieldsToValidate, errors} = this.getValidationFieldsAndErrors(nextProps);
    // Validate form
    validateSignUpForm(this.props, nextProps, fieldsToValidate);
    // Set errors
    this.errors = errors;

    const {appraiser, company} = this.props;
    const {appraiser: nextAppraiser, company: nextCompany, isManager} = nextProps;

    const nextState = {};
    const reducer = isManager ? company : appraiser;
    const nextReducer = isManager ? nextCompany : nextAppraiser;

    if (!reducer.getIn(['fileUpload', 'uploading']) && nextReducer.getIn(['fileUpload', 'uploading'])) {
      nextState.documentUploading = true;
    }

    if (reducer.getIn(['fileUpload', 'uploading']) && !nextReducer.getIn(['fileUpload', 'uploading'])) {
      // Failed to upload
      if (nextReducer.getIn(['fileUpload', 'success']) === false) {
        nextState.documentUploadFail = true;
        nextState.documentUploadFailMessage = nextReducer.getIn(['fileUpload', 'error']);
      }
      nextState.documentUploading = false;
    }
    this.setState(nextState);
  }

  /**
   * Determine which fields to validate
   * @param nextProps Next props
   */
  getValidationFieldsAndErrors(nextProps) {
    const {form} = nextProps;
    let {errors} = nextProps;
    const eo = form.get('eo');
    if (!eo) {
      return {
        errors: Immutable.Map(),
        fieldsToValidate: []
      };
    }
    const questionList = [];
    const keys = eo.keySeq().toList();
    keys.forEach(key => {
      // If we have a value for this question
      const thisKey = key.match(/^question\d/);
      // Question explanation validation
      if (thisKey) {
        const validateThisKey = ['yes', true].indexOf(eo.get(thisKey[0])) !== -1;
        if (validateThisKey) {
          const explanationPath = ['eo', thisKey[0] + 'Explanation'];
          questionList.push(explanationPath);
          // Question 1 doc
          if (thisKey[0] === 'question1') {
            questionList.push(['eo', 'question1Document']);
            // Error on no document
            if (!form.getIn(['eo', 'question1Document'])) {
              errors = errors.set('question1Document', 'Supporting document required');
            }
          }
          // Set error if explanation is blank
          if (!form.getIn(explanationPath)) {
            errors = errors.set(thisKey[0] + 'Explanation', 'Explanation required');
          }
        }
      }
    });
    return {
      errors,
      fieldsToValidate: fields.concat(questionList)
    };
  }

  /**
   * Set a dropdown value
   */
  setDropdown(value, ...path) {
    this.props.setProp(value, ...path);
  }

  /**
   * Set E&O value
   * @param event
   */
  setEoValue(event) {
    const {target: {name, value}} = event;
    const {setProp, prepend} = this.props;
    setProp(value, prepend, 'eo', name);
  }

  /**
   * Set a date for E&O expires
   * @param date
   */
  setEoDate(date) {
    const {setProp, prepend} = this.props;
    setProp(date, prepend, 'eo', 'expiresAt');
  }

  /**
   * Accept input of digits and dots only
   * @param event
   */
  @numberOnly
  setEoNumber(event) {
    this.setEoValue(event);
  }

  /**
   * Upload document upload fail
   */
  closeUpdateFailDialog() {
    this.setState({
      documentUploadFail: false
    });
  }

  render() {
    const {
      form,
      // errors,
      bsAttrs,
      enterFunctionProfile,
      profile,
      disabled = false,
      isManager = false
    } = this.props;

    const {documentUploading = false, documentUploadFail = false, documentUploadFailMessage = ''} = this.state;

    const errors = this.errors || Immutable.Map();
    return (
      <div>
        {!isManager &&
         <h3 className="no-top-spacing signup-heading text-center">Your E&O Information</h3>
        }
        <div className="row">
          <div className="col-md-6">
            <VpTextField
              value={form.getIn(['eo', 'carrier'])}
              label="E&O carrier"
              name="carrier"
              placeholder="E&O carrier"
              onChange={this.setEoValue}
              tabIndex={1}
              error={errors.getIn(['carrier'])}
              enterFunction={profile ? enterFunctionProfile : doNothing}
              disabled={disabled}
              required
            />
          </div>
          <div className="col-md-6">
            <div className="col-md-12">
              <VpDateRange
                minDate={moment().add(1, 'days')}
                date={form.getIn(['eo', 'expiresAt']) ? moment(form.getIn(['eo', 'expiresAt'])) : null}
                changeHandler={this.setEoDate}
                label="E&O expiration date"
                disabled={disabled}
                required
              />
            </div>
          </div>
        </div>

        <div className="row">
          <div className="col-md-4">
            <VpTextField
              value={form.getIn(['eo', 'claimAmount'])}
              label="E&O per claim amount"
              name="claimAmount"
              placeholder="E&O per claim amount"
              onChange={this.setEoNumber}
              tabIndex={2}
              error={errors.getIn(['claimAmount'])}
              enterFunction={profile ? enterFunctionProfile : doNothing}
              disabled={disabled}
              required
            />
          </div>
          <div className="col-md-4">
            <VpTextField
              value={form.getIn(['eo', 'aggregateAmount'])}
              label="E&O aggregate amount"
              name="aggregateAmount"
              placeholder="E&O aggregate amount"
              onChange={this.setEoNumber}
              tabIndex={3}
              error={errors.getIn(['aggregateAmount'])}
              enterFunction={profile ? enterFunctionProfile : doNothing}
              disabled={disabled}
              required
            />
          </div>
          <div className="col-md-4">
            <VpTextField
              value={form.getIn(['eo', 'deductible'])}
              label="Deductible"
              name="deductible"
              placeholder="Deductible"
              onChange={this.setEoNumber}
              tabIndex={4}
              error={errors.getIn(['deductible'])}
              enterFunction={profile ? enterFunctionProfile : doNothing}
              disabled={disabled}
              required
            />
          </div>
        </div>

        <div className="row">
          <div className="col-md-12">
            <div className={`col-md-4` && bsAttrs.eoInsurance &&
            Object.keys(bsAttrs.eoInsurance).length ? 'has-error' : ''}>
              {errors.get('eoInsurance') && <div>E&O insurance document must be uploaded</div>}
              <div className="form-group">
                <MyDropzone
                  refName="eo-insurance"
                  onDrop={this.uploadEoInsuranceDoc}
                  uploadedFiles={form.getIn(['eo', 'document']) ? Immutable.List().push(form.getIn(['eo', 'document'])) : Immutable.List()}
                  acceptedFileTypes={acceptedFileTypes}
                  instructions="Upload document"
                  label={disabled ? 'E&O insurance document' : 'Upload your E&O insurance document'}
                  hideButton={disabled}
                  hideInstructions={disabled}
                  required
                />
              </div>
            </div>
          </div>
        </div>
        <UploadDialog
          message="Your E&O insurance document is uploading. When it is finished, this dialog will close automatically."
          documentUploading={documentUploading}
        />
        {/*Failed to upload*/}
        <Dialog
          open={documentUploadFail}
          actions={<ActionButton
            text="Close"
            type="cancel"
            onClick={this.closeUpdateFailDialog}
          />}
          title="Failed to upload document"
        >
          {documentUploadFailMessage}
        </Dialog>

        {!profile &&
          <div className="row">
            <div className="col-md-12">
              <VpPlainDropdown
                setProp={this.setDropdown}
                propPath={propPaths.question1}
                value={form.getIn(['eo', 'question1'])}
                label="Within the last ten (10) years, have you been the subject of any disciplinary or corrective action by an appraisal organization, state licensing board or other regulatory body of a governmental entity as a result of your appraisal activities?"
                labelClass=""
                defaultValue="no"
                required
              />
            </div>
            <EoExplanation
              form={form}
              errors={errors}
              questionNumber={1}
              text="Please explain the circumstances of the disciplinary action, the actual action taken against you or your company, and any other relevant facts."
              setEoValue={this.setEoValue}
              document
              documentInstructions="Please upload a copy of the formal action taken against you or your company as well as the final resolution judgment."
              onDrop={this.uploadQuestion1Doc}
              tabIndex={4}
            />

            <div className="col-md-12">
              <VpPlainDropdown
                setProp={this.setDropdown}
                propPath={propPaths.question2}
                value={form.getIn(['eo', 'question2'])}
                label="Have you been notified of any investigation or review open at this time by any appraisal organization, state licensing board or other regulatory body of a governmental entity?"
                defaultValue="no"
                labelClass=""
                required
              />
            </div>
            <EoExplanation
              form={form}
              errors={errors}
              questionNumber={2}
              text="Please explain the circumstances of the investigation and any other relevant facts."
              setEoValue={this.setEoValue}
              tabIndex={5}
            />

            <div className="col-md-12">
              <VpPlainDropdown
                setProp={this.setDropdown}
                propPath={propPaths.question3}
                value={form.getIn(['eo', 'question3'])}
                label="Have you ever been convicted of a felony, or arrested, indicted, or charged with felonious misconduct? If yes, please provide a written narrative of events."
                defaultValue="no"
                labelClass=""
                required
              />
            </div>
            <EoExplanation
              form={form}
              errors={errors}
              questionNumber={3}
              text="Please explain the circumstances of the charge or conviction, and any other relevant information."
              setEoValue={this.setEoValue}
              tabIndex={6}
            />

            <div className="col-md-12">
              <VpPlainDropdown
                setProp={this.setDropdown}
                propPath={propPaths.question4}
                value={form.getIn(['eo', 'question4'])}
                label="In the last ten (10) years, have any lawsuits or claims (including notice of a potential claim) been made or filed against you? This includes lawsuits or claims, regardless if they were tendered to an insurance company for coverage."
                defaultValue="no"
                labelClass=""
                required
              />
            </div>
            <EoExplanation
              form={form}
              errors={errors}
              questionNumber={4}
              text="Please explain the circumstances of the lawsuit or claim, and any other relevant information."
              setEoValue={this.setEoValue}
              tabIndex={7}
            />

            <div className="col-md-12">
              <VpPlainDropdown
                setProp={this.setDropdown}
                propPath={propPaths.question5}
                value={form.getIn(['eo', 'question5'])}
                label="Are you aware of any circumstances that may lead to the filing of a lawsuit or claim against you?"
                defaultValue="no"
                labelClass=""
                required
              />
            </div>
            <EoExplanation
              form={form}
              errors={errors}
              questionNumber={5}
              text="Please explain the circumstances of the potential claim and any other relevant facts."
              setEoValue={this.setEoValue}
              tabIndex={8}
            />

            <div className="col-md-12">
              <VpPlainDropdown
                setProp={this.setDropdown}
                propPath={propPaths.question6}
                value={form.getIn(['eo', 'question6'])}
                label="To your knowledge are you or any Appraiser associated with the firm included on any government agency, Fannie Mae, Freddie Mac or Lender Exclusionary List?"
                defaultValue="no"
                labelClass=""
                required
              />
            </div>
            <EoExplanation
              form={form}
              errors={errors}
              questionNumber={6}
              text="Please indicate the list name and give an explanation in the space provided."
              setEoValue={this.setEoValue}
              tabIndex={9}
            />

            <div className="col-md-12">
              <VpPlainDropdown
                setProp={this.setDropdown}
                propPath={propPaths.question7}
                value={form.getIn(['eo', 'question7'])}
                label="Have you had any disciplinary actions against you or your company from a state or federal licensing board as it relates to your appraisal license(s) or appraisals performed?"
                defaultValue="no"
                labelClass=""
                required
              />
            </div>
            <EoExplanation
              form={form}
              errors={errors}
              questionNumber={7}
              text="Please explain the circumstances of the disciplinary actions, and any other relevant information."
              setEoValue={this.setEoValue}
              tabIndex={10}
            />
          </div>
        }
      </div>
    );
  }
}
