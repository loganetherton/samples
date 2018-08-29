import * as express from 'express';
import * as multer from 'multer';
import validationRules from './validationRules';

const controller = require('./retailer.controller');
const lqController = require('../lq/lq.controller');
const auth = require('../auth/auth.service');
const upload = multer({dest: __dirname + '/rates/'});

const router = express.Router();

/**
 * Helpers
 */
  // Import retailers
router.get('/import-csv', controller.importCsv);
// Save images type to retailer objects
router.get('/retailer-image-types', controller.retailerImageTypes);
// Import URL/phone
router.get('/import-url', controller.addRetailerUrl);

/**
 * Real API calls
 */
// Get retailers associated with a store
router.get('/store/:storeId', auth.isAuthenticated(), controller.getRetailersNew);
// Get retailers associated with a store, with a min value set
router.get('/store/:storeId/min/:minVal', auth.isAuthenticated(), controller.getRetailersNew);
// Get store buy/sell rates as CSV
router.post('/store/:storeId/min/:minVal/csv', auth.isAuthenticated(), (req, res, next) => {
  req.csv = true;
  next();
}, controller.getRetailersNew);
// Query retailers
router.get('/', auth.isAuthenticated(), controller.queryRetailers);
// Get all retailers
router.get('/all', auth.isAuthenticated(), controller.getAllRetailers);
// Get all rates
router.get('/rates', auth.hasRole('admin'), controller.getAllRates);
// BI info
router.get('/biInfo', auth.hasRole('admin'), controller.getBiInfo);
router.post('/biInfo', auth.hasRole('admin'), controller.updateBiInfo);
router.get('/biInfo/csv', auth.hasRole('admin'), controller.biInfoCsv);
// Inventory statistics by retailer
router.get('/salesStats/:dateBegin/:dateEnd', auth.hasRole('corporate-admin'), controller.salesStats);
router.get('/salesStats/:storeId/:dateBegin/:dateEnd', auth.hasRole('corporate-admin'), controller.salesStats);
// Create new retailer based on an old one
router.post('/createLike', auth.hasRole('admin'), controller.createNewRetailerBasedOnOldOne);
// Upload CC doc
router.post('/settings/cc/rates', auth.hasRole('admin'), upload.array('ccRates', 1), controller.uploadCcRatesDoc);
// Upload CardPool doc
router.post('/settings/cp/rates', auth.hasRole('admin'), upload.array('cpRates', 1), controller.uploadCpRatesDoc);
// Upload CardPool electronic/physical retailers doc
router.post('/settings/cp/electronicPhysical', auth.hasRole('admin'), upload.array('cpElectronicPhysical', 1),
  controller.uploadElectronicPhysical);
// Change GiftSquirrel ID
router.post('/:retailerId/gsId', auth.hasRole('admin'), controller.setGsId);
// Set retailer prop
router.post('/:retailerId/setProp', auth.hasRole('admin'), controller.setProp);
// Sync GCMGR with BI
router.post('/syncWithBi', auth.hasRole('admin'), controller.syncWithBi);
// Disable retailers for a specific company
router.post('/toggleDisableForCompany', auth.hasRole('admin'), controller.toggleDisableForCompany);
router.post('/createRetailer', auth.hasRole('admin'), controller.createRetailer);
router.put('/:retailer/regex', auth.hasRole('admin', true, validationRules), controller.setRetailerRegex);
// Download electronic brands
router.get('/admin/electronicBrands', auth.hasRole('admin'), controller.adminDownloadElectronicBrands);

module.exports = router;
