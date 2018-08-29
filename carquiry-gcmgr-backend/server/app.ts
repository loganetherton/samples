if (process.env.NODE_ENV === 'production') {
  require('newrelic');
}
/**
 * Main entry point
 */
require('source-map-support').install();

import 'babel-polyfill';
// Register models
import './api/reserve/reserve.model';
import './api/customerEdit/customerEdit.model';
import './api/biRequestLog/biRequestLog.model';
import './api/test/test.model';
import './api/buyRate/buyRate.model';
import './api/systemSettings/systemSettings.model';
import './api/log/logs.model';
import './api/errorLog/errorLog.model';
import './api/frontendErrorLog/frontendErrorLog.model';
import './api/deferredBalanceInquiries/deferredBalanceInquiries.model';
import './api/cardUpdates/cardUpdates.model';
import './api/bi/biSolutionLog.model';
import './api/callbackLog/callbackLog.model';
import './api/denialPayment/denialPayment.model';
import './api/user/resetPasswordToken.model.js';
import './api/daemonError/daemonError.model';
import './api/company/autoBuyRate.model';
import './api/vista/vistaLog.model';
import './api/elasticLog/elasticLog.model';
import './api/receipt/receipt.model';
import './api/user/user.model';
import './api/batch/batch.model';
import './api/retailer/retailer.model';
import './api/card/card.model';
import './api/inventory/inventory.model'
import './api/customer/customer.model';
import './api/company/companySettings.model'
import './api/stores/store.model'
import './api/company/company.model'
import * as express from 'express';
import * as mongoose from 'mongoose';
import * as socketio from 'socket.io';
import * as http from 'http';
// Basic logger
import logger from './config/logger';
// Debug mongoose
import debugMongo from './config/debugMongo';
// Run defers
import runDefers from './api/deferredBalanceInquiries/runDefers';
import autoRecon from './api/reconciliation/autoRecon';


import cardSocketConnect from './config/cardSocket';
import notifyUnsolvedBootstrap from './config/notifyUnsolvedSocket';

import {listenForRedisEvents} from './helpers/redis';

import config from './config/environment';

console.log('**************NO CALLBACKS**********');
console.log(config.noCallbacks);


// Set default node environment to development
process.env.NODE_ENV = process.env.NODE_ENV || 'development';

const env = process.env.NODE_ENV;

(<any>mongoose).Promise = require('bluebird');
debugMongo(mongoose);

// Connect to database
mongoose.connect(config.mongo.uri, config.mongo.options);
mongoose.connection.on('error', function(err) {
    console.error('MongoDB connection error: ' + err);
    process.exit(-1);
  }
);

// Setup server
const app = express();

const server = http.createServer(app);

// Get the x-forwarded-for header value, rather than the ELB IP
app.enable('trust proxy');

if (config.isSocketServer) {
  console.log('**************RUNNING AS SOCKET SERVER**********');
  const cardSocket = socketio(server, {
    serveClient: config.env !== 'production',
    path: '/cardIntake',
    origins: 'https://gcmgr.cardquiry.com'
  });
  const notifyUnsolvedSocket = socketio(server, {
    serveClient: false,
    path: '/bi',
    origins: 'https://gcmgr.cardquiry.com'
  });

  const testSocket = socketio(server, {
    serveClient: false,
    path: '/test',
    origins: 'https://gcmgr.cardquiry.com'
  });

  testSocket.on('connection', function (socket) {
    console.log('**************CONNECTED TEST**********');
    socket.on('test', function (data) {
      console.log('**************TEST DATA RECEIVED**********');
      console.log(data);
      socket.emit('test', {data: 'response'});
    });
  });

  cardSocketConnect(cardSocket);
  // Pub/sub socket to receive notifications about unsolved cards
  notifyUnsolvedBootstrap(notifyUnsolvedSocket);
  // Listen for Redis events
  listenForRedisEvents();
} else {
  console.log('**************CONNECTING TO SOCKET SERVER**********');
  const io = require('socket.io-client');

  // Listen on API port
  const socket = io(config.socketServerAddress, {
    path: '/test',
    secure: true
  });

  socket.on('connection', function () {
    socket.emit('test', {data: 'request'});

    socket.on('test', function (data: any) {
      console.log('**************RECEIVED TEST RESPONSE**********');
      console.log(data);
    });
  });

  socket.emit('test', {data: 'request'});

  socket.on('test', function (data: any) {
    console.log('**************RECEIVED TEST RESPONSE**********');
    console.log(data);
  });
}

// Log all requests and responses
app.use(function(req, res, next) {
  res.on('finish', logger.bind(null, req, res, next));
  next();
});

// Init app
require('./config/express')(app);
require('./routes')(app);

// Run defer if in development or
if (process.env.RUN_DEFER === 'true' || env === 'development') {
  runDefers();
}

if (process.env.AUTO_RECON === 'true' || env === 'development') {
  autoRecon();
}

/**
 * Generic not found route
 */
app.use((req, res) => {
  return res.status(404).send('Not found');
});

// Start server
server.listen(config.port, config.ip, function () {
  console.log('Express server listening on %d, in %s mode', config.port, app.get('env'));
});

// Expose app
module.exports = app;
