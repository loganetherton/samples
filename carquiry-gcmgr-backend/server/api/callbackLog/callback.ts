import * as superagent from 'superagent';

import '../company/autoBuyRate.model';
import {ICompanySettings} from '../company/companySettings.model';
import '../log/logs.model';
import Company from '../company/company.model';
import Card, {ICard} from '../card/card.model';
import '../stores/store.model';
import '../reserve/reserve.model';
import User, {IUser} from '../user/user.model';
import CallbackLog from './callbackLog.model';
import ErrorLog from '../errorLog/errorLog.model';
import {calculateValues} from '../company/company.controller';
import {callbackStatusEnabled} from '../lq/lq.controller';

import {recalculateTransactionAndReserve} from '../card/card.helpers';
import config from '../../config/environment';
import {IBiRequestLog} from "../biRequestLog/biRequestLog.model";
import {ICardCallbackParams, ILogCallbackParams} from "./callback.interfaces";

export default class Callback {
  callbackUrl: string = null;
  token: string = null;
  companySettings: ICompanySettings = null;

  constructor(callbackUrl = null) {
    this.callbackUrl = callbackUrl;
    this.token = null;
    this.companySettings = null;
  }

  // Make a callback as part of a transaction
  /*
   {
   id: string <card ID>,
   number: string <last 4 digits of card>,
   claimedBalance: float <balance claimed by user>,
   verifiedBalance: float <balance verified by BI>,
   cqPaid: float <the amount CQ is paying before fees>,
   netPayout: float <the amount CQ is paying after fees>,
   prefix: string <card prefix>,
   cqAch: string<the CQ number in our payment to you>,
   finalized: boolean <whether the sale is finalized>,
   callbackType: string <type of callback>
   }
   */
  async makeCallbackFromCard(card, callbackUrl, callbackType, finalized, sendFullCardNumber = false, callbackStatus = null, sendCurrency = false): Promise<void> {
    let verifiedBalance = card.inventory.verifiedBalance;
    const cardNumber = sendFullCardNumber ? card.number : card.getLast4Digits();
    let data: ICardCallbackParams = {
      id: card._id,
      number: cardNumber,
      claimedBalance: card.balance,
      verifiedBalance,
      cqPaid: card.inventory.transaction.cqPaid,
      netPayout: card.inventory.transaction.netPayout,
      prefix: card.inventory.transaction.prefix,
      cqAch: card.inventory.cqAch ? card.inventory.cqAch : null,
      finalized,
      callbackType
    };
    // Add lqCustomerName if one is supplied
    if (card.lqCustomerName) {
      data.lqCustomerName = card.lqCustomerName;
    }
    if (sendFullCardNumber) {
      const inventory = card.inventory;
      data.cqPaid = inventory.cqPaid;
      data.netAmount = typeof inventory.netAmount === 'number' ? parseFloat(inventory.netAmount.toFixed(2)) : null;
      data.serviceFee = typeof inventory.serviceFee === 'number' ? parseFloat(inventory.serviceFee.toFixed(2)) : null;
      // biComplete status
      if (callbackStatus !== null) {
        data.status = config.callbackStatus[callbackStatus];
        data.statusCode = callbackStatus;
      }
      delete data.netPayout;
      delete data.prefix;
    }

    if (sendCurrency) {
      data.currencyCode = card.inventory.currencyCode;
    }

    if (callbackType === 'cardFinalized' || callbackType === 'cqPaymentInitiated') {
      if (verifiedBalance === null || typeof verifiedBalance === 'undefined') {
        data.verifiedBalance = card.inventory.balance;
      }
    } else if (callbackType === 'needsAttention') {
      data.note = card.inventory.adminActivityNote
    }

    // Callback token
    if (this.token) {
      data.token = this.token;
    }

    if (config.debug) {
      console.log('**************CALLBACK DATA FROM TRANSACTION**********');
      console.log(data);
    }

    // Save initial log entry
    let logEntry = new CallbackLog({
      callbackType,
      number: cardNumber,
      pin: card.pin,
      claimedBalance: card.balance,
      verifiedBalance,
      cqPaid: card.inventory.transaction.cqPaid,
      netPayout: card.inventory.transaction.netPayout,
      prefix: card.inventory.transaction.prefix,
      cqAch: card.inventory.cqAch,
      finalized,
      success: false,
      url: callbackUrl,
      card: card._id,
      company: card.inventory.company,
      token: this.token
    });

    logEntry.body = data;

    try {
      await logEntry.save();
    } catch (err) {
      console.log('**************FAILED TO SAVE CALLBACK LOG**********');
      console.log(err);
    }

    // Don't send from development
    if (config.env === 'development' || config.env === 'test' || config.noCallbacks) {
      return;
    }

    superagent.post(callbackUrl).send(data).end(async function (err, res) {
      if (!err) {
        if (config.debug) {
          console.log('Sent '+JSON.stringify(data)+' to '+callbackUrl);
        }
      } else {
        if (config.debug) {
          console.log('*************ERROR SENDING CALLBACK*************');
          console.log(err);
        }
      }
      let success = false;
      let text = '';
      let statusCode = 404;
      if (res) {
        success = res.status ? res.status < 300 : false;
        text = res.text ? res.text : '';
        statusCode = res.status;
      }

      logEntry.failResponse = success ? '' : text;
      logEntry.statusCode = statusCode;
      logEntry.success = success;
      logEntry.finalized = finalized;
      await logEntry.save();
    });
  }

  // Make a callback directly from a /bi requests
  /*
   {
   number: string <last 4 digits of card>,
   verifiedBalance: number <balance from BI>,
   pin: string <card pin>,
   callbackType: "balanceCB",
   prefix: string <card prefix>
   }
   */
  async makeCallbackFromLog(log: IBiRequestLog, callbackType: string, sendFullCardNumber: boolean,
                            status: number = null, sendCurrency: boolean = false): Promise<void> {
    let inventory = null;
    let data: ILogCallbackParams = {
      number: sendFullCardNumber ? log.number : log.getLast4Digits(),
      verifiedBalance: log.balance,
      pin: log.pin,
      callbackType
    };
    // Add lqCustomerName if requested
    if (log.lqCustomerName) {
      data.lqCustomerName = log.lqCustomerName;
    }
    data.retailer = log.retailerId.toString();
    if (sendFullCardNumber) {
      // Send a status code with the callback
      if (status !== null) {
        data.status = config.callbackStatus[status];
        data.statusCode = status;
      }
    }

    if (sendCurrency) {
      data.currencyCode = log.currencyCode;
    }

    // BiLog callback
    if (log.prefix) {
      data.prefix = log.prefix
    // BiUnavailable callback
    } else if (inventory && inventory.isTransaction && inventory.transaction.prefix) {
      data.prefix = inventory.transaction.prefix;
    }

    // Callback token
    if (this.token) {
      data.token = this.token;
    }

    if (config.debug) {
      console.log('**************CALLBACK DATA FROM LOG**********');
      console.log(data);
    }

    const callbackUrl = this.callbackUrl;

    const logData = Object.assign({}, data, {
      success: false,
      url: callbackUrl,
      finalized: false,
      token: this.token
    });

    // Raw body
    logData.body = data;

    // Sanity check
    logData.biRequestLog = log._id;

    let logEntry = new CallbackLog(logData);
    logEntry = await logEntry.save();


    // Don't send from development
    if (config.env === 'development' || config.env === 'test' || config.noCallbacks) {
      return;
    }

    superagent.post(callbackUrl).send(data).end(async function (err, res) {
      if (!err) {
        console.log('Sent '+JSON.stringify(data)+' to '+callbackUrl);
      } else {
        console.log('*************ERROR SENDING CALLBACK*************');
        console.log(err);
      }

      let success = false;
      let text = '';
      let statusCode = 404;
      if (res) {
        success = res.status ? res.status < 300 : false;
        text = res.text ? res.text : '';
        statusCode = res.status;
      }
      // Update log with result
      logEntry.success = success;
      logEntry.failResponse = success ? '' : text;
      logEntry.statusCode = statusCode;
      logEntry.finalized = log.finalized;

      await logEntry.save();
    });
  }

  /**
   * Get user from card
   * @param {} card
   * @returns {boolean}
   */
  getUserIdFromCard(card: ICard): string {
    let user = null;
    if (card) {
      user = card.user[0];
    }
    // Make sure we have a string to return
    if (user) {
      // Object ID
      if (user.constructor.name === 'ObjectID') {
        user = user.toString();
      // Model
      } else if (user.constructor.name === 'model') {
        user = user._id.toString();
      }
    }
    return user;
  }

  /**
   * Determine if callback has already been sent
   * @param {string} cardNumber
   * @param {string} pin
   * @param {string} callbackType
   * @param {number} balance
   * @returns {Promise<boolean>}
   */
  async callbackAlreadySent(cardNumber: string, pin: string, callbackType: string, balance: number): Promise<boolean> {
    const previousCallback = await CallbackLog.find({number: cardNumber, pin, callbackType}).sort({created: -1}).limit(1);
    let callbackToUse = null;
    if (previousCallback.length) {
      callbackToUse = previousCallback[0];
    }
    // No callback
    if (!callbackToUse || typeof callbackToUse.verifiedBalance !== 'number') {
      return false;
    }
    const callbackBalance = typeof callbackToUse.verifiedBalance === 'number' ? callbackToUse.verifiedBalance : callbackToUse.balance;
    // Already send this callback, return
    return callbackToUse && callbackBalance === balance;
  }

  /**
   * Get callback status, if needed
   * @param {boolean} sendStatusAndFullNumber Status code
   * @param {IUser} user
   * @param {ICard} card
   * @param {string} callbackType
   * @returns {number}
   */
  getCallbackStatus(sendStatusAndFullNumber: boolean, user: IUser, card: ICard, callbackType: string): number {
    let status: number = null;
    // Determine callback status if necessary
    if (sendStatusAndFullNumber && ['biComplete', 'balanceCB'].includes(callbackType)) {
      if (card && card.inventory) {
        status = config.callbackStatusCodes.success;
      } else {
        status = config.callbackStatusCodes.violateSellLimits;
      }
    }
    return status;
  }

  /**
   * Send a callback directly from a BiRequestLog without a card necessarily attached
   * @param {IBiRequestLog} log
   * @param {boolean} resend
   * @returns {Promise<undefined>}
   */
  async sendCallbackFromLog(log: IBiRequestLog, resend: boolean): Promise<void> {
    let user: IUser = null;
    // Missing the user for some reason
    if (!log.user) {
      return;
    }
    // Get user
    if (log.user.constructor.name === 'model') {
      user = await User.findById(log.user._id);
    } else {
      user = await User.findById(log.user);
    }
    if (!user) {
      return;
    }
    // Determine callbackType
    const company = await Company.findById(user.company);
    const settings = await company.getSettings();
    const callbackType = settings.useBalanceCB ? 'balanceCB' : 'biComplete';
    // Send full number
    const sendStatusAndFullNumber = await callbackStatusEnabled(user.company);
    let card: ICard = null;
    if (log.card) {
      card = await Card.findById(log.card);
    }
    // Try to determine callbackStatus if one isn't defined, for a card-related callback
    const status = this.getCallbackStatus(sendStatusAndFullNumber, user, card, callbackType);
    let cardNumber: string = log.number;
    if (card) {
      cardNumber = sendStatusAndFullNumber ? card.number : card.getLast4Digits();
    }
    // Don't send duplicate callbacks
    if (!resend && await this.callbackAlreadySent(cardNumber, log.pin, callbackType, log.balance)) {
      return;
    }
    let sendCurrency = false;
    if (user) {
      const company = await Company.findById(user.company);
      this.companySettings = await company.getSettings();
      sendCurrency = this.companySettings.enableCurrencyCode || false;
      if (this.companySettings.callbackTokenEnabled) {
        this.token = this.companySettings.callbackToken;
      }
    }
    // BI callbacks
    return this.makeCallbackFromLog(log, callbackType, sendStatusAndFullNumber, status, sendCurrency);
  }

  /**
   * Callback from a card, not a log
   *
   * @param {Object} card
   * @param {String} callbackType One of "balanceCB", "biComplete", "cardFinalized", "cqPaymentInitiated"
   * @param {String} callbackUrl Send a callback directly to this URL
   * @param {Boolean} resend Resend a callback which has already potentially been sent
   * @param {Boolean} sendFullCardNumber Posting solutions receives full card info
   * @param {Number} callbackStatus Invalid status
   */
  async sendCallback(card: ICard, callbackType: string, callbackUrl: string = null, resend: boolean = false,
                     sendFullCardNumber: boolean = false, callbackStatus: number = null): Promise<void> {
    try {
      const userId = this.getUserIdFromCard(card);
      const user = await User.findById(userId);
      const cardModel = await Card.findById(card._id).populate('inventory');
      const balance = cardModel.inventory && cardModel.inventory.verifiedBalance ? cardModel.inventory.verifiedBalance : cardModel.balance;
      const cardNumber = sendFullCardNumber ? cardModel.number : cardModel.getLast4Digits();
      // Don't send duplicate callbacks
      if (!resend && await this.callbackAlreadySent(cardNumber, card.pin, callbackType, balance)) {
        return;
      }
      // Get company settings for token
      let sendCurrency = false;
      if (user) {
        const company = await Company.findById(user.company);
        this.companySettings = await company.getSettings();
        sendCurrency = this.companySettings.enableCurrencyCode || false;
        if (this.companySettings.callbackTokenEnabled) {
          this.token = this.companySettings.callbackToken;
        }
      }
      callbackUrl = this.callbackUrl;
      if (card.constructor.name !== 'model' || !(card.inventory && card.inventory._id)) {
        card = await Card.findOne({_id: card._id}).populate('inventory');
      }
      if (!card) {
        return;
      }
      if (card.inventory) {
        const finalized = ['receivedSmp', 'sendToSmp', 'rejected'].includes(card.inventory.activityStatus) || !!card.inventory.cqAch;
        // Vista
        if (card.inventory.isTransaction) {
          await this.sendTransactionCallback(resend, card, callbackType, callbackUrl, finalized, sendCurrency);
        // non-transaction callback
        } else if (card.inventory.company) {
          if (!callbackUrl) {
            const company = await Company.findById(card.inventory.company);
            this.companySettings = await company.getSettings();
            callbackUrl = card.inventory.callbackUrl || this.companySettings.callbackUrl;
          }
          if (callbackUrl) {
            await this.makeCallbackFromCard(card, callbackUrl, callbackType, finalized || false, true, callbackStatus, sendCurrency);
          }
        }
      }
    } catch (err) {
      await ErrorLog.create({
        method: 'refireCallbackFromList',
        controller: 'callbackLog.controller',
        stack: err ? err.stack : null,
        error: err
      });
    }
  }

  async sendTransactionCallback(resend: boolean, card: ICard, callbackType: string, callbackUrl: string,
                                finalized: boolean, sendCurrency: boolean): Promise<void> {
    // Don't send the callback again unless we're purposely resending
    if (resend || !card.inventory.transaction.callbacks.includes(callbackType)) {
      // Update inventory with this type of callback
      if (card.inventory.verifiedBalance) {
        if (!resend && await this.callbackAlreadySent(card.number, card.pin, callbackType, card.inventory.verifiedBalance)) {
          return;
        }
      }
      if (!callbackUrl) {
        callbackUrl = this.callbackUrl ? this.callbackUrl : await card.inventory.getCallbackUrl();
      }
      if (callbackUrl) {
        if (finalized) {
          // Recalculate card to see if anything has changes
          await recalculateTransactionAndReserve(card.inventory);
          card = await Card.findById(card._id).populate('inventory');
        }
        await this.makeCallbackFromCard(card, callbackUrl, callbackType, finalized, false, null, sendCurrency);
      }
    }
  }
}
