import {expect} from 'chai';

import TestHelper from '../../tests/helpers';
import Card from '../card/card.model';
import Inventory from '../inventory/inventory.model';
import Batch from '../batch/batch.model';

import {
  completeTransactions,
  sellCardsInLiquidation
} from '../inventory/inventory.helpers';

const test = new TestHelper();

describe('company.controller.js', function () {
  // Init DB for card controller
  test.initDb();
  // Init company and admin user
  before(async function () {
    // Create admin
    await test.createAdminUser();
    // Company and corporate admin
    await test.createCompanyAndCorporateAdminUser();
    await test.createCompanyAndCorporateAdminUser(2);
    // Create store
    await test.createStoreAndManager();
    await test.createStoreAndManager(2);
    // Create employee
    await test.createEmployee();
    await test.createEmployee(2);
    // Create a customer
    await test.createCustomer();
    await test.createCustomer(2);

    const smpMaxMin = {
      cardCash: {
        max: 50,
        min: 0
      },
      cardPool: {
        max: 100,
        min: 10
      },
      giftcardZen: {
        max: null,
        min: 100
      }
    };
    // Create 2 retailers
    await test.createRetailer({name: 'Retailer1', smpMaxMin});
    return await test.createRetailer({name: 'Retailer2', smpMaxMin});
  });

  describe('GET /activity/begin/:beginDate/end/:endDate/:perPage/:offset', function () {
    // Create cards
    before(async function () {
      try {
        // Get logins
        await test.loginUserSaveToken('employee');
        await test.loginUserSaveToken('employee', 2);
        // 2 cards from UI
        await test.createCardFromUi(1);
        await test.createCardFromUi(2);
        // Add cards from UI to inventory
        await test.addCardsToInventory(1);
        await test.addCardsToInventory(2);
        // 2 cards from lq/new
        await test.createCardFromLqNew(1);
        await test.createCardFromLqNew(2);
        // 2 cards from lq/transactions
        await test.createCardFromTransaction({}, 1);
        await test.createCardFromTransaction({}, 2);
        // Complete inventories
        await sellCardsInLiquidation();
        return await completeTransactions();
      } catch (e) {
        console.log(e);
      }
    });

    it('should successfully create cards and inventories using UI, lq/new, and lq/transactions', async function () {
      expect(true).to.be.equal(true);
      const cards = await Card.find().populate('inventory');
      expect(cards).to.have.lengthOf(6);
      cards.forEach(card => {
        expect(card).to.have.property('inventory');
      });
    });
  });

  describe('/:companyId/store/:storeId/reconcile', function () {
    it('should reconcile sold cards', async function () {
      await test.reconcile();
      const company = test.getDefaultReferenceId('companies', 1);
      const inventories = await Inventory.find({company}).populate('reconciliation');
      inventories.forEach(inventory => {
        expect(inventory.reconciliation.reconciliationComplete).to.not.be.ok;
      });
    });
  });

  describe('/:companyId/store/:storeId/markAsReconciled', function () {
    it('should mark cards as reconciled', async function () {
      await test.markAsReconciled();
      const company = test.getDefaultReferenceId('companies', 1);
      const batch = await Batch.find({company});
      const inventories = await Inventory.find({company}).populate('reconciliation');
      expect(inventories).to.have.length(3);
      inventories.forEach(inventory => {
        expect(inventory.batch).to.be.ok;
        expect(inventory.reconciliation.reconciliationComplete).to.be.ok;
      });
      expect(batch).to.have.length(1);
      expect(batch[0].inventories).to.have.length(3);
    });
  });

  // describe('GET /company/:companyId/receipts/:perPage/:offset', function () {
  //   before(async function () {
  //     // Login as employee1
  //     await test.loginUserSaveToken('employee', 1);
  //   });
  //
  //   it('should return array of receipts', async function() {
  //     return await test.request
  //       .get(`/api/companies/${test.companies[0]._id}/receipts/10/0`)
  //       .set('Authorization', `bearer ${test.tokens.employee1.token}`)
  //       .then(async res => {
  //         expect(res.body.data).to.be.an('array');
  //         expect(res.body.data.length).to.be.equal(4);
  //         expect(res.body.pagination).to.be.an('object');
  //         expect(res.body.pagination.total).to.be.equal(4);
  //       });
  //   });
  // });
});
