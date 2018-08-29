import React, {Component, PropTypes} from 'react';
import {Dialog} from 'material-ui';

/**
 * Dialog to display while document uploading
 */
export default class UploadDialog extends Component {
  static propTypes = {
    // Message to display when uploading
    message: PropTypes.string.isRequired,
    // Document is in the process of uploading
    documentUploading: PropTypes.bool.isRequired
  };

  constructor() {
    super();
    // Uploading text dots
    this.dotCount = 0;
  }

  componentWillReceiveProps(nextProps) {
    const {documentUploading} = this.props;
    const {documentUploading: nextDocumentUploading} = nextProps;
    // Start message
    if (!documentUploading && nextDocumentUploading) {
      this.dotCount = 0;
      this.uploadText.call(this, true);
    // Hide message
    } else if (documentUploading && !documentUploading) {
      this.uploadText.call(this, false);
    }
  }

  /**
   * Create uploading text while document is being uploaded
   * @param visible Uploading text visible or hidden
   */
  uploadText(visible) {
    if (visible) {
      // Display increasing dots after the word uploading
      this.interval = setInterval(() => {
        this.dotCount = this.dotCount + 1;
        if (this.dotCount > 3) {
          this.dotCount = 0;
        }
        const dots = [];
        for (let i = 0; i < this.dotCount; i = i + 1) {
          dots.push('.');
        }
        this.uploadingText = 'Uploading' + dots.join('');
        this.replaceUploadingText();
      }, 500);
    } else {
      if (this.interval) {
        this.uploadingText = '';
        clearInterval(this.interval);
        this.replaceUploadingText();
      }
    }
  }

  /**
   * Replace the current uploading text with the new text
   */
  replaceUploadingText() {
    const textNode = document.getElementById('uploading-text');
    if (textNode) {
      const newSpan = document.createElement('span');
      newSpan.setAttribute('id', 'uploading-text');
      newSpan.innerHTML = this.uploadingText;
      newSpan.style.color = '#337ab7';
      textNode.parentNode.replaceChild(newSpan, textNode);
    }
  }

  render() {
    const {message, documentUploading} = this.props;
    return (
      <Dialog
        title="Document uploading"
        modal
        open={documentUploading}
      >
        <p>{message}</p>
        <p><span id="uploading-text" style={{color: '#337ab7'}}>Uploading</span></p>
      </Dialog>
    );
  }
}
