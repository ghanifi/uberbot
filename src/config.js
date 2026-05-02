module.exports = {
  CRON_SCHEDULE: process.env.CRON_SCHEDULE || '0 6,9,18,23 * * *',
  API_PORT: process.env.API_PORT || 3000,
  API_HOST: process.env.API_HOST || 'localhost',
  NODE_ENV: process.env.NODE_ENV || 'development',
  ENABLE_SCRAPER: process.env.ENABLE_SCRAPER !== 'false',
  ENABLE_API: process.env.ENABLE_API !== 'false',

  getUberAccounts() {
    const accountsEnv = process.env.UBER_ACCOUNTS;

    if (!accountsEnv) {
      throw new Error('UBER_ACCOUNTS environment variable is not set');
    }

    try {
      const accounts = JSON.parse(accountsEnv);
      if (!Array.isArray(accounts)) {
        throw new Error('UBER_ACCOUNTS must be a JSON array');
      }
      return accounts;
    } catch (err) {
      throw new Error(`Failed to parse UBER_ACCOUNTS: ${err.message}`);
    }
  }
};
