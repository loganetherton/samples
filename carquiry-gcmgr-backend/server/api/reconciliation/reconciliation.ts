import * as mongoose from 'mongoose';
import {IInventory} from "../inventory/inventory.model";

const Schema = mongoose.Schema;

export interface IReconciliation extends mongoose.Document {
  inventory: mongoose.Types.ObjectId & IInventory;
  reconciliationComplete: boolean;
  created: Date;
  userTime: Date;
  reconciliationCompleteUserTime: Date;
}

export interface ReconciliationModel extends mongoose.Model<IReconciliation> { }

const ReconciliationSchema = new Schema({
  // Inventory
  inventory: {type: Schema.Types.ObjectId, ref: 'Inventory', required: true},
  // Reconciliation complete
  reconciliationComplete: {type: Boolean, default: false},
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
  /**
   * User time when reconciliation was complete
   */
  reconciliationCompleteUserTime: {type: Date}
});

export const Reconciliation: ReconciliationModel = mongoose.model<IReconciliation, ReconciliationModel>('Reconciliation', ReconciliationSchema);

export default Reconciliation;
