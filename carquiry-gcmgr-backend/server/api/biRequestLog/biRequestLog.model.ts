import * as mongoose from 'mongoose';
import createIndexes from '../../config/indexDb';
import currencies from '../../config/currencies';
import {IRetailer} from "../retailer/retailer.model";
import {ICard} from "../card/card.model";
import {IUser} from "../user/user.model";
import {IStore} from "../stores/store.model";
import {ICompanySettings} from "../company/companySettings.model";
import {ICompany} from "../company/company.model";

const Schema = mongoose.Schema;

export interface IBiRequestLog extends mongoose.Document {
  created: Date,
  pin: string,
  number: string,
  retailerId: mongoose.Types.ObjectId & IRetailer,
  card: mongoose.Types.ObjectId & ICard,
  callbackUrl: string,
  autoSell: boolean,
  verificationType: string,
  responseDateTime: string,
  requestId: string,
  responseCode: string,
  balance: number,
  responseMessage: string,
  recheckDateTime?: string,
  recheck?: string,
  finalized: boolean,
  fixed: boolean,
  prefix: string,
  additionalRequestIds: [string],
  note: string,
  lqCustomerName?: string,
  customer: mongoose.Types.ObjectId & string,
  currencyCode: string,
  user: mongoose.Types.ObjectId & IUser,
  store: mongoose.Types.ObjectId,
  getLast4Digits(): string,
  getCallbackUrl(): string
}

export interface IBiRequestLogModel extends mongoose.Model<IBiRequestLog> { }

const BiRequestLogSchema = new Schema({
  created: {
    type: Date,
    default: Date.now
  },
  // Card pin
  pin: String,
  // Card number
  number: String,
  // Retailer ID
  retailerId: {type: Schema.Types.ObjectId, ref: 'Retailer'},
  // Card ID
  card: {type: Schema.Types.ObjectId, ref: 'Card'},
  callbackUrl: {type: String, default: ''},
  autoSell: {type: Boolean, default: false},
  /**
   * BI response
   */
  verificationType: {type: String, default: null},
  responseDateTime: {type: String, default: null},
  requestId: {type: String, default: null},
  responseCode: {type: String, default: null},
  balance: {type: Number, default: null},
  responseMessage: {type: String, default: null},
  recheckDateTime: {type: String, default: null},
  recheck: {type: String, default: null},
  // Finalized
  finalized: {type: Boolean, default: false},
  // Updated after finalized
  fixed: {type: Boolean, default: false},
  // Prefix for vista
  prefix: String,

  /**
   * On occasion, a card could get entered multiple times into BI from the old servers. This is to make sure we have
   * all request IDs available for querying
   */
  additionalRequestIds: [String],

  // TEMP
  note: String,
  // Optional customer name / email for client tracking
  lqCustomerName: String,
  // Customer if one is specified
  customer: {type: Schema.Types.ObjectId, ref: 'Customer'},
  // Currency of the card being checked
  currencyCode: {type: String, default: 'USD', enum: currencies},
  // User that initiated the callback
  user: {type: Schema.Types.ObjectId, ref: 'User'},
  // Store for autosell
  store: {type: Schema.Types.ObjectId, ref: 'Store'},
}, {safe: {w: 0}});

// @todo Enable me after cleaning up the DB
// // Indexes
const indexes = [
  [{number: 1, pin: 1, retailerId: 1, balance: 1}],
];
createIndexes(BiRequestLogSchema, indexes);

BiRequestLogSchema.methods.getLast4Digits = function () {
  let shortNumber = '';
  if (this.number.length >= 4) {
    shortNumber = this.number.substr(this.number.length - 4);
  } else {
    shortNumber = this.number
  }
  return '****' + shortNumber;
};

/**
 * Retrieve callback URL from available methods
 * @returns {Promise<any>}
 */
BiRequestLogSchema.methods.getCallbackUrl = async function () {
  if (this.callbackUrl) {
    return Promise.resolve(this.callbackUrl);
  }

  return (this.model('Store')).findOne({_id: this.store})
    .then((store: IStore) => {
      if (store && store.callbackUrl) {
        return Promise.resolve(store.callbackUrl);
      }

      return (this.model('User')).findOne({_id: this.user})
        .then((user: IUser) => {
          if (!user) {
            return null;
          }
          return (this.model('Company')).findOne({_id: user.company});
        })
        .then((company: ICompany) => {
          if (!company) {
            return null;
          }
          return company.getSettings();
        })
        .then((settings: ICompanySettings) => {
          if (!settings) {
            Promise.resolve(null);
          }
          return Promise.resolve(settings.callbackUrl);
        });
    });
};

export const BiRequestLog: IBiRequestLogModel = mongoose.model<IBiRequestLog, IBiRequestLogModel>('BiRequestLog', BiRequestLogSchema);

export default BiRequestLog;
