import * as mongoose from 'mongoose';
import {ICard} from "../card/card.model";

const Schema = mongoose.Schema;

export interface IDeferredBalanceInquiry extends mongoose.Document {
  card: mongoose.Types.ObjectId & ICard;
  queryCount: number;
  requestId: string;
  lastRunTime: Date;
  valid: boolean;
  completed: boolean;
  created: Date;
}

export interface IDeferredBalanceInquiryModel extends mongoose.Model<IDeferredBalanceInquiry> { }

/**
 * Keep reference to deferred balance inquiries
 */
const DeferredBalanceInquirySchema = new Schema({
  // Card
  card: {type: Schema.Types.ObjectId, ref: 'Card', required: true},
  // The number of checks that have been performed already for this request
  queryCount: {type: Number, required: true, default: 1},
  // Request ID
  requestId: String,
  // Last run time
  lastRunTime: {
    type: Date,
    default: Date.now
  },
  // If valid
  valid: {
    type: Boolean,
    default: true
  },
  // BI completed
  completed: {
    type: Boolean,
    default: false
  },
  created: {
    type: Date,
    default: Date.now
  }
});

export const DeferredBalanceInquiry: IDeferredBalanceInquiryModel = mongoose.model<IDeferredBalanceInquiry, IDeferredBalanceInquiryModel>('DeferredBalanceInquiry', DeferredBalanceInquirySchema);

export default DeferredBalanceInquiry;
