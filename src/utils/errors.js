class ScraperError extends Error {
  constructor(message) {
    super(message);
    this.name = 'ScraperError';
  }
}

class LoginError extends ScraperError {
  constructor(message) {
    super(message);
    this.name = 'LoginError';
  }
}

class AddressError extends ScraperError {
  constructor(message) {
    super(message);
    this.name = 'AddressError';
  }
}

class PriceExtractionError extends ScraperError {
  constructor(message) {
    super(message);
    this.name = 'PriceExtractionError';
  }
}

module.exports = {
  ScraperError,
  LoginError,
  AddressError,
  PriceExtractionError
};
