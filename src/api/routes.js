const express = require('express');
const router = express.Router();
const controllers = require('./controllers');

// Price endpoints
router.get('/price/:routeId', controllers.getPrice);
router.get('/price/dynamic', controllers.getPriceDynamic);
router.get('/prices/:routeId', controllers.getPriceHistory);

// Health endpoint
router.get('/health', controllers.getHealth);

module.exports = router;
