import * as mongoose from 'mongoose';

const Schema = mongoose.Schema;

export interface IDaemonError extends mongoose.Document {
  referenceId: mongoose.Types.ObjectId;
  referenceModel: string;
  details: string;
  created: Date;
}

export interface IDaemonErrorModel extends mongoose.Model<IDaemonError> { }

const DaemonErrorSchema = new Schema({
  // Reference ID
  referenceId: {type: Schema.Types.ObjectId},
  // Model type
  referenceModel: String,
  // Details
  details: String,
  created: {
    type: Date,
    default: Date.now
  }
});

export const DaemonError: IDaemonErrorModel = mongoose.model<IDaemonError, IDaemonErrorModel>('DaemonError', DaemonErrorSchema);

export default DaemonError;
