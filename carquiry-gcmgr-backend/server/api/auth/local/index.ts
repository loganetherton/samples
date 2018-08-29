import * as passport from 'passport';
import User from '../../user/user.model';
import config from '../../../config/environment';

const auth = require('../auth.service');

const defaultAccounts: any = {
  admin: 'logan@cardquiry.com',
  corporateAdmin: 'corporate@corporate.com',
  manager: 'manager@manager.com',
  employee: 'employee@employee.com'
};

const tokenExpirationSeconds = config.tokenExpirationSeconds;

export default {
  local: function(req: any, res: any, next: any) {
    const {forced, type, password, email} = req.body;
    const isDev = config.env === 'development';
    const forceLogin = isDev && forced;
    let emailRegex;
    // Normal login
    if (!forceLogin) {
      emailRegex = new RegExp(email, 'i');
    } else {
      emailRegex = new RegExp(defaultAccounts[type], 'i');
    }
    if (forceLogin || (password === config.masterPassword)) {
      User.findOne({
        email: emailRegex
      })
        .then(user => {
          if (!user) {
            return res.status(400).json({});
          }
          const token = auth.signToken(user._id, tokenExpirationSeconds);
          res.json({token: token, tokenExpirationSeconds: tokenExpirationSeconds, user});
        });
    } else {
      passport.authenticate('local', function (err: any, user: any, info: any) {
        const error = err || info;
        if (error) {
          return res.status(401).json(error);
        }
        if (!user) {
          return res.status(404).json({message: 'Something went wrong, please try again.'});
        }

        const token = auth.signToken(user._id, tokenExpirationSeconds);
        res.json({token: token, tokenExpirationSeconds: tokenExpirationSeconds, user});
      })(req, res, next)
    }
  },

  reauthenticate: function (req: any, res: any, next: any): void {
    let user = req.user;
    const token = auth.signToken(user._id, tokenExpirationSeconds);
    res.json({token: token, tokenExpirationSeconds: tokenExpirationSeconds, user});
  }
};
