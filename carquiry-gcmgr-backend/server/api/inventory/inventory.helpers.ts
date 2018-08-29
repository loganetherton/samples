import * as mongoose from "mongoose";
import * as uuid from 'node-uuid';
import * as _ from 'lodash';

import Inventory, {IInventory} from "./inventory.model";
import {updateInventory} from "../card/card.socket";
import ErrorLog from "../errorLog/errorLog.model";
import {determineSellTo} from "../card/card.helpers";
import Retailer from "../retailer/retailer.model";
import {DocumentNotFoundException, SellLimitViolationException} from "../../exceptions/exceptions";
import config from "../../config/environment";
import {ICompanySettings} from "../company/companySettings.model";
import {finalizeTransactionValues} from "../deferredBalanceInquiries/runDefers";
import {ICompany} from "../company/company.model";

// Sell to, for determining which SMP should get a card
export interface ISellTo {
  smp: string,
  rate: number,
  type: string
}

/**
 * Sell cards which have been added to the liquidation API
 */
export async function sellCardsInLiquidation(inventoryIds: string[] = []) {
  try {
    let inventories = [];
    if (inventoryIds.length) {
      inventories = await Inventory.find({_id: {$in: inventoryIds}});
    } else {
      inventories = await Inventory.find({
        soldToLiquidation: false,
        // Only if allowed to proceed
        proceedWithSale: {$ne: false},
        disableAddToLiquidation: { $nin: ['sell', 'all'] },
        // Don't sell disabled cards
        type: {$ne: 'DISABLED'},
        locked: {$ne: true},
        // Don't run transactions
        isTransaction: {$ne: true},
        balance: {$gt: 0}
      })
        .limit(10);
    }

    for (let inventory of inventories) {
      // Stop if inventory got locked by another server
      inventory = await Inventory.findById(inventory._id)
        .populate('card')
        .populate('retailer')
        .populate('company');
      if (inventory.locked) {
        continue;
      }
      // Lock inventory
      inventory.locked = true;
      await inventory.save();
      // Get retailer with merch values
      const retailer = inventory.retailer.populateMerchValues(inventory);
      if (retailer) {
        const company: ICompany = inventory.company;
        const companySettings = await company.getSettings(true);
        const sellTo = determineSellTo(retailer, inventory.balance, companySettings);
        inventory.soldToLiquidation = true;
        // No sale
        if (!sellTo || sellTo.smp === null) {
          sellTo.smp = '0';
          inventory.status = 'SALE_FAILED';
          // Sale
        } else {
          inventory.smp = sellTo.smp;
          inventory.liquidationRate = sellTo.rate;
          inventory.type = sellTo.type;
        }
        if (inventory.smp === '0') {
          inventory.status = 'SALE_FAILED';
          inventory.originalType = inventory.type;
          inventory.type = 'DISABLED';
        } else {
          inventory.status = 'SALE_NON_API';
          let balance = inventory.balance;
          let liquidationRate = inventory.liquidationRate;
          if (typeof balance !== 'number') {
            balance = 0 ;
          }
          if (typeof liquidationRate !== 'number') {
            liquidationRate = 0;
          }
          inventory.liquidationSoldFor = liquidationRate * balance;
          inventory.cqTransactionId = uuid();
        }
        // Unlock card
        inventory.locked = false;
        // inventory = calculateValues(inventory, inventory.company, false);
        inventory = inventory.save();
        // Notify frontend
        updateInventory.socketUpdate(inventory);
      }
    }
  } catch (err) {
    await ErrorLog.create({
      method: 'sellCardsInLiquidation',
      controller: 'runDefers',
      stack: err ? err.stack : null,
      error: err
    });
  }
}

/**
 * Finalize transaction values
 * @param inventory
 * @param dbCompanySettings
 * @param recalculating Recalculating a transaction which was previously calculated
 * @return {Promise.<*>}
 */
export async function finalizeTransaction(inventory: IInventory, dbCompanySettings: ICompanySettings, recalculating = false) {
  // Use either array of settings or a single settings
  const companySettings: ICompanySettings = dbCompanySettings[inventory._id] ? dbCompanySettings[inventory._id] : dbCompanySettings;
  let retailer;
  // Populate retailer if we have a plain object
  let retailerId = null;
  // Make sure we have a valid retailer object
  if (inventory.retailer.constructor.name === 'model') {
    retailer = inventory.retailer;
  } else {
    if (_.isPlainObject(inventory.retailer)) {
      retailerId = inventory.retailer._id;
    } else if (inventory.retailer instanceof mongoose.Types.ObjectId) {
      retailerId = inventory.retailer;
    }
    retailer = await Retailer.findById(retailerId);
  }
  if (!retailer) {
    throw new DocumentNotFoundException('Retailer not found', 404);
  }
  retailer = retailer.populateMerchValues(inventory);
  // Don't redetermine SMP if we're recalculating, since SMP might have changes since original purchase
  if (!recalculating) {
    // Sell to rates
    const sellTo = determineSellTo(retailer, inventory.balance, companySettings);
    // Unable to sell card
    if (!sellTo) {
      throw new SellLimitViolationException('Card violates sell limits', 400);
    }
    inventory = determineSmp(sellTo, inventory);
  }
  // Service fee RATE
  const serviceFeeRate = typeof inventory.serviceFee !== 'undefined' ? inventory.serviceFee : companySettings.serviceFee;
  const margin = typeof inventory.margin !== 'undefined' ? inventory.margin : companySettings.margin;
  const balance = typeof inventory.verifiedBalance === 'number' ? inventory.verifiedBalance : inventory.balance;
  // Service fee dollar value
  inventory.transaction.serviceFee = parseFloat((serviceFeeRate * (balance * (inventory.liquidationRate - margin))).toFixed(3));
  inventory.margin = typeof inventory.margin !== 'undefined' ? inventory.margin : companySettings.margin;
  // Lock
  inventory.soldToLiquidation = true;
  // Determine amount paid
  const cqPaid = Inventory.getCqPaid(balance, (inventory.liquidationRate - inventory.margin));
  // Create reserve
  const reserveAmount = Inventory.getReserveAmount(balance, config.reserveRate);
  inventory.transaction.reserveAmount = reserveAmount;
  // Get transaction values
  inventory = inventory.getTransactionValues(reserveAmount, cqPaid, balance);
  return await inventory.save();
}

/**
 * Lock/unlock all inventories
 * @param inventories
 * @param lock Lock or unlock
 * @return {Promise.<*>}
 */
function lockInventories(inventories: IInventory[], lock = true) {
  const promises: Promise<IInventory>[] = [];
  inventories.forEach(inventory => {
    inventory.locked = lock;
    promises.push(inventory.save());
  });
  return Promise.all(promises);
}

/**
 * Create reserve for inventories
 * @param inventories
 * @return {Promise.<Array|*>}
 */
async function createInventoryReserves(inventories: IInventory[]) {
  let final = [];
  for (let inventory of inventories) {
    final.push(await inventory.createReserve());
  }
  return final;
}

/**
 * Sell cards for transactions
 */
export async function completeTransactions() {
  const dbCompanySettings: any = {};
  let dbInventories: IInventory[];
  let dbReserves;
  return Inventory.find({
    soldToLiquidation: false,
    // Only if allowed to proceed
    proceedWithSale: {$ne: false},
    disableAddToLiquidation: { $nin: ['sell', 'all'] },
    // Don't sell disabled cards
    type: {$ne: 'DISABLED'},
    locked: {$ne: true},
    // Don't run transactions
    isTransaction: true,
    // Make sure not invalid
    valid: {$ne: false},
    balance: {$gt: 0}
  })
    .populate('card')
    .populate('retailer')
    .populate('company')
    .populate('store')
    .limit(10)
    .then(inventories => lockInventories(inventories))
    .then(async inventories => {
      dbInventories = inventories;
      const promises: ICompanySettings[] = [];
      for (const inventory of inventories) {
        promises.push(await inventory.company.getSettings(true));
      }
      return Promise.all(promises);
    })
    .then(settings => {
      settings.forEach((setting, index) =>{
        dbCompanySettings[dbInventories[index]._id] = setting;
      });
    })
    // Create reserve
    .then(async () => {
      // Calculate values for transactions
      let inventories = await finalizeTransactionValues(dbInventories, dbCompanySettings);
      return await createInventoryReserves(inventories);
    })
    // Add reserve reference to inventory, store, and company
    .then(async reserves => {
      dbReserves = reserves;
      for (let reserve of reserves) {
        await Inventory.addToRelatedReserveRecords(reserve);
      }
    })
    .then(() => lockInventories(dbInventories, false))
    .then(() => {})
    .catch(async err => {
      await ErrorLog.create({
        method: 'completeTransactions',
        controller: 'runDefers',
        stack: err ? err.stack : null,
        error: err
      });
      console.log('**************RESOLVE TRANSACTION ERR**********');
      console.log(err);
      // Unlock on fuck up
      lockInventories(dbInventories, false).then(() => {})
    });
}

/**
 * Determine SMP
 * @param sellTo { rate: 0.58, smp: 'cardCash', type: 'electronic' }
 * @param inventory
 * @return {*}
 */
function determineSmp(sellTo: ISellTo, inventory: IInventory) {
  // No sale
  if (!sellTo || sellTo.smp === null) {
    sellTo.smp = '0';
    inventory.status = 'SALE_FAILED';
    // Sale
  } else {
    inventory.smp = sellTo.smp;
    inventory.liquidationRate = sellTo.rate;
    inventory.type = sellTo.type;
  }
  if (inventory.smp === '0') {
    inventory.status = 'SALE_FAILED';
    inventory.originalType = inventory.type;
    inventory.type = 'DISABLED';
  } else {
    inventory.status = 'SALE_NON_API';
    let balance = inventory.balance;
    let liquidationRate = inventory.liquidationRate;
    if (typeof balance !== 'number') {
      balance = 0 ;
    }
    if (typeof liquidationRate !== 'number') {
      liquidationRate = 0;
    }
    inventory.liquidationSoldFor = liquidationRate * balance;
    inventory.cqTransactionId = uuid();
  }
  return inventory;
}
