import {expect} from 'chai';
import * as moment from 'moment';
import * as _ from 'lodash';

import TestHelper from '../../tests/helpers';
import BiRequestLog from '../biRequestLog/biRequestLog.model';
import CallbackLog from '../callbackLog/callbackLog.model';
import Card from '../card/card.model';
import Customer from '../customer/customer.model';
import User from '../user/user.model';
import Retailer from '../retailer/retailer.model';
import Inventory from '../inventory/inventory.model';
import Receipt from '../receipt/receipt.model';
import Company from '../company/company.model';
import Store from '../stores/store.model';

import config from '../../config/environment';

const test = new TestHelper();

describe('lq.controller.js', function () {
  test.initDb();
  this.timeout(3500);

  before(async function () {
    // Create users
    await test.createAdminUser();
    await test.createCompanyAndCorporateAdminUser(1, {name: 'Posting Solution'}, {}, {
      disableLimits: true,
      globalLimits : {min: 5, max: 500},
      callbackUrl  : config.testServer
    });
    await test.createCompanyAndCorporateAdminUser(2, {name: 'Vista SUX'}, {}, {useBalanceCB: true, enableCallbackStatus: false});
    await test.createStoreAndManager();
    await test.createStoreAndManager(2);
    // Create employee
    await test.createEmployee();
    await test.createEmployee(2);
    // Create a customer
    await test.createCustomer();
    await test.createCustomer(2);
    // Login users
    await test.loginUserSaveToken('admin');
    await test.loginUserSaveToken('corporateAdmin');
    await test.loginUserSaveToken('corporateAdmin', 2);
    await test.loginUserSaveToken('employee');
    await test.loginUserSaveToken('employee', 2);
    // Create retailers
    await test.createRetailer({
      gsId: '6017'
    });
    await test.createRetailer({
      name       : 'PinLovers',
      pinRequired: true,
      gsId: '5468'
    });
    await test.createBestBuy();
    await test.createRetailer({
      name: 'NO_BI'
    });
    // Create BI request logs
    await test.createBiRequestLog(true, {
      "number": '2',
      "pin"   : "2"
    });

    await test.createBiRequestLog(true, {
      "number": '7',
      "pin"   : "7"
    });
    await test.createBiRequestLog(false, {
      "number": '8',
      "pin"   : "8"
    });
    await test.createBiRequestLog(false, {
      number: 'a',
      pin   : 'a',
      user  : test.getDefaultReferenceId('users')
    });

    await test.createBiRequestLog(true, {
      "number": '10',
      "pin"   : "10"
    });


  });

  describe('POST api/lq/account/create', function () {
    it('should create a new account', async function () {
      const params = {
        email    : 'hnnng@ecks.dee',
        password : 'aightden',
        firstName: 'herewego',
        lastName : 'newuser',
        company  : 'newcompany',
        store    : 'newStore'
      };

      return await test.request
      .post('/api/lq/account/create')
      .set('Authorization', `bearer ${test.tokens.admin1.token}`)
      .send(params)
      .then(res => {
        expect(res).to.have.status(200);
        const expectedProps = ['token', 'companyId', 'customerId'];
        test.checkResponseProperties(res.body, expectedProps);
      })
      .catch(() => expect(false).to.be.ok);
    });
  });

  describe('POST api/lq/account/create/user', function () {
    it('should create a new account under the same company', async function () {
      const companyId = test.getDefaultReferenceId('companies');
      const storeId   = test.getDefaultReferenceId('stores');
      const params    = {
        email    : 'oahsdfiusadhf@ddd.com',
        password : 'wwwwwwwwwwwwwww',
        firstName: 'herewego',
        lastName : 'newuser',
        companyId,
        storeId
      };

      return await test.request
      .post('/api/lq/account/create/user')
      .set('Authorization', `bearer ${test.tokens.corporateAdmin1.token}`)
      .send(params)
      .then(res => {
        expect(res).to.have.status(200);
        const expectedProps = ['token', 'companyId', 'customerId'];
        test.checkResponseProperties(res.body, expectedProps);
        expect(res.body.companyId).to.be.equal(test.getDefaultReferenceId('companies').toString());
      })
      .catch(() => expect(false).to.be.ok);
    });
  });

  describe('POST api/lq/new', function () {
    it('should require params', async function () {
      return test.lqNew({})
      .then(() => {expect(false).to.be.ok;})
      .catch(err => {
        test.checkErrorResponseProperties(err, ['number', 'number', 'retailer', 'retailer', 'retailer', 'userTime', 'balance']);
      })
    });

    it('should add a new card to the system', async function () {
      const params = {
        number     : '1',
        pin        : '1',
        retailer   : test.getDefaultReferenceId('retailers'),
        userTime   : moment().format(),
        balance    : 40,
        merchandise: false
      };

      return test.lqNew(params)
      .then(async res => {
        expect(res).to.have.status(200);
        test.checkResponseProperties(res.body, ['card']);

        const expectedProps = ['_id', 'sellRate', 'number', 'retailer', 'userTime', 'merchandise', 'balance', 'pin',
                               'buyAmount', 'soldFor'];

        test.checkResponseProperties(res.body.card, expectedProps);

        const card = res.body.card;

        for (const key of Object.keys(params)) {
          if (key === 'retailer') {
            expect(card.retailer).to.be.equal(test.retailers[0].name);
          } else if (key === 'userTime') {
            const expected = moment(params.userTime);
            const actual = moment(card.userTime);
            expect(actual.unix()).to.be.closeTo(expected.unix(), 2); // Tolerate 2 second difference
          } else {
            expect(card[key]).to.be.equal(params[key]);
          }
        }
      })
      .catch(() => expect(false).to.be.ok);
    });

    it('should create a customer if none is specified and default customer does not exist', async function () {
      try { // Only one card
        const cards = await Card.find({});
        expect(cards.length).to.be.equal(1);
        const thisCard     = cards[0];
        const thisCustomer = await Customer.findById(thisCard.customer);
        const user         = await User.findById(thisCard.user[0]);
        expect(user.company.toString()).to.be.equal(thisCustomer.company.toString());
        expect(thisCustomer.stateId).to.be.equal('API_Customer');
      } catch (e) {
        expect(false).to.be.ok;
      }
    });

    it('should add additional cards to the same customer if none specified', async function () {
      const params = {
        number     : '2',
        pin        : '2',
        retailer   : test.getDefaultReferenceId('retailers'),
        userTime   : moment().format(),
        balance    : 100,
        merchandise: false
      };

      return await test.lqNew(params)
      .then(async res => {
        expect(res).to.have.status(200);
        const card              = await Card.findById(res.body.card._id);
        const thisCustomer      = await Customer.findById(card.customer);
        // Get all cards this customer
        const cardsThisCustomer = await Card.find({customer: thisCustomer._id});
        expect(cardsThisCustomer.length).to.be.equal(2);
      })
      .catch(() => expect(false).to.be.ok);
    });

    it('should have specified a verified balance since a completed BI request log exists', async function () {
      try {
        const cardParams = {
          number: '2',
          pin   : '2'
        };

        const card = await Card.findOne(cardParams).populate('inventory');
        expect(card.verifiedBalance).to.be.equal(50);
        expect(card.inventory.verifiedBalance).to.be.equal(50);
      } catch (e) {
        expect(false).to.be.ok;
      }
    });

    it('should reject duplicate cards', async function () {
      const params = {
        number     : '1',
        pin        : '1',
        retailer   : test.getDefaultReferenceId('retailers'),
        userTime   : moment().format(),
        balance    : 40,
        merchandise: false
      };

      return await test.lqNew(params)
      .then(() => {expect(false).to.be.ok;})
      .catch(err => {
        expect(err).to.have.status(400);
        const body = test.getErrBody(err);
        expect(body).to.have.property('invalid');
        expect(body.invalid).to.be.equal('Card has already been inserted into the database');
      });
    });

    it('should reject cards with no matching SMPs', async function () {
      const params = {
        number     : '3',
        pin        : '3',
        retailer   : test.getDefaultReferenceId('retailers'),
        userTime   : moment().format(),
        balance    : 250,
        merchandise: false
      };

      return await test.lqNew(params, 'corporateAdmin', 2)
      .then(() => {expect(false).to.be.ok;})
      .catch(err => {
        expect(err).to.have.status(400);
        const body = test.getErrBody(err);
        expect(body).to.have.property('error');
        expect(body.error).to.have.property('errors');
        expect(body.error.errors).to.have.length(1);
        expect(body.error.errors[0]).to.have.property('balance');
        expect(body.error.errors[0].balance).to.be.equal('Card violates sell limits');
      });
    });

    it('should select lower sell rate with higher limit', async function () {
      const params = {
        number     : '4',
        pin        : '4',
        retailer   : test.getDefaultReferenceId('retailers'),
        userTime   : moment().format(),
        balance    : 60,
        merchandise: false
      };

      return await test.lqNew(params)
      .then(async res => {
        expect(res.body.card.sellRate).to.be.closeTo(0.77, 0.001);
      })
      .catch(() => expect(false).to.be.ok);
    });

    it('should accept electronic cards with no PIN if the retailer does not require a PIN code', async function () {
      const params = {
        number     : '5',
        retailer   : test.getDefaultReferenceId('retailers'),
        userTime   : moment().format(),
        balance    : 30,
        merchandise: false
      };

      return await test.lqNew(params)
      .then(res => {
        expect(res).to.have.status(200);
      })
      .catch(() => expect(false).to.be.ok);
    });

    it('should reject cards without a PIN code if a PIN is required', async function () {
      const retailer = test.references.retailers.filter(r => r.name === 'PinLovers')[0];
      const params   = {
        number     : '6',
        retailer   : retailer._id,
        userTime   : moment().format(),
        balance    : 30,
        merchandise: false
      };

      return await test.lqNew(params)
      .then(() => {expect(false).to.be.ok;})
      .catch(err => {
        const body = test.getErrBody(err);
        test.checkErrorResponseProperties(err, ['retailer']);
        expect(body.error.errors[0].message).to.be.equal('A PIN is required for this retailer');
      });
    });

    it('should complete cards when a BI response is received', async function () {
      const cardParams = {
        number: '4',
        pin   : '4'
      };
      return await test.completeBiLog(cardParams)
      .then(async res => {
        expect(res).to.have.status(200);
        const card = await Card.findOne(cardParams).populate('inventory');
        const log  = await BiRequestLog.findOne({card: card._id});
        expect(log.balance).to.be.equal(100);
        expect(card.verifiedBalance).to.be.equal(100);
        expect(card.inventory.verifiedBalance).to.be.equal(100);
      })
      .catch(() => expect(false).to.be.ok);
    });

    it('should have the store attached when a receipt is generated', async function () {
      const params = {
        number     : '7',
        pin        : '7',
        retailer   : test.getDefaultReferenceId('retailers'),
        userTime   : moment().format(),
        balance    : 100,
        merchandise: false
      };

      return await test.lqNew(params)
      .then(async res => {
        expect(res).to.have.status(200);
        const card    = await Card.findById(res.body.card._id);
        const receipt = await Receipt.findOne({inventories: [card.inventory]});
        expect(receipt).to.have.property('store');
      })
      .catch(() => expect(false).to.be.ok);
    });

    describe('card regex validation', function () {
      before(async function () {
        const retailer = test.references.retailers[0];
        retailer.numberRegex = '/[a-z]{6}/i';
        retailer.pinRegex = '[0-9]{4}';
        await retailer.save();

        const company = test.references.companies[0];
        const settings = await company.getSettings(false);
        settings.validateCard = true;
        await settings.save();
      });

      it('should reject cards that fail the regex validation (if enabled)', async function () {
        const params = {
          number: 'x1',
          pin: '1290837',
          retailer: test.getDefaultReferenceId('retailers'),
          userTime   : moment().format(),
          balance    : 100,
          merchandise: false
        };

        return await test.lqNew(params)
        .then(() => {expect(false).to.be.ok;})
        .catch(err => {
          const body = test.getErrBody(err);
          expect(err).to.have.status(400);
          expect(body.error.errors[0].message).to.be.equal('Card Number & PIN validation failed');
        });
      });

      it('should accept cards that pass the regex validation (if enabled)', async function () {
        const params = {
          number: 'ABCDEF',
          pin: '1111',
          retailer: test.getDefaultReferenceId('retailers'),
          userTime   : moment().format(),
          balance    : 100,
          merchandise: false
        };

        return await test.lqNew(params).then(async res => {
          expect(res).to.have.status(200);
        })
        .catch(() => expect(false).to.be.ok);
      });

      after(async function () {
        try {
          const retailer       = test.references.retailers[0];
          retailer.numberRegex = '';
          retailer.pinRegex    = '';
          await retailer.save();

          const company         = test.references.companies[0];
          const settings        = await company.getSettings(false);
          settings.validateCard = false;
          await settings.save();
        } catch (e) {
          expect(false).to.be.ok;
        }
      });
    });
  });

  describe('POST /lq/bi', function () {
    let number, pin;
    // Request ID of current BI request
    let biRequestId = null;
    it('should respond to fake cards for customer testing purposes', async function () {
      await test.createBiLog({
        number  : '1000',
        pin     : '1a',
        retailer: '5668fbff37226093139b912c'
      })
      .then(res => {
        const body = res.body;
        expect(body.responseCode).to.be.equal('000');
        expect(body.request_id).to.be.equal('11502131554644889807');
        expect(body.balance).to.be.equal(100);
        expect(body.responseMessage).to.be.equal('success');
      })
      .catch(() => expect(false).to.be.ok);

    });
    it('should accept a card to initiate a balance inquiry', async function () {
      number = test.createRandomNumber();
      pin = test.createRandomNumber(4);
      await test.createBiLog({number, pin})
      .then(res => {
        const expectedProps = ['balance', 'response_datetime', 'responseMessage', 'requestId', 'responseCode',
                               'responseDateTime', 'recheckDateTime'];
        test.checkResponseProperties(res.body, expectedProps);
        const body = res.body;
        expect(body.balance).to.be.equal(null);
        expect(body.requestId).to.be.ok;
        expect(body.responseCode).to.be.equal('010');
        biRequestId = body.requestId;
      })
      .catch(() => expect(false).to.be.ok);
    });

    it('should save the gcmgr user ID when making a request', async function () {
      await test.createBiLog({requestId: biRequestId}, false)
      .then(res => {
        const body = res.body;
        const expectedProps = ['balance', 'responseDateTime', 'responseMessage', 'requestId', 'responseCode',
                               'responseDateTime', 'recheckDateTime'];
        test.checkResponseProperties(res.body, expectedProps);
        expect(body.balance).to.be.equal(null);
        expect(body.requestId).to.be.equal(biRequestId);
        expect(body.responseCode).to.be.equal('010');
      })
      .catch(() => expect(false).to.be.ok);
    });

    it('should not initiate multiple balance inquiries on the same card within a 12 hour period', async function () {
      await test.createBiLog({number, pin})
      .then(res => {
        const body = res.body;
        expect(body.requestId).to.be.equal(biRequestId);
        expect(body.balance).to.be.equal(null);
        expect(body.responseCode).to.be.equal('010');
      })
      .catch(() => expect(false).to.be.ok);
    });

    it('should reject a BI request if there is neither gsId nor aiId on the retailer', async function () {
      await test.createBiLog({
        retailer: test.retailers[3]._id
      })
      .then(() => {expect(false).to.be.ok;})
      .catch(err => {
        const error = test.getErrBody(err);
        expect(error).to.have.property('error');
        expect(error.error.errors[0]).to.have.property('name');
        expect(error.error.errors[0]).to.have.property('message');
        expect(error.error.errors[0].message).to.be.equal('Balance Inquiry is not supported for this retailer');
      });
    });

    it('should store lqCustomerName on the biRequestLog entry', async function () {
      const lqCustomerName = 'testcustomer@test.com';
      await test.createBiLog({lqCustomerName})
      .then(async res => {
        const body = res.body;
        const log = await BiRequestLog.findOne({requestId: body.requestId});
        expect(log).to.have.property('lqCustomerName');
        expect(log.lqCustomerName).to.be.equal(lqCustomerName);
      })
      .catch(() => expect(false).to.be.ok);
    });

    // it('should insert BiRequestLog entries into BI if they do not exist', async function () {
    //
    // });
    //
    // it('should update BiRequestLog entries if they are completed in BI but not in gcmgr', async function () {
    //
    // });
    //
    // it('should handle BiRequestLog entries which do not have a requestId and exist in BI', async function () {
    //
    // });
    //
    // it('should handle BiRequestLog entries which do not have a requestId and do not exist in BI', async function () {
    //
    // });
  });

  describe('POST /lq/bi/:requestId', function () {
    const callbackUrl = 'http://loganfake.com';
    // Number for testing adjustment callbacks
    const adjustmentNumber = '102';
    it('should return a 400 if balance is missing', async function () {
      return await test.completeBiLog({
        "number"    : 1,
        "pin"       : 1,
        "retailerId": test.getDefaultReferenceId('retailers'),
        "invalid"   : 0,
        "fixed"     : 0,
      }, 1, false)
      .then(() => {expect(false).to.be.ok;})
      .catch(err => {
        expect(err).to.have.status(400);
        const body = test.getErrBody(err);
        expect(body.error).to.have.property('errors');
        expect(body.error.errors).to.have.lengthOf(1);
        test.checkErrorResponseProperties(err, ['balance']);
      })
    });

    it('should return a 400 if number or retailerId is missing', async function () {
      return await test.completeBiLog({}, 1, false)
      .then(() => {expect(false).to.be.ok;})
      .catch(err => {
        expect(err).to.have.status(400);
        const body = test.getErrBody(err);
        expect(body.error).to.have.property('errors');
        expect(body.error.errors).to.have.lengthOf(3);
        test.checkErrorResponseProperties(err, ['retailerId', 'number', 'balance']);
      })
    });

    it('should create a log if none exists, and link with the original requesting user', async function () {
      const userId = test.getDefaultReferenceId('users');
      const params = {
        number: 'nope',
        pin   : 'nope',
        userId
      };
      return await test.completeBiLog(params, 'nope')
      .then(async res => {
        expect(res).to.have.status(200);
        const log = await BiRequestLog.findOne({number: 'nope', pin: 'nope'});
        expect(log).to.be.ok;
        expect(log.balance).to.be.equal(100);
        expect(log.user.toString()).to.be.equal(userId.toString());
      })
      .catch(() => expect(false).to.be.ok);
    });

    /**
     * @todo update runValidation() to return specific status codes at some point
     */
    it('should return 400 if a retailer does not exist', async function () {
      const params = {
        number    : 'nope',
        pin       : 'nope',
        retailerId: test.getDefaultReferenceId('companies')
      };
      return await test.completeBiLog(params)
      .then(() => {expect(false).to.be.ok;})
      .catch(err => {
        expect(err).to.have.status(400);
        const errBody = test.getErrBody(err);
        expect(errBody).to.have.property('error');
        expect(errBody.error).to.have.property('errors');
        test.checkErrorResponseProperties(err, ['retailerId']);
      })
    });

    it('should create a new BiRequestLog if one does not exist, and attach the user ID', async function () {
      const userId = test.getDefaultReferenceId('users');
      await test.completeBiLog({
        number: '100',
        pin   : '100',
        userId
      }, 'b')
      .then(async () => {
        const log = await BiRequestLog.findOne({requestId: 'b'});
        expect(log).to.be.ok;
        expect(log.number).to.be.equal('100');
        expect(log.pin).to.be.equal('100');
        expect(log.balance).to.be.equal(100);
        expect(log.finalized).to.be.ok;
        expect(log.user.toString()).to.be.equal(userId.toString());
      })
      .catch(() => expect(false).to.be.ok);
    });

    it('should create a new BiRequestLog if one does not exist, if no user ID is available', async function () {
      await test.completeBiLog({
        number: '101',
        pin   : '101',
      }, 'c')
      .then(async () => {
        const log = await BiRequestLog.findOne({requestId: 'c'});
        expect(log).to.be.ok;
        expect(log.number).to.be.equal('101');
        expect(log.pin).to.be.equal('101');
        expect(log.balance).to.be.equal(100);
        expect(log.finalized).to.be.ok;
        expect(log.user).to.be.undefined;
      })
      .catch(() => expect(false).to.be.ok);
    });

    it('should update BiRequestLog if the value of the card changes', async function () {
      await test.completeBiLog({
        number : '100',
        pin    : '100',
        balance: 0
      }, 'b')
      .then(async () => {
        const logs = await BiRequestLog.find({requestId: 'b'}).sort({created: -1});
        expect(logs).to.have.lengthOf(1);
        const newLog = logs[0];
        expect(newLog.number).to.be.equal('100');
        expect(newLog.pin).to.be.equal('100');
        expect(newLog.balance).to.be.equal(0);
        expect(newLog.finalized).to.be.ok;
      })
      .catch(() => expect(false).to.be.ok);
    });

    it('should complete an existing BiRequestLog, and make a sale', async function () {
      const number = adjustmentNumber;
      const balance = 50;
      const invalid = false;
      const lqCustomerName = 'customer1@example.com';
      const prefix = 'd';
      await test.createAndCompleteBiLog(number, balance, invalid, lqCustomerName, prefix, callbackUrl)
      .then(async () => {
        const newLog = await BiRequestLog.findOne({number}).populate({
          path: 'card',
          populate: [{
            path: 'inventory',
            model: 'Inventory'
          }],
        });
        expect(newLog.number).to.be.equal(number);
        expect(newLog.pin).to.be.equal(number);
        expect(newLog.balance).to.be.equal(balance);
        expect(newLog.finalized).to.be.ok;
        expect(newLog.lqCustomerName).to.be.equal(lqCustomerName);
        expect(newLog).to.have.property('card');
        expect(newLog.card).to.have.property('balance');
        expect(newLog.card.balance).to.be.equal(balance);
        expect(newLog.card).to.have.property('inventory');
        expect(newLog.card.inventory).to.have.property('verifiedBalance');
        expect(newLog.card.inventory.verifiedBalance).to.be.equal(balance);
        expect(newLog.card.lqCustomerName).to.be.equal(lqCustomerName);


        const inventory = newLog.card.inventory;
        expect(inventory.serviceFee).to.be.equal(0.0075);
        expect(inventory.cqPaid).to.be.closeTo(43.5, 0.05);
        expect(inventory.netAmount).to.be.closeTo(43.17, 0.05);
      })
      .catch(() => expect(false).to.be.ok);
    });

    it('should have sent a callback if callbackUrl was specified in BiRequestLog', async function () {
      try {
        const log       = await test.getMostRecentBiRequestLog();
        const card      = await Card.findById(log.card);
        const callbacks = await CallbackLog.find({number: card.number});
        expect(callbacks).to.have.lengthOf(1);
        const callback = callbacks[0];
        expect(callback.callbackType).to.be.equal('biComplete');
        expect(callback.verifiedBalance).to.be.equal(50);
        expect(callback.pin).to.be.equal(log.pin);
        expect(callback.url).to.be.equal(callbackUrl);
      } catch (e) {
        expect(false).to.be.ok;
      }
    });

    it('should not send a duplicate callback', async function () {
      try {
        const log = await test.getMostRecentBiRequestLog();
        // BI complete
        await test.completeBiLog({
          number : log.number,
          pin    : log.pin,
          balance: 50
        }, log.requestId);

        const card      = await Card.findById(log.card);
        const callbacks = await CallbackLog.find({number: card.number});
        expect(callbacks).to.have.lengthOf(1);
        const callback = callbacks[0];
        expect(callback.callbackType).to.be.equal('biComplete');
        expect(callback.verifiedBalance).to.be.equal(50);
        expect(callback.pin).to.be.equal(log.pin);
      } catch (e) {
        expect(false).to.be.ok;
      }
    });

    it('should complete an existing BiRequestLog, which does not result in a sale', async function () {
      // Create a new BI request
      const number = '103';
      const balance = 50;
      const invalid = false;
      const lqCustomerName = 'customer2@example.com';
      await test.createAndCompleteBiLog(number, balance, invalid, lqCustomerName)
      .then(async () => {
        const newLog = await BiRequestLog.findOne({number});
        expect(newLog.number).to.be.equal(number);
        expect(newLog.pin).to.be.equal(number);
        expect(newLog.balance).to.be.equal(balance);
        expect(newLog.finalized).to.be.ok;
        expect(newLog.lqCustomerName).to.be.equal('customer2@example.com');
      })
      .catch(() => expect(false).to.be.ok);
    });

    it('should ignore limits when disableLimits is set in company settings', async function () {
      try {
        await test.createBiLog({
          number: '104',
          pin   : '104',
          lqCustomerName: 'customer3@example.com',
          autoSell: true
        });
        const log = await test.getMostRecentBiRequestLog();
        const user = await User.findById(log.user);
        const company = await Company.findById(user.company);
        let companySettings = await company.getSettingsObject();
        expect(companySettings.disableLimits).to.be.ok;
        await test.completeBiLog({
          number : '104',
          pin    : '104',
          balance: 499
        }, log.requestId);
        const newLog = await BiRequestLog.findOne({requestId: log.requestId}).populate({
          path: 'card',
          populate: [
            {
              path: 'inventory',
              model: 'Inventory'
            }
          ]
        });
        expect(newLog).to.have.property('card');
        expect(newLog.card).to.have.property('inventory');
        expect(newLog.balance).to.be.equal(499);
        expect(newLog.card.inventory.verifiedBalance).to.be.equal(499);
      } catch (err) {
        expect(false).to.be.ok;
      }
    });

    it('should respect retailer limits when disableLimits is set to false (Vista style callback)', async function () {
      const number = '4.1';
      const balance = 101;
      try {
        await test.createAndCompleteBiLog(number, balance, false, null, null, config.testServer, 2);
        expect(false).to.be.ok;
      } catch (err) {
        const errBody = test.getErrBody(err);
        expect(err).to.have.status(400);
        expect(errBody.error).to.have.property('errors');
        expect(errBody.error.errors).to.have.length(1);
        expect(errBody.error.errors[0]).to.have.property('balance');
        expect(errBody.error.errors[0].balance).to.be.equal('Card violates sell limits');

        const newLog = await BiRequestLog.findOne({number});
        expect(newLog).to.have.property('balance');
        expect(newLog.balance).to.be.equal(balance);
        const lastFourFigits = newLog.getLast4Digits();
        // Check callback
        const callbacks = await CallbackLog.find({number: lastFourFigits});
        expect(callbacks).to.have.length(1);
        await test.checkCallbacks(callbacks[0], lastFourFigits, number, null, null, config.callbackTypes.balanceCB);
      }
    });

    it('should not sell cards if disableLimits is true and card value is 0', async function () {
      const number = '105';
      const balance = 0;
      const invalid = false;
      const lqCustomerName = 'customer4@example.com';
      await test.createAndCompleteBiLog(number, balance, invalid, lqCustomerName)
      .then(() => expect(false).to.be.ok)
      .catch(err => {
        const errBody = test.getErrBody(err);
        expect(errBody).to.have.property('error');
        expect(errBody.error).to.have.property('errors');
        expect(errBody.error.errors).to.have.length(1);
        expect(errBody.error.errors[0]).to.have.property('balance');
        expect(errBody.error.errors[0].balance).to.be.equal('Card violates sell limits');
      });
    });

    it('should accept balances as strings', async function () {
      const number = '106';
      const balance = '20';
      await test.createAndCompleteBiLog(number, balance)
      .then(async () => {
        const newLog = await BiRequestLog.findOne({number});
        expect(newLog).to.have.property('balance');
        expect(newLog.balance).to.be.equal(parseFloat(balance));
      })
      .catch(() => expect(false).to.be.ok);
    });

    it('should reject null balances', async function () {
      const number = '107';
      const balance = null;
      const invalid = true;
      await test.createAndCompleteBiLog(number, balance, invalid)
      .then(() => expect(false).to.be.ok)
      .catch(err => {
        expect(err).to.have.status(400);
        const errBody = test.getErrBody(err);
        test.checkErrorResponseProperties(err, ['balance']);
        expect(errBody.error.errors[0].message).to.be.equal('Invalid balance. Balance must be an integer or float');
      });
    });

    it('should accept invalid card responses', async function () {
      const number = '108';
      const balance = 0;
      const invalid = true;
      await test.createAndCompleteBiLog(number, balance, invalid)
      .then(() => expect(false).to.be.ok)
      .catch(err => {
        const errBody = test.getErrBody(err);
        expect(err).to.have.status(400);
        expect(errBody.error).to.have.property('errors');
        expect(errBody.error.errors).to.have.length(1);
        expect(errBody.error.errors[0]).to.have.property('balance');
        expect(errBody.error.errors[0].balance).to.be.equal('Card violates sell limits');
      });
    });

    it('should return the violates status code when a card violates sell limits', async function () {
      const number = '109';
      const balance = 501;
      try {
        await test.createAndCompleteBiLog(number, balance);
        expect(false).to.be.ok;
      } catch (err) {
        const errBody = test.getErrBody(err);
        expect(err).to.have.status(400);
        expect(errBody.error).to.have.property('errors');
        expect(errBody.error.errors).to.have.length(1);
        expect(errBody.error.errors[0]).to.have.property('balance');
        expect(errBody.error.errors[0].balance).to.be.equal('Card violates sell limits');

        const newLog = await BiRequestLog.findOne({number});
        expect(newLog).to.have.property('balance');
        expect(newLog.balance).to.be.equal(501);
        // Check callback
        const callbacks = await CallbackLog.find({number});
        expect(callbacks).to.have.length(1);
        await test.checkCallbacks(callbacks[0], number, number, config.callbackStatusCodes.violateSellLimits);
      }
    });

    it('should send the biComplete callbackType unless company settings specifies otherwise', async function () {
      const number = '110';
      const balance = 501;
      try {
        await test.createAndCompleteBiLog(number, balance);
        expect(false).to.be.ok;
      } catch (err) {
        const errBody = test.getErrBody(err);
        expect(err).to.have.status(400);
        expect(errBody.error).to.have.property('errors');
        expect(errBody.error.errors).to.have.length(1);
        expect(errBody.error.errors[0]).to.have.property('balance');
        expect(errBody.error.errors[0].balance).to.be.equal('Card violates sell limits');

        const newLog = await BiRequestLog.findOne({number});
        expect(newLog).to.have.property('balance');
        expect(newLog.balance).to.be.equal(501);
        // Check callback
        const callbacks = await CallbackLog.find({number});
        expect(callbacks).to.have.length(1);
        await test.checkCallbacks(callbacks[0], number, number, config.callbackStatusCodes.violateSellLimits);
      }
    });

    it('should send the balanceCB callbackType if company settings specifies, with callbackStatus disabled', async function () {
      const number = '111';
      const balance = 99;
      try {
        await test.createAndCompleteBiLog(number, balance, false, null, null, config.testServer, 2);
        const log = await test.getMostRecentBiRequestLog();
        const lastFourDigits = log.getLast4Digits();
        // Check callback
        const callbacks = await CallbackLog.find({number: lastFourDigits});
        expect(callbacks).to.have.length(1);
        await test.checkCallbacks(callbacks[0], lastFourDigits, number, null, null, config.callbackTypes.balanceCB);
      } catch (err) {
        expect(false).to.be.ok;
      }
    });

    it('should accept a null / unsupplied liquidation customer name', async function () {
      await test.createBiLog({number: '1k', pin: '1k'})
      .then(async () => {
        const log = await BiRequestLog.findOne({number: '1k', pin: '1k'});
        expect(log).to.have.property('lqCustomerName');
        expect(log.lqCustomerName).to.be.equal(null);
      })
      .catch(() => expect(false).to.be.ok);
    });

    it('should store a liquidation customer name', async function () {
      await test.createBiLog({number: '1l', pin: '1l', lqCustomerName: 'test@cardquiry.com'})
      .then(async () => {
        const log = await BiRequestLog.findOne({number: '1l', pin: '1l'});
        expect(log).to.have.property('lqCustomerName');
        expect(log.lqCustomerName).to.be.equal('test@cardquiry.com');
      })
      .catch(() => expect(false).to.be.ok);
    });

    it('should send a supplied liquidation customer name in the callback', async function () {
      await test.createBiLog({number: '1M', pin: '1M', lqCustomerName: 'test@cardquiry.com', callbackUrl: config.testServer}, true, 2);
      const log = await test.getMostRecentBiRequestLog();
      await test.completeBiLog({number: '1M', pin: '1M', lqCustomerName: 'test@cardquiry.com'}, log.requestId)
      .then(async () => {
        const log = await test.getMostRecentBiRequestLog();
        const lastFourDigits = log.getLast4Digits();
        const callbacks = await CallbackLog.find({number: lastFourDigits});
        expect(callbacks).to.have.lengthOf(1);
        const callback = callbacks[0];
        expect(callback.body.lqCustomerName).to.be.equal('test@cardquiry.com');
      })
      .catch(() => expect(false).to.be.ok);
    });

    /**
     * We need to modify the creation of an inventory, as well as the update of a VB, to save the serviceFee, cqPaid, and netAmount
     */
    it('should should allow a denial if the VB === CB', async function () {
      try {
        const card = await Card.findOne({number: adjustmentNumber}).populate('inventory');
        await test.rejectCard([card.inventory._id.toString()]);
        const callbacks = await CallbackLog.find({number: adjustmentNumber});
        expect(callbacks).to.have.length(2);
        const denialCallback     = callbacks[1];
        const props              = ['number', 'claimedBalance', 'verifiedBalance', 'cqPaid', 'cqAch', 'finalized',
                                    'callbackType', 'netAmount', 'serviceFee'];
        const denialCallbackBody = denialCallback.body;
        props.forEach(prop => {
          if (denialCallbackBody) {
            expect(denialCallbackBody[prop]).to.not.be.undefined;
          }
        });
        expect(denialCallbackBody.verifiedBalance).to.be.equal(50);
        expect(denialCallbackBody.callbackType).to.be.equal('denial');
      } catch (e) {
        expect(false).to.be.ok;
      }
    });

    /**
     * @todo Move this to company controller test
     */
    it('should recalculate necessary values on VB change', async function () {
      try {
        const card                = await Card.findOne({number: adjustmentNumber}).populate('inventory');
        const inventory           = card.inventory;
        // Change and just make sure calculateValues is handling this properly
        inventory.verifiedBalance = 40;
        const inventory2          = await inventory.save();
        expect(inventory2.serviceFeeValue).to.be.closeTo(0.26, 0.01);
        expect(inventory2.cqPaid).to.be.closeTo(34.8, 0.05);
        expect(inventory2.netAmount).to.be.closeTo(34.54, 0.05);
      } catch (e) {
        expect(false).to.be.ok;
      }
    });


    it('should initial a denial if VB < CB', async function () {
      try {
        const card = await Card.findOne({number: adjustmentNumber}).populate('inventory');
        await test.rejectCard([card.inventory._id.toString()]);

        const callbacks = await CallbackLog.find({number: adjustmentNumber});
        expect(callbacks).to.have.length(3);
        const denialCallback     = callbacks[2];
        const props              = ['number', 'claimedBalance', 'verifiedBalance', 'cqPaid', 'cqAch', 'finalized',
                                    'callbackType', 'netAmount', 'serviceFee', 'lqCustomerName'];
        const denialCallbackBody = denialCallback.body;
        if (denialCallbackBody) {
          props.forEach(prop => {
            expect(denialCallbackBody[prop]).to.not.be.undefined;
          });
        }
        expect(denialCallbackBody.verifiedBalance).to.be.equal(40);
        expect(denialCallbackBody.callbackType).to.be.equal('denial');
        expect(denialCallbackBody.claimedBalance).to.be.equal(50);
        expect(denialCallbackBody.cqPaid).to.be.equal(34.8);
        expect(denialCallbackBody.cqAch).to.be.null;
        expect(denialCallbackBody.finalized).to.be.equal(false);
        expect(denialCallbackBody.netAmount).to.be.equal(34.54);
        expect(denialCallbackBody.serviceFee).to.be.equal(0.01);
      } catch (e) {
        expect(false).to.be.ok;
      }
    });

    it('should send a credit callback from admin activity', async function () {
      try {
        const card                = await Card.findOne({number: adjustmentNumber}).populate('inventory');
        const inventory           = card.inventory;
        // Change and just make sure calculateValues is handling this properly
        inventory.verifiedBalance = 100;
        const inventory2          = await inventory.save();
        expect(inventory2.serviceFeeValue).to.be.equal(0.6525);
        expect(inventory2.cqPaid).to.be.equal(87);
        expect(inventory2.netAmount).to.be.equal(86.3475);

        await test.rejectCard([card.inventory._id.toString()]);

        const callbacks = await CallbackLog.find({number: adjustmentNumber});
        expect(callbacks).to.have.length(4);
        const creditCallback     = callbacks[3];
        const props              = ['number', 'claimedBalance', 'verifiedBalance', 'cqPaid', 'cqAch', 'finalized',
                                    'callbackType', 'netAmount', 'serviceFee', 'lqCustomerName'];
        const creditCallbackBody = creditCallback.body;
        if (creditCallbackBody) {
          props.forEach(prop => {
            expect(creditCallbackBody[prop]).to.not.be.undefined;
          });
        }
        expect(creditCallbackBody.number).to.be.equal(adjustmentNumber);
        expect(creditCallbackBody.claimedBalance).to.be.equal(50);
        expect(creditCallbackBody.verifiedBalance).to.be.equal(100);
        expect(creditCallbackBody.cqPaid).to.be.equal(87);
        expect(creditCallbackBody.cqAch).to.be.null;
        expect(creditCallbackBody.finalized).to.be.equal(false);
        expect(creditCallbackBody.callbackType).to.be.equal('credit');
        expect(creditCallbackBody.netAmount).to.be.equal(86.35);
        expect(creditCallbackBody.serviceFee).to.be.equal(0.01);
      } catch (e) {
        expect(false).to.be.ok;
      }
    });

    it('should send a cqPaymentInitiated callback from admin activity for a transaction', async function () {

    });

    it('should send a needs attention callback from admin activity', async function () {

    });

  });

  /**
   * For these tests, you're going to need to modify the store record that is being used in the requests. Set the following properties
   * creditValuePercentage: 1.1
   * maxSpending: 100
   * payoutAmountPercentage: 0.5
   *
   * creditValuePercentage is the amount additional that the store is willing to give the customer for the card. 1.1 means that a customer will be $110 for a $100 card.
   * maxSpending is the maximum amount a customer is allowed to spend on a card for a single transaction. This endpoint is used for purchasing merchandise using cards. If the customer wants to buy an item that is $200, and they bring in a card that is $100, and they get $110 for the card, then the customer will owe the store $90 in cash (200 - 110 = 90)
   * payoutAmountPercentage: This is the amount that we will pay the store for the card. At 0.5, it means that the store will receive 50% of the value that we sell the card for. If we sold this $100 card for $80, then the store will get $40.
   */
  describe('POST /lq/transactions', function () {
    /**
     * Make sure that all required properties are sent in to new transactions. A complete transaction request body looks like this:
     * {
        "number":"12345",
        "pin":"05321",
        "retailer":"{{retailer_id}}",
        "userTime":"2016-09-10T20:34:50-04:00",
        "balance": 100,
        "memo": "Match example",
        "merchandise": false,
        "transactionTotal": 50,
        "transactionId": 12345,
        "customerId": "{{customer_id}}",
        "storeId": "{{store_id}}",
        "prefix": "xyz",
        "vmMemo1": "a",
        "vmMemo2": "b",
        "vmMemo3": "c",
        "vmMemo4": "d"
      }
     The transactions documentation lists all properties as well: http://docs.gcmgrapi.apiary.io/#reference/0/transactions
     */
    it(
      'should require number, retailer, userTime, balance, transaction, transactionTotal, customerId, and storeId in the request body',
      async function () {
        return await test.lqTransactions(null)
        .then(() => {expect(false).to.be.ok;})
        .catch(err => {
          expect(err).to.have.status(400);
          test.checkErrorResponseProperties(err,
            ['number', 'retailer', 'userTime', 'balance', 'transactionId', 'transactionTotal', 'transactionTotal',
             'customerId']);
        });
      });

    /**
     * When a card is submitted, it is sent to the balance inquiry system, which will attempt to determine the balance. When this happens, a BiRequestLog entry is created in the DB. When a response is received from BI, it is completed, and the balance returned from BI is recorded as the "verifiedBalance" in both the card and inventory. The balance that users enter when submitting a card is called the claimed balance, and it is recorded in the property "balance" in both the card and inventory.
     * In this test, create a BiRequestLog for the card being submitted, and test that the verified balance is recorded on both the card and inventory.
     */
    it('should set the verifiedBalance on both card and inventory for cards for which BI is already completed',
      async function () {
        // Make request

        return await test.lqTransactions({
          number: '10',
          pin: '10',
          callbackUrl: config.testServer
        })
        .then(async res => {
          expect(res).to.have.status(200);
          // Get BI log
          const biLog = await BiRequestLog.findOne({number: '10'});
          expect(biLog).to.be.ok;
          // Get card
          const card = await Card.findById(res.body.card._id).populate('inventory');
          expect(biLog.card.toString()).to.be.equal(card._id.toString());
          expect(card.verifiedBalance).to.be.equal(50);
          expect(card.inventory.verifiedBalance).to.be.equal(50);
        })
        .catch(() => expect(false).to.be.ok);
      });

    it('should set no verifiedBalance on the record if balance inquiry has not finished yet', async function () {
      return await test.lqTransactions({
        number: '8',
        pin   : '8'
      })
      .then(async res => {
        expect(res).to.have.status(200);
        const card = await Card.findById(res.body.card._id).populate('inventory');
        expect(card.verifiedBalance).to.be.null;
        expect(card.inventory.verifiedBalance).to.be.null;
      })
      .catch(() => expect(false).to.be.ok);
    });

    it('should reject duplicate cards', async function () {
      return await test.lqTransactions({
        number: '8',
        pin   : '8'
      })
      .then(() => expect(false).to.be.ok)
      .catch(err => {
        expect(err).to.have.status(400);
        const body = test.getErrBody(err);
        expect(body.error).to.have.property('errors');
        expect(body.error.errors).to.have.length(1);
        expect(body.error.errors[0]).to.have.property('card');
        expect(body.error.errors[0].card).to.be.equal('Card has already been inserted');
      });
    });

    it('should create a new BiRequestLog entry for a card which has not had BI started before', async function () {
      return await test.lqTransactions({
        number: '9',
        pin   : '9'
      })
      .then(async res => {
        expect(res).to.have.status(200);
        const card = await Card.findById(res.body.card._id).populate('inventory');
        const log  = await BiRequestLog.findOne({card: card._id});
        expect(log).not.to.be.undefined;
        expect(log.card.toString()).to.be.equal(card._id.toString());
      })
      .catch(() => expect(false).to.be.ok);
    });

    it('should populate the verifiedBalance of a card when BI completes', async function () {
      const cardParams = {
        number: '9',
        pin   : '9'
      };
      return await test.completeBiLog({
        number: '9',
        pin   : '9'
      })
      .then(async res => {
        expect(res).to.have.status(200);
        const card = await Card.findOne(cardParams).populate('inventory');
        const log  = await BiRequestLog.findOne({card: card._id});
        expect(log.balance).to.be.equal(100);
        expect(card.verifiedBalance).to.be.equal(100);
        expect(card.inventory.verifiedBalance).to.be.equal(100);
      })
      .catch(() => expect(false).to.be.ok);
    });

    it('should have made a callback when BI was completed', async function () {
      try {
        const cardParams = {
          number: '9',
          pin   : '9'
        };
        const logs       = await BiRequestLog.find(cardParams);
        expect(logs).to.have.length(1);
      } catch (e) {
        expect(false).to.be.ok;
      }
    });

    it('should return 404 status code if the customer does not exist', async function () {
      return await test.lqTransactions({
        number    : '9',
        pin       : '9',
        customerId: test.getDefaultReferenceId('stores')
      })
      .then(() => {expect(false).to.be.ok;})
      .catch(async err => {
        expect(err).to.have.status(400);
        const body = test.getErrBody(err);
        expect(body.error).to.have.property('errors');
        expect(body.error.errors).to.have.length(1);
        expect(body.error.errors[0]).to.have.property('name');
        expect(body.error.errors[0].name).to.be.equal('customerId');
        expect(body.error.errors[0].message).to.be.equal('Customer does not exist');
      });
    });

    it('should return a 400 status code if the card specified in the request body already exists in the DB',
      async function () {
        try {
          await test.lqTransactions({
            number: '9',
            pin   : '9'
          });
          expect(false).to.be.ok;
        } catch (err) {
          expect(err).to.have.status(400);
          const body = test.getErrBody(err);
          expect(body.error).to.have.property('errors');
          expect(body.error.errors).to.have.length(1);
          expect(body.error.errors[0]).to.have.property('card');
          expect(body.error.errors[0].card).to.be.equal('Card has already been inserted');
        }
      });

    it(
      'should specify inventory.transaction.amountDue as 45 if the transaction total is 100 and card balance is 50 if the retailer pays out 0.9 for the card',
      async function () {

        const retailerData = await Retailer.findOne({"sellRates.cardCash": "0.9"});

        // Update the maxSpending for the store being used to allow for the full value of the card to be used
        const thisStore       = test.references.stores[0];
        thisStore.maxSpending = 100;
        await thisStore.save();
        test.references.stores[0] = thisStore;

        return await test.lqTransactions({
          transactionTotal: 100,
          balance         : 50,
          retailer        : retailerData._id,
          storeId         : thisStore._id,
          number          : '17',
          pin             : '17'
        })
        .then(async res => {
          expect(res).to.have.status(200);

          const parsedText = res.body;

          let amountDue = parsedText.card.transaction.amountDue;

          expect(amountDue).to.be.equal(45);
        })
        .catch(() => expect(false).to.be.ok);

      });

    it('should specify inventory.transaction.amountDue as 0 if the transaction total is 50 and the card balance is 100',
      async function () {
        //Using the auto-increment aspect of test.lqTransactions,
        //must set test.cardNumber to high enough value to
        //avoid duplicate card errors
        test.cardNumber = 50;
        return await test.lqTransactions({
          transactionTotal: '50',
          balance         : '100'
        })
        .then(async res => {
          expect(res).to.have.status(200);

          const parsedText = res.body;

          let amountDue = parsedText.card.transaction.amountDue;

          expect(amountDue).to.be.equal(0);
        })
        .catch(() => expect(false).to.be.ok);
      });
    //
    it(
      'should specify inventory.transaction.nccCardValue as 0 if the transaction total is 100 and the card balance is 50',
      async function () {
        return await test.lqTransactions({
          transactionTotal: '100',
          balance         : '50'
        })
        .then(async res => {
          expect(res).to.have.status(200);
          const parsedText = res.body;
          let nccCardValue = parsedText.card.transaction.nccCardValue;
          expect(nccCardValue).to.be.equal(0);
        })
        .catch(() => expect(false).to.be.ok);
      });
    //
    it(
      'should specify inventory.transaction.nccCardValue as 60 if the transaction total is 50 and the card balance is 100',
      async function () {
        return await test.lqTransactions({
          transactionTotal: '50',
          balance         : '100'
        })
        .then(async res => {
          expect(res).to.have.status(200);
          const parsedText = res.body;
          let nccCardValue = parsedText.card.transaction.nccCardValue;
          expect(nccCardValue).to.be.equal(60);
        })
        .catch(() => expect(false).to.be.ok);
      });
    //
    it(
      'should specify inventory.transaction.merchantPayoutAmount as 25 if the transaction total is 50 and the card balance is 100',
      async function () {
        return await test.lqTransactions({
          transactionTotal: '50',
          balance         : '100'
        })
        .then(async res => {
          expect(res).to.have.status(200);
          const parsedText         = res.body;
          let merchantPayoutAmount = parsedText.card.transaction.merchantPayoutAmount;
          expect(merchantPayoutAmount).to.be.equal(25);
        })
        .catch(() => expect(false).to.be.ok);
      });

    it(
      'should specify inventory.transaqction.merchantPayoutAmount as 27.5 if the transaction total is 100 and the card balance is 50',
      async function () {
        return await test.lqTransactions({
          transactionTotal: '100',
          balance         : '50'
        })
        .then(async res => {
          expect(res).to.have.status(200);
          const parsedText         = res.body;
          let merchantPayoutAmount = parsedText.card.transaction.merchantPayoutAmount;
          expect(merchantPayoutAmount).to.be.equal(27.5);
        })
        .catch(() => expect(false).to.be.ok);
      });

    it('should still work if the customer has rejection', async function () {
      const customer               = await Customer.findById(test.getDefaultReferenceId('customers'));
      customer.rejectionTotal      = 200;
      const originalRejectionTotal = customer.rejectionTotal;
      await customer.save();

      return await test.lqTransactions({
        transactionTotal: '100',
        balance         : '50'
      }).then(async res => {
        expect(res).to.have.status(200);
        const card     = await Card.findById(res.body.card._id);
        const customer = await Customer.findById(test.getDefaultReferenceId('customers'));
        expect(customer.rejectionTotal).to.be.closeTo(originalRejectionTotal - card.buyAmount, 0.001);
      })
      .catch(() => expect(false).to.be.ok);
    });
  });

  describe('PATCH /lq/reconcile', function () {
    it('should reconcile cards', async function () {
      try {
        const params = {
          number     : '18',
          pin        : '18',
          retailer   : test.getDefaultReferenceId('retailers'),
          userTime   : moment().format(),
          balance    : 100,
          merchandise: false
        };

        const card = await test.lqNew(params);

        const response = await test.request.patch('/api/lq/reconcile')
        .set('Authorization', 'bearer ' + test.tokens.corporateAdmin1.token)
        .send({
          cardId  : card.body.card._id,
          userTime: moment().format()
        });

        expect(response).to.have.status(200);

        const updatedCard = await Card.findById(card.body.card._id).populate('inventory');

        expect(updatedCard.inventory).to.have.property('reconciliation');
      } catch (e) {
        expect(false).to.be.ok;
      }
    });
  });

  describe('PATCH /lq/companies/:companyId/card/:cardId/proceed-with-sale', function () {
    it('should mark an inventory for sale', async function () {
      try {
        const card    = await Card.findOne({
          number: '18',
          pin   : '18'
        });
        const company = test.getDefaultReferenceId('companies');

        const response = await test.request.patch(`/api/lq/companies/${company}/card/${card._id}/proceed-with-sale`)
        .set('Authorization', 'bearer ' + test.tokens.corporateAdmin1.token);

        expect(response).to.have.status(200);

        const inventory = await Inventory.findOne({card: card._id});

        expect(inventory.proceedWithSale).to.be.true;
      } catch (e) {
        expect(false).to.be.ok;
      }
    });
  });

  describe('Company Settings', function () {
    describe('PATCH /lq/companies/:companyId/settings', function () {
      it('should update the company settings', async function () {
        try {
          const company  = await Company.findById(test.getDefaultReferenceId('companies'));
          const settings = await company.getSettings();

          const response = await test.request.patch(`/api/lq/companies/${company._id}/settings`)
          .set('Authorization', 'bearer ' + test.tokens.corporateAdmin1.token)
          .send({
            cardType            : 'physical',
            customerDataRequired: !settings.customerDataRequired
          });

          expect(response).to.have.status(200);
          expect(response.body.cardType).to.be.equal('physical');
          expect(response.body.customerDataRequired).to.be.equal(!settings.customerDataRequired);

          const reverted = await test.request.patch(`/api/lq/companies/${company._id}/settings`)
          .set('Authorization', 'bearer ' + test.tokens.corporateAdmin1.token)
          .send({
            cardType            : settings.cardType,
            customerDataRequired: settings.customerDataRequired
          });

          expect(reverted.body.cardType).to.be.equal(settings.cardType);
          expect(reverted.body.customerDataRequired).to.be.equal(settings.customerDataRequired);
        } catch (e) {
          expect(false).to.be.ok
        }
      });
    });

    describe('GET /lq/companies/:companyId/settings', function () {
      it('should retrieve the company settings', async function () {
        try {
          const company  = await Company.findById(test.getDefaultReferenceId('companies'));
          const settings = await company.getSettings();

          const response = await test.request.get(`/api/lq/companies/${company._id}/settings`)
          .set('Authorization', 'bearer ' + test.tokens.corporateAdmin1.token);

          const expected = _.omit(response.body, ['reserveTotal'])
          const keys     = Object.keys(expected);

          expect(response).to.have.status(200);
          expect(expected).to.deep.equal(_.pick(settings, keys));
        } catch (e) {
          expect(false).to.be.ok;
        }
      });
    });
  });

  describe('Stores', function () {
    describe('POST /lq/stores', function () {
      it('should create a new store', async function () {
        try {
          const response = await test.request.post('/api/lq/stores')
          .set('Authorization', 'bearer ' + test.tokens.corporateAdmin1.token)
          .send({
            name                  : 'New Store',
            address1              : '123 Abc Street',
            address2              : 'Ct. #100',
            city                  : 'Adamsville',
            state                 : 'AL',
            zip                   : '35005',
            contact               : {
              firstName: 'John',
              role     : 'employee',
              lastName : 'Public',
              email    : 'johnq@public.com',
              password : '123456'
            },
            creditValuePercentage : 1.1,
            maxSpending           : 30,
            payoutAmountPercentage: 0.2
          });

          expect(response).to.have.status(200);
          expect(response.body).to.have.property('_id');
        } catch (e) {
          expect(false).to.be.ok;
        }
      });
    });

    describe('GET /lq/stores', function () {
      it('should retrieve all of the company\'s stores', async function () {
        try {
          const response = await test.request.get('/api/lq/stores')
          .set('Authorization', 'bearer ' + test.tokens.corporateAdmin1.token);

          expect(response).to.have.status(200);
          expect(response.body).to.have.lengthOf(3);
        } catch (e) {
          expect(false).to.be.ok;
        }
      });
    });

    describe('PATCH /lq/stores/:storeId', function () {
      it('should update the specified store', async function () {
        try {
          const store    = await Store.findOne({name: 'New Store'});
          const response = await test.request.patch(`/api/lq/stores/${store._id}`)
          .set('Authorization', 'bearer ' + test.tokens.corporateAdmin1.token)
          .send({name: 'Updated Store'});

          expect(response).to.have.status(200);
          expect(response.body.name).to.be.equal('Updated Store');
        } catch (e) {
          expect(false).to.be.ok;
        }
      });
    });
  });
});
