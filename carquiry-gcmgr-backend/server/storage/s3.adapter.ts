import StorageAdapter from './adapter.interface';
import {errorLoggable} from '../loggers/decorator';
import Logger from '../loggers/logger.interface';
import S3 = require('aws-sdk/clients/s3');
import fs = require('fs');

@errorLoggable
export default class S3Adapter implements StorageAdapter {
  /**
   * S3 client instance
   *
   * @var {S3}
   */
  client: S3;

  /**
   * S3 configuration
   *
   * @var {S3.ClientConfiguration}
   */
  config: S3.ClientConfiguration;

  /**
   * Error logger
   *
   * @var Logger
   */
  logger: Logger;

  /**
   * @param {S3.ClientConfiguration} config S3 configuration
   */
  constructor(config: S3.ClientConfiguration) {
    this.client = new S3({...config, apiVersion: '2006-03-01'});
    this.config = Object.assign({}, config);
  }

  async write(file: string, path: string): Promise<any> {
    const stream = fs.createReadStream(file);

    return await new Promise(resolve => {
      try {
        const upload = this.client.upload({
          Body: stream,
          Key: path,
          // This attribute is redundant for all requests but needs to be specified
          // to suppress linter warning, because they CBA to figure out a more appropriate interface
          Bucket: this.config.params.Bucket,
        }, (err: any, data: any) => {
          if (err) {
            this.logger.log(err);
            resolve(false);
          } else {
            resolve(true);
          }
        });
      } catch (e) {
        this.logger.log(e);
        resolve(false);
      }
    });
  }

  async getDownloadUrl(path: string): Promise<string | null> {
    if (await this.exists(path)) {
      // Some authentication methods require asynchronous callback when signing URL,
      // so let's put an await just for the sake of compatibility
      return await this.client.getSignedUrl('getObject', {
        Bucket: this.config.params.Bucket,
        Key: path
      });
    }

    return null;
  }

  async exists(path: string): Promise<boolean> {
    try {
      await this.client.headObject({
        Bucket: this.config.params.Bucket,
        Key: path
      }).promise();

      return true;
    } catch (e) {
      if (e.code !== 'NotFound') {
        // Something went wrong
        this.logger.log(e);
      }

      return false;
    }
  }
}
