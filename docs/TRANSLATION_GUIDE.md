# Airlinel Theme Translation Guide

## Overview

The Airlinel airport transfer platform supports 12 languages through a comprehensive localization system using WordPress native i18n (internationalization) functions. This guide explains how to manage, update, and contribute translations.

## Supported Languages

The theme supports the following 12 languages:

| Code | Locale | Language | Native Name | RTL |
|------|--------|----------|-------------|-----|
| EN | en_US | English | English | No |
| TR | tr_TR | Turkish | Türkçe | No |
| DE | de_DE | German | Deutsch | No |
| RU | ru_RU | Russian | Русский | No |
| FR | fr_FR | French | Français | No |
| IT | it_IT | Italian | Italiano | No |
| AR | ar | Arabic | العربية | Yes |
| DA | da_DK | Danish | Dansk | No |
| NL | nl_NL | Dutch | Nederlands | No |
| SV | sv_SE | Swedish | Svenska | No |
| ZH | zh_CN | Chinese (Simplified) | 简体中文 | No |
| JA | ja | Japanese | 日本語 | No |

## File Structure

Translation files are located in `/wp-content/themes/airlinel-transfer-services/languages/`

```
languages/
├── airlinel-theme.pot              # Translation template (English strings)
├── airlinel-theme-en_US.po         # English translation file
├── airlinel-theme-en_US.mo         # Compiled English translations (binary)
├── airlinel-theme-tr_TR.po         # Turkish translation file
├── airlinel-theme-tr_TR.mo         # Compiled Turkish translations
├── airlinel-theme-de_DE.po         # German translation file
├── airlinel-theme-de_DE.mo         # Compiled German translations
└── ... (files for all 12 languages)
```

### File Types

- **.pot** - Portable Object Template
  - Contains all untranslated strings (msgid only)
  - Used as a template for creating new language files
  - Updated when new strings are added to the code

- **.po** - Portable Object
  - Human-readable translation files
  - Contains both original strings (msgid) and translations (msgstr)
  - Easy to edit with translation tools or text editors
  - Used for translation work

- **.mo** - Machine Object
  - Binary compiled version of .po files
  - Used by WordPress at runtime
  - Generated from .po files
  - Not human-readable but faster to load

## Using i18n Functions in Code

All user-facing strings in templates should use WordPress i18n functions:

### Getting Started

```php
// Load the text domain
load_theme_textdomain('airlinel-theme', get_template_directory() . '/languages');

// In your code, wrap all strings with i18n functions:
echo __('Hello World', 'airlinel-theme');
_e('Button Text', 'airlinel-theme');
```

### Common Functions

#### `__()` - Return Translated String
Used when you need to get a translated string as a variable:

```php
<?php
$title = __('Book Your Transfer', 'airlinel-theme');
echo '<h1>' . esc_html($title) . '</h1>';
?>
```

#### `_e()` - Echo Translated String
Used to directly output translated strings:

```php
<h1><?php _e('Book Your Transfer', 'airlinel-theme'); ?></h1>
```

#### `esc_html__()` - Return Escaped Translated String
Use when outputting text that could contain HTML entities:

```php
<?php echo esc_html__('Airport, hotel, or address', 'airlinel-theme'); ?>
```

#### `esc_attr__()` - Return Escaped Attribute
Use for HTML attributes:

```php
<input placeholder="<?php echo esc_attr__('Enter location', 'airlinel-theme'); ?>">
```

#### `_x()` - Context-aware Translation
Use when the same string has different meanings in different contexts:

```php
// Same word, different contexts
_x('Post', 'post-type', 'airlinel-theme');  // Blog post
_x('Post', 'action', 'airlinel-theme');     // Mail a letter
```

#### `_n()` - Plural Forms
Use for strings with singular/plural variations:

```php
<?php
$count = 5;
echo sprintf(
    _n('%d vehicle available', '%d vehicles available', $count, 'airlinel-theme'),
    $count
);
?>
```

## Strings to Translate

All user-facing strings throughout the theme require translation support:

### Page Headers & Titles
- "Book Your Transfer"
- "Select Your Vehicle"
- "Booking Details"
- "Airport Transfers"
- "Our Premium Vehicles"

### Form Labels & Placeholders
- "Pickup Location"
- "Destination"
- "Pickup Date"
- "Pickup Time"
- "Full Name"
- "Email Address"
- "Phone Number"
- "Country"
- "Currency"

### Buttons & Actions
- "Book Now"
- "Check Availability & Price"
- "Confirm Booking"
- "Select Your Position"

### Messages & Descriptions
- "No hidden fees. No surge pricing. Just precision travel."
- "Fixed rates, professional drivers, real-time flight tracking"
- "Complete coverage on all vehicles with comprehensive insurance"

### Navigation & Menus
- "Cities"
- "Fleet"
- "Services"
- "Partners"
- "About Us"
- "Contact Us"

### Footer Links & Text
- "Top Destinations"
- "Privacy Policy"
- "Terms of Service"
- "Copyright notice"

## Editing Translations

### Using a Translation Editor

We recommend using one of these tools for professional translation work:

1. **Poedit** (https://poedit.net/)
   - Desktop application for Windows, Mac, Linux
   - Professional-grade translation editor
   - Automatic string extraction from code
   - Built-in translation memory

2. **VS Code with Gettext Extension**
   - Lightweight, open-source
   - Good for developers
   - Syntax highlighting for .po files

3. **GNU poedit or Lokalize**
   - Free, open-source options
   - Works on all major platforms

4. **Online Tools**
   - WebTranslator
   - Crowdin (for collaborative translation)

### Manual Editing (.po files)

If editing manually, use a text editor with UTF-8 support:

```bash
# Example .po file structure
msgid "Book Now"
msgstr "Réserver Maintenant"

msgid "Full Name"
msgstr "Nom Complet"

msgid "Pickup Location"
msgstr "Lieu de Prise en Charge"
```

**Important:**
- Keep msgid exactly as it appears in code
- Translate only msgstr
- Always use UTF-8 encoding
- Maintain the same format and punctuation

## Generating .mo Files

The .mo file is the compiled version of .po used by WordPress at runtime.

### Option 1: Using msgfmt (Linux/Mac)

```bash
cd wp-content/themes/airlinel-transfer-services/languages/

# Generate a single .mo file
msgfmt -o airlinel-theme-de_DE.mo airlinel-theme-de_DE.po

# Generate all .mo files
for file in *.po; do
    msgfmt -o "${file%.po}.mo" "$file"
done
```

### Option 2: Using Poedit

1. Open the .po file in Poedit
2. Click "File" → "Compile to .mo"
3. The .mo file is automatically created

### Option 3: WordPress CLI (WP-CLI)

If WP-CLI is installed on your server:

```bash
wp i18n make-mo wp-content/themes/airlinel-transfer-services/languages/
```

## Testing Translations

### In WordPress Admin

1. Go to **Settings** → **General**
2. Look for **Site Language**
3. Select a language from the dropdown
4. Save changes
5. Visit the theme frontend to verify translations are applied

### Via wp-config.php

For local testing, set the language in `wp-config.php`:

```php
// Temporarily test a specific language
define('WPLANG', 'de_DE');  // German
define('WPLANG', 'tr_TR');  // Turkish
define('WPLANG', 'fr_FR');  // French
```

### Browser Developer Tools

Check the page HTML to confirm language is being set:

```html
<!-- Should match your selected language -->
<html lang="en-US">    <!-- English -->
<html lang="tr">       <!-- Turkish -->
<html lang="de-DE">    <!-- German -->
<html lang="fr-FR">    <!-- French -->
```

### Debugging Missing Translations

If a string doesn't translate:

1. **Check the text domain** - Must be `'airlinel-theme'` in all i18n functions
2. **Verify the exact string** - String in code must match msgid in .po file exactly
3. **Regenerate .mo file** - Always recompile .po to .mo after edits
4. **Clear WordPress cache** - Cache plugins may prevent translation loading
5. **Check file permissions** - .mo files must be readable by the web server

## Adding New Strings

When adding new translatable strings to the theme:

1. **Update Code**
   ```php
   // New feature in a template
   echo __('New Feature Name', 'airlinel-theme');
   ```

2. **Update .pot Template**
   - Use your extraction tool to scan for new strings
   - Or manually add to `airlinel-theme.pot`:
   ```
   msgid "New Feature Name"
   msgstr ""
   ```

3. **Update All Language Files**
   - Add the new msgid/msgstr to each .po file
   - Example for German:
   ```
   msgid "New Feature Name"
   msgstr "Name der Neuen Funktion"
   ```

4. **Regenerate .mo Files**
   - Recompile all .po files to .mo using your tool

5. **Test**
   - Test with each language to verify the string displays correctly

## Language Manager Class API

The `Airlinel_Language_Manager` class provides methods for language management:

```php
global $airlinel_language_manager;

// Get all supported languages
$languages = $airlinel_language_manager->get_supported_languages();

// Get current language code (EN, TR, DE, etc.)
$current_lang = $airlinel_language_manager->get_current_language();

// Get current WordPress locale (en_US, tr_TR, etc.)
$locale = $airlinel_language_manager->get_current_locale();

// Switch language
$airlinel_language_manager->switch_language('TR');  // Switch to Turkish

// Get native language name
$name = $airlinel_language_manager->get_language_name('DE');  // Returns 'Deutsch'

// Get English language name
$en_name = $airlinel_language_manager->get_language_name_english('DE');  // Returns 'German'

// Check if language is RTL
$is_rtl = $airlinel_language_manager->is_rtl('AR');  // Returns true for Arabic

// Load translations for current language
$airlinel_language_manager->load_translations();

// Get text domain
$domain = $airlinel_language_manager->get_text_domain();  // Returns 'airlinel-theme'
```

## Contributing Translations

To contribute translations for a language:

1. **Fork** the repository
2. **Create a new branch**: `git checkout -b translate/language-code`
3. **Edit** the appropriate `.po` file with your translations
4. **Compile** to `.mo` file
5. **Test** thoroughly with your language selected
6. **Submit** a pull request with a clear description

### Translation Guidelines

- **Be consistent** - Use the same terminology throughout
- **Maintain formatting** - Preserve placeholders like %s, %d
- **Keep tone** - Match the original tone and style
- **Test context** - Ensure translations make sense on the actual page
- **Check length** - Translated text shouldn't break layouts
- **Use proper capitalization** - Follow language conventions
- **Avoid machine translation** - Native speakers produce better results

## Performance Considerations

- **Cache translations** - WordPress automatically caches .mo file contents
- **Lazy loading** - Only load translations for the current language
- **Minimize strings** - Keep translatable strings focused and atomic
- **.mo file size** - Binary .mo files are smaller and load faster than .po

## Troubleshooting

### Translations not showing

```php
// Check if text domain is loaded
if ( is_textdomain_loaded( 'airlinel-theme' ) ) {
    echo 'Text domain loaded';
} else {
    echo 'Text domain NOT loaded';
}

// Check if language is set
echo get_locale();  // Should output your current locale
```

### Incorrect encoding in .po file

- Ensure .po file is saved as **UTF-8 without BOM**
- In your editor, select: Encoding → UTF-8 (not UTF-8 with BOM)

### .mo file not generated

- Verify msgfmt is installed: `which msgfmt`
- Check file permissions: Files must be writable
- Ensure .po file is valid: Check for syntax errors

### Language not appearing in WordPress

- Clear any translation plugins' caches
- Verify language pack is installed via WordPress Admin
- Check `wp-config.php` for conflicting WPLANG settings

## Resources

- **WordPress Codex**: https://developer.wordpress.org/plugins/internationalization/
- **GNU Gettext Manual**: https://www.gnu.org/software/gettext/manual/
- **Poedit Documentation**: https://poedit.net/help/
- **W3C Language Tags**: https://www.w3.org/International/articles/language-tags/

## Support

For translation questions or issues:
- Contact: support@airlinel.com
- Issue Tracker: [Project Repository]
- Documentation: This guide

---

**Last Updated:** April 25, 2026
**Version:** 1.0
