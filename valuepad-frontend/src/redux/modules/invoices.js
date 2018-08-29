// Set a property explicitly
const SET_PROP = 'vp/invoices/SET_PROP';
// Get invoices
const GET_INVOICES = 'vp/invoices/GET_INVOICES';
const GET_INVOICES_SUCCESS = 'vp/invoices/GET_INVOICES_SUCCESS';
const GET_INVOICES_FAILURE = 'vp/invoices/GET_INVOICES_FAILURE';
// Payment settings
const GET_SETTINGS = 'vp/invoices/GET_SETTINGS';
// Pay invoice
const PAY_INVOICE = 'vp/invoices/PAY_INVOICE';
const PAY_INVOICE_SUCCESS = 'vp/invoices/PAY_INVOICE_SUCCESS';
const PAY_INVOICE_FAILURE = 'vp/invoices/PAY_INVOICE_FAILURE';
// Sort
const SORT_INVOICES = 'vp/invoices/SORT_INVOICES';

import Immutable from 'immutable';
import moment from 'moment';

import {setProp as setPropInherited} from 'helpers/genericFunctions';

export const emptySort = Immutable.fromJS({
  from: -1,
  paid: 0,
  amount: 0
});

const initialState = Immutable.fromJS({
  // List of invoices
  invoices: [],
  // Selected invoice
  selectedInvoice: {},
  // Sorts
  sorts: {
    from: -1,
    isPaid: 0,
    amount: 0
  },
  // Selected payment method
  paymentMethod: 'bank-account',
  // Show pagination
  meta: Immutable.Map()
});

export default function reducer(state = initialState, action = {}) {
  switch (action.type) {
    case SET_PROP:
      return state
        .setIn(action.name, action.value);
    /**
     * Get invoices
     */
    case GET_INVOICES:
      return state
        .set('gettingInvoices', true)
        .remove('getInvoicesSuccess');
    case GET_INVOICES_SUCCESS:
      const now = moment();
      let invoices = Immutable.fromJS(action.result.data);
      // Set overdue
      invoices = invoices.map(invoice => {
        return invoice
          .set('overdue', isOverdue(moment(invoice.get('to')), now, invoice.get('isPaid')));
      });
      return state
        .remove('gettingInvoices')
        .set('getInvoicesSuccess', true)
        .set('invoices', sortColumn(invoices, 'from', state.getIn(['sorts', 'date'])))
        .set('meta', Immutable.fromJS(action.result.meta));
    case GET_INVOICES_FAILURE:
      return state
        .remove('gettingInvoices')
        .set('getInvoicesSuccess', false);
    /**
     * Get settings
     */
    case GET_SETTINGS:
      return state
        .set('paymentMethod', 'cc');
    /**
     * Pay invoice
     */
    case PAY_INVOICE:
      return state
        .set('payingInvoice', true)
        .remove('payInvoiceSuccess');
    case PAY_INVOICE_SUCCESS:
      const afterPayInvoices = state.get('invoices').map(invoice => {
        if (invoice.get('id') === action.invoiceId) {
          return invoice.set('isPaid', true);
        }
        return invoice;
      });
      return state
        .remove('payingInvoice')
        .set('payInvoiceSuccess', true)
        .set('invoices', afterPayInvoices);
    case PAY_INVOICE_FAILURE:
      return state
        .remove('payingInvoice')
        .set('payInvoiceSuccess', false);
    /**
     * Sort invoices
     */
    case SORT_INVOICES:
      const sortProp = action.prop;
      const sortState = state.get('sorts');
      const originalSortVal = sortState.get(sortProp);
      let sortedInvoices;
      let sortFn;
      let newSortVal;
      switch (sortProp) {
        case 'date':
          sortFn = sortColumn.bind(this, state.get('invoices'), 'from');
          break;
        case 'paid':
          sortFn = sortColumn.bind(this, state.get('invoices'), 'isPaid');
          break;
        case 'amount':
          sortFn = sortColumn.bind(this, state.get('invoices'), 'amount');
          break;
        case 'overdue':
          sortFn = sortColumn.bind(this, state.get('invoices'), 'overdue');
          break;
      }
      if (originalSortVal === -1) {
        newSortVal = 1;
      } else {
        newSortVal = -1;
      }
      sortedInvoices = sortFn(newSortVal);
      return state
        .set('sorts', emptySort)
        .setIn(['sorts', sortProp], newSortVal)
        .set('invoices', sortedInvoices);
    default:
      return state;
  }
}

/**
 * Set a property explicitly
 * @param value Value to set it to
 * @param name Name arguments forming array for setIn
 */
export function setProp(value, ...name) {
  return setPropInherited(SET_PROP, value, ...name);
}

/**
 * Get invoices
 * @param amcId
 * @param page Current page
 * @param perPage Per page amount
 * @param sorts sort vals
 */
export function getInvoices(amcId, page = 1, perPage = 10, sorts = emptySort) {
  const sortProp = sorts.filter(sort => sort !== 0);
  let orderBy = '';
  sortProp.forEach((order, prop) => {
    if (order === 1) {
      orderBy = prop;
    } else {
      orderBy = `${prop}:desc`;
    }
  });
  return {
    types: [GET_INVOICES, GET_INVOICES_SUCCESS, GET_INVOICES_FAILURE],
    promise: client => client.get(`dev:/amcs/${amcId}/invoices?page=${page}&perPage=${perPage}&orderBy=${orderBy}`)
  };
}

/**
 * Sort based on prop
 * @param prop
 */
export function sortInvoices(prop) {
  return {
    type: SORT_INVOICES,
    prop
  };
}

/**
 * Pay invoice
 * @param amcId AMC ID
 * @param invoiceId Invoice ID
 * @param method Payment method
 */
export function payInvoice(amcId, invoiceId, method) {
  return {
    types: [PAY_INVOICE, PAY_INVOICE_SUCCESS, PAY_INVOICE_FAILURE],
    promise: client => client.post(`dev:/amcs/${amcId}/invoices/${invoiceId}/pay`, {
      data: {
        means: method
      }
    }),
    invoiceId
  };
}

/**
 * Sort by column prop
 * @param invoices List of invoices
 * @param sortProp Property to sort on
 * @param sortVal New sort direction
 */
function sortColumn(invoices, sortProp, sortVal) {
  return invoices.sort((a, b) => {
    if (sortProp === 'from') {
      a = moment(a.get(sortProp));
      b = moment(b.get(sortProp));
    } else {
      a = a.get(sortProp);
      b = b.get(sortProp);
    }
    if (a === b) {
      return 0;
    }
    return a > b ? sortVal : sortVal * -1;
  });
}

/**
 * Determine if invoice is overdue
 * @param invoiceDate Date of invoice (moment)
 * @param now Current date (moment)
 * @param isPaid Invoice is paid
 */
function isOverdue(invoiceDate, now, isPaid) {
  let overdue = '';
  const daysOverdue = now.diff(invoiceDate, 'days');
  const fifteenOverdue = Math.floor(daysOverdue / 15) * 15;
  if (fifteenOverdue && !isPaid) {
    overdue = `${fifteenOverdue} days overdue`;
  } else {
    overdue = '--';
  }
  return overdue;
}
