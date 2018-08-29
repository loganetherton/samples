import * as express from 'express';
import {prepareValidationRules} from '../../helpers/validation';
import validationRules from './validationRules';

const controller = require('./callbackLog.controller');
const auth = require('../auth/auth.service');

const router = express.Router();

// Get all callback logs
router.get('/', auth.isAuthenticated(), controller.getCallbacksInDateRange);
// Force re-run of callbacks which have already been sent based on a list
router.post('/reFire/:callbackType/list', auth.isAuthenticated(), controller.refireCallbackFromList);
// Re-run callbacks for a card which should have been sent but weren't
router.post('/reFire/:cardId/:callbackType', auth.isAuthenticated(true, validationRules), controller.reFireCallback);
// Re-run callbacks for a card which should have been sent but weren't
router.post('/fireAll/:companyId', auth.isAuthenticated(true, validationRules), controller.fireAllCallbacks);
// Get callback logs for a time range
router.get('/:begin/:end', auth.isAuthenticated(true, validationRules), controller.getCallbacksInDateRange);

module.exports = router;
