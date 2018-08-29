import React, {Component, PropTypes} from 'react';
import { connect } from 'react-redux';
import {push} from 'redux-router';
import {
  AscSearch,
  HttpError,
  SignUpProfile,
  Void,
  SignUpCompany,
  SignUpEo,
  SignUpCircles,
  SignUpQualifications,
  SignUpSamples,
  SignUpTerms,
  SignUpCoverage,
  SignUpJobTypes,
  SignUpAch,
  ProfileCircles,
  ManagerSwitchAppraiser
} from 'components';
import moment from 'moment';
import _ from 'lodash';
import {
  getCcInfo,
  submitAchInfo,
  submitCcInfo,
  getAchInfo,
  setProp as setSettingsProp
} from 'redux/modules/settings';
import {
  formChange,
  searchAppraiserAsc,
  showAscResults,
  setFoundAppraiser,
  backToStepOne,
  uploadFile,
  createAppraiser,
  setProp,
  updateValue,
  getAppraiser,
  updateAppraiser,
  searchUsername,
  removeProp,
  getLanguages
} from 'redux/modules/appraiser';
import {
  login,
  removeSession,
  setProp as setAuthProp
} from 'redux/modules/auth';
import {
  // Sign up
  APPRAISER_SIGNUP_URL,
  APPRAISER_SIGNUP_PROFILE_URL,
  APPRAISER_SIGNUP_COMPANY_URL,
  APPRAISER_SIGNUP_EO_URL,
  APPRAISER_SIGNUP_CERTIFICATIONS_URL,
  APPRAISER_SIGNUP_SAMPLES_URL,
  APPRAISER_SIGNUP_LICENSES_URL,
  APPRAISER_SIGNUP_FEES_URL,
  APPRAISER_SIGNUP_ACH_URL,
  APPRAISER_SIGNUP_TERMS_URL,
  // Profile
  APPRAISER_COMPANY_CREATE,
  PROFILE_PROFILE_URL,
  PROFILE_COMPANY_URL,
  PROFILE_EO_URL,
  PROFILE_CERTIFICATIONS_URL,
  PROFILE_SAMPLES_URL,
  // Orders URL
  ORDERS_NEW_URL
} from 'redux/modules/urls';

import {
  RaisedButton,
  Dialog,
  FlatButton,
} from 'material-ui';

import {
  getFormErrorsImmutable,
  getFormBsErrorsImmutable
} from 'helpers/validation';

import {
  checkPasswordMatches
} from 'helpers/genericFunctions';

import pureRender from 'pure-render-decorator';
import Immutable from 'immutable';

/**
 * URL of maps and steps
 */
const stepUrlMap = Immutable.fromJS({
  1: APPRAISER_SIGNUP_URL,
  2: APPRAISER_SIGNUP_PROFILE_URL,
  3: APPRAISER_SIGNUP_COMPANY_URL,
  4: APPRAISER_SIGNUP_EO_URL,
  5: APPRAISER_SIGNUP_CERTIFICATIONS_URL,
  6: APPRAISER_SIGNUP_SAMPLES_URL,
  7: APPRAISER_SIGNUP_TERMS_URL,
  8: APPRAISER_SIGNUP_LICENSES_URL,
  9: APPRAISER_SIGNUP_FEES_URL,
  10: APPRAISER_SIGNUP_ACH_URL
});

/**
 * URL of maps and steps for profile
 */
const stepUrlMapProfile = Immutable.fromJS({
  2: PROFILE_PROFILE_URL,
  3: PROFILE_COMPANY_URL,
  4: PROFILE_EO_URL,
  5: PROFILE_CERTIFICATIONS_URL,
  6: PROFILE_SAMPLES_URL,
  11: APPRAISER_COMPANY_CREATE
});

const styles = {
  errorList: {listStyleType: 'none', marginBottom: '5px'}
};

@pureRender
@connect(
  state => ({
    auth: state.auth,
    appraiser: state.appraiser,
    company: state.company,
    settings: state.settings
  }),
  {
    formChange,
    searchAppraiserAsc,
    showAscResults,
    setFoundAppraiser,
    backToStepOne,
    uploadFile,
    createAppraiser,
    setProp,
    updateValue,
    getAppraiser,
    updateAppraiser,
    pushState: push,
    login,
    removeSession,
    submitAchInfo,
    searchUsername,
    removeProp,
    getLanguages,
    getCcInfo,
    submitCcInfo,
    getAchInfo,
    setSettingsProp,
    setAuthProp
  })
export default class AppraiserSignUpForm extends Component {
  static propTypes = {
    // Appraiser reducer
    appraiser: PropTypes.object.isRequired,
    // Auth
    auth: PropTypes.instanceOf(Immutable.Map),
    // Customer reducer
    customer: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Appraiser company
    company: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Validation errors
    errors: PropTypes.object,
    // Update form
    formChange: PropTypes.func.isRequired,
    // Submit form, create appraiser
    createAppraiser: PropTypes.func.isRequired,
    // Init dropdowns
    setProp: PropTypes.func.isRequired,
    // Upload file
    uploadFile: PropTypes.func.isRequired,
    // Set found appraiser from ASC search results
    setFoundAppraiser: PropTypes.func.isRequired,
    // Search ASC for an appraiser
    searchAppraiserAsc: PropTypes.func.isRequired,
    // Display ASC results when they're returned
    showAscResults: PropTypes.func.isRequired,
    // Update array of languages
    updateValue: PropTypes.func.isRequired,
    // If loading from profile
    profile: PropTypes.bool,
    // Get appraiser
    getAppraiser: PropTypes.func.isRequired,
    // Update appraiser
    updateAppraiser: PropTypes.func.isRequired,
    // Login after creation of appraiser
    login: PropTypes.func.isRequired,
    // Push state
    pushState: PropTypes.func.isRequired,
    // URL
    location: PropTypes.object.isRequired,
    // Remove user session
    removeSession: PropTypes.func.isRequired,
    // Submit ACH
    submitAchInfo: PropTypes.func.isRequired,
    // Search for username availability
    searchUsername: PropTypes.func.isRequired,
    // Remove prop
    removeProp: PropTypes.func.isRequired,
    // Get languages
    getLanguages: PropTypes.func.isRequired,
    // Get CC info
    getCcInfo: PropTypes.func.isRequired,
    // Submit CC info
    submitCcInfo: PropTypes.func.isRequired,
    // Settings reducer
    settings: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Get ACH info
    getAchInfo: PropTypes.func.isRequired,
    // Set prop for settings reducer
    setSettingsProp: PropTypes.func.isRequired,
    // Open update dialog
    openUpdateDialog: PropTypes.func,
    // Open update fail dialog
    openUpdateFailDialog: PropTypes.func,
    // Set auth prop
    setAuthProp: PropTypes.func.isRequired,
    // Disabled (customer view)
    isDisabled: PropTypes.bool,
    // Selected appraiser (company manager)
    managerSelectedAppraiser: PropTypes.instanceOf(Immutable.Map),
    // Manager viewing appraiser profile
    isManager: PropTypes.bool,
    // Update appraiser as manager
    updateAsManager: PropTypes.func.isRequired,
    // Change form values as manager
    updateAppraiserValuesAsManager: PropTypes.func.isRequired,
    // Remove prop for company reducer
    removePropCompany: PropTypes.func.isRequired,
    // Set prop company
    setPropCompany: PropTypes.func.isRequired,
    // Update an appraiser profile value directly
    updateProfileValue: PropTypes.func.isRequired,
    // Upload file as company
    uploadFileCompany: PropTypes.func.isRequired
  };

  /**
   * Init date picker
   * @param props
   */
  constructor(props) {
    super(props);
    this.state = {
      date: moment().add(1, 'days').format('MM-DD-YYYY'),
      format: 'MM-DD-YYYY',
      inputFormat: 'MM/DD/YYYY',
      mode: 'date',
      showBusinessType: false,
      // Profile starts on step 2, since there's no ASC search required
      signUpStep: this.props.profile ? 2 : 1,
      enabledStep: this.props.profile ? 2 : 1,
      nextButtonDisabled: false,
      // Disable errors when trying to change steps while validation is failing
      changeStepsWhileErrorsExist: false
    };

    this.changeStep = ::this.changeStep;
    this.changeStepProfile = ::this.changeStepProfile;
    this.selectAscAppraiser = ::this.selectAscAppraiser;
    this.setNextButtonDisabled = ::this.setNextButtonDisabled;
    this.closeChangeStepValidationDisplay = ::this.closeChangeStepValidationDisplay;
    this.updateAppraiser = ::this.updateAppraiser;
    this.fileUpload = ::this.fileUpload;
    this.resumeUpload = this.fileUpload.bind(this, 'resume');
    this.sampleReportsUpload = this.fileUpload.bind(this, 'sampleReports');
    this.updateCertification = ::this.updateCertification;
    this.updateCheckbox = ::this.updateCheckbox;
    this.managerSelectAppraiser = ::this.managerSelectAppraiser;
    this.nextButtons = {
      0: {},
      1: {label: 'Continue to profile', fn: this.nextStep.bind(this, 2)},
      2: {label: 'Continue to company information', fn: this.nextStep.bind(this, 3)},
      3: {label: 'Continue to E&O information', fn: this.nextStep.bind(this, 4)},
      4: {label: 'Continue to certifications and qualifications', fn: this.nextStep.bind(this, 5)},
      5: {label: 'Continue to sample appraisals', fn: this.nextStep.bind(this, 6)},
      6: {label: 'Continue to terms and conditions', fn: this.nextStep.bind(this, 7)},
      7: {label: 'Continue to licenses and coverage', fn: ::this.createAppraiser},
      8: {label: 'Continue to products and fees', fn: this.nextStep.bind(this, 9)},
      9: {label: 'Continue to ACH information', fn: this.nextStep.bind(this, 10)},
      10: {label: 'Finish sign up', fn: ::this.finishSignup}
    };
  }

  /**
   * Set defaults
   */
  componentDidMount() {
    const {date} = this.state;
    // Set default values on load
    const {setProp, profile, auth, getAppraiser, location, getLanguages, customer, isManager} = this.props;
    // Sign up
    if (!profile) {
      // License state
      setProp('AL', 'signUpForm', 'licenseState');
      // Assignment state
      setProp('AL', 'signUpForm', 'assignmentState');
      // Language
      setProp(Immutable.List().push('eng'), 'signUpForm', 'languages');
      // licenseExpiresAt
      setProp(date, 'signUpForm', 'licenseExpiresAt');
      // eoExpiresAt
      setProp(date, 'signUpForm', 'eoExpiresAt');
      // va
      setProp(false, 'signUpForm', 'va');
      // certifiedRelocationProfessional
      setProp(false, 'signUpForm', 'certifiedRelocationProfessional');
      // ASC not selected
      setProp(false, 'ascSelected');
      // Default company type
      setProp('individual-ssn', 'signUpForm', 'companyType');
      // Default to this year for certified at
      setProp(moment().year(), 'signUpForm', 'qualifications', 'certifiedAt', 'year');
      // Default to last month for certified at
      setProp(moment().month() + 1, 'signUpForm', 'qualifications', 'certifiedAt', 'month');
      // Modifying from profile
    } else {
      const user = auth.get('user');
      // Retrieve appraiser info for profile
      const userId = user.get('id');
      const userType = user.get('type');
      const selectedAppraiser = customer.get('selectedAppraiser');
      if (userId && !isManager) {
        if (userType === 'customer') {
          if (selectedAppraiser) {
            // Reset form
            setProp(Immutable.Map(), 'signUpForm');
            getAppraiser(selectedAppraiser);
          }
        } else {
          // Reset form
          setProp(Immutable.Map(), 'signUpForm');
          getAppraiser(userId);
        }
      }
    }
    // Update step if on profile and not loading on profile/profile
    if (profile && location.pathname !== PROFILE_PROFILE_URL) {
      this.setPathAndStep(location.pathname);
    }
    // Retrieve available languages
    getLanguages();
  }

  /**
   * Run ASC search as user types
   * @param nextProps
   */
  componentWillReceiveProps(nextProps) {
    const {auth, getAppraiser, appraiser, location: {pathname}, openUpdateDialog, openUpdateFailDialog, pushState, company} = this.props;
    const {auth: nextAuth, appraiser: nextAppraiser, company: nextCompany} = nextProps;
    const nextUserId = nextAuth.getIn(['user', 'id']);
    const nextPathname = nextProps.location.pathname;
    // Perform search for ASC license
    this.appraiserAscSearch(nextProps);
    // Perform search for username
    this.searchUsername(nextProps);
    // Authenticate this state, load profile
    if (!auth.getIn(['user', 'id']) && nextUserId) {
      getAppraiser(nextUserId);
      if (nextAuth.get('signingUp')) {
        this.nextStep(8);
      }
    }
    // Successful update appraiser
    if (typeof appraiser.get('updateAppraiserSuccess') === 'undefined' && typeof nextAppraiser.get('updateAppraiserSuccess') !== 'undefined') {
      if (nextAppraiser.get('updateAppraiserSuccess')) {
        openUpdateDialog();
      } else {
        openUpdateFailDialog(this.getValidationErrors(nextProps));
      }
    }
    // Successful update company
    if (typeof company.get('updateAppraiserProfileSuccess') === 'undefined' && typeof nextCompany.get('updateAppraiserProfileSuccess') !== 'undefined') {
      if (nextCompany.get('updateAppraiserProfileSuccess')) {
        openUpdateDialog();
      } else {
        openUpdateFailDialog(this.getValidationErrors(nextProps));
      }
    }
    // Move to step 2 if ASC is selected
    if (!appraiser.get('ascSelected') && nextAppraiser.get('ascSelected')) {
      this.setState({
        signUpStep: 2,
        enabledStep: 2
      });
      // Transition URL
      pushState(APPRAISER_SIGNUP_PROFILE_URL);
    }
    // Change paths via back button or some such
    if (pathname !== nextPathname) {
      this.setPathAndStep(nextPathname);
    }
  }

  /**
   * Get validation errors in HTML
   */
  getValidationErrors(nextProps) {
    const {appraiser, isManager, managerSelectedAppraiser, company} = nextProps;
    const form = isManager ? managerSelectedAppraiser : appraiser.get('signUpForm');
    const appraiserFormErrors = this.checkForOtherCommercialExpertiseValidation(form,
      checkPasswordMatches(isManager ? company.get('profileFormErrors') : appraiser.get('signUpFormErrors'), form)
    );

    return (
      <ul>
        {this.displayValidationErrors(appraiserFormErrors)}
      </ul>
    );
  }

  /**
   * Update path and step based on URL
   * @param pathName
   */
  setPathAndStep(pathName) {
    // Choose map of profiles
    const map = this.props.profile ? stepUrlMapProfile : stepUrlMap;
    const key = map.findKey(current => {
      return current === pathName;
    });
    // Step
    const nextStep = parseInt(key, 10);
    // Change step to keep up with URL
    if (this.state.signUpStep !== nextStep) {
      this.changeStep(nextStep);
    }
  }

  /**
   * Set next button disabled or enabled
   * @param state Boolean
   */
  setNextButtonDisabled(state) {
    this.setState({
      nextButtonDisabled: state
    });
  }

  /**
   * Search ASC for appraiser
   */
  appraiserAscSearch(nextProps) {
    const nextAppraiserForm = nextProps.appraiser.get('signUpForm');
    const nextAppraiser = nextProps.appraiser;
    const appraiser = this.props.appraiser;
    const thisAppraiserForm = appraiser.get('signUpForm');
    const thisSearch = {
      licenseState: thisAppraiserForm.get('licenseState'),
      licenseNumber: thisAppraiserForm.get('licenseNumber')
    };
    const nextSearch = {
      licenseState: nextAppraiserForm.get('licenseState'),
      licenseNumber: nextAppraiserForm.get('licenseNumber')
    };
    // If a search is being perform
    if (!_.isEqual(thisSearch, nextSearch) && !(nextAppraiser.get('ascSelected') || nextAppraiser.get('searchingAsc'))) {
      const {licenseState, licenseNumber} = nextSearch;
      // If we have values for state and license number
      if (licenseState && licenseNumber) {
        // Perform search
        nextProps.searchAppraiserAsc({licenseState, licenseNumber})
          .then(res => {
            // Display results
            nextProps.showAscResults(res.result.data);
          });
      } else {
        nextProps.showAscResults([]);
      }
    }
  }

  /**
   * See if username is available on type
   * @param nextProps
   */
  searchUsername(nextProps) {
    const {appraiser, searchUsername} = this.props;
    const minUsernameLength = 5;
    const thisUsername = appraiser.getIn(['signUpForm', 'username']);
    const nextUsername = nextProps.appraiser.getIn(['signUpForm', 'username']);
    // Delay before searching
    const searchDelay = 250;
    // Perform query on username change
    if (typeof nextUsername === 'string' &&
        nextUsername.length >= minUsernameLength &&
        thisUsername !== nextUsername &&
        !nextProps.appraiser.get('searchingUsername')) {
      // Cancel search if it's already in timeout
      if (this.searchTimeout) {
        clearTimeout(this.searchTimeout);
      }
      // Provide for cancelling on type
      this.searchTimeout = setTimeout(() => {
        searchUsername(nextUsername);
        this.searchTimeout = null;
      }, searchDelay);
    }
  }

  /**
   * File upload
   */
  fileUpload(type, files) {
    const {isManager, uploadFile, uploadFileCompany} = this.props;
    const upload = isManager ? uploadFileCompany : uploadFile;
    const promises = [];
    files.forEach(file => {
      promises.push(upload(type, file));
    });
    return Promise.all(promises);
  }

  /**
   * Select an appraiser found via ASC
   */
  selectAscAppraiser(appraiser) {
    this.props.setFoundAppraiser(appraiser);
  }

  /**
   * Update a checkbox value
   */
  updateCheckbox(name, event) {
    this.props.updateValue(name, event.target.checked);
  }

  /**
   * Remove values unused in this view
   * @param values Appraiser values
   * @param step Current step
   */
  removeUnusedThisView(values, step) {
    switch (step) {
      case 2:
        values = values.filter((value, key) => {
          return ['firstName', 'lastName', 'email', 'languages'].indexOf(key.toString()) !== -1;
        });
        break;
      case 3:
        values = values.filter((value, key) => {
          return ['companyName', 'businessTypes', 'address1', 'address2', 'city', 'state', 'zip', 'assignmentAddress1',
                  'assignmentAddress2', 'assignmentCity', 'assignmentState', 'assignmentZip', 'phone', 'cell', 'fax',
                  'companyType', 'otherCompanyType', 'taxIdentificationNumber', 'w9'].indexOf(key.toString()) !== -1;
        });
        break;
      case 4:
        const eo = values.get('eo').filter((value, key) => {
          return key.indexOf('question') === -1;
        });
        values = Immutable.Map().set('eo', eo);
        break;
      case 5:
        const qualifications = values.get('qualifications');
        values = Immutable.Map().set('qualifications', qualifications.filter(value => {
          return typeof value !== 'undefined';
        }));
        break;
      case 6:
        values = values.filter((value, key) => {
          return key.indexOf('sampleReport') !== -1;
        });
        break;
    }
    return values;
  }

  /**
   * Update appraiser
   */
  updateAppraiser() {
    const {updateAppraiser, appraiser, auth, profile, isManager, updateAsManager, managerSelectedAppraiser, company} = this.props;
    let appraiserValues = isManager ? company.get('profileSelectedAppraiser') : appraiser.get('signUpForm');
    const step = this.state.signUpStep;
    // Remove unused views during profile updating
    if (profile) {
      appraiserValues = this.removeUnusedThisView(appraiserValues, step);
    }
    // Modify before writing to the backend
    appraiserValues = this.modifyAppraiserBeforeBackend(appraiserValues, step, profile);
    // Manager updating for appraiser
    if (isManager) {
      const appraiserId = managerSelectedAppraiser.get('id');
      const companyId = company.getIn(['updateManager', 'staff', 'company', 'id']);
      updateAsManager(companyId, appraiserId, appraiserValues.toJS());
    } else {
      updateAppraiser(auth.getIn(['user', 'id']), appraiserValues.toJS());
    }
  }

  /**
   * Format E&O values
   * @param signUpValues Incoming form values
   */
  formatEo(signUpValues) {
    if (!signUpValues.get('eo')) {
      return signUpValues;
    }
    // Format eo
    const floats = ['claimAmount', 'aggregateAmount', 'deductible'];
    const dates = ['expiresAt'];
    const originalEo = signUpValues.get('eo');
    return signUpValues.set('eo', originalEo.map((value, key) => {
      // Floats
      if (floats.indexOf(key) !== -1) {
        return parseFloat(value);
      }
      // Dates
      if (dates.indexOf(key) !== -1) {
        return moment(value).format();
      }
      // Convert questions from yes/no to bool
      if (/^question\d$/.test(key)) {
        return value === 'yes' || value === true;
      }
      return value;
    }).filter((value, key) => {
      return !(typeof key === 'string' && /^question\dExplanation$/.test(key) && value === '');
    }));
  }

  /**
   * Format sample reports
   * @param signUpValues Incoming form values
   */
  formatSampleReports(signUpValues) {
    return signUpValues.set('sampleReports', signUpValues.filter((report, key) => {
      if (/sampleReport\d/.test(key)) {
        return report;
      }
    }).toList());
  }

  /**
   * Format certified at
   * @param signUpValues Incoming form values
   */
  formatQualifications(signUpValues) {
    if (!signUpValues.get('qualifications')) {
      return signUpValues;
    }
    // Pass in a single certification type
    signUpValues = signUpValues.set('qualifications', signUpValues.get('qualifications').map((qualification, key) => {
      switch (key) {
        // Int
        case 'yearsLicensed':
        case 'numberOfNewConstructionCompleted':
        case 'newConstructionExperienceInYears':
          return parseInt(qualification, 10);
        // Bool
        case 'commercialQualified':
        case 'isNewConstructionCourseCompleted':
        case 'isFamiliarWithFullScopeInNewConstruction':
          return qualification === 'yes' || qualification === true;
        // String
        case 'otherCommercialExpertise':
          return qualification ? qualification.toString() : null;
        // Remove primary license when updating profile
        case 'primaryLicense':
          return this.props.profile ? null : qualification;
      }
      return qualification;
    }).filter(qualification => typeof qualification !== 'undefined' || qualification === null));
    return signUpValues;
  }

  /**
   * Modify appraiser before sending to backend
   * @param appraiser Appraiser values
   * @param step Current step
   * @param profile If on profile (not sign up)
   */
  modifyAppraiserBeforeBackend(appraiser, step, profile) {
    // Format certified at
    appraiser = this.formatQualifications(appraiser);
    // Format E&O
    appraiser = this.formatEo(appraiser);
    // Turn sample reports into array
    if (!profile || step === 6) {
      appraiser = this.formatSampleReports(appraiser);
    }
    // Signed at
    if (!profile || step === 7) {
      return appraiser.set('signedAt', moment(appraiser.get('signedAt')).format());
    }
    return appraiser;
  }

  /**
   * Create appraiser
   */
  createAppraiser() {
    const {createAppraiser, appraiser, login} = this.props;
    let signUpValues = appraiser.get('signUpForm');
    // Modify so the backend can handle this properly
    signUpValues = this.modifyAppraiserBeforeBackend(signUpValues);
    // Create appraiser
    createAppraiser(signUpValues.toJS())
      .then((res) => {
        if (res.error) {
          throw new HttpError('Unable to create appraiser');
        }
        // Login appraiser
        login({
          username: signUpValues.get('username'),
          password: signUpValues.get('password'),
          signingUp: true
        });
      });
  }

  /**
   * Submit payment information
   */
  finishSignup() {
    this.props.setAuthProp(false, 'signingUp');
    this.props.pushState(ORDERS_NEW_URL);
  }

  /**
   * Update selected certification type
   */
  updateCertification(type) {
    // Certifications
    const certifications = this.props.appraiser.getIn(['signUpForm', 'primaryLicense', 'certifications']);
    const index = certifications.indexOf(type);
    let certificationsUpdated;
    // Not set
    if (index === -1) {
      certificationsUpdated = certifications.push(type);
    } else {
      certificationsUpdated = certifications.delete(index);
    }
    // Update selected certifications
    this.props.setProp(certificationsUpdated, 'signUpForm', 'primaryLicense', 'certifications');
  }

  /**
   * Change step
   * @param step Step to change to
   */
  nextStep(step) {
    // This step enabled
    if (this.state.enabledStep < step) {
      this.setState({enabledStep: step});
    }
    // Change step
    this.changeStep(step);
  }

  /**
   * Retrieve the next button label and function
   */
  nextButton() {
    if (this.nextButtons[this.state.signUpStep]) {
      return this.nextButtons[this.state.signUpStep];
    }

    return this.nextButtons[0];
  }

  /**
   * Change step using the circles
   */
  changeStep(step, disableSteps) {
    // Try to change steps while validation errors exists
    if (disableSteps) {
      this.setState({
        changeStepsWhileErrorsExist: true
      });
      return;
    }
    this.setState({
      signUpStep: step
    });
    const map = this.props.profile ? stepUrlMapProfile : stepUrlMap;
    // Change the URL back
    this.props.pushState(map.get(step) || map.get(step.toString()));
  }

  /**
   * Change step using the circles during profile modification
   */
  changeStepProfile(step, disableSteps) {
    // Try to change steps while validation errors exists
    if (disableSteps) {
      this.setState({
        changeStepsWhileErrorsExist: true
      });
      return;
    }
    this.setState({
      signUpStep: step
    });
    // Change the URL back
    this.props.pushState(stepUrlMapProfile.get(step) || stepUrlMapProfile.get(step.toString()));
  }

  /**
   * Display validation errors in a dialog
   */
  displayValidationErrors(appraiserFormErrors) {
    return appraiserFormErrors.toList().map((error, index) => {
      if (error) {
        return (
          <li style={styles.errorList} key={index}>
            <strong>{Immutable.Iterable.isIterable(error) ? error.get(0) : error}</strong>
          </li>
        );
      }
    });
  }

  /**
   * Close the change step validation error message
   */
  closeChangeStepValidationDisplay() {
    this.setState({
      changeStepsWhileErrorsExist: false
    });
  }

  /**
   * Check the validity of the "other" selection for commercial expertise edge case
   */
  checkForOtherCommercialExpertiseValidation(form, errors) {
    if (!form.getIn(['qualifications', 'commercialExpertise'])) {
      return errors;
    }
    // If other is selected, check for explanation
    if (form.getIn(['qualifications', 'commercialExpertise']).indexOf('other') !== -1) {
      if (!form.getIn(['qualifications', 'otherCommercialExpertise'])) {
        errors = errors.set('otherCommercialExpertise',
          Immutable.List().push('An explanation is required for other commercial expertise'));
      }
    }
    return errors;
  }

  /**
   * Manager select appraiser from dropdown
   */
  managerSelectAppraiser() {

  }

  render() {
    const {
      appraiser,
      formChange,
      getAchInfo,
      getCcInfo,
      isDisabled,
      profile,
      setProp,
      setSettingsProp,
      settings,
      submitAchInfo,
      submitCcInfo,
      updateValue,
      uploadFile,
      removeProp,
      auth,
      managerSelectedAppraiser,
      isManager = false,
      company,
      updateAppraiserValuesAsManager,
      removePropCompany,
      setPropCompany,
      updateProfileValue,
      uploadFileCompany
    } = this.props;
    // Select correct reducer
    const usedRemoveProp = isManager ? removePropCompany : removeProp;
    const usedSetProp = isManager ? setPropCompany : setProp;
    const usedUpdateValue = isManager ? updateProfileValue : updateValue;
    const usedUploadFile = isManager ? uploadFileCompany : uploadFile;
    // Which step the user is on if completing their profile
    const {signUpStep, enabledStep, nextButtonDisabled, changeStepsWhileErrorsExist} = this.state;
    const ascSelected = appraiser.get('ascSelected');
    //const {date, format, inputFormat, mode} = this.state;
    const form = isManager ? managerSelectedAppraiser : appraiser.get('signUpForm');
    // Ensure that password/confirm match, and get other errors
    const appraiserFormErrors = this.checkForOtherCommercialExpertiseValidation(form,
      checkPasswordMatches(appraiser.get('signUpFormErrors'), form)
    );
    // Disable moving between screens when an error exists
    const disableSteps = !!appraiserFormErrors.filter(error => error).toList().count();
    // Next button
    const nextButton = this.nextButton();
    // Appraiser errors
    let errors;
    if (isManager) {
      errors = getFormErrorsImmutable(company.get('profileFormErrors'));
    } else {
      errors = getFormErrorsImmutable(appraiserFormErrors);
    }
    const formUpdate = isManager ? updateAppraiserValuesAsManager : formChange;
    const bsAttrs = getFormBsErrorsImmutable(appraiserFormErrors);
    return (
      <div>
        <div className="col-md-2">
          {/*Stepper for sign up*/}
          {!profile &&
           <SignUpCircles
             signUpStep={signUpStep}
             enabledStep={enabledStep}
             changeStep={this.changeStep}
             disableSteps={disableSteps}
           />
          }
          {/*Stepper for profile*/}
          {profile &&
           <ProfileCircles
             signUpStep={signUpStep}
             enabledStep={enabledStep}
             changeStep={this.changeStepProfile}
             disableSteps={disableSteps}
           />
          }
        </div>
        <div className="col-md-10">
          {isManager &&
            <ManagerSwitchAppraiser
              selectAppraiser={this.managerSelectAppraiser}
              company={company}
              step={signUpStep}
            />
          }
          {/*Sign up step 1*/}
          {!profile && signUpStep === 1 &&
            <div>
              <h3 className="no-top-spacing signup-heading text-center">ASC License Search</h3>
              <AscSearch
                formChange={formUpdate}
                form={form}
                stateChange={usedUpdateValue}
                appraiser={appraiser}
                signUp
                ascSelected={ascSelected}
                selectFunction={this.selectAscAppraiser}
                withHeader={false}
              />
            </div>
          }
          {/*Sign up step 2*/}
          {(ascSelected || profile) && signUpStep === 2 &&
            <SignUpProfile
              appraiser={managerSelectedAppraiser || appraiser}
              form={form}
              formChange={formUpdate}
              errors={errors}
              profile={profile ? profile : false}
              setNextButtonDisabled={this.setNextButtonDisabled}
              enterFunctionProfile={this.updateAppraiser}
              updateValue={usedUpdateValue}
              languages={appraiser.get('availableLanguages')}
              disabled={isDisabled}
              isManager={isManager}
            />
          }
          {/*Sign up step 3*/}
          {signUpStep === 3 &&
           <SignUpCompany
             appraiser={appraiser}
             form={form}
             formChange={formUpdate}
             errors={errors}
             updateValue={usedUpdateValue}
             showBusinessType={this.state.showBusinessType}
             fileUpload={this.fileUpload}
             setNextButtonDisabled={this.setNextButtonDisabled}
             profile={profile ? profile : false}
             enterFunctionProfile={this.updateAppraiser}
             disabled={isDisabled}
             setProp={usedSetProp}
             isManager={isManager}
             removeProp={usedRemoveProp}
           />
          }
          {/*Sign up step 4*/}
          {signUpStep === 4 &&
           <SignUpEo
             form={form}
             errors={errors}
             bsAttrs={bsAttrs}
             fileUpload={this.fileUpload}
             appraiser={appraiser}
             company={company}
             setProp={usedSetProp}
             setNextButtonDisabled={this.setNextButtonDisabled}
             profile={profile ? profile : false}
             enterFunctionProfile={this.updateAppraiser}
             disabled={isDisabled}
             isManager={isManager}
             prepend={isManager ? 'profileSelectedAppraiser' : 'signUpForm'}
           />
          }
          {/*Qualifications and certifications*/}
          {signUpStep === 5 &&
           <SignUpQualifications
             form={form}
             errors={errors}
             bsAttrs={bsAttrs}
             fileUpload={this.fileUpload}
             appraiser={appraiser}
             setProp={usedSetProp}
             updateCertification={this.updateCertification}
             setNextButtonDisabled={this.setNextButtonDisabled}
             enterFunctionProfile={this.updateAppraiser}
             updateValue={usedUpdateValue}
             disabled={isDisabled}
             profile={profile ? profile : false}
             isManager={isManager}
             prepend={isManager ? 'profileSelectedAppraiser' : 'signUpForm'}
           />
          }
          {/*Samples*/}
          {signUpStep === 6 &&
           <SignUpSamples
             form={form}
             fileUpload={usedUploadFile}
             removeProp={usedRemoveProp}
             setNextButtonDisabled={this.setNextButtonDisabled}
             appraiser={appraiser}
             profile={profile ? profile : false}
             updateAppraiser={this.updateAppraiser}
             disabled={isDisabled}
             isManager={isManager}
           />
          }
          {/*Terms and conditions*/}
          {signUpStep === 7 &&
           <SignUpTerms
             appraiser={appraiser}
             form={form}
             updateCheckbox={this.updateCheckbox}
             errors={errors}
             setProp={setProp}
             setNextButtonDisabled={this.setNextButtonDisabled}
           />
          }
          {/*Coverage*/}
          {signUpStep === 8 &&
           <SignUpCoverage />
          }
          {/*Job types*/}
          {signUpStep === 9 &&
           <SignUpJobTypes />
          }
          {/*ACH*/}
          {signUpStep === 10 &&
           <SignUpAch
             appraiser={appraiser}
             auth={auth}
             form={form}
             errors={errors}
             submitAchInfo={submitAchInfo}
             setNextButtonDisabled={this.setNextButtonDisabled}
             getCcInfo={getCcInfo}
             submitCcInfo={submitCcInfo}
             settings={settings}
             getAchInfo={getAchInfo}
             setProp={setSettingsProp}
           />
          }
          <div>
            {/*Sign up view*/}
            {!profile && appraiser.get('ascSelected') &&
             <div className="row">
               <Void pixels={15}/>
               <div className="col-md-12 text-center">
                 <RaisedButton
                   primary
                   label={nextButton.label}
                   onClick={nextButton.fn}
                   disabled={nextButtonDisabled}
                 />
               </div>
               <Void pixels={15} clear />
             </div>
            }
            {/*Profile view in main app*/}
            {(profile && [6, 11].indexOf(signUpStep) === -1) && !isDisabled &&
             <div className="row">
               <Void pixels={15}/>
               <div className="col-md-12 text-center">
                 <RaisedButton
                   primary
                   label="Update Account"
                   onClick={this.updateAppraiser}
                 />
               </div>
             </div>
            }
          </div>
        </div>
        {/*Try to move between screens with validation errors*/}
        <Dialog
          open={changeStepsWhileErrorsExist}
          actions={<FlatButton
            label="Close"
            primary
            keyboardFocused
            onTouchTap={this.closeChangeStepValidationDisplay}
          />}
          title="Correct all errors before changing steps"
        >
          <ul>
            {this.displayValidationErrors(appraiserFormErrors)}
          </ul>
        </Dialog>
      </div>
    );
  }
}
