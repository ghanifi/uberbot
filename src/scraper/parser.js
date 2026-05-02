class Parser {
  static parsePrice(priceString) {
    if (!priceString || typeof priceString !== 'string') return null;

    // Remove currency symbols and whitespace
    const cleaned = priceString.replace(/[$£€,\s]/g, '');
    const parsed = parseFloat(cleaned);

    return isNaN(parsed) ? null : parsed;
  }

  static extractVehicleType(text) {
    if (!text || typeof text !== 'string') return null;

    const upperText = text.toUpperCase();

    if (upperText.includes('UBERX') && upperText.includes('XL')) {
      return 'UberXL';
    } else if (upperText.includes('UBERX')) {
      return 'UberX';
    } else if (upperText.includes('XL')) {
      return 'UberXL';
    } else if (upperText.includes('EXEC')) {
      return 'Exec';
    } else if (upperText.includes('BLACK')) {
      return 'Black';
    }

    return null;
  }

  static parsePriceCard(cardText) {
    if (!cardText || typeof cardText !== 'string') return null;

    const lines = cardText.split('\n').filter(line => line.trim());
    if (lines.length === 0) return null;

    let vehicleType = null;
    let price = null;

    for (const line of lines) {
      const extracted = this.extractVehicleType(line);
      if (extracted && !vehicleType) {
        vehicleType = extracted;
      }

      const parsedPrice = this.parsePrice(line);
      if (parsedPrice !== null && !price) {
        price = parsedPrice;
      }
    }

    if (vehicleType && price !== null) {
      return { vehicleType, price };
    }

    return null;
  }
}

module.exports = Parser;
