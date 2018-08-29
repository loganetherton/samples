import * as _ from 'lodash';
import config from '../config/environment';
const {smpNames, disabledSmps} = config;

export function getActiveSmps() {
  const enabledIds = _.difference(_.keys(smpNames), _.keys(disabledSmps));
  return _.values(_.pick(smpNames, enabledIds));
}
