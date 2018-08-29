import * as superagent from 'superagent';
import BiService from './bi.service';
import SuperAgentWrapper from '../../wrappers/superagent.wrapper.js';
import config from '../../config/environment';
import {CompositeLogger, ErrorLogger, BiLogger} from '../../loggers';

const service = new BiService(new SuperAgentWrapper(superagent), config.bi);
service.setLogger(new CompositeLogger([new BiLogger(), new ErrorLogger()]));
export default service;
