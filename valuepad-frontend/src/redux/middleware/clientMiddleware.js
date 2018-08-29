import Immutable from 'immutable';

// setup holder for synchronous requests
let pending = Immutable.Map();

export default function clientMiddleware(client) {
  return ({dispatch, getState}) => {
    return next => action => {
      const nextState = getState();
      // Keep reference to last time an action was performed
      nextState.auth = nextState.auth.set('lastActionTime', Date.now());
      if (typeof action === 'function') {
        return action(dispatch, getState);
      }

      const { promise, types, ...rest } = action; // eslint-disable-line no-redeclare

      if (!Array.isArray(types) && promise) {
        return Promise.resolve(promise().then(() => {
          next(action);
        }));
      }

      if (!promise) {
        return next(action);
      }

      // get the request data
      const [REQUEST, SUCCESS, FAILURE] = types;

      // if there is a pending request of the same type
      // use that instead for a queue
      let resolvePromise = null;
      if (pending.get(REQUEST, false)) {
        resolvePromise = pending.get(REQUEST)
        .then(() => {
          // fire off the request to show we are making this request
          next({...rest, type: REQUEST});

          return Promise.resolve(
            promise(client)
              .then(result => {
                // clear this promise from the queue
                pending = pending.remove(REQUEST);
                // send back a success result
                return next({...rest, result, type: SUCCESS});
              })
              .catch(error => {
                // clear this promise from the queue
                pending = pending.remove(REQUEST);
                // send back an error result
                return next({...rest, error, type: FAILURE});
              })
          );
        });
      } else {
        // fire off the request to show we are making this request
        next({...rest, type: REQUEST});

        resolvePromise = promise(client)
          .then(result => {

            // clear this promise from the queue
            pending = pending.remove(REQUEST);
            // send back a success result
            return next({...rest, result, type: SUCCESS});
          })
          .catch(error => {
            // clear this promise from the queue
            pending = pending.remove(REQUEST);
            // send back an error result
            return next({...rest, error, type: FAILURE});
          });
      }

      // set this as request type as pending
      pending = pending.set(REQUEST, resolvePromise);

      return resolvePromise;
    };
  };
}
