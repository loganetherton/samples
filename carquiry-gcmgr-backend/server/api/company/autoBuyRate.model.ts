import * as mongoose from 'mongoose';
import {ICompanySettings} from "./companySettings.model";

const Schema = mongoose.Schema;

export interface IAutoBuyRate extends mongoose.Document {
  _95: number;
  _90_95: number;
  _85_90: number;
  _80_85: number;
  _75_80: number;
  _70_75: number;
  _65_70: number;
  _60_65: number;
  _55_60: number;
  _50_55: number;
  _50: number;
  created: Date;
  updated: Date;
  settings: mongoose.Types.ObjectId & ICompanySettings;
}

export interface IAutoBuyRateModel extends mongoose.Model<IAutoBuyRate> { }

const AutoBuyRateSchema = new Schema({
  // Auto-buy rate for specific intervals. _90_95 represents 90-94.99%. _95 represents 95% and greater. _50 represents 50% and less
  _95: {type: Number, default: 0.1},
  _90_95: {type: Number, default: 0.1},
  _85_90: {type: Number, default: 0.1},
  _80_85: {type: Number, default: 0.1},
  _75_80: {type: Number, default: 0.1},
  _70_75: {type: Number, default: 0.1},
  _65_70: {type: Number, default: 0.1},
  _60_65: {type: Number, default: 0.1},
  _55_60: {type: Number, default: 0.1},
  _50_55: {type: Number, default: 0.1},
  _50: {type: Number, default: 0.1},

  created: {
    type: Date,
    default: Date.now
  },
  updated: {
    type: Date,
    default: Date.now
  },
  // Settings document
  settings: {type: Schema.Types.ObjectId, ref: 'CompanySettings'}
});

// Updated time
AutoBuyRateSchema
  .pre('save', function(next) {
    this.updated = new Date();
    next();
  });

export const AutoBuyRate: IAutoBuyRateModel = mongoose.model<IAutoBuyRate, IAutoBuyRateModel>('AutoBuyRate', AutoBuyRateSchema);

export default AutoBuyRate;
