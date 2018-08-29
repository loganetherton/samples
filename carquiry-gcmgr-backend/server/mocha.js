require('source-map-support').install();

import * as Mocha from 'mocha';
import * as fs from 'fs';

require('babel-polyfill');
require('babel-core/register');
const mocha = new Mocha();
require('./app');

// Filter tests based on string
const testFilter = process.env.TEST;

const walkSync = function(dir, filelist) {
  const theseFiles = fs.readdirSync(dir);
  filelist = filelist || [];
  theseFiles.forEach(function(file) {
    // Iterate sub-directories
    if (fs.statSync(dir + '/' + file).isDirectory()) {
      filelist = walkSync(dir + '/' + file, filelist);
    }
    else {
      // Add tests
      if (/\.test\.js$/.test(file)) {
        if (typeof testFilter === 'string') {
          const filter = new RegExp(testFilter, 'i');
          if (filter.test(file)) {
            filelist.push(dir + '/' + file);
          }
        } else {
          filelist.push(dir + '/' + file);
        }
      }
    }
  });
  return filelist;
};

// Compile list of tests
const fileList = walkSync(__dirname);

// Add tests to mocha
fileList.forEach(function(file){
  mocha.addFile(
    file
  );
});

// Run the tests.
const runner = mocha.run(function(failures){
  process.on('exit', function () {
    process.exit(failures);  // exit with non-zero status if there were failures
  });
});

runner.on('end', function () {
  process.exit(1);
});

