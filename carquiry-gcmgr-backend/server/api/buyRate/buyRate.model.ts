import * as mongoose from 'mongoose';
import {IRetailer} from "../retailer/retailer.model";
import {IStore} from "../stores/store.model";

export interface IBuyRate extends mongoose.Document {
  retailerId: mongoose.Types.ObjectId & IRetailer,
  storeId: mongoose.Types.ObjectId & IStore,
  buyRate: {type: Number, default: 0.6}
}

export interface IBuyRateModel extends mongoose.Model<IBuyRate> { }

const BuyRateSchema = new mongoose.Schema({
  // Retailer ID
  retailerId: {type: mongoose.Schema.Types.ObjectId, ref: 'Retailer'},
  // Store ID
  storeId: {type: mongoose.Schema.Types.ObjectId, ref: 'Store'},
  // Store buy rate (default to 60%)
  buyRate: {type: Number, default: 0.6}
});

BuyRateSchema.set('toJSON', {
  virtuals: true
});

export const BuyRate: IBuyRateModel = mongoose.model<IBuyRate, IBuyRateModel>('BuyRate', BuyRateSchema);

export default BuyRate;
