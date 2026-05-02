const request = require('supertest');
const { runMigrations } = require('../src/database/migrations');
const db = require('../src/database/client');
const createServer = require('../src/api/server');
const queries = require('../src/database/queries');

describe('API Endpoints', () => {
  let app;
  let testRouteId;

  beforeAll(async () => {
    await runMigrations();
    app = createServer();

    // Create test route
    const route = await queries.createRoute('Test City', 'Test Pickup', 'Test Dropoff');
    testRouteId = route.id;

    // Insert test price
    await queries.insertPrice(testRouteId, 'UberX', 25.50, 'test@example.com');
  });

  afterAll(async () => {
    await db.pool.end();
  });

  test('GET /api/price/:routeId should return route prices', async () => {
    const response = await request(app)
      .get(`/api/price/${testRouteId}`)
      .expect(200);

    expect(response.body).toHaveProperty('route_id');
    expect(response.body).toHaveProperty('pickup');
    expect(response.body).toHaveProperty('dropoff');
    expect(response.body).toHaveProperty('prices');
    expect(response.body.prices).toHaveProperty('UberX');
  });

  test('GET /api/price/:routeId should return 404 for non-existent route', async () => {
    const response = await request(app)
      .get('/api/price/99999')
      .expect(404);

    expect(response.body).toHaveProperty('error');
  });

  test('GET /api/health should return health status', async () => {
    const response = await request(app)
      .get('/api/health')
      .expect(200);

    expect(response.body).toHaveProperty('status');
    expect(response.body).toHaveProperty('accounts');
    expect(response.body.accounts).toHaveProperty('active');
    expect(response.body.accounts).toHaveProperty('banned');
    expect(response.body.accounts).toHaveProperty('total');
  });

  test('GET /nonexistent should return 404', async () => {
    const response = await request(app)
      .get('/nonexistent')
      .expect(404);

    expect(response.body).toHaveProperty('error');
  });
});
