require('dotenv').config();

const config = require('./config');
const { runMigrations } = require('./database/migrations');
const createServer = require('./api/server');
const Scraper = require('./scraper');
const { startCronjob, stopCronjob } = require('./jobs/cronjob');
const logger = require('./utils/logger');

const startApp = async () => {
  const startTime = Date.now();

  try {
    logger.info('Starting Uber Price Bot', { nodeEnv: config.NODE_ENV });

    // Load configuration
    logger.info('Loading configuration', {
      apiPort: config.API_PORT,
      apiHost: config.API_HOST,
      cronSchedule: config.CRON_SCHEDULE,
      enableAPI: config.ENABLE_API,
      enableScraper: config.ENABLE_SCRAPER
    });

    // Run migrations
    logger.info('Running database migrations');
    await runMigrations();

    // Initialize scraper
    let scraper = null;
    let accounts = [];

    if (config.ENABLE_SCRAPER) {
      try {
        accounts = config.getUberAccounts();
        logger.info(`Loaded ${accounts.length} Uber accounts`);
      } catch (err) {
        logger.warn('Scraper enabled but UBER_ACCOUNTS not configured. Scraper will not run.', err);
        config.ENABLE_SCRAPER = false;
      }
    }

    if (config.ENABLE_SCRAPER) {
      scraper = new Scraper(accounts);
      await scraper.initialize();
    }

    // Start API server
    let server = null;

    if (config.ENABLE_API) {
      const app = createServer();
      server = app.listen(config.API_PORT, config.API_HOST, () => {
        logger.info(`API server started on http://${config.API_HOST}:${config.API_PORT}`);
      });
    }

    // Start cronjob
    if (config.ENABLE_SCRAPER && scraper) {
      startCronjob(scraper, config.CRON_SCHEDULE);
    }

    const duration = Date.now() - startTime;
    logger.info('App started successfully', { durationMs: duration });

    // Graceful shutdown
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
