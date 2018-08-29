import * as mongoose from 'mongoose';
import {IInventory} from "../inventory/inventory.model";
import {ICompany} from "../company/company.model";
import {IStore} from "../stores/store.model";

const Schema = mongoose.Schema;

export interface IReserve extends mongoose.Document {
  created: Date;
  amount: number;
  inventory: mongoose.Types.ObjectId & IInventory;
  company: mongoose.Types.ObjectId & ICompany;
  store: mongoose.Types.ObjectId & IStore;
}

export interface IReserveModel extends mongoose.Model<IReserve> { }

const ReserveSchema = new Schema({
  created: {
    type: Date,
    default: Date.now
  },
  // The amount of this transaction that goes into reserve
  amount: {type: Number, required: true},
  /**
   * References
   */
  inventory: {type: Schema.Types.ObjectId, ref: 'Inventory', required: true},
  company: {type: Schema.Types.ObjectId, ref: 'Company', required: true},
  store: {type: Schema.Types.ObjectId, ref: 'Store', required: true},
});

export const Reserve: IReserveModel = mongoose.model<IReserve, IReserveModel>('Reserve', ReserveSchema);

export default Reserve;
