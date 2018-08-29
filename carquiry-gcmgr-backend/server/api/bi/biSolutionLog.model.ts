import * as mongoose from 'mongoose';

export interface IBiSolutionLog extends mongoose.Document {
  method: string,
  url: string,
  status: number,
  requestSentAt: Date,
  responseReceivedAt: Date,
  requestBody: any,
  responseBody: any
}


export interface IBiSolutionLogModel extends mongoose.Model<IBiSolutionLog> { }

const biSolutionLogSchema = new mongoose.Schema({
  method: {type: String, required: true},
  url: {type: String, required: true},
  status: {type: Number, required: true, default: -1},
  requestSentAt: {type: Date, required: true},
  responseReceivedAt: {type: Date, required: true},
  requestBody: {type: Object, required: true, default: {}},
  responseBody: {type: Object, required: true, default: {}}
});

export const BiSolutionLog: IBiSolutionLogModel = mongoose.model<IBiSolutionLog, IBiSolutionLogModel>('BiSolutionLog', biSolutionLogSchema);

export default BiSolutionLog;
