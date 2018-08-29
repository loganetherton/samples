import React, {Component, PropTypes} from 'react';
import Immutable from 'immutable';

import {MyDropzone} from 'components';

/**
 * Appraiser resume upload component
 */
export default class AppraiserResume extends Component {
  static propTypes = {
    // File after upload
    uploadedFile: PropTypes.instanceOf(Immutable.Map),
    // File upload function
    fileUpload: PropTypes.func.isRequired
  };

  render() {
    const {fileUpload, uploadedFile} = this.props;
    return (
      <div>
        <h3 className="text-center">Upload A Resume</h3>
        <MyDropzone
          onDrop={fileUpload.bind(this, ['resume'])}
          uploadedFiles={uploadedFile ? Immutable.List().push(uploadedFile) : Immutable.List()}
          acceptedFileTypes={['ANY']}
          instructions="Upload Resume"
        />
      </div>
    );
  }
}
