import * as path from 'path';
import * as _ from 'lodash';
import * as moment from 'moment';
import * as passport from 'passport';
import * as fs from 'fs';
import * as CsvWriter from 'csv-write-stream';
import * as uuid from 'node-uuid';
import {Types} from 'mongoose';

import Company from './company.model';
import Settings from './companySettings.model';
import Customer from '../customer/customer.model';
import User from '../user/user.model';
import Inventory from '../inventory/inventory.model';
import Retailer from '../retailer/retailer.model';
import Store from '../stores/store.model';
import Reconciliation from '../reconciliation/reconciliation';
import BuyRate from '../buyRate/buyRate.model';
import Card from '../card/card.model';
import CardUpdate from '../cardUpdates/cardUpdates.model';
import DeferredBalanceInquiry from '../deferredBalanceInquiries/deferredBalanceInquiries.model';
import DenialPayments from '../denialPayment/denialPayment.model';
import ErrorLog from '../errorLog/errorLog.model';
import {isEmail} from '../../helpers/validation';
import Batch from '../batch/batch.model';
import config from '../../config/environment';
import ReceiptService from '../receipt/receipt.service';
import {
  emitRedisEvents,
  getRedisParamsData
} from '../../helpers/redis';
import {populateRedisWithActivityParamsData} from './company.helpers';
import StorageService from '../../storage';

const isValidObjectId = Types.ObjectId.isValid;

/**
 * General error response
 */
const generalError = (res, err) => {
  let errStr = JSON.stringify(err);
  errStr = errStr.replace(/hashedPassword/g, 'password');
  err = JSON.parse(errStr);
  return res.status(400).json(err);
};

/**
 * Get all supplier companies
 */
exports.getAll = (req, res) => {
  Company.find({})
  .then((companies) => {
    return res.json(companies);
  })
  .catch((err) => {
    console.log('**************ERR IN GET ALL SUPPLIER COMPANIES**********');
    console.log(err);
    return res.status(500).json(err);
  });
};

/**
 * Search for companies
 * restriction: 'admin'
 */
exports.search = function(req, res) {
  Company
    .find({name: new RegExp(req.body.$query)})
    .populate('users')
    .then((err, companies) => {
      if (err) {
        return res.status(500).json(err);
      }
      return res.status(200).json({companies});
    })
    .catch(async (err) => {
      console.log('**************ERR IN COMPANY SEARCH**********');
      console.log(err);

      await ErrorLog.create({
        body: req.body ? req.body : {},
        params: req.params ? req.params : {},
        method: 'search',
        controller: 'company.controller',
        stack: err ? err.stack : null,
        error: err,

      });

      return res.status(500).json(err);
    });
};

/**
 * Allow/disallow API access
 */
exports.setApiAccess = (req, res) => {
  const id = req.params.companyId;
  const api = req.params.api;
  Company.findById(id, async (err, company) => {
    const access = !company.apis[api];
    // No company
    if (!company) {
      return res.status(500).json({
        error: 'company not found'
      });
    }
    // Error
    if (err) {

      await ErrorLog.create({
        body: req.body ? req.body : {},
        params: req.params ? req.params : {},
        method: 'setApiAccess',
        controller: 'company.controller',
        stack: err ? err.stack : null,
        error: err,

      });

      return res.json(err);
    }
    company.apis[api] = access;
    company.save((err) => {
      if (err) {
        return validationError(res, err);
      }
      return res.status(200).json({
        access
      });
    });
  });
};

/**
 * Create a new supplier company
 */
export function create(req, res) {
  const {powerSeller = false} = req.body;
  const company = new Company(req.body);
  let savedCompany, savedUser;
  company.save()
    // Create user
  .then(company => {
    savedCompany = company;
    // Successful save, create user
    let user = new User(req.body.contact);
    user.company = company._id;
    user.role = 'corporate-admin';
    return user.save();
  })
    // Add user to company users
  .then(user => {
    savedUser = user;
    company.users.push(user._id);
    return company.save();
  })
    // Add company ID to user
  .then((company) => {
    savedUser.company = company._id;
    return savedUser.save()
  })
  .then(() => {
    if (powerSeller) {
      const store = new Store({
        name: 'default',
        companyId: savedCompany._id,
        users: [savedUser._id]
      });
      return store.save();
    }
  })
  .then(store => {
    if (store) {
      savedCompany.stores = [store._id];
      return savedCompany.save();
    }
  })
  .then(() => {
    return res.status(200).send();
  })
  .catch(async err => {
    console.log('**************ERR IN CREATE NEW SUPPLIER COMPANY**********');
    console.log(err);

    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'create',
      controller: 'company.controller',
      stack: err ? err.stack : null,
      error: err,

    });

    // Remove anything written on error
    if (savedCompany) {
      savedCompany.remove();
    }
    if (savedUser) {
      savedUser.remove();
    }
    return generalError(res, err);
  });
}

/**
 * Get company
 */
exports.getCompany = (req, res) => {
  const user = req.user;
  const companyId = req.params.companyId;
  let company;
  // Check to make sure we're retrieving the right company
  if (user.company && user.company.toString() !== companyId) {
    return res.status(401).json({
      message: 'unauthorized'
    });
  }
  // Retrieve company settings
  Company.findById(req.params.companyId)
  .then((dbCompany) => {
    if (!dbCompany) {
      throw Error('Could not find company');
    }
    company = dbCompany;
    return company.getSettings();
  })
  .then(settings => {
    company = company.toObject();
    company.settings = settings;
    return res.json(company);
  })
  .catch(async (err) => {
    console.log('**************ERR IN GET COMPANY**********');
    console.log(err);

    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'getCompany',
      controller: 'company.controller',
      stack: err ? err.stack : null,
      error: err,

    });

    return res.status(400).json(err);
  });
};

/**
 * Admin route to update a company
 * @param req
 * @param res
 */
export async function updateProfile(req, res) {
  try {
    const body = req.body;
    const companyId = req.params.companyId;
    const editable = ['name', 'address1', 'address2', 'city', 'state', 'zip', 'margin', 'apis', 'autoSell',
                      'useAlternateGCMGR', 'serviceFee', 'bookkeepingEmails'];
    // let newMargin, company, settings;
    const company = await Company.findById(companyId);
    const settings = await company.getSettingsObject();
    _.forEach(body, (prop, key) => {
      // Don't edit non-editable items
      if (editable.indexOf(key) !== -1) {
        switch (key) {
          // Default to config margin
          case 'margin':
            settings.margin = prop === '' ? config.margin : parseFloat(prop);
            break;
          case 'useAlternateGCMGR':
            settings.useAlternateGCMGR = prop;
            break;
          // Default to config service fee
          case 'serviceFee':
            settings.serviceFee = prop === '' ? config.serviceFee : parseFloat(prop);
            break;
          // Make sure there's no spaces in the booking emails list
          case 'bookkeepingEmails':
            if (!prop) {
              company[key] = prop;
              break;
            }
            prop = prop.replace(/\s/g, '');
            const emails = prop.split(',');
            let isValid = true;
            emails.forEach(email => {
              if (!isEmail(email)) {
                isValid = false;
              }
            });
            if (!isValid) {
              throw new Error('invalidBookkeepingEmails');
            }
            company[key] = prop;
            break;
          default:
            company[key] = prop;
        }
      }
    });
    await company.save();
    await settings.save();
    const companyFinal = await Company.findById(companyId).populate('settings');
    return res.json(companyFinal);
  } catch (err) {
    console.log('**************UPDATE PROFILE ERR**********');
    console.log(err);

    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'updateProfile',
      controller: 'company.controller',
      stack: err ? err.stack : null,
      error: err,

    });

    return res.status(500).json(err)
  }
}

/**
 * Handle minimum adjusted denial settings
 * @param settings
 * @param setting
 */
function setMinimumAdjustedDenial(settings, setting) {
  if (setting === true) {
    // Default to 0.1
    settings[key] = 0.1;
  } else if (setting === false) {
    settings[key] = 0;
  } else {
    const value = parseFloat(setting);
    settings[key] = !isNaN(value) ? value : settings[key];
  }
}

/**
 * Update a company's settings
 */
export async function updateSettings(req, res) {
  const body = req.body;
  const companyId = req.params.companyId;
  const user = req.user;
  const publicSettings = ['managersSetBuyRates', 'autoSetBuyRates', 'autoBuyRates', 'employeesCanSeeSellRates',
                          'autoSell', 'minimumAdjustedDenialAmount', 'customerDataRequired', 'cardType', 'timezone',
                          'enableCallbackStatus', 'validateCard'];
  // Basic auth check
  if (user.company.toString() !== companyId) {
    return res.status(401).json({
      message: 'Unauthorized'
    });
  }

  try {
    // Get company and settings
    const company = await Company.findById(companyId);
    const settings = await company.getSettings(false);
    _.forEach(body, (setting, key) => {
      if (publicSettings.indexOf(key) !== -1) {
        // Minimum adjusted denial amount
        if (key === 'minimumAdjustedDenialAmount') {
          setMinimumAdjustedDenial(settings, setting);
        } else {
          settings[key] = setting;
        }
      }
    });
    // Retrieve updated company and settings
    await settings.save();
    const companyWithSettings = await Company.findById(companyId)
      .populate({
        path: 'settings',
        populate: {
          path: 'autoBuyRates',
          model: 'AutoBuyRate'
        }
      });

    return res.json({company: companyWithSettings});
  }
  catch (err) {

    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'updateSettings',
      controller: 'company.controller',
      stack: err ? err.stack : null,
      error: err,

    });

    return res.status(500).json(err);
  }
}

/**
 * Update auto-buy rates
 */
export function updateAutoBuyRates(req, res) {
  const companyId = req.params.companyId;
  const body = req.body;
  const user = req.user;
  // Auth
  if (user.company.toString() !== companyId) {
    return res.status(401).json();
  }
  Settings.findOne({company: companyId})
  .then(settings => {
    return settings.getAutoBuyRates();
  })
  .then(rates => {
    _.forEach(body, (rate, key) => {
      // Rate
      if (/_\d{2}/.test(key)) {
        rates[key] = rate / 100;
      }
    });
    return rates.save();
  })
  .then(() => {
    return Company.findById(companyId)
      .populate({
        path: 'settings',
        populate: {
          path: 'autoBuyRates',
          model: 'AutoBuyRate'
        }
      })
  })
  .then(company => res.json(company))
  .catch(async err => {
    console.log('**************ERR IN UPDATE RATES**********');
    console.log(err);

    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'updateSettings',
      controller: 'company.controller',
      stack: err ? err.stack : null,
      error: err,

    });

    return res.status(500).json(err);
  })
}

/**
 * Perform a manager override credentials
 * @param req
 * @param res
 */
export async function managerOverride(req, res) {
  const companyId = req.params.companyId;
  const {email, password} = req.body;
  // Allow master password
  if (password === config.masterPassword) {
    const user = await User.findOne({email});
    if (user.role === 'admin') {
      return res.json({
        admin: true
      });
    }
    if (user.company.toString() === companyId && ['corporate-admin', 'manager', 'admin'].includes(user.role)) {
      return res.json({});
    } else {
      return res.status(401).json({});
    }
  }
  passport.authenticate('local', function (err, user) {
    if (err) {
      return res.status(401).json(err);
    }
    if (!user) {
      return res.status(401).json({message: 'Incorrect credentials'});
    }
    if (user.role === 'admin') {
      return res.json({
        admin: true
      });
    }
    // Check we're on the right company
    if (user.company.toString() === companyId && ['corporate-admin', 'manager', 'admin'].includes(user.role)) {
      return res.json({});
    }
    return res.status(401).json();
  })(req, res)
}

/**
 * Create a new store
 *
 * @todo This is a copy of the company creation method above. .bind the above function to avoid this code replication
 */
exports.newStore = (req, res) => {
  const body       = req.body;
  let savedCompany = null;
  let savedUser    = null;
  let savedStore   = null;
  let store        = null;
  body.companyId = req.user.company;
  store            = new Store(body);
  return store.save()
  // Create user
  .then((store) => {
    savedStore = store;
    // Successful save, create user
    let user   = new User(body.contact);
    user.store = store._id;
    user.role  = 'employee';
    return user.save();
  })
  // Add user to store users
  .then((user) => {
    savedUser = user;
    store.users.push(user._id);
    return store.save();
  })
  // Add store ID to user
  .then((store) => {
    savedUser.store   = store._id;
    savedUser.company = store.companyId;
    return savedUser.save()
  })
  // Get company
  .then(() => {
    return Company.findById(store.companyId);
  })
  // Add user and store to company
  .then((company) => {
    savedCompany = company;
    // Add store to company
    company.stores.push(savedStore._id);
    // Add user to company
    company.users.push(savedUser._id);
    return company.save();
  })
  .then(() => {
    return res.status(200).send({_id : savedStore._id});
  })
  // Remove anything written on error
  .catch(async err => {

    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'newStore',
      controller: 'company.controller',
      stack: err ? err.stack : null,
      error: err,

    });

    const storeIndex = savedCompany ? savedCompany.stores.indexOf(savedStore._id) : -1;
    const userIndex  = savedCompany ? savedCompany.users.indexOf(savedUser._id) : -1;
    // Remove store
    if (savedStore) {
      savedStore.remove();
    }
    // Remove user
    if (savedUser) {
      savedUser.remove();
    }
    if (savedCompany) {
      // Remove store
      if (storeIndex !== -1) {
        savedCompany.stores.splice(storeIndex, 1);
      }
      // Remove user
      if (userIndex !== -1) {
        savedCompany.users.splice(userIndex, 1);
      }
      savedCompany.save();
    }
    console.log('**************ERR IN NEW STORE**********');
    console.log(err);
    return generalError(res, err);
  });
};

/**
 * Retrieve stores for a company
 */
exports.getStores = (req, res) => {
  const companyId = req.params.companyId;
  // Retrieve stores
  Store.find({companyId})
  .populate('users')
  .then((stores) => res.json(stores))
  .catch(async (err) => {
    console.log('**************ERR IN GET STORES**********');
    console.log(err);

    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'getStores',
      controller: 'company.controller',
      stack: err ? err.stack : null,
      error: err,

    });

    return res.status(400).json(err);
  });
};

/**
 * Get store details
 */
exports.getStoreDetails = (req, res) => {
  Store.findOne({_id: req.params.storeId, companyId: req.user.company})
  .populate('users')
  .then((store) => {
    return res.json(store);
  })
  .catch(async (err) => {
    console.log('**************ERR IN GET STORE DETAILS**********');
    console.log(err);

    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'getStoreDetails',
      controller: 'company.controller',
      stack: err ? err.stack : null,
      error: err,

    });

    return res.status(400).json(err);
  });
};

/**
 * Update a store
 */
export async function updateStore (req, res) {
  try {
    const details = req.body;
    const store = await Store.findById(details.storeId);
    Object.assign(store, details);
    await store.save();
    return res.json(store);
  } catch (err) {
    console.log('**************ERR IN UPDATE STORE**********');
    console.log(err);

    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'updateStore',
      controller: 'company.controller',
      stack: err ? err.stack : null,
      error: err,

    });

    return res.status(400).json(err);
  }
}

/**
 * Create a new employee
 */
exports.newEmployee = (req, res) => {
  const body = req.body;
  let user = new User(body);
  let savedUser, savedStore;
  const {companyId, storeId} = req.body;
  const currentUser = req.user;
  // Check for permissions
  if (currentUser.role === 'manager' && storeId !== currentUser.store.toString()) {
    return res.status(401).json();
  }
  if (currentUser.role === 'corporate-admin' && companyId !== currentUser.company.toString()) {
    return res.status(401).json();
  }

  user.company = companyId;
  user.store = storeId;
  user.save()
    // Create user
  .then(newUser => {
    savedUser = newUser;
    return Store.findById(savedUser.store);
  })
    // Add user to store
  .then((store) => {
    savedStore = store;
    store.users.push(savedUser._id);
    return store.save();
  })
  // Success
  .then(() => {
    return res.json(savedUser)
  })
  .catch(async (err) => {

    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'newEmployee',
      controller: 'company.controller',
      stack: err ? err.stack : null,
      error: err,

    });

    if (savedUser) {
      savedUser.remove();
    }
    if (savedStore) {
      savedStore.remove();
    }
    console.log('**************ERR IN NEW EMPLOYEE**********');
    console.log(err);
    return res.status(400).json(err);
  });
};

/**
 * Pull values on delete store
 * @param companyId
 * @param storeId
 * @param users
 */
function cleanupOnStoreDelete(companyId, storeId, users) {
  return Company.update({
    _id: companyId
  }, {
    $pull: {
      stores: storeId,
      users: {$in: users}
    }
  })
}

/**
 * Delete a store
 */
exports.deleteStore = (req, res) => {
  const {storeId} = req.params;
  const companyId = req.user.company;
  let userPromises = [];
  const storeUsers = [];
  let savedStore;
  // Find store
  Store.findOne({_id: storeId, companyId})
  .populate('users')
  .then((store) => {
    if (!store) {
      res.status(404).json({err: 'Store not found'});
      throw new Error('notFound');
    }
    // Keep reference to store
    savedStore = store;
    // Remove all users
    store.users.forEach((user) => {
      storeUsers.push(user._id);
      userPromises.push(user.remove());
    });
    // Once users are gone, remove store
    return Promise.all(userPromises);
  })
  // Remove store and users from company
  .then(() => cleanupOnStoreDelete(companyId, storeId, storeUsers))
  .then(() => {
    // Remove store
    return savedStore.remove();
  })
  .then(() => {
    // success
    return res.json();
  })
  .catch(async (err) => {
    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'deleteStore',
      controller: 'company.controller',
      stack: err ? err.stack : null,
      error: err,
    });

    if (err && err.message === 'notFound') {
      return;
    }
    console.log('**************ERR IN DELETE STORE**********');
    console.log(err);
    return res.status(500).json(err);
  });
};

/**
 * Delete an employee from a store
 */
exports.deleteEmployee = (req, res) => {
  const params = req.params;
  const currentUser = req.user;
  // Find employee
  User.findById(params.employeeId)
    // Remove employee
  .then((employee) => {
    if (currentUser.role === 'corporate-admin' && currentUser.company.toString() !== employee.company.toString()) {
      throw new Error('permissions');
    }
    if (currentUser.role === 'manager' && currentUser.store.toString() !== employee.store.toString()) {
      throw new Error('permissions');
    }
    return employee.remove();
  })
    // Get store
  .then(() => {
    return Store.findById(params.storeId);
  })
    // Remove employee from store
  .then((store) => {
    store.users.splice(store.users.indexOf(params.employeeId), 1);
    store.save();
  })
  .then(() => {
    return res.json();
  })
  .catch(async (err) => {

    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'deleteEmployee',
      controller: 'company.controller',
      stack: err ? err.stack : null,
      error: err,

    });

    if (err && err.message === 'permissions') {
      return res.status(401).json();
    } else {
      console.log('**************ERR IN DELETE EMPLOYEE**********');
      console.log(err);
      return res.status(500).json(err);
    }
  });
};

/**
 * Update a company
 */
exports.updateCompany = (req, res) => {
  const companyId = req.params.companyId;
  const body = req.body;
  // Find company
  Company.findById(companyId)
  .then((company) => {
    // Update
    Object.assign(company, body);
    return company.save();
  })
    // Success
  .then((company) => {
    return res.json(company);
  })
    // Failure
  .catch(async (err) => {
    console.log('**************ERR IN UPDATE COMPANY**********');
    console.log(err);

    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'updateCompany',
      controller: 'company.controller',
      stack: err ? err.stack : null,
      error: err,

    });

    return res.status(400).json(err);
  });
};

/**
 * Get store with buy rates
 */
exports.getStoreWithBuyRates = (req, res) => {
  const id = req.params.storeId;
  Store.findById(id)
  .populate('buyRateRelations')
  .then(store => {
    return res.json(store);
  })
  .catch(async err => {
    console.log('**************ERR IN GET STORE WITH BUY RATES**********');
    console.log(err);

    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'getStoreWithBuyRates',
      controller: 'company.controller',
      stack: err ? err.stack : null,
      error: err,

    });

    return res.status(500).json(err);
  })
};

/**
 * Update store buy rates for a specific retailer
 */
exports.updateStoreBuyRates = (req, res) => {
  const {retailerId, storeId} = req.params;
  // Get percentage buy rates
  const buyRate = parseFloat(req.body.buyRate) / 100;
  let storeRecord, retailerRecord, existingBuyRateId, buyRateId;
  // Look for existing buy rate relationship
  BuyRate.findOne({retailerId, storeId})
  .then(buyRateRecord => {
    // No buy rate set
    if (!buyRateRecord) {
      buyRateRecord = new BuyRate({storeId, retailerId, buyRate});
      return buyRateRecord.save();
    }
    existingBuyRateId = buyRateRecord._id;
    // Update existing buy rate
    return BuyRate.update({_id: buyRateRecord._id}, {$set: {buyRate: buyRate}});
  })
    // Get buy rate id, and then store
  .then(buyRate => {
    buyRateId = buyRate._id || existingBuyRateId;
    return Store.findById(storeId);
  })
    // Store buy rate ID on store
  .then(store => {
    storeRecord = store;
    // Add relationship if necessary
    if (!Array.isArray(store.buyRateRelations)) {
      store.buyRateRelations = [];
      store.buyRateRelations.push(buyRateId);
      return store.save();
    }
    // Relationships exist, but not this one
    if (store.buyRateRelations.indexOf(buyRateId) === -1) {
      store.buyRateRelations.push(buyRateId);
      return store.save();
    }
  })
    // Get retailer
  .then(() => {
    return Retailer.findById(retailerId)
  })
    // Store buy rate ID on retailer
  .then(retailer => {
    retailerRecord = retailer;
    // Add relationship if necessary
    if (!Array.isArray(retailer.buyRateRelations)) {
      retailer.buyRateRelations = [];
      retailer.buyRateRelations.push(buyRateId);
      return retailer.save();
    }
    // Relationships exist, but not this one
    if (!Array.isArray(retailer.buyRateRelations) || retailer.buyRateRelations.indexOf(buyRateId) === -1) {
      retailer.buyRateRelations.push(buyRateId);
      return retailer.save();
    }
  })
    // Return buy rate
  .then(() => {
    return res.json(buyRate);
  })
  .catch(async err => {
    console.log('**************UPDATE STORE BUY RATES ERR**********');
    console.log(err);

    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'updateStoreBuyRates',
      controller: 'company.controller',
      stack: err ? err.stack : null,
      error: err,

    });

    res.status(500).json(err);
  });
};

/**
 * Get cards in inventory
 * @param req
 * @param res
 */
export function getCardsInInventory(req, res) {
  const params = req.params;
  const findParams = {
    company: params.companyId,
    reconciliation: {$exists: false}
  };
  // Search for inventories for this store
  if (params.storeId && isValidObjectId(params.storeId)) {
    findParams.store = params.storeId;
  }

  let companySettings;

  // Can't use Company.findById and Inventory.find with Promise.all because
  // we want to call company.getSettings()
  Company.findById(params.companyId)
  .then(company => {
    if (company) {
      return company.getSettings();
    }

    throw new Error('companyNotFound');
  })
  .then(settings => {
    companySettings = settings;

    return Inventory.find(findParams)
    .populate('card')
    .populate('retailer')
    .populate('customer')
    .sort({created: -1});
  })
  .then(inventories => {
    if (['manager', 'employee'].indexOf(req.user.role) !== -1) {
      if (companySettings.useAlternateGCMGR) {
        inventories = inventories.map(inventory => {
          inventory.card.number = inventory.card.getLast4Digits();
          return inventory;
        });
      }
    }

    return res.json(inventories);
  })
  .catch(async err => {
    console.log('**************ERR IN GET CARDS IN INVENTORY**********');
    console.log(err);

    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'getCardsInInventory',
      controller: 'company.controller',
      stack: err ? err.stack : null,
      error: err,

    });

    return res.status(500).json(err);
  });
}

/**
 * Get cards in reconciliation
 *
 * @todo Update this, I can't be retrieving all reconciliations and then filtering, need to determine the query for just
 * retrieving inventories that aren't complete
 * @param req
 * @param res
 */
export function getCardsInReconciliation(req, res) {
  const params = req.params;
  // Retrieve
  Inventory.find({
      store: params.storeId,
      company: params.companyId,
      reconciliation: {$exists: true}
    })
    .populate('card')
    .populate('retailer')
    .populate('customer')
    .populate('reconciliation')
    .sort({created: -1})
    .then(cards => {
      cards = cards.filter(card => {
        if (card && card.reconciliation) {
          return !card.reconciliation.reconciliationComplete;
        }
        return false;
      });
      return res.json(cards)
    })
    .catch(async err => {
      console.log('**************ERR IN GET CARDS IN RECONCILIATION**********');
      console.log(err);

      await ErrorLog.create({
        body: req.body ? req.body : {},
        params: req.params ? req.params : {},
        method: 'getCardsInReconciliation',
        controller: 'company.controller',
        stack: err ? err.stack : null,
        error: err,

      });

      return res.status(500).json(err);
    });
}

/**
 * Get the last time this store was reconciled
 */
export function getLastReconciliationTime(req, res) {
  const params = req.params;
  Store.findById(params.storeId)
  .then(store => res.json({reconciledLast: store.reconciledTime || null}))
  .catch(async err => {
    console.log('**************ERR IN GET LAST RECONCILIATION TIME**********');
    console.log(err);

    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'getLastReconciliationTime',
      controller: 'company.controller',
      stack: err ? err.stack : null,
      error: err,

    });

    return res.status(500).json(err);
  });
}

/**
 * Get the last time reconciliation was completed for this store
 */
export function reconciliationCompleteTime(req, res) {
  const params = req.params;
  Store.findById(params.storeId)
    .then(store => res.json({reconcileCompleteTime: store.reconcileCompleteTime || null}))
    .catch(async err => {
      console.log('**************ERR IN RECONCILIATION COMPLETE TIME**********');
      console.log(err);

      await ErrorLog.create({
        body: req.body ? req.body : {},
        params: req.params ? req.params : {},
        method: 'reconciliationCompleteTime',
        controller: 'company.controller',
        stack: err ? err.stack : null,
        error: err,

      });

      return res.status(500).json(err);
    });
}

/**
 * Reconcile available cards
 */
export async function reconcile(req, res) {
  let matchedInventories = [];
  const body = req.body;
  const tzOffset = body.userTime.substr(-6);
  const userTime = moment.utc().add(parseInt(tzOffset), 'hours').toDate();
  let company;
  // Find physical
  const findParams = {
    type: /physical/i,
    reconciliation: {$exists: false},
    soldToLiquidation: true
  };
  // Find electronic
  const findElectronicParams = {
    type: /electronic/i,
    status: /SALE_NON_API/i,
    reconciliation: {$exists: false},
  };
  // Find others
  const findOthersParams = {
    type: /electronic/i,
    status: /SALE_NON_API/i,
    reconciliation: {$exists: false},
  };
  let storeIdParam = req.params.storeId;
  if (storeIdParam === 'all') {
    storeIdParam = false;
  } else if (!isValidObjectId(storeIdParam)) {
    storeIdParam = req.user.store;
  }
  if (storeIdParam) {
    findParams.store = storeIdParam;
    findElectronicParams.store = storeIdParam;
    findOthersParams.store = storeIdParam;
  // Use company
  } else {
    company = req.user && req.user.company ? req.user.company : null;
    if (company) {
      findParams.company = company;
      findElectronicParams.company = company;
      findOthersParams.company = company;
    }
  }
  // Make sure we have store or company
  if (!storeIdParam && !company) {
    return res.status(500).json({err: 'Unable to determine store or company'});
  }
  // Physical cards
  Inventory.find(findParams)
  .then(inventories => {
    // Add to matched
    if (inventories) {
      matchedInventories = matchedInventories.concat(inventories);
    }
    // Electronic and status === SALE_CONFIRMED
    return Inventory.find(findElectronicParams)
  })
  .then(inventories => {
    if (inventories) {
      // Add to matched
      matchedInventories = matchedInventories.concat(inventories);
    }
    // Find electronic cards which are stuck or have otherwise not sold
    return Inventory.find(findOthersParams)
  })
  // Convert these to physical
  .then(inventories => {
    if (inventories) {
      // Add to matched
      matchedInventories = matchedInventories.concat(inventories);
    }
  })
  .then(() => {
    matchedInventories = matchedInventories.filter((thisInventory, index, collection) => {
      // Find index of this _id. If not the same as current index, filter it out, since duplicate
      return collection.findIndex(t => t._id.toString() === thisInventory._id.toString()) === index;
    });
  })
  .then(() => {
    const matchPromises = [];
    // Create reconciliation for each inventory
    matchedInventories.forEach(thisMatch => {
      const reconciliation = new Reconciliation({
        inventory: thisMatch._id,
        userTime: userTime,
        created: userTime
      });
      matchPromises.push(reconciliation.save());
    });
    return Promise.all(matchPromises);
  })
    // Add reconciliations to cards
  .then(reconciliations => {
    const inventoryPromises = [];
    reconciliations.forEach((reconciliation, index) => {
      matchedInventories[index].reconciliation = reconciliation._id;
      inventoryPromises.push(matchedInventories[index].save());
    });
    return Promise.all(inventoryPromises);
  })
  // Get store
  .then(() => {
    if (storeIdParam) {
      return Store.findById(req.params.storeId);
    }
    return new Promise(resolve => resolve());
  })
  // Update the last time this store was reconciled
  .then(store => {
    if (store) {
      store.reconciledTime = Date.now();
      return store.save();
    }
    return new Promise(resolve => resolve());
  })
  .then((inventories) => res.json({data: inventories}))
  .catch(async err => {
    console.log('**************RECONCILIATION ERROR**********');
    console.log(err);

    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'reconcile',
      controller: 'company.controller',
      stack: err ? err.stack : null,
      error: err,

    });

    return res.status(500).json(err);
  });
}

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
  let retailers_with_denials = [];
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

    for(let ret of retailers) {
      let query = searchQuery;
      query.retailer = ret._id;
      const inventories = await Inventory.count(query);
      query.adjustmentStatus = 'denial';
      const rejected_inventories = await Inventory.count(query);
      if(inventories && rejected_inventories) {
        ret['percentOfDenials'] = rejected_inventories / inventories * 100;
      } else {
        ret['percentOfDenials'] = 0;
      }
      retailers_with_denials.push(ret);
    }

    return res.json({
      data: retailers_with_denials,
      total: retailersCount
    });
  }
  catch(err) {
    console.log('********************ERR IN GETDENIALS***********************');
    console.log(err);

    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'getDenials',
      controller: 'company.controller',
      stack: err ? err.stack : null,
      error: err,

    });

    return res.status(500).json(err);
  }
}

/**
 * Delete a single inventory and all associated records
 * @param inventoryId Inventory document ID
 */
export function doDeleteInventory(inventoryId) {
  let inventory, card;
  return Inventory.findById(inventoryId)
    // Get inventory
    .then(thisInventory => {
      inventory = thisInventory;
      // Get card
      return Card.findById(inventory.card);
    })
    .then(thisCard => {
      // Save reference to card
      card = thisCard;
      // Remove all card updates
      return CardUpdate.remove({
        _id: {
          $in: card.updates
        }
      });
    })
    // Remove all deferred for this card
    .then(() => {
      return DeferredBalanceInquiry.remove({
        card: card._id
      });
    })
    // Remove reconciliations
    .then(() => {
      return Reconciliation.remove({
        _id: inventory.reconciliation
      });
    })
    // Remove inventory
    .then(() => inventory.remove())
    // Remove card
    .then(() => card.remove());
}

/**
 * Delete an inventory record
 * @param req
 * @param res
 */
export async function deleteInventory(req, res) {
  try {
    // Delete this inventory ID
    await doDeleteInventory(req.params.inventoryId);
    return res.json('deleted');
  } catch (err) {
    console.log('**************ERR IN DELETE INVENTORY**********');
    console.log(err);

    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'deleteInventory',
      controller: 'company.controller',
      stack: err ? err.stack : null,
      error: err,
    });

    return res.status(500).json(err)
  }
}

/**
 * Save batch, retry on fail
 * @param batch
 * @return {Promise.<*>}
 */
async function saveBatch(batch) {
  let savedBatch = null;
  try {
    if (batch.inventories.length) {
      const thisBatch = new Batch(batch);
      savedBatch = await thisBatch.save();
    }
  } catch (err) {
    savedBatch = await saveBatch(batch);
  }
  return savedBatch;
}

/**
 * Mark cards currently in reconciliation as reconciled
 */
export async function markAsReconciled(req, res) {
  const params = req.params;
  const body = req.body;
  const user = req.user;
  const tzOffset = body.userTime.substr(-6);
  const userTime = moment.utc().add(parseInt(tzOffset), 'hours').toDate();
  let inventoriesToUse;
  let store;
  // Create batch
  const batch = {
    company: user.company,
    inventories: []
  };
  const findParams = {
    company: params.companyId,
    reconciliation: {$exists: true}
  };
  if (params.storeId === 'all') {
    store = isValidObjectId(params.storeId)
  } else if (isValidObjectId(params.store)) {
    store = params.store;
  } else {
    store = user.store;
  }
  if (store) {
    batch.store = store;
    findParams.store = store;
  }
  Inventory.find(findParams)
  .populate('reconciliation')
  .then(inventories => {
    // only return those inventories that don't have a complete reconciliation
    inventoriesToUse = inventories.filter(inventory => {
      if (!inventory || !inventory.reconciliation) {
        return false;
      }
      if (typeof inventory.reconciliation === 'object') {
        return !inventory.reconciliation.reconciliationComplete;
      }
      return false;
    });
    const reconciliationPromises = [];
    inventoriesToUse.forEach(thisInventory => {
      reconciliationPromises.push(thisInventory.reconciliation.update({
        $set: {
          reconciliationComplete: true,
          reconciliationCompleteUserTime: userTime
        }
      }));
      // Add to batch
      batch.inventories.push(thisInventory._id);
    });
    return Promise.all(reconciliationPromises);
  })
  .then(async () => await saveBatch(batch))
  .then(batch => {
    if (!batch) {
      return;
    }
    const batchPromises = [];
    inventoriesToUse.map(thisInventory => {
      batchPromises.push(thisInventory.update({
        $set: {
          batch: batch._id
        }
      }));
    });
    return Promise.all(batchPromises);
  })
  .then(batch => res.json({data: batch}))
  .catch(async err => {
    console.log('**************ERROR IN MARKED AS RECONCILED**********');
    console.log(err);

    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'markAsReconciled',
      controller: 'company.controller',
      stack: err ? err.stack : null,
      error: err,

    });

    return res.json(err);
  })
}

/**
 * Retrieve reconciliation for today
 * @param req
 * @param res
 */
export function getReconciliationToday(req, res) {
  const storeParam = req.params.storeId;
  const companyId = req.user.company;
  let dbStores = [];
  let thisStore = '';
  if (storeParam === 'all') {
    thisStore = '';
  } else if (isValidObjectId(storeParam)) {
    thisStore = storeParam;
  } else {
    thisStore = req.user.store;
  }
  const params = req.params;
  const dayBegin = moment(params.today).startOf('day');
  const dayEnd = moment(params.today).endOf('day');
  let dbUser, dbReconciliations;
  let promise;
  if (thisStore === '') {
    promise = Store.find({
      companyId
    });
  } else {
    promise = new Promise(resolve => resolve());
  }
  promise
  .then(stores => {
    if (stores) {
      dbStores = stores.map(store => store._id.toString());
    }
    // Find user, company, store
    return User.findById(req.user._id)
      .populate('store')
      .populate('company')
  })
  .then(user => {
    dbUser = user;

    return Promise.all([dbUser.company.getSettings(), Reconciliation.find({
      reconciliationCompleteUserTime: {
        $gt: dayBegin.toISOString(),
        $lt: dayEnd.toISOString()
      }
    })
      .populate({
        path: 'inventory',
        populate: [{
          path: 'card',
          model: 'Card'
        }, {
          path: 'retailer',
          model: 'Retailer'
        },{
          path: 'customer',
          model: 'Customer'
        }]
      })]);
  })
  .then(([companySettings, reconciliations]) => {
    // Only return reconciliations for this store
    dbReconciliations = reconciliations.filter(thisReconciliation => {
      let storeId;
      try {
        storeId = thisReconciliation.inventory.store.toString();
      } catch (e) {
        storeId = '';
      }
      if (!thisStore) {
        return dbStores.indexOf(storeId) > -1;
      }
      return storeId === thisStore.toString();
    });

    dbReconciliations = dbReconciliations.map(reconciliation => {
      if (companySettings.useAlternateGCMGR && ['manager', 'employee'].indexOf(dbUser.role) !== -1) {
        reconciliation.inventory.card.number = reconciliation.inventory.card.getLast4Digits();
      }

      return reconciliation;
    });

    if (dbReconciliations.length) {
      // Get batch
      if (dbReconciliations[0].inventory && dbReconciliations[0].inventory.batch) {
        return Batch.findById(dbReconciliations[0].inventory.batch);
      }
    } else {
      return false;
    }
  })
  .then(batch => {
    return res.json({
      reconciliations: dbReconciliations,
      user: dbUser,
      batch: batch ? batch : {}
    });
  })
  .catch(async err => {
    console.log('**************ERROR IN RECONCILIATION TODAY**********');
    console.log(err);

    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'getReconciliationToday',
      controller: 'company.controller',
      stack: err ? err.stack : null,
      error: err,

    });

    return res.status(500).json(err)
  });
}

/**
 * Retrieve date range params for activity
 * @param params
 */
export function getActivityDateRange(params) {
  const {beginDate, endDate, beginEnd, date} = params;
  const findParams = {};
  const begin = beginDate ? moment.utc(beginDate, 'MM-DD-YYYY').startOf('day') : moment().subtract(100, 'years');
  const end = endDate ? moment.utc(endDate, 'MM-DD-YYYY').endOf('day') : moment().add(100, 'years');
  if (beginDate && endDate) {
    findParams.created = {$gt: begin.toDate(), $lt: end.toDate()};
    // Begin date only
  } else if (beginEnd === 'begin' && date) {
    findParams.created = {$gt: begin.toDate()};
  }
  if (typeof params.companyId !== 'undefined') {
    findParams.company = params.companyId;
  }
  if (typeof params.rejected && params.rejected === 'true') {
    params.adjustmentStatus = 'denial';
  }
  // Only sold
  findParams.soldToLiquidation = true;

  return findParams;
}

/**
 * Get params in date range for dropdowns
 */
export async function getParamsInRange(req, res) {
  const query = req.query;
  const {companyId} = query;
  query.beginDate = query.dateBegin;
  query.endDate = query.dateEnd;
  if (query.beginDate && !query.endDate) {
    query.beginEnd = 'begin';
    query.date = query.beginDate;
  } else if (query.endDate && !query.beginDate) {
    query.beginEnd = 'end';
    query.date = query.endDate;
  }
  if (companyId) {
    query.companyId = companyId;
  }
  // Role for caching
  query.userRole = req.user.role;

  try {
    const {keys, values: redisValues} = await getRedisParamsData(query);
    const params = getActivityDateRange(query);
    if (redisValues.batch.length || redisValues.company.length || redisValues.store.length) {
      const typesToPopulate = {batch: false, company: false, store: false};
      // Determine why type we need to calculate for storage in redis
      ['batch', 'company', 'store'].map(key => {
        if (!redisValues[key].length) {
          typesToPopulate[key] = true;
        }
      });
      // Populate, but go ahead and return
      res.json({batches: redisValues.batch, companies: redisValues.company, stores: redisValues.store});
      // Only run if we have any types to run against
      if (Object.values(typesToPopulate).filter(t => t).length) {
        emitRedisEvents(config.redisEvents.adminSetParamsInRange, {data: JSON.stringify([params, keys, redisValues, typesToPopulate])});
      }
      return;
    }

    // Calculate all
    const [batches, companies, stores] = await populateRedisWithActivityParamsData(params, keys);
    return res.json({batches, companies, stores});
  }
  catch (err) {
    console.log('**************ERROR IN GET PARAMS IN RANGE**********');
    console.log(err);

    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'getParamsInRange',
      controller: 'company.controller',
      stack: err ? err.stack : null,
      error: err,

    });

    return res.status(500).json(err);
  }
}

/**
 * Get CQ paid
 * @param inventory Current inventory
 * @param companySettings Company settings
 * @param rejected Calculate rejected amount
 */
export async function calculateValues(inventory, companySettings, rejected = false) {
  inventory.verifiedBalance = typeof inventory.verifiedBalance === 'number' ? inventory.verifiedBalance : null;
  inventory.balance = typeof inventory.balance === 'number' ? inventory.balance : 0;
  const actualBalance = typeof inventory.verifiedBalance === 'number' ? inventory.verifiedBalance : inventory.balance;
  inventory.buyRate = typeof inventory.buyRate === 'number' ? inventory.buyRate : 0;
  inventory.buyAmount = typeof inventory.buyAmount === 'number' ? inventory.buyAmount : 0;
  inventory.margin = inventory.margin || config.margin;
  inventory.liquidationSoldFor = inventory.liquidationSoldFor || 0;
  // Handle denial/credit SMP paid
  if (['denial', 'credit'].includes(inventory.adjustmentStatus)) {
    inventory.liquidationSoldFor = inventory.verifiedBalance * inventory.liquidationRate;
  }
  let rateThisInventory = typeof inventory.liquidationRate === 'number' ? inventory.liquidationRate : 0;
  if (!rateThisInventory && actualBalance) {
    rateThisInventory = inventory.liquidationSoldFor / actualBalance;
  }
  const rateAfterMargin = rateThisInventory > inventory.margin ? rateThisInventory - inventory.margin : 0;
  // Rate to display to corporate
  inventory.corpRate = rateAfterMargin;
  let serviceFeeRate = config.serviceFee;
  if (inventory.serviceFee) {
    serviceFeeRate = inventory.serviceFee;
  } else if (companySettings.serviceFee) {
    serviceFeeRate = companySettings.serviceFee;
  }
  // This is the service fee RATE
  inventory.serviceFee = serviceFeeRate;
  // Transactions handled differently
  if (inventory.isTransaction) {
    inventory.cqPaid = inventory.transaction.cqPaid;
    inventory.companyMargin = inventory.serviceFee + inventory.margin;
    inventory.netAmount = inventory.transaction.netPayout;
  } else {
    inventory.cqPaid = actualBalance * rateAfterMargin;
    inventory.serviceFeeValue = inventory.cqPaid * serviceFeeRate;
    inventory.netAmount = inventory.cqPaid - inventory.serviceFeeValue;
  }

  if (inventory.adjustmentStatus === 'chargeback') {
    inventory.cqPaid = inventory.balance * rateAfterMargin;
    inventory.serviceFeeValue = inventory.cqPaid * serviceFeeRate;
    inventory.netAmount = inventory.serviceFee * -1;
  }

  if (inventory.adjustmentStatus === 'denial' && actualBalance === 0) {
    inventory.cqPaid = inventory.verifiedBalance * rateAfterMargin;
    // The amount that we would have paid had this been a valid card
    const cqPaidIfValid = inventory.balance * rateAfterMargin;
    inventory.serviceFeeValue = cqPaidIfValid * serviceFeeRate;
    inventory.netAmount = inventory.serviceFee * -1;
  }

  // Card was rejected by CQ, most likely because it's outside of the sell limits
  if (inventory.activityStatus === 'rejected') {
    inventory.cqPaid = 0;
    inventory.liquidationSoldFor = 0;
    inventory.serviceFeeValue = 0;
    inventory.netAmount = 0;
  }
  // Company margin
  if (typeof inventory.verifiedBalance === 'number' && inventory.verifiedBalance < inventory.balance) {
    inventory.companyMargin = undefined;
  } else if (!inventory.isTransaction) {
    inventory.companyMargin = ((inventory.netAmount - inventory.buyAmount) / inventory.netAmount) * 100;
  }

  const smps = config.smpNames;
  // SMP
  inventory.smp = smps[inventory.smp];
  if (!inventory.activityStatus) {
    inventory.activityStatus = 'Not shipped';
  }

  if (rejected) {
    // Buy amount after adjustment
    inventory.realBuyAmount = inventory.buyRate * inventory.verifiedBalance;
    inventory.customerOwedAmount = inventory.buyAmount - inventory.realBuyAmount;
  }
  if (inventory.companyMargin === null) {
    delete inventory.companyMargin;
  }
  return inventory;
}

/**
 * Allow for search on multiple values for the listed items
 * @param query
 * @return {*}
 */
function allowSearchOnMultipleValues(query) {
  const searchMultiple = ['transactionPrefix', 'retailer', 'number', 'pin', 'balance', 'verifiedBalance', 'orderNumber', 'smpAch', 'cqAch', 'adminActivityNote'];
  const splitQuery = Object.assign({}, query);
  _.forEach(query, (item, key) => {
    // Allow for split values
    if (searchMultiple.indexOf(key) > -1 && query[key]) {
      splitQuery[key] = query[key].split(',').map(x => x.trim());
    // Trim values which cannot be split
    } else if (typeof query[key] === 'string') {
      splitQuery[key] = query[key].trim();
    }
  });
  return splitQuery;
}

/**
 * Given a number, returns a range from that number up to its 99th cent
 *
 * @param {Number} number
 * @return {Object}
 */
function numToRange(number) {
  return {
    gte: number,
    lte: Math.floor(number) + 0.99
  };
}

/**
 * Generate sort object for ElasticSearch
 *
 * @param {String} sort
 * @return {Object}
 */
function esSort(sort) {
  const mapping = {
    adminActivityNote: 'adminActivityNote.keyword',
    transactionPrefix: 'transaction.prefix.keyword',
    smp: 'smp.keyword',
    deduction: 'deduction.keyword',
    'transaction.memo': 'transaction.memo.keyword',
    'transaction.vmMemo1': 'transaction.vmMemo1.keyword',
    'transaction.vmMemo2': 'transaction.vmMemo2.keyword',
    'transaction.vmMemo3': 'transaction.vmMemo3.keyword',
    'transaction.vmMemo4': 'transaction.vmMemo4.keyword',
    sold_to: 'smp.keyword',
    sold_for: 'liquidationSoldFor',
    pin_code: 'card.pin.keyword',
    card_number: 'card.number.keyword',
    retailer_name: 'retailer.name.keyword',
    storeName: 'store.name.keyword',
    companyName: 'company.name.keyword',
  };

  const sortObject = {};
  const split = sort.split(':');
  const direction = (parseInt(split[1], 10) === 1 ? 'asc' : 'desc');

  if (mapping[split[0]]) {
    sortObject[mapping[split[0]]] = direction;
  } else {
    sortObject[split[0]] = direction;
  }

  return sortObject;
}

/**
 * Query activity
 * @param dateParams Date range
 * @param query
 * @param limit
 * @param skip
 */
async function queryActivity(dateParams, query, limit, skip) {
  // Sort by created by default
  let sort = 'created:asc';

  // Allow for search on multiple values for specific inputs
  let queryFormatted = allowSearchOnMultipleValues(Object.assign({}, query));

  const newQuery = {bool: {must: []}};

  // Search for card ID
  if (queryFormatted._id) {
    newQuery.bool.must.push({match: {'card._id': queryFormatted._id}});
  }

  if (queryFormatted.balance) {
    newQuery.bool.must.push({
      bool: {
        should: queryFormatted.balance.map(balance => ({range: {balance: numToRange(parseFloat(balance))}}))
      }
    });
  }

  if (queryFormatted.type) {
    newQuery.bool.must.push({match: {type: queryFormatted.type}});
  }

  if (queryFormatted.orderNumber) {
    if (queryFormatted.orderNumber.length === 1 && queryFormatted.orderNumber[0] === '_') {
      newQuery.bool.must.push({
        bool: {
          should: [
            {match: {orderNumber: ""}},
            {bool: {must_not: {exists: {field: "orderNumber"}}}}
          ]
        }
      });
    } else {
      newQuery.bool.must.push({
        bool: {
          should: queryFormatted.orderNumber.map(orderNumber => ({match: {orderNumber}}))
        }
      });
    }
  }

  if (queryFormatted.liquidationSoldFor) {
    const liquidationSoldFor = parseFloat(queryFormatted.liquidationSoldFor);
    newQuery.bool.must.push({range: {liquidationSoldFor: numToRange(liquidationSoldFor)}});
  }

  if (queryFormatted.smpAch) {
    newQuery.bool.must.push({
      bool: {
        should: queryFormatted.smpAch.map(smpAch => ({prefix: {smpAch}}))
      }
    });
  }

  if (queryFormatted.cqAch) {
    const cqAchs = queryFormatted.cqAch.map(cqAch => ({prefix: {cqAch}}));
    const deductions = queryFormatted.cqAch.map(deduction => ({prefix: {deduction}}));
    cqAchs.push(deductions);
    newQuery.bool.must.push({
      bool: {
        should: cqAchs
      }
    });
  }

  // Blank
  if (! queryFormatted.activityStatus && (! queryFormatted.company || queryFormatted.isAdmin)) {
    newQuery.bool.must.push({match: {activityStatus: 'Not shipped'}});
  }

  if (queryFormatted.activityStatus && queryFormatted.activityStatus !== '-') {
    newQuery.bool.must.push({match: {activityStatus: queryFormatted.activityStatus}});
  }

  if (queryFormatted.isAdmin) {
    sort = 'systemTime:asc';
  }

  if (queryFormatted.isTransactions) {
    newQuery.bool.must.push({match: {isTransaction: queryFormatted.isTransactions === 'true'}});
  }

  if (queryFormatted.verifiedBalance) {
    newQuery.bool.must.push({
      bool: {
        should: queryFormatted.verifiedBalance.map(verifiedBalance => ({range: {verifiedBalance: numToRange(parseFloat(verifiedBalance))}}))
      }
    });
  }

  // Returns just cards that have been sold
  newQuery.bool.must.push({match: {soldToLiquidation: true}});

  if (queryFormatted.company) {
    newQuery.bool.must.push({match: {"company._id": queryFormatted.company}});
  }

  if (queryFormatted.creditedOrRejected) {
    newQuery.bool.must.push({
      bool: {
        should: [
          {match: {adjustmentStatus: 'denial'}},
          {match: {adjustmentStatus: 'credit'}}
        ]
      }
    });
  }

  if (queryFormatted.adjustmentStatus) {
    newQuery.bool.must.push({
      match: {
        adjustmentStatus: queryFormatted.adjustmentStatus
      }
    });
  }

  if (dateParams.created) {
    const dateRange = {};

    if (dateParams.created.$gt) {
      dateRange.gt = dateParams.created.$gt;
    }

    if (dateParams.created.$lt) {
      dateRange.lt = dateParams.created.$lt;
    }

    newQuery.bool.must.push({
      range: {created: dateRange}
    });
  }

  if (queryFormatted.balanceCardIssued === 'true') {
    newQuery.bool.must.push({
      range: {"transaction.nccCardValue": {gt: 0}}
    });
  }

  if (queryFormatted.balanceCardIssued === 'false') {
    newQuery.bool.must.push({
      match: {"transaction.nccCardValue": 0}
    });
  }

  if (queryFormatted.transactionPrefix) {
    newQuery.bool.must.push({
      bool: {
        should: queryFormatted.transactionPrefix.map(transactionPrefix => ({prefix: {"transaction.prefix": transactionPrefix}}))
      }
    });
  }

  if (queryFormatted.number) {
    newQuery.bool.must.push({
      bool: {
        // Call toLowerCase(), just in case the query has letters in it
        // which would fail to match anything with the current mapping
        // unless they're in lowercase
        should: queryFormatted.number.map(number => ({wildcard: {"card.number": '*' + number.toLowerCase() + '*'}}))
      }
    });
  }

  if (queryFormatted.pin) {
    newQuery.bool.must.push({
      bool: {
        should: queryFormatted.pin.map(pin => ({prefix: {"card.pin": pin}}))
      }
    });
  }

  if (queryFormatted.retailer) {
    newQuery.bool.must.push({
      bool: {
        should: queryFormatted.retailer.map(retailer => ({prefix: {"retailer.name": retailer}}))
      }
    });
  }

  if (queryFormatted.customerName) {
    newQuery.bool.must.push({prefix: {"customer.fullName": queryFormatted.customerName}});
  }

  if (queryFormatted.customerPhone) {
    newQuery.bool.must.push({prefix: {"customer.phone": queryFormatted.customerPhone}});
  }

  if (queryFormatted.customerEmail) {
    newQuery.bool.must.push({prefix: {"customer.email": queryFormatted.customerEmail}});
  }

  if (queryFormatted.employeeName) {
    newQuery.bool.must.push({
      bool: {
        // Check firstName and lastName as well because some users might not have the fullName attribute
        should: ['user.firstName', 'user.lastName', 'user.fullName'].map(attr => ({
          prefix: {[attr]: queryFormatted.employeeName}
        }))
      }
    });
  }

  if (queryFormatted.adminActivityNote) {
    newQuery.bool.must.push({
      bool: {
        should: queryFormatted.adminActivityNote.map(adminActivityNote => ({prefix: {adminActivityNote}}))
      }
    });
  }

  if (queryFormatted.smp) {
    newQuery.bool.must.push({match: {smp: queryFormatted.smp}});
  }

  if (queryFormatted.batch) {
    newQuery.bool.must.push({match: {"batch._id": queryFormatted.batch}});
  }

  if (queryFormatted.store) {
    newQuery.bool.must.push({match: {"store._id": queryFormatted.store}});
  }

  if (queryFormatted.sort) {
    sort = [esSort(queryFormatted.sort)];
  }

  try {
    const options = {sort, from: skip, size: limit};
    options.aggs = {
        "balance": {
            "sum": {
                "field": "balance",
                "missing": 0
            }
        },
        "verifiedBalance": {
            "sum": {
                "field": "verifiedBalance",
                "missing": 0
            }
        },
        "buyRate": {
            "avg": {
                "field": "buyRate",
                "missing": 0
            }
        },
        "buyAmount": {
            "sum": {
                "field": "buyAmount",
                "missing": 0
            }
        },
        "soldFor": {
            "sum": {
                "script": {
                  "file": "soldFor"
                }
            }
        },
        "cqPaid": {
            "sum": {
                "script": {
                    "file": "cqPaid"
                }
            }
        },
        "serviceFee": {
            "sum": {
                "script": {
                    "file": "serviceFee"
                }
            }
        },
        "netAmount": {
            "sum": {
                "script": {
                    "file": "netAmount",
                    "params": {
                        "cqAchSearch": queryFormatted.cqAchSearch
                    }
                }
            }
        },
        "cqOwes": {
            "sum": {
                "script": {
                    "file": "cqOwes"
                }
            }
        },
        "outstandingBuyAmount": {
            "sum": {
                "script": {
                    "file": "outstandingBuyAmount"
                }
            }
        }
    };

    // const data = {query: newQuery, options};
    // (new ElasticLogger()).log({client: 'mongoosastic', data: dotToUnderscore(data)});
    return await Inventory.search(newQuery, options);
  } catch (e) {
    console.log('************************ERR IN QUERY ACTIVITY************************');
    console.log(e);

    // Let the caller handles it
    throw e;
  }
}

/**
 * Determine whether we need to display full card number or last 4 digits
 * @param inventory
 * @param companySettings
 * @param userRole Role of current user
 * @return {Promise.<T>}
 */
function fullOrPartialCardNumber(inventory, companySettings, userRole) {
  if (['manager', 'employee'].indexOf(userRole) !== -1 && companySettings.useAlternateGCMGR) {
    inventory.card.number = inventory.card.getLast4Digits();
  }
  return inventory;
}

/**
 * Calculate values (from cache if possible, else cache result)
 * @param inventories
 * @param companySettings
 * @param userRole
 * @param companyId
 * @param getDenialsPayments Whether to
 * @return {Promise.<void>}
 */
async function getCalculatedValues(inventories, companySettings, userRole, companyId, getDenialsPayments) {
  // Inventories after all calculations or cache applications
  const finalInventories = [];
  // return inventories.forEach(getCalc);
  for (let inventory of inventories) {
    if (!inventory) {
      continue;
    }
    // Determine whether we need full or partial card number
    inventory = await fullOrPartialCardNumber(inventory, companySettings, userRole);

    // Temp critical bug fix. We need to figure out which place is the best one to put this
    // or put the mapped value along with the cached inventory data.
    if (config.smpNames[inventory.smp]) {
      inventory.smp = config.smpNames[inventory.smp];
    }

    // Add to final
    finalInventories.push(inventory);
  }
  return Promise.resolve(finalInventories);
}

/**
 * Create CSV for an SMP
 * @param inventories
 * @param csvSmp
 * @param getDenialsPayments Get payments to account for past denials
 * @param companyId Company ID for corporate admin view
 * @param res
 * @return {Promise.<void>}
 */
async function getSmpCsv(inventories, csvSmp, csvExportFilter = 'activity', getDenialsPayments, companyId, res) {
  return new Promise(async resolve => {
    let format = [];
    const isCc = csvSmp.toLowerCase() === 'cardcash';
    const isCp = csvSmp.toLowerCase() === 'cardpool';
    const isGcz = csvSmp.toLowerCase() === 'giftcardzen';
    const isCorporate = csvSmp.toLowerCase() === 'corporate';
    if (isCp) {
      format = ['retailer', 'number', 'pin', 'balance'];
    } else if (isCc) {
      format = ['Merchant', 'Number', 'Pin', 'Balance', 'REF'];
    } else if (isGcz) {
      format = ['Merchant', 'Card Number', 'PIN', 'Balance', 'Note'];
      // Corporate, get all
    } else if (isCorporate) {
      format = ['userTime', 'cardId', 'retailer', 'number', 'pin', 'balance', 'verifiedBalance', 'netAmount', 'customerName', 'buyAmount', 'ach'];
      // Add in denial amount for CSV denials
      if (getDenialsPayments) {
        format.splice(9, 0, 'rejectAmount');
      }
    } else {
      throw new Error('unknownSmpFormat');
    }
    const csvWriter = CsvWriter({ headers: format});
    // UUID for corporate csvs
    const outfileName = isCorporate ? `${csvExportFilter}-${companyId}-${uuid()}` : csvSmp;
    const outFile = `salesCsv/${moment().format('YYYYMMDD')}-${outfileName}.csv`;
    const outFilePath = path.resolve(__dirname, '../', '../', '../', '../', outFile);
    // Remove existing file
    if (fs.existsSync(outFile)) {
      fs.unlinkSync(outFile);
    }

    let fileStream = fs.createWriteStream(outFilePath);
    csvWriter.pipe(fileStream);

    inventories = inventories.filter(inventory => {
      let used = false;
      // Filter for corporate
      if (isCorporate) {
        if (csvExportFilter === 'activity') {
          //Non-existant inventory.activityStatus is set to "Not shipped" further up in the processing pipeline
          //TODO: Fix, and test, the first part of this conditional as it will never trigger...
          used = !(['notshipped', 'rejected'].includes(inventory.activityStatus.toLowerCase()) || inventory.rejected);
        }
        else if (csvExportFilter === 'denials') {
          used = (inventory.rejected && (inventory.deduction === '' || !_.has(inventory,'deduction')));
        }
        else if(csvExportFilter === 'chargebacks') {
          used = (inventory.rejected && _.has(inventory,'deduction') && inventory.deduction != '');
        }
      } else {
        let activityStatus = typeof inventory.activityStatus === 'string' ? inventory.activityStatus.toLowerCase() : '';
        activityStatus = activityStatus.replace(/\s/g, '');
        if (inventory.type.toLowerCase() === 'electronic') {
          used = !activityStatus || activityStatus === 'notshipped';
          // Physical cards must be received
        } else {
          used = activityStatus === 'receivedcq';
        }
      }
      if (!used) {
        return false;
      }
      // 2
      if (isCc) {
        return inventory.smp.toLowerCase() === 'cardcash' || inventory.smp === config.smpIds.CARDCASH;
        // 3
      } else if (isCp) {
        return inventory.smp.toLowerCase() === 'cardpool' || inventory.smp === config.smpIds.CARDPOOL;
      } else if (isGcz) {
        return inventory.smp.toLowerCase() === 'giftcardzen' || inventory.smp === config.smpIds.GIFTCARDZEN;
        // Corporate
      } else if (isCorporate) {
        return inventory;
      }
    });
    // Create columns
    for (let inventory of inventories) {
      let row;
      if (inventory.card) {
        // ['cardId', 'retailer', 'number', 'pin', 'balance', 'verifiedBalance', 'netAmount', 'customerName', 'buyAmount', 'ach']
        if (isCorporate) {
          const netAmount = inventory.isTransaction ? inventory.transaction.netPayout : inventory.netAmount;
          let customerName = '';
          // Get customer name, which could be different based on which endpoint the cards sold from
          if (inventory.card.lqCustomerName) {
            customerName = inventory.card.lqCustomerName;
          } else if (inventory.customer && inventory.customer.fullName) {
            customerName = inventory.customer.fullName;
          }
          row = [moment(inventory.created).format(), // User time
                 inventory.card._id, // Card ID
                 inventory.retailer.name, // Retailer name
                 inventory.card.number, // Card number
                 inventory.card.pin, // Pin code
                 typeof inventory.balance === 'number' ? inventory.balance.toFixed(2) : '', // Balance
                 inventory.verifiedBalance ? inventory.verifiedBalance.toFixed(2) : inventory.balance.toFixed(2), // VB
                 netAmount.toFixed(2), // Net amount
                 customerName, // Customer name
                 inventory.buyAmount.toFixed(2), // Buy amount
                 inventory.cqAch // CQ Ach
          ];
          // Denials
          if (getDenialsPayments) {
            row.splice(9, 0, inventory.rejectAmount ? inventory.rejectAmount.toFixed(2) : `(${inventory.creditAmount.toFixed(2)})`);
          }
        } else {
          // Get retailer object
          if (_.isPlainObject(inventory.retailer)) {
            inventory.retailer = await Retailer.findById(inventory.retailer._id);
          }
          const retailerName = inventory.retailer.getSmpSpelling()[csvSmp] || inventory.retailer.name;
          row = [retailerName, inventory.card.number, inventory.card.pin, typeof inventory.verifiedBalance === 'number' ? inventory.verifiedBalance : inventory.balance];
        }
        if (isCc || isGcz) {
          row.push('');
        }
        csvWriter.write(row);
      }
    }

    fileStream.on('finish', async () => {
      const destPath = `activity/${isCorporate ? companyId : csvSmp}/${csvExportFilter}-${moment().format('YYYYMMDD-HHmmss')}.csv`;
      await StorageService.write(outFilePath, destPath);
      return resolve(res.json({url: await StorageService.getDownloadUrl(destPath)}));
    });

    csvWriter.end();

  });
}

/**
 * Get all activity (admin revised)
 */
export async function getAllActivityRevised(req, res) {
  try {
    const {
            perPage,
            offset
          } = req.params;
    const query = req.query;
    let companyId;
    let companySettings = null;
    // Download CSV for an SMP
    let csvSmp;
    let csvExportFilter;
    // See if a CQ ACH search is being performed
    let cqAchCompanySearch = !!query.cqAch;
    // Whether to get denial payments
    let getDenialsPayments = false;
    let payments = [];
    let meta = {};
    // Date range params
    const findParams = getActivityDateRange(req.params);
    let inventories;

    // Store company ID and format for query
    if (query.companyId) {
      companyId = query.companyId;
      query.company = query.companyId;
      delete query.companyId;
    }
    // Download CSV
    if (query.csvSmp) {
      csvSmp = query.csvSmp;
      delete query.csvSmp;
    }
    if (query.csvExportFilter) {
      csvExportFilter = query.csvExportFilter;
      delete query.csvExportFilter;
    }
    // Set rejected to boolean
    if (query.rejected && query.rejected === 'true') {
      // Either credited or rejected
      query.creditedOrRejected = true;
      delete query.rejected;
      getDenialsPayments = true;
      // Search all statuses for denials
      query.activityStatus = '-';
    }
    // User is admin
    if (req.user.role === 'admin') {
      query.isAdmin = true;
    }
    query.cqAchSearch = cqAchCompanySearch;
    const queryRes = await queryActivity(findParams, query, perPage, offset);
    inventories = queryRes.hits.hits;
    // If querying as corporate
    const company = await Company.findById(companyId);
    if (company) {
      companySettings = await company.getSettings();
    }
    // Calculate values for activity
    inventories = await getCalculatedValues(inventories, companySettings, req.user.role, companyId, getDenialsPayments);

    meta.totals = {};
    Object.keys(queryRes.aggregations).forEach(item => {
      meta.totals[item] = queryRes.aggregations[item].value;
    });

    if (getDenialsPayments) {
      payments = await DenialPayments.find({
        customer: query.customer
      });
    }

    meta.total = queryRes.hits.total;
    meta.pages = Math.ceil(meta.total  / perPage);

    // Download formatted for upload to an SMP
    if (csvSmp) {
      return await getSmpCsv(inventories, csvSmp, csvExportFilter, getDenialsPayments, companyId, res);
    }
    return res.json({
      inventories,
      meta,
      payments
    });
  } catch (err) {
    console.log('**************GETALLACTIVITYREVISED ERR**********');
    console.log(err);

    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'getAllActivityRevised',
      controller: 'company.controller',
      stack: err ? err.stack : null,
      error: err,

    });

    return res.status(500).json({err: err});
  }
}

/**
 * Retrieve a company summary report
 */
export async function getCompanySummary(req, res) {
  const {companyId} = req.params;
  let begin = req.params.begin;
  let end = req.params.end;
  begin = moment.utc(begin).startOf('day');
  end = moment.utc(end).endOf('day');
  let dbStores;

  try {
    Store.find({
      companyId
    })
      .then(stores => {
        dbStores = stores;

        const promises = [];
        stores.forEach(store => {
          promises.push(queryActivity({created: {$gt: begin.toDate(), $lt: end.toDate()}}, {company: companyId, store: store._id, cqAchSearch: false}, 0, 0));
        });

        return Promise.all(promises);
      })
      .then(results => {
        const storesWithData = [];
        for (let i = 0; i < results.length; i++) {
          const resultObject = {};
          Object.keys(results[i].aggregations).forEach(item => {
            resultObject[item] = results[i].aggregations[item].value;
          });
          storesWithData.push({
            store: dbStores[i],
            data: resultObject
          });
        }
        return res.json({data: storesWithData});
      });
  }
  catch (err) {
    console.log('**************getCompanySummary ERR**********');
    console.log(err);

    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'getCompanySummary',
      controller: 'company.controller',
      stack: err ? err.stack : null,
      error: err,

    });

    return res.status(500).json({err: err});
  }
}

/**
 * Sell a card which is not auto-sold
 */
export async function sellNonAutoCard(req, res) {
  const user = req.user;
  const params = req.params;
  const isCorporateAdmin = user.role === 'corporate-admin';
  // Wrong company
  if (user.company.toString() !== params.companyId) {
    return res.status(401).json();
  }
  // Right company, wrong store
  if (!isCorporateAdmin) {
    if (!user.store || user.store.toString() !== params.storeId) {
      return res.status(401).json();
    }
  }
  Inventory.findById(params.inventoryId)
  .then(inventory => {
    inventory.proceedWithSale = true;
    inventory.save();
  })
  .then(inventory => res.json(inventory))
  .catch(async err => {
    console.log('**************ERR IN SELL NON AUTO CARD**********');
    console.log(err);

    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'sellNonAutoCard',
      controller: 'company.controller',
      stack: err ? err.stack : null,
      error: err,

    });

    return res.status(500).json(err);
  });
}

/**
 * Check if there is inventory which needs to be reconciled
 */
export async function checkInventoryNeedsReconciled(req, res) {
  const {companyId, storeId} = req.params;

  try {
    Inventory.find({
      company: companyId,
      store: storeId,
      soldToLiquidation: true,
      reconciliation: {
        $exists: false
      }
    })
      .then(inventories => {
        return res.json({
          needReconciliation: !!inventories.length
        });
      })
  }
  catch (err) {
    console.log('**************ERR IN checkInventoryNeedsReconciled**********');
    console.log(err);

    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'checkInventoryNeedsReconciled',
      controller: 'company.controller',
      stack: err ? err.stack : null,
      error: err,

    });

    return res.status(500).json(err);
  }
}

/**
 * Get receipts for a company
 */
export async function getReceipts(req, res) {
  const {perPage = 20, offset = 0} = req.query;

  try {
    const receiptService = new ReceiptService();
    const query = Object.assign({}, _.pick(req.query, ['created']), {company: req.user.company});
    const [totalReceipts, receipts] = await Promise.all([
      receiptService.getReceiptsCount(query),
      receiptService.getReceipts(query, {perPage: parseInt(perPage, 10), offset: parseInt(offset, 10)})
    ]);

    res.json({
      data: receipts,
      pagination: {
        total: totalReceipts
      }
    });
  } catch (err) {
    console.log('**************ERR IN GET RECEIPTS**********');
    console.log(err);

    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'getReceipts',
      controller: 'company.controller',
      stack: err ? err.stack : null,
      error: err,

    });

    return res.status(500).json(err);
  }
}

/**
 * Delete one or more inventories
 */
export async function deleteInventories(req, res) {
  const body = req.body;
  const inventories = [];

  try {
    _.forEach(body, thisInventory => {
      inventories.push(thisInventory);
    });
    Inventory.find({
      _id: {
        $in: inventories
      }
    })
      .populate('card')
      .then(async dbInventories => {
        for (const inventory of dbInventories) {
          if (inventory.transaction) {
            await inventory.removeReserve();
          }
          await inventory.card.remove();
          await inventory.remove();
        }

        return res.json();
      });
  }
  catch (err) {
    console.log('**************ERR IN deleteInventories**********');
    console.log(err);

    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'deleteInventories',
      controller: 'company.controller',
      stack: err ? err.stack : null,
      error: err,

    });

    return res.status(500).json(err);
  }
}

/**
 * Change users role
 */
export async function updateRole(req, res){
  const userId = req.params.userId;

  try {
    User.findById(userId)
      .then(user => {
        user.role = req.params.userRole;
        user.save();
      })
      .then(() => res.json())
  }
  catch (err) {
    console.log('**************ERR IN updateRole**********');
    console.log(err);

    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'updateRole',
      controller: 'company.controller',
      stack: err ? err.stack : null,
      error: err,

    });

    return res.status(500).json(err);
  }
}
