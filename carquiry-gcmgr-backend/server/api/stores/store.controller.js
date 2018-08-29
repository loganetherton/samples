import ReceiptService from '../receipt/receipt.service';

import ErrorLog from '../errorLog/errorLog.model';

import * as _ from 'lodash';

/**
 * Retrieve store receipts
 */
export async function getReceipts(req, res) {
  const {perPage = 20, offset = 0} = req.query;

  try {
    const receiptService = new ReceiptService();
    const query = Object.assign({}, _.pick(req.query, ['created']), {store: req.user.store});
    const [totalReceipts, receipts] = await Promise.all([
      receiptService.getReceiptsCount(query),
      receiptService.getReceipts(query, {perPage: parseInt(perPage, 10), offset: parseInt(offset, 10)})
    ]);

    res.json({
      data: receipts,
      pagination: {
        total: totalReceipts
      }
    });
  } catch (err) {
    console.log('**************ERR IN GET RECEIPTS**********');
    console.log(err);
    await ErrorLog.create({
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'getReceipts',
      controller: 'store.controller',
      stack: err ? err.stack : null,
      error: err
    });
    return res.status(500).json(err);
  }
}
