import {
  biNotEnabled,
  companyBelongsToUser,
  companyExists,
  companyNameIsUnique,
  customerExists,
  isNotEmpty,
  isObjectId,
  isValidPassword,
  pinRequiredThisRetailer,
  retailerExists,
  retailerValidation,
  storeBelongsToUser,
  storeExists,
  userExists
} from '../../helpers/validation';
import Retailer from '../retailer/retailer.model';
import {isEmail} from '../../helpers';

// Ensure valid balance in biCompleted
const validBiCompleteBalance = (val) => {
  return typeof val !== 'number' && val !== null && val !== '' && !isNaN(parseFloat(val));
};

// Ensure we have a valid retailer
const validBiCompleteRetailer = async (val) => {
  const retailer = await Retailer.findOne({
    $or: [{
      gsId: val
    }, {
      aiId: val
    }]
  });
  return !!retailer;
};

/**
 * Validation rules for callbackLog route
 */
export default {
  '/bi'                 : {
    requestId: [{skipValidation: true}],
    number  : [{
      rule   : isNotEmpty,
      message: 'Number cannot be blank'
    }],
    retailer: [{
      rule   : isNotEmpty,
      message: 'Retailer cannot be blank'
    }, {
      rule   : isObjectId,
      message: `Retailer ID invalid`
    }, {
      rule   : retailerExists,
      message: `Retailer not found`
    }, {
      rule   : biNotEnabled,
      message: `Balance Inquiry is not supported for this retailer`
    }, {
      rule        : pinRequiredThisRetailer,
      message     : 'A PIN is required for this retailer',
      compareParam: ['pin']
    }]
  },
  '/account/create'     : {
    email    : [{
      rule   : isEmail,
      message: 'Invalid email'
    }, {
      rule   : userExists,
      message: 'This email address is already in use'
    }],
    password : [{
      rule   : isValidPassword,
      message: 'Passwords must be at least 8 characters'
    }],
    firstName: [{
      rule   : isNotEmpty,
      message: 'First name cannot be blank'
    }],
    lastName : [{
      rule   : isNotEmpty,
      message: 'Last name cannot be blank'
    }],
    company  : [{
      rule   : isNotEmpty,
      message: 'Company name cannot be blank'
    }, {
      rule   : companyNameIsUnique,
      message: 'This company name is in use'
    }],
    store    : [{
      rule   : isNotEmpty,
      message: 'Store name cannot be blank'
    }]
  },
  '/account/create/user': {
    email    : [{
      rule   : isEmail,
      message: 'Invalid email'
    }],
    password : [{
      rule   : isValidPassword,
      message: 'Passwords must be at least 8 characters'
    }],
    firstName: [{
      rule   : isNotEmpty,
      message: 'First name cannot be blank'
    }],
    lastName : [{
      rule   : isNotEmpty,
      message: 'Last name cannot be blank'
    }],
    companyId: [{
      rule   : isObjectId,
      message: 'Invalid company ID'
    }, {
      rule   : companyExists,
      message: 'Company does not exist'
    }, {
      rule   : companyBelongsToUser,
      message: 'The specified company is not associated with your account',
      user   : true
    }],
    storeId  : [{
      rule   : isNotEmpty,
      message: 'Store ID is required'
    }, {
      rule   : isObjectId,
      message: 'Invalid store ID'
    }, {
      rule   : storeExists,
      message: 'Store does not exist'
    }, {
      rule   : storeBelongsToUser,
      message: 'The specified store is not associated with your account',
      user   : true
    }]
  },
  '/new'                : {
    'number'  : [{
      rule   : isNotEmpty,
      message: 'Card number is required'
    }, {
      rule        : retailerValidation,
      message     : 'Card Number & PIN validation failed',
      compareParam: ['pin', 'retailer'],
      user        : true
    }],
    retailer: [{
      rule   : isNotEmpty,
      message: 'Retailer cannot be blank'
    }, {
      rule   : isObjectId,
      message: `Retailer ID invalid`
    }, {
      rule   : retailerExists,
      message: `Retailer not found`
    }, {
      rule        : pinRequiredThisRetailer,
      message     : 'A PIN is required for this retailer',
      compareParam: ['pin']
    }],
    'userTime': [{
      notRegex: /^\d{4}-\d{2}-\d{2}$/,
      message : 'userTime must be a valid ISO-8601 string'
    }, {
      date   : true,
      message: 'userTime must be a valid ISO-8601 string'
    }],
    'balance' : [{
      regex  : /^\d+(\.\d{1,2})?$/,
      message: 'Invalid balance. balance must be an integer or float'
    }]
  },
  '/transactions'       : {
    'number'          : [{
      rule   : isNotEmpty,
      message: 'Card number is required'
    }],
    'retailer'        : [{
      rule   : isObjectId,
      message: 'Invalid retailer ID'
    }],
    'userTime'        : [{
      notRegex: /^\d{4}-\d{2}-\d{2}$/,
      message : 'userTime must be a valid ISO-8601 string'
    }, {
      date   : true,
      message: 'userTime must be a valid ISO-8601 string'
    }],
    'balance'         : [{
      regex  : /^\d+(\.\d{1,2})?$/,
      message: 'Invalid balance. balance must be an integer or float'
    }],
    'transactionId'   : [{
      rule   : isNotEmpty,
      message: 'transactionId is required'
    }],
    'transactionTotal': [{
      rule   : isNotEmpty,
      message: 'transactionTotal is required'
    }, {
      type   : 'isNumeric',
      message: 'transactionTotal must be a number'
    }],
    'customerId'      : [{
      rule   : isObjectId,
      message: 'customerId is invalid'
    }, {
      rule   : customerExists,
      message: 'Customer does not exist',
      user   : true
    }],
  },
  '/bi/:requestId'      : {
    'retailerId': [{
      rule   : validBiCompleteRetailer,
      message: 'Valid retailer ID is required'
    }],
    'number'    : [{
      rule   : isNotEmpty,
      message: 'Card number is required'
    }],
    'balance'   : [{
      rule   : validBiCompleteBalance,
      message: 'Invalid balance. Balance must be an integer or float'
    }],
  }
}
