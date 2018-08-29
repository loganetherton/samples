import config from '../../config/environment';
import mailer from './mailer';
import {ErrorLogger} from '../../loggers';

mailer.setApiKey(config.sgToken);
mailer.setLogger(new ErrorLogger());

export default mailer;
