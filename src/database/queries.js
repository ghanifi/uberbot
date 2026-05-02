const { query } = require('./client');

// Routes queries
const getRoute = async (id) => {
  const result = await query('SELECT * FROM routes WHERE id = $1', [id]);
  return result.rows[0];
};

const getAllRoutes = async () => {
  const result = await query(
    'SELECT * FROM routes WHERE is_active = true ORDER BY city',
  );
  return result.rows;
};

const createRoute = async (city, pickupAddress, dropoffAddress) => {
  const result = await query(
    `INSERT INTO routes (city, pickup_address, dropoff_address)
     VALUES ($1, $2, $3)
     RETURNING *`,
    [city, pickupAddress, dropoffAddress],
  );
  return result.rows[0];
};

// Prices queries
const insertPrice = async (routeId, vehicleType, uberPrice, usedAccount) => {
  const result = await query(
    `INSERT INTO prices (route_id, vehicle_type, uber_price, used_account)
     VALUES ($1, $2, $3, $4)
     RETURNING *`,
    [routeId, vehicleType, uberPrice, usedAccount],
  );
  return result.rows[0];
};

const getLatestPrice = async (routeId, vehicleType) => {
  const result = await query(
    `SELECT * FROM prices
     WHERE route_id = $1 AND vehicle_type = $2
     ORDER BY checked_at DESC
     LIMIT 1`,
    [routeId, vehicleType],
  );
  return result.rows[0];
};

const getPriceHistory = async (routeId, vehicleType, days = 30) => {
  const result = await query(
    `SELECT * FROM prices
     WHERE route_id = $1 AND vehicle_type = $2
     AND checked_at > NOW() - INTERVAL '${days} days'
     ORDER BY checked_at DESC`,
    [routeId, vehicleType],
  );
  return result.rows;
};

const getPricesForRoute = async (routeId) => {
  const result = await query(
    `SELECT DISTINCT ON (vehicle_type)
      id, route_id, vehicle_type, uber_price, surge_factor, used_account, checked_at
     FROM prices
     WHERE route_id = $1
     ORDER BY vehicle_type, checked_at DESC`,
    [routeId],
  );
  return result.rows;
};

// Account health queries
const createAccount = async (email) => {
  const result = await query(
    `INSERT INTO account_health (account_email)
     VALUES ($1)
     ON CONFLICT (account_email) DO UPDATE SET updated_at = CURRENT_TIMESTAMP
     RETURNING *`,
    [email],
  );
  return result.rows[0];
};

const updateAccountSuccess = async (email) => {
  const result = await query(
    `UPDATE account_health
     SET successful_queries = successful_queries + 1,
         last_used = CURRENT_TIMESTAMP,
         updated_at = CURRENT_TIMESTAMP
     WHERE account_email = $1
     RETURNING *`,
    [email],
  );
  return result.rows[0];
};

const updateAccountFailure = async (email) => {
  const result = await query(
    `UPDATE account_health
     SET failed_queries = failed_queries + 1,
         updated_at = CURRENT_TIMESTAMP
     WHERE account_email = $1
     RETURNING *`,
    [email],
  );
  return result.rows[0];
};

const banAccount = async (email, reason) => {
  const result = await query(
    `UPDATE account_health
     SET is_banned = true,
         ban_reason = $2,
         updated_at = CURRENT_TIMESTAMP
     WHERE account_email = $1
     RETURNING *`,
    [email, reason],
  );
  return result.rows[0];
};

const getAccountHealth = async (email) => {
  const result = await query(
    'SELECT * FROM account_health WHERE account_email = $1',
    [email],
  );
  return result.rows[0];
};

const getAllAccountsHealth = async () => {
  const result = await query(
    'SELECT * FROM account_health ORDER BY last_used DESC',
  );
  return result.rows;
};

module.exports = {
  // Routes
  getRoute,
  getAllRoutes,
  createRoute,
  // Prices
  insertPrice,
  getLatestPrice,
  getPriceHistory,
  getPricesForRoute,
  // Account Health
  createAccount,
  updateAccountSuccess,
  updateAccountFailure,
  banAccount,
  getAccountHealth,
  getAllAccountsHealth,
};
