import * as mongoose from 'mongoose';
import * as mongoosastic from 'mongoosastic';
import * as ES from 'elasticsearch';

import config from '../../config/environment';
import Retailer, {IRetailer} from '../retailer/retailer.model';
import Store, {IStore} from '../stores/store.model';
import Company, {ICompany} from '../company/company.model';
import {ICompanySettings} from '../company/companySettings.model';
import Card, {ICard} from '../card/card.model';
import Batch, {IBatch} from '../batch/batch.model';
import Customer, {ICustomer} from '../customer/customer.model';
import User, {IUser} from '../user/user.model';
import {IReserve} from '../reserve/reserve.model';

import createIndexes from '../../config/indexDb';
import MongoosasticWrapper from '../../wrappers/mongoosastic.wrapper';
import currencies from '../../config/currencies';
import {formatFloat} from '../../helpers/number';

import {ElasticLogger} from '../../loggers';
import {IReconciliation} from "../reconciliation/reconciliation";
import {IReceipt} from "../receipt/receipt.model";

import {calculateValues} from '../company/company.controller';

const Schema = mongoose.Schema;
const {elasticsearch, smpIds} = config;

import {InventoryLogPlugin} from './inventoryLog.model';

// import Callback from '../callbackLog/callback';

export interface IInventoryTransaction {
  memo: string;
  nccCardValue: number;
  transactionTotal: number;
  transactionId: string;
  merchantPayoutAmount: number;
  merchantPayoutPercentage: number;
  amountDue: number;
  cqPaid?: number;
  reserve?: mongoose.Types.ObjectId & IReserve;
  reserveAmount?: number;
  cqWithHeld?: number;
  netPayout?: number;
  prefix: string;
  serviceFee?: number;
  creditValuePercentage: number;
  maxSpending: number;
  vmMemo1: string;
  vmMemo2: string;
  vmMemo3: string;
  vmMemo4: string;
  callbacks?: string[];
}

export interface IInventory extends mongoose.Document {
  balance: number;
  buyRate: number;
  realBuyAmount: number;
  buyAmount: number;
  transactionId: string;
  cqTransactionId: string;
  smp: string;
  type: string;
  status: string;
  adminActivityNote: string;
  status_message: string;
  liquidationSoldFor: number;
  liquidationSoldForAdjusted: number;
  liquidationRate: number;
  sellRateAtPurchase: number;
  corpRate: number,
  disableAddToLiquidation: string;
  margin: number;
  serviceFee: number;
  serviceFeeValue: number,
  tzOffset: string;
  rejected: boolean;
  rejectedDate: Date;
  rejectAmount: number;
  credited: boolean;
  creditedDate: Date;
  creditAmount: number;
  cqPaid: number;
  companyMargin: number;
  netAmount: number;
  proceedWithSale: boolean;
  soldToLiquidation: boolean;
  saveYaConfirmLastRunTime: Date;
  saveYa: {
    selling: boolean;
    confirmed: boolean;
    rejected: boolean;
    rejectReason: string;
    balance: number;
    saveYaRate: number;
    saveYaStatus: string;
    underReview: boolean;
    offer: number;
    paymentType: string;
    error: string;
  };
  activityStatus: string;
  adjustmentStatus: string;
  buyerShipStatus: string;
  buyerAch: string;
  paidStatus: string;
  achNumber: string;
  verifiedBalance: number;
  hasVerifiedBalance: boolean;
  orderNumber: string;
  smpAch: string;
  cqAch: string;
  created: Date;
  updated: Date;
  userTime: Date;
  systemTime: Date;
  valid: boolean;
  deduction: string;
  locked: boolean;
  merchandise: boolean;
  customerOwedAmount: Number,
  isTransaction: boolean;
  transaction: IInventoryTransaction;
  callbackUrl: string;
  changed: boolean;
  isApi: boolean;
  originalType: string;
  currencyCode: string;
  customer: mongoose.Types.ObjectId & ICustomer;
  retailer: IRetailer & mongoose.Types.ObjectId;
  store: IStore & mongoose.Types.ObjectId;
  company: ICompany & mongoose.Types.ObjectId;
  card: mongoose.Types.ObjectId & ICard;
  user: mongoose.Types.ObjectId & IUser;
  reconciliation: mongoose.Types.ObjectId & IReconciliation;
  batch: mongoose.Types.ObjectId & IBatch;
  receipt: mongoose.Types.ObjectId & IReceipt;

  getCard: () => ICard;
  getCallbackUrl: () => Promise<string>;
  getTransactionValues: (reserveAmount: number, cqPaid: number, balance: number) => this;
  createReserve: () => Promise<any>;
  undoReserveValues: (reserve: IReserve) => object;
  removeReserve: () => Promise<void>;
}

export interface IInventoryModel extends mongoose.Model<IInventory> {
  getReserveAmount: (balance: number, reserveRate: number) => number;
  getCqPaid: (balance: number, rateAfterMargin: number) => number;
  addReserveToSet: (model: any, reserve: IReserve) => Promise<void>;
  addToRelatedErrorLog: (modelType: string) => Promise<any>;
  addToRelatedReserveRecords: (reserve: IReserve) => Promise<void>;
  // Mongoosastic functions
  createMapping: () => void;
}

export const inventorySchemaObject = {
  // Balance (either from BI or from manual)
  balance: {type: Number},
  // Actual card buy rate, which can differ from buy rate calculated by retailer minus margin
  buyRate: {type: Number},
  // Buy amount calculated based on buyRate * VB
  realBuyAmount: Number,
  // Buy amount (the amount that the store bought the card from the customer for)
  buyAmount: {type: Number},
  // SMP Transaction ID (not Vista)
  transactionId: String,
  // CQ transaction ID (not Vista)
  cqTransactionId: String,
  // SMP to whom card is sold
  // CC: 2
  // CP: 3
  smp: {type: String, es_fields: {keyword: {type: 'string', index: 'not_analyzed'}}},
  // Type of card (electronic or physical) as returned from LQAPI
  type: {type: String, get: convertToLowerCase, set: convertToLowerCase},
  // Transaction status (pending, shipped, paid, denied)
  status: String,
  // Notes added in activity
  adminActivityNote: {type: String, es_fields: {keyword: {type: 'string', index: 'not_analyzed'}}},
  // Liquidation status message
  status_message: {type: String},
  // The amount that CQ receives from the SMP for the sale of a card
  liquidationSoldFor: {type: Number},
  // The amount the SMP pays for a card after an adjustment has been made
  liquidationSoldForAdjusted: Number,
  // Rate returned from liquidation API (Without margin)
  liquidationRate: {type: Number},
  // Rate at purchase (without margin included)
  sellRateAtPurchase: Number,
  // Rate displayed in corporate view
  corpRate: Number,
  // Disable adding to liquidation
  disableAddToLiquidation: {type: String},
  // Margin rate  at time of adding to liquidation
  margin: {type: Number, default: 0.03, min: 0, max: 1},
  // Service fee rate at time of transaction
  serviceFee: {type: Number, default: 0.0075, min: 0, max: 1},
  // Service fee dollar value based on liquidated value * service fee rate
  serviceFeeValue: {type: Number},
  // User timezone offset
  tzOffset: String,
  // Rejected
  rejected: {type: Boolean, default: false},
  // Rejected date
  rejectedDate: Date,
  // Reject amount
  rejectAmount: Number,
  // Credited
  credited: {type: Boolean, default: false},
  // Credited date
  creditedDate: Date,
  // Credit amount
  creditAmount: Number,
  // CQ Paid
  cqPaid: {type: Number},
  // Final margin for corporate calculations (service fee rate + margin rate)
  companyMargin: {type: Number},
  // Net Amount
  netAmount: {type: Number},
  /**
   * LQ interactions
   */
  // Proceed with sale is set to false when auto-sell is turned off, and requires an admin to approve the sale
  proceedWithSale: {type: Boolean, default: true},
  // Sold via liquidation
  soldToLiquidation: {type: Boolean, default: false},
  /**
   * SaveYa confirms
   */
  saveYaConfirmLastRunTime: {
    type: Date,
    default: Date.now
  },
  // @todo Save ya info (I wanna delete you, fucker, but we've got some old data that needs you
  saveYa: {
    // In the process of selling to SY
    selling: {type: Boolean},
    // SaveYa verification
    confirmed: {type: Boolean, default: false},
    // Save ya rejected
    rejected: {type: Boolean, default: false},
    // Saveya reject reason
    rejectReason: String,
    // SaveYa returned balance
    balance: {type: Number},
    // SaveYa rate
    saveYaRate: {type: Number},
    // SaveYa status (can set if not confirmed)
    saveYaStatus: {type: String},
    // Under review by SY
    underReview: {type: Boolean, default: false},
    // SY offer
    offer: Number,
    // payment type
    paymentType: String,
    // Error in connections with SY
    error: String,
    // Mongoosastic wouldn't stopping creating a mapping for this for some reason
    es_indexed: false
  },
  // Activity status (THIS IS THE USED STATUS)
  activityStatus: {type: String, es_type: 'string', es_index: 'not_analyzed'},
  // Adjustment status (credit, denial, chargeback)
  adjustmentStatus: {type: String, es_type: 'keyword'},
  // Corporate ship status (will be set from corporate activity page)
  // @todo Unused
  buyerShipStatus: String,
  // Corporate ACH (will be set from corporate activity page)
  buyerAch: String,
  // Paid status
  paidStatus: String,
  // Ach number
  achNumber: String,
  // Verified balance (set to 0 for invalid cards)
  verifiedBalance: {type: Number, get: defaultsToBalance},
  // Verified balance has been received
  hasVerifiedBalance: {type: Boolean, default: false},
  // Order number
  orderNumber: {type: String, es_type: 'string', es_index: 'not_analyzed'},
  // SMP ACH
  smpAch: {type: String, es_type: 'string', es_index: 'not_analyzed'},
  // CQ ACH
  cqAch: {type: String, es_type: 'string', es_index: 'not_analyzed'},
  /**
   * Created
   */
  created: {
    type: Date,
    default: Date.now,
  },
  // Last update time (unused)
  updated: Date,
  /**
   * User time when inventory created
   */
  userTime: {
    type: Date
  },
  /**
   * System time, because we've come full fucking circle
   */
  systemTime: {type: Date, default: Date.now},

  // Card is invalid, set either by an admin or by BI response
  valid: Boolean,

  // Deduction number
  deduction: {type: String, es_fields: {keyword: {type: 'string', index: 'not_analyzed'}}},
  // Process lock
  locked: {type: Boolean, default: false},
  // Merchandise
  merchandise: {type: Boolean, default: false},
  // Amount owed by a customer after a rejection
  customerOwedAmount: Number,
  /**
   * Vista data
   */
  isTransaction: {type: Boolean, default: false},
  // Transaction data
  transaction: {
    // can be set to whatever they want
    memo: {type: String, es_fields: {keyword: {type: 'string', index: 'not_analyzed'}}},
    // verifiedBalance * retailer.creditValuePercentage - amount spent
    nccCardValue: {
      type: Number
    },
    // Value of the complete transaction, both GC and cash
    transactionTotal: Number,
    // Transaction ID
    transactionId: {type: String, es_fields: {keyword: {type: 'string', index: 'not_analyzed'}}},
    // Amount paid to the merchant for this transaction
    merchantPayoutAmount: Number,
    // Percentage paid out to the merchant for this transaction
    merchantPayoutPercentage: Number,
    // Amount due in cash for this transaction
    amountDue: Number,
    // Amount CQ paid to vista
    cqPaid: {type: Number},
    // Reserve
    reserve: {type: Schema.Types.ObjectId, ref: 'Reserve'},
    // Reserve amount
    reserveAmount: Number,
    // CQ withheld
    cqWithheld: Number,
    // Net payout to Vista
    netPayout: {type: Number},
    // Prefix (whatever they want this to be, like memo)
    prefix: {type: String, es_fields: {keyword: {type: 'string', index: 'not_analyzed'}}},
    // Service fees are handled differently for transactions. This is the dollar figure, not the rate
    serviceFee: {type: Number, es_type: 'float'},
    // Amount credited based on card balance
    creditValuePercentage: Number,
    // Current max spending for this store
    maxSpending: Number,
    // VM Memos
    vmMemo1: {type: String, es_fields: {keyword: {type: 'string', index: 'not_analyzed'}}},
    vmMemo2: {type: String, es_fields: {keyword: {type: 'string', index: 'not_analyzed'}}},
    vmMemo3: {type: String, es_fields: {keyword: {type: 'string', index: 'not_analyzed'}}},
    vmMemo4: {type: String, es_fields: {keyword: {type: 'string', index: 'not_analyzed'}}},
    // Which callbacks have already been sent (we don't want repeat callbacks)
    callbacks: [String]
  },
  // Callback URL once a VB is determined
  callbackUrl: String,
  // Inventory has changed
  changed: {type: Boolean, default: true},
  // Is sold via LQ API
  isApi: {type: Boolean, default: false},
  // Original type before disabled
  originalType: String,
  // Currency
  currencyCode: {type: String, es_type: 'keyword', default: 'USD', enum: currencies},
  /**
   * Relations
   */
  // User checking the card
  customer: {type: Schema.Types.ObjectId, ref: 'Customer', es_schema: Customer.schema, es_select: '_id fullName phone email'},
  // Retailer
  retailer: {type: Schema.Types.ObjectId, ref: 'Retailer', es_schema: Retailer.schema, es_select: '_id name'},
  // Store
  store: {type: Schema.Types.ObjectId, ref: 'Store', es_schema: Store.schema, es_select: '_id name'},
  // Company
  company: {type: Schema.Types.ObjectId, ref: 'Company', es_schema: Company.schema, es_select: '_id name'},
  // Card
  card: {type: Schema.Types.ObjectId, ref: 'Card', required: true, es_schema: Card.schema, es_select: '_id number pin'},
  // User
  user: {type: Schema.Types.ObjectId, ref: 'User', es_schema: User.schema, es_select: '_id firstName lastName'},
  // Reconciliation
  reconciliation: {type: Schema.Types.ObjectId, ref: 'Reconciliation'},
  // Batch
  batch: {type: Schema.Types.ObjectId, ref: 'Batch', es_schema: Batch.schema, es_select: '_id batchId'},
  // Receipt
  receipt: {type: Schema.Types.ObjectId, ref: 'Receipt'}
};

// Schema
const InventorySchema = new Schema(inventorySchemaObject, {safe: {w: 1}, read: 'primary'});

const esPopulate = [
  {path: 'retailer', select: '_id name'},
  {path: 'store', select: '_id name'},
  {path: 'company', select: '_id name'},
  {path: 'card', select: '_id number pin callbackVb'},
  {path: 'batch', select: '_id batchId'},
  {path: 'customer', select: '_id fullName phone email'},
  {path: 'user', select: '_id firstName lastName'},
];

const esClient = new ES.Client(Object.assign({}, elasticsearch));

InventorySchema.plugin(mongoosastic, Object.assign({}, elasticsearch, {
  esClient: esClient,
  index: 'inventories',
  type: 'inventory',
  populate: esPopulate,
  hydrateOptions: {
    populate: esPopulate
  },
  bulk: {
    size: 100,
    delay: 250
  }
}));
InventorySchema.plugin(MongoosasticWrapper);
InventorySchema.plugin(InventoryLogPlugin);

// Indexes
const indexes = [
  // Unique card index
  [{card: 1}, {name: 'card', unique: true}],
  [{soldToLiquidation: 1, proceedWithSale: 1, disableAddToLiquidation: 1, type: 1, locked: 1, isTransaction: 1}],
  [{company: 1}],
  [{reconciliation: 1}],
  [{store: 1}]
];
createIndexes(InventorySchema, indexes);

// Static methods
Object.assign(InventorySchema.statics, {
  /**
   * Get reserve amount for a card
   * @param {Number} balance Claimed or verified balance
   * @param {Number} reserveRate Reserve rate
   * @return {Number}
   */
  getReserveAmount(balance: number, reserveRate: number): number {
    return formatFloat(balance * reserveRate);
  },
  /**
   * Get CQ paid amount
   * @param {Number} balance Claimed or VB
   * @param {Number} rateAfterMargin LQ rate minus margin
   * @return {Number}
   */
  getCqPaid(balance: number, rateAfterMargin: number): number {
    return formatFloat(balance * rateAfterMargin);
  },
  /**
   * Add new reserve to a company or store set of reserves
   * @param model Company or Store model
   * @param {IReserve} reserve Incoming reserve
   * @return {Promise.<void>}
   */
  async addReserveToSet(model: any, reserve: IReserve): Promise<void> {
    const reserveId = reserve._id;
    if (model.reserves.map((r: IReserve) => r.toString()).indexOf(reserveId) === -1) {
      model.reserves.push(reserveId);
      model.reserveTotal = model.reserveTotal + reserve.amount;
      await model.save();
    }
  },
  /**
   * Store an error log item if we cannot find a reference that should exist
   * @param {String} modelType
   * @return {Promise.<*>}
   */
  async addToRelatedErrorLog(modelType: string): Promise<any> {
    return await this.model('Log').create({
      path: 'runDefers/completeTransactions/addToRelatedReserveRecords',
      params: {},
      isError: true,
      statusMessage: `Unable to retrieve ${modelType}`
    });
  },
  /**
   * Add reserve values to store, company, and inventory
   * @param {IReserve} reserve
   * @return {Promise.<void>}
   */
  async addToRelatedReserveRecords(reserve: IReserve): Promise<void> {
    const company = await this.model('Company').findById(reserve.company);
    // Cannot find company
    if (!company) {
      return await this.addToRelatedErrorLog('company')
    }
    // Add this reserve to the set if it doesn't exist
    await this.addReserveToSet(company, reserve);
    // Update store
    const store = await (this.model('Store')).findById(reserve.store);
    if (!company) {
      return await this.addToRelatedErrorLog('store');
    }
    await this.addReserveToSet(store, reserve);
    // Update inventory
    await this.update({_id: reserve.inventory}, {
      $set: {'transaction.reserve': reserve._id, 'transaction.reserveAmount': reserve.amount}
    });
  }
});

Object.assign(InventorySchema.methods, {
  // Retrieve card associated with inventory
  getCard: function () {
    return (this.model('Card')).findOne({inventory: this._id});
  },
  getCallbackUrl: async function () {
    if (this.callbackUrl) {
      return Promise.resolve(this.callbackUrl);
    }

    return (this.model('Store')).findOne({_id: this.store})
    .then((store: IStore) => {
      if (store.callbackUrl) {
        return Promise.resolve(store.callbackUrl);
      }

      return (this.model('CompanySettings')).findOne({company: this.company})
      .then((settings: ICompanySettings) => {
        return Promise.resolve(settings.callbackUrl);
      });
    });
  },
  /**
   * Get transaction values
   * @param {Number} reserveAmount Reserve amount
   * @param {Number} cqPaid The amount CQ is paying for the card
   * @param {Number} balance Claimed or VB
   * @return {IInventory}
   */
  getTransactionValues(reserveAmount: number, cqPaid: number, balance: number): IInventory {
    this.transaction.cqWithheld = formatFloat(this.transaction.serviceFee + reserveAmount);
    this.transaction.netPayout = formatFloat((balance * (this.liquidationRate - this.margin)) - this.transaction.cqWithheld);
    this.transaction.cqPaid = cqPaid;
    this.cqPaid = cqPaid;
    return this;
  },
  /**
   * Create a reserve for a transaction
   * @return {Promise.<*>}
   */
  async createReserve(): Promise<any> {
    const company = this.company._id ? this.company._id : this.company;
    const reserve = new (this.model('Reserve'))({
      inventory: this._id,
      amount: this.transaction.reserveAmount,
      company,
      store: this.store
    });
    return reserve.save();
  },
  /**
   * Mongodb params for removing previously set reserves
   * @param {IReserve} reserve
   * @return {{$pull: {reserves: *}, set: {reserveTotal: *}}}
   */
  undoReserveValues(reserve: IReserve): object {
    return {
      $pull: {
        reserves: reserve._id
      },
      $inc: {reserveTotal: reserve.amount * -1}
    }
  },
  /**
   * Remove a reserve from a transaction
   * @return {Promise.<void>}
   */
  async removeReserve(): Promise<any> {
    return new Promise(async resolve => {
      try {
        const reserveId = this.transaction.reserve;
        // Remove a reserve from an inventory, company, and store so it can be recalculated
        if (this.transaction.reserve) {
          const reserve = await (this.model('Reserve')).findById(this.transaction.reserve);
          if (reserve) {
            await (this.model('Reserve')).remove({_id: reserve._id});
            // Find company and store with this reserve
            const company = await (this.model('Company')).findOne({reserves: reserve._id});
            const store = await (this.model('Store')).findOne({reserves: reserve._id});
            const inventory = await this.constructor.findById(this._id);
            // Undo company, store, and inventory for this reserve
            if (company) {
              await (this.model('Company')).update({_id: company._id}, this.undoReserveValues(reserve));
            }
            if (store) {
              await (this.model('Store')).update({_id: store._id}, this.undoReserveValues(reserve));
            }
            if (inventory) {
              await this.constructor.update({_id: this._id}, {
                $set: {
                  'transaction.reserve': null,
                  'transaction.reserveAmount': 0
                }
              });
            }
            // Remove this reserve
            await (this.model('Reserve')).remove({_id: reserveId});
          }
        }

        resolve(null);
      } catch (e) {
        console.log('**************ERR IN REMOVE RESERVE**********');
        console.log(e);
        console.log(e.stack);
      }
    });
  }
});

// Set number for SMP
InventorySchema.pre('save', async function(next) {
  // Set SMP into a uniform way
  if (typeof this.smp !== 'undefined' && [smpIds.CARDCASH, smpIds.CARDPOOL, smpIds.GIFTCARDZEN, smpIds.CARDQUIRY].indexOf(this.smp) === -1) {
    const setSmp = parseInt(this.smp);
    // Change to int
    if (isNaN(setSmp)) {
      if (this.smp && [smpIds.CARDCASH, smpIds.CARDPOOL, smpIds.GIFTCARDZEN, smpIds.CARDQUIRY].indexOf(this.smp) === -1) {
        const smp = smpIds[this.smp.toUpperCase()];
        if (smp) {
          this.smp = smp;
        }
      }
    }
  }
  next();
});

InventorySchema.post('save', async (doc: IInventory) => {
  (new ElasticLogger()).log({client: 'mongoosastic', data: doc.toObject()});
});

/**
 * Determine if inventory has changed and needs to be recalculated
 */
InventorySchema.pre('validate', function (next) {
  try {
    let settings;
    if (!this.company) {
      return next();
    }
    // Get company
    this.model('Company').findById(this.company)
    .then(dbCompany => {
      return dbCompany.getSettings(true);
    })
    // Get company settings
    .then(dbSettings => {
      settings = dbSettings;
      return this.constructor.findById(this._id);
    })
    // Get old inventory
    .then(async (oldInventory: any) => {
      if (!oldInventory) {
        this.changed = true;
        calculateValues(this, settings, this.rejected)
        .then(calculateRes => {
          Object.assign(this, calculateRes);
          if (typeof this.companyMargin !== 'number') {
            this.companyMargin = undefined;
          }
          next();
        });
      } else {
        // If anything has changed, set as changed
        const current = this.toObject();
        const old = oldInventory.toObject();
        delete old.changed;
        delete current.changed;
        if (JSON.stringify(current) !== JSON.stringify(old)) {
          this.changed = true;
        }
        if (this.changed) {
          // Determine if we need to recalculate values
          calculateValues(this, settings, this.rejected)
            .then(calculateRes => {
              Object.assign(this, calculateRes);
              // Remove if null
              if (typeof this.companyMargin !== 'number') {
                this.companyMargin = undefined;
              }
              next();
            });
        } else {
          next();
        }
      }
    })
    .catch((err: Error) => {
      this.changed = true;
      next();
    })
  } catch (err) {
    this.changed = true;
    next();
  }
});

/**
 * Attribute methods
 * @param verifiedBalance
 * @return {*}
 */
function defaultsToBalance(verifiedBalance?: number) {
  if (typeof verifiedBalance === 'number') {
    return verifiedBalance
  }
  const claimedBalance = this.balance;
  // Use CB for VB if cqAch is set and VB is not set
  if (this.cqAch) {
    return claimedBalance
  }
  // Use claimed balance if sent to SMP or received by SMP and VB is unavailable
  if (this.activityStatus) {
    const useClaimedIfNoVb = ['sentToSmp', 'receivedSmp'].indexOf(this.activityStatus) > -1;
    if (useClaimedIfNoVb) {
      return claimedBalance;
    }
  }

  return verifiedBalance;
}
function convertToLowerCase(whatever?: string) {
  if (whatever) {
    return whatever.toLowerCase();
  }
}

InventorySchema.set('toJSON', {getters: true});
InventorySchema.set('toObject', {getters: true});

export const Inventory: IInventoryModel = mongoose.model<IInventory, IInventoryModel>('Inventory', InventorySchema);

export default Inventory;
