import {expect} from 'chai';
import * as moment from 'moment';
import TestHelper from '../../tests/helpers';
const test = new TestHelper();

import {
  completeTransactions,
  sellCardsInLiquidation
} from '../inventory/inventory.helpers';

describe('retailer', function () {
  //Init DB
  test.initDb();

  // Create users and cards
  before(async function () {
    await test.createAdminUser();
    await test.createCompanyAndCorporateAdminUser();
    await test.createStoreAndManager();
    // Create employee
    await test.createEmployee();
    // Create a customer
    await test.createCustomer();
    // Create retailer
    await test.createRetailer();
    // Login users
    await test.loginUserSaveToken('admin');
    await test.loginUserSaveToken('corporateAdmin');
    await test.loginUserSaveToken('employee');
    // 2 cards from lq/new
    await test.createCardFromLqNew(1); //$50
    await test.createCardFromLqNew(1); //$50
    // Complete inventories
    await sellCardsInLiquidation();
    return await completeTransactions();
  });

  describe('GET /store/:storeId', function () {

  });

  describe('GET /store/:storeId/min/:minVal', function () {

  });

  /**
   * You will need to use import from from 'fs' to check the file system and make sure that the files were created in the right place
   */
  describe('POST /store/:storeId/min/:minVal/csv', function () {

  });

  describe('GET / and GET /app', function () {

  });

  describe('GET /rates', function () {

  });

  describe('GET /salesStats', function () {

    it('should correctly calculate total count, total balance, and avg balance of cards in inventory', async function () {
      //KM: Basic test coverage to ensure response and proper calculation
      //KM:TODO: Extend test to include all possible calculations.

      const date = new Date(), y = date.getFullYear(), m = date.getMonth();

      return await test.request
      .get('/api/retailers/salesStats/' + moment(new Date(y, m, 1)).format("MM-DD-YYYY") + '/' + moment(new Date(y, m + 1, 0)).format("MM-DD-YYYY"))
      .set('Authorization', `bearer ${test.tokens.corporateAdmin1.token}`)
      .then(async res => {
        expect(res).to.have.status(200);
        expect(res.body.results[0].totalCount).to.be.equal(2);
        expect(res.body.results[0].totalAmtBalance).to.be.equal(100);
        expect(res.body.results[0].avgBalance).to.be.equal(50);
      });

    });

  });

  describe('POST /createLike', function () {

  });

  describe('POST /rates/update', function () {

  });

  describe('POST /:retailerId/gsId', function () {

  });

  describe('POST /:retailerId/setProp', function () {

  });

  describe('POST /toggleDisableForCompany', function () {

  });

  describe('POST /createRetailer', function () {

  });
});
