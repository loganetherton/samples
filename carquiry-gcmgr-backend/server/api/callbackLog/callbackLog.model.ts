import * as mongoose from 'mongoose';
import createIndexes from '../../config/indexDb';
import config from '../../config/environment';
import {ICard} from "../card/card.model";
import {ICompany} from "../company/company.model";
import {IBiRequestLog} from "../biRequestLog/biRequestLog.model";

const Schema = mongoose.Schema;

export interface ICallbackLog extends mongoose.Document {
  created: Date,
  callbackType: string,
  number: string,
  pin: string,
  claimedBalance: number,
  verifiedBalance: number,
  cqPaid: number,
  netPayout: number,
  prefix: string,
  cqAch: string,
  finalized: boolean,
  success: boolean,
  failResponse: string,
  url: string,
  statusCode: number,
  body: any,
  resent: boolean,
  token: string,
  card: mongoose.Types.ObjectId & ICard,
  company: mongoose.Types.ObjectId & ICompany,
  biRequestLog: mongoose.Types.ObjectId & IBiRequestLog
}

export interface ICallbackLogModel extends mongoose.Model<ICallbackLog> { }

const CallbackLogSchema = new Schema({
  created: {type: Date, default: Date.now},
  // Callback type
  callbackType: {type: String, required: true},
  // Card number
  number: {type: String, required: true},
  // Card pin
  pin: String,
  // Claimed balance
  claimedBalance: Number,
  // Verified balance
  verifiedBalance: Number,
  // Amount CQ paid before fees and margin
  cqPaid: Number,
  // Amount CQ paid after fees and margin
  netPayout: Number,
  // Card prefix
  prefix: String,
  // CQ ACH number
  cqAch: String,
  // Whether a card has been finalized
  finalized: {type: Boolean, default: false},
  // Whether there was a success or failure
  success: {type: Boolean, required: true, default: true},
  // Fail response from the remote server if we encounter an error
  failResponse: String,
  // Callback URL
  url: {type: String, required: true},
  // Response status code
  statusCode: {type: Number},
  // Raw request body
  body: Object,
  // Whether this callback has been processed to be resent on failure
  resent: {type: Boolean, default: false},
  // Token
  token: {type: String, default: ''},
  /**
   * References
   */
  // Card
  card: {type: Schema.Types.ObjectId, ref: 'Card'},
  // Company making the callback request
  company: {type: Schema.Types.ObjectId, ref: 'Company'},
  // BI Request Log
  biRequestLog: {type: Schema.Types.ObjectId, ref: 'BiRequestLog'},
});

// Indexes
const indexes = [
  // Expire logs after two weeks
  [{created: 1}, {expireAfterSeconds: config.eightWeeks}],
  [{company: 1}]
];
createIndexes(CallbackLogSchema, indexes);

CallbackLogSchema.set('toJSON', {
  virtuals: true, getters: true
});

export const CallbackLog: ICallbackLogModel = mongoose.model<ICallbackLog, ICallbackLogModel>('CallbackLog', CallbackLogSchema);

export default CallbackLog;
