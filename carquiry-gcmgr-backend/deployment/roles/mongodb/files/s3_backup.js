#!/usr/bin/node
const S3 = require('aws-sdk/clients/s3');
const fs = require('fs');
const moment = require('moment');

const s3client = new S3({
  accessKeyId: '**',
  secretAccessKey: '**',
  region: 'us-east-1',
  params: {
    Bucket: 'gcmgr-prod'
  },
  apiVersion: '2006-03-01'
});

const currentDate = moment().format('YMMDD');
const targetDir = `./backups/${currentDate}`;

(function () {
  fs.readdir(targetDir, async function (err, files) {
    if (!err) {
      for (const file of files) {
        const stream = fs.createReadStream(`${targetDir}/${file}`);
        await s3client.putObject({
          Body: stream,
          // Use substring to get rid of `./` which would cause a
          // recursion in S3
          Key: `${targetDir.substring(2)}/${file}`
        }).promise();
      }
    } else {
      console.log(err);
    }
  });
})();
