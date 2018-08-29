import * as mongoose from 'mongoose';
import {config} from './config/environment';
import Inventory from './api/inventory/inventory.model';
import {ErrorLogger} from './loggers';

import './api/company/autoBuyRate.model';

const errorLogger = new ErrorLogger();

// Connect to database
mongoose.connect(config.mongo.uri, config.mongo.options);
mongoose.connection.on('error', function(err) {
  console.error('MongoDB connection error: ' + err);
  process.exit(-1);
});

async function backfill() {
  try {
    console.log('Updating new ES mapping...');
    await Inventory.createMapping();
    console.log('Applying credit adjustment...');
    await Inventory.update({credited: true}, {adjustmentStatus: 'credit'}, {multi: true});
    console.log('Applying chargeback adjustment...');
    await Inventory.update({rejected: true, deduction: {$exists: true, $ne: ''}}, {adjustmentStatus: 'chargeback'}, {multi: true});
    console.log('Applying denial adjustment...');
    await Inventory.update({rejected: true, adjustmentStatus: {$ne: 'chargeback'}}, {adjustmentStatus: 'denial'}, {multi: true});
    console.log('Database updated. Please wait a few moments for ES to finish being updated before stopping the script.');

    console.log('**************BACKFILLING INVENTORIES WITH VALUES WHICH ARE NOW TO BE SAVED IN THE DB**********');
    const inventoryCount = await Inventory.find().count();
    const promises = [];
    // Just initiate a basic change on each inventory to backfill the values necessary
    for (let currentIndex = 0; currentIndex < inventoryCount; currentIndex = currentIndex + 10) {
      const thisInventoryBatch = await Inventory.find({updated: {$exists:false}}).sort({created: -1}).limit(10).skip(currentIndex);
      for (const inventory of thisInventoryBatch) {
        inventory.updated = new Date();
        promises.push(inventory.save());
      }
    }
    setTimeout(async () => {
      await Promise.all(promises);
      console.log('**************DONE**********');
      process.exit();
    }, 2000);
  } catch (e) {
    console.log('**************CATCH IN BACKFILL**********');
    console.log(e);
    errorLogger.log(e);
    console.log('An error has occurred. Check the log for details.');
  }
}

backfill();
