# Product Lightbox Debugging Workflow - Complete Template

---

## Key Features (Reusable for Other Tasks)

✅ **No MySQL/Database Commands Required**
- No need to run `mysql` commands from terminal
- No direct database queries like `SELECT * FROM wp_posts`
- WordPress/WooCommerce functions handle all database operations internally
- We just add `error_log()` to see what WordPress is doing

✅ **Debug via PHP Code Logging**
- Add `error_log()` statements in PHP files
- View results in `wp-content/debug.log`
- No need for database access or phpMyAdmin

✅ **Auto Upload/Download via Python Scripts**
- `auto_upload_ftp.py` - Uploads modified files to production
- `auto_download_file.py` - Downloads log files from production
- One command to sync changes with live server

✅ **Client-Side Logging to Server**
- `writeLogServer()` - JavaScript logs saved to server file
- `clearLogsServer()` - Clear logs before testing
- View browser events in `wp-content/custom-debug.log`

✅ **Automated Testing**
- URL parameter triggers auto-test script (`?autotest=product-lightbox`)
- Script runs all test steps automatically
- Wait 30 seconds, download logs, analyze results

✅ **Complete Workflow Documentation**
- Step-by-step manual process
- Quick reference commands
- Common debugging scenarios
- Troubleshooting guide

---

## Prerequisites

### 1. Python Scripts Setup
Located at: `~/Documents/AutoUploadFTPbyGitStatus/`

- **auto_upload_ftp.py** - Upload modified files to production server
- **auto_download_file.py** - Download specific files from production server
- **ftp_config.ini** - FTP credentials configuration

### 2. Server-Side Logging Functions (PHP)
These functions are already available in the production site for debugging:

**Location**: Available globally in WordPress

**Functions**:
```php
// Write logs to server
error_log('Your debug message here');

// For structured data
error_log('Data: ' . print_r($data, true));
```

### 3. Client-Side Logging Functions (JavaScript)
These functions are available in the browser console:

**Functions**:
```javascript
// Write client-side logs to server
writeLogServer(data, logLevel);

// Clear both log files
clearLogsServer();
```

## Automated Testing Workflow

### Method 1: Manual Step-by-Step (For Understanding)

#### Step 1: Open Site
```bash
open https://terravivapizza.com
```
Wait 5 seconds for page load.

#### Step 2: Open Mini Cart (Browser Console)
```javascript
document.querySelector('.product-small.box a').click();
```
Wait 5 seconds for mini cart to appear.

#### Step 3: Clear Previous Logs (Browser Console)
```javascript
clearLogsServer();
```
Wait 5 seconds for logs to clear.

#### Step 4: Log State Before Action (Browser Console)
```javascript

```
Wait 5 seconds.

#### Step 5: Trigger Action (Browser Console)
```javascript

```
Wait 10 seconds for AJAX to complete and UI to update.

#### Step 6: Download Log Files (Terminal)
```bash
cd /home/vietnhq/Documents/WooCommerce/WooPizza
python3 ~/Documents/AutoUploadFTPbyGitStatus/auto_download_file.py /wp-content/debug.log && \
python3 ~/Documents/AutoUploadFTPbyGitStatus/auto_download_file.py /wp-content/custom-debug.log
```

#### Step 7: Analyze Logs (Terminal/IDE)
```bash
# Read server-side logs
cat wp-content/debug.log

# Read client-side logs
cat wp-content/custom-debug.log

# Or open in IDE to compare side-by-side
```

---

### Method 2: Automated Script (Recommended)

Use the automated test script located at: `auto-test-product-lightbox.js`

**To use**:
1. Open browser to: `https://terravivapizza.com?autotest=product-lightbox`
2. The script auto-runs (open lightbox, logs before/after, clicks 'Add to cart' button)
3. Wait 30 seconds for completion
4. Download logs from terminal:

```bash
cd /home/vietnhq/Documents/WooCommerce/WooPizza
sleep 35 && \
python3 ~/Documents/AutoUploadFTPbyGitStatus/auto_download_file.py /wp-content/debug.log && \
python3 ~/Documents/AutoUploadFTPbyGitStatus/auto_download_file.py /wp-content/custom-debug.log
```

**One-line command** (open browser + download logs after delay):
```bash
open "https://terravivapizza.com?autotest=minicart" && \
sleep 35 && \
python3 ~/Documents/AutoUploadFTPbyGitStatus/auto_download_file.py /wp-content/debug.log && \
python3 ~/Documents/AutoUploadFTPbyGitStatus/auto_download_file.py /wp-content/custom-debug.log
```

---

## Debugging PHP Code (Server-Side)

### Add Debug Logging to PHP
**Location**: `wp-content/themes/flatsome-child/functions.php`
```

### Upload Modified PHP Files
```bash
cd /home/vietnhq/Documents/WooCommerce/WooPizza

# Upload all modified files (detected by git status)
python3 ~/Documents/AutoUploadFTPbyGitStatus/auto_upload_ftp.py

# The script shows:
# - Which files changed
# - Upload progress
# - Success/failure status
```

---

## Complete Debug & Fix Cycle

### 1. Add Debug Logging
Edit the PHP file where you suspect the issue:
```bash
# Example: Edit functions.php
nano wp-content/themes/flatsome-child/functions.php

# Add error_log() statements at key points
```

### 2. Upload Changes
```bash
cd /home/vietnhq/Documents/WooCommerce/WooPizza
python3 ~/Documents/AutoUploadFTPbyGitStatus/auto_upload_ftp.py
```

### 3. Run Automated Test
```bash
open "https://terravivapizza.com?autotest=product-lightbox" && sleep 35
```

### 4. Download Logs
```bash
python3 ~/Documents/AutoUploadFTPbyGitStatus/auto_download_file.py /wp-content/debug.log && \
python3 ~/Documents/AutoUploadFTPbyGitStatus/auto_download_file.py /wp-content/custom-debug.log
```

### 5. Analyze Results
```bash
# View server logs
cat wp-content/debug.log

# View client logs
cat wp-content/custom-debug.log

# Or use grep to filter specific events
grep "PAIRED MODE" wp-content/debug.log
grep "before_quantity_change" wp-content/custom-debug.log
```

### 6. Fix Code
Based on log analysis, edit the code:
```bash
nano wp-content/themes/flatsome-child/functions.php
# Make fixes
```

### 7. Test Fix
Repeat steps 2-5 to verify the fix works.

---

## Log File Locations

### Server Logs (PHP)
**Path**: `wp-content/debug.log`

**Content**: Server-side PHP operations
- Database queries
- WooCommerce hooks execution

**Format**:

### Client Logs (JavaScript)
**Path**: `wp-content/custom-debug.log`

**Content**: Browser-side JavaScript events
- User interactions
- AJAX responses
- UI state before/after actions

**Format**:

## Common Debugging Scenarios

## Troubleshooting

### Issue: Logs Not Appearing

**Solution 1**: Check PHP error logging is enabled
```php
// In wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

**Solution 2**: Check file permissions
```bash
# Log files should be writable
chmod 644 wp-content/debug.log
chmod 644 wp-content/custom-debug.log
```

### Issue: Upload Script Fails

**Check**:
```bash
# Verify script exists
ls -la ~/Documents/AutoUploadFTPbyGitStatus/auto_upload_ftp.py

# Check FTP config
cat ~/Documents/AutoUploadFTPbyGitStatus/ftp_config.ini

# Run with error output
python3 ~/Documents/AutoUploadFTPbyGitStatus/auto_upload_ftp.py 2>&1
```

### Issue: Download Script Fails

**Check**:
```bash
# Verify remote file exists
# Check in browser or FTP client

# Try with verbose output
python3 ~/Documents/AutoUploadFTPbyGitStatus/auto_download_file.py /wp-content/debug.log -v
```

## Quick Reference Commands

```bash
# Full automated test + download logs
open "https://terravivapizza.com?autotest=product-lightbox" && \
sleep 35 && \
python3 ~/Documents/AutoUploadFTPbyGitStatus/auto_download_file.py /wp-content/debug.log && \
python3 ~/Documents/AutoUploadFTPbyGitStatus/auto_download_file.py /wp-content/custom-debug.log

# Upload changes
cd /home/vietnhq/Documents/WooCommerce/WooPizza && \
python3 ~/Documents/AutoUploadFTPbyGitStatus/auto_upload_ftp.py

# View logs
cat wp-content/debug.log
cat wp-content/custom-debug.log

# Search logs for specific events
grep "PAIRED MODE" wp-content/debug.log
grep "price" wp-content/debug.log | grep -i "error"
```
