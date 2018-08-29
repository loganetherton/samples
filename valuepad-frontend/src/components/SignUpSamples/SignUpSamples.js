import React, {Component, PropTypes} from 'react';

import {
  MyDropzone,
  UploadDialog
} from 'components';

import Immutable from 'immutable';

// Max number of sample reports
const maxSampleReports = 4;

const acceptedFileTypes = ['ANY'];

/**
 * Upload sample reports during sign up
 */
export default class SignUpSamples extends Component {
  static propTypes = {
    // Sign up form
    form: PropTypes.instanceOf(Immutable.Map).isRequired,
    // File upload function
    fileUpload: PropTypes.func.isRequired,
    removeProp: PropTypes.func.isRequired,
    // Set next button to disabled
    setNextButtonDisabled: PropTypes.func,
    // Path to remove sample
    removePath: PropTypes.array,
    // Appraiser reducer
    appraiser: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Profile
    profile: PropTypes.bool,
    // Update appraiser
    updateAppraiser: PropTypes.func,
    // Disabled (customer view)
    disabled: PropTypes.bool,
    // Viewing as manager
    isManager: PropTypes.bool
  };

  state = {
    documentUploading: false
  };

  uploadSample1 = null;
  sampleRemoved1 = null;
  uploadSample2 = null;
  sampleRemoved2 = null;
  uploadSample3 = null;
  sampleRemoved3 = null;
  uploadSample4 = null;
  sampleRemoved4 = null;

  constructor(props) {
    super(props);

    Array(maxSampleReports).fill().forEach((v, i) => {
      const idx = i + 1;
      this['uploadSample' + idx] = this.fileUpload.bind(this, ['sampleReport' + idx]);
      this['sampleRemoved' + idx] = this.fileRemoved.bind(this, 'sampleReport' + idx);
    });
  }

  /**
   * Default qualifications to false
   */
  componentDidMount() {
    this.checkDisabled.call(this, this.props);
  }

  /**
   * Enable next button when all required items complete
   * @param nextProps
   */
  componentWillReceiveProps(nextProps) {
    this.checkDisabled.call(this, nextProps);
  }

  /**
   * Check if form is disabled
   * @param nextProps Next props
   * @param forceCheck Force a check
   */
  checkDisabled(nextProps, forceCheck) {
    const {form, setNextButtonDisabled} = nextProps;
    let disabled = true;
    for (let i = 1; i <= maxSampleReports; i = i + 1) {
      const sample = form.get(`sampleReport${i}`);
      if (sample) {
        disabled = false;
      }
    }
    if ((forceCheck || !Immutable.is(this.props.form, nextProps.form) && setNextButtonDisabled)) {
      setNextButtonDisabled(disabled);
    }
  }

  /**
   * Remove a previously uploaded sample report
   * @param reportName
   */
  fileRemoved(reportName) {
    const {removePath, isManager} = this.props;
    const path = removePath ? removePath : [isManager ? 'profileSelectedAppraiser' : 'signUpForm'];
    const {profile, updateAppraiser} = this.props;
    this.setState({documentUploading: true});
    this.props.removeProp(...path, reportName).then(() => {
      if (profile) {
        updateAppraiser();
      }
      this.setState({documentUploading: false});
    });
  }

  /**
   * Uploads sample document
   *
   * @param docType
   * @param document
   */
  fileUpload(docType, document) {
    const {fileUpload, updateAppraiser, profile = false} = this.props;
    this.setState({documentUploading: true});

    fileUpload(docType, document[0]).then(result => {
      if (!result.error && profile) {
        updateAppraiser();
      }
      this.setState({documentUploading: false});
    });
  }

  render() {
    const {form, disabled = false, isManager = false} = this.props;

    const {documentUploading = false} = this.state;

    return (
      <div>
        {!isManager &&
          <h3 className="no-top-spacing signup-heading text-center">Your Sample Reports</h3>
        }

        <div className="row">
          <MyDropzone
            refName="sample-report-1"
            onDrop={this.uploadSample1}
            uploadedFiles={form.getIn(['sampleReport1']) ? Immutable.List().push(form.getIn(['sampleReport1'])) : Immutable.List()}
            acceptedFileTypes={acceptedFileTypes}
            instructions="Upload Sample"
            onRemove={this.sampleRemoved1}
            hideButton={disabled}
            hideInstructions={disabled}
            disabled={disabled}
            inline
            realInline
          />
        </div>

        <div className="row">
          <MyDropzone
            refName="sample-report-2"
            onDrop={this.uploadSample2}
            uploadedFiles={form.getIn(['sampleReport2']) ? Immutable.List().push(form.getIn(['sampleReport2'])) : Immutable.List()}
            acceptedFileTypes={acceptedFileTypes}
            instructions="Upload Sample"
            onRemove={this.sampleRemoved2}
            hideButton={disabled}
            hideInstructions={disabled}
            disabled={disabled}
            inline
            realInline
          />
        </div>

        <div className="row">
          <MyDropzone
            refName="sample-report-3"
            onDrop={this.uploadSample3}
            uploadedFiles={form.getIn(['sampleReport3']) ? Immutable.List().push(form.getIn(['sampleReport3'])) : Immutable.List()}
            acceptedFileTypes={acceptedFileTypes}
            instructions="Upload Sample"
            onRemove={this.sampleRemoved3}
            hideButton={disabled}
            hideInstructions={disabled}
            disabled={disabled}
            inline
            realInline
          />
        </div>

        <div className="row">
          <MyDropzone
            refName="sample-report-4"
            onDrop={this.uploadSample4}
            uploadedFiles={form.getIn(['sampleReport4']) ? Immutable.List().push(form.getIn(['sampleReport4'])) : Immutable.List()}
            acceptedFileTypes={acceptedFileTypes}
            instructions="Upload Sample"
            onRemove={this.sampleRemoved4}
            hideButton={disabled}
            hideInstructions={disabled}
            disabled={disabled}
            inline
            realInline
          />
        </div>

        <UploadDialog
          message="Your sample document is uploading. When it is finished, this dialog will close automatically."
          documentUploading={documentUploading}
        />
      </div>
    );
  }
}
