import * as mongoose from 'mongoose';
import createIndexes from '../../config/indexDb';
import config from '../../config/environment';
import {updateElasticIndexOnSave} from '../../helpers/elastic';
import {redisDelMatch} from '../../helpers/redis';
import {CompanySettings, ICompanySettings} from './companySettings.model';

const Schema = mongoose.Schema;

export interface ICompany extends mongoose.Document {
  name: string;
  address1: string;
  address2: string;
  city: string;
  state: string;
  zip: string;
  url: string;
  created: Date;
  apis: {
    bi: boolean;
    lq: boolean;
    dgc: boolean;
  };
  disabledRetailers: string[];
  users: mongoose.Types.ObjectId[];
  stores: mongoose.Types.ObjectId[];
  settings: mongoose.Types.ObjectId;
  cardBuyId: string;
  cardBuyCustomerId: string;
  cardBuyCcId: string;
  reserveTotal: number;
  bookkeepingemails: string;
  reserves: mongoose.Types.ObjectId[];

  getSettings: (returnPlainObject?: boolean) => Promise<any>;
  getSettingsObject: () => Promise<ICompanySettings>;
  getMargin: () => number;

  // This is only used to compare changes that might need to be synced with ES
  _originals: {
    name: string;
  };
}

export interface ICompanyModel extends mongoose.Model<ICompany> { }

const CompanySchema = new Schema({
  // Company name
  name: {
    type: String,
    required: true,
    es_fields: {keyword: {type: 'string', index: 'not_analyzed'}}
  },
  address1: String,
  address2: String,
  city: String,
  state: String,
  zip: String,
  // Company URL
  url: String,
  created: {
    type: Date,
    default: Date.now
  },
  apis: {
    bi: {type: Boolean, default: false},
    lq: {type: Boolean, default: false},
    dgc: {type: Boolean, default: false}
  },
  // Disabled retailers
  disabledRetailers: [String], // lol not object ID
  // Company users
  users: [{type: Schema.Types.ObjectId, ref: 'User'}],
  // Stores
  stores: [{type: Schema.Types.ObjectId, ref: 'Store'}],
  // Company settings
  settings: {type: Schema.Types.ObjectId, ref: 'CompanySettings'},
  // Tango ID
  cardBuyId: String,
  // Card buy
  cardBuyCustomerId: String,
  // CC id
  cardBuyCcId: String,
  // Reserve total
  reserveTotal: {type: Number, default: 0, get: function (total: number) {
    if (!total) {
      return 0;
    }
    return total;
  }},
  // Bookkeeping emails
  bookkeepingEmails: {type: String, get: function (emails: string) { return emails || ''; }},
  // Reserves
  reserves: [{type: Schema.Types.ObjectId, ref: 'Reserve'}]
});

// Indexes
const indexes = [
  [{name: 1}],
  [{reserves: 1}],
];
createIndexes(CompanySchema, indexes);

/**
 * Validations
 */

// Validate empty name
CompanySchema
  .path('name')
  .validate(function (name: string) {
    return name.length;
  }, 'Company name cannot be blank');

// Validate duplicate names
CompanySchema
  .path('name')
  .validate({isAsync: true, validator: function(name: string, cb: Function) {
    this.constructor.findOne({name}, (err: Error, company: ICompany) => {
      if (err) {
        throw err;
      }
      if (company) {
        if (this.id === company.id) {
          // Remove from redis for getParamsInRange caching
          redisDelMatch('type*company');
          return cb(true);
        }
        return cb(false);
      }
      // For when name
      redisDelMatch('type*company');
      return cb(true);
    });
  }, message: 'Company name is already taken'});

/**
 * Retrieve settings for a company
 * @param returnPlainObject Return a plain object with company settings rather than a Mongoose model
 */
CompanySchema.methods.getSettings = async function (returnPlainObject = true) {
  let settings;
  settings = await (this.model('CompanySettings')).findOne({company: this._id});
  // If no settings, create a new one
  if (!settings) {
    settings = new (this.model('CompanySettings'))({
      company: this._id
    });
    settings = await settings.save();
    this.settings = settings._id;
    this.save();
    const autoBuyRates = await settings.getAutoBuyRates();
    settings = settings.toObject();
    settings.autoBuyRates = autoBuyRates;
  // Return settings
  } else {
    settings.customerDataRequired = typeof settings.customerDataRequired === 'undefined' ? true : settings.customerDataRequired;
    const autoBuyRates = await settings.getAutoBuyRates();
    settings = settings.toObject();
    settings.autoBuyRates = autoBuyRates;
  }
  // Return plain object with auto buy rates filled in
  if (returnPlainObject) {
    return settings;
  // Return Mongoose model for settings manipulation
  } else {
    return await (this.model('CompanySettings')).findOne({company: this._id}).populate({
      path: 'settings',
      populate: {
        path: 'autoBuyRates',
        model: 'AutoBuyRate'
      }
    });
  }
};

/**
 * Get settings as mongoose object
 * @returns {Promise}
 */
CompanySchema.methods.getSettingsObject = function () {
  return new Promise(resolve => {
    let settings;
    // Get company settings
    (this.model('CompanySettings')).findOne({company: this._id}, (err: Error, dbSettings: ICompanySettings) => {
      settings = dbSettings;
      // If no settings, create a new one
      if (!dbSettings) {
        new CompanySettings({
          company: this._id
        })
        .save((err: Error, dbSettings: ICompanySettings) => {
          settings = dbSettings;
          this.settings = dbSettings._id;
          this.save();
          return resolve(settings);
        });
        // Return settings
      } else {
        settings.customerDataRequired = typeof settings.customerDataRequired === 'undefined' ? true : settings.customerDataRequired;
      }
      resolve(settings);
    });
  });
};

/**
 * Retrieve company margin
 */
CompanySchema.methods.getMargin = function () {
  let thisMargin;
  try {
    thisMargin = this.settings.margin;
  } catch (e) {
    thisMargin = config.margin;
  }
  return thisMargin;
};

CompanySchema.set('toJSON', {getters: true});
CompanySchema.set('toObject', {getters: true});

CompanySchema.path('name').set(function (newVal: string) {
  this._originals = {name: this.name};
  return newVal;
});

CompanySchema.pre('save', function (next) {
  this._originals = this._originals || {};
  next();
});

updateElasticIndexOnSave(CompanySchema, (doc: ICompany) => {
  if (typeof doc._originals.name !== 'string' || doc.name === doc._originals.name) {
    return null;
  }

  return {
    body: {
      query: {
        bool: {
          must: [
            {
              match: {
                "company._id": doc._id
              }
            }
          ]
        }
      },
      script: {
        inline: "ctx._source['company']['name'] = params.newName",
        params: {
          newName: doc.name
        }
      }
    },
    index: "inventories",
    type: "inventory"
  };
});

export const Company: ICompanyModel = mongoose.model<ICompany, ICompanyModel>('Company', CompanySchema);

export default Company;
