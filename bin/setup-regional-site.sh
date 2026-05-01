#!/bin/bash

###############################################################################
# Airlinel Regional Site Setup Script
#
# Automates the setup of a new regional site for the Airlinel platform
#
# Usage:
#   ./setup-regional-site.sh --name=antalya --lang=tr_TR \
#       --api-key=your_api_key --domain=antalya.airlinel.com
#
# Parameters:
#   --name=SITE_NAME           Regional site name (e.g., 'antalya')
#   --lang=LANGUAGE_CODE       Language code (e.g., 'tr_TR')
#   --api-key=API_KEY          Regional API key from main site
#   --domain=DOMAIN            Domain name (e.g., 'antalya.airlinel.com')
#   --db-name=DB_NAME          Database name (optional, defaults to SITE_NAME_airlinel)
#   --db-user=DB_USER          Database user (optional, defaults to root)
#   --db-pass=DB_PASS          Database password (optional)
#   --web-root=/path           Web root directory (optional, defaults to /var/www/html)
#   --theme-path=/path         Theme source path (optional, uses ../.. default)
#
###############################################################################

set -e

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Default values
WEB_ROOT="/var/www/html"
DB_USER="root"
DB_PASS=""
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
THEME_SOURCE="$SCRIPT_DIR/../"

# Functions
print_usage() {
    echo "Usage: $0 --name=SITE_NAME --lang=LANGUAGE_CODE --api-key=API_KEY --domain=DOMAIN [options]"
    echo ""
    echo "Required parameters:"
    echo "  --name=SITE_NAME           Regional site name (e.g., 'antalya')"
    echo "  --lang=LANGUAGE_CODE       Language code (e.g., 'tr_TR', 'de_DE', 'en_US')"
    echo "  --api-key=API_KEY          Regional API key from main site"
    echo "  --domain=DOMAIN            Domain name (e.g., 'antalya.airlinel.com')"
    echo ""
    echo "Optional parameters:"
    echo "  --db-name=DB_NAME          Database name (default: SITE_NAME_airlinel)"
    echo "  --db-user=DB_USER          Database user (default: root)"
    echo "  --db-pass=DB_PASS          Database password"
    echo "  --web-root=/path           Web root directory (default: /var/www/html)"
    echo "  --theme-path=/path         Theme source path (default: script directory)"
    echo ""
    echo "Example:"
    echo "  $0 --name=antalya --lang=tr_TR --api-key=xyz123 --domain=antalya.airlinel.com"
}

log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Parse arguments
while [[ $# -gt 0 ]]; do
    case $1 in
        --name=*)
            SITE_NAME="${1#*=}"
            shift
            ;;
        --lang=*)
            LANGUAGE_CODE="${1#*=}"
            shift
            ;;
        --api-key=*)
            API_KEY="${1#*=}"
            shift
            ;;
        --domain=*)
            DOMAIN="${1#*=}"
            shift
            ;;
        --db-name=*)
            DB_NAME="${1#*=}"
            shift
            ;;
        --db-user=*)
            DB_USER="${1#*=}"
            shift
            ;;
        --db-pass=*)
            DB_PASS="${1#*=}"
            shift
            ;;
        --web-root=*)
            WEB_ROOT="${1#*=}"
            shift
            ;;
        --theme-path=*)
            THEME_SOURCE="${1#*=}"
            shift
            ;;
        -h|--help)
            print_usage
            exit 0
            ;;
        *)
            log_error "Unknown parameter: $1"
            print_usage
            exit 1
            ;;
    esac
done

# Validate required parameters
if [[ -z "$SITE_NAME" ]] || [[ -z "$LANGUAGE_CODE" ]] || [[ -z "$API_KEY" ]] || [[ -z "$DOMAIN" ]]; then
    log_error "Missing required parameters"
    print_usage
    exit 1
fi

# Set default database name if not provided
if [[ -z "$DB_NAME" ]]; then
    DB_NAME="${SITE_NAME}_airlinel"
fi

# Validate language code
VALID_LANGS=("en_US" "tr_TR" "de_DE" "ru_RU" "fr_FR" "it_IT" "ar" "da_DK" "nl_NL" "sv_SE" "zh_CN" "ja")
if [[ ! " ${VALID_LANGS[@]} " =~ " ${LANGUAGE_CODE} " ]]; then
    log_error "Invalid language code: $LANGUAGE_CODE"
    echo "Valid options: ${VALID_LANGS[@]}"
    exit 1
fi

# Set site directory
SITE_DIR="$WEB_ROOT/$SITE_NAME"

log_info "Starting regional site setup..."
echo ""
echo "Configuration:"
echo "  Site Name:      $SITE_NAME"
echo "  Language:       $LANGUAGE_CODE"
echo "  Domain:         $DOMAIN"
echo "  Site Directory: $SITE_DIR"
echo "  Database Name:  $DB_NAME"
echo "  Database User:  $DB_USER"
echo ""

# Step 1: Check if directory already exists
if [[ -d "$SITE_DIR" ]]; then
    log_warning "Directory already exists: $SITE_DIR"
    read -p "Continue anyway? (y/n) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        log_info "Setup cancelled"
        exit 0
    fi
else
    log_info "Creating directory: $SITE_DIR"
    mkdir -p "$SITE_DIR"
fi

# Step 2: Download WordPress
log_info "Downloading WordPress..."
cd "$SITE_DIR"

if [[ -f "wp-config.php" ]]; then
    log_warning "wp-config.php already exists, skipping WordPress download"
else
    if command -v wget &> /dev/null; then
        wget -q https://wordpress.org/latest.tar.gz
    elif command -v curl &> /dev/null; then
        curl -s -O https://wordpress.org/latest.tar.gz
    else
        log_error "wget or curl not found. Please download WordPress manually."
        exit 1
    fi

    if [[ ! -f "latest.tar.gz" ]]; then
        log_error "Failed to download WordPress"
        exit 1
    fi

    tar -xzf latest.tar.gz
    mv wordpress/* .
    rmdir wordpress
    rm latest.tar.gz
    log_success "WordPress downloaded"
fi

# Step 3: Generate security salts
log_info "Generating security salts..."
SALTS=$(curl -s https://api.wordpress.org/secret-key/1.1/salt/)
if [[ -z "$SALTS" ]]; then
    log_error "Failed to generate security salts"
    exit 1
fi

# Step 4: Create wp-config.php
log_info "Creating wp-config.php..."

# Get database password if not provided
if [[ -z "$DB_PASS" ]]; then
    read -s -p "Enter database password for user $DB_USER: " DB_PASS
    echo
fi

cat > "$SITE_DIR/wp-config.php" <<'WPCONFIG'
<?php
/**
 * The base configuration for WordPress (Regional Site)
 * Generated by setup-regional-site.sh
 */

// Database settings
define('DB_NAME', 'DBNAME_PLACEHOLDER');
define('DB_USER', 'DBUSER_PLACEHOLDER');
define('DB_PASSWORD', 'DBPASS_PLACEHOLDER');
define('DB_HOST', 'localhost');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');

// Security salts
SALTS_PLACEHOLDER

// Database table prefix
$table_prefix = 'wp_';

// WordPress debugging
define('WP_DEBUG', false);
define('WP_DEBUG_LOG', false);

// =========================
// AIRLINEL REGIONAL CONFIG
// =========================

// Main site URL (required)
define('AIRLINEL_MAIN_SITE_URL', 'https://airlinel.com');

// Regional site API key
define('AIRLINEL_MAIN_SITE_API_KEY', 'APIKEY_PLACEHOLDER');

// Site language
define('WPLANG', 'LANGUAGE_PLACEHOLDER');

// Regional site ID
define('AIRLINEL_SITE_ID', 'SITENAME_PLACEHOLDER');

// =========================
// ABSOLUTE PATH SETTINGS
// =========================

if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

require_once ABSPATH . 'wp-settings.php';
?>
WPCONFIG

# Replace placeholders
sed -i "s|DBNAME_PLACEHOLDER|$DB_NAME|g" "$SITE_DIR/wp-config.php"
sed -i "s|DBUSER_PLACEHOLDER|$DB_USER|g" "$SITE_DIR/wp-config.php"
sed -i "s|DBPASS_PLACEHOLDER|$DB_PASS|g" "$SITE_DIR/wp-config.php"
sed -i "s|APIKEY_PLACEHOLDER|$API_KEY|g" "$SITE_DIR/wp-config.php"
sed -i "s|LANGUAGE_PLACEHOLDER|$LANGUAGE_CODE|g" "$SITE_DIR/wp-config.php"
sed -i "s|SITENAME_PLACEHOLDER|$SITE_NAME|g" "$SITE_DIR/wp-config.php"

# Replace salts in a more careful way
ESCAPED_SALTS=$(echo "$SALTS" | sed 's/[&/\]/\\&/g')
sed -i "s|SALTS_PLACEHOLDER|$ESCAPED_SALTS|g" "$SITE_DIR/wp-config.php"

log_success "wp-config.php created"

# Step 5: Copy theme
log_info "Copying Airlinel theme from: $THEME_SOURCE"
if [[ ! -d "$THEME_SOURCE" ]]; then
    log_error "Theme source directory not found: $THEME_SOURCE"
    exit 1
fi

mkdir -p "$SITE_DIR/wp-content/themes"
cp -r "$THEME_SOURCE" "$SITE_DIR/wp-content/themes/airlinel-transfer-services"

if [[ -d "$SITE_DIR/wp-content/themes/airlinel-transfer-services" ]]; then
    log_success "Theme copied"
else
    log_error "Failed to copy theme"
    exit 1
fi

# Step 6: Create database (if not exists)
if command -v mysql &> /dev/null; then
    log_info "Checking database..."

    # Check if database exists
    if ! mysql -u "$DB_USER" -p"$DB_PASS" -e "use $DB_NAME" 2>/dev/null; then
        log_info "Creating database: $DB_NAME"
        mysql -u "$DB_USER" -p"$DB_PASS" -e "CREATE DATABASE $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
        log_success "Database created"
    else
        log_warning "Database already exists: $DB_NAME"
    fi
else
    log_warning "mysql command not found. Please create the database manually."
fi

# Step 7: Set permissions
log_info "Setting file permissions..."
chmod -R 755 "$SITE_DIR/wp-content/themes/"
chmod -R 755 "$SITE_DIR/wp-content/plugins/"
chmod 644 "$SITE_DIR/wp-config.php"

if command -v chown &> /dev/null; then
    # Try to detect web server user
    if id www-data &>/dev/null 2>&1; then
        chown -R www-data:www-data "$SITE_DIR/wp-content/"
    elif id apache &>/dev/null 2>&1; then
        chown -R apache:apache "$SITE_DIR/wp-content/"
    else
        log_warning "Could not detect web server user. Please run: chown -R www-data:www-data $SITE_DIR/wp-content/"
    fi
fi

log_success "Permissions set"

# Summary
echo ""
echo "=================================="
echo -e "${GREEN}Setup Complete!${NC}"
echo "=================================="
echo ""
echo "Next steps:"
echo "1. Visit: https://$DOMAIN/wp-admin/install.php"
echo "2. Complete the WordPress installation"
echo "3. Log in to admin and activate the Airlinel theme"
echo "4. Go to Settings → Regional Site Settings to verify configuration"
echo ""
echo "Configuration Summary:"
echo "  Site Directory: $SITE_DIR"
echo "  Database Name:  $DB_NAME"
echo "  Domain:         $DOMAIN"
echo "  Language:       $LANGUAGE_CODE"
echo "  Site ID:        $SITE_NAME"
echo ""
echo "Configuration file: $SITE_DIR/wp-config.php"
echo ""
echo "For more information, see: docs/REGIONAL_SETUP.md"
echo ""
