var Express = require('express');
var webpack = require('webpack');
var http = require('http');
var https = require('https');
var fs = require('fs');
var path = require('path');
var scheme = process.env.SSL === 'true' ? 'https' : 'http';

var config = require('../src/config');
var webpackConfig = require('./dev.config');
var compiler = webpack(webpackConfig);

var host = config.host || 'localhost';
var port = (parseInt(config.port) + 1) || 3001;
var serverOptions = {
  contentBase: scheme + '://' + host + ':' + port,
  quiet: true,
  noInfo: true,
  hot: true,
  inline: true,
  lazy: false,
  publicPath: webpackConfig.output.publicPath,
  headers: {'Access-Control-Allow-Origin': '*'},
  stats: {colors: true},
  https: process.env.SSL === 'true',
};

var app = new Express();

if (process.env.SSL === 'true') {
  var server = https.createServer({
    key: fs.readFileSync(path.join(__dirname, '..', 'localhost.key')),
    cert: fs.readFileSync(path.join(__dirname, '..', 'localhost.crt'))
  }, app);
} else {
  var server = new http.Server(app);
}

app.use(require('webpack-dev-middleware')(compiler, serverOptions));
app.use(require('webpack-hot-middleware')(compiler));

server.listen(port, function onAppListening(err) {
  if (err) {
    console.error(err);
  } else {
    console.info('==> 🚧  Webpack development server listening on port %s', port);
  }
});
