# Language & Currency System Deployment Instructions

## Compiled Files Ready for Deployment

All translation files (.mo files) have been successfully compiled from their source .po files.

**Compiled Translation Files (13 total):**
- airlinel-theme-ar.mo (30 translations)
- airlinel-theme-da_DK.mo (30 translations)
- airlinel-theme-de_DE.mo (128 translations)
- airlinel-theme-en_US.mo (129 translations)
- airlinel-theme-es_ES.mo (128 translations)
- airlinel-theme-fr_FR.mo (41 translations)
- airlinel-theme-it_IT.mo (30 translations)
- airlinel-theme-ja.mo (30 translations)
- airlinel-theme-nl_NL.mo (30 translations)
- airlinel-theme-ru_RU.mo (30 translations)
- airlinel-theme-sv_SE.mo (30 translations)
- airlinel-theme-tr_TR.mo (129 translations)
- airlinel-theme-zh_CN.mo (30 translations)

**Location:** C:\Users\guven\Desktop\airlinel.com_wpvivid\airlinel-transfer-services\languages\

## Files to Upload to Server

### 1. Theme Files (Already Updated)

Upload these to: `wp-content/themes/airlinel-transfer-services/`

- **header.php** - Updated with language dropdown and currency selector
- **page-booking.php** - All form labels wrapped with _e() translation functions
- **functions.php** - AJAX handler for language preference persistence
- **includes/class-language-settings.php** - Language initialization and locale override
- **assets/js/header-selectors.js** - JavaScript for language/currency selection
- **assets/js/booking.js** - Updated with global updateCurrencyDisplay() function

### 2. Translation Files (CRITICAL)

Upload these to: `wp-content/themes/airlinel-transfer-services/languages/`

All 13 `.mo` files from the languages directory. These are the compiled binary translation files.

## Post-Upload Steps

### Step 1: Upload Files to Server

Using FTP or file manager:

1. Upload all .mo files from `languages/` directory to server's `wp-content/themes/airlinel-transfer-services/languages/`
2. Verify all 13 .mo files are on server with correct file sizes
3. Set file permissions to 644 (readable by web server)

### Step 2: Clear WordPress Cache

In WordPress admin:

1. Navigate to **Airlinel > Theme Settings > Clear Cache**
2. Click "Clear All Cache"
3. Or use plugin cache clearing if available

### Step 3: Set Default Language (Admin)

1. Go to **Settings → General** in WordPress admin
2. Under "Site Language" select the desired language (e.g., "Türkçe")
3. Click "Save Changes"
4. Page will reload

### Step 4: Test Language Display

#### Test 1: Admin Default Language
1. Visit https://testairlinel.londonos.uk/ (with your selected language)
2. Check header elements:
   - "Book Now" button text
   - Language dropdown (should show selected language)
   - Currency dropdown
3. Check booking form labels:
   - "Full Name", "Email Address", "Phone Number", etc. should be in selected language

#### Test 2: URL Parameter Override
1. Visit https://testairlinel.londonos.uk/?lang=tr_TR
2. Verify all text switches to Turkish
3. Visit https://testairlinel.londonos.uk/?lang=de_DE  
4. Verify all text switches to German
5. Verify without ?lang parameter, it shows admin's default language

#### Test 3: Language Dropdown Selection
1. Visit homepage
2. Click language dropdown (header area)
3. Select a different language from the list
4. Page should reload with that language
5. Content should persist in localStorage (reload page - stays in selected language)

#### Test 4: Currency Switching
1. With booking form open, change currency dropdown
2. Verify:
   - All vehicle prices update to selected currency
   - Form display price updates
   - Payment button shows correct amount in selected currency
3. Try different currency combinations with different languages

## Troubleshooting

### Issue: Translations still not showing after upload

**Solution:**
1. Verify .mo files are in correct directory: `wp-content/themes/airlinel-transfer-services/languages/`
2. Check file permissions: should be readable (644)
3. Clear browser cache (Ctrl+Shift+Del or Cmd+Shift+Del)
4. Clear WordPress cache via admin panel
5. Verify site language is set in Settings → General
6. Check browser console (F12) for JavaScript errors

### Issue: Only some strings are translated

**Solution:**
1. Check that _e() function is used with correct textdomain: `_e('text', 'airlinel-theme')`
2. Verify .po file contains the translation for that string
3. Verify .mo file was compiled from updated .po file

### Issue: Language dropdown not appearing

**Solution:**
1. Check header.php includes the dropdown HTML (id="language-dropdown")
2. Verify header-selectors.js is loaded (check browser console Network tab)
3. Check JavaScript console (F12) for errors in initLanguageSelector()

### Issue: Currency not updating prices

**Solution:**
1. Verify header-selectors.js is loaded
2. Check booking.js has global updateCurrencyDisplay() function
3. Verify currencyChanged custom event is being dispatched
4. Check browser console for JavaScript errors

## File Structure Reference

```
wp-content/themes/airlinel-transfer-services/
├── header.php (contains language/currency dropdowns)
├── page-booking.php (booking form with _e() wrapped strings)
├── functions.php (AJAX handlers for language saving)
├── assets/
│   ├── js/
│   │   ├── header-selectors.js (language/currency selection logic)
│   │   └── booking.js (price formatting and currency display)
├── includes/
│   └── class-language-settings.php (language initialization)
└── languages/ (CRITICAL - must be uploaded)
    ├── airlinel-theme-ar.mo
    ├── airlinel-theme-da_DK.mo
    ├── airlinel-theme-de_DE.mo
    ├── airlinel-theme-en_US.mo
    ├── airlinel-theme-es_ES.mo
    ├── airlinel-theme-fr_FR.mo
    ├── airlinel-theme-it_IT.mo
    ├── airlinel-theme-ja.mo
    ├── airlinel-theme-nl_NL.mo
    ├── airlinel-theme-ru_RU.mo
    ├── airlinel-theme-sv_SE.mo
    ├── airlinel-theme-tr_TR.mo
    └── airlinel-theme-zh_CN.mo
```

## Key Implementation Details

### Language Selection Flow
1. User clicks language in dropdown → header-selectors.js calls switchLanguage()
2. Language saved to localStorage and WordPress option via AJAX
3. Language saved to URL parameter (?lang=tr_TR)
4. Page reloads with new language
5. class-language-settings.php loads correct .mo file
6. WordPress displays strings in selected language

### Currency Selection Flow
1. User selects currency from dropdown → header-selectors.js calls switchCurrency()
2. Currency saved to localStorage
3. currencyChanged custom event dispatched
4. booking.js's updateCurrencyDisplay() receives event
5. All vehicle prices recalculated in selected currency
6. Form display and payment button updated

### Translation Function Usage
All user-facing strings use:
```php
<?php _e('String to translate', 'airlinel-theme'); ?>
```

This wraps strings for translation. WordPress loads the appropriate .mo file and displays the translation.

## Verification Checklist

After deployment, verify:

- [ ] All 13 .mo files uploaded to languages/ directory
- [ ] File permissions set to 644
- [ ] WordPress cache cleared
- [ ] Default language set in Settings → General
- [ ] Homepage displays in default language
- [ ] ?lang=tr_TR parameter switches language
- [ ] Language dropdown available and functional
- [ ] Currency dropdown available and functional
- [ ] Prices update when currency changes
- [ ] Form labels display in correct language
- [ ] No JavaScript errors in console (F12)
- [ ] No PHP warnings in server logs

## Support

If you encounter any issues during deployment:

1. Check file permissions and directory structure
2. Review browser console (F12) for JavaScript errors
3. Check server error logs for PHP warnings
4. Verify .po files match .mo file content
5. Ensure WordPress cache is completely cleared

All files are ready for deployment.
