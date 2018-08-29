import * as moment from 'moment';
import {MongoClient} from 'mongodb';
import * as _ from 'lodash';
import * as fs from 'fs';
import csv from 'fast-csv';

import '../company/autoBuyRate.model';
import '../company/companySettings.model';
import Log from '../log/logs.model';
import '../company/company.model';
import '../card/card.model';
import '../stores/store.model';
import '../reserve/reserve.model';

import Inventory from '../inventory/inventory.model';
import InventoryLog from '../inventory/inventoryLog.model';
import DenialPayment from '../denialPayment/denialPayment.model';
import BiRequestLog from '../biRequestLog/biRequestLog.model';
import BiSync from '../biRequestLog/biSync';
import CompanySettings from '../company/companySettings.model';
import Company from '../company/company.model';
import Customer from '../customer/customer.model';
import ErrorLog from '../errorLog/errorLog.model';
import FrontendErrorLog from '../frontendErrorLog/frontendErrorLog.model';
import CallbackLog from '../callbackLog/callbackLog.model';
import Retailer from '../retailer/retailer.model';
import {recalculateTransactionAndReserve, sendAdjustmentCallback} from '../card/card.helpers';
import {
  DocumentNotFoundException,
  SellLimitViolationException
} from '../../exceptions/exceptions';
import {resendCallback} from '../callbackLog/callbackLog.controller';
import {
  apiCustomerValues,
  bi,
  biCompleted,
  lqCustomerFind,
  makeFakeReqRes
} from '../lq/lq.controller';
import Card from '../card/card.model';
import config from '../../config/environment';
import mailer from '../mailer';
import BiService from '../bi/bi.request';
import InventoryLogs from '../inventory/inventoryLog.model';
import {calculateValues} from '../company/company.controller';
import {Types} from 'mongoose';
import {ErrorLogger} from '../../loggers';

const errorLogger = new ErrorLogger();

const isValidObjectId = Types.ObjectId.isValid

/**
 * Get denials since the last time reconciliation was closed
 */
export async function getDenials(req, res) {
  // Get the last time reconciliation was closed
  // Check for denials since the reconciliation close
  const {pageSize = 10, page = 0} = req.params;
  let begin = req.params.begin;
  let end = req.params.end;
  begin = moment.utc(begin).startOf('day');
  end = moment.utc(end).endOf('day');
  let retailersWithDenials = [];
  let searchQuery = {};

  if(req.query.hasOwnProperty('companyId')) {
    if(req.query.hasOwnProperty('storeId')) {
      searchQuery = {
        company: req.query.companyId,
        store: req.query.storeId
      }
    } else {
      searchQuery = {
        company: req.query.companyId
      }
    }
  }
  else if(req.query.hasOwnProperty('storeId')) {
    searchQuery = {
      store: req.query.storeId
    }
  }
  searchQuery.created = {$gt: begin.toDate(), $lt: end.toDate()};

  try {
    const retailersCount = await Retailer.count({});
    const retailers = await Retailer.find({})
      .limit(parseInt(pageSize))
      .skip(parseInt(page) * parseInt(pageSize)).lean();

    for(let retailer of retailers) {
      let query = Object.assign({}, searchQuery);
      query.retailer = retailer._id;
      const inventoriesThisRetailer = await Inventory.count(query);
      query.adjustmentStatus = 'denial';
      const rejectedInventories = await Inventory.count(query);
      if(inventoriesThisRetailer && rejectedInventories) {
        retailer['percentOfDenials'] = rejectedInventories / inventoriesThisRetailer * 100;
      } else {
        retailer['percentOfDenials'] = 0;
      }
      retailersWithDenials.push(retailer);
    }

    return res.json({
      data: retailersWithDenials,
      total: retailersCount
    });
  }
  catch(e) {
    console.log('********************ERR IN ADMIN GETDENIALS***********************');
    console.log(e);
    return res.status(500).json({err: e});
  }
}

/**
 * Set card statuses
 */
export async function setCardStatus(req, res) {
  try {
    await Inventory.update(
      {
        _id: {
          $in: req.body.cardIds
        }
      },
      {
        $set: {
          activityStatus: req.body.status
        }
      },
      {multi: true});
    res.json({});
  }
  catch(err) {
    console.log('**************ERR IN SET CARD STATUS**********');

    await ErrorLog.create({
      user: req && req.user && req.user._id ? req.user._id : null,
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'setCardStatus',
      controller: 'admin.controller',
      stack: err ? err.stack : null,
      error: err,

    });

    return res.json({
      invalid: 'An error has occurred.'
    });
  }
}

/**
 * Recreate rejection history
 */
export function recreateRejectionHistory(req, res) {
  DenialPayment.find({})
  .then(denialPayments => {
    const promises = [];
    denialPayments.forEach(denial => {
      promises.push(denial.remove());
    });
    return Promise.all(promises);
  })
  .then(() => {
    return Inventory.find({
      rejected: true
    })
    .populate('customer');
  })
  .then(inventories => {
    const promises = [];
    inventories.forEach(inventory => {
      // Update rejection amounts
      const buyAmount = inventory.buyAmount;
      // Buy amount after adjustment
      const realBuyAmount = inventory.buyRate * inventory.verifiedBalance;
      if (realBuyAmount < buyAmount) {
        const rejectAmount = buyAmount - realBuyAmount;
        // Set rejected
        inventory.rejectedDate = Date.now();
        inventory.rejectAmount = rejectAmount;
        promises.push(inventory.save());
      }
    });
    return Promise.all(promises);
  })
  .then(inventories => {
    const customers = {};
    inventories.forEach(inventory => {
      // Create collection of customers with inventories
      if (!customers[inventory.customer._id]) {
        customers[inventory.customer._id] = {
          inventories: [],
          rejectionTotal: 0,
          customer: inventory.customer
        };
      }
      customers[inventory.customer._id].inventories.push(inventory);
    });
    return customers;
  })
  .then(customers => {
    const promises = [];
    _.forEach(customers, customer => {
      customer.rejectionTotal = customer.inventories.reduce((curr, next) => {
        return curr + next.rejectAmount;
      }, 0);
      let currentRejectionTotal = 0;
      // Get current reject value
      try {
        if (_.isNumber(currentRejectionTotal)) {
          currentRejectionTotal = customer.customer.rejectionTotal;
        }
      } catch (e) {
        currentRejectionTotal = 0;
      }
      let denialPayment = null;
      // If less than it should be, create a denial payment
      if (currentRejectionTotal < customer.rejectionTotal) {
        denialPayment = new DenialPayment({
          customer: customer.customer._id,
          amount: customer.rejectionTotal - currentRejectionTotal
        });
        promises.push(denialPayment.save());
      }
      promises.push(customer.customer.save());
    });
  })
  .then(() => res.json({}));
}

/**
 * Add deduction
 */
export function addDeduction(req, res) {
  const {ach, inventory} = req.body;
  let company;

  Inventory.find({cqAch: ach})
  .then(inventories => {
    if (! inventories.length) {
      throw new Error('achNotFound');
    }

    if (inventories.length > 1) {
      const companies = new Set();
      inventories.forEach(inv => {
        companies.add(inv.company.toString());
      });

      if (companies.size > 1) {
        throw new Error('multipleCompanies');
      }
    }

    company = inventories[0].company;

    return Inventory.findById(inventory);
  })
  .then(dbInventory => {
    if (! dbInventory) {
      throw new Error('inventoryNotFound');
    }

    if (dbInventory.company.toString() !== company.toString()) {
      throw new Error('differentCompany');
    }

    dbInventory.deduction = ach;
    dbInventory.save();

    return res.json({});
  })
  .catch(async err => {

    if (err && err.message === 'achNotFound') {
      return res.status(400).json({error: "The ACH could not be found in the database."});
    }

    if (err && err.message === 'inventoryNotFound') {
      return res.status(400).json({error: "Invalid inventory specified."});
    }

    if (err && err.message === 'multipleCompanies') {
      return res.status(400).json({error: "This ACH belongs to multiple companies."});
    }

    if (err && err.message === 'differentCompany') {
      return res.status(400).json({error: "This ACH belongs to a different company."});
    }

    console.log('**************ERR IN ADDDEDUCTION**************');
    console.log(err);

    await ErrorLog.create({
      user: req && req.user && req.user._id ? req.user._id : null,
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'addDeduction',
      controller: 'admin.controller',
      stack: err ? err.stack : null,
      error: err,

    });

    return res.status(500).json({error: "Something went wrong."});
  });
}

/**
 * Fill in system time on existing cards
 */
export function systemTime(req, res) {
  Inventory.find()
  .then(inventories => {
    const promises = [];
    inventories.forEach(inventory => {
      if (!inventory.systemTime) {
        inventory.systemTime = inventory.created;
        promises.push(inventory.save());
      }
    });
    return Promise.all(promises);
  })
  .then(() => res.json({}))
}

export function testCallback(req, res) {
  console.log(req.body);
  res.json({});
}

/**
 * Fix BI log duplications
 * @return {Promise.<void>}
 */
export async function fixBiLogDuplications(req, res) {
  const duplicateLogs = {};
  const allLogs = {};
  let logs;
  logs = await BiRequestLog.find({}).sort({created: -1});
  for (const log of logs) {
    const key = `${log.retailerId.toString()}.${log.number}.${log.pin}`;
    // Duplicate
    if (allLogs[key]) {
      // Duplicate already exists in structure
      if (duplicateLogs[key]) {
        duplicateLogs[key].push(log._id);
      // First duplicate instance, push duplicate and original
      } else {
        duplicateLogs[key] = [log._id];
      }
    } else {
      allLogs[key] = log._id;
    }
  }
  // Remove duplicates
  for (const dup in duplicateLogs) {
    await BiRequestLog.remove({
      _id: {$in: duplicateLogs[dup]}
    });
  }
  return res.json({});
}

/**
 * Calculate an inventory's "completeness" score
 * @param inventory
 * @return {number}
 */
function calculateInventoryWeight(inventory) {
  // Assign partial weight to activity status, since we need to compare them, but giving entire points would throw everything off
  const activityStatusValues = {
    'notShipped': 0,
    shipped: 0.2,
    receivedCq: 0.4,
    sentToSmp: 0.6,
    receivedSmp: 0.8,
    rejected: 0.1
  };
  // Inventory "score" to see how complete it is based on admin activity interaction
  let score = 0;
  // Iterate the values typically modified from admin activity
  ['orderNumber', 'cqAch', 'smpAch', 'credited', 'rejected', 'activityStatus'].forEach(property => {
    if (inventory[property]) {
      score = score + 1;
    }
    if (property === 'activityStatus') {
      const activityStatusValue = activityStatusValues[inventory[property]];
      if (!isNaN(activityStatusValue)) {
        score = score + activityStatusValue;
      }
    }
  });
  return score;
}

/**
 * Fix inventory duplications (find multiple inventories which apply to the same card)
 */
export async function fixInventoryDuplications(req, res) {
  const inventories = await Inventory.find({created: {$gt: new Date('2017-06-01')}});
  const cards = {};
  const duplicates = {}     ;
  for (const inventory of inventories) {
    // First instance
    if (!cards[inventory.card.toString()]) {
      cards[inventory.card.toString()] = inventory;
    // Not first instance
    } else {
      // First duplicate
      if (!duplicates[inventory.card.toString()]) {
        duplicates[inventory.card.toString()] = [cards[inventory.card.toString()], inventory];
      // Additional duplicates
      } else {
        duplicates[inventory.card.toString()].push(inventory);
      }
    }
  }
  const inventoriesToDelete = {};
  for (const [id, inventories] of Object.entries(duplicates)) {
    for (const [index, inventory] of inventories.entries()) {
      // Init new comparison, assume it's the first one to delete
      if (!index) {
        inventoriesToDelete[inventory.card.toString()] = [];
      }

      const score = calculateInventoryWeight(inventory);
      // inventoriesToDelete[inventory.card.toString()].push({score, inventory: inventory._id});
      const inventoryValues = {
        '_id'           : inventory._id,
        'orderNumber'   : inventory.orderNumber,
        'cqAch'         : inventory.cqAch,
        'smpAch'        : inventory.smpAch,
        'credited'      : inventory.credited,
        'rejected'      : inventory.rejected,
        'activityStatus': inventory.activityStatus
      };
      inventoriesToDelete[inventory.card.toString()].push({score, inventory: inventoryValues});
    }
  }
  for (const [cardId, inventoryWeightTuples] of Object.entries(inventoriesToDelete)) {
    // Remove those which are marked duplicate
    for (const tuple of inventoryWeightTuples) {
      if (tuple.inventory.orderNumber && tuple.inventory.orderNumber.toLowerCase() === 'duplicate') {
        await Inventory.remove({_id: tuple.inventory._id});
      }
    }
    // make sure we don't delete all inventories
    let allZeroValues = false;
    // Delete all of the 0 scored
    for (const tuple of inventoryWeightTuples) {
      if (tuple.score > 0) {
        allZeroValues = true;
      }
    }
    // If we have a zero value, delete it so long as there are other inventories
    if (!allZeroValues) {
      for (const tuple of inventoryWeightTuples) {
        await Inventory.remove({_id: tuple.inventory._id});
      }
    // All zero values, just delete all but one
    } else {
      const count = inventoryWeightTuples.length;
      for (const inventory of inventoryWeightTuples.entries()) {
        // Remove all but one at random
        if (inventory[0] < count) {
          await Inventory.remove({_id: inventory[1].inventory._id});
        }
      }
    }
  }
  return res.json({});
}

/***
 * Recalculate transaction values
 */
export async function recalculateTransactions(req, res) {
  const {inventories, dateBegin = null, dateEnd = null} = req.body;
  let findParams = {};
  if (inventories) {
    findParams = {
      _id: {
        $in: inventories
      },
      isTransaction: true
    };
  } else if (dateBegin && dateEnd) {
    findParams = {
      created: {
        $gt: new Date(dateBegin),
        $lt: new Date(dateEnd)
      },
      isTransaction: true
    };
  } else {
    return res.status(400).json({err: 'inventories or dateBegin and dateEnd are needed'})
  }
  try {
    const dbInventories = await Inventory.find(findParams)
    .populate('retailer');
    // Redo calculations for each transaction
    for (let inventory of dbInventories) {
      const companyId = inventory.company;
      // Get settings
      let companySettings = await CompanySettings.findById(companyId);
      // No settings yet
      if (!companySettings) {
        const company = await Company.findById(companyId);
        companySettings = await company.getSettings();
      }
      await recalculateTransactionAndReserve(inventory);
      await sendAdjustmentCallback(inventory);
    }
    return res.json({});
  } catch (err) {
    console.log('**************ADMIN RECALCULATE TRANSACTION ERROR**********');
    console.log(err);

    await ErrorLog.create({
      user: req && req.user && req.user._id ? req.user._id : null,
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'recalculateTransactions',
      controller: 'admin.controller',
      stack: err ? err.stack : null,
      error: err,

    });

    if (err instanceof DocumentNotFoundException || err instanceof SellLimitViolationException) {
      return res.status(err.code).json({err: err.message});
    } else {
      res.status(500).json({err: 'Unable to recalculate transactions'});
    }
  }
}

/**
 * Update customer rejections or credits
 * @param apiCustomer Default API customer
 * @param customer Correct company customer
 * @param inventory Inventory on wrong customer
 * @param type "rejection" or "credit" or "none"
 * @return {Promise.<*>}
 */
async function updateCustomerRejectionCredit(apiCustomer, customer, inventory, type = 'none') {
  let pullType = 'credits';
  let multiplier = 1;
  let amountType = 'creditAmount';
  if (type === 'rejection') {
    pullType = 'rejections';
    multiplier = -1;
    amountType = 'rejectAmount';
  }
  // Set customer on inventory
  await inventory.update({
    $set: {
      customer: customer._id
    }
  });
  // Nothing to do on non-rejection/credits
  if (type === 'none') {
    return Promise.resolve([apiCustomer, customer]);
  } else {
    if (type === 'credit') {
      // Remove the existing denial payment
      DenialPayment.remove({
        customer: apiCustomer._id,
        amount: inventory[amountType]
      });
      // Add in new denial payment
      await DenialPayment.create({
        customer: customer._id,
        amount: inventory[amountType]
      });
    }
    // Update API customer
    apiCustomer[pullType].splice(apiCustomer[pullType].indexOf(inventory._id), 1);
    apiCustomer.rejectionTotal = apiCustomer.rejectionTotal - (inventory[amountType] * multiplier);
    apiCustomer = await apiCustomer.save();
    // Update correct customer
    customer[pullType].splice(customer[pullType].indexOf(inventory._id, 1));
    customer.rejectionTotal = customer.rejectionTotal - (inventory[amountType] * multiplier);
    customer = await customer.save();
    return Promise.resolve([apiCustomer, customer]);
  }
}

/**
 * Change generic API_CUSTOMER to a company specific API customer
 */
export async function fixLqApiCustomerCompany(req, res) {
  const ps = await Company.findOne({name: /posting/i});
  // Get
  let apiCustomer = await Customer.find(Object.assign({}, lqCustomerFind, {$or: [
    {company: {$exists: false}},
    {company: ps._id}
  ]}));
  // Make sure we're not running this multiple times, as it might have some crazy side effects
  if (apiCustomer.length > 1) {
    return res.status(400).json({err: 'Already run'});
  }
  apiCustomer = apiCustomer[0];
  // Found customer
  if (apiCustomer) {
    // Make the default customer into PS's
    if (!apiCustomer.company) {
      apiCustomer.company = ps._id;
      await apiCustomer.save();
    }
  } else {
    // // Don't allow this to be run more than once
    return res.status(400).json({err: 'Unable to find API customer'});
  }
  // Get all inventories by the API customer
  const inventories = await Inventory.find({customer: apiCustomer._id, company: {$ne: ps._id}});

  // Find inventories which do not belong to PS
  for (const inventory of inventories) {
    // Non-PS
    if (inventory.company.toString() !== ps._id.toString()) {
      // Create API customer for this company if it doesn't already exist
      let customer = await Customer.findOne(apiCustomerValues(inventory.company));
      if (!customer) {
        customer = await Customer.create(apiCustomerValues(inventory.company));
      }
      let type = 'none';
      if (inventory.credited || inventory.rejected) {
        type = inventory.credited ? 'credit' : 'rejection'
      }
      // See if this inventory has rejections/credits that need to be moved
      [apiCustomer, customer] = await updateCustomerRejectionCredit(apiCustomer, customer, inventory, type);
    }
  }
  return res.json({});
}

/**
 * Resend all previous callbacks
 * @param card
 * @param force
 * @param skipCardValidation
 * @return {Promise.<void>}
 */
async function resendAllCallbacks(card, force, skipCardValidation) {
  if (card.callbackVb) {
    for (const [type, value] of Object.entries(card.callbackVb)) {
      if (typeof value === 'number') {
        await resendCallback(null, card, type, force, skipCardValidation);
      }
    }
  }
}

/**
 * Send a single callback
 * @param types
 * @param card
 * @param force
 * @param skipCardValidation
 * @return {Promise.<void>}
 */
async function sendCallback(types, card, force, skipCardValidation) {
  for (const thisType of types) {
    await resendCallback(null, card, thisType, force, skipCardValidation);
  }
}

/**
 * Send a cqPaymentInitiated callback for each inventory specified in the request body
 */
export async function sendCallbackFromActivity(req, res) {
  const {inventories = [], cards = [], numbers = [], force = false, skipCardValidation = false} = req.body;
  let resend = false;
  let type = req.body.type;
  if (!type) {
    type = req.params.type;
  }
  let types = [];
  if (type === 'denial') {
    types = ['denial', 'credit'];
  } else if (type === 'resend') {
    resend = true;
  } else {
    types = [type];
  }

  if (cards.length) {
    for (const card of cards) {
      const dbCard = await Card.findById(card).populate('inventory');
      if (dbCard.inventory) {
        dbCard.inventory = dbCard.inventory.toObject();
      }
      if (resend) {
        await resendAllCallbacks(card, force, skipCardValidation);
      } else {
        await sendCallback(types, dbCard, force, skipCardValidation);
      }
    }
  } else if (inventories.length) {
    for (const inventory of inventories) {
      const dbInventory = await Inventory.findById(inventory).populate('card');
      const card = Object.assign({}, dbInventory.card.toObject());
      card.inventory = dbInventory.toObject();
      if (resend) {
        await resendAllCallbacks(card, force, skipCardValidation);
      } else {
        await sendCallback(types, card, force, skipCardValidation);
      }
    }
  } else if (numbers.length) {
    for (const number of numbers) {
      const card = await Card.findOne({number}).populate('inventory');
      if (!card) {
        console.log('**************NOT FOUND**********');
        console.log(number);
        continue;
      }
      if (card.inventory) {
        card.inventory = card.inventory.toObject();
      }
      if (resend) {
        await resendAllCallbacks(card, force, skipCardValidation);
      } else {
        await sendCallback(types, card, force, skipCardValidation);
      }
    }
  }

  return res.json({});
}

/**
 * Retrieve card from log
 * @param log
 * @return {Promise.<*>}
 */
async function getCardFromBiLog(log) {
  let findParams = {};
  if (log.card) {
    findParams.card = log.card;
  } else {
    findParams = {
      retailer: log.retailer,
      number: log.number,
    };
    if (log.pin) {
      findParams.pin = log.pin;
    }
  }
  return await Card.findOne(findParams);
}


/**
 * Clean up BI logs with the following logic:
 *
 * First, check for any duplicates. If duplicates were found, we'd prioritise
 * the ones that have verifiedBalance set, followed by the date they were created.
 * Any duplicates that don't have responseCode will be deleted.
 * Lastly, delete any remaining logs that have no responseCode, even if they're not duplicates.
 */
export async function cleanUpBILogs(req, res) {
  try {
    MongoClient.connect(config.mongo.uri, function (err, db) {
      const biRequestLogs = db.collection('birequestlogs');
      biRequestLogs.aggregate([{$group: {_id: {number: "$number", retailerId: "$retailerId", pin: "$pin", balance: '$balance'},count: {$sum: 1},biRequestLogs: {$push: "$$ROOT"}}},{$match: {count: {$gt: 1}}},], {allowDiskUse: true}).toArray(
        async function (err, dupes) {
          let hasMultipleCards = 0;
          let hasNoCards = 0;

          for (const dupe of dupes) {
            let card;
            let logs = dupe.biRequestLogs.sort((a, b) => {
              // Sort by date
              if (a.created === b.created) {
                return 0;
              }
              return a.created < b.created ? 1 : -1;
            });

            let hasValidLog = false;
            let numValid = 0;
            // Make sure any group that requires PINs doesn't have multiple results
            for (const log of logs) {
              if (config.retailersNoPin[log.retailerId.toString()]) {
                break;
              }
              if (typeof log.balance === 'number' && !(log.balance === 0 && log.responseCode !== config.biCodes.invalid)) {
                hasValidLog = true;
                numValid++;
              }
            }
            if (hasValidLog && numValid > 1) {
              console.log('**************NUM VALID**********');
              console.log(numValid);
              console.log(logs);
            }
            // Make sure any group doesn't have multiple prefixes
            let hasPrefix = false;
            let numPrefix = 0;
            for (const log of logs) {
              if (log.prefix) {
                hasPrefix = true;
                numPrefix++;
              }
            }
            if (hasPrefix) {
              console.log('**************HAS PREFIX**********');
              console.log(numPrefix);
            }
            // Find the ones with cards attached
            const indexWithCards = [];
            for (const [index, log] of logs.entries()) {
              card = await getCardFromBiLog(log);
              if (card) {
                indexWithCards.push(index);
              }
            }
            if (!indexWithCards.length) {
              hasNoCards++;
            } else if (indexWithCards.length > 1) {
              hasMultipleCards++;
            } else {
              hasNoCards++;
            }
            // logs = logs.map(log => log.toObject());
            /**
             * Now that we know we have a steady set, find the ones to delete
             * @type {Array}
             */
            // keep logs with a balance, if only one has a balance
            logs = logs.map(log => {
              if (typeof log.balance === 'number' && log.balance > 0) {
                log.keep = true;
              }
              return log;
            });
            const numKeep = logs.filter(log => log.keep);
            if (numKeep === 1) {
              for (const log of logs) {
                if (!log.keep) {
                  await BiRequestLog.remove({_id: log._id});
                  logs = logs.filter(thisLog => thisLog._id.toString() !== log._id.toString());
                }
              }
            }
            //
            if (logs.length === 1) {
              continue;
            }
            // If we still have logs, remove all but the most recent
            for (const [index, log] of logs.entries()) {
              if (index) {
                await BiRequestLog.remove({_id: log._id});
              }
            }
          }
          return res.json({});
        });
    });
  }
  catch (err) {
    console.log('***************************ERR IN CLEANUPBILOGS***************************');
    console.log(err);

    await ErrorLog.create({
      user: req && req.user && req.user._id ? req.user._id : null,
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'cleanUpBILogs',
      controller: 'admin.controller',
      stack: err ? err.stack : null,
      error: err,

    });

    return res.status(500).json({
      invalid: 'An error has occurred.'
    });
  }
}

/**
 * Clean up any duplicate cards
 */
export async function cleanUpCards(req, res) {
  try {
    MongoClient.connect(config.mongo.uri, function (err, db) {
      const biRequestLogs = db.collection('cards');
      biRequestLogs.aggregate([{$group: {_id: {number: "$number", retailer: "$retailer", pin: "$pin"},count: {$sum: 1},cards: {$push: "$$ROOT"}}},{$match: {count: {$gt: 1}}},], {allowDiskUse: true}).toArray(
        async function (err, dupes) {

          for (const [index, dupe] of dupes.entries()) {
            let cards = dupe.cards.sort((a, b) => {
              // Sort by date
              if (a.created === b.created) {
                return 0;
              }
              return a.created < b.created ? 1 : -1;
            });

            console.log('**************SORTED**********');
            console.log(cards);

            const hasInventory = [];

            // Prefer cards with valid inventories
            for (const card of cards) {
              if (card.inventory) {
                const inventory = await Inventory.findById(card.inventory);
                if (inventory) {
                  hasInventory.push(card.inventory.toString());
                  card.inventory = inventory;
                }
              }
            }

            console.log('**************HAS INVENTORY**********');
            console.log(hasInventory);

            // Only a single card has inventory, we can safely delete the rest
            if (hasInventory.length === 1) {
              for (const card of cards) {
                // Remove cards without inventory
                if (hasInventory.indexOf(card._id.toString()) === -1) {
                  console.log('**************REMOVE ME**********');
                  console.log(card);
                  // await card.remove();
                }
              }
              // Remove cards that have been removed from array
              cards = cards.filter(card => {
                return hasInventory.indexOf(card._id.toString()) > -1;
              });
            }

            console.log('**************CARDS**********');
            console.log(cards);

            if (index > 3) {
              break;
            }

            // let hasValidLog = false;
            // let numValid = 0;
            // // Make sure any group that requires PINs doesn't have multiple results
            // for (const log of logs) {
            //   if (config.retailersNoPin[log.retailerId.toString()]) {
            //     break;
            //   }
            //   if (typeof log.balance === 'number' && !(log.balance === 0 && log.responseCode !== config.biCodes.invalid)) {
            //     hasValidLog = true;
            //     numValid++;
            //   }
            // }
            // if (hasValidLog && numValid > 1) {
            //   console.log('**************NUM VALID**********');
            //   console.log(numValid);
            //   console.log(logs);
            // }
            // // Make sure any group doesn't have multiple prefixes
            // let hasPrefix = false;
            // let numPrefix = 0;
            // for (const log of logs) {
            //   if (log.prefix) {
            //     hasPrefix = true;
            //     numPrefix++;
            //   }
            // }
            // if (hasPrefix) {
            //   console.log('**************HAS PREFIX**********');
            //   console.log(numPrefix);
            // }
            // // Find the ones with cards attached
            // const indexWithCards = [];
            // for (const [index, log] of logs.entries()) {
            //   card = await getCardFromBiLog(log);
            //   if (card) {
            //     indexWithCards.push(index);
            //   }
            // }
            // if (!indexWithCards.length) {
            //   hasNoCards++;
            // } else if (indexWithCards.length > 1) {
            //   hasMultipleCards++;
            // } else {
            //   hasNoCards++;
            // }
            // // logs = logs.map(log => log.toObject());
            // /**
            //  * Now that we know we have a steady set, find the ones to delete
            //  * @type {Array}
            //  */
            // // keep logs with a balance, if only one has a balance
            // logs = logs.map(log => {
            //   if (typeof log.balance === 'number' && log.balance > 0) {
            //     log.keep = true;
            //   }
            //   return log;
            // });
            // const numKeep = logs.filter(log => log.keep);
            // if (numKeep === 1) {
            //   for (const log of logs) {
            //     if (!log.keep) {
            //       await BiRequestLog.remove({_id: log._id});
            //       logs = logs.filter(thisLog => thisLog._id.toString() !== log._id.toString());
            //     }
            //   }
            // }
            // //
            // if (logs.length === 1) {
            //   continue;
            // }
            // // If we still have logs, remove all but the most recent
            // for (const [index, log] of logs.entries()) {
            //   if (index) {
            //     await BiRequestLog.remove({_id: log._id});
            //   }
            // }
          }
          return res.json({});
        });
    });
  }
  catch (err) {
    console.log('***************************ERR IN CLEANUPBILOGS***************************');
    console.log(err);

    await ErrorLog.create({
      user: req && req.user && req.user._id ? req.user._id : null,
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'cleanUpBILogs',
      controller: 'admin.controller',
      stack: err ? err.stack : null,
      error: err,

    });

    return res.status(500).json({
      invalid: 'An error has occurred.'
    });
  }
}

/**
 * Sends an email
 */
export async function sendAccountingEmail(req, res) {
  const {companyId} = req.params;
  const {emailSubject, emailBody} = req.body;

  const company = await Company.findById(companyId);
  const emails = company.bookkeepingEmails.split(',');
  const recipients = [];
  emails.forEach(email => {
    if (email.trim().length) {
      recipients.push(email.trim());
    }
  });

  if (recipients.length) {
    try {
      mailer.sendAccountingEmail(recipients, emailSubject, emailBody, async err => {
        if (! err) {
          return res.json({});
        } else {
          console.log('**************************ERR IN SENDEMAILS**************************');
          console.log(err);
          console.log(err.response.body.errors);

          await ErrorLog.create({
            user: req && req.user && req.user._id ? req.user._id : null,
            body: req.body ? req.body : {},
            params: req.params ? req.params : {},
            method: 'sendAccountingEmail',
            controller: 'admin.controller',
            stack: err ? err.stack : null,
            error: err,

          });

          return res.status(500).json({
            invalid: 'An error has occurred.'
          });
        }
      });
    } catch (err) {
      console.log('**************************ERR IN SENDEMAILS**************************');
      console.log(err);

      await ErrorLog.create({
        user: req && req.user && req.user._id ? req.user._id : null,
        body: req.body ? req.body : {},
        params: req.params ? req.params : {},
        method: 'sendAccountingEmail',
        controller: 'admin.controller',
        stack: err ? err.stack : null,
        error: err,

      });

      return res.status(500).json({
        invalid: 'An error has occurred.'
      });
    }

    return;
  }

  return res.json({});
}

/**
 * Rebuilds the Elasticsearch indices
 */
export async function rebuildElasticsearchIndices(req, res) {
  try {
    await Inventory.createMapping();

    // Don't wait for this, it takes forever
    Inventory.synchronize({}, {saveOnSynchronize: false});

    return res.json({});
  } catch (err) {
    console.log('******************ERR IN REBUILDING ELASTICSEARCH INDICES******************');
    console.log(err);

    await ErrorLog.create({
      user: req && req.user && req.user._id ? req.user._id : null,
      method: 'rebuildElasticsearchIndices',
      controller: 'admin.controller',
      stack: err ? err.stack : null,
      error: err,
    });

    return res.status(500).json({});
  }
}

/**
 * Get mass update logs
 * @param req
 * @param res
 * @return {Promise.<void>}
 */
export async function getLogs(req, res) {
  const {type = 'updateDetails', begin, end} = req.params;
  const beginFormatted = moment(begin, 'YYYY-MM-DD').toDate();
  const endFormatted = moment(end, 'YYYY-MM-DD').toDate();

  const logs = await Log.find({
    path: new RegExp(`^/${type}`),
    created: {
      $gt: beginFormatted,
      $lt: endFormatted
    }
  }).sort({created: -1});
  const logFormatted = [];
  for (const log of logs) {
    const logObject = log.toObject();
    if (logObject.body.ids) {
      logObject.cards = await Inventory.find({_id: {$in: logObject.body.ids}}).populate({
        path: 'card',
        populate: [
          {
            path: 'retailer',
            model: 'Retailer'
          }
        ]
      });
    }
    logFormatted.push(logObject);
  }
  return res.json({data: logFormatted});
}

/**
 * Get callback log data for date range
 */
export async function getCallbackLogs(req, res) {
  const {begin, end} = req.params;
  const logs = await CallbackLog.find({created: {$gte: new Date(begin), $lte: new Date(end)}})
  return res.json({logs});
}

/**
 * Fixes for bad setBalance requests
 */
export async function setBalanceFixes(req, res) {
  const logs = await Log.find({path: '/setBalance'}).sort({'body.number': 1, 'body.pin': 1, 'body.retailerId': 1});
  let requestIds = [];
  let noRequestId = 0;
  for (const log of logs) {
    const body = log.body;
    const balance = parseFloat(body.balance);
    const retailer = await Retailer.findOne({
      $or: [{
        gsId: body.retailer_id
      }, {
        aiId: body.retailer_id
      }]
    });
    if (!retailer) {
      continue;
    }
    const biRequestLogs = await BiRequestLog.find({retailerId: retailer._id, number: body.number, pin: body.pin})
    .populate({
      path: 'card',
      populate: [
        {
          path: 'inventory',
          model: 'Inventory'
        }
      ]
    });
    for (const biLog of biRequestLogs) {
      if (!biLog.requestId) {
        noRequestId++;
        const requestIdResponse = await BiService.getRequestIdByAttributes(body.number, body.pin, body.retailer_id);
        // Record request ID
        if (requestIdResponse && requestIdResponse.length) {
          const parsedResponse = JSON.parse(requestIdResponse);
          const initialRequestId = parsedResponse.shift();
          biLog.requestId = initialRequestId.request_id;
          requestIds.push(initialRequestId.request_id);
          // Multiple requestIds
          if (parsedResponse.length) {
            const additionalRequestIds = parsedResponse.map(requestId => requestId.request_id);
            requestIds = requestIds.concat(additionalRequestIds);
            biLog.additionalRequestIds = additionalRequestIds;
          }
        }
        // await biLog.save();
        console.log('**************BI LOG AT END**********');
        console.log(biLog);
      }
      biLog.balance = balance;
      biLog.finalized = true;
      biLog.responseCode = '000';
      await biLog.save();
      requestIds.push(biLog.requestId);

      if (biLog.card) {
        biLog.card.verifiedBalance = balance;
        await biLog.card.save();
        if (biLog.card.inventory) {
          biLog.card.inventory.verifiedBalance = balance;
          await biLog.card.inventory.save();
        }
      }
    }
  }

  console.log('**************AFFECTED REQUEST IDS**********');
  requestIds.forEach(requestId => {
    console.log(requestId);
  });

  return res.json({});
}

/**
 * Record frontend errors
 */
export async function frontendError(req, res) {
  await FrontendErrorLog.create({
    stack: req.body.stack
  });
  return res.json({});
}

/**
 * Create fake req/res for /lq/bi
 * @param card Formatted card
 * @param retailer Retailer
 * @param user User
 * @param callbackUrl URL
 * @param userEmail email
 */
export function createFakeReqResBiInsert(card, retailer, user, callbackUrl, userEmail) {
  const [biFakeReq, biFakeRes] = makeFakeReqRes({body: {}, user});
  const bodyParams = {number: card.number, retailer: retailer._id, callbackUrl, autoSell: true, userEmail};
  if (card.pin) {
    bodyParams.pin = card.pin;
  }
  biFakeReq.body = Object.assign(biFakeReq.body, bodyParams);
  return [biFakeReq, biFakeRes];
}

/**
 * Sync existing cards with BI
 */
export async function syncCardsWithBi(req, res) {
  try {
    const file = req.files[0];
    const fileName = `${__dirname}/uploads/${file.filename}`;
    const stream = fs.createReadStream(fileName);
    // Cards to sync
    const cards = [];
    const csvStream = csv()
    .on("data", function(record){
      cards.push({
        number: record[0],
        pin: record[1],
        retailer: record[2]
      });
    })
    .on('end', async () => {
      for (const [index, card] of cards.entries()) {
        console.log('**************CARD**********');
        console.log(card);
        setTimeout(async () => {
          const formatted = Object.assign({}, card);
          // Get retailer
          const retailer = await Retailer.findOne({name: new RegExp(card.retailer, 'i')});
          formatted.pin = card.pin ? card.pin : null;
          // Get log
          const biLogs = await BiRequestLog.find({
            number: formatted.number,
            pin: formatted.pin,
            retailerId: retailer._id
          }).sort({created: -1});
          let logToUse = null;
          console.log('**************BI LOGS**********');
          console.log(biLogs);
          // Insert BI
          if (!biLogs.length) {
            const data = {
              cardNumber: formatted.number,
              retailerId: retailer.gsId || retailer.aiId
            };
            if (formatted.pin) {
              data.pin = formatted.pin;
            }

            // insert into BI
            const [fakeReq, fakeRes] = createFakeReqResBiInsert(formatted, retailer, req.user);
            console.log('**************INSERT INTO BI, DOES NOT EXIST GCMGR**********');
            console.log(fakeReq.body);
            // Keep record
            await BiSync.create({
              number: card.number,
              pin: card.pin,
              retailerId: retailer._id,
              type: 'insert',
              requestId: null
            });
            await bi(fakeReq, fakeRes);
            // Sync BI
          } else {
            logToUse = biLogs[0];
            let data = {};
            let noRequestId = false;
            if (logToUse.requestId) {
              data = await BiService.getRecord(logToUse.requestId);
            } else {
              noRequestId = true;
            }
            console.log('**************DATA FROM BI**********');
            console.log(data);
            // Make a new request for phone only/in store only
            if (data.responseCode === config.biCodes.phoneBalanceOnly || data.responseCode === config.biCodes.inStoreBalanceOnly) {
              noRequestId = true;
            }
            // Not found, reinsert
            if (data.responseCode === config.biCodes.unknownRequest) {
              const retailer = await Retailer.findById(logToUse.retailerId);
              data = {
                cardNumber: logToUse.number,
                retailerId: retailer.gsId || retailer.aiId
              };
              if (formatted.pin) {
                data.pin = formatted.pin;
              }
              if (logToUse.requestId) {
                data.requestid = logToUse.requestId;
              }
              // Keep record
              await BiSync.create({
                number: card.number,
                pin: card.pin,
                retailerId: retailer._id,
                type: 'insert',
                requestId: logToUse.requestId
              });
              console.log('**************INSERT INTO BI, EXISTS GCMGR**********');
              console.log(data);
              // Insert into BI with requestId
              await BiService.insert(data);
              // Update record (prod)
            } else if ([config.biCodes.success, config.biCodes.invalid].indexOf(data.responseCode) > -1) {
              const [biFakeReq, biFakeRes] = makeFakeReqRes({});
              const biCompleteRetailer = retailer.gsId || retailer.aiId;
              let balance = parseFloat(data.balance);
              if (isNaN(balance)) {
                balance = 0;
              }
              const biCompletedBody = {
                number: formatted.number,
                retailerId: biCompleteRetailer,
                invalid: balance ? 0 : 1,
                balance: balance ? balance : 0,
                autoSell: true
              };
              if (formatted.pin) {
                biCompletedBody.pin = formatted.pin;
              }
              biFakeReq.body = biCompletedBody;
              biFakeReq.get = () => config.biCallbackKey;
              biFakeReq.params = {requestId: data.request_id};
              // Keep record
              await BiSync.create({
                number: card.number,
                pin: card.pin,
                retailerId: retailer._id,
                type: 'sync',
                requestId: logToUse.requestId
              });
              console.log('**************BI COMPLETE**********');
              console.log(biFakeReq.body);
              await biCompleted(biFakeReq, biFakeRes);
            // No request ID, insert new record (prod)
            } else if (noRequestId) {
              // insert into BI
              const [fakeReq, fakeRes] = createFakeReqResBiInsert(formatted, retailer, req.user);
              await BiSync.create({
                number: card.number,
                pin: card.pin,
                retailerId: retailer._id,
                type: 'insert',
                requestId: null
              });
              console.log('**************INSERT INTO BI, NO REQUEST ID GCMGR**********');
              console.log(fakeReq.body);
              await bi(fakeReq, fakeRes);
            }
          }
        }, 1000 * index);
      }
    });

    stream.pipe(csvStream);
    return res.json();
  } catch (err) {
    console.log('**************ERR IN SYNC BI**********');
    console.log(err);
    return res.status(500).json({err: 'Could not process CSV'});
  }
}

/**
 * Fix BI requests which ended up with the wrong retailer associated
 */
export async function fixRetailer(req, res) {
  const {oldRetailerId, newRetailerId, pinLength, siteRegex, begin} = req.body;
  const options = {
    retailer_id: oldRetailerId,
    new_retailer_id: newRetailerId,
    pin_length: pinLength,
    site: siteRegex,
    created: begin
  };
  const newRetailer = await Retailer.findOne({$or: [{gsId: newRetailerId}, {aiId: newRetailerId}]});
  const cards = await BiService.getCardsWithIncorrectRetailer(options);
  // Reset the BiRequestLog entries for these cards. Set to the correct retailer
  for (const card of cards) {
    console.log('**************CARD**********');
    console.log(card);
    const biRequestLog = await BiRequestLog.findOne({requestId: card.request_id});
    if (biRequestLog) {
      biRequestLog.balance = null;
      biRequestLog.responseCode = null;
      biRequestLog.responseDateTime = null;
      biRequestLog.verificationType = null;
      biRequestLog.responseMessage = null;
      biRequestLog.retailerId = newRetailer._id;
      await biRequestLog.save();
      // Remove any old cards
      if (biRequestLog.card) {
        const card = await Card.findById(biRequestLog.card);
        await Inventory.remove({_id: card.inventory});
        await card.remove();
      }
    }
    // Remove old callbacks so we can send new one
    await CallbackLog.remove({
      requestId: card.request_id
    });
  }
  return res.json({});
}

export async function loadInventoryLogs(req, res) {
  const {inventory} = req.params;

  try {
    let inventories = await InventoryLog.find({inventory})
      .populate('customer')
      .populate('retailer')
      .populate('store')
      .populate('company')
      .populate('card')
      .populate('user')
      .populate('batch')
      .sort({created: -1});

    return res.json({calculatedInventories: inventories});
  } catch (err) {
    errorLogger.log({
      user: req && req.user && req.user._id ? req.user._id : null,
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'loadInventoryLogs',
      controller: 'admin.controller',
      stack: err ? err.stack : null,
      error: err,
    });

    return res.status(500).json({error: 'Something went wrong'});
  }
}

export async function revertInventory(req, res) {
  const {inventory, log} = req.params;

  try {
    const dbInventory = await Inventory.findById(inventory);
    const dbLog = await InventoryLog.findById(log);

    for (const [k, v] of Object.entries(dbLog.toObject())) {
      // We don't really want to mess with these two attributes
      if (! ['_id', '__v'].includes(k)) {
        dbInventory[k] = v;
      }
    }

    await dbInventory.save();

    return res.json({});
  } catch (err) {
    errorLogger.log({
      user: req && req.user && req.user._id ? req.user._id : null,
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'revertInventory',
      controller: 'admin.controller',
      stack: err ? err.stack : null,
      error: err,
    });

    return res.status(500).json({error: 'Something went wrong'});
  }
}

