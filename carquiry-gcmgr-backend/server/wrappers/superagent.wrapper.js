import * as querystring from 'querystring';

export default class SuperAgentWrapper {
  /**
   * @param {Object} client The SuperAgent object
   */
  constructor(client) {
    this.client = client;
  }

  /**
   * Sends a GET request
   *
   * @param {String} url
   * @param {Object} params Query string to append to the URL
   * @param {Object} headers
   */
  async get(url, params = {}, headers = {}) {
    const queryString = params ? '?' + querystring.stringify(params) : '';
    const request = this.client.get(url + queryString);

    return await this.getResponse(request, headers);
  }

  /**
   * Sends a POST request
   *
   * @param {String} url
   * @param {Object} body
   * @param {Object} headers
   */
  async post(url, body = {}, headers = {}) {
    const request = this.client.post(url).send(body);

    return await this.getResponse(request, headers);
  }

  /**
   * Sends a PUT request
   *
   * @param {String} url
   * @param {Object} body
   * @param {Object} headers
   */
  async put(url, body = {}, headers = {}) {
    const request = this.client.put(url).send(body);

    return await this.getResponse(request, headers);
  }

  /**
   * Sends a PATCH request
   *
   * @param {String} url
   * @param {Object} body
   * @param {Object} headers
   */
  async patch(url, body = {}, headers = {}) {
    const request = this.client.patch(url).send(body);

    return await this.getResponse(request, headers);
  }

  /**
   * Sends a DELETE request
   *
   * @param {String} url
   * @param {Object} headers
   */
  async delete(url, headers = {}) {
    const request = this.client.delete(url);

    return await this.getResponse(url, headers);
  }

  /**
   * Set the specified headers on the request object
   *
   * @param {Object} request The HTTP request object
   * @param {Object} headers A key/value pair of headers to apply onto the request object
   */
  setRequestHeaders(request, headers) {
    for (const [key, value] of Object.entries(headers)) {
      request.set(key, value);
    }
  }

  /**
   * Sends the request and process the response
   *
   * @param {Object} request HTTP request object
   * @param {Object} headers
   * @return {Object}
   */
  async getResponse(request, headers = {}) {
    const processedResponse = {};
    let response;

    try {
      this.setRequestHeaders(request, headers);

      processedResponse.requestSentAt = (new Date()).toISOString();

      response = await request.catch(err => {
        Object.assign(processedResponse, {
          status: err.statusCode,
          text: err.rawResponse
        });
      });

      if (response) {
        Object.assign(processedResponse, {
          status: response.status,
          body: response.body,
          text: response.text
        });
      }
    } catch (e) {
      Object.assign(processedResponse, {
        stack: e.stack
      });
    } finally {
      processedResponse.responseReceivedAt = (new Date()).toISOString();

      return processedResponse;
    }
  }
}
