import DaemonError from '../daemonError/daemonError.model';
import User from '../user/user.model';
import {doSyncWithBi} from '../retailer/retailer.controller';
import {
  completeTransactions,
  finalizeTransaction,
  sellCardsInLiquidation
} from '../inventory/inventory.helpers';

const biUpdateInteralLength = 1000 * 60 * 60 * 5;
const daemonEmail = 'daemon@daemon.com';
const intervalLength = 5000;
let daemonUser;
let dbDeferred;
let interval, biInterval;
let promises = [];


// SMP codes
export const CARDCASH = '2';
export const CARDPOOL = '3';

/**
 * Calculate transaction values
 * @param dbInventories
 * @param dbCompanySettings
 * @return {Promise.<*>}
 */
export async function finalizeTransactionValues(dbInventories, dbCompanySettings) {
  const finalInventories = [];
  for (let inventory of dbInventories) {
    finalInventories.push(await finalizeTransaction(inventory, dbCompanySettings));
  }
  return finalInventories;
}

/**
 * Update bi active every 5 hours
 */
function updateBiActive() {
  const fakeRes = {json: () => {}, status: () => {
    return {
      json: () => {}
    };
  }};
  doSyncWithBi({}, fakeRes);
}

/**
 * Begin the process
 */
function startInterval() {
  promises = [];
  // Find daemon
  User.findOne({email: daemonEmail})
  .then(daemon => {
    // Use daemon for making BI requests
    if (daemon) {
      daemonUser = daemon;
    } else {
      throw new Error('Could not find daemon');
    }
  })
  .then(() => {
    interval = setInterval(() => {
      // Attempt to sell any cards already in liquidation
      sellCardsInLiquidation();
      completeTransactions();
    }, intervalLength);
    biInterval = setInterval(() => {
      // Update BI active
      updateBiActive();
    }, biUpdateInteralLength);
  });
}

/**
 * Write errors to the Db
 */
function writeErrors() {
  const daemonError =  new DaemonError();
  daemonError.referenceId = dbDeferred._id;
  daemonError.referenceModel = 'DeferredBalanceInquiry';
  daemonError.save()
  .catch(err => {
    console.log('**************DAEMON ERROR SAVE ERROR**********');
    console.log(err);
  });
}
/**
 * Continually perform balance inquiries on those cards which were returned deferred
 *
 * @todo I need to run this using forever.js, just need to figure out how to get socket into it
 */
export default function runDefers() {
  try {
    startInterval();
  } catch (e) {
    console.log('**************CATCH RUN DEFERS**********');
    console.log(e);
    // Make note of the error
    writeErrors();
    // Kill the old
    clearInterval(interval);
    clearInterval(biInterval);
    // Bring in the new
    startInterval();
  }
}
