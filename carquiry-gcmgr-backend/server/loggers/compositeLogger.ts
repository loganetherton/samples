import Logger from './logger.interface';

export class CompositeLogger implements Logger {
  /**
   * A list of loggers to invoke
   *
   * @var {Logger[]}
   */
  loggers: Logger[];

  /**
   * @param {Logger[]} loggers An array of logger classes with a "log" function implemented
   */
  constructor(loggers: Logger[] = []) {
    this.loggers = loggers;
  }

  /**
   * Adds another logger
   *
   * @param {Logger} logger
   * @return {this}
   */
  addLogger(logger: Logger): this {
    this.loggers.push(logger);
    return this;
  }

  /**
   * Iterates over all of the referenced logger and call their log functions.
   *
   * @param {mixed} things
   * @return {this}
   */
  log(things: any): this {
    this.loggers.forEach(logger => {
      logger.log(things);
    });

    return this;
  }
}
