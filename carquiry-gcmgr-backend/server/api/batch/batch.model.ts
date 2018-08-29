import * as mongoose from 'mongoose';
import {updateElasticIndexOnSave} from '../../helpers/elastic';
import {ICompany} from "../company/company.model";
import {IStore} from "../stores/store.model";
import {IInventory} from "../inventory/inventory.model";

import createIndexes from '../../config/indexDb';

const Schema = mongoose.Schema;

export interface IBatch extends mongoose.Document {
  created: Date,
  batchId: number,
  company: mongoose.Types.ObjectId & ICompany,
  store: mongoose.Types.ObjectId & IStore,
  inventories: mongoose.Types.ObjectId[] & IInventory[]

  // This is only used to compare changes that might need to be synced with ES
  _originals: {
    batchId: number;
  };
}

export interface IBatchModel extends mongoose.Model<IBatch> { }

const BatchSchema = new mongoose.Schema({
  created: {
    type: Date,
    default: Date.now
  },
  // Batch number
  batchId: {type: Number, required: true},
  // Company
  company: {type: Schema.Types.ObjectId, ref: 'Store'},
  // Store ID
  store: {type: Schema.Types.ObjectId, ref: 'Store'},
  // Inventories
  inventories: [{type: Schema.Types.ObjectId, ref: 'Inventory'}]
});

// Indexes
const indexes = [
  // Unique card index
  [{batchId: 1}, {unique: true}],
];
createIndexes(BatchSchema, indexes);

BatchSchema
  .pre('validate', function(next) {
    this.constructor.findOne({})
      .sort({
        batchId: -1
      })
      .limit(1)
      .then((batch: IBatch) => {
        if (!batch) {
          this.batchId = 1;
        } else {
          this.batchId = batch.batchId + 1;
        }
        next();
      });
  });

BatchSchema.path('batchId').set(function (newVal: string) {
  this._originals = {batchId: this.batchId};
  return newVal;
});

BatchSchema.pre('save', function (next) {
  this._originals = this._originals || {};
  next();
});

updateElasticIndexOnSave(BatchSchema, (doc: IBatch) => {
  if (doc.batchId === doc._originals.batchId) {
    return null;
  }

  return {
    body: {
      query: {
        bool: {
          must: [
            {
              match: {
                "batch._id": doc._id
              }
            }
          ]
        }
      },
      script: {
        inline: "ctx._source['batch']['batchId'] = params.newBatchId",
        params: {
          newBatchId: doc.batchId
        }
      }
    },
    index: "inventories",
    type: "inventory"
  };
});

export const Batch: IBatchModel = mongoose.model<IBatch, IBatchModel>('Batch', BatchSchema);

export default Batch;
