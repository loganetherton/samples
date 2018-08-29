import ErrorLog from '../api/errorLog/errorLog.model';
import * as _ from 'lodash';
import Logger from './logger.interface';

export class ErrorLogger implements Logger {
  /**
   * @param {Object} object An error to log
   * @return {this}
   */
  log(object: any): this {
    if (this.canLog(object)) {
      ErrorLog.create(_.pick(object, Object.getOwnPropertyNames(object)));
    }

    return this;
  }

  /**
   * Determine if the given object is loggable
   *
   * @param {Object} object
   * @return {Boolean}
   */
  canLog(object: any): boolean {
    if (object.stack || object.error) {
      return true;
    }

    return false;
  }
}
