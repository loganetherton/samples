import * as express from 'express';

const router = express.Router();

/**
 * Log the user out
 */
router.get('/', function(req, res) {
  req.user = null;
  return res.json({});
});

module.exports = router;
