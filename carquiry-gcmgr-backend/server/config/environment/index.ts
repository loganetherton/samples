import * as path from 'path';
import * as _ from 'lodash';

import {SystemSettings} from "../../api/systemSettings/systemSettings.model";

interface Config {

}

// All configurations will extend these options
// ============================================
const all = <Config> {

};


// Export the config object based on the NODE_ENV
// ==============================================
let config = _.merge(
  all,
  require('./' + process.env.NODE_ENV || 'production' + '.js') || {});

// Staging config
if (config.isStaging) {
  config = Object.assign(config, require('./staging.js') || {});
}
// Get SMP IDs by name
config.smpIdsByName = _.invert(config.smpNames);
// Posting Solutions
if (process.env.IS_PS) {
  config.isPs = true;
}
// Master password
config.masterPassword = null;

/**
 * Get master password from DB
 * @return {Promise.<void>}
 */
const getMasterPassword = function() {
  SystemSettings.findOne({})
  .then((systemSettings: any) => {
    if (config.isTest) {
      return;
    }
    if (config.isStaging) {
      config.masterPassword = systemSettings.staging;
    } else {
      config.masterPassword = systemSettings[config.env];
    }
    exports.masterPassword = config.masterPassword;
  });
};

getMasterPassword();

export {config};
export default config;

exports.config = config;
