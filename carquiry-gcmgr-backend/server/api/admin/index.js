import * as express from 'express';
import * as multer from 'multer';
import validationRules from './validationRules'

const upload = multer({dest: __dirname + '/uploads/'});

const controller = require('./admin.controller');
const auth = require('../auth/auth.service');

const router = express.Router();

// Get denials since the last time reconciliation was completed
router.get('/denials/begin/:begin/end/:end/:pageSize/:page', auth.hasRole('admin'), controller.getDenials);
// Get mass update logs
router.get('/getLogs/:type/:begin/:end', auth.hasRole('admin'), controller.getLogs);
// Get callback logs
router.get('/getCallbackLogs/:begin/:end', auth.hasRole('admin'), controller.getCallbackLogs);
// Set card status
router.post('/setCardStatus', auth.hasRole('admin'), controller.setCardStatus);
// Recreate rejection history
router.post('/recreateRejectionHistory', auth.hasRole('admin'), controller.recreateRejectionHistory);
// Add deduction
router.post('/addDeduction', auth.hasRole('admin'), controller.addDeduction);
// Fill in system time
router.post('/systemTime', auth.hasRole('admin'), controller.systemTime);
router.post('/testCallback', controller.testCallback);
// Fix BI log duplications
router.post('/biLog/fixDuplications', auth.hasRole('admin'), controller.fixBiLogDuplications);
// Fix inventory duplications
router.post('/inventory/fixDuplications', auth.hasRole('admin'), controller.fixInventoryDuplications);
// Recalculate transactions
router.post('/recalculate/transactions', auth.hasRole('admin'), controller.recalculateTransactions);
// Seperate out API customers by company
router.post('/lq/customer/fix', auth.hasRole('admin'), controller.fixLqApiCustomerCompany);
// Fix the bad manual balance checking calls
router.post('/setBalanceFixes', auth.hasRole('admin'), controller.setBalanceFixes);
// Record frontend errors
router.post('/frontendError', controller.frontendError);
// Send payment initiated callbacks
router.put('/callbacks/:type', auth.hasRole('admin', true, validationRules), controller.sendCallbackFromActivity);
// Clean up BI request logs
router.put('/cleanUpBILogs', auth.hasRole('admin'), controller.cleanUpBILogs);
// Clean up cards
router.put('/cleanUpCards', auth.hasRole('admin'), controller.cleanUpCards);
// Send emails
router.post('/sendAccountingEmail/:companyId', auth.hasRole('admin', true, validationRules), controller.sendAccountingEmail);
// Rebuild Elasticsearch indices
router.post('/rebuild-elasticsearch-indices', auth.hasRole('admin'), controller.rebuildElasticsearchIndices);
// Sync cards with BI
router.put('/syncCardsWithBi', auth.hasRole('admin'), upload.array('file', 1), controller.syncCardsWithBi);
// Fix cards which have the incorrect retailer associated with them, put them back into the queue
router.post('/fixRetailer', auth.hasRole('admin'), controller.fixRetailer);

router.get('/inventories/:inventory/history', auth.hasRole('admin', true, validationRules), controller.loadInventoryLogs);
router.post('/inventories/:inventory/revert/:log', auth.hasRole('admin', true, validationRules), controller.revertInventory);

module.exports = router;
