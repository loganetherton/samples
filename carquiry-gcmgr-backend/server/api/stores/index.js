import * as express from 'express';

const controller = require('./store.controller');
const auth = require('../auth/auth.service');

const router = express.Router();
// Get store receipts
router.get('/:storeId/receipts', auth.hasRole('employee'), controller.getReceipts);

module.exports = router;
