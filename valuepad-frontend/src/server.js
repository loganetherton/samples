import Express from 'express';
import React from 'react';
import ReactDOM from 'react-dom/server';
import config from './config';
import favicon from 'serve-favicon';
import compression from 'compression';
import path from 'path';
import createStore from './redux/create';
import ApiClient from './helpers/ApiClient';
import Html from './helpers/Html';
import PrettyError from 'pretty-error';
import http from 'http';
import https from 'https';
import SocketIo from 'socket.io';

import {ReduxRouter} from 'redux-router';
import createHistory from 'history/lib/createMemoryHistory';
import {reduxReactRouter, match} from 'redux-router/lib/server';
import {Provider} from 'react-redux';
import qs from 'query-string';
import getRoutes from './routes';
import getStatusFromRoutes from './helpers/getStatusFromRoutes';
import fs from 'fs';

const pretty = new PrettyError();
const app = new Express();
const scheme = process.env.SSL === 'true' ? 'https': 'http';

if (process.env.SSL === 'true') {
  var server = https.createServer({
    key: fs.readFileSync(path.join(__dirname, '..', 'localhost.key')),
    cert: fs.readFileSync(path.join(__dirname, '..', 'localhost.crt'))
  }, app);
} else {
  var server = new http.Server(app);
}

app.use(compression());
app.use(favicon(path.join(__dirname, '..', 'static', 'favicon.ico')));

app.use(Express.static(path.join(__dirname, '..', 'static')));

// Set userAgent so that SSR works properly
// @link https://github.com/callemall/material-ui/pull/2172#issuecomment-157404901
app.use(function(req, res, next) {
  GLOBAL.navigator = {
    userAgent: req.headers['user-agent']
  };
  next();
});

app.use((req, res) => {
  if (__DEVELOPMENT__) {
    // Do not cache webpack stats: the script file would change since
    // hot module replacement is enabled in the development env
    webpackIsomorphicTools.refresh();
  }
  const client = new ApiClient(req);

  const store = createStore(reduxReactRouter, getRoutes, createHistory, client);

  function hydrateOnClient() {
    const isProduction = req.get('host').indexOf('app.valuepad.com') !== -1;
    const isStaging = req.get('host').indexOf('stage.valuepad.com') !== -1;
    const isDevelopment = req.get('host').indexOf('localhost') !== -1;
    res.send('<!doctype html>\n' +
      ReactDOM.renderToString(<Html assets={webpackIsomorphicTools.assets()} store={store} development={isDevelopment} staging={isStaging} production={isProduction}/>));
  }

  if (__DISABLE_SSR__) {
    hydrateOnClient();
    return;
  }

  store.dispatch(match(req.originalUrl, (error, redirectLocation, routerState) => {
    if (redirectLocation) {
      res.redirect(redirectLocation.pathname + redirectLocation.search);
    } else if (error) {
      console.error('ROUTER ERROR:', pretty.render(error));
      res.status(500);
      hydrateOnClient();
    } else if (!routerState) {
      res.status(500);
      hydrateOnClient();
    } else {
      // Workaround redux-router query string issue:
      // https://github.com/rackt/redux-router/issues/106
      if (routerState.location.search && !routerState.location.query) {
        routerState.location.query = qs.parse(routerState.location.search);
      }

      store.getState().router.then(() => {
        const component = (
          <Provider store={store} key="provider">
            <ReduxRouter/>
          </Provider>
        );

        const status = getStatusFromRoutes(routerState.routes);
        if (status) {
          res.status(status);
        }
        res.send('<!doctype html>\n' +
          ReactDOM.renderToString(<Html assets={webpackIsomorphicTools.assets()} component={component} store={store}/>));
      }).catch((err) => {
        console.error('DATA FETCHING ERROR:', pretty.render(err));
        res.status(500);
        hydrateOnClient();
      });
    }
  }));
});

if (config.port) {
  if (config.isProduction) {
    const io = new SocketIo(server);
    io.path('/api/ws');
  }

  server.listen(config.port, (err) => {
    if (err) {
      console.error(err);
    }
    console.info(`==> 💻  Open ${scheme}://%s:%s in a browser to view the app.`, config.host, config.port);
  });
} else {
  console.error('==>     ERROR: No PORT environment variable has been specified');
}
