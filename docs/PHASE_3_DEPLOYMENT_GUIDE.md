# Phase 3 Deployment Guide
## Airlinel Airport Transfer Platform - Regional Sites & Multi-Language Support

**Document Version:** 1.0  
**Last Updated:** April 2026  
**Target:** Production Deployment

---

## Table of Contents

1. [Overview](#overview)
2. [Pre-Deployment Checklist](#pre-deployment-checklist)
3. [Step-by-Step Deployment](#step-by-step-deployment)
4. [Rollback Plan](#rollback-plan)
5. [Post-Deployment Checklist](#post-deployment-checklist)
6. [Regional Site Deployment](#regional-site-deployment)
7. [Troubleshooting](#troubleshooting)

---

## Overview

### What's Being Deployed

Phase 3 includes:

- **Multi-Language Support** - 12 languages (English, French, German, Spanish, Italian, Portuguese, Turkish, Arabic, Russian, Japanese, Chinese, Korean)
- **Regional Site Proxy System** - Seamless booking across regional sites
- **API Caching & Fallback** - Improved performance and reliability
- **Analytics Dashboard** - Track bookings across all regional sites
- **Homepage Content Management** - Customizable sections per regional site
- **Data Synchronization** - Vehicle and exchange rate sync from main site

### Architecture Overview

```
Main Site (Master)
├── Vehicles database
├── Exchange rates
├── Reservations (all regional)
└── API endpoints (/search, /reservation/create)
    ↓
Regional Sites (Proxies)
├── Berlin Site → Uses proxy to call Main Site API
├── Istanbul Site → Uses proxy to call Main Site API
├── Antalya Site → Uses proxy to call Main Site API
└── Each with cached data & local language
```

### Risk Assessment

**Low Risk Areas:**
- Language support (additive feature)
- Homepage customization (database driven)
- Analytics (read-only reporting)

**Medium Risk Areas:**
- API proxy changes (critical for booking flow)
- Cache fallback behavior (affects availability)
- Data synchronization (must maintain consistency)

**High Risk Areas:**
- Database migrations (must be reversible)
- API key handling (security sensitive)
- Regional site configuration (single point of failure)

---

## Pre-Deployment Checklist

### Code Review & Testing

- [ ] All code reviewed by 2+ developers
- [ ] Security audit completed (`tests/test-security-fixes.php` passes)
- [ ] Integration tests pass (`tests/regional-site-tests.php` passes)
- [ ] No commented-out code or debugging statements
- [ ] No API keys or secrets in code
- [ ] No database credentials in code

### Environment Verification

- [ ] Staging environment available
- [ ] Staging environment identical to production (software versions, PHP version, MySQL version)
- [ ] Database backups automated and tested
- [ ] File backups automated and tested
- [ ] CDN cache can be flushed
- [ ] Error logging configured (but not verbose)

### Dependencies

- [ ] PHP 7.4+ installed (or 8.0+)
- [ ] WordPress 6.0+ up to date
- [ ] All required WordPress plugins installed:
  - [ ] WPML or Polylang (for multi-language support)
  - [ ] WooCommerce (if payments integrated)
  - [ ] Any custom plugins

- [ ] MySQL 5.7+ (or MariaDB 10.3+)
- [ ] wp-cli available for CLI commands
- [ ] Cron jobs configured for sync tasks

### Security Audit Checklist

- [ ] HTTPS enabled on all sites
- [ ] SSL certificates valid and not expiring
- [ ] WordPress security plugins updated
- [ ] No vulnerable plugins installed
- [ ] File permissions correct (755 for dirs, 644 for files)
- [ ] wp-config.php protected from direct access
- [ ] Database user has minimal required privileges
- [ ] Rate limiting configured on API endpoints
- [ ] CSRF nonce validation enabled
- [ ] SQL injection protection verified
- [ ] XSS protection headers configured

### Data Validation

- [ ] All existing vehicles data valid
- [ ] All existing reservations data intact
- [ ] Exchange rates data valid
- [ ] No duplicate zones or vehicles
- [ ] All regional sites have source_site_id configured

### Backup & Recovery

- [ ] Full database backup taken
- [ ] Full file backup taken
- [ ] Backup verified (can restore successfully)
- [ ] Backup location documented
- [ ] Recovery procedure documented
- [ ] Estimated recovery time documented

### Communication Plan

- [ ] Stakeholders notified of deployment window
- [ ] Support team briefed on new features
- [ ] Rollback procedures communicated
- [ ] Emergency contact list updated
- [ ] Status page prepared for updates

---

## Step-by-Step Deployment

### Phase 1: Staging Deployment (2-3 hours)

#### 1.1 Deploy to Staging

```bash
# Connect to staging server
ssh staging-user@staging.airlinel.com

# Navigate to WordPress root
cd /var/www/airlinel-transfer-services

# Create backup before deployment
wp db export backups/pre-phase3-$(date +%Y%m%d-%H%M%S).sql

# Pull latest code
git pull origin main
# OR manually upload files
scp -r airlinel-transfer-services/* staging-user@staging.airlinel.com:/var/www/airlinel-transfer-services/

# Set correct permissions
find . -type d -exec chmod 755 {} \;
find . -type f -exec chmod 644 {} \;
chmod 755 wp-cli.phar  # if using local wp-cli
```

#### 1.2 Database Migrations

```bash
# Run any pending database migrations
wp eval-file db-migrations/phase-3-migration.php

# Or manually execute migration SQL
mysql -u db_user -p db_name < db-migrations/phase-3-migration.sql

# Verify migration success
wp db query "SELECT COUNT(*) FROM wp_options WHERE option_name LIKE 'airlinel_%';"
```

#### 1.3 Configuration

```bash
# Set regional site configuration
wp option add airlinel_source_site_id 'main-site'
wp option add airlinel_enable_regional_sites '1'
wp option add airlinel_enable_multi_language '1'

# Configure language support
wp option add airlinel_supported_languages 'en,fr,de,es,it,pt,tr,ar,ru,ja,zh,ko'

# Configure cache settings
wp option add airlinel_cache_ttl '300'  # 5 minutes
wp option add airlinel_enable_api_cache '1'

# Configure API proxy
wp option add airlinel_main_site_url 'https://main.airlinel.com'
# Note: AIRLINEL_MAIN_SITE_API_KEY should be set in wp-config.php
```

#### 1.4 Clear Caches

```bash
# Clear WordPress cache
wp cache flush

# Clear transients
wp transient delete-all

# Clear CDN cache (if applicable)
curl -X POST https://cdn.example.com/purge-all

# Verify cache is cleared
wp cache stats
```

### Phase 2: Staging Testing (2-4 hours)

#### 2.1 Run Test Suite

```bash
# Run security tests
wp eval-file tests/test-security-fixes.php

# Run integration tests
wp eval-file tests/regional-site-tests.php

# Run custom tests
wp eval-file tests/your-custom-tests.php
```

#### 2.2 Manual Testing

```bash
# Test API proxy
curl -X POST https://staging.airlinel.com/wp-json/airlinel/v1/search \
  -H "X-API-Key: test-key" \
  -d '{"pickup":"London","dropoff":"Heathrow","country":"UK","passengers":2}'

# Test language switching
curl https://staging.airlinel.com/?lang=tr  # Should load Turkish

# Test regional site as if from Berlin site
curl -X GET https://staging.airlinel.com/wp-json/airlinel/v1/search \
  -H "X-Source-Site: berlin"
```

#### 2.3 Performance Testing

```bash
# Check response times
ab -n 100 -c 10 https://staging.airlinel.com/

# Check database query performance
wp db query "SHOW FULL PROCESSLIST;"

# Monitor cache hit rate
wp cache stats
```

#### 2.4 Sign-off

- [ ] All tests passing
- [ ] No console errors in browser
- [ ] No PHP warnings/errors in logs
- [ ] Performance acceptable
- [ ] Regional site proxy working
- [ ] Multi-language switching working
- [ ] Analytics dashboard loading
- [ ] Manager approval obtained

---

### Phase 3: Production Deployment (1-2 hours, during maintenance window)

#### 3.1 Production Backup

```bash
# Create comprehensive backups before deployment
ssh prod-user@main.airlinel.com

cd /var/www/airlinel-transfer-services

# Database backup
wp db export backups/pre-phase3-production-$(date +%Y%m%d-%H%M%S).sql

# Verify backup
ls -lh backups/pre-phase3-production-*.sql

# Full file backup (optional, but recommended)
tar -czf backups/pre-phase3-production-files-$(date +%Y%m%d-%H%M%S).tar.gz \
  --exclude=node_modules \
  --exclude=vendor \
  --exclude=wp-content/uploads \
  ./
```

#### 3.2 Enable Maintenance Mode

```bash
# Enable maintenance mode
wp maintenance-mode activate

# Or create maintenance file
echo "Maintenance in progress. Back online soon!" > .maintenance

# Verify maintenance mode is active
curl -I https://main.airlinel.com/  # Should show 503 or maintenance message
```

#### 3.3 Deploy Code

```bash
# Option A: Using Git
git pull origin main
git checkout main  # Ensure on main branch

# Option B: Upload files manually
scp -r updated-files/* prod-user@main.airlinel.com:/var/www/airlinel-transfer-services/

# Set correct permissions
find . -type d -exec chmod 755 {} \;
find . -type f -exec chmod 644 {} \;
```

#### 3.4 Run Migrations

```bash
# Stop any running sync processes
wp cron event unschedule airlinel_sync_vehicles

# Run database migrations
wp eval-file db-migrations/phase-3-migration.php

# Verify migration
wp db query "SELECT * FROM wp_options WHERE option_name LIKE 'airlinel_phase3_%';"
```

#### 3.5 Update Configuration

```bash
# Update production configuration
wp option update airlinel_enable_regional_sites '1'
wp option update airlinel_enable_multi_language '1'
wp option update airlinel_main_site_url 'https://main.airlinel.com'

# Verify configuration
wp option get airlinel_enable_regional_sites
wp option get airlinel_enable_multi_language
```

#### 3.6 Clear Caches

```bash
# Clear all caches
wp cache flush
wp transient delete-all

# Flush CDN (if applicable)
curl -X PURGE https://cdn.airlinel.com/

# Wait for cache to clear
sleep 30
```

#### 3.7 Disable Maintenance Mode

```bash
# Disable maintenance mode
wp maintenance-mode deactivate

# Or remove maintenance file
rm .maintenance

# Verify site is back online
curl -I https://main.airlinel.com/  # Should show 200 OK
```

#### 3.8 Verification

```bash
# Quick health check
wp health-check status

# Check error logs (should be quiet)
tail -n 20 wp-content/debug.log

# Verify API is responding
curl -X POST https://main.airlinel.com/wp-json/airlinel/v1/search \
  -H "X-API-Key: your-api-key" \
  -d '{"pickup":"London","dropoff":"Heathrow","country":"UK"}'
```

---

## Rollback Plan

### When to Rollback

Rollback immediately if:
- API returns 500 errors consistently
- Database migrations fail
- Booking flow broken
- Critical security issue discovered
- Performance severely degraded
- Regional sites cannot access main site

### Rollback Steps

#### Step 1: Enable Maintenance Mode

```bash
ssh prod-user@main.airlinel.com
cd /var/www/airlinel-transfer-services
wp maintenance-mode activate
```

#### Step 2: Database Rollback

```bash
# Identify most recent pre-deployment backup
ls -lh backups/pre-phase3-production-*.sql | tail -1

# Restore from backup
wp db import backups/pre-phase3-production-20260425-120000.sql

# Or use MySQL directly if wp-cli fails
mysql -u db_user -p db_name < backups/pre-phase3-production-20260425-120000.sql

# Verify restoration
wp db tables  # Should show original tables
```

#### Step 3: Code Rollback

```bash
# Option A: Using Git
git revert HEAD
git push origin main

# Option B: Restore file backup
tar -xzf backups/pre-phase3-production-files-20260425-120000.tar.gz

# Set correct permissions
find . -type d -exec chmod 755 {} \;
find . -type f -exec chmod 644 {} \;
```

#### Step 4: Clear Caches

```bash
wp cache flush
wp transient delete-all
```

#### Step 5: Disable Maintenance Mode

```bash
wp maintenance-mode deactivate
```

#### Step 6: Notify Stakeholders

- Email team with rollback notification
- Update status page
- Document reason for rollback
- Schedule post-mortem review

### Testing After Rollback

```bash
# Verify site is functioning
curl -I https://main.airlinel.com/

# Check for errors
tail -n 50 wp-content/debug.log

# Run quick test
wp eval-file tests/test-security-fixes.php
```

---

## Post-Deployment Checklist

### Immediate (First 1 hour)

- [ ] Site is accessible and loading
- [ ] No 500 errors in error logs
- [ ] Booking search working
- [ ] API responding to requests
- [ ] Database queries executing normally
- [ ] Cache is working (check hit rates)

### Short-term (First 24 hours)

- [ ] Monitor error logs for issues
  ```bash
  tail -f wp-content/debug.log
  ```

- [ ] Check regional sites can connect
  ```bash
  wp eval-file tests/regional-site-tests.php
  ```

- [ ] Verify language switching works on all sites

- [ ] Check analytics dashboard shows data

- [ ] Confirm email notifications sending

- [ ] Monitor server resource usage
  ```bash
  free -h  # Memory
  df -h    # Disk space
  top      # CPU
  ```

- [ ] Review access logs for 404s or errors
  ```bash
  grep "error" /var/log/apache2/error.log | tail -20
  ```

### Medium-term (First week)

- [ ] Monitor performance metrics
  - Response times should not increase >10%
  - Database query times normal
  - Cache hit rate >70%
  - CPU usage normal

- [ ] Check for data consistency issues
  ```bash
  wp eval-file tests/data-integrity-check.php
  ```

- [ ] Review all error logs for patterns
  ```bash
  grep -E "error|warning" wp-content/debug.log | sort | uniq -c | sort -rn
  ```

- [ ] Verify regional sites still functioning
  - Test booking from each regional site
  - Check source site tracking in reservations
  - Verify payment processing

- [ ] Get user feedback
  - Any issues reported?
  - Performance acceptable?
  - Language switching working well?

### Long-term (First month)

- [ ] Monitor cost metrics (if applicable)
  - API calls to main site
  - Database performance
  - Storage used

- [ ] Analyze analytics
  - Booking volume by region
  - Languages used
  - Peak traffic times

- [ ] Plan optimization based on usage patterns

---

## Regional Site Deployment

### New Regional Site Setup

When adding a new regional site (e.g., Antalya site):

#### Step 1: Configuration

On the **new regional site**, in `wp-config.php`:

```php
// Add these constants to wp-config.php
define('AIRLINEL_MAIN_SITE_URL', 'https://main.airlinel.com');
define('AIRLINEL_MAIN_SITE_API_KEY', 'your-secure-api-key');
define('AIRLINEL_IS_REGIONAL_SITE', true);
define('AIRLINEL_SITE_ID', 'antalya');  // Unique identifier
```

#### Step 2: Database Setup

On the **main site**, run:

```bash
# Register the new regional site
wp option add airlinel_regional_site_antalya '{
  "name": "Antalya Transfer Services",
  "url": "https://antalya.airlinel.com",
  "site_id": "antalya",
  "country": "TR",
  "language": "tr",
  "enabled": true
}'

# Create sync entries
wp db query "INSERT INTO wp_airlinel_sites (site_id, site_name, site_url, country, language) 
VALUES ('antalya', 'Antalya', 'https://antalya.airlinel.com', 'TR', 'tr');"
```

#### Step 3: DNS Setup

```bash
# Add DNS records for new regional site
# A record: antalya.airlinel.com -> IP address
# Optional: Add CNAME if using CDN
```

#### Step 4: SSL Certificate

```bash
# Obtain SSL certificate (using Let's Encrypt)
certbot certonly --webroot -w /var/www/antalya.airlinel.com -d antalya.airlinel.com

# Or if using cPanel/Plesk, use their interface to request certificate
```

#### Step 5: WordPress Installation

On the **new regional site**:

```bash
# Download WordPress (if not already installed)
wp core download

# Create wp-config.php with above constants
# Copy wp-config.php from template or create new

# Install WordPress (if not already installed)
wp core install --url="https://antalya.airlinel.com" \
  --title="Antalya Transfer Services" \
  --admin_user=admin \
  --admin_password=secure-password \
  --admin_email=admin@airlinel.com

# Activate theme and plugins
wp theme activate airlinel-transfer-services
wp plugin activate language-support-plugin
wp plugin activate stripe-payments
```

#### Step 6: Language Configuration

```bash
# Set regional site language
wp option update airlinel_current_language 'tr'
wp option update language_support 'wpml'  # or 'polylang'

# Load Turkish translations
wp language core install tr
wp language theme install airlinel-transfer-services tr
```

#### Step 7: Homepage Setup

```bash
# Configure homepage sections for this region
wp option add airlinel_homepage_sections '{
  "hero_enabled": true,
  "services_enabled": true,
  "cities_enabled": true,
  "testimonials_enabled": true,
  "hero_title": "Antalya Havalimanı Transfer Hizmetleri",
  "hero_subtitle": "Hızlı, Güvenli ve Uygun Fiyatlı Transfer"
}'
```

#### Step 8: Testing

```bash
# Test API connectivity to main site
curl -X POST https://antalya.airlinel.com/wp-json/airlinel/v1/search \
  -H "Content-Type: application/json" \
  -d '{"pickup":"Antalya Airport","dropoff":"City Center","country":"TR","passengers":2}' \
  -H "X-API-Key: api-key"

# Test language switching
curl https://antalya.airlinel.com/?lang=tr | grep -i "Antalya"

# Test homepage
curl https://antalya.airlinel.com/ | grep "Transfer"
```

#### Step 9: Monitoring Setup

```bash
# Add site to monitoring dashboard
wp option add airlinel_monitor_site_antalya '{
  "enabled": true,
  "check_interval": 300,
  "alert_email": "ops@airlinel.com"
}'

# Test monitoring
curl -I https://antalya.airlinel.com/
```

### Configuration Checklist for New Regional Sites

- [ ] wp-config.php has required constants
- [ ] Theme installed and activated
- [ ] Plugins installed (language support, payments)
- [ ] Language set to regional language
- [ ] Homepage content configured
- [ ] SSL certificate installed
- [ ] DNS records created
- [ ] API connectivity tested
- [ ] Language switching works
- [ ] Email notifications working
- [ ] Analytics tracking enabled
- [ ] Monitoring alerts configured

---

## Troubleshooting

### Common Issues & Solutions

#### Issue: "Main site is temporarily unavailable"

**Cause:** Regional site cannot connect to main site API

**Solution:**

```bash
# 1. Check API key in wp-config.php is correct
grep AIRLINEL_MAIN_SITE_API_KEY wp-config.php

# 2. Test connectivity
curl -v https://main.airlinel.com/wp-json/airlinel/v1/search

# 3. Check firewall rules
# Make sure regional site IP can reach main site

# 4. Check main site is running
# Navigate to main site admin and verify it's accessible

# 5. Check error logs
tail -f wp-content/debug.log | grep -i "api\|proxy"
```

#### Issue: Language not switching

**Cause:** Language system not configured or files missing

**Solution:**

```bash
# 1. Verify language plugin installed
wp plugin list | grep -i language

# 2. Check translation files exist
find . -name "*.po" -o -name "*.mo"

# 3. Verify language option set
wp option get airlinel_current_language

# 4. Check WPML/Polylang configuration
wp option list | grep -E "wpml|polylang"

# 5. Reactivate language plugin
wp plugin deactivate language-plugin
wp plugin activate language-plugin
```

#### Issue: Cache not working / Always returning expired data

**Cause:** Cache transients not being set or expiring too quickly

**Solution:**

```bash
# 1. Check cache configuration
wp option get airlinel_cache_ttl
wp option get airlinel_enable_api_cache

# 2. Clear existing cache
wp transient delete-all
wp cache flush

# 3. Test cache is working
wp eval-file tests/test-cache-functionality.php

# 4. Check PHP can write to cache directory
ls -la wp-content/cache/

# 5. Increase cache TTL if needed
wp option update airlinel_cache_ttl '600'  # 10 minutes
```

#### Issue: Regional site showing main site content

**Cause:** Source site ID not set or homepages not configured separately

**Solution:**

```bash
# 1. Check source site ID
wp option get airlinel_source_site_id

# 2. Set source site ID if empty
wp option update airlinel_source_site_id 'berlin'  # or other site ID

# 3. Configure homepage separately
wp option add airlinel_homepage_sections '{"hero_enabled":true}'

# 4. Verify regional site configuration
wp option list | grep airlinel_homepage

# 5. Refresh homepage
wp cache flush
```

#### Issue: Database migration failed

**Cause:** Migration script error or permission issues

**Solution:**

```bash
# 1. Check error logs
tail -f wp-content/debug.log | grep -i "migrate\|query"

# 2. Manually check if migration was partial
wp db tables

# 3. Restore from backup if migration corrupted data
wp db import backups/pre-phase3-*.sql

# 4. Run migration again
wp eval-file db-migrations/phase-3-migration.php

# 5. If still failing, run SQL manually
mysql -u user -p db_name < db-migrations/phase-3-migration.sql
```

#### Issue: Performance degraded after deployment

**Cause:** Too many queries, cache not working, or unoptimized code

**Solution:**

```bash
# 1. Check database query performance
wp db query "SHOW FULL PROCESSLIST;"

# 2. Analyze slow queries
# Check MySQL slow query log

# 3. Verify cache hit rate
wp cache stats

# 4. Check for N+1 query problems
wp eval-file tests/query-performance-test.php

# 5. Disable problematic features temporarily
wp option update airlinel_enable_regional_sites '0'
wp option update airlinel_enable_multi_language '0'

# 6. Re-enable one at a time to identify culprit
wp option update airlinel_enable_regional_sites '1'
```

### Getting Help

If deployment fails and troubleshooting doesn't help:

1. **Check logs first:**
   ```bash
   tail -f wp-content/debug.log
   tail -f /var/log/apache2/error.log
   tail -f /var/log/mysql/error.log
   ```

2. **Contact support team** with:
   - Error logs (last 100 lines)
   - What step failed
   - What you've tried
   - Current state of system

3. **Escalate to rollback** if:
   - Error cannot be identified
   - Time spent > 2 hours
   - Users reporting issues
   - Revenue-impacting issues

---

## Deployment Success Criteria

You can consider deployment successful when:

- ✓ All tests pass (security and integration)
- ✓ Site responds without 5xx errors
- ✓ API proxy working (regional sites can book)
- ✓ Languages switching properly
- ✓ Analytics dashboard shows data
- ✓ Homepage customization working
- ✓ Performance unchanged or improved
- ✓ No security vulnerabilities reported
- ✓ Team sign-off received
- ✓ 24-hour monitoring passed

---

**Document Author:** Development Team  
**Last Review:** April 2026  
**Next Review:** July 2026
