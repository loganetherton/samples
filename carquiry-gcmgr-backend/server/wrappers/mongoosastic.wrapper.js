/**
 * Doesn't do anything special except wrapping certain functions from Mongoosastic with a Promise
 *
 * @param {Object} schema Mongoose schema
 */
export default function MongoosasticWrapper(schema) {
  const functionsToWrap = ['synchronize', 'createMapping', 'search', 'count'];

  functionsToWrap.forEach(functionName => {
    if (schema.statics[functionName]) {
      const originalFunc = schema.statics[functionName];

      schema.statics[functionName] = async function (...args) {
        return await new Promise((resolve, reject) => {
          originalFunc.call(this, ...args, function (err, res) {
            if (err) {
              reject(err);
            } else {
              resolve(res);
            }
          });
        });
      };
    }
  });
}
