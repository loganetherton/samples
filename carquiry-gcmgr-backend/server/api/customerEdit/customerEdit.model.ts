import * as mongoose from 'mongoose';
import {IUser} from "../user/user.model";
import {ICustomer} from "../customer/customer.model";

const Schema = mongoose.Schema;

export interface ICustomerEdit extends mongoose.Document {
  created: Date;
  user: mongoose.Types.ObjectId[] & IUser[];
  customer: mongoose.Types.ObjectId[] & ICustomer[];
}

export interface ICustomerEditModel extends mongoose.Model<ICustomerEdit> { }

/**
 * Keep track of all edits to customers
 */
const CustomerEditSchema = new Schema({
  created: {
    type: Date,
    default: Date.now
  },
  // Store relationship
  user: [{type: Schema.Types.ObjectId, ref: 'User'}],
  // Edits
  customer: [{type: Schema.Types.ObjectId, ref: 'Customer'}]
});

export const CustomerEdit: ICustomerEditModel = mongoose.model<ICustomerEdit, ICustomerEditModel>('CustomerEdit', CustomerEditSchema);

export default CustomerEdit;
