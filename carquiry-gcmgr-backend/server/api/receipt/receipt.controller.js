import Receipt from './receipt.model';

import ErrorLog from '../errorLog/errorLog.model';

/**
 * Retrieve a receipt
 */
export function getReceipt(req, res) {
  Receipt.findById(req.params.receiptId)
    .populate('customer')
    .populate('store')
    .populate({
      path: 'user',
      populate: [
        {
          path: 'store',
          model: 'Store'
        },
        {
          path: 'company',
          model: 'Company'
        }
      ]
    })
    .populate({
      path: 'inventories',
      populate: [{
        path: 'card',
        model: 'Card'
      }, {
        path: 'retailer',
        model: 'Retailer'
      }]
    })
    .then(receipt => res.json(receipt))
    .catch(async err => {
      console.log('**************ERR IN GET RECEIPT**********');
      console.log(err);
      await ErrorLog.create({
        body: req.body ? req.body : {},
        params: req.params ? req.params : {},
        method: 'getReceipt',
        controller: 'receipt.controller',
        stack: err ? err.stack : null,
        error: err
      });
      return res.status(500).json(err);
    });
}
