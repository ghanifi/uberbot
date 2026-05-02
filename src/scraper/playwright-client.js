const { chromium } = require('playwright');

class PlaywrightClient {
  constructor() {
    this.browser = null;
    this.context = null;
    this.page = null;
  }

  async launch() {
    this.browser = await chromium.launch({ headless: true });
    this.context = await this.browser.newContext({
      userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Mobile/15E148 Safari/604.1'
    });
    this.page = await this.context.newPage();
  }

  async login(email, password) {
    await this.page.goto('https://www.uber.com', { waitUntil: 'networkidle' });

    const emailInput = await this.page.$('input[type="email"]');
    if (emailInput) {
      await emailInput.fill(email);
      await this.page.press('input[type="email"]', 'Enter');
    }

    await this.page.waitForTimeout(1000);

    const passwordInput = await this.page.$('input[type="password"]');
    if (passwordInput) {
      await passwordInput.fill(password);
      await this.page.press('input[type="password"]', 'Enter');
    }

    await this.page.waitForNavigation({ waitUntil: 'networkidle' }).catch(() => {});
  }

  async enterAddresses(pickup, dropoff) {
    const pickupInput = await this.page.$('input[placeholder*="pickup"], input[placeholder*="Pickup"], input[placeholder*="Where to?"]');
    if (pickupInput) {
      await pickupInput.fill(pickup);
      await this.page.press('input[placeholder*="pickup"], input[placeholder*="Pickup"], input[placeholder*="Where to?"]', 'ArrowDown');
      await this.page.press('input[placeholder*="pickup"], input[placeholder*="Pickup"], input[placeholder*="Where to?"]', 'Enter');
    }

    await this.page.waitForTimeout(1000);

    const dropoffInput = await this.page.$('input[placeholder*="dropoff"], input[placeholder*="destination"], input[placeholder*="Where to"]');
    if (dropoffInput) {
      await dropoffInput.fill(dropoff);
      await this.page.press('input[placeholder*="dropoff"], input[placeholder*="destination"], input[placeholder*="Where to"]', 'ArrowDown');
      await this.page.press('input[placeholder*="dropoff"], input[placeholder*="destination"], input[placeholder*="Where to"]', 'Enter');
    }

    await this.page.waitForTimeout(2000);
  }

  async extractPrices() {
    const priceElements = await this.page.$$('[data-testid*="price"]');
    const prices = {};

    for (const element of priceElements) {
      const text = await element.textContent();
      if (text && text.trim()) {
        prices[`price_${Object.keys(prices).length}`] = text.trim();
      }
    }

    return prices;
  }

  async close() {
    if (this.context) await this.context.close();
    if (this.browser) await this.browser.close();
  }

  async screenshot(filepath) {
    if (this.page) {
      await this.page.screenshot({ path: filepath });
    }
  }
}

module.exports = PlaywrightClient;
