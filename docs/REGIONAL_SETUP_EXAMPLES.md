# Regional Site Setup Examples

This document provides concrete examples for setting up three regional sites.

## Example 1: Antalya Regional Site (Turkey)

### Domain
`antalya.airlinel.com`

### Configuration

Create `/var/www/html/antalya/wp-config.php`:

```php
<?php
// Database settings
define('DB_NAME', 'antalya_airlinel');
define('DB_USER', 'wordpress');
define('DB_PASSWORD', 'secure_password_123');
define('DB_HOST', 'localhost');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');

// Security salts (generate at https://api.wordpress.org/secret-key/1.1/salt/)
define('AUTH_KEY',         'put your unique phrase here');
define('SECURE_AUTH_KEY',  'put your unique phrase here');
define('LOGGED_IN_KEY',    'put your unique phrase here');
define('NONCE_KEY',        'put your unique phrase here');
define('AUTH_SALT',        'put your unique phrase here');
define('SECURE_AUTH_SALT', 'put your unique phrase here');
define('LOGGED_IN_SALT',   'put your unique phrase here');
define('NONCE_SALT',       'put your unique phrase here');

$table_prefix = 'wp_';

define('WP_DEBUG', false);
define('WP_DEBUG_LOG', false);

// =========================
// AIRLINEL REGIONAL CONFIG
// =========================

// Main site URL (required)
define('AIRLINEL_MAIN_SITE_URL', 'https://airlinel.com');

// Regional site API key (from main site admin panel)
define('AIRLINEL_MAIN_SITE_API_KEY', 'antalya_key_abc123xyz456');

// Site language (required)
define('WPLANG', 'tr_TR');

// Regional site ID
define('AIRLINEL_SITE_ID', 'antalya');

if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

require_once ABSPATH . 'wp-settings.php';
?>
```

### Setup Steps

```bash
# 1. Create database
mysql -u root -p
mysql> CREATE DATABASE antalya_airlinel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
mysql> GRANT ALL PRIVILEGES ON antalya_airlinel.* TO 'wordpress'@'localhost' IDENTIFIED BY 'secure_password_123';
mysql> FLUSH PRIVILEGES;
mysql> EXIT;

# 2. Download and set up WordPress
cd /var/www/html/antalya
wget https://wordpress.org/latest.tar.gz
tar -xzf latest.tar.gz
mv wordpress/* .
rmdir wordpress
rm latest.tar.gz

# 3. Copy theme from main site
cp -r /var/www/html/wp-content/themes/airlinel-transfer-services \
    /var/www/html/antalya/wp-content/themes/

# 4. Set permissions
chmod -R 755 wp-content/themes/
chmod -R 755 wp-content/plugins/
chmod 644 wp-config.php
chown -R www-data:www-data wp-content/

# 5. Visit https://antalya.airlinel.com/wp-admin/install.php and complete installation
```

### Admin Panel Configuration

After WordPress installation:

1. **Settings → General**
   - Site Title: "Antalya Airport Transfer"
   - Tagline: "Premium Transfer Service"
   - Site URL: https://antalya.airlinel.com
   - Language: Türkçe (auto-set from WPLANG)

2. **Appearance → Themes**
   - Activate "Airlinel Transfer Services"

3. **Settings → Regional Site Settings**
   - Verify Site ID: "antalya"
   - Verify Language: "Turkish"
   - Verify Main Site URL: "https://airlinel.com"
   - Click "Test Connection" button
   - Should show "✓ Connected"

### Expected Result

- Admin interface in Turkish (Türkçe)
- Prices in Turkish Lira (if configured)
- Regional Settings page shows correct Turkish language
- Booking form loads vehicles from main site API

---

## Example 2: Istanbul Regional Site (Turkey)

### Domain
`istanbul.airlinel.com`

### Configuration

Create `/var/www/html/istanbul/wp-config.php`:

```php
<?php
// Database settings
define('DB_NAME', 'istanbul_airlinel');
define('DB_USER', 'wordpress');
define('DB_PASSWORD', 'secure_password_456');
define('DB_HOST', 'localhost');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');

// Security salts (generate at https://api.wordpress.org/secret-key/1.1/salt/)
define('AUTH_KEY',         'put your unique phrase here');
define('SECURE_AUTH_KEY',  'put your unique phrase here');
define('LOGGED_IN_KEY',    'put your unique phrase here');
define('NONCE_KEY',        'put your unique phrase here');
define('AUTH_SALT',        'put your unique phrase here');
define('SECURE_AUTH_SALT', 'put your unique phrase here');
define('LOGGED_IN_SALT',   'put your unique phrase here');
define('NONCE_SALT',       'put your unique phrase here');

$table_prefix = 'wp_';

define('WP_DEBUG', false);
define('WP_DEBUG_LOG', false);

// =========================
// AIRLINEL REGIONAL CONFIG
// =========================

// Main site URL (required)
define('AIRLINEL_MAIN_SITE_URL', 'https://airlinel.com');

// Regional site API key (from main site admin panel)
define('AIRLINEL_MAIN_SITE_API_KEY', 'istanbul_key_def789uvw123');

// Site language (required)
define('WPLANG', 'tr_TR');

// Regional site ID
define('AIRLINEL_SITE_ID', 'istanbul');

if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

require_once ABSPATH . 'wp-settings.php';
?>
```

### Setup Steps

Same as Antalya but with `istanbul` in place of `antalya`:

```bash
# Database name: istanbul_airlinel
# Site directory: /var/www/html/istanbul
# Site ID: istanbul
# Domain: istanbul.airlinel.com
```

### Admin Panel Configuration

Same as Antalya example.

### Expected Result

- Admin interface in Turkish (same as Antalya)
- Independent database with separate content
- Can have different page content from Antalya regional site
- Shares vehicle data and pricing from main site

---

## Example 3: London Regional Site (England)

### Domain
`london.airlinel.com`

### Configuration

Create `/var/www/html/london/wp-config.php`:

```php
<?php
// Database settings
define('DB_NAME', 'london_airlinel');
define('DB_USER', 'wordpress');
define('DB_PASSWORD', 'secure_password_789');
define('DB_HOST', 'localhost');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');

// Security salts (generate at https://api.wordpress.org/secret-key/1.1/salt/)
define('AUTH_KEY',         'put your unique phrase here');
define('SECURE_AUTH_KEY',  'put your unique phrase here');
define('LOGGED_IN_KEY',    'put your unique phrase here');
define('NONCE_KEY',        'put your unique phrase here');
define('AUTH_SALT',        'put your unique phrase here');
define('SECURE_AUTH_SALT', 'put your unique phrase here');
define('LOGGED_IN_SALT',   'put your unique phrase here');
define('NONCE_SALT',       'put your unique phrase here');

$table_prefix = 'wp_';

define('WP_DEBUG', false);
define('WP_DEBUG_LOG', false);

// =========================
// AIRLINEL REGIONAL CONFIG
// =========================

// Main site URL (required)
define('AIRLINEL_MAIN_SITE_URL', 'https://airlinel.com');

// Regional site API key (from main site admin panel)
define('AIRLINEL_MAIN_SITE_API_KEY', 'london_key_xyz123abc456');

// Site language (required - English)
define('WPLANG', 'en_US');

// Regional site ID
define('AIRLINEL_SITE_ID', 'london');

if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

require_once ABSPATH . 'wp-settings.php';
?>
```

### Setup Steps

```bash
# 1. Create database
mysql -u root -p
mysql> CREATE DATABASE london_airlinel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
mysql> GRANT ALL PRIVILEGES ON london_airlinel.* TO 'wordpress'@'localhost' IDENTIFIED BY 'secure_password_789';
mysql> FLUSH PRIVILEGES;
mysql> EXIT;

# 2. Download and set up WordPress
cd /var/www/html/london
wget https://wordpress.org/latest.tar.gz
tar -xzf latest.tar.gz
mv wordpress/* .
rmdir wordpress
rm latest.tar.gz

# 3. Copy theme from main site
cp -r /var/www/html/wp-content/themes/airlinel-transfer-services \
    /var/www/html/london/wp-content/themes/

# 4. Set permissions
chmod -R 755 wp-content/themes/
chmod -R 755 wp-content/plugins/
chmod 644 wp-config.php
chown -R www-data:www-data wp-content/
```

### Admin Panel Configuration

After WordPress installation:

1. **Settings → General**
   - Site Title: "London Airport Transfer"
   - Tagline: "Premium London Airport Transfer Service"
   - Site URL: https://london.airlinel.com
   - Language: English (United States) (auto-set from WPLANG)

2. **Appearance → Themes**
   - Activate "Airlinel Transfer Services"

3. **Settings → Regional Site Settings**
   - Verify Site ID: "london"
   - Verify Language: "English (United States)"
   - Verify Main Site URL: "https://airlinel.com"
   - Click "Test Connection" button
   - Should show "✓ Connected"

### Expected Result

- Admin interface in English
- Prices in British Pounds (GBP)
- Regional Settings page shows English language
- Booking form loads vehicles from main site API
- All content can be customized for London market

---

## Using the Automated Setup Script

Instead of manual setup, you can use the automated script:

### Antalya Setup

```bash
./bin/setup-regional-site.sh \
  --name=antalya \
  --lang=tr_TR \
  --api-key=antalya_key_abc123xyz456 \
  --domain=antalya.airlinel.com \
  --db-name=antalya_airlinel \
  --db-user=wordpress \
  --db-pass=secure_password_123
```

### Istanbul Setup

```bash
./bin/setup-regional-site.sh \
  --name=istanbul \
  --lang=tr_TR \
  --api-key=istanbul_key_def789uvw123 \
  --domain=istanbul.airlinel.com \
  --db-name=istanbul_airlinel \
  --db-user=wordpress \
  --db-pass=secure_password_456
```

### London Setup

```bash
./bin/setup-regional-site.sh \
  --name=london \
  --lang=en_US \
  --api-key=london_key_xyz123abc456 \
  --domain=london.airlinel.com \
  --db-name=london_airlinel \
  --db-user=wordpress \
  --db-pass=secure_password_789
```

---

## DNS Configuration for All Three Sites

In your DNS provider (e.g., GoDaddy, Cloudflare, Route53):

```
Type  | Name     | Value
------|----------|-------------------
CNAME | antalya  | airlinel.com
CNAME | istanbul | airlinel.com
CNAME | london   | airlinel.com
```

Or if using A records:

```
Type | Name     | Value
-----|----------|--------------------
A    | antalya  | YOUR_SERVER_IP
A    | istanbul | YOUR_SERVER_IP
A    | london   | YOUR_SERVER_IP
```

---

## SSL Certificates

For each regional site:

```bash
# Using Let's Encrypt
certbot certonly -d antalya.airlinel.com
certbot certonly -d istanbul.airlinel.com
certbot certonly -d london.airlinel.com

# Auto-renewal
certbot renew
```

Or use wildcard certificate:

```bash
certbot certonly -d "*.airlinel.com"
```

---

## Verification Checklist

For each regional site, verify:

- [ ] WordPress installation complete
- [ ] Theme activated
- [ ] Regional Settings page accessible
- [ ] Connection test shows "Connected"
- [ ] Correct language displayed in admin
- [ ] Booking form loads vehicles
- [ ] SSL certificate valid

---

## Getting API Keys from Main Site

On the main site (airlinel.com):

1. Log into admin panel
2. Go to **Settings → Airlinel Settings**
3. Scroll to **Regional Site API Keys** section
4. Generate or copy API key for each regional site
5. Paste into that regional site's wp-config.php

---

## Troubleshooting Multiple Sites

### All sites showing same content
- Verify each regional site has separate database (DB_NAME in wp-config.php)
- Verify each has separate wp-content directory

### API connection fails
- Verify AIRLINEL_MAIN_SITE_API_KEY is correct for each site
- Check that main site is accessible: `curl https://airlinel.com`
- Check regional site error log: `wp-content/debug.log`

### Language not changing
- Verify WPLANG is set correctly in wp-config.php
- Clear browser cache
- Check that language files exist in theme/languages/

### DNS not resolving
- Wait for DNS propagation (up to 48 hours)
- Check: `nslookup antalya.airlinel.com`
- Verify CNAME records are set correctly

---

**Version:** 1.0  
**Last Updated:** 2026-04-25
