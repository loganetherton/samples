import * as mongoose from 'mongoose';
import config from '../../config/environment';
import createIndexes from '../../config/indexDb';
import {ErrorLogger} from '../../loggers';

const Schema = mongoose.Schema;
const logger = new ErrorLogger();

export interface IInventoryLog extends mongoose.Document {
  inventory: mongoose.Types.ObjectId;
}

export interface IInventoryLogModel extends mongoose.Model<IInventoryLog> { }

const InventoryLogSchema = new Schema({
  inventory: {type: Schema.Types.ObjectId, ref: 'Inventory'},
  // Redefine relationships so they can be loaded using populate()
  customer: {type: Schema.Types.ObjectId, ref: 'Customer'},
  retailer: {type: Schema.Types.ObjectId, ref: 'Retailer'},
  store: {type: Schema.Types.ObjectId, ref: 'Store'},
  company: {type: Schema.Types.ObjectId, ref: 'Company'},
  card: {type: Schema.Types.ObjectId, ref: 'Card', required: true},
  user: {type: Schema.Types.ObjectId, ref: 'User'},
  batch: {type: Schema.Types.ObjectId, ref: 'Batch'},
}, {strict: false});

// Indexes
const indexes = [
  [{inventory: 1}, {expireAfterSeconds: config.twoWeeks}],
];
createIndexes(InventoryLogSchema, indexes);

export const InventoryLog: IInventoryLogModel = mongoose.model<IInventoryLog, IInventoryLogModel>('InventoryLog', InventoryLogSchema);

export default InventoryLog;

function recordChanges(inventory: any) {
  try {
    const log = new InventoryLog(inventory);
    log._id = mongoose.Types.ObjectId();
    log.inventory = inventory._id;
    log.isNew = true;
    log.save();
  } catch (e) {
    logger.log(e);
  }
}

export function InventoryLogPlugin(schema: any) {
  schema.post('save', recordChanges);
  schema.post('findOneAndUpdate', recordChanges);
  schema.post('insertMany', (inventories: any[]) => {
    inventories.forEach((inventory: any) => recordChanges(inventory));
  });
}
