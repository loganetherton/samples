import * as _ from 'lodash';

import '../company/autoBuyRate.model';
import '../company/companySettings.model';
import '../log/logs.model';
import '../company/company.model';
import '../card/card.model';
import '../stores/store.model';
import '../reserve/reserve.model';

import {getActiveSmps} from '../../helpers/smp';
import {finalizeTransaction} from '../inventory/inventory.helpers';
import Callback from '../callbackLog/callback';
import Card from '../card/card.model';
import Company from '../company/company.model';
import Inventory from '../inventory/inventory.model';

import vistaService from '../vista';

import config from '../../config/environment';
import BiRequestLog from '../biRequestLog/biRequestLog.model';

import {getCallbackUrl} from '../lq/lq.controller';

/**
 * Deterine who to sell the card to
 *
 * @param retailer
 * @param balance
 * @param companySettings
 * @return {
 *   rate: rate BEFORE margin
 *   type: card type
 *   smp: smp
 * }
 */
export function determineSellTo(retailer, balance, companySettings) {
  // Default globalLimits if disableLimits is on and globalLimits not set
  let globalLimits = companySettings.globalLimits;
  if (companySettings.disableLimits && !globalLimits) {
    globalLimits = {min: 0.01, max: Number.MAX_SAFE_INTEGER};
  }
  // Reject all cards which exceeed the global min/max set for this company, if one is set
  if (companySettings && companySettings.disableLimits && balance !== null) {
    const {min = 0.01, max = Number.MAX_SAFE_INTEGER} = globalLimits;
    if (min > balance || max < balance) {
      return false;
    }
  }
  let availableSmps = getActiveSmps();
  const sellRates = retailer.sellRates;
  const types = retailer.smpType;
  // SMP hard limits
  const hardLimits = {
    saveya: {
      min: 20,
      max: 300
    },
    cardcash: {
      min: 1,
      max: 2000
    },
    cardpool: {
      min: 25,
      max: 1000
    },
    giftcardzen: {
      min: -Infinity,
      max: Infinity
    },
    cardquiry: {
      min: -Infinity,
      max: Infinity
    },
    zeek: {
      min: -Infinity,
      max: Infinity
    }
  };
  let thisHardLimit = {
    min: -Infinity, max: Infinity
  };

  let sellTo = {
    rate: 0,
    smp: null,
    type: null
  };

  const eligibleSmps = {};

  // All Vista exchange cards should be sold only to CQ
  if (vistaService.isAnExchangeCardRetailer(retailer)) {
    availableSmps = ['cardquiry'];
  }

  // Determine SMP
  _.forEach(sellRates, (rate, smp) => {
    if (typeof smp === 'string' && availableSmps.indexOf(smp.toLowerCase()) !== -1) {
      // const maxMin = companySettings.disableLimits ? {min: 0.01, max: Infinity} : retailer.smpMaxMin[smp];
      const maxMin = retailer.smpMaxMin[smp];
      let maxValid = true;
      let minValid = true;
      let hardMinValid = true;
      let hardMaxValid = true;
      // If no balance, determine best sell rate
      if (balance !== null && typeof maxMin !== 'undefined') {
        maxValid = typeof maxMin.max === 'number' ? maxMin.max >= balance : true;
        minValid = typeof maxMin.min === 'number' ? maxMin.min <= balance : true;
      }
      // Check max/min
      if (maxValid && minValid) {
        const smpLower = smp.toLowerCase();
        if (typeof rate === 'number' && rate >= sellTo.rate && availableSmps.indexOf(smpLower) !== -1 && types[smp] !== 'disabled') {
          if (companySettings && companySettings.cardType && companySettings.cardType !== 'both' &&
              companySettings.cardType !== types[smp]) {
            return;
          }

          thisHardLimit = companySettings.disableLimits ? {min: -Infinity, max: Infinity} : hardLimits[smpLower];
          if (balance !== null) {
            hardMaxValid = thisHardLimit.max >= balance;
            hardMinValid = thisHardLimit.min <= balance;
          }
          if (hardMaxValid && hardMinValid) {
            sellTo.rate = rate;
            sellTo.smp = smp;
            sellTo.type = types[smp];

            if (eligibleSmps[rate]) {
              eligibleSmps[rate].push({
                smp,
                rate,
                type: types[smp]
              });
            } else {
              eligibleSmps[rate] = [{
                smp,
                rate,
                type: types[smp]
              }];
            }
          }
        }
      }
    }
  });

  let numberOfEligibleSmps = Object.keys(eligibleSmps).length;
  if (!numberOfEligibleSmps) {
    // Force sale to CQ
    if (companySettings.disableLimits) {
      const smpConfig = {rate: 1, smp: config.smpNames[config.smpIds.CARDQUIRY], type: 'electronic'};
      sellTo = smpConfig;
      eligibleSmps[1] = [smpConfig];
      numberOfEligibleSmps = 1;
    } else {
      return false;
    }
  }

  // No eligible SMPs here
  if (!numberOfEligibleSmps) {
    return false;
  }

  let eligible = null;
  // Find eligible
  try {
    eligible = eligibleSmps[sellTo.rate];
  } catch (e) {
    eligible = null;
  }
  // None found
  if (!eligible) {
    return false;
  }
  let smpPool = eligible.filter(smp => smp.type === 'electronic');
  if (!smpPool.length) {
    smpPool = eligible;
  }
  // Choose SMP randomly from highest rate
  if (smpPool && smpPool.length) {
    const smp = _.sample(smpPool);
    sellTo.smp = smp.smp;
    sellTo.type = smp.type;
  }

  // No SMP available
  if (sellTo.smp === null) {
    return false;
  }
  sellTo.smp = config.smpIdsByName[sellTo.smp.toLowerCase()];
  return sellTo;
}

/**
 * Recalculate transaction values for a transaction
 * @param inventory
 * @return {Promise.<void>}
 */
export async function recalculateTransactionAndReserve(inventory) {
  // Not a transaction
  if (!inventory.isTransaction) {
    return false;
  }
  // Undo previous reserve
  await inventory.removeReserve();
  // Get company settings
  const company = await Company.findOne(inventory.company);
  const companySettings = await company.getSettings();
  inventory = await finalizeTransaction(inventory, companySettings, true);
  // Create a new reserve
  const reserve = await inventory.createReserve();
  await Inventory.addToRelatedReserveRecords(reserve);
  return true;
}

/**
 * Send an adjustment (credit/denial) callback from admin activity
 * @param inventory
 * @param sendChargeback
 * @returns {Promise<void>}
 */
export async function sendAdjustmentCallback(inventory, sendChargeback = false) {
  const card = await Card.findById(inventory.card).populate('inventory');
  const log = await BiRequestLog.findOne({card: card._id});
  const url = await getCallbackUrl(inventory.company, log, inventory);
  const callback = new Callback(url);

  if (!sendChargeback && ['denial', 'credit'].includes(inventory.adjustmentStatus)) {
    return await callback.sendCallback(card, inventory.adjustmentStatus);
  }

  return await callback.sendCallback(card, 'chargeback');
}
