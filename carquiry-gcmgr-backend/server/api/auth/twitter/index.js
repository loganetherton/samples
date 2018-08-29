import * as express from 'express';
import * as passport from 'passport';

const auth = require('../auth.service');

const router = express.Router();

router
  .get('/', passport.authenticate('twitter', {
    failureRedirect: '/signup',
    session: false
  }))

  .get('/callback', passport.authenticate('twitter', {
    failureRedirect: '/signup',
    session: false
  }), auth.setTokenCookie);

module.exports = router;
