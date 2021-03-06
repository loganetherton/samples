'use strict';
var gulp = require('gulp');

/**
 * Import environment variables
 */
gulp.task('environment', function () {
  process.env.secret = require('../env.json')['session-secret'];
});

gulp.task('environment-socket', function () {
  process.env.secret = require('../env.json')['session-secret'];
  process.env.SOCKET_SERVER = 'true';
});

gulp.task('environment:prod', function () {
  process.env.secret = require('../env.json')['session-secret'];
  process.env.NODE_ENV = 'production';
});
