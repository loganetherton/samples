import * as mongoose from 'mongoose';
import {ICustomer} from "../customer/customer.model";

const Schema = mongoose.Schema;

export interface IDenialPayment extends mongoose.Document {
  amount: number;
  created: Date;
  userTime: Date;
  customer: mongoose.Types.ObjectId & ICustomer;
}

export interface IDenialPaymentModel extends mongoose.Model<IDenialPayment> { }

const DenialPaymentSchema = new Schema({
  // Payment amount
  amount: Number,
  /**
   * Created
   */
  created: {
    type: Date,
    default: Date.now
  },
  /**
   * User time when reconciliation was created
   */
  userTime: {type: Date},
  // Customer
  customer: {type: Schema.Types.ObjectId, ref: 'Customer', required: true},
});

export const DenialPayment: IDenialPaymentModel = mongoose.model<IDenialPayment, IDenialPaymentModel>('DenialPayment', DenialPaymentSchema);

export default DenialPayment;
