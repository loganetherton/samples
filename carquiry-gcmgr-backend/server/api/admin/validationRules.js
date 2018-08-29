import {isObjectId, recordExists, isString, isNotEmpty} from '../../helpers/validation';
import Inventory from '../inventory/inventory.model';

async function inventoryExists(id) {
  // Can't import Inventory in the proper file, so this workaround will have to do for now
  return await recordExists(id, Inventory);
}

/**
 * Validation rules for callbackLog route
 */
export default {
  '/callbacks/:type': {
    'type': [
      {rule: function (type) {
        return ['biComplete', 'cardFinalized', 'cqPaymentInitiated', 'needsAttention', 'denial', 'resend'].indexOf(type) > -1;
      }, message: 'Invalid callback type'}
    ],
    // 'inventories': [
    //   {rule: function (val) {
    //     const ids = val.split(',');
    //
    //     for (const id of ids) {
    //       if (!isObjectId(id)) {
    //         return false;
    //       }
    //     }
    //
    //     return true;
    //   }, message: 'Invalid object ID'}
    // ]
  },
  '/sendAccountingEmail/:companyId': {
    'companyId': [
      {rule: isObjectId, message: 'Company ID is invalid'},
      {async: recordExists, model: 'Company', message: 'Company does not exist'}
    ],
    'emailBody': [
      {rule: isString, message: 'Email body is not a string'},
      {rule: isNotEmpty, message: 'Email body is required'}
    ],
    'emailSubject': [
      {rule: isString, message: 'Email subject is not a string'},
      {rule: isNotEmpty, message: 'Email subject is required'}
    ]
  },
  '/inventories/:inventory/history': {
    'inventory': [
      {rule: isObjectId, message: 'Inventory ID is invalid'},
      {async: inventoryExists, model: 'Inventory', message: 'Inventory does not exist'}
    ]
  },
  '/inventories/:inventory/revert/:log': {
    'inventory': [
      {rule: isObjectId, message: 'Inventory ID is invalid'},
      {async: inventoryExists, model: 'Inventory', message: 'Inventory does not exist'}
    ],
    'log': [
      {rule: isObjectId, message: 'Inventory log ID is invalid'},
      {async: recordExists, model: 'InventoryLog', message: 'Inventory log does not exist'}
    ]
  }
}
