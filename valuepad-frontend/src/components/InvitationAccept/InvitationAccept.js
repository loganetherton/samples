import React, {Component, PropTypes} from 'react';
import {
  ActionButton,
  JobTypesTable,
  AchForm,
  SignUpSamples,
  AppraiserResume
} from 'components';

import {createFeeMap} from 'redux/modules/jobType';
import {Dialog} from 'material-ui';
import Immutable from 'immutable';

// job types style
const jobTypeStyle = {
  width: '100%',
  maxWidth: 'none'
};

// Sample reports modal style
const sampleReportsStyle = {
  width: '75%',
  maxWidth: 'none'
};

// Error text style
const errorStyle = {fontSize: '1.5em'};

// Step values
const stepValues = {
  requirements: {
    step: 1,
    name: 'requirements',
    displayName: 'Requirements'
  },
  ach: {
    step: 2,
    name: 'ach',
    displayName: 'Complete ACH information'
  },
  'sample-reports': {
    step: 3,
    name: 'sample-reports',
    displayName: 'Upload sample reports'
  },
  resume: {
    step: 4,
    name: 'resume',
    displayName: 'Upload a resume'
  },
  jobTypes: {
    step: 5,
    name: 'jobTypes',
    displayName: 'Job Types'
  },
  accept: {
    step: 6,
    name: 'accept',
    displayName: 'Accept'
  }
};

/**
 * Steps for invitation acceptance
 */
const stepMap = Immutable.Map()
  .set(stepValues.requirements.step, stepValues.requirements.name)
  .set(stepValues.ach.step, stepValues.ach.name)
  .set(stepValues['sample-reports'].step, stepValues['sample-reports'].name)
  .set(stepValues.resume.step, stepValues.resume.name)
  .set(stepValues.jobTypes.step, stepValues.jobTypes.name)
  .set(stepValues.accept.step, stepValues.accept.name);

/**
 * Create inverse step map for referencing steps by name
 */
let inverseStepMap = Immutable.Map();
// Create inverse step map
stepMap.forEach((step, key) => {
  inverseStepMap = inverseStepMap.set(step, key);
});

const styles = {
  dialogBody: {padding: '24px'},
  actionButton: {marginLeft: '10px'},
  requirement: {listStyleType: 'none', marginBottom: '5px'}
};

const sampleReportPath = ['sampleReports', 'form'];

export default class InvitationAccept extends Component {
  static propTypes = {
    // Requirements for this invitations
    requirements: PropTypes.instanceOf(Immutable.List).isRequired,
    // Requirements this appraiser already meets
    metRequirements: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Selected invitation
    selectedInvitation: PropTypes.instanceOf(Immutable.Map),
    // Select job type
    selectJobType: PropTypes.func.isRequired,
    // Show dialog
    show: PropTypes.bool.isRequired,
    // Hide dialog
    hide: PropTypes.func.isRequired,
    // Save job type fees
    saveJobTypeFees: PropTypes.func.isRequired,
    // Invitations reducer
    invitations: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Set fee value for job type
    setFeeValue: PropTypes.func.isRequired,
    // Set prop
    setProp: PropTypes.func.isRequired,
    // Submit ACH
    submitAch: PropTypes.func.isRequired,
    // Upload file (either sample report or resume)
    uploadFile: PropTypes.func.isRequired,
    // Update appraiser
    updateAppraiser: PropTypes.func.isRequired,
    // Accept invitation
    acceptInvitation: PropTypes.func.isRequired,
    // Apply default fees
    applyDefaultFees: PropTypes.func.isRequired,
    // Remove prop (for samples)
    removeProp: PropTypes.func.isRequired,
    // Change job type filter
    changeSearchValue: PropTypes.func.isRequired,
    // Sort job type columns
    sortColumn: PropTypes.func.isRequired,
    // Appraiser reducer
    appraiser: PropTypes.instanceOf(Immutable.Map).isRequired
  };

  /**
   * Init step
   */
  constructor(props) {
    super(props);
    // Start on requirements
    this.state = {
      step: stepValues.requirements.step,
      steps: []
    };

    this.getPastSampleUpload = ::this.getPastSampleUpload;
    this.submitResume = props.updateAppraiser.bind(this, 'resume', this.triggerSampleError.bind(this, 'resume'));
    this.saveJobTypeFees = props.saveJobTypeFees.bind(this, this.triggerSampleError.bind(this, 'jobTypesError'));
    this.nextStep = ::this.nextStep;
    this.uploadResume = ::this.uploadResume;
    this.updateAppraiserSampleReports = ::this.updateAppraiserSampleReports;
    this.uploadSampleReport = ::this.uploadSampleReport;
  }

  /**
   * Determine steps on mount
   */
  componentDidMount() {
    // Determine which steps to show
    this.determineSteps.call(this, this.props);
  }

  /**
   * Check to see which step we should really be on
   */
  componentWillReceiveProps(nextProps) {
    const {invitations} = this.props;
    const {invitations: nextInvitations} = nextProps;
    const {step, steps} = this.state;
    // Determine which steps to show
    this.determineSteps.call(this, nextProps);
    const indexCurrentStep = steps.indexOf(step);
    // Update ACH
    if ((!invitations.get('submitAchSuccess') && nextInvitations.get('submitAchSuccess')) ||
        // Update resume/sample reports
        (!invitations.get('updateAppraiserSuccess') && nextInvitations.get('updateAppraiserSuccess') && this.state.step !== 3) ||
        // Job types
        (!invitations.get('saveJobTypesSuccess') && nextInvitations.get('saveJobTypesSuccess'))
    ) {
      let nextStep = steps[indexCurrentStep + 1];
      // If there are no job types for this customer, skip job types
      if (nextStep === stepValues.jobTypes.step && !nextInvitations.get('customerJobTypes').count()) {
        nextStep = steps[steps.indexOf(nextStep) + 1];
      }
      this.setState({
        step: nextStep
      });
    }
  }

  /**
   * Revert to step 1
   */
  componentWillUnmount() {
    this.setState({
      step: 1
    });
  }

  /**
   * Trigger a sample error
   */
  triggerSampleError(type, path) {
    if (type === 'jobTypesError') {
      this.props.setProp(true, ...path);
    } else {
      this.props.setProp(true, type, 'errors', 'noData');
    }
  }

  /**
   * Determine which steps this appraiser needs to take
   * @param props Currently set of props
   */
  determineSteps(props) {
    const {requirements, metRequirements, invitations, selectedInvitation} = props;
    // Always show job types and confirm screen
    let steps = [stepValues.jobTypes.step, stepValues.accept.step];

    // Don't show the job type form if it's a company invitation
    if (selectedInvitation.get('branch')) {
      steps.shift();
    }

    // Determines whether or not the user has unmet requirements
    const initialSteps = steps.length;

    requirements.forEach(requirement => {
      if (!metRequirements.get(requirement)) {
        steps.push(inverseStepMap.get(requirement));
      }
    });

    if (steps.length > initialSteps) {
      // Requirements screen
      steps.push(stepValues.requirements.step);
    }
    // See if there's any job types
    const originalCustomerJobTypes = invitations.get('originalCustomerJobTypes').count();
    const customerJobTypes = invitations.get('customerJobTypes').count();
    // Job types queries, no job types exist
    if (typeof invitations.get('getCustomerJobTypesSuccess') !== 'undefined' &&
        !customerJobTypes && !originalCustomerJobTypes) {
      // On job types
      if (this.state.step === stepValues.jobTypes.step) {
        this.setState({
          step: stepValues.accept.step
        });
      }
    }
    // Sort steps
    steps = steps.sort();
    // Keep steps in state
    if (steps !== this.state.steps) {
      this.setState({
        steps
      });
    }
    // Skip requirements if none exist
    if (steps.indexOf(stepValues.requirements.step) === -1 && this.state.step === stepValues.requirements.step ||
        (this.props.selectedInvitation.get('id')) !== selectedInvitation.get('id')) {
      this.setState({
        step: steps[0]
      });
    }
  }

  /**
   * Go to the next step
   */
  nextStep() {
    const {step, steps} = this.state;
    const indexOfCurrentStep = steps.indexOf(step);
    this.setState({
      step: steps[indexOfCurrentStep + 1]
    });
  }

  /**
   * Invitation requirements screen
   */
  requirements() {
    const steps = this.state.steps;
    const stepList = steps.map(thisStep => {
      if (thisStep > 1 && thisStep < 5) {
        return stepValues[stepMap.get(thisStep)].displayName;
      }
    }).filter(step => step);
    return (
      <div>
        <p>You must meet the following requirements before you can accept this invitation:</p>
        <ul>
          {stepList.map((step, index) => <li key={index} style={styles.requirement}><strong>{step}</strong></li>)}
        </ul>
      </div>
    );
  }

  /**
   * Change form
   * @param propPath Property path before name
   * @param event
   */
  formChange(propPath, event) {
    const {name, value} = event.target;
    propPath = Array.isArray(propPath) ? propPath : [propPath];
    this.props.setProp(value, ...propPath, name);
  }

  /**
   * ACH
   */
  ach() {
    const {invitations, submitAch} = this.props;
    return (
      <AchForm
        form={invitations.getIn(['ach', 'form'])}
        formChange={this.formChange.bind(this, ['ach', 'form'])}
        changeDropdown={this.formChange.bind(this, ['ach', 'form'])}
        submit={submitAch}
        errors={invitations.getIn(['ach', 'errors'])}
        showHeader
      />
    );
  }

  /**
   * Upload a sample report
   *
   * @param type
   * @param file
   * @return Promise
   */
  uploadSampleReport(type, file) {
    const {uploadFile} = this.props;

    return uploadFile(type, file);
  }

  /**
   * Upload a resume
   *
   * @param type
   * @param files
   */
  uploadResume(type, files) {
    files.forEach(file => {
      this.props.uploadFile(type, file);
    });
  }

  /**
   * Updates appraiser sample reports
   */
  updateAppraiserSampleReports() {
    const {updateAppraiser} = this.props;

    updateAppraiser('sample-reports', this.triggerSampleError.bind(this, 'sampleReports'));
  }

  /**
   * Sample reports
   */
  sampleReports() {
    const {invitations, removeProp, appraiser} = this.props;
    return (
      <SignUpSamples
        form={invitations.getIn(sampleReportPath)}
        fileUpload={this.uploadSampleReport}
        removeProp={removeProp}
        removePath={sampleReportPath}
        appraiser={appraiser}
        updateAppraiser={this.updateAppraiserSampleReports}
        profile
      />
    );
  }

  /**
   * Resume
   */
  resume() {
    const {invitations} = this.props;
    return (
      <div className="text-center">
        <AppraiserResume
          uploadedFile={invitations.getIn(['resume', 'resume'])}
          fileUpload={this.uploadResume}
        />
      </div>
    );
  }

  /**
   * Accept invitation
   */
  accept() {
    return <div>You've met all requirements, would you like to accept the invitation?</div>;
  }

  /**
   * Display job types
   */
  jobTypes() {
    const {
      invitations,
      selectJobType,
      setFeeValue,
      selectedInvitation,
      changeSearchValue,
      sortColumn
    } = this.props;
    return (
      <JobTypesTable
        selectedCustomer={selectedInvitation.getIn(['customer', 'id'])}
        fees={invitations.get('fees')}
        jobTypes={invitations.get('customerJobTypes')}
        defaultJobTypes={invitations.get('jobTypes')}
        handleRowSelect={selectJobType}
        setFeeValue={setFeeValue}
        showHeader
        canEditFees
        jobType={invitations}
        createFeeMap={createFeeMap}
        changeSearchValue={changeSearchValue}
        sortColumn={sortColumn}
      />
    );
  }

  /**
   * Get modal style
   */
  dialogStyle() {
    switch (this.state.step) {
      case stepValues.jobTypes.step:
        return jobTypeStyle;
      case stepValues['sample-reports'].step:
        return sampleReportsStyle;
    }
  }

  /**
   * Get an error message for invite step
   * @param message
   */
  dialogErrorMessage(message) {
    return (
      <div className="text-center has-error">
        <p className="help-block" style={errorStyle}>{message}</p>
      </div>
    );
  }

  /**
   * Get any error text
   * @returns {XML}
   */
  dialogError() {
    const {invitations} = this.props;
    switch (this.state.step) {
      // Sample reports step
      case stepValues['sample-reports'].step:
        // No samples uploaded error
        if (invitations.getIn(['sampleReports', 'errors', 'noData'])) {
          return this.dialogErrorMessage('You must upload a sample report before continuing.');
        }
        break;
      // Resume step
      case stepValues.resume.step:
        // No samples uploaded error
        if (invitations.getIn(['resume', 'errors', 'noData'])) {
          return this.dialogErrorMessage('You must upload a resume before continuing.');
        }
        break;
      // Job types step
      case stepValues.jobTypes.step:
        // No fees set error
        if (invitations.get('jobTypesErrorNoFees')) {
          return this.dialogErrorMessage('You need to select at least one form before moving on.');
        // Incorrect data error
        } else if (invitations.get('jobTypesError')) {
          return this.dialogErrorMessage('You must correct all errors before continuing.');
        }
        break;
    }
  }

  /**
   * Performs additional check before moving past the sample report dialog
   */
  getPastSampleUpload() {
    const {invitations} = this.props;

    if (!invitations.getIn(sampleReportPath).count()) {
      this.triggerSampleError('sampleReports');
    } else {
      this.nextStep();
    }
  }

  /**
   * Get button actions
   */
  nextButtonActions() {
    const {hide, submitAch, acceptInvitation, applyDefaultFees, invitations} = this.props;
    const step = this.state.step;
    let submitFunction = this.nextStep;
    let nextButtonText = 'Next';
    // Determine submit functionality
    switch (step) {
      // ACH
      case stepValues.ach.step:
        submitFunction = submitAch;
        break;
      // Sample reports
      case stepValues['sample-reports'].step:
        submitFunction = this.getPastSampleUpload;
        break;
      // Resume
      case stepValues.resume.step:
        submitFunction = this.submitResume;
        break;
      // Job type
      case stepValues.jobTypes.step:
        submitFunction = this.saveJobTypeFees;
        break;
      // Accept
      case stepValues.accept.step:
        submitFunction = acceptInvitation;
        nextButtonText = 'Accept';
        break;
    }
    // Buttons
    const cancelButton = (
      <ActionButton
        type="cancel"
        text={'Cancel'}
        onClick={hide}
      />
    );
    const submitButton = (
      <ActionButton
        type="submit"
        text={nextButtonText}
        onClick={submitFunction}
        style={styles.actionButton}
        disabled={invitations.get('savingJobTypes') || invitations.get('updatingAppraiser')}
      />
    );
    // Apply default fees for job types
    const applyDefaultsButton = (
      <ActionButton
        type="reset"
        text="Apply Defaults"
        onClick={applyDefaultFees}
        style={styles.actionButton}
      />
    );
    const buttons = [cancelButton, submitButton];
    return step === stepValues.jobTypes.step ? buttons.concat(applyDefaultsButton) : buttons;
  }

  /**
   * Handle the creation of the dialog body using other components
   */
  dialogBody() {
    switch (this.state.step) {
      // ACH
      case stepValues.ach.step:
        return this.ach.call(this);
      // Sample reports
      case stepValues['sample-reports'].step:
        return this.sampleReports.call(this);
      // Resume
      case stepValues.resume.step:
        return this.resume.call(this);
      // Job types
      case stepValues.jobTypes.step:
        return this.jobTypes.call(this);
      // Accept
      case stepValues.accept.step:
        return this.accept.call(this);
      // Default to requirements
      default:
        return this.requirements.call(this);
    }
  }

  render() {
    const {
      show,
      hide,
      selectedInvitation
    } = this.props;
    // No selected invitation, don't proceed
    if (!selectedInvitation) {
      return <div />;
    }
    // Get modal style
    const style = this.dialogStyle.call(this);
    return (
      <Dialog
        open={show}
        onRequestClose={hide}
        actions={this.nextButtonActions()}
        modal
        title="Accept Invitation"
        contentStyle={style}
        bodyStyle={styles.dialogBody}
        autoScrollBodyContent
      >
        {this.dialogBody.call(this)}
        {this.dialogError.call(this)}
      </Dialog>
    );
  }
}
