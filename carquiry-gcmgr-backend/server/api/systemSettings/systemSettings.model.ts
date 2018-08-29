import {Document, Schema, Model, model} from 'mongoose';

export interface ISystemSettings extends Document {
  production: string;
  staging: string;
  development: string;
}

export interface ISystemSettingsModel extends Model<ISystemSettings> {}

export const SystemSettingsSchema: Schema = new Schema({
  // Master passwords
  production: String,
  // staging
  staging: String,
  // Developement
  development: String
});

export const SystemSettings: ISystemSettingsModel = model<ISystemSettings, ISystemSettingsModel>('SystemSettings', SystemSettingsSchema);

export default SystemSettings;
