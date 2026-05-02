const { runMigrations } = require('../src/database/migrations');
const db = require('../src/database/client');
const queries = require('../src/database/queries');

describe('Database Operations', () => {
  beforeAll(async () => {
    // Run migrations before tests
    await runMigrations();
  });

  afterAll(async () => {
    // Close the pool after all tests
    await db.pool.end();
  });

  describe('Routes', () => {
    let createdRoute;

    test('should create a new route', async () => {
      createdRoute = await queries.createRoute(
        'New York',
        '123 Main St',
        '456 Broadway',
      );

      expect(createdRoute).toBeDefined();
      expect(createdRoute.city).toBe('New York');
      expect(createdRoute.pickup_address).toBe('123 Main St');
      expect(createdRoute.dropoff_address).toBe('456 Broadway');
      expect(createdRoute.is_active).toBe(true);
    });

    test('should get a route by id', async () => {
      const route = await queries.getRoute(createdRoute.id);

      expect(route).toBeDefined();
      expect(route.id).toBe(createdRoute.id);
      expect(route.city).toBe('New York');
    });

    test('should get all active routes', async () => {
      // Create another route
      await queries.createRoute('Boston', '789 State St', '321 Washington');

      const routes = await queries.getAllRoutes();

      expect(routes).toBeDefined();
      expect(Array.isArray(routes)).toBe(true);
      expect(routes.length).toBeGreaterThanOrEqual(2);
      expect(routes[0].is_active).toBe(true);
    });

    test('should maintain unique constraint on routes', async () => {
      // Try to create a duplicate route
      await expect(
        queries.createRoute('New York', '123 Main St', '456 Broadway'),
      ).rejects.toThrow();
    });
  });

  describe('Prices', () => {
    let testRoute;
    let createdPrice;

    beforeAll(async () => {
      // Create a test route
      testRoute = await queries.createRoute(
        'Test City',
        'Start Address',
        'End Address',
      );
    });

    test('should insert a price', async () => {
      createdPrice = await queries.insertPrice(
        testRoute.id,
        'UberX',
        25.5,
        'test@example.com',
      );

      expect(createdPrice).toBeDefined();
      expect(createdPrice.route_id).toBe(testRoute.id);
      expect(createdPrice.vehicle_type).toBe('UberX');
      expect(parseFloat(createdPrice.uber_price)).toBe(25.5);
      expect(createdPrice.used_account).toBe('test@example.com');
    });

    test('should get latest price for a route and vehicle type', async () => {
      // Insert another price for the same route/vehicle
      await new Promise((resolve) => setTimeout(resolve, 100));
      await queries.insertPrice(testRoute.id, 'UberX', 26.5, 'test2@example.com');

      const latestPrice = await queries.getLatestPrice(
        testRoute.id,
        'UberX',
      );

      expect(latestPrice).toBeDefined();
      expect(parseFloat(latestPrice.uber_price)).toBe(26.5);
    });

    test('should get price history', async () => {
      const history = await queries.getPriceHistory(testRoute.id, 'UberX', 30);

      expect(history).toBeDefined();
      expect(Array.isArray(history)).toBe(true);
      expect(history.length).toBeGreaterThanOrEqual(1);
    });

    test('should get distinct latest prices for a route', async () => {
      // Insert prices for different vehicle types
      await queries.insertPrice(testRoute.id, 'UberXL', 35.0, 'test@example.com');
      await queries.insertPrice(
        testRoute.id,
        'UberPremium',
        45.0,
        'test@example.com',
      );

      const prices = await queries.getPricesForRoute(testRoute.id);

      expect(prices).toBeDefined();
      expect(Array.isArray(prices)).toBe(true);
      expect(prices.length).toBeGreaterThanOrEqual(3);

      // Verify DISTINCT ON vehicle_type works
      const vehicleTypes = prices.map((p) => p.vehicle_type);
      const uniqueTypes = new Set(vehicleTypes);
      expect(vehicleTypes.length).toBe(uniqueTypes.size);
    });
  });

  describe('Account Health', () => {
    const testEmail = `test${Date.now()}@example.com`;

    test('should create a new account', async () => {
      const account = await queries.createAccount(testEmail);

      expect(account).toBeDefined();
      expect(account.account_email).toBe(testEmail);
      expect(account.successful_queries).toBe(0);
      expect(account.failed_queries).toBe(0);
      expect(account.is_banned).toBe(false);
    });

    test('should update account with successful query', async () => {
      const account = await queries.updateAccountSuccess(testEmail);

      expect(account).toBeDefined();
      expect(account.successful_queries).toBe(1);
      expect(account.last_used).toBeDefined();
    });

    test('should increment successful queries', async () => {
      await queries.updateAccountSuccess(testEmail);
      const account = await queries.updateAccountSuccess(testEmail);

      expect(account.successful_queries).toBe(3);
    });

    test('should update account with failed query', async () => {
      const account = await queries.updateAccountFailure(testEmail);

      expect(account).toBeDefined();
      expect(account.failed_queries).toBe(1);
    });

    test('should ban an account', async () => {
      const banReason = 'Too many failed queries';
      const account = await queries.banAccount(testEmail, banReason);

      expect(account).toBeDefined();
      expect(account.is_banned).toBe(true);
      expect(account.ban_reason).toBe(banReason);
    });

    test('should get account health by email', async () => {
      const account = await queries.getAccountHealth(testEmail);

      expect(account).toBeDefined();
      expect(account.account_email).toBe(testEmail);
      expect(account.is_banned).toBe(true);
    });

    test('should get all accounts health', async () => {
      const accounts = await queries.getAllAccountsHealth();

      expect(accounts).toBeDefined();
      expect(Array.isArray(accounts)).toBe(true);
      expect(accounts.length).toBeGreaterThanOrEqual(1);
    });

    test('should handle ON CONFLICT for duplicate account creation', async () => {
      const account1 = await queries.createAccount(testEmail);
      const account2 = await queries.createAccount(testEmail);

      expect(account1.account_email).toBe(account2.account_email);
      expect(account1.id).toBe(account2.id);
    });
  });
});
