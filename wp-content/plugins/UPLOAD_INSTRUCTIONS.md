# How to Upload Custom Review Plugin to Production

## Quick Upload Steps

### Option 1: Upload via FTP/SFTP (Recommended)

1. **Locate the plugin folder:**
   - Local path: `/home/vietnhq/Documents/WooCommerce/WooPizza/wp-content/plugins/custom-review-plugin/`

2. **Upload to production:**
   - Production path: `/var/www/vhosts/terravivapizza.com/httpdocs/wp-content/plugins/`
   - Upload the entire `custom-review-plugin` folder

3. **Using FileZilla or similar FTP client:**
   - Connect to your server
   - Navigate to `/var/www/vhosts/terravivapizza.com/httpdocs/wp-content/plugins/`
   - Upload the `custom-review-plugin` folder
   - Make sure all files and subdirectories are uploaded:
     ```
     custom-review-plugin/
     ├── custom-review-plugin.php
     ├── README.md
     ├── INSTALLATION.md
     └── admin/
         ├── admin-page.php
         ├── admin-scripts.js
         └── admin-styles.css
     ```

4. **Set correct permissions (if needed):**
   ```bash
   chmod 755 /var/www/vhosts/terravivapizza.com/httpdocs/wp-content/plugins/custom-review-plugin
   chmod 644 /var/www/vhosts/terravivapizza.com/httpdocs/wp-content/plugins/custom-review-plugin/*.php
   chmod 644 /var/www/vhosts/terravivapizza.com/httpdocs/wp-content/plugins/custom-review-plugin/admin/*
   ```

5. **Activate the plugin:**
   - Log in to WordPress Admin (https://terravivapizza.com/wp-admin)
   - Go to Plugins
   - Find "Custom Review Plugin"
   - Click "Activate"

### Option 2: Upload via SSH/SCP (For Advanced Users)

1. **Using the tar.gz file:**
   ```bash
   # From local machine
   scp /home/vietnhq/Documents/WooCommerce/WooPizza/wp-content/plugins/custom-review-plugin.tar.gz user@terravivapizza.com:/tmp/

   # On the server
   ssh user@terravivapizza.com
   cd /var/www/vhosts/terravivapizza.com/httpdocs/wp-content/plugins/
   tar -xzf /tmp/custom-review-plugin.tar.gz
   rm /tmp/custom-review-plugin.tar.gz
   ```

2. **Activate the plugin** via WordPress Admin

### Option 3: Upload via WordPress Admin (If ZIP file is available)

1. **Create a ZIP file:**
   - The tar.gz file is already created at:
     `/home/vietnhq/Documents/WooCommerce/WooPizza/wp-content/plugins/custom-review-plugin.tar.gz`
   - You may need to convert it to .zip format for WordPress upload

2. **Upload via WordPress:**
   - Log in to WordPress Admin
   - Go to Plugins > Add New
   - Click "Upload Plugin"
   - Choose the ZIP file
   - Click "Install Now"
   - Click "Activate Plugin"

## After Activation

1. **Verify plugin is active:**
   - You should see a new "Reviews" menu in WordPress Admin (with a star icon)

2. **Add a test review:**
   - Go to Reviews > Add New Review
   - Fill in sample data (see INSTALLATION.md for examples)
   - Click "Add Review"

3. **Add shortcode to homepage:**
   - Go to Pages > Home
   - Add `[customer_reviews]` to the content
   - Update the page
   - Visit your homepage to see the reviews

## Troubleshooting

**If you see "Plugin could not be activated" error:**

1. Check that ALL files were uploaded:
   ```bash
   # On server, run:
   ls -la /var/www/vhosts/terravivapizza.com/httpdocs/wp-content/plugins/custom-review-plugin/
   ls -la /var/www/vhosts/terravivapizza.com/httpdocs/wp-content/plugins/custom-review-plugin/admin/
   ```

2. Check file permissions:
   ```bash
   # Files should be readable (644 or 755 for directories)
   ```

3. Check PHP error log:
   ```bash
   tail -50 /var/www/vhosts/terravivapizza.com/httpdocs/wp-content/debug.log
   ```

4. Enable WordPress debug mode (if not already enabled):
   - Edit wp-config.php
   - Set `WP_DEBUG` to `true`
   - Check debug.log for specific error

**If files exist but plugin doesn't show:**
- Clear browser cache
- Refresh the Plugins page
- Check that folder name is exactly `custom-review-plugin` (no extra spaces or characters)

**If plugin activates but menu doesn't appear:**
- Clear WordPress cache (if using caching plugin)
- Log out and log back in to WordPress Admin

## Files to Upload

Make sure these files are all uploaded:

```
✓ custom-review-plugin/custom-review-plugin.php (main plugin file)
✓ custom-review-plugin/README.md (documentation)
✓ custom-review-plugin/INSTALLATION.md (installation guide)
✓ custom-review-plugin/admin/admin-page.php (admin interface)
✓ custom-review-plugin/admin/admin-scripts.js (admin JavaScript)
✓ custom-review-plugin/admin/admin-styles.css (admin CSS)
```

Total: 6 files in 2 directories

## Quick Command to Verify Upload (On Server)

```bash
# SSH into server and run:
find /var/www/vhosts/terravivapizza.com/httpdocs/wp-content/plugins/custom-review-plugin -type f
```

Should show all 6 files listed above.

---

**Need help?** Check the README.md file in the plugin folder for detailed documentation.
