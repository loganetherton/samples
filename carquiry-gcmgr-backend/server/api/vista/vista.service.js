import * as querystring from 'querystring';

export default class VistaService {
  /**
   * @param {Object} client HTTP client wrapped by a generic interface
   * @param {Object} config
   * @param {String} config.username API username
   * @param {String} config.password API password
   * @param {String} config.baseUrl API base URL
   * @param {String} config.generateUrl Endpoint to generate cards
   * @param {String} config.transactionUrl Endpoint for transactions
   * @param {String[]} config.exchangeCardRetailerIds An array of exchange card retailer IDs
   */
  constructor(client, config) {
    this.client = client;
    this.username = config.username;
    this.password = config.password;
    this.baseUrl = config.baseUrl;
    this.generateUrl = config.generateUrl;
    this.transactionUrl = config.transactionUrl;
    this.retailers = config.exchangeCardRetailerIds;
  }

  /**
   * Deducts balance from an exchange card
   *
   * @param {String} id Exchange card ID
   * @param {Number} amount The amount of balance to deduct from the card
   * @return {Object}
   */
  async deductBalance(id, amount) {
    const response = await this.sendRequest({action: 'decrease', pin: id, amount});

    return this.convertApiResponseToObject(response);
  }

  /**
   * Checks the balance of an exchange card
   *
   * @param {String} id Exchange card ID
   * @return {Object}
   */
  async checkBalance(id) {
    const response = await this.sendRequest({action: 'inquire', pin: id});

    return this.convertApiResponseToObject(response);
  }

  /**
   * Increases the balance of an exchange card
   *
   * @param {String} id Exchange card ID
   * @param {Number} amount
   * @return {Object}
   */
  async topUpBalance(id, amount) {
    const response = await this.sendRequest({action: 'increase', pin: id, amount});

    return this.convertApiResponseToObject(response);
  }

  /**
   * Injects the API username & password into the request body (or query string I guess?)
   *
   * @param {Object} data
   * @return {Object}
   */
  injectCredentials(data) {
    return Object.assign({}, data, {username: this.username, password: this.password});
  }

  /**
   * Determine the endpoint to query
   * @param action
   * @return {*}
   */
  getEndpoint(action) {
    return action === 'generate' ? this.generateUrl : this.transactionUrl;
  }

  /**
   * Sends a request to the API. Only accepts a query string as a param and will
   * always send a GET request because their API doesn't make sense.
   *
   * @param {Object} params An object representing the query string to send
   * @return {Object}
   */
  async sendRequest(params = {}) {
    const requestParams = this.injectCredentials(params);
    console.log('**************PARAMS**********');
    console.log(params);
    console.log('**************REQUEST PARAMS**********');
    console.log(requestParams);
    const endpoint = this.getEndpoint(params.action);
    console.log('**************URL**********');
    console.log(this.baseUrl + endpoint);
    const response = await this.client.get(this.baseUrl + endpoint, requestParams);

    this.logger.log(Object.assign({}, response, {method: 'sendRequest', requestBody: requestParams, url: this.baseUrl + endpoint}));

    return response;
  }

  /**
   * Converts the response received from the Vista API into an object
   *
   * @param {Object} response The processed response object from the HTTP client
   * @return {Object}
   */
  convertApiResponseToObject(response) {
    if (response.status === 200 && response.text.length) {
      // We're calling Object.assign because querystring.parse only returns a pseudo-object
      // that doesn't have the standard Object methods such as toString(), hasOwnProperty(), and the likes
      return Object.assign({}, querystring.parse(response.text));
    }

    return {};
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
   * Check to see if the given retailer ID belongs to an exchange card retailer
   *
   * @param {String} retailerId
   * @return {Boolean}
   */
  isAnExchangeCardRetailer(retailerId) {
    return this.retailers.indexOf(retailerId) !== -1;
  }
}
