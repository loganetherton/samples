import * as elasticsearch from 'elasticsearch';
import * as _ from 'lodash';

import config from '../config/environment';
import {
  ElasticLogger,
  ErrorLogger
} from '../loggers';

const esClient = new elasticsearch.Client(Object.assign({}, config.elasticsearch));
const logger = new ErrorLogger();
const elasticLogger = new ElasticLogger();

/**
 * Updates an elastic index by query
 *
 * @param {Object} schema Mongoose schema
 * @param {Function} params A function that will be invoked with the corresponding document as the argument
 *                          that should return the query object for the update
 */
export function updateElasticIndexOnSave(schema, params) {
  schema.post('save', function (doc) {
    let generatedParams = params(doc);

    if (! _.isPlainObject(generatedParams)) {
      return;
    }

    // Write index in the case of conflicts
    generatedParams = Object.assign(generatedParams, {conflicts: 'proceed'});

    try {
      elasticLogger.log({client: 'native', data: dotToUnderscore(generatedParams)});
      esClient.updateByQuery(generatedParams);
    } catch (e) {
      logger.log({
        body: params,
        stack: e.stack,
        error: e
      });
    }
  });
}

// This shouldn't be here
// To do: Figure out a better way to do this
export function dotToUnderscore(obj) {
  const result = {};

  if (_.isArray(obj)) {
    return obj.map(o => dotToUnderscore(o));
  }

  for (let k in obj) {
    if (obj.hasOwnProperty(k)) {
      let v = obj[k];

      if (_.isPlainObject(v) || _.isArray(v))  {
        v = dotToUnderscore(v);
      }

      k = k.replace(/\./g, '_');

      result[k] = v;
    }
  }

  return result;
}
