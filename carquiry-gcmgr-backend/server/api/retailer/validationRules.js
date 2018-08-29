import {isObjectId, recordExists} from '../../helpers/validation';

/**
 * Validation rules for callbackLog route
 */
export default {
  ':retailer/regex': {
    'retailer': [
      {rule: isObjectId, message: 'Retailer ID is invalid'},
      {async: recordExists, model: 'Retailer', message: 'Retailer does not exist'}
    ]
  }
}
