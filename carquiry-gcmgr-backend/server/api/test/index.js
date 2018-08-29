import * as express from 'express';
import Test from './test.model';

const router = express.Router();

// Search customers
router.get('/', async function (req, res) {
  let test = await Test.findOne();
  if (!test) {
    test = new Test();
  }
  test.updated = new Date();
  await test.save();
  return res.json('test');
});

module.exports = router;
