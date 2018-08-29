import {expect} from 'chai';
import * as moment from 'moment';

import Card from '../api/card/card.model';
import config from '../config/environment';
import Retailer from '../api/retailer/retailer.model';

export default class Requests {
  /**
   * Login as a user of any type
   * @param type Type of user
   * @param setNumber Which set of data we're referring to
   */
  async loginUserSaveToken(type, setNumber = 1) {
    return await this.request
    .post('/api/auth/local')
    .send({
      email   : this.credentials[`${type}${setNumber}`].email,
      password: this.credentials[`${type}${setNumber}`].password
    })
    .then(res => {
      expect(res).to.have.status(200);
      expect(res.body.token).to.not.be.empty;
      this.tokens[`${type}${setNumber}`].token = res.body.token;
      this.tokens[`${type}${setNumber}`]._id   = res.body.user._id.toString();
    })
    .catch(() => {
      expect(false).to.be.equal(true)
    });
  }

  /**
   * Create a card from UI interaction
   * @param setNumber
   * @return {Promise.<void>}
   */
  async createCardFromUi(setNumber = 1) {
    const retailerId = this.getDefaultReferenceId('retailers', setNumber);
    const customerId = this.getDefaultReferenceId('customers', setNumber);
    const storeId    = this.getDefaultReferenceId('stores', setNumber);
    this.cardNumber  = this.cardNumber + 1;
    const balance    = 50 * setNumber;
    const tokenType  = `employee${setNumber}`;
    const params     = {
      "retailer": retailerId,
      "number"  : this.cardNumber,
      "pin"     : this.cardNumber,
      "customer": customerId,
      "store"   : storeId,
      "userTime": new Date(),
      "balance" : balance
    };
    await this.request
    .post('/api/card/newCard')
    .set('Authorization', `bearer ${this.tokens[tokenType].token}`)
    .send(params)
    .then(async res => {
      expect(res).to.have.status(200);
      return res;
    });
  }

  /**
   * Create a card from lq/new
   * @param setNumber
   * @return {Promise.<TResult>}
   */
  async createCardFromLqNew(setNumber = 1) {
    this.cardNumber = this.cardNumber + 1;
    const balance   = 50 * setNumber;
    const tokenType = `employee${setNumber}`;
    const params    = {
      number  : this.cardNumber,
      pin     : this.cardNumber,
      retailer: this.getDefaultReferenceId('retailers', setNumber),
      customer: this.getDefaultReferenceId('customers', setNumber),
      store: this.getDefaultReferenceId('stores', setNumber),
      userTime: moment().format(),
      balance : balance
    };

    return await this.request
    .post('/api/lq/new')
    .set('Authorization', `bearer ${this.tokens[tokenType].token}`)
    .send(params)
    .then(async res => {
      expect(res).to.have.status(200);
      return res;
    });
  }

  /**
   * Create a card from a transaction
   * @param params Additional params
   * @param setNumber
   * @return {Promise.<TResult>}
   */
  async createCardFromTransaction(params, setNumber = 1) {
    this.cardNumber = this.cardNumber + 1;
    const balance   = 50 * setNumber;
    const tokenType = `employee${setNumber}`;
    params    = Object.assign({
      number            : this.cardNumber,
      pin               : this.cardNumber,
      retailer          : this.getDefaultReferenceId('retailers', setNumber),
      "userTime"        : moment().format(),
      "balance"         : balance,
      "memo"            : `memo${setNumber}`,
      "merchandise"     : false,
      "transactionTotal": 50,
      "transactionId"   : 12345,
      "customerId"      : this.getDefaultReferenceId('customers', setNumber),
      "storeId"         : this.getDefaultReferenceId('stores', setNumber),
      "prefix"          : `prefix${setNumber}`,
      "vmMemo1"         : "a",
      "vmMemo2"         : "b",
      "vmMemo3"         : "c",
      "vmMemo4"         : "d"
    }, params);

    return await this.request
    .post('/api/lq/transactions')
    .set('Authorization', `bearer ${this.tokens[tokenType].token}`)
    .send(params)
    .then(async res => {
      expect(res).to.have.status(200);
      return res;
    });
  }

  /**
   * Add card to inventory from UI
   * @param setNumber
   * @return {Promise.<TResult>}
   */
  async addCardsToInventory(setNumber = 1) {
    const tokenType   = `employee${setNumber}`;
    const cards       = await Card.find({user: this.tokens[tokenType]._id});
    const requestBody = {
      "cards"          : cards,
      "userTime"       : new Date(),
      "receipt"        : false,
      "modifiedDenials": 0,
    };
    return await this.request
    .post('/api/card/addToInventory')
    .set('Authorization', `bearer ${this.tokens[tokenType].token}`)
    .send(requestBody)
    .then(async res => {
      expect(res).to.have.status(200);
      return res;
    });
  }

  /**
   * Request /lq/new
   * @param params Request body
   * @param userType Type of user making request
   * @param setNumber Set of cards, users, etc
   * @return {Promise.<*>}
   */
  async lqNew(params, userType = 'corporateAdmin', setNumber = 1) {
    if (!params) {
      this.cardNumber = this.cardNumber + 1;
      params          = {
        number     : this.cardNumber,
        pin        : this.cardNumber,
        retailer   : this.getDefaultReferenceId('retailers', setNumber),
        userTime   : moment().format(),
        balance    : 40,
        merchandise: false
      };
    }
    return await this.request
    .post('/api/lq/new')
    .set('Authorization', `bearer ${this.tokens[`${userType}${setNumber}`].token}`)
    .send(params);
  }

  /**
   * Request /lq/transactions
   * @param params Request body
   * @param userType Type of user making request
   * @param setNumber Set of cards, users, etc
   * @return {Promise.<*>}
   */
  async lqTransactions(params = {}, userType = 'corporateAdmin', setNumber = 1) {
    this.cardNumber = this.cardNumber + 1;
    const balance   = 50 * setNumber;
    if (params === null) {
      params = {};
    } else {
      params = Object.assign({
        number          : this.cardNumber,
        pin             : this.cardNumber,
        retailer        : this.getDefaultReferenceId('retailers', setNumber),
        userTime        : new Date(),
        balance         : balance,
        memo            : "Match example",
        merchandise     : false,
        transactionTotal: 50,
        transactionId   : 12345,
        customerId      : this.getDefaultReferenceId('customers', setNumber),
        storeId         : this.getDefaultReferenceId('stores', setNumber),
        prefix          : "xyz",
        vmMemo1         : "a",
        vmMemo2         : "b",
        vmMemo3         : "c",
        vmMemo4         : "d"
      }, params);
    }

    return await this.request
    .post('/api/lq/transactions')
    .set('Authorization', `bearer ${this.tokens[`${userType}${setNumber}`].token}`)
    .send(params);
  }

  /**
   * Create random number for BI requests
   * @param length
   * @returns {string}
   */
  createRandomNumber(length = 10) {
    const random = Math.random().toString();
    return random.substring(2, length + 2);
  }

  /**
   * Create a BI request log
   * @param params
   * @param merge
   * @param userIndex
   * @param retailerIndex Index of retailer to use
   * @returns {Promise<*>}
   */
  async createBiLog(params = {}, merge = true, userIndex = 1, retailerIndex = 1) {
    let biLogParams;
    if (merge) {
      biLogParams = Object.assign({
        number   : this.createRandomNumber(),
        pin      : this.createRandomNumber(4),
        retailer : this.getDefaultReferenceId('retailers', retailerIndex)
      }, params);
    } else {
      biLogParams = params;
    }
    const employee = `employee${userIndex}`;
    return await this.request
    .post('/api/lq/bi')
    .set('Authorization', `bearer ${this.tokens[employee].token}`)
    .send(biLogParams);
  }

  /**
   * Send callbacks for transaction
   * @param callbackType Callback type
   * @param inventories Inventories to send callbacks for
   * @return {Promise.<*>}
   */
  async sendTransactionCallback(callbackType, inventories) {
    return this.request.put(`/api/admin/callbacks/${callbackType}`)
    .set('Authorization', `bearer ${this.tokens.admin1.token}`)
    .send({
      inventories,
      type : callbackType,
      force: true
    });
  }

  /**
   * Complete a BI log
   * @param params
   * @param requestId BI request ID
   * @param {Boolean} mergeParams Use complete params from argument
   * @return {Promise.<void>}
   */
  async completeBiLog(params = {}, requestId = '1', mergeParams = true) {
    if (params === null) {
      params = {};
    } else if (mergeParams) {
      params            = Object.assign({
        "number"    : 1,
        "pin"       : 1,
        "retailerId": this.getDefaultReferenceId('retailers'),
        "invalid"   : 0,
        "balance"   : 100,
        "fixed"     : 0
      }, params);
    }
    const retailer = await Retailer.findById(params.retailerId);
    if (retailer) {
      // Set the BI value for retailer
      params.retailerId = retailer.gsId || retailer.aiId;
    }
    return this.request.post(`/api/lq/bi/${requestId}`)
    .set(config.biCallbackKeyHeader, config.biCallbackKey)
    .send(params);
  }

  /**
   * Update inventory details
   * @param {Array} inventories Selected inventories
   * @param {Object} params
   */
  async updateInventoryDetails(inventories, params = {}) {
    return this.request.post(`/api/card/updateDetails`)
    .set('Authorization', `bearer ${this.tokens.admin1.token}`)
    .send(Object.assign({
      ids: inventories
    }, params));
  }

  /**
   * Reject card
   */
  async rejectCard(inventories, action = 'adjustment') {
    return this.request.post(`/api/card/adjustments`)
    .set('Authorization', `bearer ${this.tokens.admin1.token}`)
    .send({
      inventories: inventories,
      action
    });
  }

  /**
   * Reconcile cards
   * @param setNumber
   * @return {Promise.<void>}
   */
  async reconcile(setNumber = 1) {
    const company = this.getDefaultReferenceId('companies', setNumber);
    const store = this.getDefaultReferenceId('stores', setNumber);
    return this.request.post(`/api/companies/${company}/store/${store}/reconcile`)
    .set('Authorization', `bearer ${this.tokens[`employee${setNumber}`].token}`)
    .send({
      userTime: moment().format()
    });
  }

  /**
   * Mark cards as reconciled
   * @param setNumber
   * @return {Promise.<void>}
   */
  async markAsReconciled(setNumber = 1) {
    const company = this.getDefaultReferenceId('companies', setNumber);
    const store = this.getDefaultReferenceId('stores', setNumber);
    return this.request.post(`/api/companies/${company}/store/${store}/markAsReconciled`)
    .set('Authorization', `bearer ${this.tokens[`employee${setNumber}`].token}`)
    .send({
      userTime: moment().format()
    });
  }

  /**
   * Get card inventories that are yet to be reconciled
   *
   * @param {Number} customerNumber
   * @param {Number} employeeNumber
   * @return {Promise}
   */
  async getExistingCardsReceipt(customerNumber = 1, employeeNumber = 1) {
    const customer = this.getDefaultReferenceId('customers', customerNumber);
    return this.request.get(`/api/card/${customer}/receipt`)
    .set('Authorization', `bearer ${this.tokens[`employee${employeeNumber}`].token}`)
    .send({
      userTime: moment().format()
    });
  }

  /**
   * Change the liqudation status on an inventory
   *
   * @param {String} inventoryId
   * @param {String} liquidationStatus
   * @param {Number} setNumber
   * @return {Promise}
   */
  async modifyInventory(inventoryId, liquidationStatus, setNumber = 1) {
    return this.request.post('/api/card/modify')
    .set('Authorization', `bearer ${this.tokens[`admin${setNumber}`].token}`)
    .send({
      inventory: {_id: inventoryId},
      value: liquidationStatus
    });
  }
}
