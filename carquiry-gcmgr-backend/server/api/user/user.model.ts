import * as mongoose from 'mongoose';
import * as crypto from 'crypto';
import createIndexes from '../../config/indexDb';
import {updateElasticIndexOnSave} from '../../helpers/elastic';
import {keepOriginalSetter} from '../../helpers/database';
import * as  _ from 'lodash';
import {ICompany} from "../company/company.model";
import {IStore} from "../stores/store.model";

const Schema = mongoose.Schema;
const authTypes = ['github', 'twitter', 'facebook', 'google'];

export interface IUser extends mongoose.Document {
  firstName: string;
  lastName: string;
  email: string;
  enabled: boolean;
  role: string;
  company: mongoose.Types.ObjectId & ICompany;
  store: mongoose.Types.ObjectId & IStore;
  created: Date;
  hashedPassword: string;
  provider: string;
  salt: string;
  password: string;
  fullName: string;
  token: {
    _id: mongoose.Types.ObjectId;
    role: string;
  };
  profile: {
    _id: mongoose.Types.ObjectId;
    email: string;
    firstName: string;
    lastName: string;
  };

  authenticate: (plainText: string) => boolean;
  makeSalt: () => string;
  encryptPassword: (password: string) => string;

  // This is only used to compare changes that might need to be synced with ES
  _originals: {
    firstName: string;
    lastName: string;
    fullName: string;
  };
}

export interface IUserModel extends mongoose.Model<IUser> { }

const UserSchema = new Schema({
  firstName: {
    type: String,
    required: true,
    es_fields: {keyword: {type: 'string', index: 'not_analyzed'}}
  },
  lastName: {
    type: String,
    required: true,
    es_fields: {keyword: {type: 'string', index: 'not_analyzed'}}
  },
  email: {
    type: String,
    required: true,
    lowercase: true,
    validate: {
      validator: (value: string) => /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i.test(
        value),
      message: 'Invalid email'
    }
  },
  // Whether employee is active
  enabled: {type: Boolean, default: true, get: function (enabled: boolean) {return !!enabled;}},
  role: {
    type: String,
    default: 'employee'
  },
  // Company this user belongs to, if any
  company: {
    type: Schema.Types.ObjectId,
    ref: 'Company'
  },
  // Store this user belongs to, if any
  store: {
    type: Schema.Types.ObjectId,
    ref: 'Store'
  },
  created: {
    type: Date,
    default: Date.now
  },
  hashedPassword: {
    type: String,
    required: true
  },
  provider: String,
  salt: String,
});

// Indexes
const indexes = [
  [{firstName: 1}],
  [{lastName: 1}],
  [{email: 1}, {unique: true}],
  [{store: 1}],
];
createIndexes(UserSchema, indexes);

/**
 * Virtuals
 */
UserSchema
  .virtual('password')
  .set(function (password: string) {
    this._password = password;
    this.salt = this.makeSalt();
    this.hashedPassword = this.encryptPassword(password);
  })
  .get(function () {
    return this._password;
  });

UserSchema
  .virtual('fullName')
  .get(function () {
    return `${this.firstName} ${this.lastName}`;
  });

// Non-sensitive info we'll be putting in the token
UserSchema
  .virtual('token')
  .get(function () {
    return {
      '_id': this._id,
      'role': this.role
    };
  });

UserSchema
  .virtual('profile')
  .get(function () {
    return {
      '_id': this.id,
      email: this.email,
      firstName: this.firstName,
      lastName: this.lastName
    }
  });

/**
 * Validations
 */

  // Validate empty email
UserSchema
  .path('email')
  .validate(function (email: string) {
    if (authTypes.indexOf(this.provider) !== -1) {
      return true;
    }
    return email.length;
  }, 'Email cannot be blank');

// Validate role in type user or admin
UserSchema
  .path('role')
  .validate(function (role: string) {
    if (authTypes.indexOf(this.provider) !== -1) {
      return true;
    }
    return ['employee', 'corporate-admin', 'manager', 'admin'].indexOf(role) !== -1;
  }, 'User type must be employee, corporate-admin, manager, or admin');

// Validate empty password
UserSchema
  .path('hashedPassword')
  .validate(function (hashedPassword: string) {
    if (authTypes.indexOf(this.provider) !== -1) {
      return true;
    }
    return hashedPassword.length;
  }, 'Password cannot be blank');

// Validate email is not taken
UserSchema
  .path('email')
  .validate({isAsync: true, validator: function (value: string, cb: Function) {
    const self = this;

    this.constructor.findOne({email: value}, function (err: Error, user: IUser) {
      if (err) {
        throw err;
      }
      if (user) {
        if (self.id === user.id) {
          return cb(true);
        }
        return cb(false);
      }
      return cb(true);
    });
  }, message: 'The specified email address is already in use.'});

const validatePresenceOf = function (value: string) {
  return value && value.length;
};

/**
 * Pre-save hook
 */
UserSchema
  .pre('save', function (next) {
    if (!this.isNew) {
      return next();
    }

    if (!validatePresenceOf(this.hashedPassword) && authTypes.indexOf(this.provider) === -1) {
      next(new Error('Invalid password'));
    } else {
      next();
    }
  });

/**
 * Methods
 */
UserSchema.methods = {
  /**
   * Authenticate - check if the passwords are the same
   *
   * @param {String} plainText
   * @return {Boolean}
   * @api public
   */
  authenticate: function (plainText: string) {
    return this.encryptPassword(plainText) === this.hashedPassword;
  },

  /**
   * Make salt
   *
   * @return {String}
   * @api public
   */
  makeSalt: function () {
    return crypto.randomBytes(16).toString('base64');
  },

  /**
   * Encrypt password
   *
   * @param {String} password
   * @return {String}
   * @api public
   */
  encryptPassword: function (password: string) {
    if (!password || !this.salt) {
      return '';
    }
    const salt = new Buffer(this.salt, 'base64');
    return crypto.pbkdf2Sync(password, salt, 10000, 64, 'SHA1').toString('base64');
  }
};

// Return virtuals
UserSchema.set('toJSON', {
  virtuals: true,
  transform: function (doc: IUser, ret: IUser) {
    delete ret.hashedPassword;
    delete ret.salt;
    return ret;
  }
});


['firstName', 'lastName'].forEach(attr => {
  UserSchema.path(attr).set(keepOriginalSetter(attr));
});

UserSchema.pre('save', function (next) {
  this._originals = this._originals || {};
  next();
});

updateElasticIndexOnSave(UserSchema, (doc: IUser) => {
  const _originals = Object.assign({}, doc._originals);
  delete doc._originals;
  if (_.isMatch(doc, _originals)) {
    return null;
  }

  return {
    body: {
      query: {
        bool: {
          must: [
            {
              match: {
                "user._id": doc._id
              }
            }
          ]
        }
      },
      script: {
        inline: "ctx._source['user']['firstName'] = params.newFirstName; ctx._source['user']['lastName'] = params.newLastName; ctx._source['user']['fullName'] = params.newFullName",
        params: {
          newFirstName: doc.firstName,
          newLastName: doc.lastName,
          newFullName: doc.fullName
        }
      }
    },
    index: "inventories",
    type: "inventory"
  };
});

export const User: IUserModel = mongoose.model<IUser, IUserModel>('User', UserSchema);

export default User;
