import classNames from 'classnames';

/**
 * Create input group class
 * @param hasError
 */
export function inputGroupClass(hasError) {
  return classNames('form-group', {'has-error is-focused': hasError});
}
