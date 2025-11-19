# Custom Review Plugin

A WordPress plugin for managing and displaying customer reviews with a beautiful frontend layout matching your site's design.

## Features

✅ **Admin Interface**
- Add, edit, and delete reviews
- Approve/reject/pending status management
- Bulk actions (approve, reject, delete)
- Inline status updates via AJAX
- Filter reviews by status
- Full form validation

✅ **Database Management**
- Automatic table creation on activation
- Stores reviewer information, ratings, and review content
- Tracks review source (Tripadvisor, Google, Facebook, etc.)

✅ **Frontend Display**
- Beautiful shortcode for displaying reviews
- Matches your site's Bricolage Grotesque font and color scheme
- Responsive design
- 5-star rating display with filled/empty circles
- Avatar support with fallback gradient
- Vietnamese date formatting

## Installation

1. The plugin is already installed at: `/wp-content/plugins/custom-review-plugin/`

2. **Activate the plugin:**
   - Go to WordPress Admin > Plugins
   - Find "Custom Review Plugin"
   - Click "Activate"

3. **Database table will be created automatically** upon activation as `{prefix}_reviews`

## Usage

### Admin Panel

After activation, you'll see a new "Reviews" menu in WordPress Admin with a star icon.

#### Adding a Review

1. Go to **Reviews > Add New Review**
2. Fill in the form:
   - **Reviewer Name*** (required) - e.g., "Lucy EH"
   - **Location** - e.g., "Thành phố Hồ Chí Minh"
   - **Avatar URL** - Full URL to avatar image (optional, defaults to gradient)
   - **Rating*** (required) - 1 to 5 stars
   - **Review Title*** (required) - e.g., "Amazing food!"
   - **Review Content*** (required) - The full review text
   - **Review Date*** (required) - Date picker
   - **Reviewer Type** - e.g., "Couples", "Family", "Solo"
   - **Contributions** - Number like "84 đóng góp"
   - **Source** - Manual, Tripadvisor, Google, Facebook, or Website
   - **Status*** (required) - Approved, Pending, or Rejected

3. Click "Add Review" or "Update Review"

#### Managing Reviews

1. Go to **Reviews > All Reviews**
2. You'll see a list of all reviews with:
   - Reviewer name and location
   - Review title and preview
   - Star rating
   - Status (clickable to change)
   - Date
   - Edit and Delete actions

3. **Quick status change:**
   - Click on the status badge (Approved/Pending/Rejected)
   - Select new status from dropdown
   - Status updates instantly via AJAX

4. **Bulk actions:**
   - Select multiple reviews using checkboxes
   - Choose action: Approve, Set Pending, Reject, or Delete
   - Click "Apply"

5. **Filter by status:**
   - Click "All", "Approved", "Pending", or "Rejected" tabs

### Frontend Display

#### Using the Shortcode

Add reviews to any page or post using the shortcode. Reviews are displayed in a beautiful **4-column responsive slider** with auto-slide functionality!

```
[customer_reviews]
```

#### Shortcode Attributes

**Show limited number of reviews:**
```
[customer_reviews limit="8"]
```

**Show pending reviews (default is approved only):**
```
[customer_reviews status="pending"]
```

**Change number of columns (default is 4):**
```
[customer_reviews columns="3"]
```

**Combine attributes:**
```
[customer_reviews limit="12" status="approved" columns="4"]
```

#### Slider Features

✨ **Automatic Responsive Behavior:**
- Desktop (>1200px): 4 columns
- Laptop (992px-1200px): 3 columns
- Tablet (768px-992px): 2 columns
- Mobile (<768px): 1 column

✨ **Interactive Controls:**
- Previous/Next arrow buttons
- Navigation dots at the bottom
- Auto-slide every 5 seconds
- Pause on hover
- Smooth animations

✨ **Touch-Friendly:**
- Responsive on all screen sizes
- Mobile-optimized navigation

#### Where to Add the Shortcode

**Option 1: Add to Homepage**
1. Go to **Pages > Home** (or your homepage)
2. In the editor, add the shortcode: `[customer_reviews]`
3. Click "Update"

**Option 2: Add to a Widget**
1. Go to **Appearance > Widgets**
2. Add a "Text" or "HTML" widget to your desired widget area
3. Paste the shortcode: `[customer_reviews]`
4. Save

**Option 3: Add to a Custom Template**
In your theme file (e.g., `page-home.php`), add:
```php
<?php echo do_shortcode('[customer_reviews]'); ?>
```

## Frontend Layout

The shortcode displays reviews in a beautiful **slider with 4 columns**:

### Card Design
Each review card features:
- **Circular avatar** (50px) with gradient fallback
- **Reviewer name** and location at the top
- **5-star rating** with filled (green #00A86B) and empty (light green #C8E6C9) circles
- **Review title** in bold (max 2 lines with ellipsis)
- **Date and reviewer type** (e.g., "thg 3 2025 • Couples")
- **Review content** (max 5 lines with ellipsis)
- **Footer text** with Vietnamese date formatting
- **Card hover effect** - elevates with shadow on hover
- **Clean white background** with subtle border and shadow

### Slider Features
- **4 columns on desktop** (automatically adjusts to 3/2/1 on smaller screens)
- **Left/Right arrow navigation** with red accent color (#CD0000)
- **Dot navigation** at the bottom for quick page jumping
- **Auto-slide** every 5 seconds (pauses on hover)
- **Smooth transitions** between slides
- **Fully responsive** layout

## Styling

The plugin automatically uses your site's styling:
- **Font**: Bricolage Grotesque (already loaded by your theme)
- **Colors**: Matches your site's color scheme
- **Responsive**: Works on all screen sizes

Colors used:
- Star filled: `#00A86B` (green)
- Star empty: `#C8E6C9` (light green)
- Text: `#000` (black) and `#636363` (gray)

## Database Structure

Table name: `{prefix}_reviews`

| Field | Type | Description |
|-------|------|-------------|
| id | INT(11) | Auto increment primary key |
| reviewer_name | VARCHAR(255) | Name of reviewer |
| reviewer_location | VARCHAR(255) | Location/city |
| reviewer_avatar | TEXT | URL to avatar image |
| rating | INT(1) | 1-5 star rating |
| review_title | VARCHAR(500) | Review headline |
| review_content | TEXT | Full review text |
| review_date | DATE | Date of review |
| reviewer_type | VARCHAR(100) | Couples, Family, Solo, etc. |
| contributions | INT(11) | Number of contributions |
| source | VARCHAR(50) | manual, Tripadvisor, Google, etc. |
| status | ENUM | pending, approved, rejected |
| created_at | DATETIME | Auto timestamp |
| updated_at | DATETIME | Auto updated timestamp |

## Example Reviews to Add

Based on the image provided, here's a sample review:

**Review 1:**
- Reviewer Name: Lucy EH
- Location: Thành phố Hồ Chí Minh
- Avatar URL: (leave empty for gradient)
- Rating: 5 stars
- Review Title: Amazing food!
- Review Content: We came for the first time today and the food was delicious! We will definitely come again. The pizza was so tasty as was the ravioli we ordered. Efficient and friendly waiting staff. Lovely ambience too.
- Review Date: 2025-03-15
- Reviewer Type: Couples
- Contributions: 84
- Source: Tripadvisor
- Status: Approved

**Review 2:**
- Reviewer Name: Sue M
- Location: Plymouth
- Avatar URL: (leave empty for gradient)
- Rating: 5 stars
- Review Title: Best pizza in town!
- Review Content: Absolutely fantastic experience! The pizzas are authentic Italian style with fresh ingredients. The staff is very attentive and the atmosphere is cozy. Highly recommend for anyone looking for quality pizza.
- Review Date: 2025-03-10
- Reviewer Type: Family
- Contributions: 18
- Source: Google
- Status: Approved

## File Structure

```
custom-review-plugin/
├── custom-review-plugin.php       # Main plugin file
├── admin/
│   ├── admin-page.php            # Admin interface (list and form)
│   ├── admin-styles.css          # Admin CSS
│   └── admin-scripts.js          # Admin JavaScript (AJAX)
└── README.md                      # This file
```

## Troubleshooting

**Plugin not showing in admin:**
- Make sure the plugin folder is in `/wp-content/plugins/`
- Check file permissions (should be readable)
- Try deactivating and reactivating

**Database table not created:**
- Deactivate and reactivate the plugin
- Check database user has CREATE TABLE permissions

**Shortcode not displaying:**
- Make sure you have approved reviews in the database
- Check that the shortcode is exactly: `[customer_reviews]`
- Try adding a review first through admin

**Status not updating:**
- Make sure JavaScript is enabled
- Check browser console for errors
- Verify AJAX is working (check Network tab in browser dev tools)

**Styling looks different:**
- The inline CSS in shortcode matches your site's design
- If needed, you can add custom CSS in your theme's style.css

## Security Features

✅ Nonce verification for all forms
✅ Capability checks (only admins can manage reviews)
✅ SQL injection protection with prepared statements
✅ XSS protection with proper escaping
✅ CSRF protection on AJAX requests
✅ Input sanitization for all user data

## Support

For any issues or questions:
1. Check the WordPress error log at `/wp-content/debug.log`
2. Review this README file
3. Check the admin interface for any error messages

## Version

**Version**: 1.0.0
**Author**: WooPizza Team
**Requires**: WordPress 5.0+, PHP 7.4+

## Changelog

### 1.0.0 (2025-11-18)
- Initial release
- Admin interface for managing reviews
- Frontend shortcode with beautiful layout
- AJAX status updates
- Bulk actions support
- Vietnamese date formatting
- Multi-source support (Tripadvisor, Google, etc.)
