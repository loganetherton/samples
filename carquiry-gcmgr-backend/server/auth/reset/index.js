import * as express from 'express';
import * as mongoose from 'mongoose';

import User from '../../api/user/user.model';
import ResetPasswordToken from '../../api/user/resetPasswordToken.model';

const router = express.Router();

router.post('/', (req, res) => {
  const {id, token, password, confirm} = req.body;

  if (!mongoose.Types.ObjectId.isValid(id)) {
    return res.status(400).json({error: 'Invalid ID.'});
  }

  if (!token) {
    return res.status(400).json({error: 'Missing token.'});
  }

  if (password !== confirm) {
    return res.status(400).json({error: 'Password confirmation does not match.'});
  }

  ResetPasswordToken.findById(id)
    .then(resetPasswordToken => {
      if (!resetPasswordToken) {
        throw new Error('notFound');
      }

      return resetPasswordToken.compareToken(token).then(match => {
        if (match) {
          User.findById(resetPasswordToken.user).then(user => {
            user.password = password;
            user.save();

            resetPasswordToken.remove();

            return res.json();
          });
        } else {
          throw new Error('invalidToken');
        }
      })
        .catch(err => {
          return Promise.reject(err);
        });
    })
    .catch(err => {
      console.log(err);
      if (err && err.message === 'notFound') {
        return res.status(400).json({error: 'Token not found.'});
      }

      if (err && err.message === 'invalidToken') {
        return res.status(400).json({error: 'Invalid token.'});
      }

      console.log('*******************ERROR IN RESET PASSWORD*******************');
      console.log(err);

      return res.status(500).json({error: 'Something went wrong.'});
    });
});

module.exports = router;
