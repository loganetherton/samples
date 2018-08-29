import superagent from 'superagent';
import config from '../config';

const methods = ['get', 'post', 'put', 'patch', 'delete'];
const defaultOptions = {
  overrideToken: true,
};

// set the dev path and export it for use in other places
const devHost = (__DEVELOPMENT__) ? 'https://stage.valuepad.com' : '';
export const devPath = `${devHost}/api`;
// Length of URL to remove from paths routed to dev server
const devStringLength = 6;
// Dev path prefix
const devPathPrefix = `${devPath}/v2.0/`;

/**
 * Create URL, either for mock backend or dev backend
 * @param path
 * @returns {string}
 */
function formatUrl(path) {
  const adjustedPath = path[0] !== '/' ? '/' + path : path;
  if (__SERVER__) {
    // Prepend host and port of the API server to the path.
    return 'https://' + config.apiHost + ':' + config.apiPort + adjustedPath;
  }
  // Pass to dev server
  if (adjustedPath.indexOf('dev:') === 1) {
    return devPathPrefix + adjustedPath.substring(devStringLength);
  }

  if (adjustedPath.indexOf('batch:') === 1) {
    return devPath + adjustedPath.substring(7);
  }

  // Don't modify a complete URL
  if (adjustedPath.indexOf('https://')) {
    return adjustedPath.substring(1);
  }
  // Prepend `/api` to relative URL, to proxy to API server.
  return '/api/v2.0' + adjustedPath;
}

/*
 * This silly underscore is here to avoid a mysterious "ReferenceError: ApiClient is not defined" error.
 * See Issue #14. https://github.com/erikras/react-redux-universal-hot-example/issues/14
 *
 * Remove it at your own risk.
 */
class _ApiClient {
  constructor(req) {
    methods.forEach((method) =>
      this[method] = (path, { params, data, options = defaultOptions } = {}) => new Promise((resolve, reject) => {
        const request = superagent[method](formatUrl(path));

        if (params) {
          request.query(params);
        }

        if (__SERVER__ && req.get('cookie')) {
          request.set('cookie', req.get('cookie'));
        }
        /**
         * Set token
         */
        // Prefer passed in token
        if (_.isPlainObject(data) && data.token && options.overrideToken) {
          request.set('token', data.token);
          // Look in localStorage
        } else if (localStorage.token) {
          request.set('token', localStorage.token);

        }
        // Other headers
        if (_.isPlainObject(data) && data.headers) {
          _.forEach(data.headers, (header, key) => {
            request.set(key, header);
          });
        }

        if (data) {
          // Look for an object name file. If found, attach it
          if (data.file && typeof data.file === 'object') {
            // Append uploaded file using FormData
            const formData = new FormData();
            formData.append(data.file.name, data.file.file);
            request.send(formData);
          } else {
            request.send(data);
          }
        }

        request.end((err, { body } = {}) => err ? reject(body || err) : resolve(body));
      }));
  }
}

const ApiClient = _ApiClient;

export default ApiClient;
