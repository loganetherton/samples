import {ErrorLogger} from '../../loggers';

class CardRegexValidator {
  /**
   * Validates a card number and its PIN
   *
   * @param {String} number
   * @param {String} pin
   * @param {String} numberRegex
   * @param {String} pinRegex
   * @return {Boolean}
   */
  validate(number = '', pin = '', numberRegex = '', pinRegex = '') {
    if (typeof numberRegex === 'string' && numberRegex.length > 0) {
      const numRegExp = this.convertToRegExp(numberRegex);

      if (! numRegExp || ! numRegExp.test(number)) {
        return false;
      }
    }

    if (typeof pinRegex === 'string' && pinRegex.length > 0) {
      const pinRegExp = this.convertToRegExp(pinRegex);

      if (! pinRegExp || ! pinRegExp.test(pin)) {
        return false;
      }
    }

    return true;
  }

  /**
   * Converts the regex string into a RegExp object
   *
   * @param {String} regex
   * @return {RegExp}
   */
  convertToRegExp(regex) {
    try {
      if (regex[0] === '/') {
        // Split the pattern and the options into two groups
        const matches = regex.match(/\/(.+)\/(.*)/);
        return new RegExp(matches[1], matches[2]);
      } else {
        // No delimiters, let's assume the entire string is the complete pattern
        return new RegExp(regex);
      }
    } catch (e) {
      if (this.logger) {
        this.logger.log(e);
      }
    }
  }

  /**
   * Set a logger object to log any errors in the regex conversion
   *
   * @param {Object} logger
   * @param {Function} logger.log
   * @return {this}
   */
  setLogger(logger) {
    this.logger = logger;
    return this;
  }
}

const validator = new CardRegexValidator();
validator.setLogger(new ErrorLogger());
export default validator;
