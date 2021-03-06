import * as express from 'express';
import environment from '../../config/environment';
import validation from './validationRules';
import {checkStructuredValidation} from '../../helpers/validation';

const auth = require('../auth/auth.service');
const controller = require('./lq.controller');
const adminController = require('../admin/admin.controller');

const router = express.Router();

/**
 * LQ authentication
 * @done
 */
router.post('/account/create', auth.hasRole('admin', validation), controller.createAccount);
router.post('/account/create/user', auth.isAuthenticated(validation), controller.createSubAccount);
router.post('/login', controller.authenticateLq);
/**
 * Retailers
 * @done
 */
router.get('/retailers', auth.isAuthenticated(), controller.getRetailers);
router.get('/retailers/:retailer', auth.isAuthenticated(true), controller.getRetailer);
/**
 * Get card status
 */
// @todo
router.get('/status/:cardId', auth.isAuthenticated(), controller.getCardStatus);
// Get status of all cards, sorted by date
router.get('/status', auth.isAuthenticated(), controller.getCardStatus);
// Get status of all cards after date
router.get('/status/begin/:begin/end/:end', auth.isAuthenticated(), controller.getCardStatus);
router.get('/status/begin/:begin', auth.isAuthenticated(), controller.getCardStatus);
router.get('/status/end/:end', auth.isAuthenticated(), controller.getCardStatus);
/**
 * Sell
 * @todo
 */
// New card
router.post('/new', auth.isAuthenticated(true, validation), controller.lqNewCard);
// New transaction
router.post('/transactions', auth.isAuthenticated(true, validation), controller.newTransaction);
// Reconcile
router.patch('/reconcile', auth.isAuthenticated(), controller.reconcile);
// Proceed with sale
router.patch('/companies/:companyId/card/:cardId/proceed-with-sale', auth.hasRole('manager'), controller.proceedWithSale);
/**
 * BI
 * @todo
 */
// Balance inquiry
router.post('/bi', auth.isAuthenticated(validation), controller.bi);
// BI completed
router.post('/bi/:requestId', checkStructuredValidation(validation), controller.biCompleted);
// Reinsert BI records
router.post('/bi/reinsert/:begin/:end', auth.hasRole('employee'), controller.reinsertBi);
/**
 * Company
 * @done
 */
router.get('/companies/:companyId', auth.hasRole('corporate-admin'), controller.getCompanyInfo);
router.get('/companies/:companyId/settings', auth.hasRole('corporate-admin'), controller.getCompanySettings);
router.patch('/companies/:companyId/settings', auth.hasRole('corporate-admin'), controller.updateCompanySettings);
/**
 * Customer management
 * @done
 */
router.post('/stores/:storeId/customers', auth.hasRole('manager'), controller.newCustomer);
router.patch('/stores/:storeId/customers/:customerId', auth.hasRole('manager'), controller.updateCustomer);
router.get('/stores/:storeId/customers', auth.isAuthenticated(), controller.getStoreCustomers);
router.get('/stores/:storeId/customers/search/:customerName', auth.isAuthenticated(), controller.searchCustomers);
router.get('/stores/:storeId/customers/:customerId', auth.isAuthenticated(), controller.getCustomer);
router.delete('/stores/:storeId/customers/:customerId', auth.hasRole('manager'), controller.deleteCustomer);
/**
 * Store management
 * @done
 */
router.get('/stores', auth.hasRole('corporate-admin'), controller.getStores);
router.post('/stores', auth.hasRole('corporate-admin'), controller.createStore);
router.patch('/stores/:storeId', auth.hasRole('corporate-admin'), controller.updateStore);
router.get('/stores/:storeId', auth.hasRole('corporate-admin'), controller.getStore);
router.delete('/stores/:storeId', auth.hasRole('corporate-admin'), controller.deleteStore);
/**
 * Employee management
 * @done
 */
router.get('/stores/:storeId/employees', auth.hasRole('manager'), controller.getEmployees);
router.post('/stores/:storeId/employees', auth.hasRole('manager'), controller.createEmployee);
router.get('/stores/:storeId/employees/:employeeId', auth.hasRole('manager'), controller.getEmployee);
router.patch('/stores/:storeId/employees/:employeeId', auth.hasRole('manager'), controller.updateEmployee);
router.delete('/stores/:storeId/employees/:employeeId', auth.hasRole('manager'), controller.deleteEmployee);

// Reset
router.post('/reset/transactions', auth.hasRole('admin'), controller.resetTransactions);

// Mock credit/reject
// if (environment.isStaging) {
//   router.post('/mock/reject', auth.hasRole('employee'), controller.mockCreditReject);
//   // Test callbacks stage
//   router.put('/callbacks/:type', auth.hasRole('corporate-admin'), function (req, res, next) {
//     req.body.skipCardValidation = true;
//     next();
//   }, adminController.sendCallbackFromActivity);
// }

module.exports = router;
