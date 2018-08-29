import React, {Component, PropTypes} from 'react';

import Immutable from 'immutable';

import {VpMultiselect} from 'components';

/**
 * Select from list of available language
 */
export default class Languages extends Component {

  static propTypes = {
    // Change
    changeHandler: PropTypes.func.isRequired,
    // Form
    form: PropTypes.instanceOf(Immutable.Map).isRequired,
    // Errors
    error: PropTypes.string,
    // Required field
    required: PropTypes.bool,
    // Available languages
    languages: PropTypes.instanceOf(Immutable.List).isRequired,
    // Disabled
    disabled: PropTypes.bool
  };

  /**
   * Handle change for languages
   */
  handleMultiselect(languageCode) {
    const {changeHandler} = this.props;
    changeHandler(languageCode);
  }

  render() {
    const {form, error = '', required, languages, disabled = false} = this.props;
    // Format languages
    const languagesFormatted = languages.map(language => {
      return Immutable.fromJS({
        name: language.get('name'),
        value: language.get('code')
      });
    });
    const selectedLanguages = form.get('languages') || Immutable.List();
    return (
      <div>
        <VpMultiselect
          options={languagesFormatted}
          selected={selectedLanguages}
          onClick={::this.handleMultiselect}
          label="Languages"
          error={error}
          required={required}
          disabled={disabled}
        />
      </div>
    );
  }
}
