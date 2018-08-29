import createIndexes from '../../config/indexDb';
import config from '../../config/environment';
import * as mongoose from 'mongoose';
import {IUser} from "../user/user.model";

const Schema = mongoose.Schema;

export interface ILog extends mongoose.Document {
  created: Date;
  path: string;
  method: string;
  body: object;
  params: object;
  query: object;
  statusCode: number;
  statusMessage: string;
  isError: boolean;
  user: mongoose.Types.ObjectId & IUser;
  ip: string;
}

export interface ILogModel extends mongoose.Model<ILog> { }

const Logs = new Schema({
  // When original record is created
  created: {
    type: Date,
    default: Date.now
  },
  // Path requested
  path: String,
  // Request method
  method: String,
  // Request body
  body: Object,
  // Request params
  params: Object,
  // Request query
  query: Object,
  // Response status code
  statusCode: Number,
  // Response status message
  statusMessage: String,
  // Is error log
  isError: {type: Boolean, default: false},
  // User
  user: {type: Schema.Types.ObjectId, ref: 'User'},
  // IP
  ip: String
});

// Indexes
const indexes = [
  // Unique card index
  [{created: 1}, {expireAfterSeconds: config.eightWeeks}]
];
createIndexes(Logs, indexes);

export const Log: ILogModel = mongoose.model<ILog, ILogModel>('Log', Logs);

export default Log;
