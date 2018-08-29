import * as express from 'express';
import User from '../user/user.model';
import {default as localController} from './local';

const config = require('../../config/environment');
const auth = require('./auth.service');

// Passport Configuration
require('./local/passport').setup(User, config);
//require('./facebook/passport').setup(User, config);
//require('./google/passport').setup(User, config);
//require('./twitter/passport').setup(User, config);

const router = express.Router();

router.use('/local', localController.local);
// router.use('/local', require('./local'));
//router.use('/facebook', require('./facebook'));
//router.use('/twitter', require('./twitter'));
//router.use('/google', require('./google'));

router.use('/reauthenticate', auth.isAuthenticated(), localController.reauthenticate);

router.use('/logout', auth.isAuthenticated(), require('./logout'));

router.use('/forgot-password', require('./forgot'));
router.use('/reset-password', require('./reset'));

module.exports = router;
