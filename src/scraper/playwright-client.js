const { chromium } = require('playwright');

class PlaywrightClient {
  constructor() {
    this.browser = null;
    this.context = null;
    this.page = null;
  }

  async launch() {
    const executablePath = '/usr/bin/chromium-browser';
    
    this.browser = await chromium.launch({
      headless: true,
      executablePath: executablePath,
      args: ['--no-sandbox', '--disable-setuid-sandbox']
    });

    this.context = await this.browser.newContext({
      userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15'
    });
    this.page = await this.context.newPage();
  }

  async login(email, password) {
    try {
      await this.page.goto('https://www.uber.com', { waitUntil: 'networkidle', timeout: 30000 });

      const emailInput = await this.page.$('input[type="email"]');
      if (emailInput) {
        await emailInput.fill(email);
        await this.page.press('input[type="email"]', 'Enter');
      }

      await this.page.waitForTimeout(2000);

      const passwordInput = await this.page.$('input[type="password"]');
      if (passwordInput) {
        await passwordInput.fill(password);
        await this.page.press('input[type="password"]', 'Enter');
      }

      await this.page.waitForNavigation({ waitUntil: 'networkidle', timeout: 30000 }).catch(() => {});
    } catch (err) {
      throw new Error(`Login failed: ${err.message}`);
    }
  }

  async enterAddresses(pickup, dropoff) {
    try {
      const pickupInput = await this.page.$('input[placeholder*="pickup"], input[placeholder*="Pickup"], input[placeholder*="Where"]');
      if (pickupInput) {
        await pickupInput.fill(pickup);
        await this.page.waitForTimeout(500);
        await this.page.press('input', 'ArrowDown');
        await this.page.press('input', 'Enter');
      }

      await this.page.waitForTimeout(1500);

      const dropoffInput = await this.page.$('input[placeholder*="dropoff"], input[placeholder*="destination"], input[placeholder*="Where"]');
      if (dropoffInput) {
        await dropoffInput.fill(dropoff);
        await this.page.waitForTimeout(500);
        await this.page.press('input', 'ArrowDown');
        await this.page.press('input', 'Enter');
      }

      await this.page.waitForTimeout(3000);
    } catch (err) {
      throw new Error(`Address entry failed: ${err.message}`);
    }
  }

  async extractPrices() {
    try {
      const priceElements = await this.page.$$('[data-testid*="price"], .price, [class*="price"]');
      const prices = {};

      for (const element of priceElements) {
        const text = await element.textContent();
        if (text && text.includes('$')) {
          const type = await element.getAttribute('data-testid') || 'UberX';
          prices[type] = text.trim();
        }
      }

      return prices.length > 0 ? prices : { UberX: null };
    } catch (err) {
      throw new Error(`Price extraction failed: ${err.message}`);
    }
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
