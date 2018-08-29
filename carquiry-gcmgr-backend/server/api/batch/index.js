import * as express from 'express';

const controller = require('./batch.controller');
const auth = require('../auth/auth.service');

const router = express.Router();

// All customers
router.get('/all', auth.hasRole('admin'), controller.getAllBatches);

module.exports = router;
