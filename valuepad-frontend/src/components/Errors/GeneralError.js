import BaseError from './BaseError';
/**
 * General error
 */
export default class GeneralError extends BaseError {
  constructor(message) {
    super(message);
  }
}
