/**
 * Main application routes
 */
import * as express from 'express';
import * as path from 'path';

module.exports = function(app) {
  // Auth
  app.use('/api/auth', require('./api/auth'));
  // Users
  app.use('/api/users', require('./api/user'));
  // Callback log
  app.use('/api/callbacks', require('./api/callbackLog'));
  // Companies
  app.use('/api/companies', require('./api/company'));
  // Retailers
  app.use('/api/retailers', require('./api/retailer'));
  // Customers
  app.use('/api/customers', require('./api/customer'));
  // Card
  app.use('/api/card', require('./api/card'));
  // Sell rate
  // app.use('/api/sellRate', require('./api/sellRte'));
  // Store
  app.use('/api/settings', require('./api/systemSettings'));
  // Store
  app.use('/api/stores', require('./api/stores'));
  // Receipt
  app.use('/api/receipt', require('./api/receipt'));
  // Admin
  app.use('/api/admin', require('./api/admin'));
  // LQ
  app.use('/api/lq', require('./api/lq'));
  // Batches
  app.use('/api/batches', require('./api/batch'));
  // Download retailers csv
  app.use('/api/retailerCsv', express.static(path.resolve(__dirname, '../', '../', 'retailerCsv'), {
    setHeaders: res => {
      const url = res.req.url;
      res.setHeader('Access-Control-Allow-Headers', 'accept, authorization, content-type, x-requested-with, Range');
      res.setHeader('Access-Control-Allow-Methods', 'GET,HEAD,PUT,PATCH,POST,DELETE');
      res.setHeader('Access-Control-Allow-Origin', '*');
      res.setHeader('Content-Type', 'text/csv');
      res.setHeader('Content-Disposition', 'attachment; filename=' + url.substring(1, url.length - 1));
    }
  }));
  // Download sales
  app.use('/api/salesCsv', express.static(path.resolve(__dirname, '../', '../', 'salesCsv'), {
    setHeaders: res => {
      const url = res.req.url;
      res.setHeader('Access-Control-Allow-Headers', 'accept, authorization, content-type, x-requested-with, Range');
      res.setHeader('Access-Control-Allow-Methods', 'GET,HEAD,PUT,PATCH,POST,DELETE');
      res.setHeader('Access-Control-Allow-Origin', '*');
      res.setHeader('Content-Type', 'text/csv');
      res.setHeader('Content-Disposition', 'attachment; filename=' + url.substring(1, url.length - 1));
    }
  }));
  // Download bi info
  app.use('/api/biInfoCsv', express.static(path.resolve(__dirname, '../', '../', 'biInfoCsv'), {
    setHeaders: res => {
      const url = res.req.url;
      res.setHeader('Access-Control-Allow-Headers', 'accept, authorization, content-type, x-requested-with, Range');
      res.setHeader('Access-Control-Allow-Methods', 'GET,HEAD,OPTIONS');
      res.setHeader('Access-Control-Allow-Origin', '*');
      res.setHeader('Content-Type', 'text/csv');
      res.setHeader('Content-Disposition', 'attachment; filename=' + url.substring(1, url.length - 1));
    }
  }));
  // BI
  app.use('/api/bi', require('./api/bi'));

  app.use('/api/test', require('./api/test'));
};
