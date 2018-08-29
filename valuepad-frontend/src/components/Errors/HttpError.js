import BaseError from './BaseError';
/**
 * Generic http error
 */
export default class HttpError extends BaseError {
  constructor(message) {
    super(message);
  }
}
