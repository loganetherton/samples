import * as _ from 'lodash';

import Log from '../api/log/logs.model';

// Target methods to log
const methods = ['POST', 'DELETE', 'PUT', 'PATCH', 'GET'];

export default async function logger(req, res, next) {
  try {
    if (methods.indexOf(req.method) > -1) {
      const {originalUrl, method, body = {}, params = {}, query = {}} = req;
      const {statusCode, statusMessage} = res;
      const logParams = {path: originalUrl, method, statusCode, statusMessage};
      const optionalTypes = {body, params, query};
      // Store optionally query, body, params
      _.forEach(optionalTypes, (value, name) => {
        if (Object.keys(value).length) {
          logParams[name] = value;
        }
      });
      if (req.user) {
        logParams.user = req.user;
      }
      logParams.ip = req.ip;
      await Log.create(logParams);
    }
    // next();
  } catch (e) {
    console.log('**************ERR IN LOGGER**********');
    console.log(e);
  }
}
