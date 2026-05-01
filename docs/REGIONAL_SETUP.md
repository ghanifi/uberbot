# Regional Site Setup & Deployment Guide

This guide provides step-by-step instructions for setting up a new regional site for the Airlinel airport transfer platform.

## Overview

Regional sites are separate WordPress installations that use the same theme as the main site (airlinel.com) but are configured to call the main site's API via a regional proxy to fetch prices, create reservations, and manage bookings. Each regional site can be customized by language, currency, and local market preferences.

## Architecture

```
┌─────────────────────────────────────────┐
│  Regional Site (e.g., antalya.airlinel.com)  │
│  - WordPress 6.x                          │
│  - Airlinel Theme                         │
│  - Language Settings Plugin               │
│  - Regional Site Proxy                    │
│  - Admin: Regional Settings Page          │
└──────────────┬──────────────────────────┘
               │
               │ API Calls (via Proxy)
               │ /wp-json/airlinel/v1/search
               │ /wp-json/airlinel/v1/reservation/create
               │
┌──────────────v──────────────────────────┐
│  Main Site (airlinel.com)                 │
│  - Source of truth for:                   │
│    - Pricing zones & rates                │
│    - Fleet management                     │
│    - All reservations                     │
│    - Exchange rates                       │
└──────────────────────────────────────────┘
```

## Prerequisites

Before setting up a regional site, ensure you have:

1. **Main Site Ready**: airlinel.com running with all Phase 1-3.0 features
2. **API Key Generated**: Obtain the regional API key from main site admin:
   - Navigate to: Settings → Airlinel Settings → Regional Settings
   - Generate/copy the API key for the regional site
3. **Domain/Subdomain**: DNS configured (e.g., antalya.airlinel.com, istanbul.airlinel.com)
4. **Database**: Empty MySQL database for the regional site
5. **Web Server**: PHP 8.x, WordPress-compatible hosting
6. **Access**: FTP/SSH access to web server

## Step-by-Step Setup

### Step 1: Download WordPress

Download the latest WordPress core files to your regional site directory:

```bash
# SSH into your hosting
ssh user@your-server.com

# Navigate to web root
cd /var/www/html/antalya

# Download WordPress
wget https://wordpress.org/latest.tar.gz
tar -xzf latest.tar.gz
mv wordpress/* .
rm -rf wordpress latest.tar.gz
```

### Step 2: Create wp-config.php

Create a `wp-config.php` file with the following configuration:

```php
<?php
/**
 * The base configuration for WordPress (Regional Site)
 */

// Database settings (customize for your database)
define('DB_NAME', 'antalya_airlinel');
define('DB_USER', 'db_user');
define('DB_PASSWORD', 'db_password');
define('DB_HOST', 'localhost');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');

// Security salts (generate at https://api.wordpress.org/secret-key/1.1/salt/)
define('AUTH_KEY',         'generate-from-wordpress-api');
define('SECURE_AUTH_KEY',  'generate-from-wordpress-api');
define('LOGGED_IN_KEY',    'generate-from-wordpress-api');
define('NONCE_KEY',        'generate-from-wordpress-api');
define('AUTH_SALT',        'generate-from-wordpress-api');
define('SECURE_AUTH_SALT', 'generate-from-wordpress-api');
define('LOGGED_IN_SALT',   'generate-from-wordpress-api');
define('NONCE_SALT',       'generate-from-wordpress-api');

// Database table prefix
$table_prefix = 'wp_';

// WordPress debugging (disable in production)
define('WP_DEBUG', false);
define('WP_DEBUG_LOG', false);

// =========================
// AIRLINEL REGIONAL CONFIG
// =========================

// Main site URL (required)
define('AIRLINEL_MAIN_SITE_URL', 'https://airlinel.com');

// Regional site API key (get from main site admin panel)
define('AIRLINEL_MAIN_SITE_API_KEY', 'YOUR_REGIONAL_API_KEY_HERE');

// Site language (required)
// Supported: en_US, tr_TR, de_DE, ru_RU, fr_FR, it_IT, ar, da_DK, nl_NL, sv_SE, zh_CN, ja
define('WPLANG', 'tr_TR');

// Regional site ID (used for analytics and tracking)
// This should be unique for each regional site
// Optional - defaults to subdomain if not set
define('AIRLINEL_SITE_ID', 'antalya');

// =========================
// ABSOLUTE PATH SETTINGS
// =========================

if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

require_once ABSPATH . 'wp-settings.php';
?>
```

**Important Configuration Values:**

| Setting | Example | Description |
|---------|---------|-------------|
| `DB_NAME` | `antalya_airlinel` | Regional site database name |
| `AIRLINEL_MAIN_SITE_URL` | `https://airlinel.com` | Main site URL (no trailing slash) |
| `AIRLINEL_MAIN_SITE_API_KEY` | `<key>` | API key from main site (see Prerequisites) |
| `WPLANG` | `tr_TR` | Language locale for regional site |

### Step 3: Copy the Airlinel Theme

Copy the theme from the main site to the regional site:

```bash
# Method 1: Via SSH
scp -r /path/to/main/wp-content/themes/airlinel-transfer-services \
    user@your-server.com:/var/www/html/antalya/wp-content/themes/

# Method 2: Via FTP
# Download from: wp-content/themes/airlinel-transfer-services
# Upload to regional site: wp-content/themes/airlinel-transfer-services

# Verify permissions
chmod -R 755 wp-content/themes/airlinel-transfer-services
chown -R www-data:www-data wp-content/themes/airlinel-transfer-services
```

### Step 4: Activate Language Settings Plugin

The Airlinel theme includes a language settings plugin that automatically configures WordPress language settings based on `WPLANG` in wp-config.php.

To verify it's working:

1. Install/activate the plugin:
   ```bash
   # The plugin is auto-loaded if present
   cp -r path/to/plugin/airlinel-language-settings \
       /var/www/html/antalya/wp-content/plugins/
   ```

2. Check WordPress admin:
   - Go to: Settings → General
   - Verify "Site Language" is set correctly (e.g., "Türkçe" for tr_TR)

### Step 5: Complete WordPress Installation

Visit your regional site URL in a browser to complete the WordPress installation:

```
https://antalya.airlinel.com/wp-admin/install.php
```

Fill in:
- Site Title: "Antalya Airport Transfer"
- Username: Choose a secure username
- Password: Choose a strong password
- Email: Admin email address
- Search Engine Visibility: Discourage search engines (optional)

### Step 6: Verify Regional Settings

After installation, log into the admin panel and verify the regional configuration:

1. Navigate to: **Settings → Regional Site Settings**
2. Verify the following information displays correctly:
   - ✅ **Site ID**: Should show "antalya"
   - ✅ **Language**: Should show "Turkish" (or configured language)
   - ✅ **Main Site URL**: Should show "https://airlinel.com"
   - ✅ **Connection Status**: Should be **GREEN** (Connected)
3. Click **"Test Connection"** button to verify the regional site can reach the main site API
4. Review and save any additional settings as needed

### Step 7: Activate the Airlinel Theme

1. Go to **Appearance → Themes**
2. Find "Airlinel Transfer Services" theme
3. Click **"Activate"** button
4. Verify the theme activates without errors

### Step 8: Configure Initial Content

Create the essential pages and posts:

```bash
# Via WP-CLI (if available)
wp post create --post_type=page --post_title="Home" --post_name="home" \
  --post_content="Welcome to Antalya Airport Transfer" --post_status=publish

wp post create --post_type=page --post_title="About" --post_name="about" \
  --post_content="About Antalya Airport Transfer Service" --post_status=publish

wp post create --post_type=page --post_title="Services" --post_name="services" \
  --post_content="Our Services" --post_status=publish

wp post create --post_type=page --post_title="Contact" --post_name="contact" \
  --post_content="Contact Us" --post_status=publish
```

Or manually via WordPress admin:
1. Go to **Pages → Add New**
2. Create pages: Home, About, Services, Contact, Booking
3. Set Home page as front page: **Settings → Reading → Front page**

### Step 9: Configure Basic Settings

In WordPress admin, configure:

1. **Settings → General**
   - Site Title: "Antalya Airport Transfer"
   - Tagline: "Premium Transfer Service"
   - Site URL: https://antalya.airlinel.com
   - Home URL: https://antalya.airlinel.com
   - Timezone: UTC or your local timezone
   - Language: Auto-set from WPLANG (e.g., Türkçe)

2. **Settings → Reading**
   - Front page displays: Static page
   - Front page: Select "Home" page
   - Posts page: Select "Blog" or similar

3. **Settings → Permalinks**
   - Permalink structure: Post name (for clean URLs)
   - Common settings: `/%postname%/`

### Step 10: Test Functionality

Perform these tests to ensure the regional site works correctly:

#### Test 1: Theme Loads
- [ ] Visit homepage (https://antalya.airlinel.com)
- [ ] Verify page loads without errors
- [ ] Check console for JavaScript errors (F12)

#### Test 2: Regional Settings Page Works
- [ ] Log into admin
- [ ] Go to **Settings → Regional Site Settings**
- [ ] Verify all status indicators display
- [ ] Click "Test Connection" button
- [ ] Verify response shows "Connected to main site"

#### Test 3: Booking Form Works
- [ ] Visit booking page
- [ ] Enter test pickup/dropoff locations
- [ ] Click search
- [ ] Verify vehicles load from main site API
- [ ] Check that prices display

#### Test 4: Language Settings Work
- [ ] Go to **Settings → Regional Site Settings**
- [ ] Verify language dropdown shows current language
- [ ] (Optional) Change language and save
- [ ] Verify admin interface changes language

#### Test 5: API Connectivity
- [ ] Use browser console to test API:
  ```javascript
  fetch('/wp-json/airlinel/v1/search', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({
      pickup: 'Airport',
      dropoff: 'City Center',
      country: 'UK',
      passengers: 1,
      currency: 'GBP'
    })
  })
  .then(r => r.json())
  .then(data => console.log(data))
  .catch(e => console.error(e))
  ```
- [ ] Verify response contains vehicle data

## Configuration Examples

### Example 1: Turkish Regional Site (Antalya)

**File: wp-config.php**
```php
define('AIRLINEL_MAIN_SITE_URL', 'https://airlinel.com');
define('AIRLINEL_MAIN_SITE_API_KEY', 'antalya_regional_key_xyz123');
define('WPLANG', 'tr_TR');
define('AIRLINEL_SITE_ID', 'antalya');
```

**Expected:**
- Admin interface in Turkish
- Prices in TRY (if configured)
- Regional settings show Turkish language

### Example 2: German Regional Site (Berlin)

**File: wp-config.php**
```php
define('AIRLINEL_MAIN_SITE_URL', 'https://airlinel.com');
define('AIRLINEL_MAIN_SITE_API_KEY', 'berlin_regional_key_abc456');
define('WPLANG', 'de_DE');
define('AIRLINEL_SITE_ID', 'berlin');
```

**Expected:**
- Admin interface in German
- Content translated to German
- Regional settings show German language

### Example 3: English Regional Site (London)

**File: wp-config.php**
```php
define('AIRLINEL_MAIN_SITE_URL', 'https://airlinel.com');
define('AIRLINEL_MAIN_SITE_API_KEY', 'london_regional_key_def789');
define('WPLANG', 'en_US');
define('AIRLINEL_SITE_ID', 'london');
```

**Expected:**
- Admin interface in English
- Prices in GBP (if configured)
- Regional settings show English language

## Troubleshooting

### Issue: "Connection to main site failed"

**Cause**: API key is incorrect or network connectivity issue

**Solution**:
1. Verify `AIRLINEL_MAIN_SITE_API_KEY` in wp-config.php
2. Get correct key from: Main Site Admin → Settings → Regional Settings
3. Check that main site is accessible: `curl https://airlinel.com`
4. Verify firewall allows outbound HTTPS to main site

### Issue: Language not changing in admin

**Cause**: Language files not found or WPLANG not set correctly

**Solution**:
1. Verify WPLANG is set correctly in wp-config.php
2. Check that language files exist in wp-content/languages/
3. Go to Settings → General and manually change language
4. Clear browser cache (Ctrl+Shift+Del)

### Issue: Booking form doesn't load vehicles

**Cause**: API call failing or Regional Site Proxy not initialized

**Solution**:
1. Check browser console for JavaScript errors (F12)
2. Check WordPress error log: `wp-content/debug.log`
3. Verify AIRLINEL_MAIN_SITE_URL is correct in wp-config.php
4. Test API directly: Go to Regional Settings → Test Connection

### Issue: Database connection error during installation

**Cause**: Database credentials incorrect in wp-config.php

**Solution**:
1. Verify DB_NAME, DB_USER, DB_PASSWORD in wp-config.php
2. Confirm database exists on the server:
   ```bash
   mysql -u root -p -e "SHOW DATABASES;"
   ```
3. If database doesn't exist, create it:
   ```bash
   mysql -u root -p -e "CREATE DATABASE antalya_airlinel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   mysql -u root -p -e "GRANT ALL PRIVILEGES ON antalya_airlinel.* TO 'db_user'@'localhost' IDENTIFIED BY 'db_password'; FLUSH PRIVILEGES;"
   ```

### Issue: Permission denied errors

**Cause**: Incorrect file/directory permissions

**Solution**:
```bash
# Set correct permissions
chmod -R 755 wp-content/themes/
chmod -R 755 wp-content/plugins/
chmod -R 755 wp-content/uploads/
chmod 644 wp-config.php

# Set correct ownership
chown -R www-data:www-data wp-content/
chown www-data:www-data wp-config.php
```

## Domain Setup (DNS)

For each regional site, configure DNS:

### For Subdomain (Recommended)

```
Type    | Name     | Value
--------|----------|-------------------
CNAME   | antalya  | airlinel.com
```

After DNS update, verify:
```bash
nslookup antalya.airlinel.com
# Should resolve to same IP as airlinel.com
```

### For Separate Domain

```
Type | Name         | Value
-----|--------------|----------------
A    | (root)       | YOUR_SERVER_IP
CNAME| www          | domain.com (optional)
```

## SSL Certificate

Ensure SSL is configured for the regional site:

```bash
# If using Let's Encrypt
certbot certonly -d antalya.airlinel.com
certbot renew  # Setup auto-renewal

# Verify SSL
openssl s_client -connect antalya.airlinel.com:443
```

Update wp-config.php if needed:
```php
define('FORCE_SSL_ADMIN', true);
define('FORCE_SSL_LOGIN', true);
```

## Maintenance & Updates

### Regular Tasks

1. **Daily**: Monitor error logs
   ```bash
   tail -f wp-content/debug.log
   ```

2. **Weekly**: Check connection to main site
   - Admin → Settings → Regional Site Settings → Test Connection

3. **Monthly**: 
   - Update WordPress core: WP Dashboard → Updates
   - Update plugins: Plugins → Updates
   - Backup database: Via hosting control panel

4. **Quarterly**:
   - Review and rotate API keys (if needed)
   - Check for plugin security updates

### Backup Strategy

Create regular backups:

```bash
# Backup database
mysqldump -u db_user -p antalya_airlinel > backup_$(date +%Y%m%d).sql

# Backup wp-content
tar -czf wp-content_$(date +%Y%m%d).tar.gz wp-content/

# Backup entire site
tar -czf antalya_airlinel_$(date +%Y%m%d).tar.gz .
```

## Performance Optimization

### Enable Caching

The Regional Site Proxy automatically caches vehicle searches for 5 minutes. To adjust:

Edit `/admin/regional-settings.php` and modify `transient_ttl`:

```php
private $transient_ttl = 300; // 5 minutes
```

### Content Delivery Network (CDN)

For better performance, configure a CDN:

1. Update theme URLs to use CDN:
   ```php
   define('AIRLINEL_CDN_URL', 'https://cdn.antalya.airlinel.com');
   ```

2. Configure CloudFlare or similar CDN

### Database Optimization

```bash
# Optimize all tables
mysqlcheck -u db_user -p antalya_airlinel -o -a
```

## Advanced Configuration

### Custom Pricing

To customize pricing per regional site, override in Regional Site Proxy:

See: `includes/class-regional-site-proxy.php`

### Multi-Region Deployment

To deploy multiple regional sites efficiently, use the setup script:

```bash
./bin/setup-regional-site.sh --name=istanbul --lang=tr_TR \
  --api-key=istanbul_xyz123 --domain=istanbul.airlinel.com
```

## Support & Resources

- **API Documentation**: See `/docs/API.md`
- **Main Site Admin**: https://airlinel.com/wp-admin
- **Regional Settings**: Regional Site Admin → Settings → Regional Site Settings
- **Error Logs**: `wp-content/debug.log`
- **WordPress Docs**: https://wordpress.org/support/
- **Airlinel API**: https://airlinel.com/wp-json/airlinel/v1/

## Quick Reference Checklist

- [ ] Domain/subdomain configured (DNS)
- [ ] Database created
- [ ] WordPress downloaded
- [ ] wp-config.php created with regional settings
- [ ] Theme copied to wp-content/themes/
- [ ] Language plugin activated
- [ ] WordPress installation completed
- [ ] Theme activated
- [ ] Regional Settings page shows "Connected"
- [ ] Test Connection button works
- [ ] Booking form loads vehicles
- [ ] Language set correctly in admin
- [ ] SSL certificate configured
- [ ] Backup strategy in place

---

**Version:** 1.0  
**Last Updated:** 2026-04-25  
**Status:** Production Ready
