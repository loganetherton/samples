import {IBiRequestLog} from "../biRequestLog/biRequestLog.model";

/**
 * Callback params for callbacks made from Cards
 */
export interface ICardCallbackParams {
  id: string,
  number: string,
  claimedBalance: number,
  verifiedBalance: number,
  cqPaid: number,
  netPayout: number,
  prefix?: string,
  cqAch: string,
  finalized: boolean,
  callbackType: string,
  netAmount?: number,
  serviceFee?: number,
  statusCode?: number,
  status?: string,
  currencyCode?: string,
  note?: string,
  token?: string,
  lqCustomerName?: string
}

/**
 * Callback params for callbacks made from BiRequestLogs
 */
export interface ILogCallbackParams {
  number: string,
  pin: string,
  verifiedBalance: number,
  callbackType: string,
  retailer?: string,
  status?: string,
  statusCode?: number,
  currencyCode?: string,
  prefix?: string,
  token?: string,
  body?: any,
  biRequestLog?: IBiRequestLog,
  lqCustomerName?: string
}
