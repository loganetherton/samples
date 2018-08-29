import * as mongoose from "mongoose";
import {Types} from "mongoose";
import {IInventory, IInventoryTransaction} from "../inventory/inventory.model";
import {IUser} from "../user/user.model";
import {IBiRequestLog} from "../biRequestLog/biRequestLog.model";
import {ICompany} from "../company/company.model";
import {ICustomer} from "../customer/customer.model";
import {IStore} from "../stores/store.model";
import {IRetailer} from "../retailer/retailer.model";
import {IBiSearchParams} from "../../helpers/interfaces";

/**
 * Constraints for querying for a customer
 */
export interface ICustomerConstraint {
  store: string,
  company: string,
  _id?: string,
  email?: string
}

/**
 * Incoming req.body for lq/transaction
 */
export interface INewTransactionBody {
  number: string,
  pin: string,
  balance: number,
  retailer: string,
  memo?: string,
  transactionTotal: number,
  transactionId: string,
  merchandise: boolean,
  customerId: string,
  storeId: string,
  vmMemo1: string,
  vmMemo2: string,
  vmMemo3: string,
  vmMemo4: string,
  callbackUrl: string,
  prefix?: string,
  userTime: Date,
  lqCustomerName: string
}

/**
 * Response from Vista BI system
 */
export interface IVistaBalanceResponse {
  msg: string,
  balance: number
}

/**
 * Response from checking the Vista BI system
 */
export interface IVistaBiResponse {
  finalized: boolean,
  valid?: boolean,
  verifiedBalance?: number
}

/**
 * Params for lq new card
 */
export interface ILqNewCardBodyParams {
  number: string,
  pin: string,
  retailer: string,
  userTime: Date,
  balance: number,
  merchandise?: boolean,
  callbackUrl?: string,
  customer?: string,
  store?: string,
  autoSell?: boolean,
  user: IUser,
  lqCustomerName?: string
}

/**
 * Params for test BI responses
 */
export interface IFakeBiParams {
  number: string,
  pin: string,
  retailerId: string,
  balance: number|string,
  user?: IUser
}

/**
 * Stack of callbacks to be made at the end of the execution path
 */
export interface ICallbackStack {
  [key: string]: any[],
}

/**
 * Decorated card for respopnse
 */
export interface ICardDecoratedForResponse {
  saleAccepted: boolean,
  saleVerified: boolean,
  saleFinal: boolean,
  claimedBalanceInaccurate: boolean,
  transaction: ITransaction,
  balance: number
}

export interface IDecoratedCard {
  balance: number,
  saleAccepted?: boolean,
  saleVerified?: boolean,
  saleFinal?: boolean,
  claimedBalanceInaccurate?: boolean,
  transaction?: IInventoryTransaction,
}

/**
 * Response object for creating new cards from LQ
 */
export interface ICardResponse extends IDecoratedCard {
  _id?: mongoose.Types.ObjectId,
  __v?: string,
  created?: string,
  number?: string,
  pin?: string,
  retailer?: string,
  sellRate?: number,
  soldFor?: number,
  customer?: string,
  balanceStatus?: string,
  buyRate?: number,
  user?: mongoose.Types.ObjectId,
  updates?: mongoose.Types.ObjectId[],
  valid?: boolean,
  inventory?: IInventory
}

/**
 * Test card respone
 */
export interface ITestCard {
  "card": {
    "sellRate": number,
    "_id": string,
    "number": string,
    "retailer": string,
    "userTime": string,
    "balance": number,
    "pin": string,
    "buyAmount": number,
    "soldFor": number,
    "statusCode": number,
    "status": string,
    "saleAccepted": boolean,
    "saleVerified": boolean,
    "saleFinal": boolean,
    "claimedBalanceInaccurate": boolean
  }
}

/**
 * LQ error response
 */
export interface IErrorResponse {
  code: number,
  message: any
}

/**
 * Transaction values
 */
export interface ITransaction {
  memo: string,
  nccCardValue: number,
  transactionTotal: number,
  transactionId: string,
  merchantPayoutAmount: number,
  merchantPayoutPercentage: number,
  amountDue: number,
  prefix: string,
  vmMemo1: string,
  vmMemo2: string,
  vmMemo3: string,
  vmMemo4: string,
  creditValuePercentage: number,
  maxSpending: number
}

/**
 * Fake BI Response for testing
 */
export interface ILqInternalBiResponse {
  responseDateTime: string,
  responseCode: string,
  request_id?: string,
  balance: number,
  responseMessage: string,
  response_datetime?: string,
  requestId?: string,
  recheckDateTime?: string
}

/**
 * BI response from /lq/transaction, which could include interactions with Vista's BI service
 */
export interface ITransactionBiResponse {
  finalized?: boolean,
  valid?: boolean,
  verifiedBalance?: number,
  balanceSubtracted: boolean
}

/**
 * Params for finding a BiRequestLog
 */
export interface IBiRequestLogFindParams {
  number: string,
  pin?: string,
  balance?: number,
  requestId?: string,
  retailerId: string|Types.ObjectId,
  user?: string|Types.ObjectId|null,
  callbackUrl?: string,
  autoSell?: boolean,
  store?: string,
  card?: Types.ObjectId|string,
  responseDateTime?: string,
  responseCode?: string,
  responseMessage?: string,
  customer?: string,
  lqCustomerName?: string
}

/**
 * Complete card from BI completion
 */
export interface ICompleteCardFromBiParams {
  log: IBiRequestLog,
  balance: number,
  callbackStackId: string,
  user: IUser,
  useCallbackStack?: boolean
}

/**
 * Params for handling BI complete
 */
export interface IHandleBiCompleteParams {
  requestId: string,
  retailerId: string,
  number: string,
  pin: string,
  balance: number,
  callbackStackId: string,
  // For test requests
  lqCustomerName?: string,
  // If we're getting the user ID from BI
  userId?: string
}

export interface ICardStatusQuery {
  user: IUser,
  userTime?: ICardDateQueryParams
}

/**
 * Date query params
 */
export interface ICardDateQueryParams {
  $lt: string,
  $gt: string
}

/**
 * Card for decoration before returningto the user
 */
export interface ICardForDecoration {
  balance: number,
}

/**
 * Settings formatted for display
 */
export interface IFormattedSettings {
  cardType: string,
  autoSell: boolean,
  biOnly: boolean,
  minimumAdjustedDenialAmount: number,
  customerDataRequired: boolean,
  callbackUrl: string,
  enableCallbackStatus: boolean,
  validateCard: boolean,
  disableLimits: boolean,
  enableCurrencyCode: boolean,
  globalLimits: boolean|{max: number, min: number},
  callbackToken?: string,
  enableChargebackCallback?: boolean,
  lqCustomerNameEnabled?: boolean
}

/**
 * Response from create user
 */
export interface ICreateUserResponse {
  token: string,
  user: IUser,
  company: ICompany,
  store: IStore,
  customer: ICustomer
}

/**
 * Sub-user creation body
 */
export interface ISubUserBody {
  email: string,
  company: string,
  store: string,
  role?: string
}

/**
 * Retailer formatted for return to user
 */
export interface IFormattedRetailer extends IRetailer {
  cardType: string,
  biEnabled: boolean,
}

/**
 * Params for performing BI as part of a /transaction
 */
export interface ITransactionBiParams {
  number: string,
  pin?: string,
  balance: number,
  biSearchValues: IBiSearchParams,
  retailer: string,
  callbackUrl: string,
  prefix: string,
  user: IUser
}

/**
 * Parsed BI log from transactions
 */
export interface IParsedBiLog {
  verifiedBalance: number,
  valid: boolean,
  finalized: boolean
}

/**
 * Params for initiating BI completed
 */
export interface IBiCompletedParams {
  retailerId: string,
  number: string,
  pin: string,
  balance: number,
  lqCustomerName: string,
  // User ID stored in BI
  userId: string
}

/**
 * Response from calculating transactions
 */
export interface ICalculateTransactionResponse {
  amountDue: number,
  cardValue: number,
  merchantPayoutAmount: number
}

/**
 * Request body for /bi
 */
export interface IBiRequestBody {
  number: string,
  pin: string,
  retailer: string,
  retailerId?: string,
  prefix?: string,
  callbackUrl?: string,
  autoSell?: boolean,
  store?: string,
  customer?: string,
  lqCustomerName?: string,
  userEmail?: string,
  requestId?: string
}

/**
 * Params for doing the actual insertion of cards into BI
 */
export interface IInsertBiParams extends IBiRequestBody {
  user?: IUser
}

/**
 * Params for test BI
 */
export interface IHandleTestBiParams {
  isTestCard: boolean,
  retailer: string,
  number: string,
  requestId: string,
  user: IUser,
  pin: string
}

/**
 * Response from test BI
 */
export interface IHandleTestBiResponse {
  isTestCard: boolean,
  noAdditionalCallbackTests: boolean,
  callbackStackId: string
}

/**
 * Params for createBiLogAsPartOfCompletion()
 */
export interface ICreateBiLogParams {
  number: string,
  pin: string,
  retailerId: string,
  balance: number,
  userId?: string
}
