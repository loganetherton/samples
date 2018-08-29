import {IRedisEvent, redisClient, redisPubSub} from "../config/redis";
import ErrorLog from "../api/errorLog/errorLog.model";
import {populateRedisWithActivityParamsData} from '../../server/api/company/company.helpers';
import {getActivityDateRange} from '../../server/api/company/company.controller';

// Shape of query for activity data
interface IRedisActivityParamsQuery {
  beginDate: string,
  endDate: string,
  beginEnd: string,
  date: string
}

// Param search query
interface IRedisActivityParamsSearchParams {
  created: {
    $gt?: string,
    $lt?: string
  },
  company: string,
  rejected: boolean,
  soldToLiquidation: boolean,
}

/**
 * Get the redis value for this key
 * @param key Redis key
 * @returns {Promise<void>}
 */
export async function getRedisValue(key: string) {
  return new Promise(resolve => {
    let response: any[] = [];
    try {
      redisClient.json_get(key, function (err: object, redisRes: string) {
        // Result
        if (!err && redisRes) {
          response = JSON.parse(redisRes);
        }
        return resolve(response);
      });
    } catch (err) {
      logRedisServerDown(err);
      return resolve(response);
    }
  });
}

/**
 * Store a value in Redis
 * @param {string} key
 * @param {string} value
 * @param {number} expire
 */
export function setRedisValue(key: string, value: object, expire? : number) {
  redisClient.json_set(key, '.', JSON.stringify(value));
  if (expire) {
    redisClient.expire(key, expire);
  }
}

/**
 * Delete keys matching a phrase using redis matching syntax
 * @param match
 */
export function redisDelMatch(match: string) {
  return new Promise((resolve, reject) => {
    // Find keys
    redisClient.keys(`*${match}*`, (err, keys) => {
      // If we have keys
      if (keys && keys.length) {
        // Remove each
        keys.forEach(key => {
          redisClient.json_del(key, function (err: object) {
            if (err) {
              return reject(err);
            }
            return resolve(true);
          });
        });
      }
    });
  })
}

/**
 * Log that the Redis server is down
 * @param err
 * @returns {Promise<void>}
 */
export async function logRedisServerDown(err: Error) {
  await ErrorLog.create({
    method: 'getParamsInRange',
    controller: 'company.controller',
    stack: err ? err.stack : null,
    error: 'Redis server unavailable',
  });
}

/**
 * Create redis key for this value
 * @param params Search params to base the key on
 * @returns {Promise<*[]>}
 */
export async function getRedisKeys(params: IRedisActivityParamsSearchParams) {
  // Caches get invalidated whenever these collections change
  const batchKey = JSON.stringify(Object.assign({}, params, {type: 'batch'}));
  const companyKey = JSON.stringify(Object.assign({}, params, {type: 'company'}));
  const storeKey = JSON.stringify(Object.assign({}, params, {type: 'store'}));
  return [batchKey, companyKey, storeKey];
}

/**
 * Get redis data as related to Company::getParamsInRange
 * @param {IRedisActivityParamsQuery} query
 * @returns {Promise<{keys: string[]; values: {batch: Promise<any>; company: Promise<any>; store: Promise<any>}}>}
 */
export async function getRedisParamsData(query: IRedisActivityParamsQuery) {
  const params: any = getActivityDateRange(query);
  // Create redis keys
  const keys = await getRedisKeys(params);
  const [batchKey, companyKey, storeKey] = keys;
  // Check for existence in redis
  const redisValues = {
    batch: await getRedisValue(batchKey),
    company: await getRedisValue(companyKey),
    store: await getRedisValue(storeKey)
  };
  return {keys, values: redisValues};
}

/**
 * Listen for redis events and perform any necessary actions
 * @returns {Promise<void>}
 */
export async function listenForRedisEvents() {
  redisPubSub.on('admin:setParamsInRange', async (data: IRedisEvent) => {
    let args = Array.prototype.slice.call(JSON.parse(data.data));
    populateRedisWithActivityParamsData.apply(null, args);
  });
}

/**
 * Emit a redis pub-sub event
 * @param {string} event
 * @param {IRedisEvent} data
 * @returns {Promise<void>}
 */
export async function emitRedisEvents(event: string, data: IRedisEvent) {
  redisPubSub.emit(event, data);
}
