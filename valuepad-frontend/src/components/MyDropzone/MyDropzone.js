import React, {Component, PropTypes} from 'react';

import classNames from 'classnames';

import {Link} from 'react-router';
import Dropzone from 'react-dropzone';
import Immutable from 'immutable';
import {inputGroupClass} from 'helpers/styleHelpers';

const refNameDefault = 'dropzone';
// Style for text of previously uploaded files
const textStyle = {color: '#3A87C0', cursor: 'pointer', fontSize: '1.3em'};
// Link style for non-button, text only
const linkStyle = {
  color: '#1976D2',
  cursor: 'pointer',
  textDecoration: 'none'
};

export default class MyDropzone extends Component {
  static propTypes = {
    // Name for ref on top level component
    refName: PropTypes.string,
    // On drop function
    onDrop: PropTypes.func.isRequired,
    // Multiple uploads
    multiple: PropTypes.bool,
    // Uploaded files
    uploadedFiles: PropTypes.instanceOf(Immutable.List),
    // Accepted file types
    acceptedFileTypes: PropTypes.array,
    // Instructions
    instructions: PropTypes.oneOfType([
      PropTypes.string,
      PropTypes.object
    ]),
    // Label
    label: PropTypes.string,
    // No instructions displayed
    noInstructions: PropTypes.bool,
    // message to display after file upload
    afterUploadMessage: PropTypes.oneOfType([
      PropTypes.string,
      PropTypes.object
    ]),
    // If there's an additional button required below dropzone
    additionalButton: PropTypes.object,
    // Message below uploaded image
    finalMessage: PropTypes.object,
    // Display download button
    displayDownload: PropTypes.bool,
    // Display button and uploaded doc name inline
    inline: PropTypes.bool,
    realInline: PropTypes.bool,
    // Button class
    buttonClass: PropTypes.string,
    // Hide button, show string
    hideButton: PropTypes.bool,
    // Hide instructions
    hideInstructions: PropTypes.bool,
    // Error (such as missing doc)
    error: PropTypes.string,
    // Required field
    required: PropTypes.bool,
    // Remove document
    onRemove: PropTypes.func,
    // Label class
    labelClass: PropTypes.string,
    // Disabled
    disabled: PropTypes.bool
  };

  /**
   * Initial state set class and uploads to null
   */
  constructor(props) {
    super(props);
    // Set dropzone classes
    this.state = {
      dzClass: 'dropzone needsclick dz-clickable filepicker',
      uploads: null,
      dragOver: false
    };
  }

  /**
   * On drag enter event for dropzone
   */
  onDragEnter() {
    const dropzone = this.refs[this.props.refName || refNameDefault];
    this.setState({
      dzClass: dropzone.props.className.indexOf('dz-drag-hover') !== -1 ? dropzone.props.className : dropzone.props.className + ' dz-drag-hover',
      dragOver: true
    });
  }

  /**
   * On drop leave even for dropzone
   */
  onDragLeave() {
    const dropzone = this.refs[this.props.refName || refNameDefault];
    this.setState({
      dzClass: dropzone.props.className.indexOf('dz-drag-hover') !== -1 ? dropzone.props.className.replace('dz-drag-hover') : dropzone.props.className,
      dragOver: false
    });
  }

  onDrop(...args) {
    this.props.onDrop(...args);
    this.setState({
      dragOver: false
    });
  }

  /**
   * Make sure file uploads are instances of Immutable.List
   * @param uploads File uploads
   */
  ensureUploadsAreList(uploads) {
    // Make sure we always have an array, even if uploads are stored in a single map
    if (Immutable.Map.isMap(uploads)) {
      uploads = Immutable.List().push(uploads);
    }
    return uploads;
  }

  render() {
    const {
      refName = refNameDefault,
      multiple = false,
      acceptedFileTypes = ['jpg', 'png', 'gif', 'pdf'],
      label,
      instructions = 'Drag the document here, or click to select it',
      // noInstructions,
      afterUploadMessage,
      additionalButton,
      finalMessage,
      displayDownload = true,
      inline,
      realInline,
      buttonClass,
      hideButton = false,
      hideInstructions = false,
      error,
      required = false,
      labelClass = 'control-label',
      onRemove = () => {},
      disabled = false
    } = this.props;
    const {dzClass, dragOver} = this.state;
    // Make sure we have a list here
    const uploadedFiles = this.ensureUploadsAreList(this.props.uploadedFiles);
    const fileCount = uploadedFiles.filter(file => file).count();
    // File type display message
    let fileTypeDisplay = '';
    if (acceptedFileTypes && acceptedFileTypes[0] && acceptedFileTypes[0].toLowerCase() !== 'any' && !fileCount) {
      fileTypeDisplay = `(.${acceptedFileTypes.join(', .')})`;
    }
    let content;

    const shouldDisplay = displayDownload && !!uploadedFiles.count();

    // Not displaying inline
    if (!inline) {
      content = (
        <div>
          <div
            className={`${inputGroupClass(error)} ${required ? 'required' : ''}`}
            style={{ marginTop: 0 }}>
            {!!label &&
              <div>
                <label className={labelClass}>{label}</label>
              {!!error && <p className="control-label" style={{color: '#d9534f'}}>{error}</p>}
          </div>}
          <Dropzone
            ref={refName}
            className={`dropzone-inline ${dzClass}`}
            onDragEnter={::this.onDragEnter}
            onDragLeave={::this.onDragLeave}
            onDrop={::this.onDrop}
            multiple={multiple}
          >
          <div>
            {!hideButton &&
              <div>
                <button
                className={typeof buttonClass !== 'undefined' ? buttonClass : classNames('btn btn-raised', {'btn-success': !dragOver, 'btn-danger': dragOver, 'required-button': required})}
                type="submit">
                <i className="fa fa-upload"/>
                &nbsp; {dragOver ? 'Drop to upload' : instructions} {fileTypeDisplay}
                </button>
              </div>
            }

            {hideButton && !hideInstructions &&
              <a style={linkStyle}>{instructions}</a>
            }

            <div>
              {!fileCount &&
                <div className="dz-message needsclick">
                  <span className="note needsclick"/>
                </div>
              }
              {!!fileCount &&
                <div>
                  {afterUploadMessage}
                  {finalMessage}
                </div>
              }
            </div>
          </div>

          </Dropzone>
        </div>
        {displayDownload && !!uploadedFiles.count() &&
          uploadedFiles.map((file, index) => {
            return (
              <p
                key={index}
                onClick={() => window.open(file.get('url'), '_blank')}
                style={textStyle}>
                {file.get('name')}
              </p>
            );
          })
          }
        {additionalButton}
        </div>
      );
    // Display button and uploaded doc name inline
    } else if (inline && !realInline) {
      content = (
        <div>
          {!hideButton &&
            <div className="col-md-6">
              <Dropzone
                ref={refName}
                className={`refName ${dzClass}`}
                onDragEnter={::this.onDragEnter}
                onDragLeave={::this.onDragLeave}
                onDrop={::this.onDrop}
                multiple={multiple}
              >
                <div>
                  {!hideButton &&
                    <button
                      className={classNames('btn btn-raised', {
                        'btn-success': !dragOver,
                        'btn-danger': dragOver,
                        'required-button': required
                      })}
                      type="submit"><i className="fa fa-upload"/>&nbsp; {dragOver ? 'Drop to upload' :
                                                                         instructions} {fileTypeDisplay}
                    </button>
                  }

                  <div>
                    {!fileCount && <div className="dz-message needsclick">
                      <span className="note needsclick"/>
                    </div>
                    }
                    {!!fileCount && <div>
                      {afterUploadMessage}
                      {finalMessage}
                    </div>
                    }
                  </div>
                </div>

              </Dropzone>
            </div>
          }
          <div className="col-md-6">
            {shouldDisplay &&
             uploadedFiles.map((file, index) => {
               return (
                 <p
                   key={index}
                   onClick={() => window.open(file.get('url'), '_blank')}
                   style={textStyle}>
                   {file.get('name')}
                 </p>
               );
             })
            }
          </div>
          {additionalButton}
        </div>
      );
    } else if (realInline) {
      content = (
        <div>
          <Dropzone
            ref={refName}
            className={`${dzClass} realInline`}
            onDragEnter={::this.onDragEnter}
            onDragLeave={::this.onDragLeave}
            onDrop={::this.onDrop}
            multiple={multiple}
          >
            {!hideButton &&
              <button
                className={classNames('btn btn-raised', {
                  'btn-success': !dragOver,
                  'btn-danger': dragOver,
                  'required-button': required
                })}
                type="submit"
              >
                <i className="fa fa-upload"/>
                &nbsp; {dragOver ? 'Drop to upload' : instructions} {fileTypeDisplay}
              </button>
            }
          </Dropzone>
          <span>
            {shouldDisplay &&
              uploadedFiles.map((file, index) => {
                return (
                  <span key={index}>
                    <Link to={file.get('url')} target="_blank" style={textStyle}>
                      <button className="btn btn-raised btn-info" style={{ marginLeft: '10px' }}>View Sample</button>
                    </Link>
                    {!disabled &&
                      <button className="btn btn-raised btn-danger" style={{ marginLeft: '10px' }} onClick={onRemove}>
                        Remove Sample</button>
                    }
                  </span>
                );
              })
            }
          </span>
        </div>
      );
    }

    return content;
  }
}
