const { query } = require('./client');

const runMigrations = async () => {
  try {
    console.log('Starting database migrations...');

    // Create prices table (address-based, no routes FK)
    await query(`
      CREATE TABLE IF NOT EXISTS prices (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        city TEXT NOT NULL,
        pickup_address TEXT NOT NULL,
        dropoff_address TEXT NOT NULL,
        vehicle_type TEXT NOT NULL,
        price REAL NOT NULL,
        surge_factor REAL DEFAULT 1.0,
        used_account TEXT,
        checked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
      )
    `);
    console.log('Prices table created');

    // Create index for address + vehicle + time queries
    await query(`
      CREATE INDEX IF NOT EXISTS idx_prices_address_vehicle_time
      ON prices(city, pickup_address, dropoff_address, vehicle_type, checked_at DESC)
    `);

    // Create index for time-based queries (historical data)
    await query(`
      CREATE INDEX IF NOT EXISTS idx_prices_checked_at
      ON prices(checked_at DESC)
    `);
    console.log('Prices indexes created');

    // Create account_health table
    await query(`
      CREATE TABLE IF NOT EXISTS account_health (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        email TEXT UNIQUE NOT NULL,
        last_used TIMESTAMP,
        successful_queries INTEGER DEFAULT 0,
        failed_queries INTEGER DEFAULT 0,
        is_banned INTEGER DEFAULT 0,
        ban_reason TEXT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
      )
    `);
    console.log('Account health table created');

    console.log('All migrations completed successfully');
    return true;
  } catch (err) {
    console.error('Migration error:', err);
    throw err;
  }
};

if (require.main === module) {
  runMigrations()
    .then(() => {
      console.log('Migrations completed');
      process.exit(0);
    })
    .catch((err) => {
      console.error('Migration failed:', err);
      process.exit(1);
    });
}

module.exports = {
  runMigrations,
};
