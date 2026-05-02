const queries = require('../database/queries');
const PricingEngine = require('../pricing/engine');
const logger = require('../utils/logger');

const getPrice = async (req, res) => {
  try {
    const { routeId } = req.params;

    const route = await queries.getRoute(parseInt(routeId, 10));
    if (!route) {
      return res.status(404).json({ error: 'Route not found' });
    }

    const prices = await PricingEngine.getLatestPrices(parseInt(routeId, 10));

    res.json({
      route_id: route.id,
      pickup: route.pickup_address,
      dropoff: route.dropoff_address,
      city: route.city,
      prices
    });
  } catch (err) {
    logger.error('Error in getPrice', err);
    res.status(500).json({ error: 'Internal server error' });
  }
};

const getPriceDynamic = async (req, res) => {
  try {
    const { pickup, dropoff, vehicle } = req.query;

    if (!pickup || !dropoff || !vehicle) {
      return res.status(400).json({ error: 'Missing required parameters: pickup, dropoff, vehicle' });
    }

    const priceData = await PricingEngine.getPriceInterpolated(pickup, dropoff, vehicle);

    if (!priceData) {
      return res.status(404).json({ error: 'Price data not found' });
    }

    res.json(priceData);
  } catch (err) {
    logger.error('Error in getPriceDynamic', err);
    res.status(500).json({ error: 'Internal server error' });
  }
};

const getPriceHistory = async (req, res) => {
  try {
    const { routeId } = req.params;
    const { days = 30, vehicle } = req.query;

    if (!vehicle) {
      return res.status(400).json({ error: 'Missing required parameter: vehicle' });
    }

    const route = await queries.getRoute(parseInt(routeId, 10));
    if (!route) {
      return res.status(404).json({ error: 'Route not found' });
    }

    const history = await PricingEngine.getHistoricalPrices(
      parseInt(routeId, 10),
      vehicle,
      parseInt(days, 10)
    );

    res.json({
      route_id: route.id,
      pickup: route.pickup_address,
      dropoff: route.dropoff_address,
      vehicle,
      days: parseInt(days, 10),
      ...history
    });
  } catch (err) {
    logger.error('Error in getPriceHistory', err);
    res.status(500).json({ error: 'Internal server error' });
  }
};

const getHealth = async (req, res) => {
  try {
    const healthSummary = require('../scraper/account-manager');
    const accountManager = new healthSummary([]);

    const summary = await accountManager.getHealthSummary();

    res.json({
      status: 'ok',
      accounts: {
        active: summary.activeAccounts,
        banned: summary.bannedAccounts,
        total: summary.totalAccounts,
        totalQueries: summary.details.reduce((sum, acc) => sum + acc.successful_queries + acc.failed_queries, 0)
      }
    });
  } catch (err) {
    logger.error('Error in getHealth', err);
    res.status(500).json({ error: 'Internal server error' });
  }
};

module.exports = {
  getPrice,
  getPriceDynamic,
  getPriceHistory,
  getHealth
};
