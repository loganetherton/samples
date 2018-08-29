import {expect} from 'chai';
import S3Adapter from './s3.adapter';
import config from '../config/environment';
import fs = require('fs');
let adapter: S3Adapter = new S3Adapter(config.storage.s3);
let testFileName = 'test-' + (+ new Date);
let testFilePath = __dirname + '/' + testFileName;

describe('S3 Adapter', function () {
  before(function (done) {
    fs.writeFile(testFilePath, 'Testing S3 Adapter', done);
  });

  after(function (done) {
    fs.unlink(testFilePath, done);
  });

  it('should be able to upload a file', async function () {
    const result = await adapter.write(testFilePath, testFileName);
    expect(result).to.be.true;
  });

  it('shouldn\'t be able to upload an inexistent file', async function () {
    const result = await adapter.write(testFilePath + (+ new Date), testFileName);
    expect(result).to.be.false;
  });

  it('should be able to locate an uploaded file', async function () {
    const result = await adapter.exists(testFileName);
    expect(result).to.be.true;
  });

  it('shouldn\'t be able to locate an inexistent file', async function () {
    const result = await adapter.exists(testFileName + (+new Date));
    expect(result).to.be.false;
  });

  it('should be able to generate a download URL for an uploaded file', async function () {
    const result = await adapter.getDownloadUrl(testFileName);
    expect(result).to.be.a('string');
  });

  it('shouldn\'t be able to generate a download URL for an inexistent file', async function () {
    const result = await adapter.getDownloadUrl(testFileName + (+new Date));
    expect(result).to.be.null;
  });
});
