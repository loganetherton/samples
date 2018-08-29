import '../company/autoBuyRate.model';
import '../company/companySettings.model';
import '../log/logs.model';
import '../company/company.model';
import '../card/card.model';
import '../stores/store.model';
import '../reserve/reserve.model';

import BiRequestLog from '../biRequestLog/biRequestLog.model';
import Callback from './callback';
import CallbackLog from './callbackLog.model';
import Card from '../card/card.model';
import Inventory from '../inventory/inventory.model';
import Retailer from '../retailer/retailer.model';
import ErrorLog from '../errorLog/errorLog.model';
import Company from '../company/company.model';

/**
 * Get callbacks in a date range
 */
export async function getCallbacksInDateRange(req, res) {
  try {
    // No company, don't proceed
    if (!req.user.company) {
      return res.status(400).json({err: 'This user does not have a company associated with their account'});
    }
    const {begin, end} = req.params;
    const company = req.user.company.toString();
    const findParams = {company};
    if (begin && end) {
      findParams.begin = {$gt: new Date(begin)};
      findParams.end = {$lt: new Date(end)};
    }
    // Get all logs for this company
    const logs = await CallbackLog.find(findParams);
    return res.json(logs);
  } catch (err) {
    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'getCallbacksInDateRange',
      controller: 'callbackLog.controller',
      stack: err ? err.stack : null,
      error: err,

    });
  }
}

/**
 * Determine if a log has the incorrect card ID
 * @param log
 * @param card
 * @return {*|boolean}
 */
function incorrectCardId(log, card) {
  try {
    return log.card && log.card.toString() !== card._id.toString();
  } catch (err) {
    return false;
  }
}

/**
 * Resend callbacks for a specific card
 * @param res
 * @param card
 * @param callbackType
 * @param resend Resend a callback which has already been sent
 * @param force Force callback without needing values set
 * @return {Promise.<boolean>}
 */
export async function resendCallback(res, card, callbackType, resend = false, force = false) {
  try {
    const log = await BiRequestLog.findOne({$or: [{card: card._id}, {number: card.number, pin: card.pin, retailerId: card.retailer}]});
    let url;
    if (log) {
      url = await log.getCallbackUrl();
    }
    if (!url) {
      url = card.inventory.callbackUrl;
    }
    if (!url) {
      const company = await Company.findById(card.company);
      if (company) {
        const settings = await company.getSettings();
        if (settings && settings.callbackUrl) {
          url = settings.callbackUrl;
        }
      }
    }
    if (!url) {
      return false;
    }
    switch (callbackType) {
      case 'biComplete':
        if (log && (!log.card || incorrectCardId(log, card))) {
          log.card = card._id;
          await log.save();
        }
        if (!log) {
          if (res) {
            res.status(404).json({err: 'Unable to find BI log for the requested card'});
            return true;
          }
        }
        await (new Callback(url)).sendCallbackFromLog(log, resend);
        break;
      case 'cardFinalized':
        if (card.inventory && ['sentToSmp', 'receivedSmp', 'rejected'].indexOf(card.inventory.activityStatus) > -1 || force) {
          // console.log('**************CARD FINALIZED**********');
          // console.log(card.inventory._id);
          // console.log(card.inventory.transaction.callbacks.join(','));
          await (new Callback(url)).sendCallback(card, 'cardFinalized', null, resend);
        } else {
          if (res) {
            res.json({err: 'Card has not been finalized'});
            return true;
          }
        }
        break;
      case 'cqPaymentInitiated':
        if (card.inventory.cqAch || force) {
          // console.log('**************CQ PAYMENT INITIATED**********');
          // console.log(card.inventory._id);
          // console.log(card.inventory.transaction.callbacks.join(','));
          await (new Callback(url)).sendCallback(card, 'cqPaymentInitiated', null, resend);
        } else {
          if (res) {
            res.json({err: 'Card has not had payment initiated yet'});
            return true;
          }
        }
        break;
      case 'denial':
        // console.log('**************REJECTED**********');
        // console.log(card.inventory._id);
        // console.log(card.inventory.transaction.callbacks.join(','));
        await (new Callback(url)).sendCallback(card, 'denial', null, resend);
        break;
      case 'credit':
        if (card.inventory.adjustmentStatus === 'credited') {
          // console.log('**************CREDIT**********');
          // console.log(card.inventory._id);
          // console.log(card.inventory.transaction.callbacks.join(','));
          await (new Callback(url)).sendCallback(card, 'credit', null, resend);
        } else {
          if (res) {
            res.json({err: 'Card has not been credited'});
            return true;
          }
        }
        break;
      case 'biUnavailableCardAccepted':
        const retailer = Retailer.findById(card.retailer);
        if (retailer.gsId || retailer.aiId) {
          if (res) {
            res.json({err: 'BI is available for this card'});
            return true;
          }
        } else {
          await (new Callback(url)).sendCallback(card, 'biUnavailableCardAccepted', null, resend);
        }
        break;
      case 'needsAttention':
        await (new Callback(url)).sendCallback(card, 'needsAttention', null, resend);
        break;
    }
    return false;
  } catch (err) {
    console.log('**************ERR IN RESEND CALLBACK**********');
    console.log(err);
  }
}

/**
 * Fire a callback for a card whether it has been fired before or not
 */
export async function reFireCallback(req, res) {
  try {
    const {cardId, callbackType} = req.params;
    const card = await Card.findById(cardId).populate('inventory');
    // Send callbacks
    const noCallbackSent = await resendCallback(res, card, callbackType, true);
    // Callback cannot be sent, so an error has already been returned
    if (noCallbackSent) {
      return;
    }
    return res.json({});
  } catch (err) {
    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'reFireCallback',
      controller: 'callbackLog.controller',
      stack: err ? err.stack : null,
      error: err,

    });
  }
}

/**
 * Refire a callback from a list
 */
export async function refireCallbackFromList(req, res) {
  try {
    const {callbackType} = req.params;
    let cardIds;
    try {
      if (callbackType === 'cqPaymentInitiated') {
        cardIds = require('./cqPaymentInitiatedBadVb').cqPaymentInitiatedBadVb;
      } else if (callbackType === 'cardFinalized') {
        cardIds = require('./cardFinalizedBadVb').cardFinalizedBadVb;
      }
    } catch (err) {
      console.log('**************ERR**********');
      console.log(err);
      await ErrorLog.create({
        body: req.body ? req.body : {},
        params: req.params ? req.params : {},
        method: 'refireCallbackFromList',
        controller: 'callbackLog.controller',
        stack: err ? err.stack : null,
        error: err,

      });

      return res.status(500).json({err: 'Unable to include list of card IDs'});
    }
    const cards = await Card.find({_id: {
      $in: cardIds
    }}).populate('inventory');
    let counter = 1;
    for (const card of cards) {
      // Send callbacks
      (async function (multiplier) {
        setTimeout(async () => {
          if (card.inventory.isTransaction) {
            await resendCallback(null, card, callbackType, true);
          }
        }, 1000 * multiplier);
      })(counter);
      counter = counter + 1;
    }
    return res.json({});
  } catch (err) {
    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'refireCallbackFromList',
      controller: 'callbackLog.controller',
      stack: err ? err.stack : null,
      error: err,

    });
    return res.status(500).json({});
  }
}

/**
 * Fire all callbacks which should have been fired but which were not
 * @return {Promise.<void>}
 */
export async function fireAllCallbacks(req, res) {
  try {
    const {companyId} = req.params;
    const {resend = false, dateBegin = null, dateEnd = null} = req.body;
    let {callbacks = []} = req.body;
    // Only allow them to do this for their own company
    if (req.user.company.toString() !== companyId) {
      return res.status(401).send('Unauthorized');
    }
    const findParams = {company: companyId, cqAch: {$exists: true}, isTransaction: true};
    // If we're not resending, then only do it on new callbacks
    if (resend === false && callbacks.length === 0) {
      findParams['transaction.callbacks'] = {$size: 0};
      callbacks = ['cardFinalized', 'cqPaymentInitiated', 'denial', 'credit']
    }
    if (dateEnd && dateBegin) {
      findParams['created'] = {$gt: new Date(dateBegin), $lt: new Date(dateEnd)};
    } else if (dateEnd) {
      findParams['created'] = {$lt: new Date(dateEnd)};
    } else if (dateBegin) {
      findParams['created'] = {$gt: new Date(dateBegin)};
    }
    // Get inventories
    const inventories = await Inventory.find(findParams);
    // Card IDs for those inventories
    const cardIds = inventories.map(i => i.card.toString());
    // Get in correct format
    const cards = await Card.find({_id: {
      $in: cardIds
    }}).populate('inventory');
    let counter = 1;
    // Iterate cards
    for (const card of cards) {
      // Iterate desired callbacks
      for (const callback of callbacks) {
        // Wait a second between each callback
        (async function (multiplier) {
          setTimeout(async () => {
            if (card.inventory.isTransaction) {
              await resendCallback(null, card, callback, resend);
            }
          }, 1000 * multiplier);
        })(counter);
        counter = counter + 1;
      }
    }
    return res.json({});
  } catch (err) {
    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'fireAllCallbacks',
      controller: 'callbackLog.controller',
      stack: err ? err.stack : null,
      error: err,

    });
    return res.status(500).json({});
  }
}
