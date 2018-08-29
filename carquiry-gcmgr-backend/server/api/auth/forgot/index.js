import User from '../../user/user.model';
import ResetPasswordToken from '../../user/resetPasswordToken.model';
import mailer from '../../mailer';
import ErrorLog from '../../errorLog/errorLog.model';

import * as express from 'express';

const router = express.Router();
const config = require('../../../config/environment');

// Token lifespan in milliseconds
const tokenLifespan = 60 * 60 * 1000;

router.post('/', (req, res) => {
  const {email} = req.body;

  User.findOne({email})
  .then(user => {
    if (! user) {
      throw new Error('notFound');
    }

    const resetPassword = new ResetPasswordToken({
      user: user._id
    });

    const token = resetPassword.generateToken(function () {
      resetPassword.save();
    });

    let resetLink = config.frontendBaseUrl;
    resetLink += 'auth/reset-password?id=' + resetPassword._id;
    resetLink += '&token=' + token;

    mailer.sendResetPasswordEmail(email, {resetLink}, async function (error) {
      if (error) {

        console.log('*****************ERROR WITH SENDGRID*****************');
        console.log(error);
        if (error.response && error.response.body && error.response.body.errors) {
          console.log('**************ERROR OBJECT**********');
          console.log(error.response.body.errors);
          await ErrorLog.create({
            body: req.body ? req.body : {},
            params: req.params ? req.params : {},
            method: 'sendResetPasswordEmail',
            controller: 'auth.forgot',
            stack: error.stack,
            error: error.response.body.errors,

          });
        } else {
          await ErrorLog.create({
            body: req.body ? req.body : {},
            params: req.params ? req.params : {},
            method: 'sendResetPasswordEmail',
            controller: 'auth.forgot',
            stack: error.stack,
            error: error,

          });
        }
      }
    });

    setTimeout(function () {
      resetPassword.remove();
    }, tokenLifespan);

    return res.json({});
  })
  .catch(async err => {

    if (err && err.message === 'notFound') {
      return res.status(400).json({error: 'User not found.'});
    }

    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'forgotpassword',
      controller: 'auth.forgot',
      stack: err ? err.stack : null,
      error: err,

    });

    console.log('*****************ERROR IN FORGOTPASSWORD*****************');
    console.log(err);

    return res.status(500).json({error: 'Something went wrong.'});
  });
});

module.exports = router;
