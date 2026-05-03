require('dotenv').config();

const fs = require('fs');
const path = require('path');
const config = require('./config');
const { runMigrations } = require('./database/migrations');
const createServer = require('./api/server');
const Scraper = require('./scraper');
const AccountManager = require('./scraper/account-manager');
const { startCronjob, stopCronjob } = require('./jobs/cronjob');
const logger = require('./utils/logger');

const startApp = async () => {
  const startTime = Date.now();

  try {
    const dataDir = path.join(__dirname, '../data');
    if (!fs.existsSync(dataDir)) {
      fs.mkdirSync(dataDir, { recursive: true });
    }

    logger.info('Starting Uber Price Bot', { nodeEnv: config.NODE_ENV });

    logger.info('Loading configuration', {
      apiPort: config.API_PORT,
      cronSchedule: config.CRON_SCHEDULE,
      enableAPI: config.ENABLE_API,
      enableScraper: config.ENABLE_SCRAPER
    });

    await runMigrations();

    let scraper = null;
    let accountManager = null;

    if (config.ENABLE_SCRAPER) {
      try {
        const accounts = config.getUberAccounts();
        logger.info(`Loaded ${accounts.length} Uber accounts`);
        
        accountManager = new AccountManager(accounts);
        await accountManager.initializeAccounts();
        
        scraper = new Scraper(accountManager);
        await scraper.initialize();
      } catch (err) {
        logger.warn('Scraper error:', err);
        config.ENABLE_SCRAPER = false;
      }
    }

    let server = null;

    if (config.ENABLE_API) {
      const app = createServer();
      server = app.listen(config.API_PORT, '0.0.0.0', () => {
        logger.info(`API server started on http://0.0.0.0:${config.API_PORT}`);
      });
    }

    if (config.ENABLE_SCRAPER && scraper) {
      startCronjob(scraper, config.CRON_SCHEDULE);
    }

    const duration = Date.now() - startTime;
    logger.info('App started successfully', { durationMs: duration });

    process.on('SIGINT', async () => {
      logger.info('Received SIGINT, shutting down gracefully');
      stopCronjob();
      if (server) {
        server.close(() => {
          logger.info('API server closed');
        });
      }
      process.exit(0);
    });
  } catch (err) {
    logger.error('Failed to start app', err);
    process.exit(1);
  }
};

startApp();
