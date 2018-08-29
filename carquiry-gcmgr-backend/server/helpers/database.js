/**
 * Determine if mongoose document
 * @param val
 * @return {boolean}
 */
export function isMongooseDocument(val) {
  return val.constructor.name === 'model';
}

/**
 * Determine if mongoose object ID (as opposed to document or string)
 * @param val
 * @return {boolean}
 */
export function isMongooseObjectId(val) {
  return val.constructor.name === 'ObjectID';
}


/**
 * Keep a reference to the original value. This is meant to be used with a custom
 * Mongoose setter.
 *
 * @param {String} attr
 * @return {Function}
 */
export function keepOriginalSetter(attr) {
  return function (newVal) {
    this._originals = this._originals || {};
    this._originals[attr] = this[attr];
    return newVal;
  }
}
