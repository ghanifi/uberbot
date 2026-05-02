const queries = require('../database/queries');

class PricingEngine {
  static async getLatestPrices(routeId) {
    const prices = await queries.getPricesForRoute(routeId);
    const result = {};

    for (const price of prices) {
      result[price.vehicle_type] = {
        price: price.uber_price,
        checkedAt: price.checked_at
      };
    }

    return result;
  }

  static async getPriceInterpolated(pickup, dropoff, vehicleType) {
    const allRoutes = await queries.getAllRoutes();

    // Simple keyword matching to find closest route
    let bestMatch = null;
    let bestScore = 0;

    for (const route of allRoutes) {
      let score = 0;

      if (route.pickup_address.toLowerCase().includes(pickup.toLowerCase())) {
        score += 50;
      }
      if (route.dropoff_address.toLowerCase().includes(dropoff.toLowerCase())) {
        score += 50;
      }

      if (score > bestScore) {
        bestScore = score;
        bestMatch = route;
      }
    }

    if (!bestMatch) {
      return null;
    }

    const price = await queries.getLatestPrice(bestMatch.id, vehicleType);

    if (!price) {
      return null;
    }

    return {
      price: price.uber_price,
      checkedAt: price.checked_at,
      source: 'interpolated',
      nearestRoute: {
        id: bestMatch.id,
        city: bestMatch.city,
        pickup: bestMatch.pickup_address,
        dropoff: bestMatch.dropoff_address
      }
    };
  }

  static async getHistoricalPrices(routeId, vehicleType, days = 30) {
    const priceHistory = await queries.getPriceHistory(routeId, vehicleType, days);

    if (priceHistory.length === 0) {
      return {
        prices: [],
        stats: null,
        dataPoints: 0
      };
    }

    const prices = priceHistory.map(p => p.uber_price);
    const min = Math.min(...prices);
    const max = Math.max(...prices);
    const avg = prices.reduce((a, b) => a + b, 0) / prices.length;

    return {
      prices: priceHistory.map(p => ({
        price: p.uber_price,
        checkedAt: p.checked_at,
        usedAccount: p.used_account
      })),
      stats: {
        min,
        max,
        average: parseFloat(avg.toFixed(2)),
        dataPoints: prices.length
      },
      dataPoints: prices.length
    };
  }
}

module.exports = PricingEngine;
