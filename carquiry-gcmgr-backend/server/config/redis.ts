// import * as redis from 'redis';
import redis = require('redis');
// For some reason I can't just require the package, I get a cannot find module error
import nrp = require('node-redis-pubsub/lib/node-redis-pubsub');
import * as rejson from 'redis-rejson';
import config from './environment';

// Redis with rejson
interface IRedisJson extends redis.RedisClient {
  json_del?: (key: string, callback: (err: object) => void) => void,
  json_get?: (key: string, callback: (err: object, key: string) => void) => Promise<any>,
  json_set?: (key: string, separator: string, value: string) => void,
  // Not sure about these yet, gotta look into them
  json_mget?: () => void,
  json_type?: () => void
  json_numincrby?: () => void
  json_nummultby?: () => void
  json_strappend?: () => void
  json_strlen?: () => void
  json_arrappend?: () => void
  json_arrindex?: () => void
  json_arrinsert?: () => void
  json_arrlen?: () => void
  json_arrpop?: () => void
  json_arrtrim?: () => void
  json_objkeys?: () => void
  json_objlen?: () => void
  json_debug?: () => void
  json_forget?: () => void
  json_resp?: () => void
}

// Node Redis pub-sub
interface INrp {
  on: (event: string, cb: (data: object) => void) => void,
  emit: (event: string, data: object) => void
}

export interface IRedisEvent {
  data: any
}

const redisConfig = config.redis;

// Add rejson methods to redis
rejson(redis);

const redisOptions: redis.ClientOpts = {
  host: redisConfig.host
};

// Redis connection
export const redisClient: IRedisJson = redis.createClient(redisOptions);

// Redis pub-sub
export const redisPubSub: INrp = nrp(redisOptions);

redisClient.on('error', function (err) {
  console.log('error event - ' + redisOptions.host + ':' + redisOptions.port + ' - ' + err);
});

