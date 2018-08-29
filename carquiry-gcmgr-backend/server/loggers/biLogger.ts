import * as querystring from 'querystring';

import Logger from './logger.interface';
import BiSolutionLog from '../api/bi/biSolutionLog.model';
import config from '../config/environment';

export class BiLogger implements Logger {
  /**
   * @param {Object} object
   * @return {this}
   */
  log(object: any): this {
    if (this.canLog(object)) {
      let requestBody = object.requestBody;
      let responseBody = object.responseBody;

      if (object.method === 'GET') {
        if (typeof object.requestBody === 'string') {
          requestBody = Object.assign({}, querystring.parse(object.requestBody));
        }

        if (typeof object.responseBody === 'string') {
          responseBody = Object.assign({}, querystring.parse(object.responseBody));
        }
      }

      BiSolutionLog.create({
        url: object.url,
        status: object.status,
        requestSentAt: object.requestSentAt,
        responseReceivedAt: object.responseReceivedAt,
        requestBody: requestBody,
        responseBody: responseBody
      });
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
    return (object.url && object.url.indexOf(config.bi.baseUrl) !== -1);
  }
}
