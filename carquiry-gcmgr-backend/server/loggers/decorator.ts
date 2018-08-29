import {ErrorLogger} from './errorLogger';

export function errorLoggable<T extends {new(...args:any[]):{}}>(constructor: T) {
  const descriptors = Object.getOwnPropertyDescriptors(constructor.prototype);
  const logger = new ErrorLogger();
  constructor.prototype.logger = logger;

  for (const [key, value] of Object.entries(descriptors)) {
    if (value.value instanceof Function) {
      const original = value.value;

      if (original.constructor.name === 'AsyncFunction') {
        value.value = async function (...args: any[]) {
          try {
            return await original.apply(this, args);
          } catch (e) {
            logger.log(e);
            throw e;
          }
        }
      } else {
        value.value = function (...args: any[]) {
          try {
            return original.apply(this, args);
          } catch (e) {
            logger.log(e);
            throw e;
          }
        }
      }

      Object.defineProperty(constructor.prototype, key, value);
    }
  }
}
