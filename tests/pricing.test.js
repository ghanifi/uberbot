const { runMigrations } = require('../src/database/migrations');
const db = require('../src/database/client');
const queries = require('../src/database/queries');
const PricingEngine = require('../src/pricing/engine');

describe('Pricing Engine', () => {
  let testRouteId;

  beforeAll(async () => {
    await runMigrations();

    // Create test route and prices
    const route = await queries.createRoute('London', 'Heathrow', 'Central London');
    testRouteId = route.id;

    // Insert multiple prices for history testing
    await queries.insertPrice(testRouteId, 'UberX', 25.00, 'test@example.com');
    await queries.insertPrice(testRouteId, 'UberX', 27.50, 'test@example.com');
    await queries.insertPrice(testRouteId, 'UberXL', 35.00, 'test@example.com');
  });

  afterAll(async () => {
    await db.pool.end();
  });

  test('getLatestPrices should return latest prices for all vehicle types', async () => {
    const prices = await PricingEngine.getLatestPrices(testRouteId);

    expect(prices).toBeDefined();
    expect(prices).toHaveProperty('UberX');
    expect(prices).toHaveProperty('UberXL');
    expect(prices.UberX).toHaveProperty('price');
    expect(prices.UberX).toHaveProperty('checkedAt');
  });

  test('getLatestPrices should return correct latest price', async () => {
    const prices = await PricingEngine.getLatestPrices(testRouteId);

    // Should return the latest price for UberX (27.50)
    expect(prices.UberX.price).toBe(27.50);
  });

  test('getPriceInterpolated should find matching route and return price', async () => {
    const result = await PricingEngine.getPriceInterpolated('Heathrow', 'Central', 'UberX');

    expect(result).toBeDefined();
    if (result) {
      expect(result).toHaveProperty('price');
      expect(result).toHaveProperty('checkedAt');
      expect(result).toHaveProperty('source');
      expect(result).toHaveProperty('nearestRoute');
    }
  });

  test('getPriceInterpolated should return null for non-matching routes', async () => {
    const result = await PricingEngine.getPriceInterpolated('NonExistent', 'Nowhere', 'UberX');

    expect(result).toBeNull();
  });

  test('getHistoricalPrices should return price history with stats', async () => {
    const history = await PricingEngine.getHistoricalPrices(testRouteId, 'UberX', 30);

    expect(history).toHaveProperty('prices');
    expect(history).toHaveProperty('stats');
    expect(history).toHaveProperty('dataPoints');
    expect(Array.isArray(history.prices)).toBe(true);
    expect(history.prices.length).toBeGreaterThan(0);
  });

  test('getHistoricalPrices should calculate correct stats', async () => {
    const history = await PricingEngine.getHistoricalPrices(testRouteId, 'UberX', 30);

    if (history.stats) {
      expect(history.stats).toHaveProperty('min');
      expect(history.stats).toHaveProperty('max');
      expect(history.stats).toHaveProperty('average');
      expect(history.stats.min).toBeLessThanOrEqual(history.stats.max);
      expect(history.stats.average).toBeLessThanOrEqual(history.stats.max);
      expect(history.stats.average).toBeGreaterThanOrEqual(history.stats.min);
    }
  });

  test('getHistoricalPrices should return empty result for non-existent vehicle', async () => {
    const history = await PricingEngine.getHistoricalPrices(testRouteId, 'NonExistentVehicle', 30);

    expect(history.prices).toEqual([]);
    expect(history.stats).toBeNull();
    expect(history.dataPoints).toBe(0);
  });
});
