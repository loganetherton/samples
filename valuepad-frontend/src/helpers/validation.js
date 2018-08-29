import Immutable from 'immutable';
/**
 * Validation
 *
 * Validation should be handled as such:
 * 1) Define form fields:
 * const formFields = ['name', 'address1', 'address2', 'city', 'state', 'zip', ...];
 *
 * 2) Define validation constraints object:
 * const constraints = {};
 *
 * 3) Iterate field, ensuring that an empty constraint is set for each at the beginning of the iteration. Then
 * define specific validations on each field.
 * formField.forEach(field => {
 *  // Empty constraint
 *  constraints[field] = {};
 *  if (field === 'email') {
 *    valEmail(constraints, field);
 *  }
 *  if (field === 'confirm') {
 *    valConfirm(constraints, field);
 *  }
 *  ...
 * }
 *
 * 4) Check for validation errors in reducer. If they exist, send them back to the smart object
 * // Reducer
 * case SIGNUP_FORM_CHANGE:
 *  const signUpForm = Object.assign({}, state.signUpForm, {
 *    [action.name]: action.value
 *  });
 *  return {
 *    ...state,
 *    form,
 *    // Validate only for the field being typed on, but keep existing errors
 *    formErrors: Object.assign({}, state.formErrors, {[action.name]: (validate(form, constraints) || {})[action.name] || {}})
 *  };
 */

import validate from 'validate.js';
import moment from 'moment';
import {ValidationCreatorError} from 'components';

/**
 * Extend validate using moment
 */
validate.extend(validate.validators.datetime, {
  parse: function parseMoment(value) {
    return +moment.utc(value, 'YYYY');
  },
  // Input is a unix timestamp
  format: function formatMoment(value, options) {
    const format = options.dateOnly ? 'YYYY-MM-DD' : 'YYYY-MM-DD hh:mm:ss';
    return moment.utc(value).format(format);
  }
});

/**
 * Check a year-only date range
 */
validate.validators.checkYearRange = function(value, options) {
  const setOptions = validate.extend({}, this.options, options);
  const year = moment(value, 'YYYY');
  if (!year.isValid()) {
    return setOptions.message || this.message || 'is not a valid four digit year';
  }
  if (options.max < year || options.min > year) {
    return setOptions.message || this.message || 'cannot be outside the specified date range';
  }
};

/**
 * Check a date range (MM-DD-YYYY)
 */
validate.validators.checkDateRange = function(value, options) {
  const setOptions = validate.extend({}, this.options, options);
  const inputVal = moment(value, 'MM-DD-YYYY');
  // Between
  if (options.max && options.min && options.max < inputVal || options.min > inputVal) {
    return setOptions.message || this.message || 'cannot be outside the specified date range';
    // Less than date
  } else if (options.max && options.max < inputVal) {
    return setOptions.message || this.message || 'cannot be more than the specified date range';
  } else if (options.min && options.min > inputVal) {
    return setOptions.message || this.message || 'cannot be less than the specified date range';
  }
};

export default validate;

/**
 * Validate email
 * @param existingConstraints
 * @param field
 * @param message Complete message
 */
export function email(existingConstraints, field, message) {
  Object.assign(existingConstraints[field], {email: {message: message || 'is not a valid email'}});
}

/**
 * Validate confirmation field (such as confirm password)
 * @param existingConstraints
 * @param field
 */
export function confirm(existingConstraints, field) {
  Object.assign(existingConstraints[field], {equality: 'password'});
}

/**
 * Validate zip code
 * @param existingConstraints
 * @param field
 */
export function zip(existingConstraints, field) {
  Object.assign(existingConstraints[field], {format: {pattern: '[0-9]{5}', message: 'is not a valid 5-digit zip code'}});
}

/**
 * Validate zip code with full message
 * @param existingConstraints
 * @param field
 * @param message
 */
export function zipFullMessage(existingConstraints, field, message) {
  Object.assign(existingConstraints[field], {format: {pattern: '[0-9]{5}', message}});
}

/**
 * Validate a pattern based on a regex expression
 * @param existingConstraints
 * @param field
 * @param regex
 * @param message
 */
export function pattern(existingConstraints, field, regex, message) {
  Object.assign(existingConstraints[field], {
    format: {
      pattern: regex,
      message: message || 'is invalid'
    }
  });
}

/**
 * Validate a pattern based on a regex expression with full message
 * @param existingConstraints
 * @param field
 * @param regex
 * @param message
 */
export function patternFullMessage(existingConstraints, field, regex, message) {
  Object.assign(existingConstraints[field], {
    format: {
      pattern: regex,
      message
    }
  });
}

/**
 * Validate the presence of a field (that it has some input)
 * @param existingConstraints
 * @param field
 * @param message Message override
 */
export function presence(existingConstraints, field, message) {
  Object.assign(existingConstraints[field], {presence: {message: message || 'is required'}});
}

/**
 * Validate the length of an array
 * @param existingConstraints
 * @param field
 * @param message Message
 */
export function length(existingConstraints, field, message) {
  Object.assign(existingConstraints[field], {length: {message}});
}

/**
 * Make sure that a specific item is excluded
 * @param existingConstraints
 * @param field
 * @param valueToExclude
 * @param message
 */
export function excludeValue(existingConstraints, field, valueToExclude, message) {
  Object.assign(existingConstraints[field], {exclusion: {
    within: valueToExclude,
    message: message || '^Invalid selection'
  }});
}

/**
 * Make sure that a specific item is excluded with full message
 * @param existingConstraints
 * @param field
 * @param valueToExclude
 * @param message
 */
export function excludeValueFullMessage(existingConstraints, field, valueToExclude, message) {
  Object.assign(existingConstraints[field], {exclusion: {
    within: valueToExclude,
    message
  }});
}

/**
 * Make sure that a number within the specific range is supplied
 * @param existingConstraints
 * @param field
 * @param options Specific options for this validation
 *    Values:
 *     lessThan - Less than specified number
 *     greaterThan - Greater than specified number
 *     intOnly - Only allow integers
 * @param message
 */
export function numRange(existingConstraints, field, options, message = 'Invalid integer') {
  // Check for valid options
  if (typeof options === 'undefined' ||
      (typeof options.lessThan === 'undefined' && typeof options.greaterThan === 'undefined')) {
    throw new ValidationCreatorError('Please define a minimum or maximum integer range');
  }
  Object.assign(existingConstraints[field], {numericality: {
    onlyInteger: typeof options.intOnly !== 'undefined' ? options.intOnly : true,
    greaterThan: typeof options.greaterThan !== 'undefined' ? options.greaterThan : 0,
    lessThan: typeof options.lessThan !== 'undefined' ? options.lessThan : 10000000000000,
    message
  }});
}

/**
 * Validate against a range in years
 * @param existingConstraints
 * @param field
 * @param minYears The number of years in the past allowable
 * @param maxYears The number of years in the future allowable
 */
export function yearRange(existingConstraints, field, minYears, maxYears) {
  Object.assign(existingConstraints[field], {
    checkYearRange: {
      dateOnly: true,
      min: moment.utc().subtract(minYears, 'years'),
      max: moment.utc().add(maxYears, 'years'),
      message: `^Date must be between ${minYears} years ago and ${maxYears} years in the future`
    }
  });
}

/**
 * Validate against a specific date range
 * @param existingConstraints
 * @param field
 * @param min Min units of time allowable in range
 * @param max Max units of time allowable in range
 * @param unit Unit acceptable by moment.js (years, etc...)
 */
export function dateRange(existingConstraints, field, min, max, unit = 'years') {
  const minDate = typeof min === 'number' ? moment.utc().subtract(min, unit) : null;
  const maxDate = typeof max === 'number' ? moment.utc().add(max, unit) : null;
  let message = `^Date must be `;
  if (minDate && maxDate) {
    message += `between ${minDate.format('MM-DD-YYYY')} and ${maxDate.format('MM-DD-YYYY')}`;
  } else if (minDate) {
    message += `greater than ${minDate.format('MM-DD-YYYY')}`;
  } else if (maxDate) {
    message += `less than ${maxDate.format('MM-DD-YYYY')}`;
  } else {
    message += 'valid';
  }
  Object.assign(existingConstraints[field], {
    checkDateRange: {
      dateOnly: true,
      min: minDate,
      max: maxDate,
      message
    }
  });
}

/**
 * Get backend errors, and display only if there are no frontend validation errors
 * @param action
 * @returns {*}
 */
export function backendErrorsImmutable(action) {
  const errors = {};
  for (const [key, err] of Object.entries(action.error.errors)) {
    if (err && key) {
      errors[key] = [err.message];
    }
  }
  return Immutable.fromJS(errors);
}

/**
 * Get frontend validation errors for the element being entered
 * @param state
 * @param action
 * @param errorsObject Object of existing errors
 * @param form Form undergoing validation
 * @param constraints Validation constraints
 */
export function frontendErrorsImmutable(state, action, errorsObject, form, constraints) {
  const name = Array.isArray(action.name) ? action.name[action.name.length - 1] : action.name;
  // Make sure to have the value in plain JS
  const value = Immutable.Iterable.isIterable(form.get(name)) ? form.get(name).toJS() : form.get(name);
  return state.get(errorsObject)
    .merge({[name]: (validate({[name]: value}, constraints) || {})[name] || ''});
}

/**
 * Copy of the original frontend immutable validation, just modified until I can update the rest of the app
 * @param state
 * @param action
 * @param errorsObject Object of existing errors
 * @param form Form undergoing validation
 * @param constraints Validation constraints
 */
export function profileErrors(state, action, errorsObject, form, constraints) {
  let name;
  if (Array.isArray(action.name)) {
    if (action.name.length > 1) {
      action.name.shift();
      name = action.name;
    } else {
      name = action.name;
    }
  } else {
    name = [action.name];
  }
  // Name used for validation
  const validateName = name[name.length - 1];

  // Make sure to have the value in plain JS
  const value = Immutable.Iterable.isIterable(form.getIn(name)) ? form.getIn(name).toJS() : form.getIn(name);
  return state.get(errorsObject)
    .merge({[validateName]: (validate({[validateName]: value}, constraints, {
      fullMessages: false
    }) || {})[validateName] || ''});
}

/**
 * A smarter way to validate for frontend errors
 * @param state State after the most recent value has been set
 * @param action Input action
 * @param constraints Constraints (nested objects separated by colon)
 */
export function frontendErrors(state, action, constraints) {
  const name = Array.isArray(action.name) ? action.name : [action.name];
  // Get this set value
  const value = state.getIn(name);
  // Join name name along colon
  const joinedName = action.name.join(':');
  // Perform validation on just this single input value
  const validationResult = validate({[joinedName]: value}, constraints);
  // Get just this validation result
  const validationReturn = validationResult ? validationResult[joinedName] : null;
  // Set it into the errors object
  return state.setIn(['errors'].concat(name), validationReturn);
}

/**
 * Format a date for passing to the backend
 */
export function backendFormatDate(form, key, val) {
  form[key] = moment.utc(val).format();
}

/**
 * Get validation errors for display
 */
export function getFormErrorsImmutable(formErrors) {
  let signUpDisplayErrors = Immutable.Map();
  // Get individual errors
  formErrors.forEach((error, attribute) => {
    if (attribute && error && Immutable.List.isList(error)) {
      signUpDisplayErrors = signUpDisplayErrors.set(attribute, error.get(0));
    }
  });
  return signUpDisplayErrors;
}

/**
 * Create sign up input BS styles
 */
export function getFormBsErrorsImmutable(formErrors) {
  let signUpDisplayErrors = Immutable.Map();
  formErrors.forEach((error, attribute) => {
    if (attribute) {
      // Set error display
      if (error && Immutable.List.isList(error)) {
        signUpDisplayErrors = signUpDisplayErrors.set(attribute, Immutable.Map().set('bsStyle', 'error'));
      } else {
        // Default to no error
        signUpDisplayErrors = signUpDisplayErrors.set(attribute, Immutable.Map());
      }
    }
  });
  return signUpDisplayErrors.toJS();
}

/**
 * Set a validation based on a specific length being exceeded (ie, don't display validation error for "must be 5 characters" until 5 characters are passed the first time
 * @param state State
 * @param action Action
 * @param actionName Name sought
 * @param length Length that must be passed
 * @param validationFunction Bound validation function
 * @returns {*}
 */
export function setConditionalValidation(state, action, actionName, length, validationFunction) {
  if (action.name === actionName && action.value.length === length && !state.getIn(['validationSet', actionName])) {
    state = state.setIn(['validationSet', 'username'], true);
    validationFunction();
  }
  return state;
}

/**
 * Apply conditional validation for username and password
 * @param state
 * @param action
 * @param constraints Existing validation constraints
 * @returns {*}
 */
export function validateUsernamePasswordAfterType(state, action, constraints) {
  // Validate username after 5 characters passed
  state = setConditionalValidation(state, action, 'username', 5,
    pattern.bind(null, constraints, 'username', /[a-zA-Z@._0-9\-]{5,50}/,
      'can only contain letters and must be between 5 and 50 characters'));
  // Validate password after 5 characters passed
  state = setConditionalValidation(state, action, 'password', 5,
    pattern.bind(null, constraints, 'password', /^[0-9a-zA-Z `~!@#\$%\^&\*\(\)_\+=-\?/\|\.,'"<>\{\}\[\]:;]{5,255}$/,
      'must be at least 5 characters'));
  return state;
}
