import * as mongoose from 'mongoose';
import * as moment from 'moment';

import config from '../../config/environment';
import BiRequestLog from '../biRequestLog/biRequestLog.model';
import Card from '../card/card.model';
import Inventory, {IInventory} from '../inventory/inventory.model';
import {completeCardAndInventory, makeFakeReqRes} from '../lq/lq.controller';
import Retailer from '../retailer/retailer.model';
import User from '../user/user.model';
import {sellCardsInLiquidation} from "../inventory/inventory.helpers";
import {doAddToInventory} from "./card.controller";
import {IGenericExpressResponse} from "../../helpers/interfaces";
import {ICompleteCardFromBiParams} from "../lq/lq.interfaces";
import {IAddToInventoryParams} from "./card.interfaces";
import ErrorLog from "../errorLog/errorLog.model";

(async () => {
  mongoose.connect(config.mongo.uri, config.mongo.options);
  mongoose.connection.on('error', function(err) {
      console.error('MongoDB connection error: ' + err);
      process.exit(-1);
    }
  );
  let req;
  let user;
  try {
    const begin = moment().subtract(60, 'days');
    const logs = await BiRequestLog.find({
      created: {$gt: begin.toDate()},
      card: {$exists: false},
      balance: {$gt: 0},
      user: {$exists: true}
    });
    // Sync
    for (let log of logs) {
      try {
        user = await User.findById(log.user);
        const retailer = await Retailer.findById(log.retailerId);
        const biRetailerId = retailer.gsId || retailer.aiId;
        req = {
          body: {
            fixed: 1,
            invalid: 0,
            number: log.number,
            pin: log.pin,
            balance: log.balance,
            retailerId: biRetailerId
          },
          user
        };

        const [] = makeFakeReqRes(req);
        const body: ICompleteCardFromBiParams = {
          log,
          balance: log.balance,
          callbackStackId: null,
          user
        };
        // Complete card, send callback, etc
        const completeCardResponse: IGenericExpressResponse = await completeCardAndInventory(body);
        // No success
        if (completeCardResponse.status !== config.statusCodes.success) {
          const card = await Card.findOne({
            number: log.number,
            pin: log.pin,
            retailer: log.retailerId
          });
          if (card) {
            log.card = card._id;
            log = await log.save();
            if (!card.inventory) {
              const addToInventoryParams: IAddToInventoryParams = {
                userTime: new Date(card.userTime),
                modifiedDenials: 0,
                store: card.store.toString(),
                transaction: null,
                callbackUrl: log.callbackUrl,
                user: req.user,
                cards: [card]
              };
              const addToInventoryResponse = await doAddToInventory(addToInventoryParams);
              if (addToInventoryResponse.status === config.statusCodes.success) {
                const receipt = addToInventoryResponse.message;
                const inventories: IInventory[] = await Inventory.find({inventories: {$in: [receipt.inventories]}});
                for (const inventory of inventories) {
                  await sellCardsInLiquidation([inventory._id]);
                }
              }
            }
          }
        }
      } catch (err) {
        await ErrorLog.create({
          body: req.body ? req.body : {},
          params: req.params ? req.params : {},
          method: 'sellUnsold',
          controller: 'main',
          stack: err ? err.stack : null,
          error: err,

        });
      }
    }
    process.exit(0);
  } catch (err) {
    console.log('**************SELL UNSOLD ERR**********');
    console.log(err);
    process.exit(1);
  }
})();
