# Airlinel Page Management Guide (Task 3.7)

## Overview

Task 3.7 implements editable content pages with SEO optimization for the Airlinel airport transfer platform. This includes three main page templates (About, Services, Contact) with regional site support and comprehensive SEO features.

## Features Implemented

### 1. Three Editable Page Templates

#### About Us Page (`/page-about.php`)
- **Template Name:** About Us
- **Key Sections:**
  - Hero section with company description
  - Mission and values section
  - Trust indicators (years in business, customers served, fleet size, daily rides)
  - Company history/background
  - Trust signals (certifications, safety, availability, ratings)
  - Call-to-action buttons

- **Editable Content:**
  - Company description
  - Mission statement
  - Company history
  - Trust indicators (configured in admin)
  - Page content area (via WordPress editor)

#### Services Page (`/page-services.php`)
- **Template Name:** Services
- **Content:**
  - Service grid with 9 service cards
  - Each service card has image, title, description, and CTA link
  - Description section (editable via WordPress editor)
  - CTA section encouraging bookings
  - Responsive design (1-column mobile, 3-column desktop)

#### Contact Us Page (`/page-contact.php`)
- **Template Name:** Contact Us
- **Key Features:**
  - Contact form (name, email, phone, subject, message)
  - Contact information display (phone, email, address)
  - Business hours display
  - Open/closed status indicator
  - Google Maps embedded location
  - FAQ section with collapsible items
  - Call-to-action section

- **Contact Form Features:**
  - Server-side form validation
  - AJAX submission (no page reload)
  - Email notification to admin
  - Automatic confirmation email to user
  - Security nonce verification
  - HTML email formatting

### 2. Page Manager Class (`/includes/class-page-manager.php`)

The `Airlinel_Page_Manager` class provides helper methods for managing page content:

#### Static Methods for Content Retrieval

**Contact Information:**
```php
// Get all contact info
$contact = Airlinel_Page_Manager::get_contact_info();
// Returns: ['phone' => '...', 'email' => '...', 'address' => '...']

// Get individual items
$phone = Airlinel_Page_Manager::get_office_phone();
$email = Airlinel_Page_Manager::get_office_email();
$address = Airlinel_Page_Manager::get_office_address();
```

**Business Hours:**
```php
// Get business hours array
$hours = Airlinel_Page_Manager::get_business_hours();
// Returns: ['monday' => ['open' => '06:00', 'close' => '23:00'], ...]

// Get formatted business hours string
$formatted = Airlinel_Page_Manager::get_formatted_business_hours();

// Check if currently open
$is_open = Airlinel_Page_Manager::is_open_now();
```

**Company Information:**
```php
// Get company content
$description = Airlinel_Page_Manager::get_company_description();
$mission = Airlinel_Page_Manager::get_company_mission();
$history = Airlinel_Page_Manager::get_company_history();
```

**Trust Indicators:**
```php
$indicators = Airlinel_Page_Manager::get_trust_indicators();
// Returns: [
//   'years_in_business' => 15,
//   'customers_served' => '50,000',
//   'fleet_size' => 150,
//   'daily_rides' => '500'
// ]
```

**SEO Meta Data:**
```php
$seo = Airlinel_Page_Manager::get_seo_meta($post_id);
// Returns: [
//   'seo_title' => '...',
//   'seo_description' => '...',
//   'focus_keyword' => '...',
//   'og_image' => '...',
//   'canonical_url' => '...'
// ]
```

### 3. SEO Meta Boxes

Every page and post now has an SEO Information meta box with fields for:

- **SEO Title** (50-60 characters recommended)
  - Character counter shows current length
  - Displayed in browser title and search results

- **Meta Description** (150-160 characters recommended)
  - Character counter shows current length
  - Appears below page title in search results

- **Focus Keyword**
  - Primary keyword for SEO optimization
  - Helps structure content around target keyword

- **Open Graph Image**
  - URL to image used for social media sharing
  - Recommended size: 1200x630px
  - Improves click-through from social platforms

- **Canonical URL**
  - Prevents duplicate content issues
  - Defaults to page URL if left empty
  - Use for redirect consolidation

#### How to Use SEO Meta Boxes

1. Navigate to Edit Page/Post in WordPress admin
2. Scroll down to "SEO Information" section
3. Fill in SEO fields:
   - Keep title 50-60 characters
   - Keep description 150-160 characters
   - Set focus keyword to main topic
   - Add OG image URL for social sharing
   - Leave canonical URL empty unless consolidating duplicates
4. Publish/Update page
5. SEO meta tags automatically appear in page `<head>`

### 4. Page Content Settings Admin Panel

Access via **Settings > Page Content Settings**

This admin panel allows you to manage:

#### Contact Information
- Phone number (displayed on contact page)
- Email address (receives contact form submissions)
- Address (displayed on contact page and Google Maps)

#### Company Information
- Company description (About page hero section)
- Mission statement (About page mission section)
- Company history (About page history section)

#### Trust Indicators
- Years in business
- Customers served
- Fleet size
- Daily rides average

#### Business Hours
- Set opening/closing times for each day
- Used to display hours on contact page
- Used to show "Open/Closed" status

#### Regional Site Support

If this is a regional site (AIRLINEL_MAIN_SITE_URL defined), contact information and hours are stored per-site, allowing each regional site to have its own:
- Phone number
- Email address
- Address
- Business hours

If not a regional site, defaults are used.

### 5. Contact Form Handler

The contact form on the Contact page includes:

#### Frontend Features
- Responsive form layout
- Field validation (client-side and server-side)
- AJAX submission (no page reload)
- Loading state on submit button
- Success/error message display
- Auto-hide success messages after 5 seconds
- Accessible form fields with labels

#### Backend Features
- AJAX handler: `wp_ajax_airlinel_submit_contact_form`
- Security nonce verification
- Input sanitization and validation
- Email to site admin with subject line
- Automatic confirmation email to user
- Optional logging of submissions

#### Email Sent To
- **Admin Email:** Receives all contact form submissions
- **User Email:** Receives confirmation that message was received

#### Form Fields
- Name (required, text)
- Email (required, email)
- Phone (optional, text)
- Subject (required, select dropdown)
- Message (required, textarea)

#### Subject Options
- Booking Inquiry
- General Question
- Complaint or Feedback
- Partnership Opportunity
- Other

### 6. Styling and Layout

All pages use the Airlinel design system:

#### Color Scheme
- Primary color: `var(--primary-color)` (#CC4452)
- Dark background: `var(--dark-background-color)` (#343434)
- Light background: `var(--light-background-color)` (#FEEFEF)
- Text colors: `var(--dark-text-color)`, `var(--gray-text-color)`

#### Typography
- Heading font: `var(--font-family-heading)` (Barlow)
- Body font: `var(--font-family-body)` (Noto Sans)

#### Responsive Design
- Mobile-first approach
- Tailwind CSS classes for styling
- Full viewport responsive layouts
- Touch-friendly form fields

### 7. WordPress Integration

#### Post Type Support
SEO meta boxes are registered for:
- Pages
- Posts

#### Filter Hooks
- `wp_head` - Outputs SEO meta tags

#### Action Hooks
- `add_meta_boxes` - Registers SEO meta box
- `save_post` - Saves SEO meta data
- `admin_menu` - Registers admin pages

#### AJAX Actions
- `wp_ajax_airlinel_submit_contact_form` - Contact form handler

## Configuration

### Step 1: Create Pages in WordPress Admin

1. Go to **Pages > Add New**
2. Enter page title (e.g., "About Us")
3. Select appropriate template from "Page Attributes > Template":
   - About Us → `page-about.php`
   - Services → `page-services.php`
   - Contact Us → `page-contact.php`
4. Add content in the editor (optional for About/Contact)
5. Configure SEO fields in the "SEO Information" meta box
6. Publish the page

### Step 2: Configure Contact Information

1. Go to **Settings > Page Content Settings**
2. Fill in:
   - Phone number
   - Email address
   - Office address
3. Click "Save Changes"

The contact form will now:
- Display your phone, email, and address
- Send submissions to your email
- Use your address in Google Maps

### Step 3: Configure Company Information

1. Go to **Settings > Page Content Settings**
2. Enter:
   - Company description
   - Mission statement
   - Company history
3. Click "Save Changes"

This content appears on the About page.

### Step 4: Set Trust Indicators

1. Go to **Settings > Page Content Settings**
2. Update:
   - Years in business
   - Customers served
   - Fleet size
   - Daily rides
3. Click "Save Changes"

These statistics appear in the About page "By The Numbers" section.

### Step 5: Set Business Hours

1. Go to **Settings > Page Content Settings**
2. For each day:
   - Set opening time (e.g., 06:00)
   - Set closing time (e.g., 23:00)
3. Click "Save Changes"

Hours appear on Contact page and "Open/Closed" status updates automatically.

### Step 6: Configure SEO for Each Page

1. Edit the page in WordPress admin
2. Scroll to "SEO Information" meta box
3. Fill in:
   - SEO Title (50-60 characters)
   - Meta Description (150-160 characters)
   - Focus Keyword
   - Open Graph Image URL
   - Canonical URL (optional)
4. Save/Update page

## Regional Site Support

For regional sites (defined with `AIRLINEL_MAIN_SITE_URL`):

### Unique Settings Per Region
- Contact phone number
- Contact email address
- Office address
- Business hours

### Shared Templates
- About page template
- Contact page template
- Services page template
- SEO meta boxes

Each region's WordPress admin can configure **Settings > Page Content Settings** independently to maintain regional-specific information.

## Usage Examples

### Display Contact Information in Templates

```php
<?php
require_once get_template_directory() . '/includes/class-page-manager.php';
$page_mgr = new Airlinel_Page_Manager();
$contact = $page_mgr::get_contact_info();
?>

<p>Phone: <?php echo esc_html($contact['phone']); ?></p>
<p>Email: <?php echo esc_html($contact['email']); ?></p>
<p>Address: <?php echo esc_html($contact['address']); ?></p>
```

### Display Business Hours

```php
<?php
$hours = $page_mgr::get_business_hours();
foreach ($hours as $day => $times) {
    echo ucfirst($day) . ': ' . $times['open'] . ' - ' . $times['close'];
}
```

### Display Trust Indicators

```php
<?php
$indicators = $page_mgr::get_trust_indicators();
?>
<p><?php echo $indicators['years_in_business']; ?> years in business</p>
<p><?php echo $indicators['customers_served']; ?> happy customers</p>
```

## Customization

### Adding More SEO Fields

To add additional SEO fields:

1. Edit `/admin/page-content-settings.php` and add the field in the SEO meta box
2. Update the `airlinel_render_seo_meta_box()` function in `functions.php` to display it
3. Update `airlinel_save_seo_meta_box()` to save it
4. Update `airlinel_output_seo_meta_tags()` to output it in the page head

### Customizing Contact Form Fields

To modify contact form fields:

1. Edit `/page-contact.php` to change form fields
2. Update `/functions.php` contact form handler to validate/process new fields
3. Update email templates in the handler function

### Styling Pages

All pages use Tailwind CSS classes. To customize styling:

1. Modify Tailwind classes in the template files
2. Use CSS custom variables for colors (defined in header.php)
3. Add custom CSS to `airlinel-tailwind.css`

## Testing Checklist

After implementation, test:

- [ ] About page displays correctly with all sections
- [ ] Contact page form submits successfully
- [ ] Contact form emails are received
- [ ] Services page displays service grid
- [ ] SEO meta tags appear in page source
- [ ] Business hours update correctly
- [ ] Contact info displays on contact page
- [ ] Trust indicators display on about page
- [ ] Admin settings page saves correctly
- [ ] Mobile responsive design works
- [ ] Form validation works (client and server)
- [ ] Google Maps displays on contact page

## Troubleshooting

### Contact Form Not Sending Emails

1. Check WordPress email configuration in wp-config.php
2. Verify email address in **Settings > Page Content Settings**
3. Check WordPress error logs
4. Test with a plugin like "WP Mail Logging" to debug

### SEO Meta Tags Not Appearing

1. Verify SEO fields are filled in the page editor
2. Check page source (View Page Source) for meta tags
3. Ensure page template is assigned correctly
4. Clear browser cache if using caching plugin

### Business Hours Not Showing

1. Go to **Settings > Page Content Settings**
2. Verify all days have opening/closing times
3. Check Contact page displays hours correctly
4. Ensure regional site API is working (for regional sites)

### Admin Settings Not Saving

1. Verify you have manage_options capability
2. Check browser console for JavaScript errors
3. Verify form inputs aren't blocked by security plugins
4. Check server error logs

## File Locations

```
/includes/class-page-manager.php          Page Manager class
/admin/page-content-settings.php          Admin settings page
/page-about.php                           About page template
/page-contact.php                         Contact page template
/page-services.php                        Services page template
/functions.php                            (updated with hooks/handlers)
```

## Database Schema

### WordPress Options (wp_options)

Contact Information:
- `airlinel_contact_phone` - Phone number
- `airlinel_contact_email` - Email address
- `airlinel_contact_address` - Office address

Company Information:
- `airlinel_company_description` - Company description
- `airlinel_company_mission` - Mission statement
- `airlinel_company_history` - Company history

Trust Indicators:
- `airlinel_years_in_business` - Integer
- `airlinel_customers_served` - Integer
- `airlinel_fleet_size` - Integer
- `airlinel_daily_rides` - Integer

Business Hours:
- `airlinel_business_hours` - Serialized array of hours per day

### WordPress Post Meta (wp_postmeta)

SEO Fields (per post/page):
- `_airlinel_seo_title` - SEO page title
- `_airlinel_seo_description` - Meta description
- `_airlinel_focus_keyword` - Focus keyword
- `_airlinel_og_image` - Open Graph image URL
- `_airlinel_canonical_url` - Canonical URL

## Security Considerations

### Nonce Protection
- All forms use WordPress nonces
- AJAX endpoints verify nonces before processing
- Admin pages verify nonces on submission

### Input Sanitization
- All text inputs sanitized with `sanitize_text_field()`
- Emails sanitized with `sanitize_email()`
- URLs sanitized with `esc_url_raw()`
- HTML content sanitized with `wp_kses_post()`

### Output Escaping
- Text output escaped with `esc_html()`
- Attributes escaped with `esc_attr()`
- URLs escaped with `esc_url()`
- JavaScript escaped with `esc_js()`

### Capability Checks
- Admin pages require `manage_options` capability
- AJAX endpoints verify user capabilities
- Form handling validates user intent

## Performance

### Caching Recommendations

1. Cache admin settings page with object cache
2. Cache PageManager static method results
3. Use WordPress transients for frequently accessed data
4. Enable browser caching for static assets

### Database Queries

PageManager uses minimal queries:
- 1 query per get_option() call
- Cached meta results via WordPress meta API
- Leverages WordPress post_meta optimization

## Support and Maintenance

### Regular Tasks
- Update contact information quarterly
- Review and update trust indicators annually
- Monitor contact form submissions
- Test SEO meta tags in Google Search Console

### Monitoring
- Check admin email receives contact forms
- Monitor contact page performance metrics
- Track form submission rates
- Review page ranking in search results

## Migration from Other Systems

If migrating from another page management system:

1. Gather existing page content
2. Create WordPress pages with correct templates
3. Use admin settings page to configure company info
4. Manually set SEO fields based on existing data
5. Update internal and external links to new page URLs
6. Redirect old URLs to new pages using .htaccess or plugin

## Future Enhancements

Possible improvements:
- Add blog/news section
- Implement testimonials page with client quotes
- Add team member profiles
- Create FAQ management interface
- Add multilingual page support
- Implement page view analytics
- Add A/B testing for CTAs
- Create page version history/revisions

---

**Task Status:** Completed
**Files Modified:** functions.php, functions.php (content page additions)
**Files Created:** class-page-manager.php, page-about.php, page-contact.php, page-content-settings.php
**Date Completed:** 2026-04-25
