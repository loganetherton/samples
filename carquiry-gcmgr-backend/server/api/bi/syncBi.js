import * as mongoose from 'mongoose';
import * as moment from 'moment';

import config from '../../config/environment';
import BiRequestLog from '../biRequestLog/biRequestLog.model';
import BiService from './bi.request';
import '../inventory/inventory.model';
import {
  bi,
  biCompleted,
  makeFakeReqRes,
} from '../lq/lq.controller';
import {createFakeReqResBiInsert} from '../admin/admin.controller';
import Retailer from '../retailer/retailer.model';
import User from '../user/user.model';
import Inventory from '../inventory/inventory.model';

(async () => {
  mongoose.connect(config.mongo.uri, config.mongo.options);
  mongoose.connection.on('error', function(err) {
      console.error('MongoDB connection error: ' + err);
      process.exit(-1);
    }
  );
  try {
    const begin = moment().subtract(3, 'days');
    const logs = await BiRequestLog.find({created: {$gt: begin.toDate()}, $or: [
        {requestId: {$exists: false}},
        {responseCode: config.biCodes.defer}
      ] });
    // Sync
    for (const log of logs) {
      const formatted = log.toObject();
      formatted.retailer = formatted.retailerId;
      let data = {};
      let noRequestId = false;
      if (log.requestId) {
        data = await BiService.getRecord(log.requestId);
      } else {
        noRequestId = true;
      }
      const retailer = await Retailer.findById(log.retailerId);
      // Not found, reinsert
      if (data.responseCode === config.biCodes.unknownRequest) {
        data = {
          cardNumber: log.number,
          retailerId: retailer.gsId || retailer.aiId
        };
        if (formatted.pin) {
          data.pin = formatted.pin;
        }
        if (log.requestId) {
          data.requestid = log.requestId;
        }
        // Insert into BI with requestId
        await BiService.insert(data);
        // Update record
      } else if ([config.biCodes.success, config.biCodes.invalid].indexOf(data.responseCode) > -1) {
        const [biFakeReq, biFakeRes] = makeFakeReqRes({});
        const biCompleteRetailer = retailer.gsId || retailer.aiId;
        let balance = parseFloat(data.balance);
        if (isNaN(balance)) {
          balance = 0;
        }
        const biCompletedBody = {
          number: formatted.number,
          retailerId: biCompleteRetailer,
          invalid: balance ? 0 : 1,
          balance: balance ? balance : 0,
          autoSell: true
        };
        if (formatted.pin) {
          biCompletedBody.pin = formatted.pin;
        }
        biFakeReq.body = biCompletedBody;
        biFakeReq.get = () => config.biCallbackKey;
        biFakeReq.params = {requestId: data.request_id};
        await biCompleted(biFakeReq, biFakeRes);
        // No request ID, insert new record (prod)
      } else if (noRequestId) {
        const user = await User.findById(log.user);
        // insert into BI
        const [fakeReq, fakeRes] = createFakeReqResBiInsert(formatted, retailer, user, log.callbackUrl, user.email);
        await bi(fakeReq, fakeRes);
      }
    }
    process.exit(0);
  } catch (err) {
    console.log('**************SYNC BI ERR**********');
    console.log(err);
    process.exit(1);
  }
})();

// /**
//  * Graph average sales over a period of time
//  * @returns {Promise<void>}
//  */
// async function graphSalesByTime() {
//   let startDate = moment('2018-01-01');
//   let endDate = moment('2018-04-01');
//   const graph = {};
//
//   while (startDate < endDate) {
//     let hour = 0;
//     while (hour < 24) {
//       const inventories = await Inventory.find({created: {$gt: startDate.format(), $lt: startDate.add(1, 'hours').format()}});
//       let total = 0;
//       inventories.forEach(i => {
//         total += i.verifiedBalance;
//       });
//       if (typeof graph[hour] === 'undefined') {
//         graph[hour] = [total];
//       } else {
//         graph[hour].push(total);
//       }
//       startDate = startDate.add(1, 'hours');
//       hour++;
//     }
//   }
//
//   for (const key in graph) {
//     const len = graph[key].length;
//     const total = graph[key].reduce((accumulator, currentValue) => accumulator + currentValue);
//     graph[key] = total / len
//   }
// }
