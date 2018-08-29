import * as mongoose from 'mongoose';
import config from '../../config/environment';
import CallbackLog from './callbackLog.model';
import * as moment from 'moment';
import Callback from './callback';
import {ErrorLogger} from '../../loggers';

const errorLogger = new ErrorLogger();

// Connect to database
mongoose.connect(config.mongo.uri, config.mongo.options);
mongoose.connection.on('error', function(err) {
  console.error('MongoDB connection error: ' + err);
  process.exit(-1);
});

const batchSize = 10;
const batchDelay = 3 * 1000;
const startDate = new Date(moment().subtract(3, 'days').format('YYYY-MM-DD'));

/**
 * Resend failed callbacks
 *
 * @param {Number} batchSize Maximum number of callbacks to try per batch
 * @param {Number} batchDelay Time to wait between each batch in milliseconds
 * @param {Date} startDate The starting date to search for callbacks that failed
 */
async function resendCallbacks(batchSize, batchDelay, startDate) {
  const callbackConstraint = {
    success: false,
    created: {$gte: startDate},
    resent: {$ne: true}
  };

  const totalCallbacks = await CallbackLog.count(callbackConstraint);
  const callbackCursor = await CallbackLog.find(callbackConstraint).populate('card,biRequestLog').cursor();
  let processed = 0;
  const promises = [];

  for (const i of gen(totalCallbacks)) {
    const batch = [];

    let callback;
    while (batch.length < batchSize && (callback = callbackCursor.next().value)) {
      batch.push(callback);
    }

    for (const callbackLog of batch) {
      let callback = false;

      if (callbackLog.card) {
        callback = new Callback(callbackLog.url);
        callback = wrapCallbackResendInPromise(
          callback.sendCallback.bind(callback, callbackLog.card, callbackLog.callbackType, null, true)
        );
      } else {
        if (callbackLog.biRequestLog) {
          callback = new Callback(callbackLog.url);
          callback = wrapCallbackResendInPromise(
            callback.sendCallback.bind(callback, callbackLog.biRequestLog, callbackLog.callbackType, callbackLog.url, true)
          );
        }
      }

      if (callback) {
        promises.push(callback);
      }

      callbackLog.resent = true;
      try {
        // We don't really want to await on this, but just in case for whatever reason
        // it fails to save, it can't hurt to log the error for debugging purposes.
        await callbackLog.save();
      } catch (e) {
        errorLogger.log(e);
      }
    }

    // Pause temporarily before continuing to the next batch
    await new Promise(resolve => { setTimeout(resolve, batchDelay); });
  }

  // Don't need a try/catch because exception is handled inside each Promise
  await Promise.all(promises);

  process.exit();
}

/**
 * Creates a generator for the main loop
 * Normally this wouldn't be necessary, but at the moment we have to use "for of"
 * to be able to reliably use await inside the loop body
 *
 * @param {Number} i The upper bound of the generator which should be the total of callbacks to process
 * @return {Generator}
 */
function* gen(i) {
  for (let x = 0; x < i; x += batchSize) {
    yield x;
  }
}

/**
 * Wrap the send callback function in a Promise
 *
 * @param {Function} resend
 * @return {Promise}
 */
function wrapCallbackResendInPromise(resend) {
  return new Promise(async (resolve) => {
    try {
      await resend();
      resolve();
    } catch (e) {
      errorLogger.log(e);
    }
  });
}

resendCallbacks(batchSize, batchDelay, startDate);
