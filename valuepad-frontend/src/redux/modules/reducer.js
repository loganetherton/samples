import { combineReducers } from 'redux';
import { routerStateReducer } from 'redux-router';
import {responsiveStateReducer} from 'redux-responsive';

import accounting from './accounting';
import amc from './amc';
import appraiser from './appraiser';
import auth from './auth';
import company from './company';
import coverage from './coverage';
import customer from './customer';
import features from './features';
import invitations from './invitations';
import invoices from './invoices';
import jobType from './jobType';
import messages from './messages';
import notifications from './notifications';
import orders from './orders';
import settings from './settings';
import w9 from './w9';

export default combineReducers({
  router: routerStateReducer,
  browser: responsiveStateReducer,
  accounting,
  amc,
  appraiser,
  auth,
  company,
  coverage,
  customer,
  features,
  invitations,
  invoices,
  jobType,
  messages,
  notifications,
  orders,
  settings,
  w9
});
