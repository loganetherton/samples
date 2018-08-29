import React, {Component, PropTypes} from 'react';
import ReactDOM from 'react-dom/server';
import serialize from 'serialize-javascript';
import Helmet from 'react-helmet';
import DocumentMeta from 'react-document-meta';
import Immutable from 'immutable';
import transit from 'transit-immutable-js';
import {Footer} from 'components';

/**
 * Wrapper component containing HTML metadata and boilerplate tags.
 * Used in server-side code only to wrap the string output of the
 * rendered route component.
 *
 * The only thing this component doesn't (and can't) include is the
 * HTML doctype declaration, which is added to the rendered output
 * by the server.js file.
 */
export default class Html extends Component {
  static propTypes = {
    assets: PropTypes.object,
    component: PropTypes.node,
    store: PropTypes.object,
    // Environment
    development: PropTypes.bool.isRequired,
    staging: PropTypes.bool.isRequired,
    production: PropTypes.bool.isRequired
  };

  render() {
    const {assets, component, store, development, staging, production} = this.props;
    const content = component ? ReactDOM.renderToString(component) : '';
    const head = Helmet.rewind();

    const htmlStyle = {
      position: 'relative',
      minHeight: '100%'
    };
    const bodyStyle = {
      backgroundColor: '#fff',
      /* Margin bottom by footer height */
      marginBottom: '60px'
    };

    return (
      <html lang="en-us" style={htmlStyle}>
        <head>
          {head.base.toComponent()}
          {head.title.toComponent()}
          {head.meta.toComponent()}
          {head.link.toComponent()}
          {head.script.toComponent()}

          {DocumentMeta.renderAsReact()}

          <link rel="shortcut icon" href="/favicon.ico" />
          <meta name="viewport" content="width=device-width, initial-scale=1" />
          {/* styles (will be present only in production with webpack extract text plugin) */}
          {Object.keys(assets.styles).map((style, key) =>
            <link href={assets.styles[style]} key={key} rel="stylesheet" type="text/css" charSet="UTF-8"/>
          )}

          {/* (will be present only in development mode) */}
          {/* outputs a <style/> tag with all bootstrap styles + App.scss + it could be CurrentPage.scss. */}
          {/* can smoothen the initial style flash (flicker) on page load in development mode. */}
          {/* ideally one could also include here the style for the current page (Home.scss, About.scss, etc) */}
          { Object.keys(assets.styles).length === 0 ? <style dangerouslySetInnerHTML={{__html: require('../theme/bootstrap.config.js') + require('../containers/App/App.scss')._style}}/> : null }

          {/* Material Design fonts */}
          <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700" type="text/css"/>
          <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet"/>
        </head>
        <body style={bodyStyle}>
          <div id="content" dangerouslySetInnerHTML={{__html: content}}/>
          <script dangerouslySetInnerHTML={{__html: `window.__data=${transit.toJSON(store.getState())};`}} charSet="UTF-8"/>
          <script dangerouslySetInnerHTML={{__html: `window.isDevelopment=${development}; window.isStaging=${staging}; window.isProduction=${production};`}}/>
          <script src={assets.javascript.main} charSet="UTF-8"/>
          <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCcwYdyfIPKPYXjmzZU2dSjeQ5BRTx233w"/>

          <Footer />
        </body>
      </html>
    );
  }
}
