import * as mongoose from 'mongoose';

const Schema = mongoose.Schema;

export interface ITest extends mongoose.Document {
  updated: Date;
}

export interface ITestModel extends mongoose.Model<ITest> { }

const TestSchema = new Schema({
  updated: {
    type: Date,
    default: Date.now
  }
}, {safe: {w: 'majority'}});

export const Test: ITestModel = mongoose.model<ITest, ITestModel>('Test', TestSchema);

export default Test;
