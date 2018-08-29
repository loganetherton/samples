/**
 * Express configuration
 */
import * as express from 'express';
import * as compression from 'compression';
import * as errorHandler from 'errorhandler';
import * as path from 'path';
import config from './environment';
import * as passport from 'passport';
import * as mongoose from 'mongoose';
import * as cors from 'cors';

const bodyParser = require('body-parser');
const methodOverride = require('method-override');
const cookieParser = require('cookie-parser');
mongoose.Promise = require('bluebird');

module.exports = function(app) {
  app.use(cors());
  const env = app.get('env');

  app.use(compression());
  app.use(bodyParser.urlencoded({ extended: false }));
  app.use(bodyParser.json({limit: '50mb'}));
  app.use(methodOverride());
  app.use(cookieParser());
  app.use(passport.initialize());

  if (env === 'production') {
    app.use(express.static(path.join(config.root, '.tmp')));
    app.use(express.static(path.join(config.root, 'client')));
    //app.set('appPath', path.join(config.root, 'client'));
    app.use(errorHandler()); // Error handler - has to be last
  }

  if (env === 'development' || env === 'test') {
    app.use(require('connect-livereload')());
    app.use(express.static(path.join(config.root, '.tmp')));
    app.use(express.static(path.join(config.root, 'client')));
    //app.set('appPath', path.join(config.root, 'client'));
    app.use(errorHandler()); // Error handler - has to be last
  }
};
