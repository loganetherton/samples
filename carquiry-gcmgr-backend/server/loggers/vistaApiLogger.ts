import Logger from './logger.interface';
import VistaLog from '../api/vista/vistaLog.model';
import config from '../config/environment';
import * as querystring from 'querystring';

export class VistaApiLogger implements Logger {
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

      VistaLog.create({
        method: object.method,
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
    if (object.url && object.url.indexOf(config.vista.baseUrl) !== -1) {
      return true;
    }

    return false;
  }
}
