import * as _ from 'lodash';
import * as moment from 'moment';
import * as validator from 'validator';
import {Types} from 'mongoose';
import Card from '../api/card/card.model';
import Company, {ICompany} from '../api/company/company.model';
import User from '../api/user/user.model';
import Store, {IStore} from '../api/stores/store.model';
import Retailer from '../api/retailer/retailer.model';
import CardRegexValidator from '../api/lq/cardRegexValidator';
import config from '../config/environment';
import Customer from "../api/customer/customer.model";

const isValidObjectId = Types.ObjectId.isValid;

const models = {
  Card,
  Company
};

/**
 * Check structured validation in middleware
 * @param validationRules Validation rules for endpoints in this route
 */
export function checkStructuredValidation(validationRules) {
  return function (req, res, next) {
    return async function () {
      req.validationFailed = false;
      // No route for some weird reason
      if (!req.route) {
        return next();
      }
      const route = req.route.path;
      if (!validationRules) {
        return next();
      }
      // Get this specific validation rule
      const ruleToUse = validationRules[route];
      // No validation rules for this endpoint
      if (!ruleToUse) {
        return next();
      }
      // Check for validation errors
      const body = Object.assign({}, req.body);
      const params = Object.assign({}, req.params);
      const valErrors = await runValidation(ruleToUse, convertBodyToStrings(body), convertBodyToStrings(params), req.user);
      // Return validation errors
      if (valErrors.length) {
        returnValidationErrors(res, valErrors);
        req.validationFailed = true;
        return;
      }
      next();
    }();
  }
}

/**
 * Convert all body props to string
 * @param body Req.body
 * @return {*}
 */
export function convertBodyToStrings(body) {
  const bodyStrings = {};
  // Convert everything to a string for validation
  for (let i in body) {
    if (body.hasOwnProperty(i)) {
      try {
        if (typeof body[i] !== 'undefined' && typeof body[i].toString === 'function') {
          bodyStrings[i] = body[i].toString();
        }
      } catch (e) {

      }
    }
  }
  return bodyStrings;
}

/**
 * Ensure that we have a decimal value, rather than an integer representation of percentages
 * @param next
 * @param props
 * @param propMaxes
 */
export function ensureDecimals(next, props, propMaxes = {}) {
  props.forEach(prop => {
    if (this[prop]) {
      // Make sure it's a decimal
      if (this[prop]) {
        this[prop] = parseFloat(this[prop]);
        let maxValue = 1;
        // margin could potentially be less than "1", but entered wrong. It will always be less than 10%
        if (propMaxes[prop]) {
          maxValue = propMaxes[prop];
        }
        if (this[prop] > maxValue) {
          this[prop] = (this[prop] / 100).toFixed(3);
        }
      }
    }
  });
  next();
}

function pushValError(valErrors, k, v) {
  valErrors.push({name: k, message: v.message});
}

/**
 * Return validation errors
 * @param res
 * @param valErrors Validation errors
 */
export function returnValidationErrors(res, valErrors) {
  return res.status(400).json({error: {errors: valErrors}});
}

/**
 * Combine vals
 * @param body
 * @param params
 * @param k
 * @returns {*}
 */
function getCompareVal(body, params, k) {
  // Value in body
  let compareVal = _.get(body, k);
  // Value in params
  if (!compareVal) {
    compareVal = _.get(params, k);
  }
  return typeof compareVal === 'string' ? compareVal : '';
}

/**
 * Run validation on a request body
 * @param validation Validation rules
 * @param body Request body
 * @param params Path params
 * @param user Current user
 * @return {Array}
 */
export async function runValidation(validation, body, params = {}, user) {
  const valErrors = [];
  try {
    // So we can break out if we need to stop validation, like on checking BI request ID only
    validationLoop:
    for (let [k, v] of Object.entries(validation)) {
      const compareVal = getCompareVal(body, params, k);

      if (_.isPlainObject(v)) {
        v = [v];
      }

      for (const thisV of v) {
        if (thisV.type && !validator[thisV.type](compareVal, thisV.options)) {
          // Invalid based on validator.js
          pushValError(valErrors, k, thisV);
        } else if (thisV.regex && !thisV.regex.test(compareVal)) {
          // Invalid based on regex
          pushValError(valErrors, k, thisV);
          // Check to make sure string does not match this regex
        } else if (thisV.notRegex && thisV.notRegex.test(compareVal)) {
          pushValError(valErrors, k, thisV);
        } else if (thisV.date && !moment(compareVal).isValid()) {
          // Invalid based on moment()
          pushValError(valErrors, k, thisV);
          // Invalid based on custom validation rule
          /**
           * This bears some additional explanation
           * When a custom rule is passed in, it will compare again the function which is called
           * If that function returns false, the validation fails
           * The req.body or req.param value specified is the first param of the custom function
           * If user:true is included in the validation definition, then the user object
           * is passed into the custom function as the second param
           * Any additional params from req.body/req.params can be passed in by specifying compareParams, such as
           * compareParam: ['pin', 'retailer'],
           * Will pass in req.body.pin/req.params.pin first, then req.body.retailer/req.params.retailer second
           */
        } else if (thisV.rule) {
          const params = [compareVal];
          // Use user for comparison
          if (thisV.user) {
            params.push(user);
          }
          // Additional parameters used in comparison
          if (thisV.compareParam) {
            thisV.compareParam.forEach(param => {
              const additionalCompareVal = getCompareVal(body, params, param);
              params.push(additionalCompareVal);
            });
          }
          let ruleRes = thisV.rule.apply(null, params, user);
          if (ruleRes instanceof Promise) {
            ruleRes = await ruleRes;
          }

          if (!ruleRes) {
            pushValError(valErrors, k, thisV);
          }
        // Invalid based on record existence
        } else if (thisV.async && !await thisV.async(compareVal, models[thisV.model])) {
          // Invalid based on async validation rule
          pushValError(valErrors, k, thisV);
        } else if (thisV.enum && thisV.enum.indexOf(compareVal) === -1) {
          pushValError(valErrors, k, thisV);
          // Stop validation if this value is encountered and true
        } else if (thisV.skipValidation && compareVal) {
          break validationLoop;
        }
      }
    }
    return valErrors;
  } catch (err) {
    console.log('**************VALIDATION ERROR**********');
    console.log(err);
    return valErrors;
  }
}

/**
 * Err on the side of caution here
 */
export function isEmail(val) {
  return /.+@.+\..+/.test(val);
}

/**
 * Check if email is in use
 */
export async function userExists(val): Promise<boolean> {
  return await !!User.find({email: val}).count();
}

/**
 * Check if retailer has bi enabled
 * @param val
 * @returns {Promise<boolean>}
 */
export async function biNotEnabled(val) {
  if (val === config.biTestRetailer) {
    return true;
  }
  if (!isObjectId(val)) {
    return false;
  }
  const retailer = await Retailer.findById(val);
  return !!(retailer.gsId || retailer.aiId);
}

/**
 * Check to make sure retailer exists
 * @param val Retailer ID
 * @returns {Promise<IRetailer | null>}
 */
export async function retailerExists(val) {
  if (val === config.biTestRetailer) {
    return true;
  }
  if (!isObjectId(val)) {
    return false;
  }
  return await Retailer.findById(val);
}

/**
 * Check to make sure customer exists and belongs to this company
 * @param val
 * @param user
 * @returns {Promise<any>}
 */
export async function customerExists(val, user) {
  if (!val) {
    return false;
  }
  if (!isObjectId(val)) {
    return false;
  }
  const customer = await Customer.findById(val);
  if (!customer) {
    return false;
  }
  return customer.company.toString() === user.company.toString();
}

/**
 * Check if company name is in use
 */
export async function companyNameIsUnique(val, user): Promise<boolean> {
  const company: ICompany = await Company.findOne({name: val});
  if (company) {
    if (company._id.toString() === user.company && user.company._id.toString()) {
      return true;
    }
  }
  return !company;
}

/**
 * Check if company exists
 * @param {string} val Company ID
 * @return {Promise<boolean>}
 */
export async function companyExists(val) {
  return await Company.count({_id: val}) > 0;
}

/**
 * Check that company belongs to the current user
 * @param val Company ID
 * @param user User record
 * @returns {Promise<boolean>}
 */
export async function companyBelongsToUser(val, user) {
  return user.company.toString() === val;
}

/**
 * Chck that store belongs to the current user
 * @param val Store ID
 * @param user User record
 * @returns {Promise<boolean>}
 */
export async function storeBelongsToUser(val, user) {
  if (user.role === 'corporate-admin') {
    const company = await Company.findById(user.company);
    return company.stores.filter(store => store.toString() === val.toString()).length;
  }
  return user.store.toString() === val;
}

/**
 * Make sure a store is specified
 * @param val
 * @param user
 * @returns {Promise<any>}
 */
export async function ensureStoreBelongsToUser(val, user): Promise<IStore> {
  let store = val;
  // No store, just use first one
  if (!store) {
    const company = await Company.findById(user.company);
    return await Store.findById(company.stores[0]);
  } else {
    if (!isObjectId(val)) {
      return null;
    }
    store = await Store.findById(val);
    // Valid store
    if (store.companyId.toString() === user.company.toString()) {
      return store;
    }
    return null;
  }
}

/**
 * Check if store exists
 */
export async function storeExists(val) {
  return await Store.count({_id: val}) > 0;
}

/**
 * Check if store does not exist
 */
export async function storeDoesNotExist(val) {
  return await Store.count({_id: val}) === 0;
}

/**
 * Just make sure passwords are 8 characters or more
 * @param val
 * @returns {boolean}
 */
export function isValidPassword(val) {
  return /.{8,}/.test(val);
}

/**
 * Checks a given string to make sure it's not empty
 *
 * @param {String} val
 * @return {Boolean}
 */
export function isNotEmpty(val) {
  return validator.isLength(validator.trim(val), {min: 1});
}

/**
 * Make sure a PIN is supplied for retailers which require it
 * @param retailerId Retailer ID from req.body
 * @param pin Pin from req.body
 * @returns {Promise<void>}
 */
export async function pinRequiredThisRetailer(retailerId, pin) {
  if (!retailerId) {
    return true;
  }
  if (!isObjectId(retailerId)) {
    return false;
  }
  const retailer = await Retailer.findById(retailerId);

  if (!retailer) {
    return true;
  }

  if (!retailer.pinRequired) {
    return true;
  }

  return typeof pin === 'string' && pin.trim().length;
}

/**
 * Check that card number and pin code validation passes
 * @param number
 * @param user
 * @param pin
 * @param retailerId
 * @returns {Promise<void>}
 */
export async function retailerValidation(number, user, pin, retailerId) {
  if (!retailerId) {
    return false;
  }
  const company = await Company.findById(user.company);
  if (!isObjectId(retailerId)) {
    return false;
  }
  const retailer = await Retailer.findById(retailerId);
  const settings = await company.getSettings();
  if (settings.validateCard) {
    return CardRegexValidator.validate(number, pin, retailer.numberRegex, retailer.pinRegex);
  }
  return true;
}

/**
 * Check for valid objectId
 * @param val
 */
export function isObjectId(val) {
  if (!val) {
    return false;
  }
  return isValidObjectId(val);
}

/**
 * Test simple date format: YYYY-MM-DD
 * @param val
 * @return {boolean}
 */
export function isSimpleDate(val) {
  return /^\d{4}-\d{2}-\d{2}$/.test(val);
}

/**
 * Check if value is a string
 */
export function isString(val) {
  return typeof val === 'string';
}

/**
 * Check to see if a record exists
 * @param id
 * @param model
 * @return {Promise.<void>}
 */
export async function recordExists(id, model) {
  if (!isObjectId(id)) {
    return false;
  }
  return !!await model.findById(id);
}
