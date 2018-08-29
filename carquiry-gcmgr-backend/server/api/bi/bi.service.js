import * as moment from 'moment';

export default class BiService {
  /**
   * @param {Object} client HTTP client wrapped by a generic interface
   * @param {Object} config
   * @param {String} config.hostKey API header key
   * @param {String} config.baseUrl API base URL
   */
  constructor(client, config) {
    this.client = client;
    this.hostKey = config.hostKey;
    this.baseUrl = config.baseUrl;
  }

  /**
   * Set a logger object to log API calls. Must implement a "log" function.
   *
   * @param {Object} logger
   * @param {Function} logger.log
   * @return {this}
   */
  setLogger(logger) {
    this.logger = logger;
    return this;
  }

  /**
   * Retrieve the pending BI cards
   *
   * @return {Object}
   */
  async getPendingCards(row, direction, dateBegin, dateEnd) {
    const response = await this.sendRequest(`/api/pending/${row}/${direction}/${dateBegin}/${dateEnd}`, 'get', null, false);
    return response.body;
  }

  /**
   * Retrieve a request ID based on attributes
   * @param number
   * @param pin
   * @param retailerId
   * @return {Promise.<void>}
   */
  async getRequestIdByAttributes(number, pin, retailerId) {
    const response = await this.sendRequest(`/api/get_request_id/${number}/${pin}/${retailerId}`, 'get', null, false);
    return response.body;
  }

  /**
   * Update a cards balance
   *
   * @return {Object}
   */
  async setBalance(params) {
    const response = await this.sendRequest('/api/complete', 'post', params, false);
    return response.body;
  }

  /**
   * Get headers for gcmgr
   *
   * @param vista Is a Vista card
   */
  getBiReceiverHeaders(vista) {
    const userId = 'cGaWqPc7ut';
    return {
      'content-type': "application/x-www-form-urlencoded",
      'date': moment().format('ddd, DD MMM YYYY HH:mm:ss +0000'),
      'authorization': `a ${userId}:gcmgr_sig`
    };
  }

  /**
   * Get headers for BI receiver JSON endpoints
   */
  getBiReceiverJsonHeaders() {
    const userId = 'cGaWqPc7ut';
    return {
      "Content-Type": "application/json",
      "Host-Key": "***",
      "Date": "Wed, 31 May 2017 22:58:43 +0000",
      "Authorization": `a ${userId}:gcmgr_sig`,
      "Cache-Control": "no-cache",
      "Postman-Token": "209f0f3b-1041-6cab-4266-39ff62ff90bd"
    };
  }

  /**
   * Retrieve a record from BI
   * @param requestId
   * @return {Promise.<void>}
   */
  async getRecord(requestId) {
    const headers = this.getBiReceiverHeaders();
    const response = await this.sendRequest(`/api/giftcardbalance`, 'get', {cardNumber: requestId}, false, headers);
    return JSON.parse(response.text);
  }

  /**
   * Insert a record into BI
   * @param params
   * @param vista Is a Vista card
   * @return {Promise.<void>}
   */
  async insert(params, vista = false) {
    const headers = this.getBiReceiverHeaders(vista);
    const response = await this.sendRequest(`/api/giftcardbalance`, 'get', params, false, headers);
    return JSON.parse(response.text);
  }

  /**
   * @params options
   * @returns {Promise<void>}
   */
  async getCardsWithIncorrectRetailer(options) {
    const headers = this.getBiReceiverJsonHeaders();
    const response = await this.sendRequest('/api/incorrect_retailer_cards', 'post', options, false, headers);
    return JSON.parse(response.text);
  }

  /**
   * Retrieve pending BI cards
   *
   * @param path {String} API path
   * @param method {String} Method
   * @param params {Object} Request body
   * @param doLog {Boolean} Create a log
   * @param headers {Object} Additional headers
   *
   * @return {Object}
   */
  async sendRequest(path, method = 'get', params, doLog = true, headers = {'Host-Key': this.hostKey}) {
    const response = await this.client[method](this.baseUrl + path, params, headers);

    if (doLog) {
      this.logger.log(Object.assign({}, response, {url: this.baseUrl + path}));
    }

    return response;
  }

  /**
   * Check to see if the given retailer ID belongs to an exchange card retailer
   *
   * @param {String} retailerId
   * @return {Boolean}
   */
  isAnExchangeCardRetailer(retailerId) {
    return this.retailers.indexOf(retailerId) !== -1;
  }
}
