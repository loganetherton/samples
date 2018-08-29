import * as mongoose from 'mongoose';

const Schema = mongoose.Schema;

export interface IFrontendErrorLog extends mongoose.Document {
  stack: any;
  created: Date;
}

export interface IFrontendErrorLogModel extends mongoose.Model<IFrontendErrorLog> {}

const FrontendErrorLogSchema = new Schema({

  stack: {
    type: Schema.Types.Mixed
  },

  created: {
    type: Date,
    default: Date.now
  }

});


export const FrontendErrorLog: IFrontendErrorLogModel = mongoose.model<IFrontendErrorLog, IFrontendErrorLogModel>('FrontendErrorLog', FrontendErrorLogSchema);

export default FrontendErrorLog;
