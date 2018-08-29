import * as mongoose from 'mongoose';
import createIndexes from '../../config/indexDb';
import {updateElasticIndexOnSave} from '../../helpers/elastic';
import {keepOriginalSetter} from '../../helpers/database';
import * as _ from 'lodash';
import {IStore} from '../stores/store.model';
import {ICompany} from "../company/company.model";
import {ICustomerEdit} from "../customerEdit/customerEdit.model";
import {IInventory} from "../inventory/inventory.model";

const Schema = mongoose.Schema;

export interface ICustomer extends mongoose.Document {
  firstName: string;
  lastName: string;
  middleName: string;
  fullName: string;
  stateId: string;
  address1: string;
  address2: string;
  city: string;
  state: string;
  zip: string;
  phone: string;
  systemId: string;
  created: Date;
  rejectionTotal: number;
  enabled: boolean;
  company: mongoose.Types.ObjectId & ICompany;
  store: mongoose.Types.ObjectId[] & IStore[];
  edits: mongoose.Types.ObjectId[] & ICustomerEdit[];
  rejections: mongoose.Types.ObjectId[] & IInventory;
  credits: mongoose.Types.ObjectId[] & IInventory[];
  email: string;

  // This is only used to compare changes that might need to be synced with ES
  _originals: {
    fullName: string;
    phone: string;
    email: string;
  };
}

export interface ICustomerModel extends mongoose.Model<ICustomer> { }

const CustomerSchema = new Schema({
  // First name
  firstName: {
    type: String,
    required: true
  },
  // last name
  lastName: {
    type: String,
    required: true
  },
  middleName: String,
  fullName: {
    type: String,
    es_fields: {keyword: {type: 'string', index: 'not_analyzed'}}
  },
  // State ID, such as driver's license
  stateId: {
    type: String,
    required: true
  },
  address1: {
    type: String,
    required: true
  },
  address2: {
    type: String
  },
  city: {
    type: String,
    required: true
  },
  state: {
    type: String,
    required: true
  },
  zip: {
    type: String,
    required: true
  },
  phone: {
    type: String,
    required: true
  },
  // System ID, used internally at stores
  systemId: String,
  created: {
    type: Date,
    default: Date.now
  },
  // Rejection total
  rejectionTotal: {
    type: Number, default: 0
  },
  // Whether customer is active
  enabled: {type: Boolean, default: true, get: function (enabled: boolean) {return !!enabled;}},
  // Company on which this customer was created
  company: {type: Schema.Types.ObjectId, ref: 'Company'},
  // Store relationship
  store: [{type: Schema.Types.ObjectId, ref: 'Store'}],
  // Edits
  edits: [{type: Schema.Types.ObjectId, ref: 'CustomerEdit'}],
  // Rejected inventories
  rejections: [{type: Schema.Types.ObjectId, ref: 'Inventory'}],
  // Credited inventories
  credits: [{type: Schema.Types.ObjectId, ref: 'Inventory'}],
  // Email address
  email: String
});

// Indexes
const indexes = [
  [{fullName: 1}],
  [{stateId: 1}],
  [{phone: 1}],
  [{systemId: 1}],
  [{company: 1}],
  [{address1: 1}],
  [{city: 1}],
  [{state: 1}],
];
createIndexes(CustomerSchema, indexes);

/**
 * Validations
 */

// Validate empty name
CustomerSchema
  .path('firstName')
  .validate(function (name: string) {
    return name.length;
  }, 'First name cannot be blank');

CustomerSchema
  .path('lastName')
  .validate(function (name: string) {
    return name.length;
  }, 'Last name cannot be blank');

// Validate duplicate names
CustomerSchema
.path('email')
.validate(function(email: string) {
  this.constructor.findOne({
    email,
    company: this.company,
    store: this.store
  }, (err: Error, store: IStore) => {
    if (err) {
      throw err;
    }
    if (store) {
      return this.id === store.id;
    }
    return true;
  });
}, 'Email is already taken');

/**
 * Create full name on save
 */
CustomerSchema
  .pre('save', function(next) {
    this.fullName = `${this.firstName}${this.middleName ? ` ${this.middleName} ` : ' '}${this.lastName}`;

    next();
  });

CustomerSchema.set('toJSON', {
  virtuals: true, getters: true
});

['fullName', 'phone', 'email'].forEach(attr => {
  CustomerSchema.path(attr).set(keepOriginalSetter(attr));
});

CustomerSchema.pre('save', function (next) {
  this._originals = this._originals || {};
  next();
});

updateElasticIndexOnSave(CustomerSchema, (doc: ICustomer) => {
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
                "customer._id": doc._id
              }
            }
          ]
        }
      },
      script: {
        inline: "ctx._source['customer']['fullName'] = params.newFullName; ctx._source['customer']['phone'] = params.newPhone; ctx._source['customer']['email'] = params.newEmail",
        params: {
          newFullName: doc.fullName,
          newPhone: doc.phone,
          newEmail: doc.email
        }
      }
    },
    index: "inventories",
    type: "inventory"
  };
});

export const Customer: ICustomerModel = mongoose.model<ICustomer, ICustomerModel>('Customer', CustomerSchema);

export default Customer;
