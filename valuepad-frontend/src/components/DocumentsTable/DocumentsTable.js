import React, {Component, PropTypes} from 'react';

import Immutable from 'immutable';
import moment from 'moment';

// Styles
const styles = {
  linkColor: {
    color: '#1976D2'
  },
  m0: { margin: 0 },
  w40: {width: '40%'},
  w34: {width: '34%'},
  w26: {width: '26%'},
};

/**
 * Display table of documents, such as orders additional documents or reconsideration docs
 */
export default class DocumentsTable extends Component {
  static propTypes = {
    // Additional docs which have been uploaded
    uploadedAdditionalDocs: PropTypes.instanceOf(Immutable.List),
    // Hide date column
    hideDate: PropTypes.bool
  };

  render() {
    const {uploadedAdditionalDocs, hideDate = false} = this.props;

    return (
      <table className="data-table" style={{ width: '100%', marginTop: '10px' }}>
        <colgroup>
          <col style={styles.w40} />
          <col style={styles.w26} />
          <col style={styles.w34} />
        </colgroup>
        <thead>
          <tr>
            <th><label className="control-label" style={styles.m0}>Document Type</label></th>
            <th><label className="control-label" style={styles.m0}>Document</label></th>
            {!hideDate && <th><label className="control-label" style={styles.m0}>Date</label></th>}
          </tr>
        </thead>
        <tbody>
          {uploadedAdditionalDocs.map((doc, index) => {
            const uploadedAt = doc.get('createdAt') ? moment(doc.get('createdAt')).format('MM-DD-YYYY h:mmA') : '';
            const docUrl = doc.getIn(['document', 'urlEncoded']) || doc.getIn(['document', 'url']);
            const type = (!doc.get('type') || doc.getIn(['type', 'title']) === 'Other') ? doc.get('label') :
                         doc.getIn(['type', 'title']);
            return (
              <tr key={index}>
                <td>{type}</td>
                <td><a href={docUrl} target="_blank" style={styles.linkColor}>{doc.getIn(['document', 'name'])}</a></td>
                {!hideDate && <td>{uploadedAt}</td>}
              </tr>
            );
          })}
        </tbody>
      </table>
    );
  }
}
