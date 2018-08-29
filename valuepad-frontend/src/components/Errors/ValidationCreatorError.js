import BaseError from './BaseError';
/**
 * General validation definition error
 */
export default class ValidationCreatorError extends BaseError {
  constructor(message) {
    super(message);
  }
}
