const { query, pool } = require('./client');

const runMigrations = async () => {
  try {
    console.log('Starting database migrations...');

    // Create routes table
    await query(`
      CREATE TABLE IF NOT EXISTS routes (
        id SERIAL PRIMARY KEY,
        city VARCHAR(255) NOT NULL,
        pickup_address TEXT NOT NULL,
        dropoff_address TEXT NOT NULL,
        is_active BOOLEAN DEFAULT true,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE(city, pickup_address, dropoff_address)
      )
    `);
    console.log('Routes table created');

    // Create prices table
    await query(`
      CREATE TABLE IF NOT EXISTS prices (
        id SERIAL PRIMARY KEY,
        route_id INTEGER NOT NULL REFERENCES routes(id) ON DELETE CASCADE,
        vehicle_type VARCHAR(50) NOT NULL,
        uber_price DECIMAL(10, 2) NOT NULL,
        surge_factor DECIMAL(5, 2) DEFAULT 1.0,
        used_account VARCHAR(255),
        checked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
      )
    `);
    console.log('Prices table created');

    // Create indexes for prices table
    await query(`
      CREATE INDEX IF NOT EXISTS idx_prices_route_vehicle_checked
      ON prices(route_id, vehicle_type, checked_at DESC)
    `);

    await query(`
      CREATE INDEX IF NOT EXISTS idx_prices_checked_at
      ON prices(checked_at DESC)
    `);
    console.log('Prices indexes created');

    // Create account_health table
    await query(`
      CREATE TABLE IF NOT EXISTS account_health (
        id SERIAL PRIMARY KEY,
        account_email VARCHAR(255) UNIQUE NOT NULL,
        last_used TIMESTAMP,
        successful_queries INTEGER DEFAULT 0,
        failed_queries INTEGER DEFAULT 0,
        is_banned BOOLEAN DEFAULT false,
        ban_reason VARCHAR(500),
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

// Run migrations if this file is executed directly
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
