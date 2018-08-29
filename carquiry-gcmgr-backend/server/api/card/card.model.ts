import {Document, Model, model, Schema, Types} from 'mongoose';
import * as _ from "lodash";
import {updateElasticIndexOnSave} from "../../helpers/elastic";
import {keepOriginalSetter} from "../../helpers/database";
import {ICardUpdate} from "../cardUpdates/cardUpdates.model";
import {IUser} from "../user/user.model";
import {ICustomer} from "../customer/customer.model";
import {IRetailer} from "../retailer/retailer.model";
import {IInventory} from "../inventory/inventory.model";
import {IStore} from "../stores/store.model";
import {ICompany} from "../company/company.model";

export interface ICard extends Document {
  number: string,
  pin: string,
  created: Date,
  userTime: Date,
  balanceStatus: string,
  valid: boolean,
  uid: string,
  lqCustomerName: string,
  buyRate: number,
  buyAmount: number,
  sellRate: number,
  balance: number,
  verifiedBalance: number,
  callbackVb: {
    biComplete: number,
    cardFinalized: number,
    cqPaymentInitiated: number,
    denial: number,
    credit: number
  },
  updates: Types.ObjectId[] & ICardUpdate[],
  user: Types.ObjectId[] & IUser,
  customer: Types.ObjectId & ICustomer,
  retailer: Types.ObjectId & IRetailer & string,
  inventory: Types.ObjectId & IInventory,
  store: Types.ObjectId & IStore,
  company: Types.ObjectId & ICompany,
  merchandise: boolean

  getLast4Digits(): string;

  // This is only used to compare changes that might need to be synced with ES
  _originals: {
    number: string;
    pin: string;
  },
  // For iteration
  [key: string]: any
}

export interface ICardModel extends Model<ICard> {
  getCardWithInventory: (cardId: string|Types.ObjectId) => Promise<ICardModel>;
}

export const CardSchema: Schema = new Schema({
  // Card number
  number: {
    type: String,
    required: true,
    es_fields: {keyword: {type: 'string', index: 'not_analyzed'}}
  },
  // Pin
  pin: {type: String, es_fields: {keyword: {type: 'string', index: 'not_analyzed'}}},
  // When original record is created
  created: {
    type: Date,
    default: Date.now
  },
  // User time when created (this is currently wrong. The timezone which is sent to the backend
  // is converted to UTC on saving to mongo, which renders this useless)
  userTime: {
    type: Date
  },
  balanceStatus: {
    type: String,
    validate: {
      validator: function (v: string) {
        return /^(unchecked|deferred|received|bad|manual)$/.test(v);
      },
      message: 'Balance status must be "unchecked," "deferred," "bad", "received", or manual'
    }
  },
  // Whether a card is valid or not. Assumed to be valid until BI returns invalid
  valid: {
    type: Boolean,
    default: true
  },
  // LQ ID
  uid: String,
  // Customer name coming in from LQ
  lqCustomerName: String,
  // Retailer buy rate at the time of sale. This is not the actual buy rate, which can be overwritten on
  // intake.
  buyRate: {type: Number},
  // Buy amount
  buyAmount: Number,
  // Sell rate at time of card intake after company margins
  sellRate: {type: Number},
  // Balance
  balance: {type: Number},
  // Verified balance (now it's possible to get a verified balance on a card without an inventory), set to 0 for invalid cards
  verifiedBalance: {type: Number},
  // Verified balance at the time of callbacks to prevent duplicates
  callbackVb: {
    biComplete: Number,
    cardFinalized: Number,
    cqPaymentInitiated: Number,
    denial: Number,
    credit: Number
  },
  // Updates
  updates: [{type: Schema.Types.ObjectId, ref: 'CardUpdate'}],
  // User adding the card
  user: [{type: Schema.Types.ObjectId, ref: 'User'}],
  // Customer selling the card
  customer: {type: Schema.Types.ObjectId, ref: 'Customer'},
  // Retailer
  retailer: {type: Schema.Types.ObjectId, ref: 'Retailer', required: true},
  // Inventory
  inventory: {type: Schema.Types.ObjectId, ref: 'Inventory'},
  // Store
  store: {type: Schema.Types.ObjectId, ref: 'Store'},
  // Company
  company: {type: Schema.Types.ObjectId, ref: 'Company'},
  // Whether this is a merchandise card or not. Defaults to false.
  merchandise: {type: Boolean, default: false},
}, {safe: {w: 1}, read: 'primary'});

CardSchema.method('getLast4Digits', function (): string {
  let shortNumber = '';
  if (this.number.length >= 4) {
    shortNumber = this.number.substr(this.number.length - 4);
  } else {
    shortNumber = this.number
  }
  return '****' + shortNumber;
});

// // Indexes
// const indexes = [
//   // Unique inventory index
//   [{inventory: 1}],
//   // @todo
//   [{number: 1, pin: 1, retailer: 1}, {name: 'inventory', unique: true}]
// ];
// // @todo did not work
// createIndexes(CardSchema, indexes);

CardSchema.statics = {
  /**
   * Get card with inventory
   * @param cardId
   * @return {Promise.<*|{path, model}>}
   */
  async getCardWithInventory(cardId: string|Types.ObjectId): Promise<ICardModel> {
    return this.findById(cardId).populate('inventory');
  }
};

['number', 'pin'].forEach(attr => {
  CardSchema.path(attr).set(keepOriginalSetter(attr));
});

CardSchema.pre('save', function (next) {
  this._originals = this._originals || {};
  next();
});

updateElasticIndexOnSave(CardSchema, (doc: ICard) => {
  if (_.isMatch(doc, doc._originals)) {
    return null;
  }

  return {
    body: {
      query: {
        bool: {
          must: [
            {
              match: {
                "card._id": doc._id
              }
            }
          ]
        }
      },
      script: {
        inline: "ctx._source['card']['number'] = params.newNumber; ctx._source['card']['pin'] = params.newPin",
        params: {
          newNumber: doc.number,
          newPin: doc.pin
        }
      }
    },
    index: "inventories",
    type: "inventory"
  };
});

export const Card: ICardModel = model<ICard, ICardModel>('Card', CardSchema);

export default Card;
