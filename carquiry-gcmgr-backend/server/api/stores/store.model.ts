import * as mongoose from 'mongoose';
import {ensureDecimals} from '../../helpers/validation';
import createIndexes from '../../config/indexDb';
import {updateElasticIndexOnSave} from '../../helpers/elastic';
import {redisDelMatch} from '../../helpers/redis';
import {ICompany} from "../company/company.model";
import {IUser} from "../user/user.model";
import {IBuyRate} from "../buyRate/buyRate.model";
import {IReserve} from "../reserve/reserve.model";

const Schema = mongoose.Schema;

export interface IStore extends mongoose.Document {
  name: string;
  address1: string;
  address2: string;
  city: string;
  state: string;
  zip: string;
  phone: string;
  created: Date;
  reconciledTime: Date;
  reconcileCompleteTime: Date;
  creditValuePercentage: number;
  maxSpending: number;
  payoutAmountPercentage: number;
  reserveTotal: number;
  companyId: mongoose.Types.ObjectId & ICompany;
  users: mongoose.Types.ObjectId[] & IUser[];
  buyRateRelations: mongoose.Types.ObjectId[] & IBuyRate[];
  reserves: mongoose.Types.ObjectId[] & IReserve[];
  callbackUrl: string;

  // This is only used to compare changes that might need to be synced with ES
  _originals: {name: string};
}

export interface IStoreModel extends mongoose.Model<IStore> { }

const StoreSchema = new Schema({
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
  phone: String,
  created: {
    type: Date,
    default: Date.now
  },
  // The last time a store was reconciled
  reconciledTime: Date,
  // The last time a store closed a reconciliation for shipment
  reconcileCompleteTime: Date,
  // Credit value percentage
  creditValuePercentage: {type: Number, default: 1.1},
  // Maximum spending in total transaction
  maxSpending: {type: Number, default: 30, get: function (spend?: number) {
    return typeof spend !== 'number' ? 30 : spend;
  }},
  // Payout percentage
  payoutAmountPercentage: {type: Number, default: 0.5},
  // Reserve total
  reserveTotal: {type: Number, default: 0, get: function (total?: number) {
    if (!total) {
      return 0;
    }
    return total;
  }},
  /**
   * References
   */
  companyId: {type: Schema.Types.ObjectId, ref: 'Company'},
  // Company users
  users: [{type: Schema.Types.ObjectId, ref: 'User'}],
  // Buy rate relations
  buyRateRelations: [{type: Schema.Types.ObjectId, ref: 'BuyRate'}],
  // Reserves
  reserves: [{type: Schema.Types.ObjectId, ref: 'Reserve'}],
  // Verified balance received callback URL
  callbackUrl: String
});

// Indexes
const indexes = [
  [{name: 1}],
  [{companyId: 1}],
];
createIndexes(StoreSchema, indexes);

/**
 * Validations
 */

// Validate empty name
StoreSchema
  .path('name')
  .validate(function (name: string) {
    return name.length;
  }, 'Store name cannot be blank');

// Validate duplicate names
StoreSchema
  .path('name')
  .validate({isAsync: true, validator: function(name: string, cb: Function) {
    this.constructor.findOne({
      name,
      companyId: this.companyId
    }, (err: Error, store: IStore) => {
      if (err) {
        throw err;
      }
      if (store) {
        if (this.id === store.id) {
          // Remove from redis for getParamsInRange caching
          redisDelMatch('type*store');
          return cb(true);
        }
        return cb(false);
      }
      redisDelMatch('type*store');
      return cb(true);
    });
  }, message: 'Store name is already taken'});

/**
 * Make sure that margin and service fee are decimals
 */
StoreSchema.pre('validate', function (next) {
  ensureDecimals.call(this, next, ['payoutAmountPercentage', 'creditValuePercentage'], {creditValuePercentage: 2})
});

// Return virtuals
StoreSchema.set('toJSON', {
  virtuals: true
});

StoreSchema.path('name').set(function (newVal: string) {
  this._originals = {name: this.name};
  return newVal;
});

StoreSchema.pre('save', function (next) {
  this._originals = this._originals || {};
  next();
});

updateElasticIndexOnSave(StoreSchema, (doc: IStore) => {
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
                "store._id": doc._id
              }
            }
          ]
        }
      },
      script: {
        inline: "ctx._source['store']['name'] = params.newName",
        params: {
          newName: doc.name
        }
      }
    },
    index: "inventories",
    type: "inventory"
  };
});

export const Store: IStoreModel = mongoose.model<IStore, IStoreModel>('Store', StoreSchema);

export default Store;
