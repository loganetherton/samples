import S3Adapter from './s3.adapter';
import config from '../config/environment';

export default new S3Adapter(config.storage.s3);
