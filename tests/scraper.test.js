const { runMigrations } = require('../src/database/migrations');
const db = require('../src/database/client');
const queries = require('../src/database/queries');
const AccountManager = require('../src/scraper/account-manager');
const Parser = require('../src/scraper/parser');

describe('Account Manager', () => {
  const mockAccounts = [
    { email: 'bot-1@gmail.com', password: 'pass1' },
    { email: 'bot-2@gmail.com', password: 'pass2' },
  ];

  let accountManager;

  beforeAll(async () => {
    // Run migrations before tests
    await runMigrations();
    accountManager = new AccountManager(mockAccounts);
  });

  afterAll(async () => {
    // Close the pool after all tests
    await db.pool.end();
  });

  test('should initialize accounts', async () => {
    await accountManager.initializeAccounts();

    const health = await accountManager.getHealthSummary();

    expect(health.totalAccounts).toBe(2);
    expect(health.activeAccounts).toBe(2);
    expect(health.bannedAccounts).toBe(0);
  });

  test('should get next account (not banned)', async () => {
    const account = await accountManager.getNextAccount();

    expect(account).toBeDefined();
    expect([mockAccounts[0].email, mockAccounts[1].email]).toContain(account.email);
  });

  test('should record success', async () => {
    const email = mockAccounts[0].email;
    await accountManager.recordSuccess(email);

    const health = await queries.getAccountHealth(email);

    expect(health.successful_queries).toBe(1);
  });

  test('should record failure and ban after threshold', async () => {
    const email = mockAccounts[1].email;

    // Record 5 failures
    for (let i = 0; i < 5; i++) {
      await accountManager.recordFailure(email);
    }

    const health = await queries.getAccountHealth(email);

    expect(health.is_banned).toBe(true);
    expect(health.failed_queries).toBe(5);
  });

  test('should not return banned accounts', async () => {
    const account = await accountManager.getNextAccount();

    // Should return bot-1 since bot-2 is banned
    expect(account.email).toBe('bot-1@gmail.com');
  });

  test('should throw if all accounts banned', async () => {
    const email = mockAccounts[0].email;

    // Ban the remaining active account
    for (let i = 0; i < 5; i++) {
      await accountManager.recordFailure(email);
    }

    await expect(accountManager.getNextAccount()).rejects.toThrow('No active accounts available');
  });
});

describe('Parser', () => {
  test('should parse price with dollar sign', () => {
    expect(Parser.parsePrice('$25.50')).toBe(25.50);
  });

  test('should parse price with pound sign', () => {
    expect(Parser.parsePrice('£25.50')).toBe(25.50);
  });

  test('should parse price with comma separator', () => {
    expect(Parser.parsePrice('$1,250.99')).toBe(1250.99);
  });

  test('should parse price with multiple spaces', () => {
    expect(Parser.parsePrice('$ 25 . 50')).toBe(25.50);
  });

  test('should return null for invalid price string', () => {
    expect(Parser.parsePrice('invalid')).toBeNull();
  });

  test('should extract UberX vehicle type', () => {
    expect(Parser.extractVehicleType('UberX - Economy')).toBe('UberX');
  });

  test('should extract UberXL vehicle type', () => {
    expect(Parser.extractVehicleType('UberXL - Extra Large')).toBe('UberXL');
  });

  test('should extract Exec vehicle type', () => {
    expect(Parser.extractVehicleType('Exec - Executive')).toBe('Exec');
  });

  test('should return null for unknown vehicle type', () => {
    expect(Parser.extractVehicleType('Unknown Type')).toBeNull();
  });

  test('should parse valid price card', () => {
    const cardText = 'UberX\n$25.50\nETA 12 min';
    const result = Parser.parsePriceCard(cardText);
    expect(result).toEqual({ vehicleType: 'UberX', price: 25.50 });
  });

  test('should return null for invalid price card', () => {
    const cardText = 'No price here\nNo type either';
    expect(Parser.parsePriceCard(cardText)).toBeNull();
  });

  test('should handle empty price card', () => {
    expect(Parser.parsePriceCard('')).toBeNull();
  });
});
