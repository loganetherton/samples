import Logger from './logger.interface';
import ElasticLog from '../api/elasticLog/elasticLog.model';

export class ElasticLogger implements Logger {
  /**
   * @param {Object} object
   * @return {this}
   */
  log(object: any): this {
    if (this.canLog(object)) {
      // Disabling this for now because we don't need
      // Feel free to uncomment if there's an issue with ES
      // ElasticLog.create(object);
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
    return object.client && object.data;
  }
}
