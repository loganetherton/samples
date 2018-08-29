import * as fs from 'fs';
import * as csv from 'fast-csv';
import * as _ from 'lodash';
import * as moment from 'moment';
import {DocumentQuery} from 'mongoose';

import '../company/autoBuyRate.model';
import '../log/logs.model';
import '../card/card.model';
import {default as Store} from '../stores/store.model';
import '../reserve/reserve.model';

import Card, {ICard} from './card.model';
import CardUpdate from '../cardUpdates/cardUpdates.model';
import BiService from '../bi/bi.request';
import DeferredInquiries from '../deferredBalanceInquiries/deferredBalanceInquiries.model';
import Inventory, {IInventory} from '../inventory/inventory.model';
import Customer, {ICustomer} from '../customer/customer.model';
import Company, {ICompany} from '../company/company.model';
import ErrorLog from '../errorLog/errorLog.model';
import Receipt from '../receipt/receipt.model';
import Retailer, {IRetailer} from '../retailer/retailer.model';
import Batch from '../batch/batch.model';
import DenialPayment from '../denialPayment/denialPayment.model';
import User, {IUser} from '../user/user.model';
import BiRequestLog, {IBiRequestLog} from '../biRequestLog/biRequestLog.model';
import Callback from '../callbackLog/callback';
import config from '../../config/environment';
import {retailerSetBuyAndSellRates} from '../retailer/retailer.controller';
import {
  determineSellTo,
  recalculateTransactionAndReserve,
  sendAdjustmentCallback
} from '../card/card.helpers';
import {redisDelMatch} from '../../helpers/redis';
import {stripDollarSign} from '../../helpers/string';
import {IBiRequestResponse, IBiSearchParams, IGenericExpressResponse} from "../../helpers/interfaces";
import {
  IAddToInventoryCardUpdate,
  IAddToInventoryParams,
  IBalanceInquiryParams,
  ICreateInventoryParams,
  ICreateNewCardParams,
  ICustomerDenialsParams,
  IMarginSetting,
  IMassUpdateParams,
  INewBiRequestParams,
  IRejectCustomerUpdateArray,
  IRetailerObject
} from "./card.interfaces";
import {IBiRequestLogFindParams, IInsertBiParams} from "../lq/lq.interfaces";
import {insertBi, sendCallbackFromCompanySettingsOrDirectUrl} from "../lq/lq.controller";
import {ICompanySettings} from "../company/companySettings.model";


// Default user name
const defaultName = '__default__';

/**
 * Test cards
 */
const testRetailerIds = ['952', '1045'];
const testNumbers = ['55555', '44444', '33333', '22222'];
const allowTest = true;

// Output BI results for testing
export const testBiMockData = [];

/**
 * Create an update record when a card is updated
 * @param userId User ID of the user making the request
 * @param biResponse Response from balance inquiry service
 * @param card Card document
 * @param balance Card balance
 * @returns {*}
 */
function createCardUpdate(userId, biResponse, card, balance) {
  // Create update record
  const update = new CardUpdate();
  update.card = card._id;
  update.user = [userId];
  const manualCodes = [config.biCodes.timeout, config.biCodes.headerError, config.biCodes.authenticationError,
                       config.biCodes.invalid, config.biCodes.retailerNotSupported, config.biCodes.retailerDisabled,
                       config.biCodes.inStoreBalanceOnly, config.biCodes.phoneBalanceOnly, config.biCodes.systemDown];
  // Retailer not supported
  if (manualCodes.indexOf(biResponse.responseCode) !== -1 || /error/i.test(biResponse)) {
    update.balanceStatus = 'manual';
    // Success
  } else if (biResponse.responseCode === config.biCodes.success) {
    update.balanceStatus = 'received';
    update.balance = balance;
    // Default to defer
  } else {
    update.balanceStatus = 'deferred';
  }
  return update.save();
}

/**
 * Update BI log
 * @param log BI log
 * @param biResponse Response from BI
 * @param balance Balance
 * @return {*}
 */
function updateBiLog(log, biResponse, balance) {
  if (typeof balance !== 'undefined') {
    log.balance = balance;
  }
  // Update unless unknown, auth error, or system problems
  if ([config.biCodes.unknownRequest, config.biCodes.headerError, config.biCodes.systemDown].indexOf(log.responseCode) === -1) {
    log.verificationType = biResponse.verificationType;
    log.responseDateTime = biResponse.responseDateTime;
    // Insert request ID
    if (typeof log.requestId === 'undefined') {
      log.requestId = biResponse.requestId;
    }
    log.responseCode = biResponse.responseCode;
    log.responseMessage = biResponse.responseMessage;
    if ([config.biCodes.success, config.biCodes.invalid, config.biCodes.retailerNotSupported, config.biCodes.inStoreBalanceOnly, config.biCodes.phoneBalanceOnly].indexOf(log.responseCode) > -1) {
      log.finalized = true;
    }
  }
  return log;
}

/**
 * Update a card during a balance inquiry
 * @param dbCard Card document
 * @param update Update document
 * @param balance Card balance
 * @param biResponse Exact response from BI
 * @returns {*}
 */
async function updateCardDuringBalanceInquiry(dbCard, update, balance, biResponse) {
  try {
    if (config.debug) {
      console.log('**************UPDATE CARD DURING BALANCE INQUIRY**********');
      console.log(dbCard);
      console.log(update);
      console.log(balance);
      console.log(biResponse);
    }
    // Push update onto card
    dbCard.updates.push(update._id);
    // Update card info
    dbCard.balanceStatus = update.balanceStatus;
    // Bad card
    if (dbCard.balanceStatus === 'bad' || dbCard.balanceStatus === 'manual') {
      // Set invalid
      if (dbCard.balanceStatus === 'bad') {
        dbCard.valid = false;
      }
      return dbCard.save();
    }
    // Successful balance
    const hasBalance = typeof balance !== 'undefined';
    // For when we don't have a card
    const biSearchParams: IBiSearchParams = {
      number: dbCard.number,
      retailerId: dbCard.retailer._id
    };
    if (dbCard.pin) {
      biSearchParams.pin = dbCard.pin;
    }

    // Update log if we have one
    let log = await BiRequestLog.findOne({
      $or: [{
        card: dbCard._id
      }, biSearchParams]
    });
    if (log) {
      // Update BI log
      log = updateBiLog(log, biResponse, balance);
      log = await log.save();
    }
    // Create BiLog
    if (!log) {
      const biParams: IBiRequestLogFindParams = {
        number: dbCard.number,
        retailerId: dbCard.retailer._id,
        card: dbCard._id,
        responseDateTime: biResponse.response_datetime,
        requestId: biResponse.requestId,
        responseCode: biResponse.responseCode,
        responseMessage: update.balanceStatus
      };
      if (dbCard.pin) {
        biParams.pin = dbCard.pin;
      }
      if (hasBalance) {
        biParams.balance = balance;
      }
      log = new BiRequestLog(biParams);
      await log.save();
    }
    // Have inventory, update it
    if (hasBalance && dbCard.inventory) {
      dbCard.inventory.verifiedBalance = balance;
      await dbCard.inventory.save();
      // No inventory, set VB on card
    } else if (hasBalance) {
      dbCard.verifiedBalance = balance;
    }

    return dbCard.save();
  } catch (err) {
    await ErrorLog.create({
      method: 'updateCardDuringBalanceInquiry',
      controller: 'card.controller',
      stack: err ? err.stack : null,
      error: err
    });
    console.log('**************ERR IN updateCardDuringBalanceInquiry**********');
    console.log(err);
  }
}

/**
 * Handle BI response
 * @param {string} cardId
 * @param {string} userId
 * @param {IBiRequestResponse} biResponse
 * @returns {Promise<IBiRequestResponse>}
 */
async function handleBiResponse(cardId: string, userId: string,
                                biResponse: IBiRequestResponse): Promise<IBiRequestResponse> {
  try {
    if (config.debug) {
      console.log('**************HANDLE BI RES**********');
      console.log(biResponse);
    }
    let success;
    // Success or failure of BI request
    if (biResponse.responseCode) {
      success = biResponse.responseCode === config.biCodes.success;
    }
    let balance = null;
    // Parse successful response
    if (success) {
      // Balance
      if (typeof biResponse.balance === 'string') {
        balance = parseFloat(biResponse.balance);
      }
      // Balance is null
      if (isNaN(balance)) {
        balance = null;
      }
      // No card ID, just return balance, don't update card
      if (cardId === null) {
        return Object.assign(biResponse, {balance});
      }
    } else {
      // No card ID, just return balance, don't update card
      if (cardId === null) {
        return biResponse;
      }
    }
    // Existing card
    let dbCard = await Card.findById(cardId)
    .populate('retailer')
    .populate('inventory');
    if (dbCard) {
      const update = await createCardUpdate(userId, biResponse, dbCard, balance);
      await updateCardDuringBalanceInquiry(dbCard, update, balance, biResponse);
    }
    return biResponse;
  } catch (err) {
    console.log('**************ERR IN HANDLE BI RESPONSE**********');
    console.log(err);
    await ErrorLog.create({
      method: 'handleBiResponse',
      controller: 'card.controller',
      stack: err ? err.stack : null,
      error: err,
      user: userId
    });
  }
}

/**
 * Perform the actual balance inquiry
 * @param {IBalanceInquiryParams} params
 * @returns {Promise<IBiRequestResponse>}
 */
export async function balanceInquiry(params: IBalanceInquiryParams): Promise<IBiRequestResponse> {
  let biParams: INewBiRequestParams;
  try {
    let method = 'insert';
    const isVista = params.companyId && Array.isArray(config.vistaBiUser) &&
                   config.vistaBiUser.indexOf(params.companyId.toString()) > -1;
    if (params.requestId) {
      method = 'getRecord'
    } else {
      biParams = {cardNumber: params.number, pin: params.pin, retailerId: params.retailerId, user_id: params.userId};
    }
    const response: IBiRequestResponse = await BiService[method](biParams || params.requestId, isVista);
    return await handleBiResponse(params.cardId, params.userId, response);
  } catch (err) {
    await ErrorLog.create({
      body: params,
      params: params,
      method: 'balanceInquiry',
      controller: 'card.controller',
      stack: err ? err.stack : null,
      error: err,
    });
  }
}

class GenericResponse extends Error {
  constructor(message) {
    super();
    this.message = message;
  }
}

/**
 * Handle checking a balance
 * @param {IRetailer & string} retailer
 * @param {IUser} user
 * @param {string} number
 * @param {string | null} pin
 * @param {string | null} cardId
 * @param {string | null} requestId
 * @returns {Promise<IBiRequestResponse>}
 */
export async function doCheckBalance(retailer: IRetailer, user: IUser, number: string, pin: string, cardId: string, requestId: string): Promise<IBiRequestResponse> {
  // Get log
  const log: IBiRequestLog = await BiRequestLog.findOne({
    number, pin, retailer: retailer._id
  });
  // If we have a response code in the log
  const hasResponse = log && 'responseCode' in log;
  // BI already finished
  if (hasResponse && (log.responseCode === '000' || log.responseCode === '900011')) {
    const card: ICard = await Card.findOne({
      number, pin, retailer: retailer
    });
    if (log.responseCode === '000' && log.balance) {
      card.verifiedBalance = log.balance;
      card.balanceStatus = 'received';
    } else if (log.responseCode === '900011') {
      card.verifiedBalance = log.balance;
      card.balanceStatus = 'received';
    }
    await card.save();
    return {
      verificationType : log.verificationType,
      balance          : log.balance,
      response_datetime: log.responseDateTime,
      responseMessage  : log.responseMessage,
      requestId        : log.requestId,
      responseCode     : log.responseCode,
      request_id       : log.requestId,
      responseDateTime : log.responseDateTime,
      recheck          : log.recheck,
      recheckDateTime  : log.recheckDateTime
    };
    // No log, begin BI
  } else {
    const biParams: IBalanceInquiryParams = {
      retailerId: retailer.gsId || retailer.aiId,
      number,
      pin,
      cardId,
      userId: typeof user === 'string' ? user : user._id.toString(),
      companyId: user.company.toString(),
      requestId
    };
    const balanceInquiryResponse: IBiRequestResponse = await balanceInquiry(biParams);
    return balanceInquiryResponse;
  }
}

/**
 * Check balance from UI and perform autosell
 * @param req
 * @param res
 * @returns {Promise<any>}
 */
export async function checkBalanceFromUi(req, res) {
  try {
    let {retailer: retailerObject, number, pin = '', _id: cardId = null, customer, store} = req.body;
    const retailer: IRetailer = await Retailer.findById(retailerObject._id);
    // const balanceResponse: IBiRequestResponse = await doCheckBalance(retailer, req.user, number, pin, cardId, requestId);
    const user: IUser = req.user;
    const company: ICompany = await Company.findById(user.company);
    const companySettings: ICompanySettings = await company.getSettings();
    const insertBiParams: IInsertBiParams = {
      number,
      pin,
      retailer: retailer._id,
      retailerId: retailer._id,
      autoSell: true,
      store,
      customer: customer._id,
      user: req.user
    };
    if (companySettings.callbackUrl) {
      insertBiParams.callbackUrl
    }
    const balanceResponse = await insertBi(insertBiParams);
    return res.json(balanceResponse);
  } catch (err) {
    if (err.message === 'Retailer not found') {
      return res.status(400).json({err: err.message});
    }
    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'checkBalance',
      controller: 'card.controller',
      stack: err ? err.stack : null,
      error: err,
    });
    res.status(500).json(err);
  }
}

/**
 * Check a card balance
 */
export async function checkBalance(req, res) {
  try {
    let {retailer: retailerObject, number, pin = '', _id: cardId = null, requestId = null} = req.body;
    const retailer = await Retailer.findById(retailerObject._id);
    const balanceResponse: IBiRequestResponse = await doCheckBalance(retailer, req.user, number, pin, cardId, requestId);
    return res.json(balanceResponse);
  } catch (err) {
    if (err.message === 'Retailer not found') {
      return res.status(400).json({err: err.message});
    }
    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'checkBalance',
      controller: 'card.controller',
      stack: err ? err.stack : null,
      error: err,
    });
    console.log('**************CHECK BALANCE ERR**********');
    console.log(err);
    res.status(500).json(err);
  }
}

/**
 * Check to see if a BI ID exists for a retailer
 * @param retailer
 */
function checkBiIdExists(retailer) {
  if (!(retailer.gsId || retailer.aiId)) {
    throw new Error('biUnavailableThisRetailer');
  }
}

/**
 * Check to see if a retailer is available for BI
 * @param retailerId
 * @return {Promise.<null|*>}
 */
async function checkBiAvailable(retailerId) {
  const retailer = await Retailer.findById(retailerId);
  checkBiIdExists(retailer);
  return retailer;
}

/**
 * Checks a card balance
 *
 * @param {Object|String} retailer
 * @param {String} number
 * @param {String} pin
 * @param {String} cardId
 * @param {String} requestId
 * @param userId
 * @param companyId
 */
export async function checkCardBalance(retailer: IRetailer, number: string, pin: string, cardId: string,
                                       requestId: string, userId: string, companyId: string): Promise<IBiRequestResponse> {
  let retailerToUse;
  // Plain object retailer
  if (_.isPlainObject(retailer) && (retailer.gsId || retailer.aiId)) {
    checkBiIdExists(retailer);
    retailerToUse = retailer;
  } else if (retailer.constructor.name === 'model') {
    checkBiIdExists(retailer);
    retailerToUse = retailer;
  // Object ID as string or actual object ID
  } else if (typeof retailer === 'string' || retailer.constructor.name === 'ObjectID') {
    retailerToUse = await checkBiAvailable(retailer);
    checkBiIdExists(retailer);
  } else {
    throw new Error('biUnavailableThisRetailer');
  }

  const biParams: IBalanceInquiryParams = {
    retailerId: retailerToUse.gsId || retailerToUse.aiId,
    number,
    pin,
    cardId: cardId.toString(),
    userId,
    companyId,
    requestId
  };
  return await balanceInquiry(biParams);
}

/**
 * Update card balance
 */
export async function updateBalance(req, res) {
  try {
    const userid = req.user._id.toString();
    const _card = await Card.findOne({_id: req.body._id});
    if (!_card) {
      return res.status(404).json({err: 'Card does not exist'});
    }
    if (_card.user.toString() !== userid) {
      return res.status(401).json({err: 'Card does not belong to this customer'});
    }
    const card = req.body;
    await Card.findByIdAndUpdate(card._id, {
      $set: {
        balance: card.balance
      }
    });
    return res.json({});
  } catch (err) {
    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'updateBalance',
      controller: 'card.controller',
      stack: err ? err.stack : null,
      error: err,

    });
    console.log('**************ERR IN UPDATE BALANCE**********');
    console.log(err);
    return res.status(500).json(err);
  }
}

/**
 * Find cards which already exist in the DB
 * @param {string} retailer
 * @param {string} number
 * @param {string} pin
 * @returns {"mongoose".DocumentQuery<ICard | null, ICard>}
 */
export function findCards(retailer: string, number: string, pin: string): DocumentQuery<ICard, ICard> {
  return Card.findOne({
    retailer,
    number,
    pin
  });
}

/**
 * Create a default user if necessary
 * @param {ICustomer} customer
 * @param {IUser} reqUser
 * @returns {Promise<any>}
 */
export function createDefaultCustomer(customer: string, reqUser: IUser): Promise<string> {
  return new Promise((resolve) => {
    if (customer === 'default') {
      // Find default user this company
      Customer.findOne({
        company: reqUser.company,
        firstName: defaultName,
        lastName: defaultName
      })
      .then(customer => {
        // No default customer, create one
        if (!customer) {
          const customer = new Customer({
            firstName: defaultName,
            lastName: defaultName,
            stateId: defaultName,
            address1: defaultName,
            city: defaultName,
            state: defaultName,
            zip: defaultName,
            phone: defaultName,
            company: reqUser.company
          });
          customer.save()
          .then(customer => {
            resolve(customer._id);
          })
        } else {
          // Default user exists
          resolve(customer._id);
        }
      });
    } else {
      resolve(customer);
    }
  });
}

/**
 * Check if this is a test card
 * @param card
 * @returns {boolean}
 */
export function isTestCard(card) {
  return allowTest && testRetailerIds.indexOf(card.uid) !== -1 && testNumbers.indexOf(card.number) !== -1;
}

/**
 * Handle the creation of a new card
 * @param {ICreateNewCardParams} params
 * @returns {Promise<IGenericExpressResponse>}
 */
export async function createNewCard(params: ICreateNewCardParams): Promise<IGenericExpressResponse> {
  params.customer = await createDefaultCustomer(params.customer, params.user);
  let card: ICard = await findCards(params.retailer, params.number, params.pin).populate('retailer');

  if (card) {
    // Don't overwrite test card
    if (!isTestCard(card) && !card.inventory) {
      return {
        status: 200,
        message: card
      };
    }
    return {
      status: 400,
      message: {invalid: 'Card has already been inserted into the database'}
    };
  }

  // Create the new card
  card = new Card(params);
  card.userTime = params.userTime;
  card.created = moment.utc().toDate();
  card.user = params.user._id;
  card.company = params.company._id;
  card.balanceStatus = 'unchecked';
  // Save
  card = await card.save();
  // Retrieve card with retailer
  card = await Card.findById(card._id)
    .populate({
      path: 'retailer',
      populate: {
        path: 'buyRateRelations',
        model: 'BuyRate'
      }
    })
    .populate('customer');

  const companySettings = await params.company.getSettings();

  let settings: IMarginSetting | ICompanySettings;
  if (companySettings) {
    settings = companySettings
  } else {
    settings = {margin: 0.03};
  }
  // Populate merch
  let retailer: IRetailerObject = card.retailer.populateMerchValues(card);
  retailer       = retailerSetBuyAndSellRates(retailer, settings, params.store, null, card.balance);
  card.buyRate   = retailer.buyRate;
  card.sellRate  = retailer.sellRate;
  card           = await card.save();
  if (card && retailer.sellRate) {
    return {
      status: 200,
      message: card
    };
  }
  return {
    status: 400,
    message: {error: {errors: [{balance: config.lqNewCardResponse.violateSellLimits}]}}
  };
}

/**
 * Input a new card
 */
export async function newCard(req, res){
  try {
    const {retailer, number, userTime, balance, customer, lqCustomerName} = req.body;
    const user = req.user;
    const companyId = user.company ? user.company.toString() : req.body.company;
    const company = await Company.findById(companyId);
    const newCardParams: ICreateNewCardParams = {
      retailer,
      number,
      pin: req.body.pin || null,
      userTime: new Date(userTime),
      balance,
      customer,
      lqCustomerName: lqCustomerName || null,
      user,
      store: req.body.store || user.store.toString(),
      company
    };
    const newCardResponse: IGenericExpressResponse = await createNewCard(newCardParams);
    return res.status(newCardResponse.status).json(newCardResponse.message);
  } catch (err) {
    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'newCard',
      controller: 'card.controller',
      stack: err ? err.stack : null,
      error: err,

    });
    console.log('**************NEW CARD ERR**********');
    console.log(err);
    throw err;
  }
}

/**
 * Get existing cards
 */
export async function getExistingCards(req, res) {
  try {
    const customerId = req.params.customerId;
    const userCompany = req.user.company;
    // Make sure that the customer being queried belongs to the company that the user belongs to
    const customer = await Customer.findOne({_id: customerId, company: userCompany});
    if (!customer) {
      return res.status(401).json({err: 'Customer does not belong to this company'});
    }
    // Get cards for this customer
    const cards = await Card.find({
      customer,
      inventory: {$exists: false}
    })
    .populate('retailer')
    .sort({created: -1});
    return res.json({data: cards});
  } catch (err) {
    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'getExistingCards',
      controller: 'card.controller',
      stack: err ? err.stack : null,
      error: err,

    });
    return res.status(500).json({err});
  }
}

/**
 * Get existing cards for receipt
 */
export function getExistingCardsReceipt(req, res) {
  let customer = req.params.customerId;
  // Find inventories for the default customer for this store
  if (customer === 'default') {

  } else {
    Inventory
    // Find cards in inventory that have not been reconciled
    .find({
      reconciliation: {$exists: false},
      customer
    })
    .sort({created: -1})
    .populate('retailer')
    .populate('card')
    .then(inventories => res.json({data: inventories}))
    .catch(async err => {
      await ErrorLog.create({
        body: req.body ? req.body : {},
        params: req.params ? req.params : {},
        method: 'getExistingCardsReceipt',
        controller: 'card.controller',
        stack: err ? err.stack : null,
        error: err,

      });
      return res.status(500).json(err);
    });
  }
}

/**
 * Edit an existing card
 */
export async function editCard(req, res) {
  const {_id, number, pin, retailer: {retailerId}, merchandise} = req.body;
  const userid = req.user._id.toString();

  const _card = await Card.findOne({_id: _id});
  if (!_card) {
    return res.status(404).json({err: 'Card does not exist'});
  }
  if (_card.user.toString() !== userid) {
    return res.status(401).json({err: 'Card does not belong to this customer'});
  }
  let dbCard;
  // Find and update card
  await Card.findById(_id)
  .populate('retailer')
  .then(card => {
    dbCard = card;
    dbCard.number = number;
    dbCard.pin = pin;
    dbCard.merchandise = merchandise;
    return dbCard.save();
  })
  // Remove any existing deferred
  .then((card) => {
    dbCard = card;
    return DeferredInquiries.remove({card: _id});
  })
  .then(() => {
    return Company.findById(req.user.company);
  })
  .then(company => {
    return company.getSettings();
  })
  // Recalculate buy and sell rates
  .then(settings => {
    const retailer = retailerSetBuyAndSellRates(dbCard.retailer, settings, req.user.store, null, dbCard.merchandise);
    dbCard.buyRate = retailer.buyRate;
    dbCard.sellRate = retailer.sellRate;
    return dbCard.save();
  })
  // return response
  .then(() => {
    return res.json(dbCard);
  })
  // Begin balance inquiry
  .then(async () => {
    const biRequestLog = await BiRequestLog.findOne({
      number,
      pin,
      retailerId
    });
    const requestId = biRequestLog ? biRequestLog.requestId : null;
    const biParams: IBalanceInquiryParams = {
      retailerId,
      number,
      pin,
      cardId: _id,
      userId: req.user._id.toString(),
      companyId: req.user.company.toString(),
      requestId
    };
    return await balanceInquiry(biParams);
  })
  .catch(async err => {
    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'editCard',
      controller: 'card.controller',
      stack: err ? err.stack : null,
      error: err,

    });
    res.status(500).json(err);
  });
}

/**
 * Remove a card
 * @param cardId Card ID
 * @param user User making the request
 * @returns {*}
 */
async function removeCard(cardId, user) {
  const card = await Card.findById(cardId);
  // Card not found
  if (!card) {
    return 'notFound';
  }
  let cardCompany = card.company;
  if (!cardCompany) {
    const cardUser = await User.findOne(card.user[0]);
    cardCompany = cardUser.company;
  }
  // Card does not belong to the requesting user
  if (user.role === 'corporate-admin' && (cardCompany.toString() !== user.company.toString())) {
    return 'unauthorized';
  } else if (user.role === 'employee' && (card.user[0].toString() !== user._id.toString())) {
    return 'unauthorized';
  }
  // Card cannot be removed because an inventory is attached
  if (card.inventory) {
    return 'inventoryAttached';
  }
  await DeferredInquiries.remove({card: cardId});
  await CardUpdate.remove({card: cardId});
  return 'CardRemoved';
}

/**
 * handle the response from removing a card
 * @param res
 * @param removeValue Return value from remove card
 * @return {*}
 */
function handleRemoveCardResponse(res, removeValue) {
  if (removeValue) {
    switch (removeValue) {
      case 'notFound':
        res.status(404).send('');
        return true;
      case 'unauthorized':
        res.status(401).send('');
        return true;
      case 'inventoryAttached':
        res.status(400).json({err: 'Cannot remove a card with an inventory attached'});
        return true;
      case 'CardRemoved':
        res.status(200).send('Card successfully removed');
        return true;
    }
  }
  return false;
}

/**
 * Delete a card
 */
export async function deleteCard(req, res) {
  try {
    // Remove card
    const cardId = req.params.cardId;
    const removeValue = await removeCard(cardId, req.user);

    // Attempt to remove card
    if (!handleRemoveCardResponse(res, removeValue)) {
      return res.status(500).json({err: 'Unable to handle card removal'});
    }
  }
   catch (err) {
    console.log('**************DELETE CARD ERR**********');
    console.log(err);
     await ErrorLog.create({
       body: req.body ? req.body : {},
       params: req.params ? req.params : {},
       method: 'deleteCard',
       controller: 'card.controller',
       stack: err ? err.stack : null,
       error: err,

     });
    return res.status(500).json(err);
  }
}

/**
 * Make sure we have a valid number for inventory props
 * @param input
 */
function ensureValidInventoryNumber(input) {
  if (isNaN(input)) {
    return 0;
  }
  if (typeof input !== 'number') {
    return 0;
  }
  return input;
}

/**
 * Handle the actual inventory creation
 * @param {ICreateInventoryParams} params
 * @returns {Promise<[any , any , any , any , any , any , any , any , any , any]>}
 */
function createInventory(params: ICreateInventoryParams) {
  const inventoryPromises = [];
  _.forEach(params.cards, card => {
    const inventory = new Inventory();
    // Save the local time that the user created this inventory
    inventory.userTime = new Date(params.userTime);
    let balance = card.balance;
    const buyRate = card.buyRate;
    const buyAmount = card.buyAmount;
    inventory.card = card._id;
    inventory.user = params.user._id;
    inventory.store = params.store._id;
    inventory.company = params.user.company;
    inventory.balance = ensureValidInventoryNumber(balance);
    inventory.buyRate = ensureValidInventoryNumber(buyRate);
    inventory.buyAmount = ensureValidInventoryNumber(buyAmount);
    inventory.customer = card.customer;
    inventory.retailer = card.retailer._id;
    // Auto-sell
    inventory.proceedWithSale = params.companySettings.autoSell;
    // Margin
    inventory.margin = params.companySettings.margin || 0.03;
    // Merchandise
    inventory.merchandise = card.merchandise;
    // Transaction
    inventory.isTransaction = !!params.transaction;
    inventory.transaction = params.transaction;
    inventory.callbackUrl = params.callbackUrl;
    inventory.serviceFee = typeof params.companySettings.serviceFee === 'number' ? params.companySettings.serviceFee : config.serviceFee;
    // Card is already populated with merch values
    const sellTo = determineSellTo(card.retailer, inventory.balance, params.companySettings);
    if (sellTo) {
      // Rate at the time of purchase
      inventory.sellRateAtPurchase = sellTo.rate;
      inventory.smp = sellTo.smp;
      inventory.type = sellTo.type;
      // Timezone offset
      inventory.tzOffset = params.tzOffset;
      inventory.created = params.realUserTime;
      inventory.userTime = params.realUserTime;
      inventoryPromises.push(inventory.save());
    }
  });
  return Promise.all(inventoryPromises);
}

/**
 * Add inventory records to cards after inventory is created
 * @param cards
 * @param dbInventories
 */
function addInventoryToCards(cards, dbInventories) {
  const cardPromises = [];
  // Iterate cards
  cards.forEach(card => {
    // iterate inventories and find the corresponding inventory for each card
    dbInventories.forEach(dbInventory => {
      if (card._id.toString() === dbInventory.card.toString()) {
        card.inventory = dbInventory._id;
        cardPromises.push(card.save());
      }
    });
  });
  return Promise.all(cardPromises);
}

/**
 * Roll back additions to inventory
 * @param dbCards
 * @param dbInventories
 */
function rollBackInventory(dbCards, dbInventories) {
  const errorPromises = [];
  // Roll back cards
  if (dbCards) {
    dbCards.forEach(card => {
      delete card.inventory;
      errorPromises.push(card.save());
    });
  }
  if (dbInventories) {
    // Remove inventories
    dbInventories.forEach(inventory => {
      errorPromises.push(inventory.remove());
    });
  }
  return Promise.all(errorPromises);
}

/**
 * Determine sale total for display on receipt (reducer)
 * @returns Number
 */
function determineOrderTotal(curr, next) {
  // Use buy amount explicitly set
  if (typeof next.buyAmount === 'number') {
    return curr + next.buyAmount;
  }
  const balance = parseFloat(next.balance);
  const buyRate = parseFloat(next.buyRate);
  // No balance, ain't worth it
  if (!balance || !buyRate || isNaN(balance) || isNaN(buyRate)) {
    return curr + 0;
  }
  // Use buy rate and balance
  if (next.buyRate) {
    return curr + (buyRate * balance);
  }
  // Give up on life, your hopes, your dreams
  return curr + 0;
}

/**
 * Handle customer denial adjustments
 * @param {ICustomerDenialsParams} params
 * @returns {Promise<void>}
 */
async function handleCustomerDenials(params: ICustomerDenialsParams) {
  let denialPayment;
  // This amount is still owed
  if (params.rejectionTotal > params.thisOrderPurchaseAmount || params.modifiedDenials) {
    const modification: number = params.modifiedDenials ? params.modifiedDenials : params.thisOrderPurchaseAmount;
    // Modified denials
    const paidTowardsRejection = typeof params.modifiedDenials === 'number' && modification;
    params.customer.rejectionTotal = params.rejectionTotal - paidTowardsRejection;
    denialPayment = new DenialPayment({
      amount: paidTowardsRejection,
      userTime: params.userTime,
      customer: params.customer._id
    });
    // No further amount owed
  } else {
    params.customer.rejectionTotal = 0;
  }
  // Make sure we didn't screw up here
  params.customer.rejectionTotal = params.customer.rejectionTotal < 0 ? 0 : params.customer.rejectionTotal;
  if (denialPayment) {
    denialPayment = await denialPayment.save();
  }
  await Promise.all([
    params.customer.save(),
    denialPayment ? denialPayment.save() : null
  ]);
}

/**
 * Ensure that sale is possible for the submitted cards
 * @param cards
 * @param companySettings
 * @returns {Promise<IGenericExpressResponse>}
 */
async function ensureSalePossible(cards, companySettings): Promise<IGenericExpressResponse> {
  let continueSale = true;
  const noSmpCards = [];
  // Check to make sure we can sell all cards
  cards.forEach(card => {
    // Assign merch values, assume default if not set
    const retailer = card.retailer.populateMerchValues(card);
    const sellTo = determineSellTo(retailer, card.balance, companySettings);
    if (!sellTo) {
      continueSale = false;
      noSmpCards.push(card);
    }
  });
  // Don't continue
  if (!continueSale) {
    return {
      status: 400,
      message: {reason: 'noSmp', cards: noSmpCards}
    };
  }
  return null;
}

/**
 * Handle adding cards to inventory
 * @param {IAddToInventoryParams} params
 * @returns {Promise<IGenericExpressResponse>}
 */
export async function doAddToInventory(params: IAddToInventoryParams): Promise<IGenericExpressResponse> {
  let dbCards = [];
  let dbInventories;
  try {
    let rejectionTotal = 0, thisOrderPurchaseAmount = 0;
    let company;
    company = await Company.findById(params.user.company);
    const dbCompanySettings = await company.getSettings();
    // Set buyAmount and balance for each card
    for (const thisCard of params.cards) {
      const $set: IAddToInventoryCardUpdate = {
        balance: thisCard.balance,
      };
      if (thisCard.buyAmount) {
        $set.buyAmount = thisCard.buyAmount;
      }
      const dbCard = await Card.findByIdAndUpdate(thisCard._id, {
        $set
      }).populate('retailer');
      dbCards.push(await dbCard.save());
    }
    // Ensure that we can make the sale on all cards
    const salePossibleResponse = await ensureSalePossible(dbCards, dbCompanySettings);
    if (salePossibleResponse) {
      return salePossibleResponse;
    }
    // Remove any inventories which might exist for whatever reason
    for (const thisCard of dbCards) {
      if (thisCard.inventory) {
        const inventory = await Inventory.findOne({
          card: thisCard._id
        });
        if (inventory) {
          inventory.remove();
        }
      }
    }
    // Get customer
    let customer = await Customer.findOne({_id: params.cards[0].customer});
    rejectionTotal = customer.rejectionTotal || 0;
    // Only subtract a specified amount from this sale if we have modified
    thisOrderPurchaseAmount = params.cards.reduce(determineOrderTotal, 0);
    // If we have a pending denial amount
    if (customer && params.modifiedDenials < rejectionTotal || (!isNaN(rejectionTotal) && rejectionTotal)) {
      const customerDenialParams: ICustomerDenialsParams = {
        rejectionTotal,
        thisOrderPurchaseAmount,
        modifiedDenials: params.modifiedDenials,
        customer,
        userTime: params.userTime
      };
      await handleCustomerDenials(customerDenialParams);
    }
    const tzOffset = params.userTime.toISOString().substr(-6);
    const realUserTime = moment.utc().add(parseInt(tzOffset), 'hours').toDate();
    let store = await Store.findById(params.store || params.user.store);
    // Store doesn't exist or doesn't belong to this company, assign to first store
    if (!store || store.companyId.toString() !== company._id.toString()) {
      store = await Store.findOne({companyId: company._id});
    }
    // Create the inventories
    const createInventoryParams: ICreateInventoryParams = {
      cards: dbCards,
      userTime: params.userTime,
      user: params.user,
      companySettings: dbCompanySettings,
      tzOffset,
      store,
      realUserTime,
      transaction: params.transaction,
      callbackUrl: params.callbackUrl
    };
    dbInventories = await createInventory(createInventoryParams);
    // Requery updated cards
    const cardIds = [];
    for (const inventory of dbInventories) {
      cardIds.push(inventory.card);
    }
    dbCards = await Card.find({
      '_id': {$in: cardIds}
    });
    // Add inventory to cards
    await addInventoryToCards(dbCards, dbInventories);
    let receipt = new Receipt();
    // Create receipts
    dbInventories.forEach((inventory, key) => {
      if (!key) {
        receipt.customer = inventory.customer;
        receipt.userTime = realUserTime;
        receipt.user = params.user._id;
        receipt.store = store._id || params.user.store;
        receipt.company = params.user.company;
        // Amount of pending denials
        receipt.rejectionTotal = rejectionTotal;
        // Total amount of receipt
        receipt.total = thisOrderPurchaseAmount;
        // Applied towards denials
        receipt.appliedTowardsDenials = 0;
        // Grand total
        receipt.grandTotal = 0;
        // Amount remaining
        receipt.remainingDenials = 0;
        // Modified denial amount if we have one
        if (typeof params.modifiedDenials === 'number') {
          receipt.modifiedDenialAmount = params.modifiedDenials;
        }
        // Determine amount applied towards denials
        if (rejectionTotal) {
          // Apply modified amount
          if (params.modifiedDenials) {
            receipt.appliedTowardsDenials = params.modifiedDenials;
            // Apply full amount
          } else if (rejectionTotal >= thisOrderPurchaseAmount) {
            receipt.appliedTowardsDenials = thisOrderPurchaseAmount;
            // All denials paid, but receipt is higher value
          } else {
            receipt.appliedTowardsDenials = rejectionTotal;
          }
          receipt.grandTotal = thisOrderPurchaseAmount - receipt.appliedTowardsDenials;
          // No denials, all cash
        } else {
          receipt.grandTotal = thisOrderPurchaseAmount;
        }
      }
      receipt.inventories.push(inventory._id);
    });
    receipt = await receipt.save();
    // Add receipt to inventories
    for (const inventory of dbInventories) {
      inventory.receipt = receipt._id;
      await inventory.save();
    }
    return {
      status: 200,
      message: receipt
    };
  } catch (err) {
    // Roll back inventory actions
    await rollBackInventory(dbCards, dbInventories);
    throw err;
  }
}

/**
 * Add to inventory
 */
export async function addToInventory(req, res) {
  const {userTime, modifiedDenials = 0, store, transaction = null, callbackUrl = null, cards} = req.body;
  try {
    const addToInventoryParams: IAddToInventoryParams = {
      userTime: new Date(userTime),
      modifiedDenials,
      store,
      transaction,
      callbackUrl,
      user: req.user,
      cards
    };
    const addToInventoryResponse = await doAddToInventory(addToInventoryParams);
    return res.status(addToInventoryResponse.status).json(addToInventoryResponse.message);
  } catch (err) {
    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'addToInventory',
      controller: 'card.controller',
      stack: err ? err.stack : null,
      error: err,

    });
    console.log('**************ADD TO INVENTORY ERR**********');
    console.log(err);
    res.status(500).json(err);
  }
}

/**
 * Modify an inventory item (admin)
 */
export function modifyInventory(req, res) {
  const body = req.body;
  // Find the current inventory
  Inventory.findById(body.inventory._id)
  .then(inventory => {
    switch (body.value) {
      case 'notAddedToLiquidation':
        inventory.soldToLiquidation = false;
        break;
      case 'addedToLiquidation':
        inventory.soldToLiquidation = false;
        break;
      case 'rateVerified':
        inventory.soldToLiquidation = false;
        break;
      case 'rateVerifiedNotAcceptable':
        inventory.soldToLiquidation = false;
        break;
      case 'soldToLiquidation':
        inventory.soldToLiquidation = true;
        break;
    }
    return inventory.save();
  })
  .then(inventory => res.json(inventory))
  .catch(async err => {
    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'modifyInventory',
      controller: 'card.controller',
      stack: err ? err.stack : null,
      error: err,

    });
    console.log('**************MODIFY INVENTORY ERROR**********');
    console.log(err);
    return res.status(500).json(err);
  });
}

/**
 * Update specific value on an inventory
 * @param inventory Inventory
 * @param key Key
 * @param value Value
 */
function updateInventoryValue(inventory, key, value) {
  if (typeof value !== 'undefined') {
    switch (key) {
      case 'created':
        inventory.created = new Date(value);
        inventory.userTime = new Date(value);
        break;
      // Update SMP rate and SMP paid
      case 'liquidationRate':
        value = parseFloat(value);
        value = value > 1 ? value / 100 : value;
        const balance = typeof inventory.verifiedBalance === 'number' ? inventory.verifiedBalance : inventory.balance;
        inventory.liquidationRate = value;
        inventory.liquidationSoldFor = balance * value;
        break;
      case 'activityStatus':
        value = value === '-' ? undefined : value;
        inventory.activityStatus = value;
        break;
      default:
        inventory[key] = value;
        inventory.card[key] = value;
    }
  }
  return inventory;
}


/**
 * Change SMP, PIN, or number for a card
 * @param req
 * @param res
 */
export function updateDetails(req, res) {
  const ids = req.body.ids;
  const {smp, activityStatus, cqAch, batch} = req.body;
  const body = req.body;
  // SMPs
  const smps = config.smpIds;
  Inventory.find({
    _id: {
      $in: ids
    }
  })
  .populate('card')
  .populate('batch')
  .then(async inventories => {
    for (let inventory of inventories) {
      const oldCard = inventory.card.toObject();
      // I have no idea why there are multiple values for liquidationSoldFor
      const mutable = ['activityStatus', 'orderNumber', 'smpAch', 'cqAch', 'liquidationSoldFor', 'liquidationSoldFor2',
                       'liquidationRate', 'customer', 'number', 'pin', 'created', 'user', 'store', 'margin',
                       'serviceFee', 'retailer', 'type', 'adminActivityNote'];
      // Update mutable values
      for (let key of mutable) {
        inventory = updateInventoryValue(inventory, key, body[key])
      }
      if (smp) {
        inventory.smp = smps[smp.toUpperCase()];
      }
      if (batch) {
        // Invalidate redis for getParamsInRange caching
        redisDelMatch('type*batch');
        const oldBatch = inventory.batch;
        // Remove from old batch
        if (oldBatch) {
          await oldBatch.update({
            $pull: {
              inventories: inventory._id
            }
          });
        }
        // Add to new batch
        await Batch.update({_id: batch}, {
          $addToSet: {
            inventories: inventory._id
          }
        });
        // Update inventory batch
        inventory.batch = batch;
      }
      const newCard = inventory.card.toObject();
      // Only update card if necessary
      if (!_.isEqual(oldCard, newCard)) {
        await inventory.card.save();
      }
      if (body.clear) {
        _.forEach(body.clear, (val, key) => {
          if (val) {
            inventory[key] = null;
          }
        });
      }
      await inventory.save();
    }
    // Send notification
    if (typeof cqAch !== 'undefined' || activityStatus === 'sentToSmp') {
      for (const id of ids) {
        const card = await Card.findOne({inventory: id}).populate('inventory');
        if (card && card.inventory) {
          try {
            let url: string = null;
            if (card.inventory.callbackUrl) {
              url = card.inventory.callbackUrl;
            }
            if (!url) {
              const log = await BiRequestLog.findOne({number: card.number, pin: card.pin, retailerId: card.retailer});
              if (log && log.callbackUrl) {
                url = log.callbackUrl;
              }
            }
            if (url) {
              const callback = new Callback(card.inventory.callbackUrl);
              await callback.sendCallback(card, card.inventory.cqAch ? 'cqPaymentInitiated' : 'cardFinalized');
            }
          } catch (e) {
            console.log('**************NOTE CALLBACK ERROR**********');
            console.log(e);
          }
        }
      }
    }
    res.json();
  })
  .catch(async err => {
    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'updateDetails',
      controller: 'card.controller',
      stack: err ? err.stack : null,
      error: err,

    });
    console.log('**************ERR IN CHANGE SMP**********');
    console.log(err);
    return res.status(500).json(err);
  });
}

/**
 * Upload cards
 */
export function uploadCards(req, res) {
  const file = req.files[0];
  const cards = [];
  const body = req.body;
  const fileName = `${__dirname}/uploads/${file.filename}`;
  const stream = fs.createReadStream(fileName);
  const user = req.user;
  let cardCount = 0;
  const csvStream = csv()
    .on("data", function(record){
      if (cardCount === 0) {
        cardCount++;
        return;
      }
      /**
       * Fields:
       * 1) Retailer ID (either BI, GS, or GCMGR)
       * 2) Merchant name
       * 3) Card number
       * 4) Card pin
       * 5) Balance
       */
      // Create record
      const thisRecord = {
        retailerId: record[0],
        retailerName: record[1],
        number: record[2]
      };
      if (typeof record[3] !== 'undefined' && record[3]) {
        thisRecord['pin'] = record[3];
      }
      if (typeof record[4] !== 'undefined' && record[4]) {
        let balance: any = record[4];
        if (typeof balance === 'string') {
          thisRecord['balance'] = stripDollarSign(record[4]);
        }
      }
      cards.push(thisRecord);
      cardCount++;
    })
    .on('end', () => {
      const promises = [];
      cards.forEach(thisCard => {
        promises.push(// let dbRetailer;
          new Promise(resolve => {
            // Find retailer by ID
            if (/^[0-9a-fA-F]{24}$/.test(thisCard.retailerId)) {
              return resolve(Retailer.findById(thisCard.retailerId));
            } else {
              return resolve(Retailer.findOne({
                $or: [{gsId: thisCard.retailerId}, {retailerId: thisCard.retailerId}]
              }));
            }
          })
            .then(retailer => {
              return new Promise(resolve => {
                createDefaultCustomer(body.customer, user)
                  .then(customer => {
                    resolve({
                      retailer,
                      customer
                    });
                  })
              });
            })
            .then((data: any) => {
              return new Promise(resolve => {
                findCards(data.retailer._id, thisCard.number, thisCard.pin)
                  .then(card => {
                    // dbCustomer = data.customer;
                    resolve({
                      card,
                      customer: data.customer,
                      retailer: data.retailer
                    });
                  })
              });
            })
            .then((data: any) => {
              if (data.card) {
                console.log('**************CARD ALREADY EXISTS DURING UPLOAD**********');
                console.log(data.card);
              } else {
                return data;
              }
            })
            .then(data => {
              if (!data) {
                return;
              }
              const newCard = <any>new Card(thisCard);
              newCard.user = user._id;
              newCard.balanceStatus = 'unchecked';
              // User time when newCard was created
              newCard.userTime = Date.now();
              newCard.customer = data.customer;
              newCard.retailer = data.retailer._id;
              newCard.uid = data.retailer.uid;
              // Save
              return newCard.save()
            })
            .then(newCard => {
              if (!newCard) {
                return;
              }
              // Retrieve card with retailer
              return Card.findById(newCard._id)
                .populate({
                  path: 'retailer',
                  populate: {
                    path: 'buyRateRelations',
                    model: 'BuyRate'
                  }
                })
                .populate('customer');
            })
            // Return
            .then(newCard => {
              if (!newCard) {
                return;
              }
              return new Promise(resolve => {
                Company.findById(user.company)
                  .populate({
                    path: 'settings',
                    populate: {
                      path: 'autoBuyRates',
                      model: 'AutoBuyRate'
                    }
                  })
                  .then(company => {
                    resolve({
                      company,
                      card: newCard
                    });
                  });
              });
            })
            // Get card buy and sell rate
            .then((data: any) => {
              if (!data) {
                return;
              }
              const retailer = retailerSetBuyAndSellRates(data.card.retailer, data.company.settings, user.store, null, data.card.merchandise);
              data.card.buyRate = retailer.buyRate;
              data.card.sellRate = retailer.sellRate;
              return data.card.save();
            })
            .catch(err => {
              console.log('**************UPLOAD ERR**********');
              console.log(err);
            }));
      });
      Promise.all(promises)
        .then(() => res.json());
    });

  stream.pipe(csvStream);
}

/**
 * Run BI
 */
export function runBi(req, res) {
  const cards = req.body.cards;
  const dbCards = [];
  cards.forEach(card => {
    dbCards.push(Card.findById(card)
      .populate('retailer'));
  });
  Promise.all(dbCards)
    .then(foundCards => {
      let currentCard = 0;
      const thisInt = setInterval(async () => {
        const dbCard = foundCards[currentCard];
        currentCard++;
        if (!dbCard) {
          clearInterval(thisInt);
          return res.json();
        }
        let retailerId;
        retailerId = dbCard.retailer.gsId || dbCard.retailer.aiId;
        if (retailerId) {
          const biRequestLog = await BiRequestLog.findOne({number: dbCard.number, pin: dbCard.pin, retailerId});
          let requestId: string;
          if (biRequestLog) {
            requestId = biRequestLog.requestId;
          }
          const biParams: IBalanceInquiryParams = {
            retailerId,
            number: dbCard.number,
            pin: dbCard.pin,
            cardId: dbCard._id.toString(),
            userId: req.user._id.toString(),
            companyId: req.user.company.toString(),
            requestId
          };
          balanceInquiry(biParams);
        }
      }, 500);
    });
}

/**
 * Move cards over to Upload Sales for sale
 */
export function moveForSale(req, res) {
  let dbCustomer;
  Customer.findById('5764baef5f244aff7abe6160')
  .then(customer => {
    if (!customer) {
      throw new Error('noCustomer');
    }
    dbCustomer = customer;
    return Card.find({
      balance: {$exists: true},
      customer: req.body.customerId
    })
    .populate('retailer');
  })
  .then(cards => {
    const cardPromises = [];
    cards.forEach(card => {
      let sellRate, buyRate;
      try {
        if (card.sellRate) {
          sellRate = card.sellRate;
        } else {
          sellRate = card.retailer.sellRates.best - 0.03;
        }
        if (card.buyRate) {
          buyRate = card.buyRate;
        } else {
          buyRate = card.retailer.sellRates.best - 0.1;
        }
      } catch (e) {
        throw new Error('noSellRate');
      }
      cardPromises.push(card.update({
        sellRate,
        buyRate,
        customer: dbCustomer._id
      }));
    });
    return Promise.all(cardPromises);
  })
  .then(() => {
    return res.json();
  })
  .catch(err => {
    console.log('**************ERR**********');
    console.log(err);
    if (err && err.message === 'noCustomer') {
      return res.status(500).json({customer: false});
    }
    if (err && err.message === 'noSellRate') {
      return res.status(500).json({sellRate: false});
    }
  });
}

/**
 * Perform balance update for a single card
 * @param cardId
 * @param balance
 * @param userRole
 */
function updateInventoryBalance(cardId, balance) {
  return Inventory.findById(cardId)
    .populate('card')
    .then(async inventory => {
      if (!inventory) {
        return null;
      }
      inventory.balance = balance;

      if (inventory.type.toLowerCase() === 'disabled' && inventory.soldToLiquidation === true) {
        if (inventory.originalType) {
          inventory.type = inventory.originalType;
          inventory.soldToLiquidation = false;
          inventory.status = 'SALE_NON_API';
        }
      }

      return inventory.save();
    })
    .then(inventory => {
      if (!inventory) {
        return null;
      }
      return Card.update({
        _id: inventory.card._id
      }, {
        $set: {
          balance
        }
      });
    });
}

/**
 * Edit card balance (admin)
 */
export function editBalance(req, res) {
  const {cardId, balance, ids} = req.body;
  if (cardId) {
    return updateInventoryBalance(cardId._id, balance)
    .then(() => res.json())
    .catch(async err => {
      await ErrorLog.create({
        body: req.body ? req.body : {},
        params: req.params ? req.params : {},
        method: 'editBalance',
        controller: 'card.controller',
        stack: err ? err.stack : null,
        error: err,

      });
    });
  } else if (ids) {
    const promises = [];
    ids.forEach(id => {
      promises.push(updateInventoryBalance(id, balance));
    });
    Promise.all(promises)
    .then(() => res.json())
    .catch(async err => {
      await ErrorLog.create({
        body: req.body ? req.body : {},
        params: req.params ? req.params : {},
        method: 'editBalance',
        controller: 'card.controller',
        stack: err ? err.stack : null,
        error: err,

      });
    });
  }
}

/**
 * Get inventory fr
 * @param cardId
 * @return {Promise.<void>}
 */
async function getInventoryFromCard(cardId) {
  return Card.findById(cardId).populate('inventory');
}

/**
 * Update BI Request Log when balance is initially set
 * @param {IInventory} inventory
 * @param {ICard} card
 * @returns {Promise<IBiRequestLog>}
 */
async function updateLogBalance(inventory: IInventory, card: ICard) {
  let log = await BiRequestLog.findOne({number: card.number, pin: card.pin, retailerId: card.retailer});
  log.balance = inventory.verifiedBalance;
  return await log.save();
}

/**
 * Send a callback if the balance is set for the first time
 * @param {IInventory} inventory
 * @returns {Promise<void>}
 */
async function biCompleteCallback(inventory: IInventory) {
  if (inventory.card) {
    const card: ICard = await Card.findById(inventory.card);
    if (card) {
      const log = await updateLogBalance(inventory, card);
      sendCallbackFromCompanySettingsOrDirectUrl(log, log.user.toString())
    }
  }
}

/**
 * Set inventory ship status
 */
export async function setCardValue(req, res) {
  const {status, type, transaction, cardId} = req.body;
  let inventoryId = req.body.inventoryId;
  const {companyId} = req.params;
  // Staging testing
  if (config.isStaging) {
    const card = await getInventoryFromCard(cardId);
    if (card) {
      try {
        inventoryId = card.inventory._id.toString();
      } catch (e) {
        console.log('**************IGNORE**********');
      }
    }
  }
  return new Promise((resolve, reject) => {
    // Corporate
    if (companyId) {
      Inventory.findById(inventoryId)
        .populate('company')
        .then(inventory => {
          if (inventory.company._id.toString() !== companyId) {
            return reject();
          }
          // Modify transaction
          if (transaction) {
            inventory.transaction[type] = status;
          } else {
            inventory[type] = status;
          }
          resolve(inventory.save());
        });
    // Admin
    } else {
      let promises = [];
      Promise.all(promises)
      .then(() => {
        Inventory.findById(inventoryId)
        .then(async inventory => {
          const previousVb = inventory.verifiedBalance;
          // Delete so the inventory will show up in blank searches
          if (type === 'activityStatus' && status === '-') {
            inventory.activityStatus = undefined;
          } else {
            inventory[type] = status;
          }
          // Send VB callback
          if (type === 'verifiedBalance' && (previousVb === null || typeof previousVb === 'undefined')) {
            await biCompleteCallback(inventory);
          }
          resolve(inventory.save());
        });
      });
    }
  })
    .then(async () => {
      if (type === 'activityStatus' && status === 'sentToSmp') {
        Card.findOne({inventory: inventoryId})
        .populate('inventory')
        .then(card => {
          if (card) {
            (new Callback()).sendCallback(card, 'cardFinalized');
          }
        });
      }

      if (type === 'verifiedBalance') {
        const inventory = await Inventory.findById(inventoryId);

        if (inventory.type.toLowerCase() === 'disabled' && inventory.soldToLiquidation === true) {
          if (inventory.originalType) {
            inventory.type = inventory.originalType;
            inventory.soldToLiquidation = false;
            inventory.status = 'SALE_NON_API';
            await inventory.save();
          }
        }
      }

      res.json();
    })
    .catch(async err => {
      await ErrorLog.create({
        body: req.body ? req.body : {},
        params: req.params ? req.params : {},
        method: 'setCardValue',
        controller: 'card.controller',
        stack: err ? err.stack : null,
        error: err,

      });
      console.log('**************UNABLE TO SET CARD VALUE**********');
      console.log(err);
      return res.status(500).json(err);
    });
}

/**
 * Mass update inventories
 */
export function massUpdate(req, res) {
  const {ids, values} = req.body;
  const {companyId} = req.params;
  const updateParams: IMassUpdateParams = {
    '_id': {$in: ids}
  };
  if (companyId) {
    updateParams.company = companyId;
  }
  Inventory.update(updateParams, {
    $set: values
  }, {multi: true})
    .then(inventories => res.json(inventories))
    .catch(async err => {
      await ErrorLog.create({
        body: req.body ? req.body : {},
        params: req.params ? req.params : {},
        method: 'massUpdate',
        controller: 'card.controller',
        stack: err ? err.stack : null,
        error: err,

      });
      console.log('**************ERR IN MASS UPDATE**********');
      console.log(err);
      return res.status(err).json(err);
    });
}

/**
 * Remove a rejection or credit from a customer record
 * @param type "rejections" or "credits"
 * @param customerUpdates Updated for current customer
 * @param customerId
 * @param inventoryId
 * @return {*}
 */
function removeRejectionOrCredit(type = 'rejections', customerUpdates, customerId, inventoryId) {
  if (customerUpdates[customerId][type].indexOf(inventoryId) !== -1) {
    customerUpdates[customerId][type].splice(
      customerUpdates[customerId][type].indexOf(inventoryId),
      1
    );
  }
  return customerUpdates;
}

/**
 * Handle rejection of inventory
 * @param inventory Inventory record
 * @param customerUpdates Customer updates to make
 * @return {Promise.<*>}
 */
async function handleInventoryReject(inventory, customerUpdates: IRejectCustomerUpdateArray) {
  const defaultBuyRate = 0.9;
  const defaultSellRate = 0.1;
  const customerId = inventory.customer._id;
  // Denote whether an inventory was rejected or created or neither
  let adjustmentType: string = null;
  if (!customerUpdates[customerId]) {
    customerUpdates[customerId] = {
      credits: Array.isArray(inventory.customer.credits) ? inventory.customer.credits : [],
      rejections: Array.isArray(inventory.customer.rejections) ? inventory.customer.rejections : [],
      amount: typeof inventory.customer.rejectionTotal === 'number' ? inventory.customer.rejectionTotal : 0
    };
  }
  // Set rejection amount based on difference between paid and what should have been paid
  if (typeof inventory.verifiedBalance === 'number') {
    // Original buy amount
    let buyAmount = inventory.buyAmount;
    // Assume 10% for API, which has a bug until recently which didn't set buyAmount
    if (!buyAmount) {
      buyAmount = inventory.balance * defaultBuyRate;
    }
    let buyRate = inventory.buyRate > 1 ? inventory.buyRate / 100 : inventory.buyRate;

    if (inventory.isApi) {
      buyRate = inventory.card.sellRate - defaultSellRate;
    }

    // Buy amount after adjustment
    const realBuyAmount = buyRate * inventory.verifiedBalance;

    // Undo any previous rejection
    const undoRejectionAndCredit = inventory.balance === inventory.verifiedBalance;

    if (realBuyAmount !== buyAmount) {
      // Reset amount of previous rejection
      if (inventory.rejected && inventory.rejectAmount) {
        customerUpdates[customerId].amount = customerUpdates[customerId].amount - inventory.rejectAmount;
      }

      // Reset amount of previous credit
      if (inventory.credited && inventory.creditAmount) {
        customerUpdates[customerId].amount = customerUpdates[customerId].amount + inventory.creditAmount;
      }

      const deltaAmount = buyAmount - realBuyAmount;
      customerUpdates[customerId].amount += deltaAmount;

      // Undo previous rejection/credit
      if (undoRejectionAndCredit) {
        customerUpdates = removeRejectionOrCredit('rejections', customerUpdates, customerId, inventory._id);
        customerUpdates = removeRejectionOrCredit('credits', customerUpdates, customerId, inventory._id);
      } else {
        // Rejection
        if (deltaAmount > 0) {
          // Add to rejection list
          if (customerUpdates[customerId].rejections.indexOf(inventory._id) === -1) {
            customerUpdates[customerId].rejections.push(inventory._id);
          }
          // Remove from credit list
          customerUpdates = removeRejectionOrCredit('credits', customerUpdates, customerId, inventory._id);
          // Credit
        } else if (deltaAmount < 0) {
          // Add to credit list
          if (customerUpdates[customerId].credits.indexOf(inventory._id) === -1) {
            customerUpdates[customerId].credits.push(inventory._id);
          }
          // Remove from rejection list
          customerUpdates = removeRejectionOrCredit('rejections', customerUpdates, customerId, inventory._id);
        }
      }

      inventory.rejected = !undoRejectionAndCredit && deltaAmount > 0;
      inventory.rejectedDate = inventory.rejected ? Date.now() : null;
      inventory.rejectAmount = inventory.rejected ? deltaAmount : null;
      inventory.credited = !undoRejectionAndCredit && deltaAmount < 0;
      inventory.creditedDate = inventory.credited ? Date.now() : null;
      inventory.creditAmount = inventory.credited ? Math.abs(deltaAmount) : null;
      if (deltaAmount !== 0) {
        adjustmentType = inventory.rejected ? 'denial' : 'credited';
        inventory.adjustmentStatus = adjustmentType;
      }
      await inventory.save();
    }
  }
  return {customerUpdates, adjustmentType};
}

/**
 * Adjusts selected inventories
 */
export async function adjustments(req, res) {
  try {
    const {inventories: ids, action} = req.body;

    if (!['adjustment', 'chargeback'].includes(action)) {
      return res.status(400).json({});
    }

    let customerUpdates = {};
    const inventories = await Inventory.find({
      _id: {
        $in: ids
      }
    })
    .populate('customer')
    .populate('card')
    .populate('retailer');

    const companySettings = {};

    // Handle reject on each inventory
    // Send callbacks for credit/rejection
    for (let inventory of inventories) {
      const rejectedResponse = await handleInventoryReject(inventory, customerUpdates);
      customerUpdates = rejectedResponse.customerUpdates;
      const adjustmentType: string = rejectedResponse.adjustmentType;
      if (inventory.card && inventory.isTransaction) {
        await recalculateTransactionAndReserve(inventory);
      }

      if (!companySettings[inventory.company.toString()]) {
        const company = await Company.findById(inventory.company);
        companySettings[inventory.company.toString()] = await company.getSettings();
      }

      let callbackType = null;

      if (typeof inventory.verifiedBalance === 'number') {
        inventory.adjustmentStatus = inventory.verifiedBalance > inventory.balance ? 'credit' : 'denial';
        callbackType = inventory.adjustmentStatus;
      }

      let sendChargeback: boolean = false;
      if (action === 'chargeback') {
        sendChargeback = companySettings[inventory.company.toString()].enableChargebackCallback;
        inventory.adjustmentStatus = 'chargeback';
        callbackType = 'chargeback';
      } else if (action === 'adjustment' && adjustmentType) {
        inventory.adjustmentStatus = adjustmentType;
        callbackType = adjustmentType;
      }

      await inventory.save();

      if (typeof callbackType === 'string') {
        await sendAdjustmentCallback(
          inventory,
          sendChargeback
        );
      }
    }

    for (const [id, update] of Object.entries(customerUpdates)) {
      const thisUpdate: any = update;
      await Customer.update({
        _id: id
      }, {
        $set: {
          rejectionTotal: thisUpdate.amount,
          rejections: thisUpdate.rejections,
          credits: thisUpdate.credits
        }
      });
    }

    return res.json({});
  } catch (err) {
    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'adjustments',
      controller: 'card.controller',
      stack: err ? err.stack : null,
      error: err,

    });
    console.log('**************ERR IN ADJUSTMENTS**********');
    console.log(err);
    return res.status(500).json({});
  }
}

export async function reject(req, res) {
  try {
    const {inventories: ids} = req.body;

    await Inventory.update({
      _ids: {
        $in: ids
      }
    }, {
      $set: {
        // cqPaid: 0,
        liquidationSoldFor: 0,
        serviceFee: 0,
        // netAmount: 0,
        activityStatus: 'rejected'
    }});

    return res.json({});
  } catch (err) {
    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'reject',
      controller: 'card.controller',
      stack: err ? err.stack : null,
      error: err,

    });
    console.log('**************ERR ADDING REJECTIONS**********');
    console.log(err);
    return res.status(500).json({});
  }
}

/**
 * Resell cards which have not already been sent to an SMP to determine new best rates
 */
export function resellCards(req, res) {
  const {inventories} = req.body;
  // Find inventories not sent to SMP, and without a transaction ID
  Inventory.find({
    _id: {$in: inventories}
  })
  .populate('card')
  .then(inventories => {
    const promises = [];
    inventories.forEach(inventory => {
      // Don't resell already sold cards
      if (inventory.smp !== '1' && inventory.smp !== 'saveya' &&
          ['sentToSmp', 'receivedSmp', 'rejected'].indexOf(inventory.activityStatus) === -1 && inventory.card) {
        inventory.soldToLiquidation = false;
        promises.push(inventory.save());
      }
    });
    return Promise.all(promises);
  })
  .then(() => res.json())
  .catch(err => {
    console.log('**************ERR IN RESELL CARDS**********');
    console.log(err);
    return res.status(500).json();
  });
}
