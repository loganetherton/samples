import * as mongoose from 'mongoose';
import {ICard} from "../card/card.model";

const Schema = mongoose.Schema;

export interface ICardUpdate extends mongoose.Document {
  card: mongoose.Types.ObjectId & ICard;
  number: string;
  pin: string;
  created: Date;
  balanceStatus: string;
  valid: boolean;
  balance: number;
  user: mongoose.Types.ObjectId[];
  customer: mongoose.Types.ObjectId;
  retailer: mongoose.Types.ObjectId;
}

export interface ICardUpdateModel extends mongoose.Model<ICardUpdate> { }

const CardUpdateSchema = new Schema({
  // Card
  card: {type: Schema.Types.ObjectId, ref: 'Card', required: true},
  // Card number
  number: String,
  // Pin
  pin: String,
  // When original record is created
  created: {
    type: Date,
    default: Date.now
  },
  // Must be one of the strings listed below
  balanceStatus: {
    type: String,
    validate: {
      validator: function(v: string) {
        return /^(unchecked|deferred|received|bad|manual)$/.test(v);
      },
      message: 'Balance status must be "unchecked," "deferred," or "received"'
    }
  },
  // Whether a card is valid or not. Assumed to be valid until BI returns invalid
  valid: Boolean,
  // Balance
  balance: Number,
  // Store adding the card
  user: [{type: Schema.Types.ObjectId, ref: 'User'}],
  // User checking the card
  customer: {type: Schema.Types.ObjectId, ref: 'Customer'},
  // Retailer
  retailer: {type: Schema.Types.ObjectId, ref: 'Retailer'}
});

export const CardUpdate: ICardUpdateModel = mongoose.model<ICardUpdate, ICardUpdateModel>('CardUpdate', CardUpdateSchema);

export default CardUpdate;
