# Airlinel Theme i18n (Internationalization) System

## Overview

The Airlinel airport transfer platform includes a complete internationalization (i18n) and localization (l10n) system supporting 12 languages. This system enables the theme to display content in multiple languages while maintaining WordPress native functionality.

## Quick Start

### What's Included

1. **Language Manager Class** - `includes/class-language-manager.php`
   - Manages language settings and translations
   - 12 supported languages with metadata
   - Methods for switching languages and retrieving language info

2. **Translation Files** - `languages/` directory
   - `.pot` template file with all strings
   - `.po` files for each language (human-readable)
   - `.mo` files for each language (compiled, used by WordPress)

3. **Documentation** - `docs/` directory
   - `TRANSLATION_GUIDE.md` - Complete translation guide
   - `I18N_IMPLEMENTATION_EXAMPLES.md` - Code examples
   - `I18N_SYSTEM_README.md` - This file

4. **Integration** - `functions.php`
   - Automatic loading of text domain
   - Language manager initialization
   - Translation loading on theme setup

## Supported Languages

| Code | Language | Locale | Native | RTL |
|------|----------|--------|--------|-----|
| EN | English | en_US | English | No |
| TR | Turkish | tr_TR | Türkçe | No |
| DE | German | de_DE | Deutsch | No |
| RU | Russian | ru_RU | Русский | No |
| FR | French | fr_FR | Français | No |
| IT | Italian | it_IT | Italiano | No |
| AR | Arabic | ar | العربية | **Yes** |
| DA | Danish | da_DK | Dansk | No |
| NL | Dutch | nl_NL | Nederlands | No |
| SV | Swedish | sv_SE | Svenska | No |
| ZH | Chinese (Simplified) | zh_CN | 简体中文 | No |
| JA | Japanese | ja | 日本語 | No |

## File Structure

```
wp-content/themes/airlinel-transfer-services/
├── includes/
│   └── class-language-manager.php          # Language management class
├── languages/
│   ├── airlinel-theme.pot                  # Translation template
│   ├── airlinel-theme-en_US.po/mo          # English
│   ├── airlinel-theme-tr_TR.po/mo          # Turkish
│   ├── airlinel-theme-de_DE.po/mo          # German
│   ├── airlinel-theme-fr_FR.po/mo          # French
│   ├── airlinel-theme-ru_RU.po/mo          # Russian
│   ├── airlinel-theme-it_IT.po/mo          # Italian
│   ├── airlinel-theme-ar.po/mo             # Arabic
│   ├── airlinel-theme-da_DK.po/mo          # Danish
│   ├── airlinel-theme-nl_NL.po/mo          # Dutch
│   ├── airlinel-theme-sv_SE.po/mo          # Swedish
│   ├── airlinel-theme-zh_CN.po/mo          # Chinese
│   ├── airlinel-theme-ja.po/mo             # Japanese
│   └── airlinel-theme-strings.txt          # List of all translatable strings
├── docs/
│   ├── TRANSLATION_GUIDE.md                # Full translation guide
│   ├── I18N_IMPLEMENTATION_EXAMPLES.md     # Code examples
│   └── I18N_SYSTEM_README.md               # This file
└── functions.php                            # i18n initialization
```

## Basic Usage

### In PHP Templates

Use WordPress i18n functions to wrap all user-facing strings:

```php
// Return translated string (store in variable)
$text = __('Book Now', 'airlinel-theme');

// Echo translated string directly
_e('Book Now', 'airlinel-theme');

// Echo HTML-escaped translation
echo esc_html__('Book Now', 'airlinel-theme');

// Echo HTML attribute-escaped translation
echo esc_attr__('Enter location', 'airlinel-theme');
```

### Switching Language

```php
global $airlinel_language_manager;

// Switch to Turkish
$airlinel_language_manager->switch_language('TR');

// Get current language
$current = $airlinel_language_manager->get_current_language();  // Returns 'EN', 'TR', etc.

// Get current locale
$locale = $airlinel_language_manager->get_current_locale();     // Returns 'en_US', 'tr_TR', etc.
```

### In WordPress Admin

1. Navigate to **Settings** → **General**
2. Find **Site Language** dropdown
3. Select desired language
4. Save changes
5. Theme will display in selected language

## Workflow

### For Development

1. **Write Code with i18n Functions**
   ```php
   <h1><?php _e('My Page Title', 'airlinel-theme'); ?></h1>
   ```

2. **Extract Strings** (using Poedit or similar)
   - Scans theme for translatable strings
   - Generates/updates `.pot` file

3. **Create Language Files** (from `.pot` template)
   - Copy `.pot` to `airlinel-theme-[LOCALE].po`
   - Translate strings in `.po` files

4. **Compile Translations**
   ```bash
   msgfmt airlinel-theme-de_DE.po -o airlinel-theme-de_DE.mo
   ```

5. **Test**
   - Change WordPress language in Settings
   - Verify correct language displays

### For Translators

1. **Open `.po` file** in translation editor (Poedit, VS Code, etc.)
2. **Translate strings** from `msgid` to `msgstr`
3. **Test translations** in WordPress admin
4. **Compile to `.mo`** file
5. **Submit pull request** with translations

## Language Manager API

### Available Methods

```php
// Get all supported languages with metadata
$languages = $airlinel_language_manager->get_supported_languages();

// Get current language code (EN, TR, DE, etc.)
$code = $airlinel_language_manager->get_current_language();

// Get WordPress locale (en_US, tr_TR, etc.)
$locale = $airlinel_language_manager->get_current_locale();

// Switch to a language
$airlinel_language_manager->switch_language('FR');

// Get native language name
$name = $airlinel_language_manager->get_language_name('TR');  // Returns 'Türkçe'

// Get English language name
$en_name = $airlinel_language_manager->get_language_name_english('DE');  // Returns 'German'

// Check if language is RTL
$is_rtl = $airlinel_language_manager->is_rtl('AR');  // Returns true

// Load translations for current language
$airlinel_language_manager->load_translations();

// Get text domain
$domain = $airlinel_language_manager->get_text_domain();  // Returns 'airlinel-theme'

// Get translated string
$translated = $airlinel_language_manager->get_translated_string('Book Now');

// Get all translations as array
$all = $airlinel_language_manager->get_all_translations();
```

## Key Features

### 1. WordPress Native i18n
- Uses WordPress built-in translation functions
- Compatible with WordPress language switching
- Works with language plugins

### 2. Comprehensive Language Support
- 12 languages including RTL (Arabic)
- Full locale support (e.g., en_US, de_DE)
- Language metadata (native names, English names)

### 3. Translation Management
- `.pot` template for consistent translations
- `.po` files for human-readable translations
- `.mo` compiled files for WordPress runtime

### 4. Easy Integration
- Single initialization in `functions.php`
- Global access to Language Manager
- Automatic text domain loading

### 5. Flexible Language Switching
- WordPress admin interface
- Programmatic switching via API
- Persistent language setting in database

## Translation Statistics

- **Total Strings:** 150+
- **Languages Supported:** 12
- **Main Areas:**
  - Page headers and titles
  - Form labels and placeholders
  - Button text
  - Navigation items
  - Footer content
  - Error messages
  - Status messages

## Technical Details

### Text Domain
- **Domain Name:** `airlinel-theme`
- **Used in all i18n functions:** `__('text', 'airlinel-theme')`
- **Location:** `languages/` directory

### File Formats

**Translation Template (`.pot`):**
- Contains original strings only
- UTF-8 encoded
- Updated when new strings are added

**Portable Object (`.po`):**
- Human-readable translation format
- Source code and translations side-by-side
- Easy to edit with text editors or translation tools
- Used as source for compilation

**Machine Object (`.mo`):**
- Binary compiled format
- Optimized for WordPress runtime
- Faster to load than `.po` files
- Generated from `.po` files via `msgfmt`

### Localization Support

Each locale includes:
- Language code (e.g., 'TR')
- WordPress locale (e.g., 'tr_TR')
- Native language name (e.g., 'Türkçe')
- English language name (e.g., 'Turkish')
- RTL flag (for Arabic)

## Best Practices

1. **Always wrap user-facing strings** with i18n functions
2. **Use appropriate functions:**
   - `__()` for returning strings
   - `_e()` for echoing strings
   - `esc_html__()` for HTML output
   - `esc_attr__()` for HTML attributes
3. **Keep strings atomic** - translate concepts, not paragraphs
4. **Test with all languages** - verify layout compatibility
5. **Provide translator context** - comment on complex strings
6. **Maintain consistent terminology** throughout all translations
7. **Regular updates** - keep translations current with code changes

## Troubleshooting

### Translations Not Showing

1. Check text domain is `'airlinel-theme'`
2. Verify `.mo` file exists in `languages/` folder
3. Confirm WordPress language is set correctly
4. Clear WordPress cache (if using cache plugin)
5. Check file permissions (`.mo` must be readable)

### String Not Translating

1. Verify exact string matches in `.po` file
2. Regenerate `.mo` file from `.po`
3. Clear WordPress cache
4. Check text domain parameter matches

### Language Not Listed

1. Verify locale code in Language Manager class
2. Check WordPress language pack is installed
3. Clear WordPress cache
4. Restart web server if necessary

## Performance Notes

- **MO Files:** Binary format optimized for performance
- **Caching:** WordPress automatically caches translations
- **Lazy Loading:** Only current language translations are loaded
- **File Size:** Minimal overhead for language support

## Contributing Translations

To contribute translations:

1. Fork the repository
2. Copy `.pot` template to `airlinel-theme-[LOCALE].po`
3. Translate strings in the `.po` file
4. Compile to `.mo` file
5. Test thoroughly
6. Submit pull request with clear description

See `TRANSLATION_GUIDE.md` for detailed contribution guidelines.

## Related Documentation

- **Translation Guide:** See `TRANSLATION_GUIDE.md` for comprehensive translation instructions
- **Implementation Examples:** See `I18N_IMPLEMENTATION_EXAMPLES.md` for code samples
- **WordPress i18n Codex:** https://developer.wordpress.org/plugins/internationalization/
- **GNU Gettext Manual:** https://www.gnu.org/software/gettext/manual/

## Support

For questions about the i18n system:
- Email: support@airlinel.com
- Documentation: See `docs/` folder
- Issues: Submit via project repository

## Version History

- **v1.0** (April 2026) - Initial implementation
  - 12 language support
  - Language Manager class
  - Translation files for all languages
  - Complete documentation

---

**Last Updated:** April 25, 2026
**Theme Version:** 1.0
**Status:** Production Ready
