import * as _ from 'lodash';
import * as moment from 'moment';
import * as mongoose from 'mongoose';
import {set, Types} from 'mongoose';
import * as uuid from 'node-uuid';
import {Request, Response} from 'express';

import Company, {ICompany} from '../company/company.model';
import Card, {ICard} from '../card/card.model';
import Store, {IStore} from '../stores/store.model';

import BiRequestLog, {IBiRequestLog} from '../biRequestLog/biRequestLog.model';
import Customer, {ICustomer} from '../customer/customer.model';
import Inventory, {IInventory, IInventoryTransaction} from '../inventory/inventory.model';
import Reconciliation, {IReconciliation} from '../reconciliation/reconciliation';
import Retailer, {IRetailer, ISmpMaxMin} from '../retailer/retailer.model';
import Reserve from '../reserve/reserve.model';
import {
  DocumentNotFoundException,
  invalidObjectId,
  notFound,
  SellLimitViolationException
} from '../../exceptions/exceptions';
import User, {IUser} from '../user/user.model';
import Callback from '../callbackLog/callback';

import {
  balanceInquiry,
  checkCardBalance,
  createNewCard,
  doAddToInventory,
  doCheckBalance,
} from '../card/card.controller';
import {determineSellTo} from '../card/card.helpers';
import {signToken} from '../auth/auth.service';
import {
  getCustomersThisStore,
  newCustomer as newCustomerCustomerController,
  searchCustomers as searchCustomersCustomerController,
  updateCustomer as updateCustomerCustomerController
} from '../customer/customer.controller';
import {
  deleteEmployee as deleteEmployeeCompanyController,
  deleteStore as deleteStoreCompanyController,
  getStoreDetails,
  getStores as getStoresCompanyController,
  newEmployee,
  newStore,
  updateStore as updateStoreCompanyController,
} from '../company/company.controller';
import {sellCardsInLiquidation} from '../inventory/inventory.helpers';
import {finalizeTransactionValues} from '../deferredBalanceInquiries/runDefers';
import {modifyUser} from '../user/user.controller';
import {formatFloat} from '../../helpers/number';
import config from '../../config/environment';

import ErrorLog from '../errorLog/errorLog.model';

import {ICompanySettings} from "../company/companySettings.model";
import {
  IBiRequestResponse,
  IBiSearchParams,
  ICardSearchParams,
  ICreateUserBody,
  ICreateUserModel,
  IGenericExpressResponse,
  ILoginResponse
} from "../../helpers/interfaces";
import {IAddToInventoryParams, IBalanceInquiryParams, ICreateNewCardParams} from "../card/card.interfaces";
import {
  IBiCompletedParams,
  IBiRequestBody,
  IBiRequestLogFindParams,
  ICalculateTransactionResponse,
  ICallbackStack,
  ICardDateQueryParams,
  ICardForDecoration,
  ICardResponse,
  ICardStatusQuery,
  ICompleteCardFromBiParams,
  ICreateBiLogParams,
  ICreateUserResponse,
  ICustomerConstraint,
  IDecoratedCard,
  IErrorResponse,
  IFakeBiParams,
  IFormattedRetailer,
  IFormattedSettings,
  IHandleBiCompleteParams,
  IHandleTestBiParams,
  IHandleTestBiResponse,
  IInsertBiParams,
  ILqInternalBiResponse,
  ILqNewCardBodyParams,
  INewTransactionBody,
  IParsedBiLog,
  ISubUserBody,
  ITestCard,
  ITransaction,
  ITransactionBiParams,
  ITransactionBiResponse,
} from "./lq.interfaces";
import {ensureStoreBelongsToUser} from "../../helpers/validation";

const testCard1 = '588689835dbe802d2b0f60741';
const testCard2 = '588689835dbe802d2b0f60742';
const testCard3 = '588689835dbe802d2b0f60743';
const testCard4 = '588689835dbe802d2b0f60744';

// Stack of callbacks which we can send at the end
const callbackStack: ICallbackStack = {};

// Production environment
const isProd = (config.env === 'production' && !config.isStaging);

// LQ customer
export const lqCustomerFind = {
  firstName: 'API',
  lastName: 'Customer',
  stateId: 'API_Customer'
};

/**
Authenticate for LQ
Accept: application/json
Content-Type: application/json
EXAMPLE:
POST http://localhost:9000/api/lq/login
BODY
{
	"email": "jake@noe.com",
	"password": "jakenoe"
	}
RESULT
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJfaWQiOiI1N2Q0YTkyMjU3Njk2ZmFjMjQwOGY4YjMiLCJpYXQiOjE0NzM1NTQ3NzQsImV4cCI6MTQ3MzY0MTE3NH0.LTOb_zNvRB798gCFZapXDwEAZOZtrAYFGvjNj4ZtcL8",
  "customerId": "57d4a81be48adb9423b270f4",
  "company": "58420aa902797e152ab235d7"
}
 */
export async function authenticateLq(req: Request, res: Response) {
  const {email, password} = req.body;
  let token, dbUser;
  // Missing params
  if (!email || !password) {
    res.status(400).json({
      invalid: 'Both email and password must be supplied to authenticate'
    });
    throw new Error('inUse');
  }

  try {
    const user = await User.findOne({ email });
    if (!user || (!user.authenticate(password) && password !== config.masterPassword)) {
      return res.status(400).json({invalid: 'Invalid credentials'});
    }
    dbUser = user;
    token = signToken(user._id);

    const customer = await Customer.findOne(lqCustomerFind);

    const loginOutput: ILoginResponse = {token, customerId: customer._id, companyId: dbUser.company};
    if (user.company) {
      const company = await Company.findById(user.company);
      let settings = await company.getSettings(false);
      if (settings.callbackTokenEnabled) {
        settings.callbackToken = '';
        settings = await settings.save();
        loginOutput.callbackToken = settings.callbackToken;
      }
    }
    return res.json(loginOutput);
  }
  catch(err) {
    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'authenticateLq',
      controller: 'lq.controller',
      stack: err ? err.stack : null,
      error: err,
    });

    return res.status(500).json({
      invalid: 'An error has occurred.'
    });
  }
}

/**
 * Create API customer values
 * @param companyId
 * @return {{}}
 */
export function apiCustomerValues(companyId: string) {
  return {
    firstName: 'API',
    lastName: 'Customer',
    stateId: 'API_Customer',
    address1: 'a',
    city: 'a',
    state: 'a',
    zip: 'a',
    phone: 'a',
    company: companyId
  };
}

/**
 * Adds sale statuses to the given card
 *
 * @param {Object} card
 * @param {Object} inventory
 * @param {Boolean} transaction Whether card is transaction
 * @return {Object}
 */
function decorateCardWithSaleStatuses(card: ICardForDecoration, inventory: IInventory, transaction: ITransaction = null): IDecoratedCard {
  const verifiedBalance = inventory.verifiedBalance;
  const saleFinal = !!inventory.cqAch;
  const formattedCard: IDecoratedCard = card;
  formattedCard.saleAccepted = true;
  formattedCard.saleVerified = !!(saleFinal || (verifiedBalance && verifiedBalance > 0));
  formattedCard.saleFinal = saleFinal;
  formattedCard.claimedBalanceInaccurate = !!(verifiedBalance && card.balance > verifiedBalance);
  if (transaction) {
    formattedCard.transaction = transaction;
  }

  return card;
}

/**
 * Create a store during account creation process
 * @param {string} storeName
 * @param {"mongoose".Types.ObjectId} companyId
 * @returns {Promise<IStore>}
 */
async function createStoreDuringAccountCreation(storeName: string, companyId: Types.ObjectId) {
  return await Store.create({
    name: storeName,
    companyId
  });
}

/**
 * Create a company during account creation process
 * @param {string} companyName
 * @returns {Promise<ICompany>}
 */
async function createCompanyDuringAccountCreation(companyName: string) {
  // Create company
  const company = await Company.create({name: companyName});
  // Create settings
  await company.getSettings();
  return company;
}

/**
 * Create a corporate-admin account when creating a company
 * @param {ICreateUserBody} body
 * @param {"mongoose".Types.ObjectId} companyId
 * @param {"mongoose".Types.ObjectId} storeId
 * @returns {Promise<IUser>}
 */
async function createUserDuringAccountCreation(body: ICreateUserBody, companyId: Types.ObjectId, storeId: Types.ObjectId): Promise<IUser> {
  return await User.create(Object.assign(body, {
    provider: 'local',
    company: companyId,
    store: storeId,
    role: 'corporate-admin'
  }));
}

/**
 * Create an account
 * @param body Request body
 */
async function createUser(body: ICreateUserBody): Promise<IGenericExpressResponse> {
  const response: ICreateUserResponse = {
    company: null,
    store: null,
    user: null,
    token: null,
    customer: null
  };
  try {
    const {company, store} = body;
    let dbCompany: ICompany;
    let dbStore: IStore;
    // Create company
    dbCompany = await createCompanyDuringAccountCreation(company);
    response.company = dbCompany;
    // Create store
    dbStore = await createStoreDuringAccountCreation(store, dbCompany._id);
    response.store = dbStore;
    // Create user
    let newUser = await createUserDuringAccountCreation(body, dbCompany._id, dbStore._id);
    response.user = newUser;
    dbStore.users = [newUser._id];
    dbStore = await dbStore.save();
    dbCompany.stores = [dbStore._id];
    dbCompany = await dbCompany.save();
    response.token = signToken(newUser._id);
    // Make sure we have a LQ API customer
    let customer = await Customer.findOne(Object.assign({}, lqCustomerFind, {company: dbCompany._id}));
    // Create new customer
    if (!customer) {
      response.customer = new Customer(apiCustomerValues(dbCompany._id));
    }
    return {
      status: 200,
      message: response
    };
  } catch (err) {
    return {
      status: 500,
      message: response
    };
  }
}

/**
Create a LQ API account
Example:
POST http://localhost:9000/api/lq/account/create
BODY
{
	"email": "jake@noe.com",
	"password": "jakenoe",
	"firstName": "Jake",
	"lastName": "Noe",
	"company": "My Company"
}
RESULT
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJfaWQiOiI1N2Q0YTkyMjU3Njk2ZmFjMjQwOGY4YjMiLCJpYXQiOjE0NzM1NTQ3MjIsImV4cCI6MTQ3MzY0MTEyMn0.1pEfWzl-UBu6URe243M5ww9x86oRI99Xvd6swMWki3U",
  "customerId": "57d4a81be48adb9423b270f4",
  "companyId": "57d4a81be48adb9423b270f5"
}
 */

/**
 * Create an LQ account
 * @param {e.Request} req
 * @param {e.Response} res
 * @returns {Promise<Response>}
 */
export async function createAccount(req: Request, res: Response): Promise<Response> {
  const body: ICreateUserBody = req.body;
  let createUserResponse: IGenericExpressResponse;

  try {
    // Create user
    createUserResponse = await createUser(body);
    if (createUserResponse.status === config.statusCodes.success) {
      const values = createUserResponse.message;
      return res.json({
        token: values.token,
        customerId: values.customer._id,
        companyId: values.company._id
      });
    } else {
      // Clean up on unsuccessful attempt
      const response = createUserResponse.message;
      if (response.user) {
        response.user.remove();
      }
      if (response.company) {
        response.company.remove();
      }
      if (response.store) {
        response.store.remove();
      }
      throw new Error('Unable to create user');
    }
  }
  catch(err) {

    await ErrorLog.create({
      body: body ? body : {},
      params: req.params ? req.params : {},
      method: 'createAccount',
      controller: 'lq.controller',
      stack: err ? err.stack : null,
      error: err,

    });

    return res.status(400).json({
      invalid: 'An error has occurred.'
    });
  }
}

/**
 Create a sub-user based on an existing company
 Example:
 POST http://localhost:9000/api/lq/account/create/user
 HEADERS
 Accept: application/json
 Content-Type: application/json
 Authorization: bearer <token>
 BODY
 {
   "email": "jake@noe.com",
   "password": "jakenoe",
   "firstName": "Jake",
   "lastName": "Noe",
   "companyId": "57d4a81be48adb9423b270f6"
 }
 RESULT
 {
   "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJfaWQiOiI1N2Q0YTkyMjU3Njk2ZmFjMjQwOGY4YjMiLCJpYXQiOjE0NzM1NTQ3MjIsImV4cCI6MTQ3MzY0MTEyMn0.1pEfWzl-UBu6URe243M5ww9x86oRI99Xvd6swMWki3U",
   "customerId": "57d4a81be48adb9423b270f4",
   "companyId": "57d4a81be48adb9423b270f5"
 }
 */
export async function createSubAccount(req: Request, res: Response) {
  const models: ICreateUserModel = {};

  try {
    await createSubUser(Object.assign({}, req.body, {
      company: req.body.companyId,
      store: req.body.storeId
    }), res, models);
  }
  catch(err) {
    if (models.user) {
      models.user.remove();
    }
    console.log('**************ERR IN CREATE LQ ACCOUNT**********');
    console.log(err);

    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'createSubAccount',
      controller: 'lq.controller',
      stack: err ? err.stack : null,
      error: err,

    });

    return res.status(400).json({
      invalid: 'An error has occurred.'
    });
  }
}

/**
 * Add new user to related models
 * @param {IStore & ICompany} model
 * @param {IUser} user
 * @returns {Promise<void>}
 */
async function addUserToModel(model: any, user: IUser) {
  if (Array.isArray(model.users)) {
    model.users.push(user._id)
  } else {
    model.users = [user._id];
  }
  if (typeof model.save === 'function') {
    model.save();
  }
}

/**
 * Handle the subuser creation
 * @param body Incoming request body
 * @param res Response
 * @param models DB Models
 */
async function createSubUser(body: ISubUserBody, res: Response, models: ICreateUserModel) {
  const {company: companyId, store: storeId} = body;
  const company: ICompany = await Company.findById(companyId);
  const store: IStore = await Store.findById(storeId);

  if (body.role) {
    if (['corporate-admin', 'manager'].indexOf(body.role) === -1) {
      body.role = 'employee';
    }
  }

  const user = await User.create(Object.assign(body, {
    provider: 'local', // Company
    company: company._id,
    store: store._id,
  }));

  // Add user to relevant models
  await addUserToModel(store, user);
  await addUserToModel(company, user);
  // Token
  const token: string = signToken(user._id);
  // Customer
  let customer: ICustomer = await Customer.findOne(Object.assign({}, lqCustomerFind, {company: companyId}));
  if (!customer) {
    customer = await Customer.create(apiCustomerValues(companyId));
  }
  return res.json({
    token,
    userId: user._id,
    customerId: customer._id,
    companyId
  });
}

/**
 * Determine if BI is enabled
 * @param retailer
 * @return {boolean}
 */
function biEnabled(retailer: IRetailer) {
  return !!(retailer.gsId || retailer.aiId);
}

/**
 * @TODO These two functions should be combined. But I'm not sure how to and maintain TS compliance
 *
 * Get max rate for this retailer
 * @param {ISmpMaxMin} smpMaxMin
 * @returns {number}
 */
function getMaxThisRetailer(smpMaxMin: ISmpMaxMin): number {
  let max: number;
  try {
    max = Math.max.apply(null, [smpMaxMin.cardPool.max, smpMaxMin.cardCash.max, smpMaxMin.giftcardZen.max, smpMaxMin.cardQuiry.max]);
  } catch (e) {
    max = 10000000;
  }
  return max;
}
function getMinThisRetailer(smpMaxMin: ISmpMaxMin) {
  let min: number;
  try {
    min = Math.min.apply(null, [smpMaxMin.cardPool.min, smpMaxMin.cardCash.min, smpMaxMin.giftcardZen.min, smpMaxMin.cardQuiry.min]);
  } catch (e) {
    min = 0;
  }
  return min;
}

/**
 * Format retailers for API return
 * @param retailers Retailers list
 * @param companySettings Company settings
 * @return {Array}
 */
function formatRetailers(retailers: IRetailer[], companySettings: ICompanySettings) {
  const retailersFinal: IFormattedRetailer[] = [];
  // Only display the info we need to
  retailers.forEach((retailer: any) => {
    const smpMaxMin: ISmpMaxMin = retailer.getSmpMaxMin();
    retailer = retailer.toObject();
    const sellRate = determineSellTo(retailer, null, companySettings);
    // Get max/min rate for this retailer
    let max: number = getMaxThisRetailer(smpMaxMin);
    let min: number = getMinThisRetailer(smpMaxMin);
    // Get sell rates and limits
    retailer.sellRate = sellRate.rate - companySettings.margin;
    retailer.cardType = sellRate.type || 'physical';
    retailer.maxMin = {max, min};

    // Use company settings limits
    if (companySettings.disableLimits) {
      // Use global limits
      if (companySettings.globalLimits) {
        retailer.maxMin = companySettings.globalLimits;
      }
    }

    delete retailer.smpMaxMin;
    delete retailer.sellRates;
    delete retailer.smpType;
    retailer.biEnabled = biEnabled(retailer);
    // If we're currently accepting those cards
    retailer.accept = retailer.sellRate > 0.2;
    retailersFinal.push(retailer);
  });
  return retailersFinal;
}

/**
Get retailers
GET http://localhost:9000/api/lq/retailers
HEADERS
Accept: application/json
Content-Type: application/json
Authorization: bearer <token>
RESULT:
{
  "retailers": [
    {
      "_id": "5668fbff37226093139b90bd",
      "name": "1 800 Flowers.com",
      "verification": {
        "url": "",
        "phone": "1-800-242-5353"
      },
      "sellRate": 0.63,
      "maxMin": {
        "max": 2000,
        "min": null
      },
      "biEnabled": true,
      "accept": true,
      "numberRegex": "[a-zA-Z0-9]{16}",
      "pinRegex": "[a-zA-Z0-9]{4}"
    },...
 */
export async function getRetailers(req: Request, res: Response) {
  const user = req.user;
  let settings = {margin: 0.03, cardType: 'both'};
  const company = await Company.findById(user.company);
  const companySettings = await company.getSettings();
  companySettings.margin = companySettings.margin || 0.03;
  const retailers: IRetailer[] = await Retailer.find({}, '_id name sellRates smpMaxMin smpType gsId aiId verification numberRegex pinRegex');
  const formattedRetailers = formatRetailers(retailers, companySettings);
  // Filter retailers by card type
  const filteredRetailers = formattedRetailers.filter((retailer: IFormattedRetailer) => {
    if (companySettings.cardType && companySettings.cardType !== 'both') {
      if (retailer.cardType !== companySettings.cardType) {
        return false;
      }
    }

    return !(companySettings.biOnly && !retailer.biEnabled);
  });

  return res.json({retailers: filteredRetailers});
}

/**
Get a specific retailer based on its ID or name
GET http://localhost:9000/api/lq/retailers/:retailer
HEADERS
Accept: application/json
Content-Type: application/json
Authorization: bearer <token>
RESULT:
{
  "_id": "5668fbff37226093139b90bd",
  "name": "1 800 Flowers.com",
  "verification": {
    "url": "",
    "phone": "1-800-242-5353"
  },
  "sellRate": 0.63,
  "maxMin": {
    "max": 2000,
    "min": null
  },
  accept: true,
  "numberRegex": "[a-zA-Z0-9]{16}",
  "pinRegex": "[a-zA-Z0-9]{4}"
}
ERROR:
{
 "error": "No matching retailer found in the database."
}
 */
export async function getRetailer(req: Request, res: Response) {
  try {
    const user = req.user;
    const {retailer} = req.params;
    let dbRetailer: IRetailer;
    const company: ICompany = await Company.findById(user.company);
    const companySettings: ICompanySettings = await company.getSettings();
    const fields = '_id name sellRates smpMaxMin smpType gsId verification numberRegex pinRegex aiId';

    // Find retailer
    if (mongoose.Types.ObjectId.isValid(retailer)) {
      dbRetailer = await Retailer.findById(retailer, fields);
    } else {
      dbRetailer = await Retailer.findOne({name: new RegExp(retailer, 'i')}, fields);
    }
    if (!dbRetailer) {
      return res.status(notFound.code).json(notFound.res);
    }
    // Format retailer for response
    const formattedRetailer: IFormattedRetailer[] = formatRetailers([dbRetailer], companySettings);
    return res.json(formattedRetailer[0]);
  } catch (err) {
    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'getRetailer',
      controller: 'lq.controller',
      stack: err ? err.stack : null,
      error: err,

    });
    return res.status(500).json({
      invalid: 'An error has occurred.'
    });
  }
}

/**
 * Format a card for API response
 * @param card Incoming card response record
 */
async function formatCardResponse(card: ICardResponse) {
  // Set retailer in text form
  if (!card.retailer && card.inventory.retailer) {
    const retailer = await Retailer.findById(card.inventory.retailer);
    if (retailer) {
      card.retailer = retailer.name;
    }
  }
  const attrs = ['_id', 'sellRate', 'number', 'pin', 'retailer', 'userTime', 'balance', 'merchandise', 'buyAmount', 'soldFor', 'statusCode', 'status', 'saleAccepted', 'saleVerified', 'saleFinal', 'claimedBalanceInaccurate', 'transaction', 'verifiedBalance'];
  for (const attr in card) {
    if (card.hasOwnProperty(attr)) {
      if (attrs.indexOf(attr) === -1) {
        delete card[attr];
      }
    }
  }
  return card;
}

/**
 * Perform balance check
 * @param retailer Retailer record
 * @param card Card record
 * @param userId User ID
 * @param companyId Company ID
 * @param log BI request log
 * @param isTransaction
 */
async function doCheckCardBalance(retailer: IRetailer, card: ICardResponse, userId: string = null, companyId: string,
                                  log: IBiRequestLog, isTransaction = false): Promise<void> {
  let finalCard: ICard | ICardResponse = card;
  try {
    // All of the updating of the log and whatnot is handled in updateCardDuringBalanceInquiry()
    await checkCardBalance(retailer, finalCard.number, finalCard.pin, finalCard._id.toString(), log.requestId,
      userId.toString(), companyId.toString());
  } catch (err) {
    console.log('*************************ERR IN LQ CHECKCARDBALANCE*************************');
    console.log(err);
    // Give us the stack unless bi is just unavailable
    if (err) {
      console.log(err.stack);
    }

    await ErrorLog.create({
      method: 'doCheckCardBalance',
      controller: 'lq.controller',
      stack: err ? err.stack : null,
      error: err
    });
  }
}

/**
 * Use test cards for LQ
 * @param res
 * @param retailer Retailer ID
 * @param number Card number
 * @param userTime User time
 * @return {boolean}
 */
function lqTestCards(retailer: string, number: string, userTime: Date): ITestCard|boolean {
  if (retailer === config.biTestRetailer) {
    switch (number) {
      case '1000':
        return {
          "card": {
            "sellRate": 0.75,
            "_id": testCard1,
            "number": number,
            "retailer": "Best Buy",
            "userTime": userTime.toISOString(),
            "balance": 100,
            "pin": null,
            "buyAmount": 65,
            "soldFor": 75,
            "statusCode": 0,
            "status": "Sale proceeding",
            "saleAccepted": true,
            "saleVerified": false,
            "saleFinal": false,
            "claimedBalanceInaccurate": false
          }
        };
      case '2000':
        return {
          "card": {
            "sellRate": 0.75,
            "_id": testCard2,
            "number": number,
            "retailer": "Best Buy",
            "userTime": userTime.toISOString(),
            "balance": 100,
            "pin": null,
            "buyAmount": 65,
            "soldFor": 75,
            "statusCode": 0,
            "status": "Sale proceeding",
            "saleAccepted": true,
            "saleVerified": false,
            "saleFinal": false,
            "claimedBalanceInaccurate": false
          }
        };
      case '3000':
        return {
          "card": {
            "sellRate": 0.75,
            "_id": testCard3,
            "number": number,
            "retailer": "Best Buy",
            "userTime": userTime.toISOString(),
            "balance": 100,
            "pin": null,
            "buyAmount": 65,
            "soldFor": 75,
            "statusCode": 1,
            "status": "Check required",
            "saleAccepted": true,
            "saleVerified": false,
            "saleFinal": false,
            "claimedBalanceInaccurate": false
          }
        };
      case '4000':
        return {
          "card": {
            "sellRate": 0.75,
            "_id": testCard4,
            "number": number,
            "retailer": "Best Buy",
            "userTime": userTime.toISOString(),
            "balance": 100,
            "pin": null,
            "buyAmount": 65,
            "soldFor": 75,
            "statusCode": 1,
            "status": "Check required",
            "saleAccepted": true,
            "saleVerified": false,
            "saleFinal": false,
            "claimedBalanceInaccurate": false
          }
        };
      default:
        return false;
    }
  }
}

/**
 * Handle error from LQ\
 * @param res
 * @param cardId Card ID
 * @param code Response code
 * @param message Response message
 * @return {Promise.<void>}
 */
async function handleLqNewError(cardId: string, code: number, message: string): Promise<IErrorResponse> {
  // Remove card and inventory
  if (cardId) {
    const card = await Card.findById(cardId);
    if (card) {
      const inventory = await Inventory.findOne({
        _id: card.inventory
      });
      if (inventory) {
        inventory.remove();
      }
      card.remove();
    }
  }
  return {code, message};
}

/**
 * Create a fake res object for interacting with an endpoint without an http request
 * @return {{status: status, json: json}}
 */
export function createFakeRes() {
  return {
    status: function(code: number) {
      this.code = code;
      return this;
    },
    json: function(jsonObject: any) {
      this.response = jsonObject;
      return this;
    }
  };
}

/**
 * Handle the creation of an inventory error
 * @param {IGenericExpressResponse} addToInventoryResponse Response from addToInventory()
 * @param {ICardResponse} responseBodyCard Card
 * @param {boolean} autoSell Whether to autoSell
 * @returns {Promise<IGenericExpressResponse>}
 */
async function handleCreateInventoryError(addToInventoryResponse: IGenericExpressResponse, responseBodyCard: ICardResponse,
                                          autoSell: boolean): Promise<IGenericExpressResponse> {
  if (addToInventoryResponse && (addToInventoryResponse.status === 400 || addToInventoryResponse.status === 500)) {
    let errorMessage;
    // Can't sell
    if (addToInventoryResponse.message === 'noSmp') {
      errorMessage = 'Card violates sell limits';
    } else {
      // Create error
      errorMessage = addToInventoryResponse.message;
    }
    // Don't set res if autoSell from biCompleted
    if (autoSell) {
      return errorMessage;
    } else {
      const lqErrorResponse: IErrorResponse = await handleLqNewError(responseBodyCard._id.toString(), addToInventoryResponse.status, errorMessage);
      const message = lqErrorResponse.message;
      const cards = message.cards.map(card => {
        const cardObject = card.toObject();
        let retailer = cardObject.retailer;
        // Get retailer name, or leave as ID if thats what we're working with
        if (retailer) {
          if (!(typeof retailer === 'string' || retailer.constructor.name === 'ObjectID') && (typeof retailer.name !== 'undefined')) {
            retailer = retailer.name;
          }
        }
        return {
          number: cardObject.number,
          pin: cardObject.pin,
          retailer
        };
      });
      return {
        status: lqErrorResponse.code,
        message: {error: {errors: {balance: 'Card violates balance limits'}}, cards}
      };
    }
  }
  return null;
}

/**
Create a card
POST http://localhost:9000/api/lq/new
STATUS CODES:
 0: Sale proceeding as normal
 1: Sale status must be checked to see if sale was rejected
HEADERS
BODY
{
"number":"777775777675775476775577776657777",
"pin":"666",
"retailer":"5668fbff37226093139b90bd",
"userTime":"2016-09-10T20:34:50-04:00",
"balance": 3005,
"merchandise": true
}
RESPONSE
{
 "card": {
   "sellRate": "0.75",
   "_id": "588689835dbe802d2b0f6074",
   "number": "gewfwgegewqgewgwgewe",
   "retailer": "Adidas",
   "userTime": "2017-01-23T18:53:55.884Z",
   "merchandise": true,
   "balance": 300,
   "pin": null,
   "__v": 0,
   "buyAmount": "195.00",
   "soldFor": "225.00"
   "statusCode": "0",
   "status": "Sale proceeding"
 }
}

TEST CARDS:
NO PIN CODES

Adidas: 5668fbff37226093139b90d5
1000: Complete immediately: $0
5000: Complete immediately: $5

Nike: 5668fbff37226093139b9357
1000: Deferred: $0
5000: Deferred: $5
 */
async function getNewCardCustomer(customer: string, user: IUser) {
  let dbCustomer: ICustomer;
  // Specific customer
  if (customer) {
    dbCustomer = await Customer.findById(customer);
  } else {
    dbCustomer = await Customer.findOne({
      stateId: 'API_Customer',
      company: user.company,
    });
  }
  // No customer, create generic
  if (!dbCustomer) {
    const newGenericCustomerValues = apiCustomerValues(user.company.toString());
    const newGenericCustomer = new Customer(newGenericCustomerValues);
    dbCustomer = await newGenericCustomer.save();
  }
  return dbCustomer;
}

/**
 * Get or create a BiRequestLog after card creation
 * @param {ICard} card
 * @param {ILqNewCardBodyParams} params
 * @returns {Promise<IBiRequestLog>}
 */
async function getOrCreateBiRequestLog(card: ICard, params: ILqNewCardBodyParams) {
  let dbBiLog: IBiRequestLog;
  // Find BI log, if we have one
  dbBiLog = await BiRequestLog.findOne({
    number: card.number,
    pin: card.pin,
    retailerId: card.retailer._id
  });
  // If we have a BI log, attach card
  if (dbBiLog) {
    dbBiLog.card = card._id;
    dbBiLog = await dbBiLog.save();

    // Set verified balance on card and inventory if the birequestlog is already complete
    if (typeof dbBiLog.balance === 'number' && dbBiLog.finalized) {
      card.verifiedBalance = dbBiLog.balance;
      card.save();
    }
    // Create BI log if one doesn't exist
  } else {

    dbBiLog = await BiRequestLog.create({
      pin: card.pin,
      number: card.number,
      retailerId: card.retailer._id,
      card: card._id,
      user: params.user._id,
      callbackUrl: params.callbackUrl,
      lqCustomerName: params.lqCustomerName
    });
  }
  return dbBiLog;
}

/**
 * Handle the action of creating a new card from lq
 * @param {ILqNewCardBodyParams} params
 * @param {string} callbackStackId
 * @returns {Promise<IGenericExpressResponse>}
 */
async function doLqNewCard(params: ILqNewCardBodyParams, callbackStackId: string): Promise<IGenericExpressResponse> {
  let responseBodyCard: ICardResponse;
  let card: ICard;
  let dbBiLog: IBiRequestLog;
  let biComplete: boolean = false;
  let retailer: IRetailer;
  let store: string;
  // Test cards
  const testCardResponse = lqTestCards(params.retailer, params.number, params.userTime);
  if (testCardResponse) {
    return {
      status: 200,
      message: testCardResponse
    };
  }

  retailer = await Retailer.findById(params.retailer);

  // Get references
  const company = await Company.findById(params.user.company);
  const companySettings = await company.getSettings();
  const dbCustomer = await getNewCardCustomer(params.customer, params.user);
  store = params.store ? params.store : company.stores[0].toString();

  try {
    // Create card
    const newCardParams: ICreateNewCardParams = {
      retailer: params.retailer,
      number: params.number,
      pin: params.pin,
      userTime: moment(params.userTime).toDate(),
      balance: params.balance,
      lqCustomerName: params.lqCustomerName,
      customer: dbCustomer._id.toString(),
      user: params.user,
      store,
      company
    };

    // Handle card creation
    const newCardResponse = await createNewCard(newCardParams);
    // Pass it on down the line
    if (newCardResponse.status !== 200) {
      return newCardResponse;
    }
    card = newCardResponse.message;
    // Card is unable to sell
    if (!card.sellRate) {
      return {
        status: 400,
        message: {invalid: 'Card violates sell limits'}
      };
    }
  } catch (err) {
    console.log('**************ERR FROM NEW CARD**********');
    console.log(err);

    await ErrorLog.create({
      body: params,
      params: {},
      method: 'lqNewCard',
      controller: 'lq.controller',
      stack: err ? err.stack : null,
      error: err,
    });
  }
  // Get or create a BiRequestLog
  dbBiLog = await getOrCreateBiRequestLog(card, params);
  // Set buyAmount for this card
  card.buyAmount = formatFloat((card.sellRate - 0.1) * card.balance);
  card = await card.save();

  // Card for response
  const empty: any = {};
  responseBodyCard = Object.assign(empty, card.toObject());
  responseBodyCard.retailer = card.retailer.name;
  responseBodyCard.sellRate = responseBodyCard.sellRate ? formatFloat(responseBodyCard.sellRate) : null;
  responseBodyCard.soldFor = responseBodyCard.soldFor ? formatFloat(responseBodyCard.soldFor) : null;
  delete responseBodyCard.customer;
  delete responseBodyCard.balanceStatus;
  delete responseBodyCard.buyRate;
  delete responseBodyCard.user;
  delete responseBodyCard.updates;
  delete responseBodyCard.valid;

  const userId = params.user._id.toString();
  const companyId = params.user.company.toString();

  if (!dbBiLog.finalized) {
    // Check one, if deferred, begin interval of checking request ID for 5 minutes
    await doCheckCardBalance(retailer, responseBodyCard, userId, companyId, dbBiLog);
  }

  const addToInventoryParams: IAddToInventoryParams = {
    userTime: params.userTime,
    modifiedDenials: 0,
    store,
    transaction: null,
    callbackUrl: params.callbackUrl,
    user: params.user,
    cards: [card]
  };
  // Create inventory, get receipt
  const addToInventoryResponse: IGenericExpressResponse = await doAddToInventory(addToInventoryParams);
  const receipt = addToInventoryResponse.message;
  // Unable to create inventory, return message
  const createInventoryErrorResponse = await handleCreateInventoryError(addToInventoryResponse, responseBodyCard, params.autoSell);
  // Return error message for biCompleted to handle
  if (createInventoryErrorResponse) {
    return createInventoryErrorResponse;
  }
  if (responseBodyCard.__v) {
    delete responseBodyCard.__v;
  }
  if (responseBodyCard.created) {
    delete responseBodyCard.created;
  }
  // Mark inventory as API
  let inventory = await Inventory.findById(receipt.inventories[0]);
  // @todo This error message is a lie. Fix me.
  if (!inventory) {
    return {
      status: 400,
      message: {invalid: 'Card violates buy/sell limits'}
    };
  }
  // Already have a balance
  if (dbBiLog.finalized) {
    inventory.verifiedBalance = dbBiLog.balance;
  }
  inventory.isApi = true;
  inventory = await inventory.save();

  // Determine who card is being sold to
  const sellTo = determineSellTo(retailer, inventory.balance, companySettings);
  // No SMP available
  if (!sellTo) {
    const lqErrorResponse: IErrorResponse = await handleLqNewError(responseBodyCard._id.toString(), 400,
      'Card violates sell limits');
    return {
      status: lqErrorResponse.code,
      message: {invalid: lqErrorResponse.message}
    };
  }

  // Make callback, as card is finalized
  if (biComplete && params.callbackUrl) {
    // Push onto stack
    if (callbackStackId && Array.isArray(callbackStack[callbackStackId])) {
      const callback = new Callback(params.callbackUrl);
      callbackStack[callbackStackId].push(callback.sendCallback.bind(callback, card, 'cardFinalized'));
      // Send callback immediately
    } else {
      await (new Callback(params.callbackUrl)).sendCallback(card, 'cardFinalized');
    }
  }

  responseBodyCard.saleAccepted = true;
  const decoratedCard: ICardResponse = decorateCardWithSaleStatuses(responseBodyCard, inventory);

  return {
    status: 200,
    message: {card: await formatCardResponse(decoratedCard)}
  };
}


/**
 * Submit a new card
 * @param {e.Request} req
 * @param {e.Response} res
 * @returns {Promise<Response>}
 *
 * Checked - Logan - 4/3/18
 *  globalLimits: {max: 500, min: 5}
 * BI-enabled card - Walmart - 5668fbff37226093139b94b5 (max: 100, min: 10)
 *  disableLimits: true
 *    - Submit: $50 - 11
 *    - Submit: $50, solve $50 - 13
 *      - Callback - 13
 *    - Submit: $50, solve $45 - 14
 *      - Callback - 14
 *    - Submit: $50, solve $55 - 15
 *      - Callback - 15
 *    - Submit: $50, solve $501 (violates sell limits)
 *      - Callback
 *    - Submit: $50, solve $4 (violates sell limits)
 *      - Callback
 *    - Submit: $50, solve $0 (invalid)
 *      - Callback
 *    - Submit: $0 (invalid)
 *    - Submit: $501 (violates sell limits)
 *    - Submit: $4 (violates sell limits)
 *  disableLimits: false
 *  - Successful (disableLimits: true), $50 -
 *  - Successful (disableLimits: false) -
 *  - Card exists - /
 *  - Violates sell limits (disableLimits: true) - /
 *  - Violates sell limits (disableLimits: false) - /
 * BI-disabled card (retailer exists in BI, does not have aiId or gsId) - 7/11 - 5668fbff37226093139b90c3 (max: 100, min: 10)
 *  - Successful (disableLimits: true) -
 *  - Successful (disableLimits: false) -
 *  - Card exists - /
 *  - Violates sell limits (disableLimits: true) - /
 *  - Violates sell limits (disableLimits: false) -
 * Retailer does not exist in BI DB - Earth Fare - 5668fbff37226093139b91f1 (max: 100, min: 10)
 *  - Successful -
 *  - Card exists -
 *  - Violates sell limits -
 */
export async function lqNewCard(req: Request, res: Response) {
  try {
    const reqBody: ILqNewCardBodyParams = req.body;
    const {number, pin, retailer, balance, callbackUrl = null, customer, store = null, lqCustomerName = null,
      // Sale proceeding from biCompleted
      autoSell = false
    } = reqBody;
    const userTime = new Date(reqBody.userTime);
    const params: ILqNewCardBodyParams = {
      number,
      pin,
      retailer,
      userTime,
      balance,
      callbackUrl,
      customer,
      store,
      autoSell,
      user: req.user,
      lqCustomerName
    };
    // Make sure we have a store to attach the record to
    let dbStore = await ensureStoreBelongsToUser(params.store, req.user);
    if (!dbStore) {
      return storeDoesNotBelongToUserResponse(res);
    }
    params.store = dbStore._id.toString();
    const newCardResponse: IGenericExpressResponse = await doLqNewCard(params, null);
    return res.status(newCardResponse.status).json(newCardResponse.message);
  } catch (err) {
    console.log('**************ERR IN LQ NEW CARD**********');
    console.log(err);

    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'lqNewCard',
      controller: 'lq.controller',
      stack: err ? err.stack : null,
      error: err,
    });

    return res.status(400).json({
      invalid: 'An error has occurred.'
    });
  }
}

/**
 * Calculate transaction values
 * @param transactionTotal Transaction total
 * @param maxSpending Max amount allowed
 * @param cardValue Card value
 * @param payoutPercentage Payout percentage to merchant
 * @return {{amountDue: number, cardValue: number, merchantPayoutAmount: number}}
 */
function calculateTransactionValues(transactionTotal, maxSpending, cardValue, payoutPercentage): ICalculateTransactionResponse {
  let amountDue: number = 0;
  let newCardValue: number = 0;
  let merchantPayoutAmount: number = 0;
  // Calculate transaction data
  if (transactionTotal >= cardValue && cardValue <= maxSpending) {
    amountDue = formatFloat(transactionTotal - cardValue);
    newCardValue = 0;
    merchantPayoutAmount = formatFloat(payoutPercentage * cardValue);
  } else {
    amountDue = Math.max(0, transactionTotal - Math.min(maxSpending, cardValue));
    newCardValue = cardValue - Math.min(maxSpending, transactionTotal);
    merchantPayoutAmount = formatFloat(payoutPercentage * Math.min(maxSpending, cardValue, transactionTotal));
  }
  // Format nicely
  if (typeof newCardValue === 'number') {
    newCardValue = formatFloat(newCardValue);
  }
  if (typeof amountDue === 'number') {
    amountDue = formatFloat(amountDue);
  }
  if (typeof merchantPayoutAmount === 'number') {
    merchantPayoutAmount = formatFloat(merchantPayoutAmount);
  }
  return {amountDue: amountDue, cardValue: newCardValue, merchantPayoutAmount: merchantPayoutAmount};
}

/**
 * Create search params for bi log
 * @param number
 * @param retailer
 * @param pin
 * @returns {IBiSearchParams}
 */
function getBiLogSearch(number, retailer, pin): IBiSearchParams {
  // See if we have BI for this already
  const biLogSearch: IBiSearchParams = {
    number,
    retailerId: retailer
  };
  if (pin) {
    biLogSearch.pin = pin;
  }
  return biLogSearch;
}

/**
 * Parse BI log
 * @param biRes
 * @returns {any}
 */
function parseBiLog(biRes): IParsedBiLog {
  if (!biRes) {
    return {verifiedBalance: null, valid: null, finalized: false};
  }
  let verifiedBalance = null;
  const finalized = !!biRes.finalized;
  // Invalid card
  if (biRes.responseCode === config.biCodes.invalid) {
    return {verifiedBalance: 0, valid: false, finalized}
  }
  // See if we already have a balance
  if (biRes && biRes.balance) {
    try {
      verifiedBalance = parseFloat(biRes.balance);
    } catch (e) {
      verifiedBalance = null;
    }
  }
  // If we have a balance, return it
  if (!isNaN(verifiedBalance)) {
    return {verifiedBalance, valid: true, finalized};
  }
  return {verifiedBalance: null, valid: null, finalized}
}

function getAddToInventoryErrorResponse(response) {
  // Can't sell
  try {
    if (response.message === 'noSmp' || response.message.reason === 'noSmp') {
      return {invalid: 'Card violates sell limits'};
    } else {
      return response;
    }
  } catch (e) {
    return response;
  }
}

/**
 * Format the transaction response card
 * @param dbCard
 * @return {*}
 */
function formatResponseCard(dbCard) {
  dbCard.retailer = dbCard.retailer.name;
  dbCard.sellRate = formatFloat(dbCard.sellRate);
  dbCard.soldFor = formatFloat(dbCard.sellRate * dbCard.balance);
  delete dbCard.customer;
  delete dbCard.balanceStatus;
  delete dbCard.buyRate;
  delete dbCard.user;
  delete dbCard.updates;
  delete dbCard.valid;
  if (dbCard.__v) {
    delete dbCard.__v;
  }
  if (dbCard.created) {
    delete dbCard.created;
  }
  return dbCard;
}

/**
 * Check BI either with CQ or Vista
 * @param {ITransactionBiParams} params
 * @returns {any}
 */
async function newTransactionBi(params: ITransactionBiParams): Promise<IGenericExpressResponse> {
  let biRes;
  // Check to see if we have a bi log
  biRes = await BiRequestLog.findOne(params.biSearchValues);
  // See if we have a verified balance
  biRes = parseBiLog(biRes);
  return {status: 200, message: biRes};
}

/**
 * Retrieve necessary models to complete transaction
 * @param {string} customerId
 * @param {string} companyId
 * @param {string} storeId
 * @param {IUser} user
 * @returns {Promise<IGenericExpressResponse>}
 */
async function getTransactionModels(customerId: string, companyId: string, storeId: string, user: IUser): Promise<IGenericExpressResponse> {
  let customerConstraint: ICustomerConstraint = {
    store: storeId,
    company: companyId
  };
  if (mongoose.Types.ObjectId.isValid(customerId)) {
    customerConstraint._id = customerId;
  } else {
    customerConstraint.email = customerId;
  }
  // Find transaction customer
  const dbCustomer = await Customer.findOne(customerConstraint);
  if (!dbCustomer || dbCustomer.company.toString() !== user.company.toString()) {
    return {
      message: 'Customer',
      status: notFound.code
    };
  }
  // Find company
  const dbCompany = await Company.findById(companyId);
  if (!dbCompany) {
    return {
      message: 'Company',
      status: notFound.code
    };
  }
  // Company settings
  const dbCompanySettings = await dbCompany.getSettings();
  // Find store
  const dbStore = await Store.findById(storeId).populate('companyId');
  if (!dbStore) {
    return {
      message: 'Store',
      status: notFound.code
    };
  } else if (dbStore.companyId._id.toString() !== companyId.toString()) {
    return {
      message: 'Store',
      status: notFound.code
    };
  }
  return {
    message: {
      dbCustomer,
      dbCompany,
      dbStore,
      dbCompanySettings
    },
    status: 200
  };
}

/**
 * Get or create BI log for transaction
 * @param {IBiSearchParams} biSearchValues
 * @param {ICard} card
 * @param {string} userId
 * @param {string} prefix Card prefix
 * @param {string} lqCustomerName Customer information for the customer selling the card
 * @param {string} currencyCode
 * @returns {Promise<IBiRequestLog>}
 */
async function transactionBiLog(biSearchValues: IBiSearchParams, card: ICard, userId: string, prefix: string, lqCustomerName: string, currencyCode: string): Promise<IBiRequestLog> {
  // Get most recent log if we have one
  const logs = await BiRequestLog.find(biSearchValues).sort({created: -1});
  let log = null;
  if (logs) {
    log = logs[0];
  }
  if (log) {
    log.card = card._id;
    log = await log.save();
  } else {
    log = new BiRequestLog({
      pin: card.pin,
      number: card.number,
      retailerId: card.retailer._id,
      card: card._id,
      user: userId,
      autoSell: true,
      prefix
    });
    log = await log.save();
  }
  return log;
}

/**
 * Get callbackUrl from log, inventory, or company settings
 * @param {string} companyId
 * @param {IBiRequestLog} log
 * @param {IInventory} inventory
 * @returns {Promise<string>}
 */
export async function getCallbackUrl(companyId: string, log: IBiRequestLog = null, inventory: IInventory = null): Promise<string> {
  let url: string = null;
  if (log) {
    if (log.callbackUrl) {
      return log.getCallbackUrl();
    }
    let card: ICard = null;
    let inventory: IInventory = null;
    if (log.card) {
      card = await Card.findById(log.card);
      if (card && card.inventory) {
        inventory = await Inventory.findById(card.inventory);
      }
    }
    if (!url && inventory) {
      url = inventory.callbackUrl;
    }
  }
  if (inventory) {
    url = await inventory.getCallbackUrl();
    if (url) {
      return url;
    }
  }
  if (companyId) {
    const company = await Company.findById(companyId);
    if (!company) {
      return;
    }
    const settings = await company.getSettings();
    if (!settings) {
      return;
    }
    if (settings.callbackUrl) {
      url = settings.callbackUrl;
    }
  }
  return url;
}


/**
 * Send a callback based on a user's company settings or from directly specified URL
 * @param {IBiRequestLog} log
 * @param {string} userId
 * @param {string} callbackStackId
 * @param {boolean} resend
 * @returns {Promise<void>}
 */
export async function sendCallbackFromCompanySettingsOrDirectUrl(log: IBiRequestLog, userId: string = '',
                                                                 callbackStackId: string = null, resend: boolean = false) {
  if (!userId) {
    return;
  }
  const user = await User.findById(userId);
  if (user) {
    const url: string = await getCallbackUrl(user.company.toString(), log);
    if (url) {
      if (callbackStackId) {
        let callback = new Callback(url);
        callbackStack[callbackStackId].push(callback.sendCallbackFromLog.bind(callback, log, resend));
      } else {
        await (new Callback(url)).sendCallbackFromLog(log, resend);
      }
    }
  }
}

/**
 * Handle the creation of a callback from a BiRequestLog model
 * @param {IBiRequestLog} log
 * @param {string} userId
 * @param {string} callbackStackId
 * @param {boolean} resend
 * @returns {Promise<void>}
 */
async function doCreateLogCallback(log: IBiRequestLog, userId: string = null,
                                   callbackStackId: string = null, resend: boolean = false): Promise<void> {
  const logUser = log.user ? log.user.toString() : null;
  const finalUserId = userId ? userId : logUser;
  if (config.isTest) {
    await sendCallbackFromCompanySettingsOrDirectUrl(log, finalUserId, callbackStackId, resend);
  } else {
    sendCallbackFromCompanySettingsOrDirectUrl(log, finalUserId, callbackStackId, resend);
  }
}

/**
 Create a transaction for Vista
 POST http://localhost:9000/api/lq/transaction
 HEADERS
 BODY
 {
 "number":"421421412",
 "pin":"666",
 "retailer":"5668fbff37226093139b90bd",
 "userTime":"2016-09-10T20:34:50-04:00",
 "balance": 100,
 "merchandise": true,
 "transactionAmount": 300
 }
 */

/**
 * 5668fbff37226093139b94b5
 * BI enabled, success - /
 * BI enabled, violates - /
 *
 * 5668fbff37226093139b90c3
 * BI not enabled, success - /
 * BI not enabled, violates - /
 */
export async function newTransaction(req: Request, res: Response) {
  let body: INewTransactionBody = req.body;
  const currencyCode: string = req.body.currencyCode || null;
  let dbCard;
  let dbRetailer;
  let transactionFinal: IInventoryTransaction;
  let biSearchValues: IBiSearchParams;
  // BI response values
  let biResolved = false;
  let biRes: ITransactionBiResponse;
  let inventory: IInventory;
  // Vista transaction
  const {
    number, pin, balance, retailer, memo, transactionTotal, transactionId, merchandise, customerId,
    vmMemo1 = null, vmMemo2 = null, vmMemo3 = null, vmMemo4 = null, callbackUrl = null, userTime, lqCustomerName = null,
    prefix = null
  } = body;
  let storeId = body.storeId;
  const store = await ensureStoreBelongsToUser(storeId, req.user);
  if (!store) {
    return storeDoesNotBelongToUserResponse(res);
  }
  storeId = store._id.toString();
  // Currently, we're ignoring '0000' PINs, at the request of Vista, since they require PINs on their side and are having
  // trouble changing their vaidation. So, 0000 means "no PIN"
  try {
    if (!pin || pin === '0000') {
      body.pin = null;
    }
    // Prevent duplicates
    const existingCard = await Card.findOne({number: body.number, pin: body.pin, retailer: body.retailer});
    if (existingCard) {
      // Card sold, or card belongs to another user
      if (existingCard.inventory || (existingCard.user.toString() !== req.user._id.toString())) {
        return res.status(400).json({error: {errors: [{card: 'Card has already been inserted'}]}});
      // Allow user to resubmit their own card if it hasn't been sold
      } else {
        await existingCard.remove();
      }
    }
    const user = req.user;

    // Get BI search values
    biSearchValues = getBiLogSearch(number, retailer, pin);

    const transactionBiParams: ITransactionBiParams = {
      number,
      pin,
      balance,
      biSearchValues,
      retailer,
      callbackUrl,
      prefix,
      user: req.user
    };
    // Handle BI either via Vista or CQ
    const newTransactionBiRes = await newTransactionBi(transactionBiParams);

    biRes = newTransactionBiRes.message;
    biResolved = biRes.finalized;

    // Ensure we're working with numbers here
    biRes.verifiedBalance = typeof biRes.verifiedBalance === 'string' ? parseFloat(biRes.verifiedBalance) : biRes.verifiedBalance;

    // Make sure we have the necessary models to complete the transactions
    const modelResponse = await getTransactionModels(customerId, user.company.toString(), storeId, req.user);
    if (modelResponse.status !== config.statusCodes.success) {
      return res.status(modelResponse.status).json(notFound.resFn(modelResponse.message));
    }
    const {dbCustomer, dbCompany, dbStore, dbCompanySettings} = modelResponse.message;

    // Create new card
    const newCardParams: ICreateNewCardParams = {
      retailer,
      number,
      pin,
      userTime: new Date(userTime),
      balance,
      lqCustomerName,
      customer: dbCustomer._id.toString(),
      user,
      store: dbStore._id.toString(),
      company: dbCompany
    };

    const newCardResponse = await createNewCard(newCardParams);
    // Pass it on down the line
    if (newCardResponse.status !== 200) {
      return res.status(newCardResponse.status).json(newCardResponse.message);
    }
    let thisCard = newCardResponse.message;
    // Set VB if we have one
    if (!(typeof biRes.verifiedBalance === 'undefined')) {
      thisCard.verifiedBalance = biRes.verifiedBalance;
    }
    thisCard = await thisCard.save();

    // Get or create log for transaction
    const log = await transactionBiLog(biSearchValues, thisCard, req.user._id, prefix, lqCustomerName, currencyCode);

    if (newCardResponse.status !== 200) {
      return newCardResponse;
    }
    let card = newCardResponse.message;
    // Retailer with merch values
    dbRetailer = card.retailer.populateMerchValues(card);
    card.balance = balance;
    card.buyAmount = formatFloat((card.sellRate - 0.1) * card.balance);
    card.retailer = dbRetailer;

    /**
     * Transaction calculations
     */
      // NCC card value before transaction
    let nccCardValue = balance * dbStore.creditValuePercentage;

    const transactionValues = calculateTransactionValues(transactionTotal, dbStore.maxSpending, nccCardValue,
      dbStore.payoutAmountPercentage);

    transactionFinal = {
      memo,
      nccCardValue: transactionValues.cardValue,
      transactionTotal,
      transactionId,
      merchantPayoutAmount: transactionValues.merchantPayoutAmount,
      merchantPayoutPercentage: dbStore.payoutAmountPercentage,
      amountDue: transactionValues.amountDue,
      prefix: body.prefix,
      vmMemo1, vmMemo2, vmMemo3, vmMemo4,
      creditValuePercentage: dbStore.creditValuePercentage,
      maxSpending: dbStore.maxSpending,
    };

    // Add card to inventory
    const addToInventoryParams: IAddToInventoryParams = {
      userTime: new Date(userTime),
      modifiedDenials: 0,
      store: dbStore._id.toString(),
      transaction: transactionFinal,
      callbackUrl,
      user: req.user,
      cards: [card]
    };
    // Create inventory, get receipt
    const addToInventoryResponse: IGenericExpressResponse = await doAddToInventory(addToInventoryParams);

    // Card rejected
    if (addToInventoryResponse.status !== 200) {
      const errorRes = getAddToInventoryErrorResponse(addToInventoryResponse);
      return res.status(addToInventoryResponse.status).json(errorRes);
    }
    // Updated card
    let cardBeforeResponse = await Card.findById(card._id).populate('inventory');
    const cardBeforeResponseObject = cardBeforeResponse.toObject();
    dbCard = Object.assign({}, cardBeforeResponseObject);
    dbCard = formatResponseCard(dbCard);
    inventory = cardBeforeResponse.inventory;
    // Try to get verified balance
    if (!biResolved || typeof biRes.verifiedBalance !== 'number') {
      const card = Object.assign({}, dbCard);
      card.inventory = inventory;
      const userId = req.user._id.toString();
      const companyId = req.user.company.toString();
      // Check one, if deferred, begin interval of checking request ID for 5 minutes
      await doCheckCardBalance(dbRetailer, card, userId, companyId, log, true);
    }

    inventory.isApi = true;
    inventory = await Inventory.findById(inventory._id);
    if (typeof biRes.verifiedBalance === 'number') {
      inventory.verifiedBalance = biRes.verifiedBalance;
      await inventory.save();
      // If log is already finalized, then send the BI complete callback
      await doCreateLogCallback(log);
    }
    inventory = await Inventory.findById(inventory._id);
    dbCard.inventory = inventory;
    const sellTo = determineSellTo(dbRetailer, inventory.balance, dbCompanySettings);
    if (!sellTo) {
      return res.status(400).json({invalid: 'Card violates sell limits'});
    }

    dbCard.statusCode = 0;
    // Auto sell on or off
    if (inventory.proceedWithSale) {
      dbCard.status = 'Sale proceeding';
    } else {
      dbCard.status = 'Sale pending approval';
    }

    /**
     * The reason that we're using balance here and not verifiedBalance
     * @type {Number}
     */
    const displaySellRate = formatFloat(sellTo.rate - dbCompanySettings.margin);
    // let balanceForCalculations;
    const balanceForCalculations = dbCard.inventory.verifiedBalance ? dbCard.inventory.verifiedBalance : dbCard.inventory.balance;
    // Display sell for
    dbCard.soldFor = balanceForCalculations * displaySellRate;
    dbCard.verifiedBalance = dbCard.inventory.verifiedBalance ? dbCard.inventory.verifiedBalance : null;
    dbCard = decorateCardWithSaleStatuses(dbCard, inventory, transactionFinal);
    return res.json({card: await formatCardResponse(dbCard)});
  } catch (err) {
    console.log('**************ERR IN TRANSACTION**********');
    console.log(err);
    if (err instanceof SellLimitViolationException) {
      return res.status(400).json({err: 'Card violates sell limits'});
    }
    if (err) {
      console.log(err.stack);
    }
    let remove = false, cardToDelete;

    if (err && err.message === 'cardRejected') {
      // The promise chain above is already sending a response
      remove = true;
    }

    if (err) {
      if (err.message === 'cardExists') {
        remove = true;
        return res.status(400).json({
          invalid: 'Card already exists in database'
        });
      }
      if (err.message === 'noSmp') {
        remove = true;
      }
    }

    if (remove && dbCard) {
      // Remove card and inventory
      Card.findById(dbCard._id)
      .then(async card => {
        cardToDelete = card;
        const inventory = await Inventory.findById(card.inventory);
        if (inventory) {
          inventory.remove();
        }
      })
      .then(() => {
        return Card.remove({
          _id: cardToDelete._id
        })
      });
      return;
    }

    await ErrorLog.create({
      method: 'newTransaction',
      controller: 'lq.controller',
      stack: err ? err.stack : null,
      error: err,
      body: req.body ? req.body : {},
      params: req.params ? req.params : {}
    });

    return res.status(500).json({
      invalid: 'An error has occurred.'
    });
  }
}

/**
 * Make fake req/res for internal requests
 * @param req
 */
export function makeFakeReqRes(req) {
  // Mock express res object
  const fakeRes = {
    status: function(code) {
      this.code = code;
      return this;
    },
    json: function(jsonObject) {
      this.response = jsonObject;
      return this;
    }
  };
  // Mock req
  const fakeReq = {
    body: req.body,
    user: req.user
  };
  return [fakeReq, fakeRes];
}

/**
 * Determine the response message for BI
 * @param log
 * @returns {string}
 */
function determineBiResponseMessage(log): string {
  let responseMessage = 'success';
  if (log.responseCode === '900011') {
    responseMessage = 'Invalid card';
  } else if (log.responseCode === '010') {
    responseMessage = 'Delayed Verification Required';
  }
  return responseMessage;
}

/**
 * Format a log for response from an endpoint
 * @param {IBiRequestLog} log
 * @param {number} balance
 * @returns {Promise<IBiRequestResponse>}
 */
async function formatLogResponse(log: IBiRequestLog, balance: number = null): Promise<IBiRequestResponse> {
  const responseMessage = determineBiResponseMessage(log);
  const retailer = await Retailer.findById(log.retailerId);
  const finalized = [config.biCodes.success, config.biCodes.invalid].includes(log.responseCode);
  const response: IBiRequestResponse = {
    responseDateTime: log.responseDateTime,
    responseCode: log.responseCode,
    request_id: log.requestId,
    requestId: log.requestId,
    balance,
    responseMessage,
    retailer: retailer.name
  };
  if (!finalized) {
    response.recheckDateTime = log.recheckDateTime;
    response.recheck = log.recheck;
  }
  return response;
}

/**
 * Create BI response message from a successful BI lookup
 * @param log
 * @param finalized
 * @param userId User ID for callbacks
 * @return {{responseDateTime: *, responseCode: (string|string), request_id: *, balance: Number, responseMessage: string}}
 */
async function createBiResponse(log, finalized = true, userId = null) {
  const responseMessage = determineBiResponseMessage(log);
  if (responseMessage === config.biResponseMessages.success) {
    await doCreateLogCallback(log, userId, null, true);
  }
  const retailer = await Retailer.findById(log.retailerId);
  const balance = typeof log.balance === 'number' ? parseFloat(log.balance) : null;
  const response: IBiRequestResponse = {
    responseDateTime: log.responseDateTime,
    responseCode: log.responseCode,
    request_id: log.requestId,
    requestId: log.requestId,
    balance,
    responseMessage,
    retailer: retailer.name
  };
  if (!finalized) {
    response.recheckDateTime = log.recheckDateTime;
    response.recheck = log.recheck;
  }
  return response;
}

/**
 * Parse a response from BI
 * @param log Log file
 * @param biRes BI Response
 */
async function parseBiResponseAndUpdateLog(log: IBiRequestLog, biRes: IBiRequestResponse): Promise<IBiRequestResponse> {
  log.requestId = biRes.request_id;
  log.responseDateTime = biRes.response_datetime;
  log.responseCode = biRes.responseCode;
  if (biRes.recheckDateTime) {
    log.recheckDateTime = biRes.recheckDateTime;
  }
  if (biRes.recheck) {
    log.recheck = biRes.recheck;
  }
  delete biRes.bot_statuses;
  delete biRes.request_id;
  delete biRes.verificationType;
  delete biRes.recheck;

  log.balance = biRes.balance;
  log.save();
  return biRes;
}

/**
 * Fake BI responses
 * @param {string} retailer
 * @param {string} number
 * @param {string} requestId
 * @returns {any}
 */
function fakeBi(retailer: string, number: string, requestId: string): ILqInternalBiResponse {
  if (retailer === config.biTestRetailer) {
    if (number === '1000') {
      return {
        "responseDateTime": moment().format('Y-MM-DD HH:mm:ss.ms'),
        "responseCode": "000",
        "request_id": "11502131554644889807",
        "balance": 100,
        "responseMessage": "success"
      };
    } else if (number === '2000') {
      return {
        "responseDateTime": moment().format('Y-MM-DD HH:mm:ss.ms'),
        "responseCode": "000",
        "request_id": "11502131554644889808",
        "balance": 100,
        "responseMessage": "success"
      };
    } else if (number === '3000') {
      return {
        "responseDateTime": moment().format('Y-MM-DD HH:mm:ss.ms'),
        "responseCode": "000",
        "request_id": "11502131554644889809",
        "balance": 100,
        "responseMessage": "success"
      };
    } else if (number === '4000') {
      if (requestId) {
        return {
          "responseDateTime": moment().format('Y-MM-DD HH:mm:ss.ms'),
          "responseCode": "000",
          "request_id": "11502131554644889810",
          "balance": 100,
          "responseMessage": "success"
        };
      } else {
        return {
          "balance": null,
          "response_datetime": moment().format('Y-MM-DD HH:mm:ss.ms'),
          "responseMessage": "Delayed Verification Required",
          "requestId": "11502131554644889810",
          "responseCode": "010",
          "responseDateTime": moment().format('Y-MM-DD HH:mm:ss.ms'),
          "recheckDateTime": moment().add(1, 'hour').format('Y-MM-DD HH:mm:ss.ms')
        };
      }
    }
  }
  return null;
}

/**
 * Reinsert BI requests that didn't make it to the receiver
 */
export async function reinsertBi(req: Request, res: Response) {
  const {begin, end} = req.params;
  const logs = await BiRequestLog.find({created: {$gt: new Date(begin), $lt: new Date(end)}}).populate('retailerId');
  for (const log of logs) {
    await doCheckBalance(log.retailerId, req.user, log.number, log.pin, log.card._id.toString(), log.requestId);
  }
  return res.json({});
}

/**
 * Handle fake BI response for testing purposes
 * @param {IUser} user
 * @param {string} number
 * @param {string} pin
 * @param {string} retailer
 * @param {ILqInternalBiResponse} fakeCardAutoResponse
 * @returns {Promise<ILqInternalBiResponse | null>}
 */
async function handleFakeBiResponse(user: IUser, number: string, pin: string, retailer: string,
                                    fakeCardAutoResponse: ILqInternalBiResponse): Promise<ILqInternalBiResponse|null> {
  if (user) {
    const fakeBiParams: IFakeBiParams = {
      number,
      pin,
      retailerId: retailer,
      balance: fakeCardAutoResponse.balance
    };
    // Remove any existing log
    await BiRequestLog.remove(fakeBiParams);
    fakeBiParams.user = user._id;
    const log = new BiRequestLog(fakeBiParams);
    await doCreateLogCallback(log, null, null, true);
  }
  return fakeCardAutoResponse;
}

/**
 * Get most recent log according to incoming params
 * @param {string} number
 * @param {string | null} pin
 * @param {"mongoose".Types.ObjectId | string} retailer
 * @returns {IBiRequestLog}
 */
async function getMostRecentLog(number: string, pin: string|null, retailer: Types.ObjectId|string): Promise<IBiRequestLog|null> {
  let logs = await BiRequestLog.find({
    number,
    pin,
    retailerId: retailer
  }).sort({created: -1});
  if (logs.length) {
    return logs[0];
  }
  return null;
}

/**
 * Finalize log and card, if exists
 * @param log
 * @param user
 * @param number
 * @param pin
 * @param retailer
 * @returns {IGenericExpressResponse | void}
 */
async function attachCardToLog(log: IBiRequestLog, user: IUser, number: string, pin: string|null,
                               retailer: string|Types.ObjectId): Promise<IGenericExpressResponse> {
  if (log) {
    let card = null;
    // See if we have a valid card
    if (log.card) {
      card = await Card.findById(log.card);
      if (!card) {
        card = await Card.findOne({number: log.number, pin: log.pin, retailer: log.retailerId});
        if (card) {
          log.card = card._id;
          await log.save();
        }
      }
    }
    const belongsToUser = log.user && log.user.toString() === user._id.toString();
    const logCreated = moment(log.created);
    const now = moment();
    // Don't recreate if less than 24 hours old with a balance
    const logStillValid = belongsToUser && typeof log.balance === 'number' && now.diff(logCreated, 'seconds') < config.threeDays;
    if (card || logStillValid) {
      return {
        status: 200,
        message: await createBiResponse(log, true, user ? user._id : null)
      };
      // Remove older records which did not result in a sale
    } else {
      await BiRequestLog.remove({
        number,
        pin,
        retailerId: retailer
      });
      return {
        status: 200,
        message: null
      };
    }
  }
  return {
    status: 200,
    message: null
  };
}

/**
 * Handle test BI interactions
 * @param {IHandleTestBiParams} params
 * @returns {Promise<IGenericExpressResponse>}
 */
async function handleTestBi(params: IHandleTestBiParams): Promise<IGenericExpressResponse> {
  const response: IHandleTestBiResponse = {
    isTestCard: false,
    noAdditionalCallbackTests: false,
    callbackStackId: null
  };
  if (!isProd) {
    response.isTestCard = params.pin !== null && config.lqTestPatterns.isTestCard.test(params.number);
  }
  let callbackStackId: string = null;
  // Callback stack ID for sending callbacks at the end
  if (response.isTestCard) {
    response.callbackStackId = uuid();
    callbackStack[callbackStackId] = [];
  }
  response.noAdditionalCallbackTests = !(/^10000000000.*(88888|99999)/.test(params.number));

  // Fake BI responses
  const fakeCardAutoResponse: ILqInternalBiResponse = fakeBi(params.retailer, params.number, params.requestId);
  if (fakeCardAutoResponse) {
    const testBiResponse = await handleFakeBiResponse(params.user, params.number, params.pin, params.retailer, fakeCardAutoResponse);
    if (testBiResponse) {
      return {
        status: 201,
        message: testBiResponse
      };
    }
  }
  return {
    status: 200,
    message: response
  };
}

/**
 * Complete test BI requests
 * @param {IHandleTestBiResponse} testBiResponseValues
 * @param {IBiRequestBody} body
 * @param {string} retailerId
 * @param {IBiRequestLog} log
 * @param {string} userId
 * @returns {Promise<void>}
 */
async function completeTestBiRequest(testBiResponseValues: IHandleTestBiResponse, body: IBiRequestBody,
                                     retailerId: string, log: IBiRequestLog, userId: string) {
  let fakeBalance = parseInt(body.pin);
  setTimeout(async () => {
    // Handle BI complete
    const handleBiCompleteParams: IHandleBiCompleteParams = {
      requestId: body.requestId,
      retailerId,
      number: body.number,
      pin: body.pin,
      balance: fakeBalance,
      callbackStackId: testBiResponseValues.callbackStackId,
      lqCustomerName: body.lqCustomerName,
      userId
    };
    // Handle bi complete functionality
    await handleBiComplete(handleBiCompleteParams);
    if (testBiResponseValues.noAdditionalCallbackTests) {
      await log.remove();
    }
  }, 5000);
}

/**
 * Create a fake BI response for test cards
 * @returns {{verificationType: string; balance: null; response_datetime: string; responseMessage: string; requestId: string; responseCode: string; request_id: string; responseDateTime: string; recheck: string; recheckDateTime: string}}
 */
function createTestBiResponse() {
  const requestId = Math.random().toString().slice(2,20);
  return {
    verificationType : 'PJVT_BOT',
    balance          : null,
    response_datetime: new Date().toString(),
    responseMessage  : 'Delayed Verification Required',
    requestId        : requestId,
    responseCode     : '010',
    request_id       : requestId,
    responseDateTime : new Date().toString(),
    recheck          : 'True',
    recheckDateTime  : new Date().toString()
  };
}

/**
 * Query BI by request id
 * @param {IInsertBiParams} params
 * @returns {Promise<IGenericExpressResponse>}
 */
async function queryBiByRequestId(params: IInsertBiParams): Promise<IGenericExpressResponse> {
  const biParams: IBalanceInquiryParams = {requestId: params.requestId};
  const biRes = await balanceInquiry(biParams);
  if (biRes) {
    const log = await BiRequestLog.findOne({requestId: params.requestId});
    // Create log if we're mising one
    if (!log) {
      const logParams: IBiRequestLogFindParams = {
        number: params.number,
        pin: params.pin,
        retailerId: params.retailer,
        user: params.user ? params.user._id : null,
        callbackUrl: params.callbackUrl,
        autoSell: params.autoSell,
        customer: params.customer,
        lqCustomerName: params.lqCustomerName,
        requestId: params.requestId
      };
      if (params.store) {
        logParams.store = params.store;
      }
    }
    return {
      status: 200,
      message: await parseBiResponseAndUpdateLog(log, biRes)
    };
  } else {
    return {
      status: 404,
      message: {error: 'Request ID not found'}
    };
  }
}

/**
 * Handle insertion of card into BI
 * @param {IInsertBiParams} params
 * @returns {Promise<IGenericExpressResponse>}
 */
export async function insertBi(params: IInsertBiParams): Promise<IGenericExpressResponse> {
  const {number, pin = null, requestId, prefix, callbackUrl = '', autoSell = false, store = null,
    customer = null, lqCustomerName = null, user} = params;
  let retailer = params.retailer || params.retailerId;
  let biRes: IBiRequestResponse = {
    balance: null,
    responseMessage: null,
    requestId: null,
    responseCode: null,
    request_id: null,
    responseDateTime: null,
  };
  let log: IBiRequestLog;
  let logParams: IBiRequestLogFindParams;
  // Just check by request ID
  if (requestId) {
    return await queryBiByRequestId(params);
  }
  const testBiParams = {
    isTestCard: null,
    retailer,
    number,
    requestId,
    user,
    pin
  };
  // Handle potential test BI responses
  const testBiResponse = await handleTestBi(testBiParams);
  if (testBiResponse.status === 201) {
    return testBiResponse;
  }
  const testBiResponseValues = testBiResponse.message;

  const dbRetailer = await Retailer.findById(retailer);

  log = await getMostRecentLog(number, pin, retailer);
  // If card is already sold, do not proceed
  let isAlreadySold = false;
  if (log && log.card) {
    const logCard = await Card.findById(log.card);
    if (logCard) {
      isAlreadySold = !!logCard.inventory;
    }
  }
  let lastRequestLessThanTwelveHoursAgo = false;
  if (log) {
    // Don't query again if the request is less than 12 hours old
    lastRequestLessThanTwelveHoursAgo = moment().diff(moment(log.created, 'hours')) < config.twelveHours;
  }
  if (isAlreadySold || lastRequestLessThanTwelveHoursAgo) {
    const formattedResponse: IBiRequestResponse = await formatLogResponse(log);
    return {
      status: 200,
      message: formattedResponse
    };
  }
  // Attach card to log if they're not attached for any reason
  const attachCardRes: IGenericExpressResponse = await attachCardToLog(log, user, number, pin, retailer);
  log = attachCardRes.message;

  logParams = {
    number,
    pin,
    retailerId: retailer,
    user: user ? user._id : null,
    callbackUrl,
    autoSell,
    customer,
    lqCustomerName
  };
  if (store) {
    logParams.store = store;
  }
  // Create new log
  log = new BiRequestLog(logParams);
  // Save user to log
  if (user) {
    log.user = user._id;
  }
  if (prefix) {
    log.prefix = prefix;
  }
  log = await log.save();
  // Don't check balance on test cards
  if (testBiResponseValues.isTestCard) {
    biRes = createTestBiResponse();
  } else {
    // Initiate balance check
    biRes = await doCheckBalance(dbRetailer, user, number, pin, null, null);
  }
  log = await BiRequestLog.findById(log._id);
  let biSuccess: boolean = false;
  if (biRes && biRes.responseMessage === 'success') {
    biSuccess = biRes.responseMessage === 'success';
  }
  // Update BI log
  _.forEach(biRes, (val, prop) => {
    log[prop] = val;
  });
  if (biSuccess) {
    log.finalized = true;
  }
  log = await log.save();
  try {
    if (biSuccess && user && user._id) {
      await doCreateLogCallback(log, user._id.toString(), null, true);
    }
    // Continue with test card scenario
    if (testBiResponseValues.isTestCard) {
      params.requestId = biRes.request_id;
      await completeTestBiRequest(testBiResponseValues, params, dbRetailer._id.toString(), log, user._id.toString());
      await log.save();
    }
    return {
      status: 200,
      message: await parseBiResponseAndUpdateLog(log, biRes)
    };
  } catch (e) {
    await ErrorLog.create({
      body: params ? params : {},
      params: {},
      method: 'bi',
      controller: 'lq.controller',
      stack: e.stack,
      error: e,
    });
  }
}

/**
 * Generic response for when a specified store does not belong to a user
 * @param res
 * @returns {any}
 */
function storeDoesNotBelongToUserResponse(res) {
  return res.status(400).json({error: {errors: [{store: 'Store does not belong to user'}]}});
}

/**
 * Check balance of a card
 *
ERROR:
{
 "error": "ERROR IN CHECK GIFTCARD BALANCE."
}
DEFER:
{
 "balance": "Null",
 "response_datetime": "2016-10-05 21:52:07.807075",
 "responseMessage": "Delayed Verification Required",
 "requestId": "17452881757755311094",
 "responseCode": "010",
 "responseDateTime": "2016-10-05 21:52:07.807075",
 "recheckDateTime": "2016-10-05 22:52:37.860233"
}
SUCCESS:
{
 "responseDateTime": "2016-10-05 21:55:11.940567",
 "responseCode": "000",
 "request_id": "11502131554644889807",
 "balance": 5.5,
 "responseMessage": "success"
}
 */

/**
 * Checked - Logan - 4/3/18
 * BI-enabled card - Walmart - 5668fbff37226093139b94b5
 *  - Successful - 01
 *  - Card exists - 02
 *  - Submitted with no store - 02
 *  - Submitted with valid store - 03
 *  - Submitted with invalid store - 04
 *  - Submitted invalid retailer - 05
 *  - Submitted no PIN (none required) - 07
 *  - Submitted no PIN (PIN required) - 10
 *  - Submitted no number - 11
 * BI-disabled card (retailer exists in BI, does not have aiId or gsId) - 7/11 - 5668fbff37226093139b90c3
 *  - Rejected - 11
 * Retailer does not exist in BI DB - Earth Fare - 5668fbff37226093139b91f1
 *  - Rejected - 11
 */
export async function bi(req: Request, res: Response): Promise<Response> {
  const body: IBiRequestBody = req.body;
  let user = req.user;
  if (user && user.role === 'admin') {
    user = await User.findOne({email: body.userEmail});
    if (!user) {
      throw new DocumentNotFoundException('Unable to find user');
    }
  }
  try {
    let insertBiParams: IInsertBiParams = body;
    // Make sure we have a store
    const store = await ensureStoreBelongsToUser(insertBiParams.store, req.user);
    if (!store) {
      return storeDoesNotBelongToUserResponse(res);
    }
    insertBiParams.store = store._id.toString();
    insertBiParams.user = req.user;
    const insertBiResponse = await insertBi(insertBiParams);
    return res.status(insertBiResponse.status).json(insertBiResponse.message);
  } catch (err) {
    console.log('**************ERR IN BI**********');
    console.log(err);

    await ErrorLog.create({
      body: body ? body : {},
      params: req.params ? req.params : {},
      method: 'bi',
      controller: 'lq.controller',
      stack: err ? err.stack : null,
      error: err,

    });

    return res.status(500).json({
      invalid: 'An error has occurred.'
    });
  }
}

/**
 * Determine callback type - balanceCB for vista, otherwise biComplete
 * @param useCallbackStatus
 * @returns {string | string}
 */
function getCallbackType(useCallbackStatus: boolean) {
  return useCallbackStatus ? config.callbackTypes.biComplete : config.callbackTypes.balanceCB;
}

/**
 * Finalize card and inventory attached to log
 * @param log BI log
 * @param valid Card is valid
 * @param balance Balance (or 0 for invalid)
 * @param callbackStackId ID for callback stack
 */
async function finalizeLogCardAndInventory(log, valid, balance, callbackStackId) {
  if (log.card && (typeof log.card === 'string' || log.card.constructor.name === 'ObjectID')) {
    log = await BiRequestLog.findById(log._id).populate(logPopulationValues);
  }
  let user: IUser = null;
  if (log.user) {
    user = await User.findById(log.user).populate('company');
  }
  // Set card
  if (user && log.card && log.card.constructor.name === 'model') {
    // Resend card if balance changes
    const resend = log.card.verifiedBalance !== balance;
    log.card.valid = valid;
    log.card.verifiedBalance = balance;
    await log.card.save();
    if (log.card.inventory) {
      log.card.inventory.verifiedBalance = balance;
      await log.card.inventory.save();
    }
    // Add callback to beginning of stack
    if (log.card.inventory && !log.card.inventory.isTransaction) {
      await doCreateLogCallback(log, user._id.toString(), callbackStackId, resend);
    }
    // Set inventory values
    if (log.card.inventory && !['credit', 'denial'].includes(log.card.inventory.adjustmentStatus)) {
      if (log.card.inventory.constructor.name !== 'model') {
        if (_.isPlainObject(log.card.inventory)) {
          log.card.inventory = await Inventory.findById(log.card.inventory._id);
        } else if (log.card.inventory.constructor.name === 'ObjectID' || typeof log.card.inventory === 'string') {
          log.card.inventory = await Inventory.findById(log.card.inventory);
        }
      }
      log.card.inventory.valid = valid;
      log.card.inventory.verifiedBalance = balance;
      await log.card.inventory.save();
    }
    return await BiRequestLog.findById(log._id).populate(logPopulationValues);
  // No card, just send to company callback URL
  } else {
    if (log.user) {
      await doCreateLogCallback(log, user._id.toString(), callbackStackId);
    }
  }
  return log;
}

/**
 * Retrieve a log with card and inventory
 * @param logId
 * @return {Promise.<*>}
 */
async function getLogAndInventory(logId) {
  return await BiRequestLog.findById(logId).populate({
    path: 'card',
    populate: [
      {
        path: 'inventory',
        model: 'Inventory'
      }
    ]
  });
}


// function s3Backup() {
//   const S3 = require('aws-sdk/clients/s3');
//   const fs = require('fs');
//   const moment = require('moment');
//
//   const s3client = new S3({
//     accessKeyId: 'AKIAINKZKYLAVIQCZIEQ',
//     secretAccessKey: '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',
//     region: 'us-east-1',
//     params: {
//       Bucket: 'gcmgr-prod'
//     },
//     apiVersion: '2006-03-01'
//   });
//
//   const currentDate = moment().format('YMMDD');
//   const targetDir = `./backups/${currentDate}`;
//
//   (function () {
//     fs.readdir(targetDir, async function (err, files) {
//       if (!err) {
//         for (const file of files) {
//           const stream = fs.createReadStream(`${targetDir}/${file}`);
//           await s3client.putObject({
//             Body: stream,
//             // Use substring to get rid of `./` which would cause a
//             // recursion in S3
//             Key: `${targetDir.substring(2)}/${file}`
//           }).promise();
//         }
//       } else {
//         console.log(err);
//       }
//     });
//   })();
// }

/**
 * Complete cards and inventories associated with logs
 * @param {ICompleteCardFromBiParams} params
 * @returns {Promise<"mongoose".Types.ObjectId & IInventory>}
 */
export async function completeCardAndInventory(params: ICompleteCardFromBiParams): Promise<IGenericExpressResponse> {
  const {balance, callbackStackId} = params;
  let log = params.log;
  let invalid = '';
  const logCard: ICard = await Card.findOne({number: log.number, pin: log.pin, retailer: log.retailerId});
  if (!log.card) {
    if (logCard) {
      log.card = logCard._id;
    }
  }
  if (log.autoSell && log.user) {
    let card;
    // See if card actually exists (we used to have a bug which left a card record even after card had been deleted)
    card = logCard;
    const user = await User.findById(log.user);
    // Card exists, balance being updated
    if (card){
      const card = await Card.findById(log.card).populate('inventory');
      if (card) {
        card.verifiedBalance = balance;
        await card.save();
      }
      if (card.inventory) {
        card.inventory.verifiedBalance = balance;
        await card.inventory.save();
        // Send balanceCB for transactions
        await doCreateLogCallback(log, null, callbackStackId);
      } else {
        const addToInventoryParams: IAddToInventoryParams = {
          userTime: moment().toDate(),
          modifiedDenials: 0,
          store: log.store ? log.store.toString() : null,
          transaction: null,
          callbackUrl: log.callbackUrl,
          user,
          cards: [card]
        };
        const addToInventoryResponse: IGenericExpressResponse = await doAddToInventory(addToInventoryParams);
        if (addToInventoryResponse.status !== 200) {
          return addToInventoryResponse;
        }
        // Requery the log so we can send biComplete
        log = await getLogAndInventory(log._id);
        if (!invalid && log.card && log.card.inventory) {
          await sellCardsInLiquidation([log.card.inventory._id.toString()]);
        }
      }
    // No card, create one
    } else {
      // Allow cards which may have been marked $0 incorrectly to be corrected
      const retailer = await Retailer.findOne({retailerId: log.retailerId});
      if (retailer) {
        await Card.remove({number: log.number, pin: log.pin, retailer: retailer._id, balance: 0});
      }
      // Get either specified customer or default
      const customer = await getNewCardCustomer(null, params.user);
      const company = await Company.findById(params.user.company);
      // Create new card
      const newCardParams: ICreateNewCardParams = {
        retailer: log.retailerId.toString(),
        number: log.number,
        pin: log.pin,
        userTime: moment().toDate(),
        balance,
        lqCustomerName: log.lqCustomerName,
        customer: customer._id.toString(),
        user,
        store: log.store ? log.store.toString() : null,
        company
      };
      const newCardRes = await createNewCard(newCardParams);

      // Get back a response
      // Card is invalid or violates sell limits, still send a biComplete callback
      invalid = newCardRes.message.error;
      if (invalid) {
        // Add callback to stack
        await doCreateLogCallback(log, user._id.toString(), callbackStackId);
        // Can't continue if card exists
        return newCardRes;
      }
      log.card = newCardRes.message;
      log = await log.save();
      const addToInventoryParams: IAddToInventoryParams = {
        userTime: moment().toDate(),
        modifiedDenials: 0,
        store: log.store ? log.store.toString() : null,
        transaction: null,
        callbackUrl: log.callbackUrl,
        user,
        cards: [newCardRes.message]
      };
      const addToInventoryResponse: IGenericExpressResponse = await doAddToInventory(addToInventoryParams);
      if (addToInventoryResponse.status !== 200) {
        return addToInventoryResponse;
      }
      // Requery the log so we can send biComplete
      log = await getLogAndInventory(log._id);
      if (!invalid && log.card && log.card.inventory) {
        await sellCardsInLiquidation([log.card.inventory._id.toString()]);
      }
    }
    // Requery the log so we can send biComplete
    log = await getLogAndInventory(log._id);
  }
  if (log.user) {
    log = await finalizeLogCardAndInventory(log, balance !== 0, balance, callbackStackId);
  }
  // Save card
  if (log.card && log.card.inventory) {
    // Save inventory
    return {
      status: 200,
      message: await log.card.inventory.save()
    }
  } else {
    return {
      status: 200,
      message: await log.save()
    };
  }
}

/**
 * Complete bi logs
 * @param log BiRequestLog
 * @param balance Balance
 * @param requestId Request ID
 * @return {Promise.<*>}
 */
async function completeBiLog(log, balance, requestId): Promise<IBiRequestLog> {
  if (requestId === 'test') {
    requestId = null;
  }
  log.verificationType = 'PJVT_BOT';
  log.responseDateTime = moment().format('YYYY-MM-DD');
  log.finalized = true;
  // Success
  if (typeof balance === 'number' && balance !== 0) {
    log.balance = balance;
    log.responseCode = '000';
    log.responseMessage = 'success';
    // Invalid card
  } else {
    log.balance = 0;
    log.responseCode = '900011';
    log.responseMessage = 'invalid card';
  }
  // Fill in request ID
  if (requestId && !log.requestId) {
    log.requestId = requestId;
  }
  return await log.save();
}

/**
 * Values with which to populate logs
 * @type {{path: string, populate: [*]}}
 */
const logPopulationValues = {
  path: 'card',
  populate: [{
    path: 'inventory',
    model: 'Inventory',
    // Does this work?
    populate: [{
      path: 'company',
      model: 'Company'
    }, {
      path: 'retailer',
      model: 'Retailer'
    }, {
      path: 'store',
      model: 'Store'
    }]
  }],
};

/**
 * Create a new BI log if balance changes, or an initial BI log
 * @param {ICreateBiLogParams} params
 * @returns {Promise<IBiRequestLog>}
 */
async function createBiLogAsPartOfCompletion(params: ICreateBiLogParams): Promise<IBiRequestLog> {
  const {number, pin, retailerId, balance, userId} = params;
  // See if we can find a card associated with this log
  const findParams: IBiSearchParams = {
    number, pin
  };
  const cardFindParams: ICardSearchParams = Object.assign({}, findParams, {retailer: retailerId});
  const biFindParams: IBiSearchParams = Object.assign({}, findParams, {retailerId});
  let card: ICard = await Card.findOne(cardFindParams);
  // Get most recent log
  const logs: IBiRequestLog[] = await BiRequestLog.find(biFindParams).sort({created: -1});
  const originalLog: IBiRequestLog = logs[0];

  // No log, create one
  if (!originalLog) {
    const newLogVals: IBiRequestLogFindParams = {
      pin,
      number,
      retailerId,
      balance
    };
    if (card) {
      newLogVals.card = card._id;
    }
    if (userId) {
      newLogVals.user = userId;
    }
    let newLog: IBiRequestLog = new BiRequestLog(newLogVals);
    // Reattach card
    if (card) {
      newLog.card = card._id;
    }
    await newLog.save();
  // Update balance
  } else if (typeof originalLog.balance === 'number' && originalLog.balance !== balance) {
    originalLog.balance = balance;
    await originalLog.save();
  }
  return await BiRequestLog.findOne(biFindParams).populate(logPopulationValues);
}

/**
 * Send the callbacks in the current stack
 * @param callbackStackId
 * @return {Promise.<void>}
 */
async function sendCallbacksInStack(callbackStackId) {
  const stack = callbackStack[callbackStackId];
  let callbackNumber = -1;
  if (stack && stack.length) {
    for (const callback of stack) {
      callbackNumber = callbackNumber + 1;
      await new Promise(res => {
        setTimeout(async function () {
          await callback();
          res();
        }, config.callbackDelaySeconds * callbackNumber);
      });
    }
  }
  delete callbackStack[callbackStackId];
}

/**
 * Create a mock log which is decreasing in value due to fraud
 * @param firstLog Log with half balance
 * @param secondLog Log with quarter balance
 * @return {[Object,Object]}
 */
function createMockDecreaseLogs(firstLog, secondLog) {
  const firstDecreaseBalance = firstLog.balance / 2;
  const secondDecreaseBalance = firstDecreaseBalance / 2;
  firstLog.balance = firstDecreaseBalance;
  firstLog.card.verifiedBalance = firstDecreaseBalance;
  firstLog.card.inventory.verifiedBalance = firstDecreaseBalance;
  secondLog.balance = secondDecreaseBalance;
  secondLog.card.verifiedBalance = secondDecreaseBalance;
  secondLog.card.inventory.verifiedBalance = secondDecreaseBalance;
  return [firstLog, secondLog];
}

/**
 * Determine if the current user belongs to PS
 * @param companyId
 * @return {Promise.<boolean>}
 */
export async function callbackStatusEnabled(companyId) {
  const company = await Company.findById(companyId);
  if (company) {
    const settings: ICompanySettings = await company.getSettings();
    return settings.enableCallbackStatus;
  }
  return false;
}

/**
 * Get BI request Logs
 * @param requestId
 * @param number
 * @param pin
 * @returns {Promise<*>}
 */
async function getLogs(requestId: string, number: string, pin: string): Promise<IBiRequestLog> {
  const findByNumber: IBiSearchParams = {number};
  if (pin) {
    findByNumber.pin = pin;
  }
  let searchParams = {};
  if (requestId) {
    searchParams = {
      $or: [{
        requestId
      }, findByNumber]
    };
  } else {
    searchParams = findByNumber;
  }
  // Get most recent log
  const logs = await BiRequestLog.find(searchParams)
  .sort({created: -1})
  .limit(1)
  .populate(logPopulationValues);
  // Most recent log if we have one
  if (logs.length) {
    return logs[0];
  }
  return null;
}

/**
 * Get retailer from log
 * @returns {Promise<IRetailer | null>}
 */
async function getLogRetailer(retailerId): Promise<IRetailer> {
  const orParams = [{
    gsId: retailerId
  }, {
    aiId: retailerId
  }];
  if (Types.ObjectId.isValid(retailerId)) {
    const addMongoRetailerId: any = {
      _id: retailerId
    };
    orParams.push(addMongoRetailerId);
  }
  const params = {
    $or: orParams
  };
  return await Retailer.findOne(params);
}

function completedTransactionLog(log) {
  return log.card && log.card.inventory && log.card.inventory.isTransaction;
}

/**
 * Handle BI complete for transactions
 * @param log
 * @param balance
 * @param companySettings
 * @returns {Promise<void>}
 */
async function handleBiCompleteTransaction(log, balance, companySettings): Promise<void> {
  const inventory: IInventory = log.card.inventory;
  const inventoryTransaction: IInventoryTransaction = inventory.transaction;
  const nccCardValue: number = balance * inventoryTransaction.creditValuePercentage;
  // Recalculate transaction values
  const transaction: ICalculateTransactionResponse = calculateTransactionValues(inventoryTransaction.transactionTotal,
    inventoryTransaction.maxSpending, nccCardValue, inventoryTransaction.merchantPayoutPercentage);
  // New transaction
  inventory.transaction = Object.assign(inventory.transaction, transaction);
  inventory.transaction.nccCardValue = transaction.cardValue;
  // Verified balance has been received
  inventory.hasVerifiedBalance = true;
  await inventory.save();
  // If transaction
  if (completedTransactionLog(log) && companySettings) {
    await finalizeTransactionValues([log.card.inventory], companySettings);
  }
}

/**
 * Handle mock BI for test cards
 * @param dbLog
 * @param number
 * @param callbackStackId
 * @returns {Promise<void>}
 */
async function handleBiCompleteTestCards(dbLog, number, callbackStackId): Promise<void> {
  if (config.lqTestPatterns.paymentInitiated.test(number) && dbLog.card) {
    const card = await Card.findById(dbLog.card._id);
    const callback = new Callback(dbLog.callbackUrl);
    // Push cqPayment Initiated onto stack
    callbackStack[callbackStackId].push(callback.sendCallback.bind(callback, card, 'cqPaymentInitiated', null, true));
    // Fake denial callback
  } else if (config.lqTestPatterns.denial.test(number) && dbLog.card) {
    const card = await Card.findById(dbLog.card._id).populate('inventory');
    card.verifiedBalance = 0;
    card.inventory.verifiedBalance = 0;
    await card.inventory.save();
    await card.save();
    const callback = new Callback(dbLog.callbackUrl);
    callbackStack[callbackStackId].push(callback.sendCallback.bind(callback, card, 'denial', null, true));
  }
}

/**
 * Email credit or chargeback
 * @param type
 * @param dbLog
 * @param callbackStackId
 * @returns {Promise<void>}
 */
async function doEmulateCreditOrChargeback(type, dbLog, callbackStackId) {
  if (type === 'emulateChargeback') {
    let firstDecreaseLog = await getLogAndInventory(dbLog._id);
    let secondDecreaseLog = await getLogAndInventory(dbLog._id);
    [firstDecreaseLog, secondDecreaseLog] = createMockDecreaseLogs(firstDecreaseLog, secondDecreaseLog);
    let callback = new Callback(firstDecreaseLog.callbackUrl);
    // First chargeback
    callbackStack[callbackStackId].push(callback.sendCallback.bind(callback, firstDecreaseLog.card, 'denial', null, true));
    // Second chargeback
    callbackStack[callbackStackId].push(callback.sendCallback.bind(callback, secondDecreaseLog.card, 'denial', null, true));
    // Emulate a credit after card is accepted
  } else if (type === 'emulateCredit') {
    const creditLog = await getLogAndInventory(dbLog._id);
    const newBalance = creditLog.balance * 2;
    creditLog.balance = newBalance;
    creditLog.card.verifiedBalance = newBalance;
    creditLog.card.inventory.verifiedBalance = newBalance;
    let callback = new Callback(creditLog.callbackUrl);
    callbackStack[callbackStackId].push(callback.sendCallback.bind(callback, creditLog.card, 'credit', null, true));
  }
}

/**
 * Remove records when a test card is sent in
 * @param isTestCard
 * @param dbLog
 * @returns {Promise<void>}
 */
async function removeRecordsOnTestCard(isTestCard: boolean, dbLog: IBiRequestLog): Promise<void> {
  if (isTestCard) {
    if (dbLog.card) {
      if (dbLog.card.inventory) {
        await dbLog.card.inventory.remove();
      }
      await dbLog.card.remove();
    }
    await dbLog.remove();
  }
}

/**
 * Emulate either a credit or chargeback
 * @param {boolean} emulateCredit
 * @param {boolean} emulateChargeback
 * @param {IBiRequestLog} dbLog
 * @param {string} callbackStackId
 * @returns {Promise<void>}
 */
async function emulateCreditOrChargeback(emulateCredit: boolean, emulateChargeback: boolean,
                                         dbLog: IBiRequestLog, callbackStackId: string) {
  let emulation: string = null;
  if (emulateCredit) {
    emulation = 'emulateCredit';
  } else if (emulateChargeback) {
    emulation = 'emulateChargeback';
  }
  if (emulation) {
    await doEmulateCreditOrChargeback(emulation, dbLog, callbackStackId)
  }
}

/**
 * Determine if BI request has already been completed
 * @param {IBiRequestLog} dbLog
 * @param {ICreateBiLogParams} params
 * @returns {Promise<boolean>}
 */
async function biAlreadyCompleted(dbLog: IBiRequestLog, params: ICreateBiLogParams): Promise<boolean> {
  if (!dbLog) {
    return false;
  }
  const dbRetailer = await Retailer.findOne({$or: [{aiId: params.retailerId}, {gsId: params.retailerId}]});
  const samePinAndNumber = dbLog.number === params.number && dbLog.pin === params.pin;
  const sameRetailer = dbRetailer.gsId === params.retailerId || dbRetailer.aiId === params.retailerId;
  const sameBalance = dbLog.balance === params.balance;
  return samePinAndNumber && sameRetailer && sameBalance;
}

/**
 * Handle BI complete
 * @param {IHandleBiCompleteParams} params
 * @returns {Promise<IGenericExpressResponse>}
 */
export async function handleBiComplete(params: IHandleBiCompleteParams): Promise<IGenericExpressResponse> {
  let dbLog: IBiRequestLog;
  let isTestCard: boolean = false;
  const {number, pin, requestId, retailerId, balance, userId} = params;
  let callbackStackId: string;
  try {
    let dbCompanySettings: ICompanySettings = null;
    let emulateCredit: boolean;
    let emulateChargeback: boolean;
    // Test numbers for emulating chargebacks and credits
    isTestCard = (!isProd && config.lqTestPatterns.isTestCard.test(number));
    emulateCredit = config.lqTestPatterns.credit.test(number);
    emulateChargeback = config.lqTestPatterns.chargeback.test(number);
    // Find by request ID, then number and pin
    dbLog = await getLogs(requestId, number, pin);
    // Don't continue if this is a repeat
    if (await biAlreadyCompleted(dbLog, params)) {
      return {
        message: {},
        status: 200
      };
    }
    // Put callbacks into a stack which we can send at the end
    callbackStackId = params.callbackStackId || uuid();
    // Create new stack
    callbackStack[callbackStackId] = callbackStack[callbackStackId] ? callbackStack[callbackStackId] : [];
    // Get retailers
    const retailer: IRetailer = await getLogRetailer(retailerId);

    const completeBiLogParams: ICreateBiLogParams = {
      number,
      pin,
      retailerId: retailer._id.toString(),
      balance,
      userId
    };

    // No log, or create a new log if the balance has changed
    if (!dbLog || (typeof dbLog.balance === 'number' && dbLog.balance !== balance)) {
      dbLog = await createBiLogAsPartOfCompletion(completeBiLogParams);
    }
    // If we have a previously completed log, see if we need to make a new one
    dbLog = await completeBiLog(dbLog, balance, requestId);
    let user: IUser = null;
    if (dbLog.user) {
      user = await User.findById(dbLog.user);
    }
    // Body for mocking subsequent requests
    const body: ICompleteCardFromBiParams = {
      log: dbLog,
      balance,
      callbackStackId,
      user
    };
    // Complete card, send callback, etc
    const completeCardResponse: IGenericExpressResponse = await completeCardAndInventory(body);
    if (completeCardResponse.status !== config.statusCodes.success) {
      if (callbackStackId) {
        await sendCallbacksInStack(callbackStackId);
      }
      return completeCardResponse;
    }
    // Find logs
    dbLog = await BiRequestLog.findById(dbLog._id)
    .populate(logPopulationValues);
    // Get settings if we have an inventory
    if (dbLog && dbLog.card && dbLog.card.inventory && dbLog.card.inventory.isTransaction) {
      dbCompanySettings = await dbLog.card.inventory.company.getSettings();
      await handleBiCompleteTransaction(dbLog, balance, dbCompanySettings);
    }
    // Handle fake card actions
    await handleBiCompleteTestCards(dbLog, number, callbackStackId);
    // Emulate a chargeback by pushing two decreases
    await emulateCreditOrChargeback(emulateCredit, emulateChargeback, dbLog, callbackStackId);
    // Send callbacks in stack
    await sendCallbacksInStack(callbackStackId);
    // Remove test requests
    await removeRecordsOnTestCard(isTestCard, dbLog);
    return {
      status: 200,
      message: {}
    };
  } catch (err) {
    console.log('**************COMPLETE BI ERR**********');
    console.log(err);
    if (err) {
      console.log(err.stack);
    }
    // Remove test requests
    if (isTestCard) {
      if (dbLog && dbLog.card) {
        if (dbLog.card.inventory) {
          await dbLog.card.inventory.remove();
        }
        await dbLog.card.remove();
      }
      await dbLog.remove();
    }
    // Send callbacks in stack
    await sendCallbacksInStack(callbackStackId);

    throw err;
  }
}

/**
 * BI completed
 */

/**
 * Checked - Logan - 3/26/18
 * BI-enabled card - Walmart - 5668fbff37226093139b94b5
 *  - Successful - //
 *  - Card submitted via /new, then /bi - //
 *  - Card submitted via /new, solved, then /bi - //
 *  - Card submitted via /bi, then /new - //
 *  - Card submitted via /bi, then solved, then /new - //
 *  - Violates sell limits - /
 *  - Repeated callbacks - /
 */
export async function biCompleted(req: Request, res: Response) {
  const body: IBiCompletedParams = req.body;
  try {
    const key = req.get(config.biCallbackKeyHeader);
    // Make sure that we have the right key for callback
    if (key !== config.biCallbackKey) {
      return res.status(401).send('Unauthorized');
    }
    const {retailerId, number, pin, userId} = body;
    let balance: number = 0;
    // Get balance
    if (typeof body.balance === 'string') {
      balance = parseFloat(body.balance);
    } else if (typeof body.balance === 'number') {
      balance = body.balance;
    } else {
      return res.status(400).json('Balance must sent as a string or number');
    }

    // Handle BI complete
    const handleBiCompleteParams: IHandleBiCompleteParams = {
      requestId: req.params.requestId,
      retailerId,
      number,
      pin,
      balance,
      callbackStackId: null,
      userId
    };
    // Handle bi complete functionality
    const handleBiResponse = await handleBiComplete(handleBiCompleteParams);
    return res.status(handleBiResponse.status).json(handleBiResponse.message);
  } catch (err) {
    // Don't record violate sell limits in error log
    if (err && err.message !== 'Card violates sell limits') {
      await ErrorLog.create({
        body: body ? body : {},
        params: req.params ? req.params : {},
        method: 'biCompleted',
        controller: 'lq.controller',
        stack: err ? err.stack : null,
        error: err,
      });
    } else if (err && err.message === 'Card violates sell limits') {
      return res.status(400).json({invalid: err});
    }

    return res.status(500).json({
      invalid: 'An error has occurred.'
    });
  }
}

/**
 * Fake card status values
 * @param cardId Incoming card ID
 * @return {*}
 */
function fakeCardStatus(cardId) {
  if (cardId.indexOf(config.testCardBegin) !== -1) {
    if (cardId === testCard1) {
      return {
        "created": "2017-01-23T15:07:00-05:00",
        "lastFour": "1000",
        "pin": null,
        "status": "Not shipped",
        "claimedBalance": 100,
        "verifiedBalance": 0,
        "soldFor": 0,
        "sellRate": 0.75,
        "reconciled": false,
        "retailer": "Adidas",
        "saleConfirmed": true,
        "saleAccepted": true,
        "saleVerified": false,
        "saleFinal": false,
        "claimedBalanceInaccurate": false
      };
    } else if (cardId === testCard2) {
      return {
        "created": "2017-01-23T15:07:00-05:00",
        "lastFour": "1000",
        "pin": null,
        "status": "Not shipped",
        "claimedBalance": 100,
        "verifiedBalance": 100,
        "soldFor": 75,
        "sellRate": 0.75,
        "reconciled": false,
        "retailer": "Adidas",
        "saleConfirmed": true,
        "saleAccepted": true,
        "saleVerified": false,
        "saleFinal": false,
        "claimedBalanceInaccurate": false
      };
    } else if (cardId === testCard3) {
      return {
        "created": "2017-01-23T15:07:00-05:00",
        "lastFour": "1000",
        "pin": null,
        "status": "Not shipped",
        "claimedBalance": 100,
        "verifiedBalance": 100,
        "soldFor": 75,
        "sellRate": 0.75,
        "reconciled": false,
        "retailer": "Adidas",
        "saleConfirmed": false,
        "saleAccepted": true,
        "saleVerified": false,
        "saleFinal": false,
        "claimedBalanceInaccurate": false
      };
    } else if (cardId === testCard4) {
      return {
        "created": "2017-01-23T15:07:00-05:00",
        "lastFour": "1000",
        "pin": null,
        "status": "Not shipped",
        "claimedBalance": 100,
        "verifiedBalance": 0,
        "soldFor": 0,
        "sellRate": 0.75,
        "reconciled": false,
        "retailer": "Adidas",
        "saleConfirmed": true,
        "saleAccepted": true,
        "saleVerified": false,
        "saleFinal": false,
        "claimedBalanceInaccurate": false
      };
    }
  }
  return false;
}

/**
 * Get card status after sale
 GET http://localhost:9000/api/lq/status/:cardId
 GET http://localhost:9000/api/lq/status/begin/:begin/end/:end
 GET http://localhost:9000/api/lq/status/begin/:begin
 GET http://localhost:9000/api/lq/status/end/:end
 HEADERS
 Params
 {
 "cardId":"57ffbdd5283e93464809c84b",
 "begin":"2016-11-18T18:03:46-05:00", (optional param, format ISO 8601)
 "end":"2016-11-18T18:03:46-05:00" (optional param, format ISO 8601)
 }
 RESPONSE
 {
  "created": "2016-10-13T20:34:50-04:00",
  "lastFour": "2053",
  "pin": "3313",
  "status": "Received by CQ",
  "claimedBalance": 300,
  "verifiedBalance": 53,
  "soldFor": 36.84,
  "sellRate": 0.695,
  "reconciled": false
}
 */
export async function getCardStatus(req: Request, res: Response) {
  try {
    const {cardId} = req.params;
    const userTime = formatDateParams(req.params, res);
    // Validate card ID
    if (cardId) {
      if (cardId.indexOf(config.testCardBegin) === -1 && !mongoose.Types.ObjectId.isValid(cardId)) {
        return res.status(400).json({error: 'Invalid card ID'});
      }
    }
    let search;
    const user = req.user;
    if (cardId) {
      // Test cards
      const testVal = fakeCardStatus(cardId);
      if (testVal) {
        return res.json(testVal);
      }
      let card = await Card.findOne({
        _id: cardId,
        user: user._id
      })
      .populate('inventory')
      .populate('retailer');
      if (!card) {
        return res.status(400).json({error: 'Card not found'});
      }

      let cardObject: any = card.toObject();
      const inventory = card.inventory;
      // No inventory
      if (!inventory) {
        return res.status(500).json({error: "Card data invalid"});
      }

      cardObject.saleConfirmed = !(inventory.smp === '1' && inventory.saveYa && !inventory.saveYa.confirmed);

      cardObject = formatCardStatusResults(cardObject);
      cardObject = decorateCardWithSaleStatuses(Object.assign(cardObject, {balance: cardObject.claimedBalance}), inventory);
      delete cardObject.balance;

      return res.json(cardObject);
    } else {
      const query: ICardStatusQuery = {
        user: user._id,
      };
      if (userTime) {
        query.userTime = userTime;
      }
      search = Card.find(query)
      .populate('inventory')
      .populate('retailer')
      .sort({
        userTime: -1
      })
      .then(cards => {
        let processedCards = [];

        cards.forEach(card => {
          const thisInventory: IInventory = card.inventory;
          if (!thisInventory) {
            return;
          }
          card.saleConfirmed = !(thisInventory.smp === '1' && thisInventory.saveYa && !thisInventory.saveYa.confirmed);
          const cardObject = {
            inventory: thisInventory,
            saleConfirmed: !(thisInventory.smp === '1' && thisInventory.saveYa && !thisInventory.saveYa.confirmed)
          };

          card = formatCardStatusResults(cardObject);
          const formattedCard: ICardForDecoration = {
            balance: card.claimedBalance,
          };
          const decoratedCard: IDecoratedCard = decorateCardWithSaleStatuses(formattedCard, thisInventory);
          delete decoratedCard.balance;

          processedCards.push(decoratedCard);
        });

        res.json(processedCards);
      });
    }
  } catch(err) {
    console.log('**************ERROR**********');
    console.log(err);
    if (err && (err.message === 'invalidBegin' || err.message === 'invalidEnd')) {
      return;
    }

    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'getCardStatus',
      controller: 'lq.controller',
      stack: err.stack,
      error: err,

    })
      .then(()=> {
        return res.status(500).json({
          invalid: 'An error has occurred.'
        });
      });
  }
}

/**
 * Format date params when searching cards
 * @param params
 * @param res
 */
function formatDateParams(params, res): ICardDateQueryParams {
  let {begin, end} = params;
  let userTime;
  if (begin) {
    begin = moment(begin);
    if (begin.isValid()) {
      userTime = {
        $gt: begin.format()
      };
    } else {
      res.status(400).json({error: 'Invalid begin date'});
      throw new Error('invalidBegin');
    }
  }
  if (end) {
    end = moment(end);
    if (end.isValid()) {
      if (!userTime) {
        userTime = {};
      }
      userTime.$lt = end.format();
    } else {
      res.status(400).json({error: 'Invalid end date'});
      throw new Error('invalidEnd');
    }
  }
  return userTime;
}

/**
 * Format results when getting card statuses
 * @param card Single card to format
 */
function formatCardStatusResults(card) {
  try {
    let status;
    if (typeof card.toObject === 'function') {
      card = card.toObject();
    }
    switch (card.inventory.activityStatus) {
      case 'shipped':
        status = 'Shipped to CQ';
        break;
      case 'receivedCq':
      case 'sentToSmp':
      case 'receivedSmp':
        status = 'Received by CQ';
        break;
      case 'rejected':
        status = 'Rejected';
        break;
      default:
        status = 'Not shipped';
    }
    const displaySellRate = formatFloat(card.inventory.liquidationRate - card.inventory.margin);
    let balanceForCalculations;
    balanceForCalculations = card.inventory.verifiedBalance ? card.inventory.verifiedBalance : card.inventory.balance;
    let soldFor = balanceForCalculations * displaySellRate;
    if (isNaN(soldFor)) {
      soldFor = 0;
    }
    const saleFinal = !!card.inventory.cqAch;
    return {
      _id: card._id,
      created: moment(card.userTime).format(),
      lastFour: card.number.substring(card.number.length - 4),
      pin: card.pin,
      status,
      claimedBalance: card.balance,
      verifiedBalance: saleFinal ? (card.inventory.verifiedBalance || card.inventory.balance) : (card.inventory.verifiedBalance || null),
      soldFor: formatFloat(soldFor),
      sellRate: displaySellRate,
      reconciled: !!card.inventory.reconciliation,
      retailer: card.retailer.name,
      saleConfirm: card.saleConfirmed
    };
  } catch(e) {
    e = e.toString();
    console.log('**************ERR IN LQ FORMATCARDSTATUSRESULTS**********');
    console.log(e);
    switch (true) {
      // Retailer missing
      case /name/.test(e):
        card.retailer = {};
        return formatCardStatusResults(card);
      // Number missing
      case /substring/.test(e):
        card.number = null;
        return formatCardStatusResults(card);
      // Pin
      case /pin/.test(e):
        card.pin = null;
        return formatCardStatusResults(card);
      // Inventory error
      case /(verifiedBalance|reconciliation)/.test(e):
        card.inventory = {};
        return formatCardStatusResults(card);
      // Sold for
      case /toFixed/.test(e):
        card.soldFor = 0;
        return formatCardStatusResults(card);
      default:
        throw new Error('unknown');
    }
  }
}

/**
 * Add card to reconciliation
 PATCH http://localhost:9000/api/lq/reconcile
 HEADERS
 BODY
 {
 "cardId":"57ffbdd5283e93464809c84b",
 "userTime":"2016-09-10T20:34:50-04:00",
 }
 RESPONSE 200
 */
export async function reconcile(req: Request, res: Response) {
  const {cardId, userTime} = req.body;
  let card: ICard;
  if (!cardId || !userTime) {
    return res.status(400).json({
      invalid: 'Include the following POST parameters: cardId, userTime'
    });
  }

  card = await Card.findOne({
    _id: cardId,
    user: req.user._id
  })
  .populate('inventory');
  if (!card) {
    return res.status(403).json({error: 'Card not found'});
  }
  if (card.reconciliation) {
    return res.status(400).json({error: 'Card already reconciled'});
  }
  const reconciliation = new Reconciliation({
    userTime,
    inventory: card.inventory._id
  });
  const dbReconciliation: IReconciliation = await reconciliation.save();
  if (!dbReconciliation) {
    return;
  }
  card.inventory.reconciliation = dbReconciliation._id;
  const inventory: IInventory = await card.inventory.save();
  if (!inventory) {
    return;
  }
  res.status(200).json();
}

/**
 * Get company info
 * @param {e.Request} req
 * @param {e.Response} res
 * @returns {Promise<void>}
 */
export async function getCompanyInfo(req: Request, res: Response) {
  const company = await Company.findById(req.params.companyId, 'name address1 address2 city state zip autoSell stores users bookkeepingEmails')
    .populate('stores', '-__v -companyId -reconciledTime -reserves -buyRateRelations -reserveTotal -payoutAmountPercentage -maxSpending -creditValuePercentage -created')
    .populate('users', '-company -__v');
  return res.json(company);
}

/**
 * Get company settings
GET http://gcmgr-staging.cardquiry.com:9000/api/lq/company/:companyId/settings
HEADERS
Accept: application/json
Content-Type: application/json
Authorization: bearer <token>
Params
{
"companyId": "56637dd6295c4d131c901ba1"
}
Response
{
"cardType": "electronic",
"autoSell": true,
"minimumAdjustedDenialAmount": 0.1,
"biOnly": true,
"enableCallbackStatus": false,
"validateCard": false
}
 */
export async function getCompanySettings(req: Request, res: Response) {
  try {
    const {companyId} = req.params;

    const company = await Company.findById(companyId);
    if (!company) {
      return res.status(400).json({err: 'Company not found'});
    }
    const settings = await company.getSettings();
    return res.json({
      cardType: settings.cardType || 'both',
      autoSell: settings.autoSell,
      minimumAdjustedDenialAmount: settings.minimumAdjustedDenialAmount,
      biOnly: settings.biOnly || false,
      customerDataRequired: settings.customerDataRequired,
      reserveTotal: company.reserveTotal,
      callbackUrl: settings.callbackUrl,
      enableCallbackStatus: settings.enableCallbackStatus || false,
      enableChargebackCallback: settings.enableChargebackCallback || false,
      validateCard: settings.validateCard || false,
      disableLimits: settings.disableLimits || false,
      enableCurrencyCode: settings.enableCurrencyCode || false,
      globalLimits: settings.globalLimits || false
    });
  } catch (err) {
    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'getCompanySettings',
      controller: 'lq.controller',
      stack: err ? err.stack : null,
      error: err,

    })
    .then(()=> {
      return res.status(500).json({
        invalid: 'An error has occurred.'
      });
    });
  }
}

/**
 *Update company settings
PATCH http://gcmgr-staging.cardquiry.com:9000/api/lq/company/:companyId/settings
HEADERS
Accept: application/json
Content-Type: application/json
Authorization: bearer <token>
Params
{
"companyId": "56637dd6295c4d131c901ba1"
}
Body
{
"cardType": "electronic",
"autoSell": true,
"minimumAdjustedDenialAmount": 0.1,
"biOnly": true,
"customerDataRequired": true,
"callbackUrl": "www.testcall.com",
"enableCallbackStatus": true,
"validateCard": true
}
Response
200
 */
export async function updateCompanySettings(req: Request, res: Response) {
  try {
    const {companyId} = req.params;
    const body = req.body;

    const company = await Company.findById(companyId);
    let settings = await company.getSettingsObject();
    [
      'cardType',
      'autoSell',
      'biOnly',
      'customerDataRequired',
      'minimumAdjustedDenialAmount',
      'callbackUrl',
      'enableCallbackStatus',
      'enableChargebackCallback',
      'validateCard',
      'disableLimits',
      'enableCurrencyCode',
      'globalLimits',
      'lqCustomerNameEnabled'
    ].forEach(attr => {
      if (typeof body[attr] !== 'undefined') {
        settings[attr] = body[attr];
      }
    });
    if (typeof body.callbackToken !== 'undefined') {
      settings.callbackTokenEnabled = body.callbackToken;
    }
    settings = await settings.save();

    const formattedSettings: IFormattedSettings = {
      cardType: settings.cardType || 'both',
      autoSell: settings.autoSell,
      biOnly: settings.biOnly || false,
      minimumAdjustedDenialAmount: settings.minimumAdjustedDenialAmount,
      customerDataRequired: settings.customerDataRequired,
      callbackUrl: settings.callbackUrl,
      enableCallbackStatus: settings.enableCallbackStatus || false,
      enableChargebackCallback: settings.enableChargebackCallback || false,
      validateCard: settings.validateCard || false,
      disableLimits: settings.disableLimits || false,
      enableCurrencyCode: settings.enableCurrencyCode || false,
      globalLimits: settings.globalLimits || false
    };
    if (settings.callbackToken) {
      formattedSettings.callbackToken = settings.callbackToken;
    }
    return res.json(formattedSettings);
  } catch (err) {
    await await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'updateCompanySettings',
      controller: 'lq.controller',
      stack: err ? err.stack : null,
      error: err,

    });
    return res.status(500).json({
      invalid: 'An error has occurred.'
    });
  }
}

/**
 * Mark a card for sale
 PATCH http://gcmgr-staging.cardquiry.com:9000/api/lq/card/:cardId/proceed-with-sale
 HEADERS
 Accept: application/json
 Content-Type: application/json
 Authorization: bearer <token>
 Params
 {
 "cardId": "5668fbff37229093139b93d1"
 }
 Response
 200
 */
export function proceedWithSale(req: Request, res: Response) {
  const {cardId} = req.params;

  if (!mongoose.Types.ObjectId.isValid(cardId)) {
    return res.status(400).json({error: 'Invalid card ID'});
  }

  Card.findById(cardId)
  .populate('inventory')
  .then(card => {
    if (!card) {
      throw new Error('notFound');
    }
    const inventory = card.inventory;
    inventory.proceedWithSale = true;
    return inventory.save();
  })
  .then(() => res.json())
  .catch(async err => {
    if (err && err.message === 'notFound') {
      return res.status(400).json({error: 'Card not found'});
    }

    console.log('*******************ERR IN PROCEEDWITHSALE*******************');
    console.log(err);

    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'proceedWithSale',
      controller: 'lq.controller',
      stack: err ? err.stack : null,
      error: err,

    })
    .then(()=> {
      return res.status(500).json({
        invalid: 'An error has occurred.'
      });
    });
  });
}

/**
 * Get customers for this store
 */
export function getStoreCustomers(req: Request, res: Response) {
  req.params.store = req.params.storeId;
  return getCustomersThisStore(req, res);
}

/**
 * Search customers
GET http://gcmgr-staging.cardquiry.com:9000/api/lq/customers/search/:customerName
HEADERS
Accept: application/json
Content-Type: application/json
Authorization: bearer <token>
Params
{
"customerName": "Blah"
}
RESULT:
[
 {
   "_id": "56cca6cf780b493151881a58",
   "fullName": "Blah Blah Blah",
   "state": "AR",
   "company": "56637dd6295c4d131c901ba1",
   "firstName": "Blah",
   "middleName": "Blah",
   "lastName": "Blah",
   "stateId": "53532523",
   "phone": "513-404-7626",
   "address1": "1",
   "address2": "1",
   "city": "1",
   "zip": "44444",
   "systemId": "444444",
   "__v": 0,
   "credits": [],
   "rejections": [
     "57e891c5cc40659d2804d9f9",
     "57e8948ecc40659d2804da09",
     "573dff03dcd0429650cb27dc"
   ],
   "edits": [],
   "store": [],
   "rejectionTotal": 0,
   "created": "2016-02-23T18:37:03.876Z",
   "id": "56cca6cf780b493151881a58"
 },
 ...
]
 */
export function searchCustomers(req: Request, res: Response) {
  req.query.name = req.params.customerName;

  return searchCustomersCustomerController(req, res);
}

/**
 * Get a specific customer
GET http://gcmgr-staging.cardquiry.com:9000/api/lq/customers/:customerId
HEADERS
Accept: application/json
Content-Type: application/json
Authorization: bearer <token>
Params
{
 "customerId": "56cca6cf780b493151881a58"
}
RESULT:
{
 "_id": "56cca6cf780b493151881a58",
 "fullName": "Blah Blah Blah",
 "state": "AR",
 "company": "56637dd6295c4d131c901ba1",
 "firstName": "Blah",
 "middleName": "Blah",
 "lastName": "Blah",
 "stateId": "53532523",
 "phone": "513-404-7626",
 "address1": "1",
 "address2": "1",
 "city": "1",
 "zip": "44444",
 "systemId": "444444",
 "__v": 0,
 "credits": [],
 "rejections": [
   "57e891c5cc40659d2804d9f9",
   "57e8948ecc40659d2804da09",
   "573dff03dcd0429650cb27dc"
 ],
 "edits": [],
 "store": [],
 "rejectionTotal": 0,
 "created": "2016-02-23T18:37:03.876Z",
 "id": "56cca6cf780b493151881a58"
}
  */
export function getCustomer(req: Request, res: Response) {
  const {customerId} = req.params;
  const company = req.user.company;

  if (mongoose.Types.ObjectId.isValid(customerId)) {
    Customer.findOne({_id: customerId, company}).then(customer => {
      // Not found
      if (!customer) {
        return res.status(404).json();
      }

      return res.json(customer);
    });
  } else {
    return res.status(invalidObjectId.code).json(invalidObjectId.res);
  }
}

/**
 * Delete a customer
 */
export function deleteCustomer(req: Request, res: Response) {
  Customer.findById(req.params.customerId)
  .then(customer => {
    // No customer
    if (!customer) {
      res.status(notFound.code).json(notFound.res);
      throw new Error('notFound');
    }
    customer.enabled = false;
    return customer.save();
  })
  .then(() => res.json({}))
  .catch(async err => {
    if (err && err.message === 'notFound') {
      return;
    }

    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'deleteCustomer',
      controller: 'lq.controller',
      stack: err ? err.stack : null,
      error: err,

    })
    .then(()=> {
      return res.status(500).json({
        invalid: 'An error has occurred.'
      });
    });
  })
}

/**
 * Create a new customer
POST http://gcmgr-staging.cardquiry.com:9000/api/lq/customers
HEADERS
Accept: application/json
Content-Type: application/json
Authorization: bearer <token>
BODY
{
  "state": "AL",
  "firstName": "John",
  "middleName": "Q",
  "lastName": "Public",
  "stateId": "1ABC",
  "phone": "111-879-8765",
  "address1": "123 Abc Street",
  "address2": "Ct. #100",
  "city": "Adamsville",
  "zip": "35005",
  "systemId": "1148832"
}
RESULT
{
  "__v": 0,
  "fullName": "John Q Public",
  "company": "56637dd6295c4d131c901ba1",
  "state": "AL",
  "firstName": "John",
  "middleName": "Q",
  "lastName": "Public",
  "stateId": "1ABC",
  "phone": "111-879-8765",
  "address1": "123 Abc Street",
  "address2": "Ct. #100",
  "city": "Adamsville",
  "zip": "35005",
  "systemId": "1148832",
  "_id": "59079ad0565cb21e5458e894",
  "credits": [],
  "rejections": [],
  "edits": [],
  "store": [],
  "rejectionTotal": 0,
  "created": "2017-05-01T20:30:08.440Z",
  "id": "59079ad0565cb21e5458e894"
}
 */
export function newCustomer(req: Request, res: Response) {
  req.user.store = req.params.storeId;
  req.body.store = req.params.storeId;
  return newCustomerCustomerController(req, res);
}

/**
 * Update a customer
PATCH http://gcmgr-staging.cardquiry.com:9000/api/lq/customers/:customerId
HEADERS
Accept: application/json
Content-Type: application/json
Authorization: bearer <token>
Params
{
  "customerId": "56cca6cf780b493151881a58"
}
BODY
{
  "state": "AL",
  "firstName": "John",
  "middleName": "Q",
  "lastName": "Public",
  "stateId": "1ABC",
  "phone": "111-879-8765",
  "address1": "123 Abc Street",
  "address2": "Ct. #100",
  "city": "Adamsville",
  "zip": "35005",
  "enabled": true
}
RESULT
200
 */
export function updateCustomer(req: Request, res: Response) {
  return updateCustomerCustomerController(req, res);
}

/**
 * Create a new store
POST http://gcmgr-staging.cardquiry.com:9000/api/lq/stores
HEADERS
Accept: application/json
Content-Type: application/json
Authorization: bearer <token>
BODY
{
  "name": "New Store",
  "address1": "123 Abc Street",
  "address2": "Ct. #100",
  "city": "Adamsville",
  "state": "AL",
  "zip": "35005",
  "contact": {
    "firstName": "John",
    "role": "employee",
    "lastName": "Public",
    "email": "johnq@public.com",
    "password": "123456"
  },
  "creditValuePercentage": 1.1,
  "maxSpending": 30,
  "payoutAmountPercentage": 0.2
}
RESULT
{
  "_id": "56cca6cf780b493151881a59"
}
*/
export function createStore(req: Request, res: Response) {
  req.body.companyId = req.user.company;
  return newStore(req, res);
}

/**
 * Update a store
PATCH http://gcmgr-staging.cardquiry.com:9000/api/lq/stores/:storeId
HEADERS
Accept: application/json
Content-Type: application/json
Authorization: bearer <token>
PARAMS
{
  "storeId": "56cca6cf780b493151881a59"
}
BODY
{
  "name": "New Store",
  "address1": "123 Abc Street",
  "address2": "Ct. #100",
  "city": "Adamsville",
  "state": "AL",
  "zip": "35005",
  "phone": "111-555-8888",
  "creditValuePercentage": 120,
  "maxSpending": 50,
  "payoutAmountPercentage": 35
}
RESULT
{
  "_id":"56cca6cf780b493151881a59",
  "name": "New Store",
  "address1": "123 Abc Street",
  "address2": "Ct. #100",
  "city": "Adamsville",
  "state": "AL",
  "zip": "35005",
  "phone": "111-555-8888",
  "companyId": "56637dd6295c4d131c901ba1",
  "reconciledTime": "2017-05-02T22:33:23.191Z",
  "created": "2015-12-07T03:57:47.461Z",
  "creditValuePercentage": 120,
  "maxSpending": 50,
  "payoutAmountPercentage": 35
}
*/
export function updateStore(req: Request, res: Response) {
  req.body.storeId = req.params.storeId;

  // Prevents them from being able to change the companyId.
  // This attribute should be ignored in the future.
  if (req.body.companyId) {
    req.body.companyId = req.user.company;
  }

  return updateStoreCompanyController(req, res);
}

/**
 * Retrieve all stores
GET http://gcmgr-staging.cardquiry.com:9000/api/lq/stores
HEADERS
Accept: application/json
Content-Type: application/json
Authorization: bearer <token>
RESULT
[
  {
    "_id":"56cca6cf780b493151881a59",
    "name": "New Store",
    "address1": "123 Abc Street",
    "address2": "Ct. #100",
    "city": "Adamsville",
    "state": "AL",
    "zip": "35005",
    "phone": "111-555-8888",
    "companyId": "56637dd6295c4d131c901ba1",
    "reconciledTime": "2017-05-02T22:33:23.191Z",
    "created": "2015-12-07T03:57:47.461Z",
    "creditValuePercentage": 120,
    "maxSpending": 50,
    "payoutAmountPercentage": 35
    "users": [
      {
        "_id": "590bb39363f76f1aab9cb717",
        "store": "56cca6cf780b493151881a59",
        "firstName": "John",
        "lastName": "Public",
        "email": "johnq@public.com",
        "__v": 0,
        "company": "56637dd6295c4d131c901ba1",
        "created": "2017-05-04T23:04:51.694Z",
        "role": "employee",
        "profile": {
          "lastName": "Public",
          "firstName": "John",
          "email": "johnq@public.com",
          "_id": "590bb39363f76f1aab9cb717"
        },
        "token": {
          "role": "employee",
          "_id": "590bb39363f76f1aab9cb717"
        },
        "fullName": "John Public",
        "id": "590bb39363f76f1aab9cb717"
      }
    ]
  },
  ...
]
*/
export function getStores(req: Request, res: Response) {
  req.params.companyId = req.user.company;
  return getStoresCompanyController(req, res);
}

/**
 * Retrieve a store
GET http://gcmgr-staging.cardquiry.com:9000/api/lq/stores/:storeId
HEADERS
Accept: application/json
Content-Type: application/json
Authorization: bearer <token>
PARAMS
{
  "storeId": "56cca6cf780b493151881a59"
}
RESULT
{
  "_id":"56cca6cf780b493151881a59",
  "name": "New Store",
  "address1": "123 Abc Street",
  "address2": "Ct. #100",
  "city": "Adamsville",
  "state": "AL",
  "zip": "35005",
  "phone": "111-555-8888",
  "companyId": "56637dd6295c4d131c901ba1",
  "reconciledTime": "2017-05-02T22:33:23.191Z",
  "created": "2015-12-07T03:57:47.461Z",
  "creditValuePercentage": 120,
  "maxSpending": 50,
  "payoutAmountPercentage": 35
  "users": [
    {
      "_id": "590bb39363f76f1aab9cb717",
      "store": "56cca6cf780b493151881a59",
      "firstName": "John",
      "lastName": "Public",
      "email": "johnq@public.com",
      "__v": 0,
      "company": "56637dd6295c4d131c901ba1",
      "created": "2017-05-04T23:04:51.694Z",
      "role": "employee",
      "profile": {
        "lastName": "Public",
        "firstName": "John",
        "email": "johnq@public.com",
        "_id": "590bb39363f76f1aab9cb717"
      },
      "token": {
        "role": "employee",
        "_id": "590bb39363f76f1aab9cb717"
      },
      "fullName": "John Public",
      "id": "590bb39363f76f1aab9cb717"
    }
  ]
}
*/
export function getStore(req: Request, res: Response) {
  return getStoreDetails(req, res);
}

/**
 * Delete a store
DELETE http://gcmgr-staging.cardquiry.com:9000/api/lq/stores/:storeId
HEADERS
Accept: application/json
Content-Type: application/json
Authorization: bearer <token>
PARAMS
{
  "storeId": "56cca6cf780b493151881a59"
},
RESULT
200
*/
export function deleteStore(req: Request, res: Response) {
  return deleteStoreCompanyController(req, res);
}

/**
 * Create an employee
POST http://gcmgr-staging.cardquiry.com:9000/api/lq/stores/:storeId/employees
HEADERS
Accept: application/json
Content-Type: application/json
Authorization: bearer <token>
PARAMS
{
  "storeId": "56cca6cf780b493151881a59"
}
BODY
{
  "firstName": "John",
  "lastName": "Public",
  "email": "johnq@public.com",
  "password": "123456",
  "role": "employee"
}
RESULT
{
  "_id": "590bb39363f76f1aab9cb717",
  "store": "56cca6cf780b493151881a59",
  "firstName": "John",
  "lastName": "Public",
  "email": "johnq@public.com",
  "__v": 0,
  "company": "56637dd6295c4d131c901ba1",
  "created": "2017-05-04T23:04:51.694Z",
  "role": "employee",
  "profile": {
    "lastName": "Public",
    "firstName": "John",
    "email": "johnq@public.com",
    "_id": "590bb39363f76f1aab9cb717"
  },
  "token": {
    "role": "employee",
    "_id": "590bb39363f76f1aab9cb717"
  },
  "fullName": "John Public",
  "id": "590bb39363f76f1aab9cb717"
}
 */
export function createEmployee(req: Request, res: Response) {
  req.body.companyId = req.user.company.toString();
  req.body.storeId = req.params.storeId;

  if (req.user.role === 'manager' && req.body.role === 'corporate-admin') {
    return res.status(401).json({error: "Managers can't create corporate admin accounts"});
  }

  return newEmployee(req, res);
}

/**
 * Update an employee
PATCH http://gcmgr-staging.cardquiry.com:9000/api/lq/stores/:storeId/employees/:employeeId
HEADERS
Accept: application/json
Content-Type: application/json
Authorization: bearer <token>
PARAMS
{
  "storeId": "56cca6cf780b493151881a59",
  "employeeId": "590bb39363f76f1aab9cb717"
}
BODY
{
  "firstName": "John",
  "lastName": "Public",
  "email": "johnq@public.com",
  "password": "123456",
  "role": "employee"
}
RESULT
{
  "_id": "590bb39363f76f1aab9cb717",
  "store": "56cca6cf780b493151881a59",
  "firstName": "John",
  "lastName": "Public",
  "email": "johnq@public.com",
  "__v": 0,
  "company": "56637dd6295c4d131c901ba1",
  "created": "2017-05-04T23:04:51.694Z",
  "role": "employee",
  "profile": {
    "lastName": "Public",
    "firstName": "John",
    "email": "johnq@public.com",
    "_id": "590bb39363f76f1aab9cb717"
  },
  "token": {
    "role": "employee",
    "_id": "590bb39363f76f1aab9cb717"
  },
  "fullName": "John Public",
  "id": "590bb39363f76f1aab9cb717"
}
*/
export function updateEmployee(req: Request, res: Response) {
  let fakeReq, fakeRes;
  [fakeReq, fakeRes] = makeFakeReqRes(req);
  fakeReq.params = req.params;
  fakeReq.params.id = req.params.employeeId;

  modifyUser(fakeReq, fakeRes)
  .then(() => {
    if (fakeRes.code) {
      return res.status(fakeRes.code).json(fakeRes.response);
    }

    return fakeRes.response;
  })
  .then(user => {
    if (req.body.role) {
      if (req.user.role === 'manager' && ['manager', 'employee'].indexOf(req.body.role) !== -1) {
        user.role = req.body.role;
      }

      if (req.user.role === 'corporate-admin' && ['manager', 'employee', 'corporate-admin'].indexOf(req.body.role) !== -1) {
        user.role = req.body.role;
      }
    }

    return user.save();
  })
  .then(user => res.json(user))
  .catch(async err => {
    console.log('**************ERR IN UPDATEEMPLOYEE**************');
    console.log(err);

    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'updateEmployee',
      controller: 'lq.controller',
      stack: err ? err.stack : null,
      error: err,

    })
    .then(()=> {
      return res.status(500).json({
        invalid: 'An error has occurred.'
      });
    });
  });
}

/**
 * Delete an employee
DELETE http://gcmgr-staging.cardquiry.com:9000/api/lq/stores/:storeId/employees/:employeeId
HEADERS
Accept: application/json
Content-Type: application/json
Authorization: bearer <token>
PARAMS
{
  "storeId": "56cca6cf780b493151881a59",
  "employeeId": "590bb39363f76f1aab9cb717"
}
RESULT
200
*/
export function deleteEmployee(req: Request, res: Response) {
  return deleteEmployeeCompanyController(req, res);
}

/**
 * Retrieve all employees of a store
GET http://gcmgr-staging.cardquiry.com:9000/api/lq/stores/:storeId/employees
HEADERS
Accept: application/json
Content-Type: application/json
Authorization: bearer <token>
PARAMS
{
  "storeId": "56cca6cf780b493151881a59"
}
RESULT
[
  {
    "_id": "590bb39363f76f1aab9cb717",
    "store": "56cca6cf780b493151881a59",
    "firstName": "John",
    "lastName": "Public",
    "email": "johnq@public.com",
    "__v": 0,
    "company": "56637dd6295c4d131c901ba1",
    "created": "2017-05-04T23:04:51.694Z",
    "role": "employee",
    "profile": {
      "lastName": "Public",
      "firstName": "John",
      "email": "johnq@public.com",
      "_id": "590bb39363f76f1aab9cb717"
    },
    "token": {
      "role": "employee",
      "_id": "590bb39363f76f1aab9cb717"
    },
    "fullName": "John Public",
    "id": "590bb39363f76f1aab9cb717"
  },
  ...
]
*/
export function getEmployees(req: Request, res: Response) {
  const {storeId} = req.params;
  // Invalid object ID
  if (!mongoose.Types.ObjectId.isValid(storeId)) {
    return res.status(invalidObjectId.code).json(invalidObjectId.res);
  }

  Store.findOne({_id: storeId, companyId: req.user.company})
  .populate('users')
  .then(store => {
    if (!store) {
      return res.status(404).json();
    }

    return res.json(store.users);
  })
  .catch(async err => {
    console.log('****************************ERR IN GETEMPLOYEES****************************');
    console.log(err);

    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'getEmployees',
      controller: 'lq.controller',
      stack: err ? err.stack : null,
      error: err,

    })
    .then(()=> {
      return res.status(500).json({
        invalid: 'An error has occurred.'
      });
    });
  });
}

/**
 * Retrieve an employee
GET http://gcmgr-staging.cardquiry.com:9000/api/lq/stores/:storeId/employees/:employeeId
HEADERS
Accept: application/json
Content-Type: application/json
Authorization: bearer <token>
PARAMS
{
  "storeId": "56cca6cf780b493151881a59",
  "employeeId": "590bb39363f76f1aab9cb717"
}
RESULT
{
  "_id": "590bb39363f76f1aab9cb717",
  "store": "56cca6cf780b493151881a59",
  "firstName": "John",
  "lastName": "Public",
  "email": "johnq@public.com",
  "__v": 0,
  "company": "56637dd6295c4d131c901ba1",
  "created": "2017-05-04T23:04:51.694Z",
  "role": "employee",
  "profile": {
    "lastName": "Public",
    "firstName": "John",
    "email": "johnq@public.com",
    "_id": "590bb39363f76f1aab9cb717"
  },
  "token": {
    "role": "employee",
    "_id": "590bb39363f76f1aab9cb717"
  },
  "fullName": "John Public",
  "id": "590bb39363f76f1aab9cb717"
}
 */
export function getEmployee(req: Request, res: Response) {
  const {storeId, employeeId} = req.params;

  if (!mongoose.Types.ObjectId.isValid(storeId) ||
      !mongoose.Types.ObjectId.isValid(employeeId)) {
    return res.status(404).json();
  }

  User.findOne({_id: employeeId, store: storeId, company: req.user.company})
  .then(user => {
    if (!user) {
      return res.status(404).json();
    }

    return res.json(user);
  })
  .catch(async err => {
    console.log('*************************ERR IN GETEMPLOYEE*************************');
    console.log(err);

    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'getEmployee',
      controller: 'lq.controller',
      stack: err ? err.stack : null,
      error: err,

    })
    .then(()=> {
      return res.status(500).json({
        invalid: 'An error has occurred.'
      });
    });
  })
}

/**
 * Reset transactions
 */
export function resetTransactions(req: Request, res: Response) {
  Store.find({})
  .then(stores => {
    const promises = [];
    stores.forEach(store => {
      store.reserveTotal = 0;
      store.reserves = [];
      promises.push(store.save());
    });
    return Promise.all(promises);
  })
  .then(() => Company.find({}))
  .then(companies => {
    const promises = [];
    companies.forEach(company => {
      company.reserveTotal = 0;
      company.reserves = [];
      promises.push(company.save());
    });
    return Promise.all(promises);
  })
  .then(() => Inventory.find({})
  .populate('card')
  .then(inventories => {
    const promises = [];
    inventories.forEach(inventory => {
      if (inventory.transaction) {
        if (inventory.card) {
          promises.push(inventory.card.remove());
        }
        promises.push(inventory.remove());
      }
    });
    return Promise.all(promises);
  }))
  // Remove reserve records
  .then(async () => await Reserve.remove({}))
  .then(() => res.json({}));
}

async function setVerifiedBalance(inventory, verifiedBalance) {
  inventory.verifiedBalance = verifiedBalance;
  inventory.isTransaction = true;
  return inventory.save();
}

// /**
//  * Mock a credit/reject for staging
//  */
// export function mockCreditReject(req: Request, res: Response) {
//   const {verifiedBalance, cards} = req.body;
//   return Card.find({_id: {$in: cards}}).populate('inventory')
//   .then(async cards => {
//     const dbInventories = cards.map(card => card.inventory);
//     for (let inventory of dbInventories) {
//       await setVerifiedBalance(inventory, verifiedBalance);
//     }
//     const [fakeReq, fakeRes] = makeFakeReqRes(req);
//     fakeReq.body.inventories = dbInventories.map(inv => inv._id.toString());
//     await rejectCards(fakeReq, fakeRes);
//     return res.json({});
//   })
//   .catch(async err => {
//     if (err && err.message === 'notFound') {
//       return;
//     }
//     console.log('**************ERR IN MOCK CREDIT REJECT**********');
//     console.log(err);
//
//     await ErrorLog.create({
//       body: req.body ? req.body : {},
//       params: req.params ? req.params : {},
//       method: 'mockCreditReject',
//       controller: 'lq.controller',
//       stack: err ? err.stack : null,
//       error: err,
//
//     });
//
//     return res.status(500).json({
//       invalid: 'An error has occurred.'
//     });
//   })
//   // {inventories: ["5943fa2c9d19ae2e9499c45c"], verifiedBalance: 100}
// }
