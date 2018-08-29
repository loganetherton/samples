import {ROUTER_DID_CHANGE} from 'redux-router/lib/constants';
import getDataDependencies from '../../helpers/getDataDependencies';
// Google Analytics
import ReactGa from 'react-ga';
// Initialize client side
if (typeof window !== 'undefined') {
  ReactGa.initialize('UA-39862822-2');
}

const locationsAreEqual = (locA, locB) => (locA.pathname === locB.pathname) && (locA.search === locB.search);

export default ({getState, dispatch}) => next => action => {
  if (action.type === ROUTER_DID_CHANGE) {
    if (getState().router && locationsAreEqual(action.payload.location, getState().router.location)) {
      return next(action);
    }
    // Prevent server-side calls
    if (typeof window !== 'undefined') {
      let path, userId;
      userId = getState().auth.getIn(['user', 'id']);
      if (action && action.payload && action.payload.location && action.payload.location.pathname) {
        path = action.payload.location.pathname;
      }
      if (userId) {
        ReactGa.set({userId: userId});
      }
      if (path) {
        ReactGa.set({page: path});
        ReactGa.pageview(path);
      }
    }

    const {components, location, params} = action.payload;
    const promise = new Promise((resolve) => {
      const doTransition = () => {
        next(action);
        Promise.all(getDataDependencies(components, getState, dispatch, location, params, true))
          .then(resolve)
          .catch(error => {
            // TODO: You may want to handle errors for fetchDataDeferred here
            console.warn('Warning: Error in fetchDataDeferred', error);
            return resolve();
          });
      };

      Promise.all(getDataDependencies(components, getState, dispatch, location, params))
        .then(doTransition)
        .catch(error => {
          // TODO: You may want to handle errors for fetchData here
          console.warn('Warning: Error in fetchData', error);
          return doTransition();
        });
    });

    if (__SERVER__) {
      // router state is null until ReduxRouter is created so we can use this to store
      // our promise to let the server know when it can render
      getState().router = promise;
    }

    return promise;
  }

  return next(action);
};
