import * as _ from 'lodash';

import Inventory from '../inventory/inventory.model';
import {setRedisValue} from '../../helpers/redis';
// import {Inventory} from "./company.controller";
import config from "../../config/environment";

/**
 * Activity params queried from Redis
 */
interface IRedisValues {
  batch: {_id: string, name: string}[],
  company: {_id: string, name: string}[],
  store: {_id: string, name: string}[]
}

/**
 * Params in range for querying activity params
 */
interface IParamsInRangeParams {
  created: {
    $gt: string,
    $lt: string
  },
  soldToLiquidation: boolean
  // Some other params which I can figure out later
  [key: string]: any
}

/**
 * Populate Redis with any values which are not currently stored at this time
 * @param params
 * @param redisKeys
 * @param redisValues
 * @param types
 * @returns {Promise<*[]>}
 */
export async function populateRedisWithActivityParamsData(
  params: IParamsInRangeParams,
  redisKeys = ['', '', ''],
  redisValues: IRedisValues = {batch: [], company: [], store: []},
  types = {batch: true, company: true, store: true}
) {
  let batchMap = {}, companyMap = {}, storeMap = {};
  const {batch = [], company = [], store = []} = redisValues;
  const [batchKey, companyKey, storeKey] = redisKeys;
  const inventories = await Inventory.find(params)
    .populate('batch')
    .populate('company')
    .populate('store');
  inventories.forEach(inventory => {
    // Calculate and store batch values
    if (types.batch && !batch.length && inventory.batch) {
      batchMap = createParamMap(batchMap, inventory, 'batch', 'batchId');
    }
    // Calculate and store company values
    if (types.company && !company.length && inventory.company) {
      companyMap = createParamMap(companyMap, inventory, 'company');
    }
    // Calculate and store store values
    if (types.store && !store.length && inventory.store) {
      storeMap = createParamMap(storeMap, inventory, 'store');
    }
  });

  if (!store.length) {
    _.forEach(storeMap, item => store.push(item));
    setRedisValue(storeKey, store, config.oneDay);
  }
  if (!company.length) {
    _.forEach(companyMap, item => company.push(item));
    setRedisValue(companyKey, company, config.oneDay);
  }
  if (!batch.length) {
    _.forEach(batchMap, item => batch.push(item));
    setRedisValue(batchKey, batch, config.oneDay);
  }
  return [batch, company, store];
}

/**
 * Create param map as an intermediate step for getting search params
 * @param map Incoming map
 * @param inventory Current inventory
 * @param inventoryParam
 * @param displayParam
 */
function createParamMap(map: any, inventory: any, inventoryParam: any, displayParam = 'name') {
  map[inventory[inventoryParam]._id] = {
    [displayParam]: inventory[inventoryParam][displayParam],
    _id: inventory[inventoryParam]._id
  };
  return map;
}
