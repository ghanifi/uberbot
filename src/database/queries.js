const { query } = require('./client');

// Prices queries (address-based)
const insertPrice = (city, pickup, dropoff, vehicleType, price, surge, account) =>
  query(
    'INSERT INTO prices (city, pickup_address, dropoff_address, vehicle_type, price, surge_factor, used_account) VALUES (?, ?, ?, ?, ?, ?, ?)',
    [city, pickup, dropoff, vehicleType, price, surge, account]
  );

const getLatestPrice = (city, pickup, dropoff, vehicleType) =>
  query(
    'SELECT * FROM prices WHERE city = ? AND pickup_address = ? AND dropoff_address = ? AND vehicle_type = ? ORDER BY checked_at DESC LIMIT 1',
    [city, pickup, dropoff, vehicleType]
  );

const getPriceHistory = (city, pickup, dropoff, vehicleType, days = 30) =>
  query(
    'SELECT vehicle_type, price, surge_factor, used_account, checked_at FROM prices WHERE city = ? AND pickup_address = ? AND dropoff_address = ? AND vehicle_type = ? AND checked_at > datetime("now", "-" || ? || " days") ORDER BY checked_at DESC',
    [city, pickup, dropoff, vehicleType, days]
  );

const getAllPricesForRoute = (city, pickup, dropoff) =>
  query(
    'SELECT DISTINCT vehicle_type, (SELECT price FROM prices p2 WHERE p2.city = prices.city AND p2.pickup_address = prices.pickup_address AND p2.dropoff_address = prices.dropoff_address AND p2.vehicle_type = prices.vehicle_type ORDER BY p2.checked_at DESC LIMIT 1) as latest_price FROM prices WHERE city = ? AND pickup_address = ? AND dropoff_address = ? GROUP BY vehicle_type',
    [city, pickup, dropoff]
  );

const findSimilarRoutes = (city, pickup, limit = 5) =>
  query(
    'SELECT DISTINCT pickup_address, dropoff_address FROM prices WHERE city = ? AND pickup_address LIKE ? GROUP BY pickup_address, dropoff_address LIMIT ?',
    [city, `%${pickup}%`, limit]
  );

// Account health queries
const initializeAccount = (email) =>
  query(
    'INSERT OR IGNORE INTO account_health (email, successful_queries, failed_queries, is_banned) VALUES (?, 0, 0, 0)',
    [email]
  );

const recordSuccessfulQuery = (email) =>
  query(
    'UPDATE account_health SET successful_queries = successful_queries + 1, last_used = CURRENT_TIMESTAMP, failed_queries = 0 WHERE email = ?',
    [email]
  );

const recordFailedQuery = (email) =>
  query(
    'UPDATE account_health SET failed_queries = failed_queries + 1, last_used = CURRENT_TIMESTAMP WHERE email = ?',
    [email]
  );

const markAccountBanned = (email, reason) =>
  query(
    'UPDATE account_health SET is_banned = 1, ban_reason = ? WHERE email = ?',
    [reason, email]
  );

const getAccountHealth = (email) =>
  query(
    'SELECT * FROM account_health WHERE email = ?',
    [email]
  );

const getAllAccountHealth = () =>
  query('SELECT * FROM account_health');

const isBanned = async (email) => {
  const results = await query(
    'SELECT is_banned FROM account_health WHERE email = ?',
    [email]
  );
  return results.length > 0 && results[0].is_banned === 1;
};

module.exports = {
  insertPrice,
  getLatestPrice,
  getPriceHistory,
  getAllPricesForRoute,
  findSimilarRoutes,
  initializeAccount,
  recordSuccessfulQuery,
  recordFailedQuery,
  markAccountBanned,
  getAccountHealth,
  getAllAccountHealth,
  isBanned,
};

// Alias for backward compatibility
const getAllAccountsHealth = getAllAccountHealth;

module.exports.getAllAccountsHealth = getAllAccountsHealth;
