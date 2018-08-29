import * as express from 'express';

const controller = require('./bi.controller');
const auth = require('../auth/auth.service');

const router = express.Router();

// Get pending cards currently in bi
router.get('/pending/:row/:direction/:dateBegin/:dateEnd', auth.hasRole('admin'), controller.getPendingCards);
// Get pending cards currently in bi for a single retailer
router.get('/pending/:retailer', auth.hasRole('admin'), controller.getPendingCards);
// Set balance for a card
router.post('/setBalance', auth.hasRole('admin'), controller.setBalance);
// Set balance for multiple cards
router.post('/setBalance/batch', auth.hasRole('admin'), controller.setBalanceBatch);
// Reinsert cards which exist here but not in BI
router.post('/reinsertBiReceiver', auth.hasRole('admin'), controller.reinsertBi);


module.exports = router;
