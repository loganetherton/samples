// Disable ssl rejection for retailer syncing
import StorageService from '../../storage';

process.env.NODE_TLS_REJECT_UNAUTHORIZED = '0';

import Retailer from './retailer.model';
import Inventory from '../inventory/inventory.model';
import '../company/autoBuyRate.model';
import Company from '../company/company.model';

import * as _ from 'lodash';
import * as fs from 'fs';
import * as csv from 'fast-csv';
import * as CsvWriter from 'csv-write-stream';
import * as moment from 'moment';
import * as mongoose from 'mongoose';
import config from '../../config/environment';
import {determineSellTo} from '../card/card.helpers';
import {getActiveSmps} from '../../helpers/smp';

import ErrorLog from '../errorLog/errorLog.model';

import * as superagent from 'superagent';
import SuperAgentWrapper from '../../wrappers/superagent.wrapper';

const auth = require('../auth/auth.service');

const superAgentWrapper = new SuperAgentWrapper(superagent);
// Amount to subtract from sell rate when card sells for less than default buy rate
const defaultBuyLessThanSell = 0.05;

/**
 * Import CSV
 * @param req
 * @param res
 */
exports.importCsv = (req, res) => {
  const stream = fs.createReadStream('/public/cardquiry/giftcard_manager/server/files/retailers.csv');
  const promises = [];
  // Convert rates to percentages
  const getRate = (item) => {
    let rate = 0;
    if (item) {
      rate = parseFloat(item).toFixed(2);
    }
    return rate;
  };

  const csvStream = csv()
    .on("data", function(record){
      let urlMatch, url, retailerRecord = new Retailer();
      record.forEach((item, key) => {
        switch (key) {
          case 0:
            retailerRecord.name = item;
            break;
          case 1:
            retailerRecord.uid = item;
            break;
          case 2:
            retailerRecord.offerType = item;
            break;
          case 3:
            retailerRecord.retailerId = item;
            break;
          case 4:
            urlMatch = item.match(/https:\/\/dl\.airtable\.com[^)]+/);
            url = '';
            if (urlMatch) {
              url = urlMatch[0];
            }
            retailerRecord.imageUrl = url;
            retailerRecord.imageOriginal = item;
            break;
          case 5:
            retailerRecord.buyRate = getRate(item);
            break;
          case 6:
            retailerRecord.sellRates.saveYa = getRate(item);
            break;
          case 7:
            retailerRecord.sellRates.best = getRate(item);
            break;
          case 8:
            retailerRecord.sellRates.sellTo = item;
            break;
          case 9:
            retailerRecord.sellRates.cardCash = getRate(item);
            break;
        }
      });
      promises.push(retailerRecord.save());

    })
    .on("end", function(){
      Promise.all(promises)
      .then(() => {
        return res.json();
      })
      .catch((err) => {
        return res.status(500).json(err);
      });
    });

  stream.pipe(csvStream);
};

/**
 * Add retailer URL
 * @param req
 * @param res
 */
exports.addRetailerUrl = (req, res) => {
  const stream = fs.createReadStream('./server/files/master-retailers-url-phone.csv');
  const promises = [];

  const csvStream = csv()
    .on("data", function(record){
      const uid = record[1].replace(',', '');
      const promise = Retailer.findOne({uid})
      .then(retailer => {
        retailer.verification = {
          url: record[4],
          phone: record[5]
        };
        return retailer.save();
      });
      promises.push(promise);
    })
    .on("end", function(){
      Promise.all(promises)
        .then(() => {
          return res.json();
        })
        .catch((err) => {
          return res.status(500).json(err);
        });
    });

  stream.pipe(csvStream);
};

/**
 * Save image types on retailers
 */
exports.retailerImageTypes = (req, res) => {
  const promises = [];
  let imageType, imageUrl;
  Retailer.find()
  .sort({name: 1})
  .then(retailers => {
    retailers.forEach(retailer => {
      imageUrl = retailer.imageUrl.split('.');
      imageType = imageUrl[imageUrl.length - 1];
      //retailer.imageType = imageType;
      promises.push(Retailer.update({_id: retailer._id}, {$set: {imageType}}));
    });
    return Promise.all(promises);
  })
  .then(() => {
    return res.json();
  })
  .catch(async err => {
    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'retailerImageTypes',
      controller: 'retailer.controller',
      stack: err ? err.stack : null,
      error: err
    });
    return res.status(500).json(err);
  });
};

/**
 * Get buy rates for auto-set buy rates
 * @param retailers
 * @param settings
 */
export function getBuyRateAuto(retailers, settings) {
  return retailers.map(retailer => {
    // Calculate rate based on auto buy rate settings
    const bestSellRate = retailer.sellRate;
    const nearestRoundDown = Math.floor(bestSellRate * 100 / 5) * 5;
    const key = `_${nearestRoundDown}_${nearestRoundDown + 5}`;
    let customerMargin = settings.autoBuyRates[key];
    if (!customerMargin) {
      // No margin, use sell rate
      customerMargin = defaultBuyLessThanSell;
    }
    try {
      retailer.buyRate = parseFloat((bestSellRate - customerMargin).toFixed(2));
    } catch (e) {
      retailer.buyRate = bestSellRate - customerMargin;
    }
    return retailer;
  });
}

/**
 * Filter retailers based on best sell rate and selected min sell rate value
 * @param retailers
 * @param minVal
 * @returns {*}
 */
function filterRetailersBasedOnMinSellRate(retailers, minVal) {
  return retailers.filter(retailer => {
    if (minVal) {
      // Return all for all
      if (minVal === 'All') {
        return retailer;
      }
      return retailer.sellRate > parseInt(minVal) / 100;
    }
    return retailer;
  });
}

/**
 * Get retailers based on the set buy rates
 * @param retailers
 * @param storeId
 */
export function getBuyRatesSet(retailers, storeId) {
  return retailers.map(retailer => {
    // Find buy rate relations for this store
    const thisBuyRateRelation = retailer.buyRateRelations.filter(relation => {
      if (relation && relation.storeId) {
        return relation.storeId.toString() === storeId.toString();
      }
      return false;
    });
    // Apply relation
    if (thisBuyRateRelation.length) {
      try {
        retailer.buyRate = thisBuyRateRelation[0].buyRate;
      } catch (e) {
        retailer.buyRate = config.defaultBuyRate;
      }
      // Set to default buy rate
    } else {
      if ((retailer.sellRate - defaultBuyLessThanSell) > config.defaultBuyRate) {
        retailer.buyRate = config.defaultBuyRate;
      } else {
        retailer.buyRate = parseFloat((retailer.sellRate - defaultBuyLessThanSell).toFixed(2));
      }
    }
    return retailer;
  });
}

/**
 * Set buy and sell rates on retailer
 * @param retailers Retailers (with values AFTER margin is applied)
 * @param settings Company settings
 * @param storeId Store ID
 * @param minVal Minimum sell rate to return
 * @param {Number} balance Card balance
 */
export function retailerSetBuyAndSellRates(retailers, settings = {margin: 0.03}, storeId, minVal, balance = null) {
  let returnArray = true;
  // Return a single retailer
  if (!Array.isArray(retailers)) {
    returnArray = false;
  }
  retailers = Array.isArray(retailers) ? retailers : [retailers];
  retailers = retailers.map(retailer => {
    // Get best sell rate (margin not included)
    const bestSellRate = determineSellTo(retailer, balance, settings);
    if (!bestSellRate) {
      return {sellRate: 0};
    }
    // Convert to plain if it's not already
    if (!_.isPlainObject(retailer)) {
      retailer = retailer.toObject();
    }
    retailer.sellRate = bestSellRate.rate - settings.margin;
    retailer.type = bestSellRate.type;
    return retailer;
  });
  // Filter out no sell rate
  retailers = retailers.filter(retailer => retailer.sellRate > 0);
  // Filter based on min val
  retailers = filterRetailersBasedOnMinSellRate(retailers, minVal);
  // Remove retailers with 0 sell rates
  retailers = retailers.filter(retailer => retailer.sellRate);
  // Determine best buy rate if rates are auto-set
  if (settings.autoSetBuyRates) {
    retailers = getBuyRateAuto(retailers, settings);
  } else {
    // Filter buy rates by store
    if (storeId) {
      retailers = getBuyRatesSet(retailers, storeId);
    }
  }
  const filteredRetailers = retailers.filter(retailer => retailer);
  // Array of retailers
  if (returnArray) {
    return filteredRetailers;
  // Single retailer
  } else {
    if (filteredRetailers.length) {
      return filteredRetailers[0];
    } else {
      return {};
    }
  }
}

/**
 * Retrieve retailers with buy and sell rates
 */
export async function getRetailersNew(req, res) {
  try {
    const {storeId, minVal = 0} = req.params;
    const isCsv = req.csv;
    let margin, company, settings;

    company = await Company.findOne({stores: storeId});
    settings = await company.getSettings();
    // Save margin
    margin = _.isUndefined(settings.margin) ? 0.03 : settings.margin;
    settings.margin = margin;
    // Retailers
    let retailers = await Retailer.find()
    .populate('buyRateRelations')
    .sort({name: 1});
    retailers = filterDisabledRetailers(retailers, company);
    // Get retailers with buy and sell rates set
    retailers = retailerSetBuyAndSellRates(retailers, settings, storeId, minVal);
    if (isCsv) {
      const csvDir = config.root + '/retailerCsv';
      if (!fs.existsSync(csvDir)){
        fs.mkdirSync(csvDir);
      }
      const csvWriter = CsvWriter({ headers: ['retailer', 'buyRate', 'sellRate', 'type']});
      const outFile = `${moment().format('YYYYMMDD')}-${storeId}.csv`;
      csvWriter.pipe(fs.createWriteStream(csvDir + '/' + outFile));
      retailers.forEach(retailer => {
        csvWriter.write([retailer.name, retailer.buyRate, retailer.sellRate, retailer.type]);
      });
      csvWriter.end();
      return res.json({url: `${config.serverApiUrl}${csvDir.replace(config.root + '/', '') + '/' + outFile}`});
    }
    return res.json(retailers);
  } catch (err) {
    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'getRetailersNew',
      controller: 'retailer.controller',
      stack: err ? err.stack : null,
      error: err
    });
    return res.status(500).json({});
  }
}

/**
 * Filter out disabled retailers
 * @param retailers
 * @param company
 */
function filterDisabledRetailers(retailers, company) {
  return retailers.filter(retailer => {
    if (!company.disabledRetailers) {
      return true;
    }
    return company.disabledRetailers.indexOf(retailer._id.toString()) === -1;
  });
}

/**
 * Get all retailers for card intake
 */
exports.queryRetailers = (req, res) => {
  const query = req.query.query;
  let dbCompany;
  Company.findOne({
    _id: req.user.company
  })
  .then(company => {
    dbCompany = company;
    // const user = req.user;
    return Retailer.find({name: new RegExp(query, 'i')})
      .populate('buyRateRelations')
      .sort({name: 1})
      .limit(10)
  })
  .then(retailers => {
    // Filter out disabled retailers
    retailers = filterDisabledRetailers(retailers, dbCompany);
    return res.json({retailers});
  })
  .catch(async err => {
    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'queryRetailers',
      controller: 'retailer.controller',
      stack: err ? err.stack : null,
      error: err
    });
    console.log('**************QUERY RETAILER ERR**********');
    console.log(err);
    return res.status(500).json(err);
  });
};

/**
 * Retrieve all rates
 */
export function getAllRates(req, res) {
  // Rates for return
  const rates = {};
  Retailer.find()
  .then(retailers => {
    const ratesFinal = [];
    retailers.forEach(retailer => {
      if (!rates[retailer.uid]) {
        rates[retailer.uid] = {};
      }
      _.forEach(retailer.getSmpSpelling().toObject(), (spelling, smp) => {
        if (smp.toLowerCase() === 'saveya') {
          return;
        }
        const rateObj = {
          smpSpelling: retailer.getSmpSpelling()[smp],
          retailer: retailer.name,
          smpType: retailer.getSmpType()[smp],
          max: retailer.getSmpMaxMin()[smp].max,
          min: retailer.getSmpMaxMin()[smp].min,
          _id: retailer._id,
          uid: retailer.uid,
          smp,
        };

        ratesFinal.push(Object.assign({}, rateObj, {
          sellRates: retailer.getSellRates()[smp],
          smpType: retailer.getSmpType()[smp],
          max: retailer.getSmpMaxMin()[smp].max,
          min: retailer.getSmpMaxMin()[smp].min,
          isMerch: false
        }));

        const maxMin = retailer.getSmpMaxMinMerch()[smp];

        ratesFinal.push(Object.assign({}, rateObj, {
          sellRates: retailer.getSellRatesMerch()[smp],
          smpType: retailer.getSmpTypeMerch()[smp],
          max: maxMin.max,
          min: maxMin.min,
          isMerch: true
        }));
      });
    });
    return ratesFinal;
  })
  .then(completeRates => {
    return completeRates.sort((current, next) => {
      if (current.retailer.toLowerCase() < next.retailer.toLowerCase()) {
        return -1;
      }
      if (current.retailer.toLowerCase() > next.retailer.toLowerCase()) {
        return 1;
      }
      return 0;
    });
  })
  .then(retailers => res.json({retailers}))
  .catch(async err => {
    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'getAllRates',
      controller: 'retailer.controller',
      stack: err ? err.stack : null,
      error: err
    });
    return res.status(500).json({});
  });
}

/**
 * Get BI info
 */
export function getBiInfo(req, res) {
  return Retailer.find()
  .then(retailers => res.json({retailers}))
  .catch(async err => {
    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'getBiInfo',
      controller: 'retailer.controller',
      stack: err ? err.stack : null,
      error: err
    });
    return res.status(500).json({});
  });
}

/**
 * Update BI info
 */
export function updateBiInfo(req, res) {
  const {_id, propPath, value} = req.body;
  const propToUpdate = propPath.join('.');
  Retailer.update({_id}, {
    $set: {
      [propToUpdate]: value
    }
  })
  .then(() => res.json())
  .catch(async err => {
    console.log('**************ERR IN UPDATE BI INFO**********');
    console.log(err);
    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'updateBiInfo',
      controller: 'retailer.controller',
      stack: err ? err.stack : null,
      error: err
    });
    res.status(500).json({message: err.toString()});
  });
}

/**
 * Download BI info CSV
 */
export function biInfoCsv(req, res) {
  Retailer.find()
  .then(retailers => {
    if (!fs.existsSync('biInfoCsv')){
      fs.mkdirSync('biInfoCsv');
    }
    const csvWriter = CsvWriter({ headers: ['retailer', 'url', 'phone']});
    const outFile = `biInfoCsv/${moment().format('YYYYMMDD')}.csv`;
    csvWriter.pipe(fs.createWriteStream(outFile));
    retailers.forEach(retailer => {
      csvWriter.write([retailer.name, retailer.verification.url, retailer.verification.phone]);
    });
    csvWriter.end();
    return res.json({url: `${config.serverApiUrl}${outFile}`});
  })
  .catch(async err => {
    console.log('**************ERR IN GET BI INFO CSV**********');
    console.log(err);
    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'biInfoCsv',
      controller: 'retailer.controller',
      stack: err ? err.stack : null,
      error: err
    });
    return res.status(500).json({message: err.toString()});
  });
}

/**
 * Upload Cardcash rates doc
 */
export function uploadCcRatesDoc(req, res) {
  const file = req.files[0];
  const ccRates = [];
  const fileName = `${__dirname}/rates/${file.filename}`;
  const stream = fs.createReadStream(fileName);
  const csvStream = csv()
    .on("data", function(record){
      /**
       * Fields:
       * 1) ID
       * 2) Name
       * 3) Percentage
       * 4) Max
       * 5) Method
       */
      // Create record
      const thisRecord = {
        id: record[0],
        name: record[1],
        percentage: record[2],
        max: record[3],
        method: record[4]
      };
      ccRates.push(thisRecord);
    })
    .on('end', () => {
      const promises = [];
      ccRates.forEach(rate => {
        let type;
        if (/online/i.test(rate.method)) {
          type = 'electronic';
        } else if (/mail/i.test(rate.method)) {
          type = 'physical';
        } else {
          type = 'disabled';
        }
        let max, percentage;
        try {
          max = parseFloat(rate.max);
        } catch (e) {
          max = 0;
        }
        try {
          percentage = parseFloat(rate.percentage);
          if (isNaN(percentage)) {
            percentage = 0;
          } else {
            if (percentage > 1) {
              percentage = percentage / 100;
            }
          }
        } catch (e) {
          percentage = 0;
        }
        promises.push(Retailer.update({
          'apiId.cardCash': rate.id
        }, {
          $set: {
            'smpSpelling.cardCash': rate.name,
            'sellRates.cardCash': percentage,
            'smpMaxMin.cardCash.max': isNaN(max) ? 0 : max,
            'smpType.cardCash': type
          }
        }).then(() => {}));
      });
      Promise.all(promises)
      .then(() => {
        fs.unlink(fileName);
        return res.json();
      });
    });

  stream.pipe(csvStream);
}

/**
 * Handle cardpool uploads
 * @param req
 * @param res
 * @param type
 */
function handleCp(req, res, type) {
  const file = req.files[0];
  const cpRates = [];
  const fileName = `${__dirname}/rates/${file.filename}`;
  const stream = fs.createReadStream(fileName);
  const csvStream = csv()
    .on("data", function(record){
      let thisRecord;
      /**
       * Fields:
       * 1) Name
       * 2) Type
       */
      // Rates
      if (type === 'rates') {
        // Create record
        thisRecord = {
          name: record[0],
          percentage: record[1].replace('%', '')
        };
        // Electronic/physical
      } else if (type === 'electronicPhysical') {
        thisRecord = {
          name: record[0],
          electronicPhysical: record[1]
        };
      }
      cpRates.push(thisRecord);
    })
    .on('end', () => {
      const promises = [];
      if (type === 'rates') {
        cpRates.forEach(rate => {
          // Make sure we have a reasonable percentage
          let percentage = parseFloat(rate.percentage);
          if (isNaN(percentage)) {
            percentage = 0;
          } else {
            if (percentage > 1) {
              percentage = percentage / 100;
            }
          }
          promises.push(Retailer.update({
            'smpSpelling.cardPool': rate.name
          }, {
            $set: {
              'sellRates.cardPool': percentage
            }
          }).then(() => {}));
        });
      } else if (type === 'electronicPhysical') {
        cpRates.forEach(rate => {
          let type = 'physical';
          if (/both/i.test(rate.electronicPhysical)) {
            type = 'electronic';
          }
          promises.push(Retailer.update({
            'smpSpelling.cardPool': rate.name
          }, {
            $set: {
              'smpType.cardPool': type
            }
          }).then(() => {}));
        });
      }
      return Promise.all(promises)
      .then(() => {
        fs.unlink(fileName);
        return res.json();
      });
    });

  stream.pipe(csvStream);
}

/**
 * Upload Cardpool rates doc
 */
export function uploadCpRatesDoc(req, res) {
  handleCp(req, res, 'rates');
}

/**
 * Upload Cardpool electronic/physical doc
 */
export function uploadElectronicPhysical(req, res) {
  handleCp(req, res, 'electronicPhysical');
}

/**
 * Get all retailers
 */
export function getAllRetailers(req, res) {
  return Retailer.find()
    .then(retailers => res.json(retailers))
    .catch(async err => {
      await ErrorLog.create({
        body: req.body ? req.body : {},
        params: req.params ? req.params : {},
        method: 'getAllRetailers',
        controller: 'retailer.controller',
        stack: err ? err.stack : null,
        error: err
      });
      return res.status(500).json({});
    });
}

/**
 * Change the GiftSquirrel ID of a retailer
 */
export function setGsId(req, res) {
  Retailer.findByIdAndUpdate(req.params.retailerId, {
    gsId: req.body.gsId
  })
  .then(retailer => res.json(retailer))
  .catch(async err => {
    console.log('**************ERR IN SET GS ID**********');
    console.log(err);
    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'setGsId',
      controller: 'retailer.controller',
      stack: err ? err.stack : null,
      error: err
    });
    return res.status(500).json(err);
  });
}

/**
 * Set retailer property
 */
export async function setProp(req, res) {
  try {
    const {propPath} = req.body;
    let {value} = req.body;
    let retailer = await Retailer.findById(req.params.retailerId);
    if (!retailer) {
      throw new Error('Retailer not found');
    }
    if (propPath[0] === 'sellRates' || propPath === 'sellRatesMerch') {
      value = parseFloat(value);
      // Forgotten decimal
      if (value > 1) {
        value = value / 100;
      }
    }
    _.set(retailer, propPath, value);
    retailer = await retailer.save();
    return res.json(retailer);
  } catch (err) {
    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'setProp',
      controller: 'retailer.controller',
      stack: err ? err.stack : null,
      error: err
    });
    console.log('**************ERR IN RETAILER SET PROP**********');
    console.log(err);
    return res.status(500).json({err: err.message});
  }
}

/**
 * Return inventory statistics by retailer for admin and companies
 */
export async function salesStats(req, res) {

  const queryParams =
  {
    dateBegin: req.params.dateBegin,
    dateEnd: req.params.dateEnd,
    storeId: req.params.storeId
  };

  const query = [
    {"$match":{"created":{"$gte":new Date(queryParams.dateBegin),"$lte":new Date(queryParams.dateEnd)}}},
    {$group : {
       _id: "$retailer",
       totalCount: {$sum: 1},
       totalRejectedCount: {$sum: {$switch: {branches:[{"case": {"$eq": ["$rejected", true]}, "then": 1 }], "default": 0}}},
       totalAmtBalance: {$sum: "$balance"},
       avgBalance: {$avg: "$balance"},
       totalAmtVerified: {$sum: "$verifiedBalance"},
       avgVerified: {$avg: "$verifiedBalance"},
       totalAmtBuy: {$sum: "$buyAmount"},
       avgBuy: {$avg: "$buyAmount"},
       totalAmtRejected: {$sum: "$rejectAmount"},
       avgRejected: {$avg: "$rejectAmount"}
       }},
       {$lookup:
           {
           from: "retailers",
             localField: "_id",
             foreignField: "_id",
             as: "retailerObject"
          }
      },
      { $unwind: "$retailerObject" },
       {$project:{
         retailer: "$retailerObject.name",
         totalCount: "$totalCount",
         totalRejectedCount: "$totalRejectedCount",
         rejectedCountPercentage: {$cond: [ { $eq: [ "$totalCount", 0 ] }, "N/A",{$divide: ["$totalRejectedCount", "$totalCount"]}]},
           totalAmtBalance: "$totalAmtBalance",
           avgBalance: "$avgBalance",
           totalAmtVerified: "$totalAmtVerified",
           avgVerified: "$avgVerified",
           totalAmtBuy: "$totalAmtBuy",
           avgBuy: "$avgBuy",
           totalAmtRejected: "$totalAmtRejected",
           avgRejected: "$avgRejected",
           rejectedAmtPercentage: {$cond: [ { $eq: [ "$totalAmtBuy", 0 ] }, "N/A",{$divide: ["$totalAmtRejected", "$totalAmtBuy"]}]}

       }},
     {$sort : {totalCount: -1}}
   ];

   //if user is not an admin, filter by company
   if (req.user.role != 'admin') {
     query[0]["$match"].company = req.user.company;

     if (queryParams.storeId) {
       query[0]["$match"].store = mongoose.Types.ObjectId(queryParams.storeId);
     }
   }

   Inventory.aggregate(query)
   .exec((err, results) => {
     if (err) {
       return res.status(500).json(err);
     }
     return res.json({results});
   });

}

/**
 * Match retailer from BI based on the name segments
 * @param chunks Separate segments of the BI retailer name
 * @param chunkSize The current size of segment to examine
 * @return {Promise.<*>}
 */
async function queryRetailerByChunk(chunks, chunkSize = 1) {
  const chunkSlice = chunks.slice(0, chunkSize);
  let name = chunkSlice.join(' ');
  name = name.replace(/[.'"\/]/g, '\.?');
  name = name.replace(/(\w*)s/g, '$1[\s\/\'"]*');
  name = name.replace(/\s/g, '[\\s\\/\'" ]*');
  name = new RegExp(name, 'i');
  const retailers = await Retailer.find({name});
  if (retailers.length === 1) {
    return retailers[0];
  } else if (retailers.length > 1) {
    let matchedRetailer;
    // Try to match full retailer
    matchedRetailer = retailers.filter(retailer => {
      return retailer.name === chunks.join(' ');
    });
    if (matchedRetailer.length === 1) {
      return matchedRetailer[0];
    }
    // Match by single chunk
    matchedRetailer = retailers.filter(retailer => {
      return retailer.name === chunkSlice.join(' ');
    });
    if (matchedRetailer.length === 1) {
      return matchedRetailer[0];
    }
  }
  chunkSize = chunkSize + 1;
  if (chunkSize > chunks.length) {
    return name;
  }
  return queryRetailerByChunk(chunks, chunkSize);
}

/**
 * Handle actual sync with BI
 * @returns {Promise<*>}
 */
export async function doSyncWithBi() {
  const response = await superAgentWrapper.get(`${config.bi.baseUrl}/retailers`);

  if (response.status > 400) {
    return {
      status: 400,
      message: {error: 'Could not query retailers'}
    };
  }

  let retailers;

  // JSON response (as it should be)
  if (Object.keys(response.body)) {
    retailers = JSON.parse(response.body);
    // Text response
  } else {
    retailers = JSON.stringify(response.text)
  }

  await Retailer.update({}, {
    $unset: {
      gsId: false
    },
  }, {
    multi: true
  });

  let failedToMatch = 0;

  for (const [index, biRetailer] of retailers.entries()) {
    setTimeout(async () => {
      let name = biRetailer.name;
      const nameChunks = name.split(' ');
      // Get retailer as best we can
      const retailer = await queryRetailerByChunk(nameChunks);
      if (!retailer || typeof retailer === 'string' || retailer.constructor.name !== 'model') {
        failedToMatch = failedToMatch + 1;
        return;
      }
      // GS ID
      if (biRetailer.retailer_id) {
        retailer.gsId = biRetailer.retailer_id;
      }
      // Addtoit ID
      if (biRetailer.ai_id) {
        retailer.aiId = biRetailer.ai_id;
      }
      retailer.biActive = biRetailer.active === 1;
      const updateResponse = await retailer.save();
      if (!updateResponse || updateResponse.n < 1) {
        failedToMatch = failedToMatch + 1;
      }
    }, 2000 * index);
  }
  return {
    status: 200,
    message: ''
  };
}

/**
 * Sync retailers with BI
 */
export async function syncWithBi(req, res) {
  try {
    const response = await doSyncWithBi();
    return res.status(response.status).json(response.message);
  } catch (err) {
    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'syncWithBi',
      controller: 'retailer.controller',
      stack: err ? err.stack : null,
      error: err
    });
    return res.status(500).json({});
  }
}

/**
 * Create a new retailer based on an old one (such as a merch credit retailer)
 */
export function createNewRetailerBasedOnOldOne(req, res) {
  const body = req.body.retailer;
  Retailer.findOne({
    $or: [
      {gsId: body.gsId},
      {retailerId: body.gsId}
    ]
  })
  .then(retailer => {
    if (!retailer) {
      return res.status(400).json();
    }
    const old = retailer.toObject();
    old.name = body.name;
    old.original = old._id;
    delete old._id;
    const newRetailer = new Retailer(old);
    return newRetailer.save();
  })
  .then(retailer => {
    if (!retailer) {
      return;
    }
    return res.json();
  })
  .catch(async err => {
    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'createNewRetailerBasedOnOldOne',
      controller: 'retailer.controller',
      stack: err ? err.stack : null,
      error: err
    });
    return res.status(500).json({});
  });
}

/**
 * Create new retailer
 */
export async function createRetailer(req, res){
  try {
    const body = req.body;

    const result = await Retailer.findOne({
      name: body.name
    });

    if (!result) {
      let retailer = new Retailer(body);
      getActiveSmps().forEach(smp => {
        // Not decimal
        if (typeof retailer.sellRates[smp] === 'number' && retailer.sellRates[smp] > 1) {
          retailer.sellRates[smp] = (retailer.sellRates[smp] / 100).toFixed(2);
        }
      });

      retailer.save()
      .then(() => res.json({msg: "Retailer saved successfully"}));
    } else {
      res.status(400).json({
        msg: "Retailer exists"
      });
    }
  } catch (err) {
    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'createRetailer',
      controller: 'retailer.controller',
      stack: err ? err.stack : null,
      error: err
    });
    return res.status(500).json(err);
  }
}
/**
 * Toggle disable retailers for a company
 */
export function toggleDisableForCompany(req, res) {
  const body = req.body;
  Company.findOne({
    _id: body.company
  })
  .then(company => {
    body.retailers.forEach(retailer => {
      const index = company.disabledRetailers.indexOf(retailer.toString());
      // Exists, so remove
      if (index !== -1) {
        company.disabledRetailers.splice(index, 1);
      } else {
        company.disabledRetailers.push(retailer);
      }
    });
    return company.save();
  })
  .then(() => res.json())
  .catch(async err => {
    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'toggleDisableForCompany',
      controller: 'retailer.controller',
      stack: err ? err.stack : null,
      error: err
    });
    return res.status(500).json({});
  });
}

/**
 * Set a retailer's number and PIN regex
 */
export async function setRetailerRegex(req, res) {
  const {number, pin} = req.body;
  const {retailer} = req.params;

  const dbRetailer = await Retailer.findById(retailer);

  if (!dbRetailer) {
    return res.status(400).json({err: 'Retailer not found'});
  }

  dbRetailer.numberRegex = number;
  dbRetailer.pinRegex = pin;

  await dbRetailer.save();

  return res.json({
    number: dbRetailer.numberRegex,
    pin: dbRetailer.pinRegex
  });
}

/**
 * Download CSV of electronic brands
 * @returns {Promise<void>}
 *
 */
export async function adminDownloadElectronicBrands(req, res) {
  let retailers = await Retailer.find()
  .sort({name: 1});
  const csvDir = config.root + '/retailerCsv';
  if (!fs.existsSync(csvDir)){
    fs.mkdirSync(csvDir);
  }
  // Filter for electronic-only retailers
  retailers = retailers.filter(retailer => {
    const types = Object.values(retailer.smpType);
    const electronic = types.filter(type => type === 'electronic');
    return electronic.length;
  });
  const csvWriter = CsvWriter({ headers: ['Retailer']});
  const outFile = `${moment().format('YYYYMMDD')}_electronic_brands.csv`;
  let fileStream = fs.createWriteStream(csvDir + '/' + outFile);
  csvWriter.pipe(fileStream);
  retailers.forEach(retailer => {
    csvWriter.write([retailer.name]);
  });
  // Upload to s3
  fileStream.on('finish', async () => {
    const destPath = `${csvDir}/${outFile}`;
    await StorageService.write(`${destPath}`, `retailer/electronic/${outFile}`);
    const url = await StorageService.getDownloadUrl(`retailer/electronic/${outFile}`);
    res.json({url});
  });
  csvWriter.end();
}
