# Homepage Content Management System - User Guide

## Overview
The Homepage Content Management system allows admins to control which sections appear on the homepage and customize their content without editing code.

## Accessing the Management Interface

1. Go to WordPress Admin Dashboard
2. Click **Settings** in the left sidebar
3. Click **Homepage Content**

## Managing Sections

### Toggling Section Visibility

Each section can be shown or hidden:

1. Click the tab for the section you want to manage
2. Check or uncheck "Show this section on homepage"
3. Click **Save Changes**

When unchecked, that section will not appear on the homepage.

### Customizing Section Content

1. Click the section tab
2. Use the text editor to enter custom content
3. You can use:
   - Formatted text
   - HTML tags
   - Links
   - Images (via media uploader)
   - Lists and formatting

4. Leave empty to use default content
5. Click **Save Changes**

## Available Sections

### 1. Featured Routes
- **Purpose:** Highlight popular travel routes
- **Default:** Shows major airport routes (Heathrow, Gatwick, etc.)
- **Location:** Main services section on homepage

### 2. Customer Testimonials  
- **Purpose:** Display customer reviews
- **Default:** 3-4 sample 5-star testimonials
- **Location:** Travel Intelligence/Blog section

### 3. Service Highlights
- **Purpose:** Showcase key features
- **Default:** 8 service features (24/7 support, professional drivers, etc.)
- **Location:** "Why Choose Airlinel" section

### 4. Trust Signals
- **Purpose:** Build credibility with trust badges
- **Default:** Licensed, insured, trusted by thousands, 5-star rated
- **Location:** Partners & Drivers section

### 5. Special Offers
- **Purpose:** Promote discounts and deals
- **Default:** Current promotions (10% off returns, free cancellation, etc.)
- **Location:** Call-to-Action section

### 6. Fleet Showcase
- **Purpose:** Display available vehicles
- **Default:** Auto-pulls from fleet vehicles in WordPress
- **Location:** Services grid section
- **Note:** Automatically displays vehicles added as "Fleet" custom post type

### 7. Booking Call-to-Action
- **Purpose:** Primary booking prompt
- **Default:** "Book Your Transfer" messaging
- **Location:** Top hero section of homepage

### 8. FAQ Section
- **Purpose:** Answer common questions
- **Default:** 5 common questions about transfers
- **Location:** Cities/FAQ section

## Resetting to Defaults

To restore all sections to their default content and visibility:

1. Scroll to the bottom of the page
2. Click **Reset to Defaults**
3. Confirm the action
4. All sections will be set to visible with default content

**Note:** Custom content is not deleted, only visibility is reset.

## Tips & Best Practices

### For Custom Content
- Keep text concise and scannable
- Use formatting (bold, lists) for readability
- Include links where appropriate
- Test on mobile devices

### Section Coordination
- Disable sections that don't apply to your current offer
- Keep trust signals visible during off-peak periods
- Highlight special offers when they're active
- Show all sections during peak booking periods

### Content Updates
- Update special offers regularly
- Refresh testimonials with new customer feedback
- Keep service highlights current
- Update fleet information when vehicles change

## Troubleshooting

### Changes Not Appearing
1. Clear browser cache (Ctrl+F5 or Cmd+Shift+R)
2. Check that "Save Changes" was clicked
3. Verify the section visibility checkbox is checked

### Custom Content Not Showing
1. Ensure the section visibility is enabled
2. Check that content is not empty in the editor
3. Verify HTML syntax if using custom markup
4. Clear WordPress cache if using a caching plugin

### Missing Sections
1. Refresh the admin page
2. Check WordPress admin for errors
3. Verify no custom code is blocking sections

## Technical Details

### Database Storage
Each section uses WordPress options:
- `airlinel_homepage_section_{id}_visible` - Boolean (1/0)
- `airlinel_homepage_section_{id}_content` - Text with HTML

### Section IDs (for developers)
- `featured_routes`
- `customer_testimonials`
- `service_highlights`
- `trust_signals`
- `special_offers`
- `fleet_showcase`
- `booking_cta`
- `faq_section`

### Using in Code
```php
$homepage_mgr = new Airlinel_Homepage_Manager();

// Check visibility
if ($homepage_mgr->get_section_visibility('featured_routes')) {
    // Section is visible
}

// Get custom content
$content = $homepage_mgr->get_section_content('featured_routes');

// Get all sections
$sections = $homepage_mgr->get_all_sections();

// Toggle visibility
$homepage_mgr->set_section_visibility('featured_routes', false);
```

## Support

For issues or questions about the Homepage Management system:
1. Check the WordPress error log
2. Verify admin permissions (requires manage_options capability)
3. Contact development team with details

---

**Version:** 1.0 (Task 3.2)  
**Last Updated:** April 25, 2026
