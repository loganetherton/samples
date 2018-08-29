import * as mongoose from 'mongoose';
import createIndexes from '../../config/indexDb';

mongoose.Promise = require('bluebird');
const Schema = mongoose.Schema;

const BiSync = new Schema({
  created: {
    type: Date,
    default: Date.now
  },
  // Card pin
  pin: String,
  // Card number
  number: String,
  // Retailer ID
  retailerId: {type: Schema.Types.ObjectId, ref: 'Retailer'},
  requestId: {type: String, default: null},
  // Type of sync. 'insert' if insert into BI, 'sync' if syncing from BI
  type: String
}, {safe: {w: 0}});

// // Indexes
const indexes = [
  [{number: 1, pin: 1, retailerId: 1, balance: 1}],
];
createIndexes(BiSync, indexes);


module.exports = mongoose.model('BiSync', BiSync);
