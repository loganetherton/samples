'use strict';

var path = require('path');
var gulp = require('gulp');
var conf = require('./conf');

var browserSync = require('browser-sync');
var browserSyncSpa = require('browser-sync-spa');

var util = require('util');

browserSync.use(browserSyncSpa({
  selector: '[ng-app]'// Only needed for angular apps
}));

gulp.task('serve', ['environment', 'express:dev', 'express:watch'], function () {});

// Recompile server, not frontend
gulp.task('server', ['environment', 'express:dev', 'express:watch'], function () {});

gulp.task('server-socket', ['environment-socket', 'express:dev-socket', 'express:watch'], function () {});

gulp.task('serve:dist', ['build', 'express:prod'], function () {});

gulp.task('serve:e2e', ['inject'], function () {});

gulp.task('serve:e2e-dist', ['build'], function () {});
