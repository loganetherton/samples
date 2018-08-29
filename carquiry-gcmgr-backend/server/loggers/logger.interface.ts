export default interface Logger {
  /**
   * @param {mixed} object
   * @return {this}
   */
  log(object: any): this;
}
