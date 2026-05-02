module.exports = {
  VEHICLE_TYPES: ['UberX', 'UberXL', 'Exec'],

  SELECTORS: {
    emailInput: 'input[type="email"]',
    passwordInput: 'input[type="password"]',
    submitButton: 'button[type="submit"]',
    priceDisplay: '[data-testid="price-display"]',
  },

  ROUTES_LONDON: [
    { pickup: 'Heathrow Airport', dropoff: 'Central London' },
    { pickup: 'Gatwick Airport', dropoff: 'Canary Wharf' },
    { pickup: 'Stansted Airport', dropoff: 'West End' },
  ],

  ROUTES_ANTALYA: [
    { pickup: 'Antalya Airport', dropoff: 'Lara Beach' },
    { pickup: 'Antalya Airport', dropoff: 'Old Town' },
  ],

  SCRAPE_DELAY_MIN: 2000,
  SCRAPE_DELAY_MAX: 5000,

  MAX_LOGIN_RETRIES: 3,
  FAILED_QUERY_THRESHOLD: 5,
};
