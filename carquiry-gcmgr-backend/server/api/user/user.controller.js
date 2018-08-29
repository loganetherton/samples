import User from './user.model';
import ErrorLog from '../errorLog/errorLog.model';

import {signToken} from '../auth/auth.service';

const validationError = function(res, err) {
  return res.status(422).json(err);
};

/**
 * Get list of users
 * restriction: 'admin'
 */
exports.index = function(req, res) {
  User.find({})
    .populate('company')
    .populate('store')
    .then(users => {
      return res.status(200).json(users);
    })
    .catch(async err => {
      await ErrorLog.create({
        body: req.body ? req.body : {},
        params: req.params ? req.params : {},
        method: 'index',
        controller: 'user.controller',
        stack: err ? err.stack : null,
        error: err
      });
      return res.status(500).json(err);
    });
};

/**
 * Creates a new user
 */
export async function create(req, res) {
  try {
    const newUser    = new User(req.body);
    newUser.provider = 'local';
    newUser.role     = req.body.role || 'user';
    const dbUser = await newUser.save();
    const token = signToken(dbUser._id);
    if (dbUser) {
      return res.json({token});
    } else {
      throw new Error();
    }
  } catch (err) {
    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'create',
      controller: 'user.controller',
      stack: err ? err.stack : null,
      error: err
    });
    return res.status(500).json(err);
  }
};

/**
 * Display user details
 */
exports.show = function (req, res) {
  User.findById(req.params.id)
  .then((employee) => {
    return res.json(employee);
  })
  .catch(async err => {
    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'show',
      controller: 'user.controller',
      stack: err ? err.stack : null,
      error: err
    });
    return res.status(500).json(err);
  });
};

/**
 * Deletes a user
 * restriction: 'admin'
 */
exports.destroy = function(req, res) {
  User.findByIdAndRemove(req.params.id, function (err) {
    if (err) {
      return res.status(500).send(err);
    }
    return res.status(204).send();
  });
};

/**
 * Change a users password
 */
exports.changePassword = function(req, res) {
  const userId = req.user._id;
  const oldPass = String(req.body.oldPassword);
  const newPass = String(req.body.newPassword);

  User.findById(userId, function (err, user) {
    if(user.authenticate(oldPass)) {
      user.password = newPass;
      user.save(function(err) {
        if (err) {
          return validationError(res, err);
        }
        return res.status(200).send('');
      });
    } else {
      res.status(403).send('Forbidden');
    }
  });
};

/**
 * Modify a user
 * @param req
 * @param res
 */
exports.modifyUser = (req, res) => {
  const thisUser = req.user;
  const {firstName, lastName, email, password} = req.body;
  // Get user
  return User.findById(req.params.id)
  .then(employee => {
    // No employee
    if (!employee) {
      throw new Error('not-found');
    }
    // Permissions
    if (thisUser.role !== 'admin') {
      if (thisUser.role === 'corporate-admin' && thisUser.company.toString() !== employee.company.toString()) {
        throw new Error('permissions');
      }
      if (thisUser.role === 'manager' && thisUser.store.toString() !== employee.store.toString()) {
        throw new Error('permissions');
      }
    }
    // Update props
    employee.firstName = firstName;
    employee.lastName = lastName;
    employee.email = email;
    if (password) {
      delete employee.hashedPassword;
      employee.password = password;
    }
    return employee.save();
  })
  .then((employee) => {
    return res.json(employee);
  })
  .catch(async err => {
    console.log('**************ERROR IN MODIFY USER**********');
    console.log(err);
    if (err && err.message === 'not-found') {
      return res.status(400).json({
        message: 'Employee not found'
      });
    }
    // permissions error
    if (err && err.message === 'permissions') {
      return res.status(401).json({
        message: 'Invalid permissions'
      });
    }
    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'modifyUser',
      controller: 'user.controller',
      stack: err ? err.stack : null,
      error: err
    });
    return res.status(err).json(err);
  });
};

/**
 * Change a user's role
 */
exports.changeRole = (req, res) => {
  const id = req.params.id;
  const role = req.params.role;
  User.findById(id, (err, user) => {
    // No user
    if (!user) {
      return res.status(500).json({
        error: 'User not found'
      });
    }
    // Error
    if (err) {
      return res.json(err);
    }
    // Update role and save
    user.role = role;
    user.save((err) => {
      if (err) {
        return validationError(res, err);
      }
      return res.status(200).send();
    });
  });
};

/**
 * Retrieve employee info
 */
exports.employee = function(req, res) {
  const userId = req.user._id;
  User.findById(userId)
  .populate('company')
  .populate('store')
  .then((employee) => {
    return res.json(employee);
  })
  .catch(async err => {
    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'employee',
      controller: 'user.controller',
      stack: err ? err.stack : null,
      error: err
    });
    return res.status(400).json(err);
  });
};

/**
 * Authentication callback
 */
exports.authCallback = function(req, res) {
  res.redirect('/');
};
