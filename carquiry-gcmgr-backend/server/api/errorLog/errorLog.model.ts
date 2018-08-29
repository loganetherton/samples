import * as mongoose from 'mongoose';
import {IUser} from "../user/user.model";
import {ICompany} from "../company/company.model";

const Schema = mongoose.Schema;

export interface IErrorLog extends mongoose.Document {
  message: string;
  method: string;
  controller: string;
  revision: string;
  stack: any;
  error: any;
  user: mongoose.Types.ObjectId & IUser;
  company: mongoose.Types.ObjectId & ICompany;
  body: object;
  params: object;
  created: Date;
}

export interface IErrorLogModel extends mongoose.Model<IErrorLog> { }

const ErrorLogSchema = new Schema({
  // Error object message
  message: String,

  method: {
    type: String
  },

  // This is the name of controller where an error occurs.
  controller: {
    type: String
  },

  // Git revision under which the error occurred
  revision: {
    type: String,
  },

  stack: {
    type: Schema.Types.Mixed
  },

  error: {
    type: Schema.Types.Mixed
  },

  user: {
    type: Schema.Types.ObjectId,
    ref: 'User'
  },

  company: {
    type: Schema.Types.ObjectId,
    ref: 'Company'
  },

  // Request body
  body: Object,
  // Params
  params: Object,

  created: {
    type: Date,
    default: Date.now
  }

});

export const ErrorLog: IErrorLogModel = mongoose.model<IErrorLog, IErrorLogModel>('ErrorLog', ErrorLogSchema);

export default ErrorLog;
