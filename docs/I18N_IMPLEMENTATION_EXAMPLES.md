# i18n Implementation Examples

This document shows practical examples of how to use i18n functions in Airlinel theme templates and code.

## Basic Examples

### Example 1: Simple Header Title

**Before (hardcoded):**
```php
<h1>Book Your Airport Transfer</h1>
```

**After (with i18n):**
```php
<h1><?php _e('Book Your Airport Transfer', 'airlinel-theme'); ?></h1>
```

### Example 2: Form Label

**Before:**
```php
<label>Full Name</label>
<input type="text" name="full_name">
```

**After:**
```php
<label><?php _e('Full Name', 'airlinel-theme'); ?></label>
<input type="text" name="full_name">
```

### Example 3: Button Text

**Before:**
```php
<button class="btn">Book Now</button>
```

**After:**
```php
<button class="btn"><?php _e('Book Now', 'airlinel-theme'); ?></button>
```

### Example 4: Dynamic String with Variables

**Before:**
```php
<?php
$price = 50;
echo "Your price is £" . $price;
?>
```

**After:**
```php
<?php
$price = 50;
printf(
    __('Your price is £%s', 'airlinel-theme'),
    number_format($price, 2)
);
?>
```

## Advanced Examples

### Example 5: Plural Forms

**Use case:** "1 vehicle available" vs "5 vehicles available"

```php
<?php
$count = get_available_vehicles_count();
echo sprintf(
    _n(
        '%d vehicle available',
        '%d vehicles available',
        $count,
        'airlinel-theme'
    ),
    $count
);
?>
```

In your .po file:
```
msgid "%d vehicle available"
msgid_plural "%d vehicles available"
msgstr[0] "1 aracı mevcuttur"
msgstr[1] "%d araç mevcuttur"
```

### Example 6: Escaped Output (HTML safe)

**Before:**
```php
<input placeholder="Enter your location">
```

**After:**
```php
<input placeholder="<?php echo esc_attr__('Enter your location', 'airlinel-theme'); ?>">
```

### Example 7: HTML Content with wp_kses_post()

```php
<?php
$description = __('Welcome to our <strong>premium</strong> service.', 'airlinel-theme');
echo wp_kses_post($description);
?>
```

### Example 8: Context-Aware Translations

When the same word has different meanings in different contexts:

```php
<?php
// "Post" as in blog post
echo _x('Post', 'noun-blog', 'airlinel-theme');

// "Post" as in mail/send
echo _x('Post', 'verb-send', 'airlinel-theme');
?>
```

In your .po file:
```
msgctxt "noun-blog"
msgid "Post"
msgstr "Yazı"

msgctxt "verb-send"
msgid "Post"
msgstr "Gönder"
```

### Example 9: Escaped Plurals

```php
<?php
$count = 3;
printf(
    esc_html(
        _n(
            'Booked %d transfer',
            'Booked %d transfers',
            $count,
            'airlinel-theme'
        )
    ),
    $count
);
?>
```

## Real Template Examples

### Example 10: Complete Booking Form

**File:** `page-booking.php`

```php
<?php
get_header();
?>

<div class="booking-container">
    <h1><?php _e('Book Your Transfer', 'airlinel-theme'); ?></h1>
    
    <form id="booking-form">
        <fieldset>
            <legend><?php _e('Trip Details', 'airlinel-theme'); ?></legend>
            
            <div class="form-group">
                <label for="pickup">
                    <?php _e('Pickup Location', 'airlinel-theme'); ?>
                </label>
                <input 
                    type="text" 
                    id="pickup" 
                    name="pickup"
                    placeholder="<?php echo esc_attr__('Airport, hotel, or address', 'airlinel-theme'); ?>"
                    required
                >
            </div>
            
            <div class="form-group">
                <label for="destination">
                    <?php _e('Destination', 'airlinel-theme'); ?>
                </label>
                <input 
                    type="text" 
                    id="destination" 
                    name="destination"
                    placeholder="<?php echo esc_attr__('Where are you going?', 'airlinel-theme'); ?>"
                    required
                >
            </div>
            
            <div class="form-group">
                <label for="pickup-date">
                    <?php _e('Pickup Date', 'airlinel-theme'); ?>
                </label>
                <input type="date" id="pickup-date" name="pickup_date" required>
            </div>
            
            <div class="form-group">
                <label for="pickup-time">
                    <?php _e('Pickup Time', 'airlinel-theme'); ?>
                </label>
                <input type="time" id="pickup-time" name="pickup_time" required>
            </div>
        </fieldset>
        
        <fieldset>
            <legend><?php _e('Passenger Details', 'airlinel-theme'); ?></legend>
            
            <div class="form-group">
                <label for="full-name">
                    <?php _e('Full Name', 'airlinel-theme'); ?> *
                </label>
                <input type="text" id="full-name" name="full_name" required>
            </div>
            
            <div class="form-group">
                <label for="email">
                    <?php _e('Email Address', 'airlinel-theme'); ?> *
                </label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="phone">
                    <?php _e('Phone Number', 'airlinel-theme'); ?> *
                </label>
                <input type="tel" id="phone" name="phone" required>
            </div>
        </fieldset>
        
        <div class="form-agreement">
            <input type="checkbox" id="terms" name="agree_terms" required>
            <label for="terms">
                <?php _e('I agree to the Terms and Conditions', 'airlinel-theme'); ?>
            </label>
        </div>
        
        <button type="submit" class="btn-primary">
            <?php _e('Confirm Booking', 'airlinel-theme'); ?>
        </button>
    </form>
</div>

<?php get_footer(); ?>
```

### Example 11: Fleet Display

**File:** `page-fleet.php`

```php
<?php
get_header();

$fleet = get_posts(array(
    'post_type' => 'fleet',
    'posts_per_page' => -1
));
?>

<div class="fleet-section">
    <h1><?php _e('Our Premium Vehicles', 'airlinel-theme'); ?></h1>
    
    <?php if ($fleet) : ?>
        <div class="fleet-grid">
            <?php foreach ($fleet as $vehicle) : ?>
                <div class="vehicle-card">
                    <div class="vehicle-image">
                        <?php echo get_the_post_thumbnail($vehicle->ID, 'large'); ?>
                    </div>
                    
                    <h3><?php echo esc_html($vehicle->post_title); ?></h3>
                    
                    <div class="vehicle-info">
                        <p class="status">
                            <span class="badge">
                                <?php _e('Available Now', 'airlinel-theme'); ?>
                            </span>
                        </p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else : ?>
        <p class="no-vehicles">
            <?php _e('No vehicles in fleet at this time.', 'airlinel-theme'); ?>
        </p>
    <?php endif; ?>
    
    <section class="fleet-features">
        <h2><?php _e('Premium Selection', 'airlinel-theme'); ?></h2>
        
        <div class="features-grid">
            <div class="feature">
                <h4><?php _e('Fully Insured', 'airlinel-theme'); ?></h4>
                <p><?php _e('Complete coverage on all vehicles with comprehensive insurance.', 'airlinel-theme'); ?></p>
            </div>
            
            <div class="feature">
                <h4><?php _e('Expert Chauffeurs', 'airlinel-theme'); ?></h4>
                <p><?php _e('Professionally trained drivers with extensive experience.', 'airlinel-theme'); ?></p>
            </div>
            
            <div class="feature">
                <h4><?php _e('Always On Time', 'airlinel-theme'); ?></h4>
                <p><?php _e('Punctuality guaranteed with real-time tracking.', 'airlinel-theme'); ?></p>
            </div>
            
            <div class="feature">
                <h4><?php _e('Eco-Conscious', 'airlinel-theme'); ?></h4>
                <p><?php _e('Modern vehicles with reduced emissions.', 'airlinel-theme'); ?></p>
            </div>
        </div>
    </section>
</div>

<?php get_footer(); ?>
```

### Example 12: Cities Archive

**File:** `page-cities.php`

```php
<?php
get_header();
?>

<div class="cities-section">
    <div class="section-header">
        <h1><?php _e('Cities', 'airlinel-theme'); ?></h1>
        <p class="subtitle">
            <?php _e('Book airport transfers in London, Manchester, Istanbul and Antalya. Fixed rates, professional drivers, real-time flight tracking — available 24/7.', 'airlinel-theme'); ?>
        </p>
    </div>
    
    <div class="cities-grid">
        <?php
        $cities = get_terms(array(
            'taxonomy' => 'category',
            'hide_empty' => false
        ));
        
        if (!empty($cities) && !is_wp_error($cities)) :
            foreach ($cities as $city) :
                $count = $city->count;
                ?>
                <div class="city-card">
                    <div class="city-info">
                        <h3><?php echo esc_html($city->name); ?></h3>
                        <p class="stats">
                            <span class="stat">
                                <strong><?php echo $count; ?></strong>
                                <?php _e('Available', 'airlinel-theme'); ?>
                            </span>
                            <span class="rating">
                                <strong>4.9</strong>
                                <?php _e('Rated', 'airlinel-theme'); ?>
                            </span>
                        </p>
                        <p class="features">
                            <?php _e('Mercedes-Benz Fleet', 'airlinel-theme'); ?> &nbsp;•&nbsp;
                            <?php _e('Meet & Greet', 'airlinel-theme'); ?> &nbsp;•&nbsp;
                            <?php _e('Flight Tracking', 'airlinel-theme'); ?>
                        </p>
                    </div>
                </div>
            <?php
            endforeach;
        else :
        ?>
            <p class="no-cities">
                <?php _e('No cities found. Add blog categories via WordPress Admin → Posts → Categories.', 'airlinel-theme'); ?>
            </p>
        <?php endif; ?>
    </div>
    
    <div class="cta-section">
        <h2><?php _e('Ready to Book Your Transfer?', 'airlinel-theme'); ?></h2>
        <p><?php _e('Select your city above or book directly — any destination, any time.', 'airlinel-theme'); ?></p>
    </div>
</div>

<?php get_footer(); ?>
```

## Common Patterns

### Pattern 1: Checking if Translation Exists

```php
<?php
$translated = __('Book Now', 'airlinel-theme');
if ($translated === 'Book Now') {
    // Translation not found, using original string
    echo 'Translation missing!';
} else {
    // Translation exists
    echo $translated;
}
?>
```

### Pattern 2: Conditional Strings Based on Language

```php
<?php
global $airlinel_language_manager;
$current_lang = $airlinel_language_manager->get_current_language();

if ($current_lang === 'AR') {
    $direction = 'rtl';
} else {
    $direction = 'ltr';
}
?>
<div dir="<?php echo esc_attr($direction); ?>">
    <?php _e('Your content here', 'airlinel-theme'); ?>
</div>
```

### Pattern 3: Language Switcher

```php
<?php
global $airlinel_language_manager;
$languages = $airlinel_language_manager->get_supported_languages();
$current = $airlinel_language_manager->get_current_language();
?>

<div class="language-selector">
    <?php foreach ($languages as $code => $lang) : ?>
        <a 
            href="<?php echo esc_url(add_query_arg('lang', $code)); ?>"
            class="<?php echo ($current === $code) ? 'active' : ''; ?>"
            title="<?php echo esc_attr($lang['name_native']); ?>"
        >
            <?php echo esc_html($lang['name_native']); ?>
        </a>
    <?php endforeach; ?>
</div>
```

## Best Practices

1. **Always use the same text domain:** `'airlinel-theme'`
2. **Wrap all user-facing strings:** Don't leave hardcoded text
3. **Use esc_html__() for HTML output:** Prevents XSS vulnerabilities
4. **Use esc_attr__() for HTML attributes:** Safe for attribute context
5. **Keep strings atomic:** Translate single concepts, not paragraphs
6. **Use placeholders for variables:** Don't concatenate variables
7. **Provide context for translators:** Use comments above strings
8. **Test with all languages:** Verify layout doesn't break with longer translations

## Testing Your i18n Implementation

### Verify Strings are Wrapped

```bash
# Search for unwrapped strings (example - adjust pattern as needed)
grep -r "echo '" templates/ | grep -v "__\|_e\|esc_"
```

### Check Text Domain

```bash
# Verify all functions use correct text domain
grep -r "airlinel-theme" . --include="*.php" | wc -l
```

### Test String Extraction

Use Poedit to extract all strings automatically and verify nothing is missed.

---

For more information, see [TRANSLATION_GUIDE.md](./TRANSLATION_GUIDE.md)
