# Yeni Dil & Para Birimi Sistemi Mimarisi

## 1. DATABASE SCHEMA

### wp_language_domains Table
```sql
CREATE TABLE wp_language_domains (
  id INT AUTO_INCREMENT PRIMARY KEY,
  language_code VARCHAR(10) NOT NULL UNIQUE,  -- tr_TR, de_DE, en_US
  language_name VARCHAR(50),                   -- Türkçe, Deutsch, English
  domain_url VARCHAR(255),                     -- havalimanitransfer.com
  flag CHAR(2),                                -- TR, DE, EN (opsiyonel)
  is_active TINYINT DEFAULT 1,
  display_order INT DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### wp_booking_analytics - Currency Ekleme
```sql
ALTER TABLE wp_booking_analytics ADD COLUMN (
  session_currency VARCHAR(3) DEFAULT 'GBP',
  INDEX idx_session_currency (session_currency)
);
```

## 2. ADMIN INTERFACE

### Location
- WordPress Admin → Settings → Language Domains
- CRUD interface

### Fields
- Language Code (tr_TR)
- Language Name (Türkçe)
- Domain URL (havalimanitransfer.com)
- Flag (TR)
- Status (Active/Inactive)
- Display Order

## 3. LANGUAGE SELECTOR FLOW

```
User clicks language dropdown
  ↓
Language option selected
  ↓
JavaScript POST: /wp-admin/admin-ajax.php
  action: 'get_language_domain'
  language: 'tr_TR'
  ↓
Backend:
  1. wp_language_domains table'dan domain bul
  2. domain_url varsa return, yoksa null
  ↓
JavaScript:
  if (domain_url) {
    window.location.href = 'https://' + domain_url + '/';
  }
```

## 4. CURRENCY SYSTEM FLOW

```
User selects currency
  ↓
Query String: ?currency=EUR
  (or ?currency=EUR&lang=tr_TR for combined)
  ↓
Backend:
  1. $_GET['currency'] kontrol et
  2. Valid mi? (GBP, EUR, TRY, USD)
  3. $_SESSION['airlinel_currency'] = 'EUR'
  ↓
Booking/API Requests:
  1. Session'dan currency al
  2. API'ye ekle: ?currency=EUR
  3. Prices EUR'da hesapla
  ↓
Analytics:
  1. Booking'i currency ile kaydet
```

## 5. FILE CHANGES

### New Files
- `admin/pages/language-domains.php` - Admin interface
- `database/migrations/006-add-language-domains.php` - Migration
- `includes/class-language-domains.php` - Language domains helper class
- `includes/class-currency-session.php` - Session currency manager

### Modified Files
- `functions.php` - Add AJAX handlers
- `header-selectors.js` - Change from redirect() to domain lookup
- `booking.js` - Use session currency instead of localStorage
- `page-booking.php` - Add currency hidden field from session

## 6. IMPLEMENTATION SEQUENCE

1. Create database migration
2. Create Language Domains admin interface
3. Create helper classes (Language Domains, Currency Session)
4. Update functions.php with AJAX handlers
5. Update JavaScript (header-selectors.js)
6. Update booking system (page-booking.php, booking.js)
7. Update API to handle currency parameter
8. Update analytics to log currency

## 7. SESSION MANAGEMENT

```php
// Initialize session if needed
if (!session_id()) {
    session_start();
}

// Set default currency
if (!isset($_SESSION['airlinel_currency'])) {
    $_SESSION['airlinel_currency'] = 'GBP';
}

// Check for currency parameter and update session
if (isset($_GET['currency'])) {
    $currency = sanitize_text_field($_GET['currency']);
    $valid_currencies = ['GBP', 'EUR', 'TRY', 'USD'];
    if (in_array($currency, $valid_currencies)) {
        $_SESSION['airlinel_currency'] = $currency;
    }
}
```

## 8. API INTEGRATION

```php
// In API requests, include currency
$booking_data = [
    'pickup' => $pickup,
    'dropoff' => $dropoff,
    'currency' => $_SESSION['airlinel_currency'],  // Add currency
    // ... other data
];
```

## 9. ANALYTICS

```php
// When saving booking
$booking_data = [
    'customer_email' => $email,
    'currency' => $_SESSION['airlinel_currency'],
    'amount' => $total_price,
    // ... other data
];
```

## 10. URL EXAMPLES

### Main Site (English)
- URL: airlinel.com
- Language: en_US
- Currency: GBP
- Booking: airlinel.com/book-your-ride?currency=GBP

### Turkish Site
- URL: havalimanitransfer.com
- Language: tr_TR
- Currency: TRY
- Booking: havalimanitransfer.com/book-your-ride?currency=TRY

### German Site
- URL: flughafentransfer.com
- Language: de_DE
- Currency: EUR
- Booking: flughafentransfer.com/book-your-ride?currency=EUR

