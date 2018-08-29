import BiService from './bi.request';
import BiRequestLog from '../biRequestLog/biRequestLog.model';
import ErrorLog from '../errorLog/errorLog.model';
import Retailer from '../retailer/retailer.model';

/**
 * Get pending cards from BI (optionally, for a single retailer)
 */
export async function getPendingCards(req, res) {
  const {row, direction, dateBegin, dateEnd} = req.params;
  const cards = await BiService.getPendingCards(row, direction, dateBegin, dateEnd);
  return res.json({cards});
}

/**
 * Set the balance for a card in BI
 */
export async function setBalance(req, res) {
  await BiService.setBalance(req.body);
  return res.json({});
}

/**
 * Set the balance for multiple cardsin BI
 */
export async function setBalanceBatch(req, res) {
  const {cards} = req.body;
  for (const card of cards) {
    await BiService.setBalance(card);
  }
  return res.json({});
}

/**
 * Reinsert BI requests to be
 * @param req
 * @param res
 * @return {Promise.<void>}
 */
export async function reinsertBi(req, res) {
  try {
    const {begin, end, cardNumbers = []} = req.body;
    let logs;
    // By card number
    if (cardNumbers.length) {
      logs = await BiRequestLog.find({number: {$in: cardNumbers}});
    // By date
    } else {
      logs = await BiRequestLog.find({created: {$gt: new Date(begin), $lt: new Date(end)}});
    }
    for (let [index, log] of logs.entries()) {
      setTimeout(async () => {
        try {
          const biResponse = await BiService.getRecord(log.requestId);
          // Doesn't exist
          if (biResponse.responseCode === '182') {
            const retailer = await Retailer.findById(log.retailerId);
            const retailerId = retailer.gsId || retailer.aiId;
            if (!retailerId) {
              throw new Error('Unable to find BI retailer ID');
            }
            const data = {
              cardNumber: log.number,
              pin: log.pin,
              retailerId
            };
            if (log.requestId) {
              data.requestid = log.requestId;
            }
            await BiService.insert(data);
          }
        } catch (err) {
          console.log('**************ERR IN REINSERTBI LOOP**********');
          console.log(err);
          await ErrorLog.create({
            user: req && req.user && req.user._id ? req.user._id : null,
            body: req.body ? req.body : {},
            params: req.params ? req.params : {},
            method: 'reinsertBi',
            controller: 'bi.controller',
            stack: err ? err.stack : null,
            error: err,
            message: err.message
          });
        }
      }, index * 1000);
    }
    return res.json({});
  } catch (err) {
    console.log('**************ERR IN REINSERTBI**********');
    await ErrorLog.create({
      user: req && req.user && req.user._id ? req.user._id : null,
      body: req.body ? req.body : {},
      params: req.params ? req.params : {},
      method: 'reinsertBi',
      controller: 'bi.controller',
      stack: err ? err.stack : null,
      error: err,
      message: err.message
    });
  }
}
