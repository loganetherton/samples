export default interface StorageAdapter {
  /**
   * Writes the source file to the specified target path
   *
   * @param {string} file Source file to write to remote location
   * @param {string} path Target file path
   * @return {Promise<any>}
   */
  write(file: string, path: string): Promise<any>;

  /**
   * Returns a download URL for the given path
   * Should be asynchronous just in case it requires connecting to a third-party service
   * Should return null if file doesn't exist
   *
   * @param {string} path File to download
   * @return {Promise<string | null>}
   */
  getDownloadUrl(path: string): Promise<string | null>;

  /**
   * Checks if the specified file exists
   *
   * @param {string} path
   * @return {Promise<boolean>}
   */
  exists(path: string): Promise<boolean>;
}
