import * as superagent from 'superagent';

import VistaService from './vista.service';
import SuperAgentWrapper from '../../wrappers/superagent.wrapper.js';
import config from '../../config/environment';
import {CompositeLogger, ErrorLogger, VistaApiLogger} from '../../loggers';

const service = new VistaService(new SuperAgentWrapper(superagent), config.vista);
service.setLogger(new CompositeLogger([new VistaApiLogger(), new ErrorLogger()]));
export default service;
