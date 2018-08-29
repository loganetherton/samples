import * as mongoose from 'mongoose';
import * as uuid from 'node-uuid';

import config from '../../config/environment';
import {ensureDecimals} from '../../helpers/validation';

import {IAutoBuyRate} from './autoBuyRate.model';

const Schema = mongoose.Schema;

export interface ICompanySettings extends mongoose.Document {
  managersSetBuyRates: boolean;
  autoSetBuyRates: boolean;
  employeesCanSeeSellRates: boolean;
  autoSell: boolean;
  margin: number;
  minimumAdjustedDenialAmount: number;
  created: Date;
  updated: Date;
  company: mongoose.Types.ObjectId;
  autoBuyRates: mongoose.Types.ObjectId;
  cardType: string;
  biOnly: boolean;
  customerDataRequired: boolean;
  useAlternateGCMGR: boolean;
  serviceFee: number;
  callbackUrl: string;
  timezone: string;
  callbackTokenEnabled: boolean;
  callbackToken: string;
  enableCallbackStatus: boolean;
  enableChargebackCallback: boolean;
  validateCard: boolean;
  disableLimits: boolean;
  globalLimits: {
    max: number,
    min: number
  },
  enableCurrencyCode: boolean,
  useBalanceCB: boolean,

  getAutoBuyRates: () => Promise<IAutoBuyRate>;
  [key: string]: any
}

export interface ICompanySettingsModel extends mongoose.Model<ICompanySettings> { }

const CompanySettingsSchema = new Schema({
  // Managers only allowed to set buy rates
  managersSetBuyRates: {type: Boolean, default: false},
  // Auto-set buy rates based on sell-rates
  autoSetBuyRates: {type: Boolean, default: false},
  // Employees can see buy rates
  employeesCanSeeSellRates: {type: Boolean, default: false},
  // Auto-sell cards which are put in inventory
  autoSell: {type: Boolean, default: true},
  // Company margin
  margin: {type: Number, default: 0.03, min: 0, max: 1},
  // Minimum adjusted denial amount allowed to take on a sale
  // So, if a customer owes 500 and this is set to 10%, the adjusted buy amount cannot be set less than $50
  minimumAdjustedDenialAmount: {type: Number, default: 0.1, min: 0, max: 1},

  created: {
    type: Date,
    default: Date.now
  },
  updated: {
    type: Date,
    default: Date.now
  },
  // Company
  company: {type: Schema.Types.ObjectId, ref: 'Company'},
  // Auto buy rates
  autoBuyRates: {type: Schema.Types.ObjectId, ref: 'AutoBuyRate'},
  // Card type
  cardType: {type: String, enum: ['electronic', 'physical', 'both'], default: 'both', get: convertToLowerCase, set: convertToLowerCase},
  // BI only
  biOnly: {type: Boolean, default: false},
  // Must include information on customer when creating (address, phone, etc)
  customerDataRequired: {type: Boolean, default: true},
  // Use alternate GCMGR
  useAlternateGCMGR: {type: Boolean, default: false},
  // Service fee
  serviceFee: {type: Number, get: defaultServiceFee, set: setServiceFee},
  // Callback url for once a VB has been retrieved for a card
  callbackUrl: String,
  // Timezone
  timezone: {type: String, get: getTimezone},
  // Callback token
  callbackTokenEnabled: {type: Boolean, default: false},
  // The current callback token
  callbackToken: {type: String},
  // Enable extra attributes that represent the BI status in the callbacks,
  enableCallbackStatus: Boolean,
  // Enable chargeback callbacks
  enableChargebackCallback: {type: Boolean, default: false},
  // Enable regex validation (if one was set) on cards submitted via the LQ API
  validateCard: {type: Boolean, default: false},
  // Disable SMP min/max limits on for each retailer
  disableLimits: {type: Boolean, default: false},
  // Global max/min for this customer
  globalLimits: {
    max: {type: Number, get: (v: number) => { return v || Number.MAX_SAFE_INTEGER; }},
    min: {type: Number, get: (v: number) => { return v || 0.01; }}
  },
  // Adds currency code to BI callbacks
  enableCurrencyCode: {type: Boolean, default: false},
  // Use balanceCB (for Vista only)
  useBalanceCB: {type: Boolean, default: false}
});

// Updated time
CompanySettingsSchema
  .pre('save', function(next) {
    // Default to sending full card number and status for new companies
    if (this.isNew && typeof this.enableCallbackStatus === 'undefined') {
      this.enableCallbackStatus = true;
    }
    this.updated = new Date();
    if (this.callbackTokenEnabled && !this.callbackToken) {
      this.callbackToken = uuid();
    }
    next();
  });

/**
 * Make sure that margin and service fee are decimals
 */
CompanySettingsSchema.pre('validate', function (next) {
  ensureDecimals.call(this, next, ['margin', 'serviceFee'], {margin: 0.15});
});

CompanySettingsSchema.methods = {
  /**
   * Get auto-buy settings, or create a new one
   */
  getAutoBuyRates: async function () {
    let dbBuyRates = await this.model('AutoBuyRate').findOne({settings: this._id});
    // Create auto buy rate
    if (!dbBuyRates) {
      dbBuyRates = await this.model('AutoBuyRate').create({settings: this._id});
      this.autoBuyRates = dbBuyRates._id;
      this.save();
      return dbBuyRates;
    }
    return dbBuyRates;
  }
};

/**
 * Default to 0.0075 for service fee, unless set
 * @param serviceFee
 * @return {*}
 */
function defaultServiceFee(serviceFee: number) {
  if (!serviceFee) {
    return config.serviceFee;
  }
  return serviceFee;
}
function setServiceFee(serviceFee: number) {
  return serviceFee;
}

// Make sure whatever is returned is lowercase
function convertToLowerCase(whatever: string) {
  if (whatever) {
    return whatever.toLowerCase();
  }
}

function getTimezone(timezone: string) {
  if (timezone) {
    return timezone;
  }

  return 'America/Los_Angeles';
}

CompanySettingsSchema.set('toJSON', {getters: true});
CompanySettingsSchema.set('toObject', {getters: true});

export const CompanySettings: ICompanySettingsModel = mongoose.model<ICompanySettings, ICompanySettingsModel>('CompanySettings', CompanySettingsSchema);

export default CompanySettings;
