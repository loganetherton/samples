import React, {Component} from 'react';

const styles = {
  container: {marginBottom: '20px', backgroundColor: '#fff', border: '1px solid transparent', borderRadius: '6px', boxShadow: '0 1px 6px 0 rgba(0, 0, 0, .12), 0 1px 6px 0 rgba(0, 0, 0, .12)', height: '50%'}
};

/**
 * Generic text field property to replace the old MUI one
 */
export default class NoAppraiserSelected extends Component {

  render() {
    return (
      <div className="container">
        <div className="text-center" style={styles.container}>
          <h4 style={{padding: '10px 0px'}}>No appraiser selected</h4>
          <p>To select an appraiser, click "Switch Appraiser" and then type the name of the appraiser you would like to select.</p>
          <p>When a list of available appraisers appears, click the one you would like to select.</p>
        </div>
      </div>
    );
  }
}
