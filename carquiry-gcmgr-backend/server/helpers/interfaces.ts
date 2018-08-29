import {Types} from "mongoose";
import {ICompany} from "../api/company/company.model";
import {IUser} from "../api/user/user.model";
import {IStore} from "../api/stores/store.model";

/**
 * Generic response for a function which is going to result in the resolution of an endpoint
 */
export interface IGenericEndResponse {
  status: number,
  response: any
}

/**
 * Response from login
 */
export interface ILoginResponse {
  token: string | Types.ObjectId,
  customerId: string | Types.ObjectId,
  companyId: string | Types.ObjectId,
  callbackToken?: string
}

/**
 * Body for creating user
 */
export interface ICreateUserBody {
  email: string,
  password: string,
  firstName: string,
  lastName: string,
  company?: string,
  store?: string
}

/**
 * Models to return when creating user
 */
export interface ICreateUserModel {
  company?: ICompany,
  user?: IUser,
  store?: IStore
}

/**
 * Generic BI request response
 */
export interface IBiRequestResponse {
  verificationType?: string,
  balance: number,
  response_datetime?: string,
  responseMessage: string,
  requestId: string,
  responseCode: string,
  request_id: string,
  responseDateTime?: string,
  recheck?: string,
  recheckDateTime?: string,
  retailer        ?: string,
  bot_statuses?: string
}

/**
 * Generic BI search params
 */
export interface IBiSearchParams {
  number: string,
  retailerId?: Types.ObjectId,
  pin?: string,
}

/**
 * Card search params
 */
export interface ICardSearchParams {
  retailer: string
}

/**
 * Generic response intended to be handled by express
 */
export interface IGenericExpressResponse {
  status: number,
  message: any
}
