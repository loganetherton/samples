import * as mongoose from 'mongoose';
import * as uuid from 'node-uuid';
import {ICompany} from "../company/company.model";
import {IStore} from "../stores/store.model";
import {IInventory} from "../inventory/inventory.model";
import {ICustomer} from "../customer/customer.model";
import {IUser} from "../user/user.model";

const Schema = mongoose.Schema;

export interface IReceipt extends mongoose.Document {
  created: Date;
  userTime: Date;
  receiptId: string;
  rejectionTotal: number;
  total: number;
  modifiedDenialAmount: number;
  appliedTowardsDenials: number;
  grandTotal: number;
  remainingDenials: number;
  company: mongoose.Types.ObjectId & ICompany;
  store: mongoose.Types.ObjectId & IStore;
  inventories: mongoose.Types.ObjectId[] & IInventory[];
  customer: mongoose.Types.ObjectId & ICustomer;
  user: mongoose.Types.ObjectId & IUser;
}

export interface IReceiptModel extends mongoose.Model<IReceipt> { }

/**
 * Receipt
 */
export const receiptSchema = {
  /**
   * Created
   */
  created: {
    type: Date,
    default: Date.now
  },
  /**
   * User time when inventory created
   */
  userTime: {
    type: Date
  },
  // Receipt ID
  receiptId: {
    type: String,
    default: uuid()
  },
  // Denial amount BEFORE the current receipt generated
  rejectionTotal: {type: Number, default: 0},
  // Receipt total
  total: {type: Number, default: 0},
  // Modified denial subtraction amount (does not subtract full buy amount from customer denials total)
  modifiedDenialAmount: Number,
  // Amount applied towards denials
  appliedTowardsDenials: {type: Number, required: true},
  // Grand total
  grandTotal: {type: Number, required: true},
  // Amount of denials remaining for this customer
  remainingDenials: Number,
  // Company/store
  company: {type: Schema.Types.ObjectId, ref: 'Company'},
  store: {type: Schema.Types.ObjectId, ref: 'Store'},
  // Inventories involved in this receipt
  inventories: [{type: Schema.Types.ObjectId, ref: 'Inventory'}],
  // Customer
  customer: {type: Schema.Types.ObjectId, ref: 'Customer'},
  // User creating record (cashier)
  user: {type: Schema.Types.ObjectId, ref: 'User'}
};

// Schema
const ReceiptSchema = new Schema(receiptSchema);

export const Receipt: IReceiptModel = mongoose.model<IReceipt, IReceiptModel>('Receipt', ReceiptSchema);

export default Receipt;
