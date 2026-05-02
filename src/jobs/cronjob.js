const cron = require('node-cron');
const logger = require('../utils/logger');

let job = null;

const startCronjob = (scraper, schedule) => {
  if (job) {
    logger.warn('Cronjob already running');
    return;
  }

  job = cron.schedule(schedule, async () => {
    try {
      logger.info('Cronjob: Starting scraping cycle');
      const result = await scraper.run();

      if (result.success) {
        logger.info('Cronjob: Scraping cycle completed successfully', { durationMs: result.duration });
      } else {
        logger.warn('Cronjob: Scraping cycle failed', { error: result.error, durationMs: result.duration });
      }
    } catch (err) {
      logger.error('Cronjob: Unexpected error during scraping cycle', err);
    }
  });

  logger.info(`Cronjob: Scheduled with pattern "${schedule}"`);
};

const stopCronjob = () => {
  if (job) {
    job.stop();
    job.destroy();
    job = null;
    logger.info('Cronjob: Stopped');
  }
};

module.exports = {
  startCronjob,
  stopCronjob
};
