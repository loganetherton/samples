import React, {Component, PropTypes} from 'react';

import {MyDropzone, VpTextField} from 'components';

import Immutable from 'immutable';

/**
 * Styling
 */
const style = {
  question: {
    marginLeft: '20px'
  }
};

const acceptedFileTypes = ['ANY'];

export default class EoExplanation extends Component {
  static propTypes = {
    // EO form
    form: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Errors
    errors: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Explanation text
    text: PropTypes.string.isRequired,
    // Question number
    questionNumber: PropTypes.number.isRequired,
    // Set text value
    setEoValue: PropTypes.func.isRequired,
    // If question includes a document
    document: PropTypes.bool,
    // Document instructions
    documentInstructions: PropTypes.string,
    // Document drop function
    onDrop: PropTypes.func,
    // Tab index
    tabIndex: PropTypes.oneOfType([
      PropTypes.string,
      PropTypes.number
    ])
  };

  render() {
    const {
      form,
      errors,
      text,
      questionNumber,
      setEoValue,
      document = false,
      documentInstructions,
      onDrop,
      tabIndex = 0
    } = this.props;
    let explanation;
    // Display question
    if (form.getIn(['eo', `question${questionNumber}`]) === true || form.getIn(['eo', `question${questionNumber}`]) === 'yes') {
      explanation = (
        <div style={style.question}>
          <div className="col-md-12">
            <VpTextField
              label={text}
              value={form.getIn(['eo', `question${questionNumber}Explanation`])}
              name={`question${questionNumber}Explanation`}
              onChange={setEoValue}
              tabIndex={tabIndex}
              error={errors.get(`question${questionNumber}Explanation`)}
              multiLine
              labelClass=""
              required
            />
          </div>
          {/*Upload file to support E&O question*/}
          {document &&
           <div className="col-md-12">
             <MyDropzone
               refName="question1Document"
               onDrop={onDrop}
               uploadedFiles={form.getIn(['eo', `question${questionNumber}Document`]) ? Immutable.List().push(form.getIn(['eo', `question${questionNumber}Document`])) : Immutable.List()}
               acceptedFileTypes={acceptedFileTypes}
               instructions="Upload Document"
               label={documentInstructions}
               required
               error={errors.get('question1Document')}
               labelClass=""
             />
           </div>
          }
        </div>
      );
    } else {
      explanation = <div/>;
    }
    return explanation;
  }
}
