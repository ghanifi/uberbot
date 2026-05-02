const AccountManager = require('./account-manager');
const PlaywrightClient = require('./playwright-client');
const Parser = require('./parser');
const queries = require('../database/queries');
const logger = require('../utils/logger');
const { ROUTES_LONDON, ROUTES_ANTALYA, SCRAPE_DELAY_MIN, SCRAPE_DELAY_MAX } = require('../constants');
const { LoginError, AddressError, PriceExtractionError } = require('../utils/errors');

class Scraper {
  constructor(accounts) {
    this.accountManager = new AccountManager(accounts);
    this.client = null;
  }

  async initialize() {
    await this.accountManager.initializeAccounts();
    logger.info('Scraper initialized');
  }

  async randomDelay() {
    const delay = Math.random() * (SCRAPE_DELAY_MAX - SCRAPE_DELAY_MIN) + SCRAPE_DELAY_MIN;
    await new Promise(resolve => setTimeout(resolve, delay));
  }

  async scrapeRoute(account, city, pickup, dropoff) {
    try {
      // Get or create route
      let route = await queries.getAllRoutes();
      route = route.find(r => r.city === city && r.pickup_address === pickup && r.dropoff_address === dropoff);

      if (!route) {
        route = await queries.createRoute(city, pickup, dropoff);
        logger.info(`Created route: ${city} - ${pickup} to ${dropoff}`, { routeId: route.id });
      }

      // Enter addresses
      await this.client.enterAddresses(pickup, dropoff);
      await this.randomDelay();

      // Extract prices
      const pricesData = await this.client.extractPrices();
      if (!pricesData || Object.keys(pricesData).length === 0) {
        throw new PriceExtractionError('No prices found on page');
      }

      // Parse and insert prices
      for (const key in pricesData) {
        const cardText = pricesData[key];
        const parsed = Parser.parsePriceCard(cardText);

        if (parsed && parsed.price && parsed.vehicleType) {
          await queries.insertPrice(
            route.id,
            parsed.vehicleType,
            parsed.price,
            account.email
          );
          logger.debug(`Inserted price: ${parsed.vehicleType} - $${parsed.price}`, { routeId: route.id });
        }
      }

      // Record success
      await this.accountManager.recordSuccess(account.email);
      logger.info(`Scraped successfully: ${city} - ${pickup} to ${dropoff}`, { account: account.email });

      return { success: true, route };
    } catch (err) {
      await this.accountManager.recordFailure(account.email);
      logger.error(`Failed to scrape route: ${city} - ${pickup} to ${dropoff}`, err);
      return { success: false, error: err.message };
    }
  }

  async run() {
    const startTime = Date.now();

    try {
      this.client = new PlaywrightClient();
      await this.client.launch();

      const account = await this.accountManager.getNextAccount();
      if (!account) {
        throw new Error('No active accounts available');
      }

      await this.client.login(account.email, account.password);
      logger.info(`Logged in with account: ${account.email}`);

      // Scrape London routes
      logger.info('Starting London routes scraping');
      for (const route of ROUTES_LONDON) {
        await this.scrapeRoute(account, 'London', route.pickup, route.dropoff);
        await this.randomDelay();
      }

      // Scrape Antalya routes
      logger.info('Starting Antalya routes scraping');
      for (const route of ROUTES_ANTALYA) {
        await this.scrapeRoute(account, 'Antalya', route.pickup, route.dropoff);
        await this.randomDelay();
      }

      await this.client.close();

      const duration = Date.now() - startTime;
      logger.info('Scraping cycle completed', { durationMs: duration });

      return {
        success: true,
        duration
      };
    } catch (err) {
      logger.error('Scraping cycle failed', err);

      if (this.client) {
        await this.client.close().catch(e => logger.error('Error closing client', e));
      }

      const duration = Date.now() - startTime;
      return {
        success: false,
        duration,
        error: err.message
      };
    }
  }
}

module.exports = Scraper;
