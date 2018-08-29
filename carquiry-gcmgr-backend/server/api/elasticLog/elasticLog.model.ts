import * as mongoose from 'mongoose';
import createIndexes from "../../config/indexDb";
import config from "../../config/environment";

export interface IElasticLog extends mongoose.Document {
  client: string;
  data: object;
  created: Date;
}

export interface IElasticLogModel extends mongoose.Model<IElasticLog> { }

const ElasticLogSchema = new mongoose.Schema({
  client: String,
  data: Object,
  created: {type: Date, default: Date.now},
});

// Indexes
const indexes = [
  // Unique card index
  [{created: 1}, {expireAfterSeconds: config.oneWeek}]
];
createIndexes(ElasticLogSchema, indexes);

export const ElasticLog: IElasticLogModel = mongoose.model<IElasticLog, IElasticLogModel>('ElasticLog', ElasticLogSchema);

export default ElasticLog;
