import * as mongoose from "mongoose";
import {IUser} from "../user/user.model";
import {ICompany} from "../company/company.model";
import {IStore} from "../stores/store.model";
import {IInventoryTransaction} from "../inventory/inventory.model";
import {ICompanySettings} from "../company/companySettings.model";
import {ICard} from "./card.model";
import {ICustomer} from "../customer/customer.model";

/**
 * Retailer values necessary for retrieving buy and sell rates
 */
export interface IRetailerObject {
  sellRate?: number,
  buyRate?: number
}

/**
 * Margin, as from CompanySettings
 */
export interface IMarginSetting {
  margin: number,
}

/**
 * Update commands for cards when adding to inventory
 */
export interface IAddToInventoryCardUpdate {
  balance: number,
  buyAmount?: number
}

/**
 * Params for selecting inventories to mass update
 */
export interface IMassUpdateParams {
  _id: {$in: string[]},
  company?: string
}

/**
 * Array of customers being updated during rejection
 */
export interface IRejectCustomerUpdateArray {
  [key: string]: IRejectCustomerUpdate
}

/**
 * Customer values updated during rejection
 */
export interface IRejectCustomerUpdate {
  credits: mongoose.Types.ObjectId[],
  rejections: mongoose.Types.ObjectId[],
  amount: number
}

/**
 * Params for creating a new card
 */
export interface ICreateNewCardParams {
  retailer: string,
  number: string,
  pin: string,
  userTime: Date,
  balance: number,
  lqCustomerName: string,
  customer: string,
  user: IUser,
  store: string,
  company: ICompany
}

/**
 * Params for creating the actual inventory
 */
export interface ICreateInventoryParams {
  cards: ICard[],
  userTime: Date,
  user: IUser,
  companySettings: ICompanySettings,
  tzOffset: string,
  store: IStore,
  realUserTime: Date,
  transaction: IInventoryTransaction,
  callbackUrl: string
}

/**
 * Params for updating customer denial total
 */
export interface ICustomerDenialsParams {
  rejectionTotal: number,
  thisOrderPurchaseAmount: number,
  modifiedDenials: number,
  customer: ICustomer,
  userTime: Date
}

/**
 * Add cards to inventory params
 */
export interface IAddToInventoryParams {
  userTime: Date,
  modifiedDenials: number,
  store: string,
  transaction: IInventoryTransaction,
  callbackUrl: string,
  user: IUser,
  cards: ICard[]
}

/**
 * Params for making a request to BI to insert a new record
 */
export interface INewBiRequestParams {
  requestId?: string,
  cardNumber?: string,
  pin?: string,
  retailerId?: string,
  user_id?: string
}

/**
 * Params for making a request to BI
 */
export interface IBalanceInquiryParams {
  retailerId?: string,
  number?: string,
  pin?: string,
  cardId?: string,
  userId?: string,
  companyId?: string,
  requestId?: string
}
