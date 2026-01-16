---
description: Automated workflow template for testing and fixing bugs or implementing new features on production site
---

# Auto Test and Fix Workflow

## How to Use This Template

When starting a new task, provide the agent with:
1. **Bug Description** or **Implement Task** - What needs to be done
2. **Automated Testing Workflow** - Specific test steps for this task

The agent will automatically follow this workflow structure.

---

## Prerequisites (Already Configured)

### Python Scripts
Located at: `~/Dropbox/AutoUploadFTPbyGitStatus/`
- `auto_upload_ftp.py` - Upload modified files to production
- `auto_download_file.py` - Download log files from production

### Logging Tools
| Tool | Purpose | Usage |
|------|---------|-------|
| `error_log()` | PHP server-side | `error_log('message');` |
| `console.log()` | JS for Agent browser | Agent reads directly |
| `writeLogServer()` | JS to server log | `writeLogServer(data, 'info');` |
| `clearLogsServer()` | Clear log files | `clearLogsServer();` |

### Log File Locations
- **Server logs (PHP)**: `wp-content/debug.log`
- **Client logs (JS)**: `wp-content/custom-debug.log`

---

## Workflow Steps

### 1. ANALYZE - Understand the Task
// turbo
```bash
# View relevant source files
cat [SOURCE_FILE_PATH]
```

### 2. ADD DEBUG LOGGING (if needed)
Add `error_log()` or `console.log()` statements to understand the issue.

### 3. UPLOAD CHANGES
// turbo
```bash
cd ~/Dropbox/WooPizza && python3 ~/Dropbox/AutoUploadFTPbyGitStatus/auto_upload_ftp.py
```

### 4. CLEAR CACHE (if browser caching issues)
Update script version in functions.php or add cache-busting parameter.

### 5. TEST USING BROWSER AGENT
Run the automated test using Antigravity Browser:
- Navigate to the site
- Perform the test actions
- Verify results via console.log() or DOM state

### 6. DOWNLOAD LOGS (if needed)
// turbo
```bash
cd ~/Dropbox/WooPizza && \
python3 ~/Dropbox/AutoUploadFTPbyGitStatus/auto_download_file.py /wp-content/debug.log && \
python3 ~/Dropbox/AutoUploadFTPbyGitStatus/auto_download_file.py /wp-content/custom-debug.log
```

### 7. ANALYZE RESULTS
// turbo
```bash
cat ~/Dropbox/WooPizza/wp-content/debug.log
cat ~/Dropbox/WooPizza/wp-content/custom-debug.log
```

### 8. FIX CODE
Based on analysis, implement the fix in the source files.

### 9. REPEAT
Upload changes and test again until fixed.

### 10. CLEANUP
Remove debug logging after fix is verified.

---

## Template for User Input

```markdown
## Bug Description
<!-- OR: ## Implement Task -->

[Describe what's broken or what needs to be implemented]
- File location: [path/to/file.php or .js]
- Function name: [function_name()]
- Expected behavior: [what should happen]
- Current behavior: [what's happening now]

---

## Automated Testing Workflow

### Test Steps for Browser Agent:
1. Navigate to `https://terravivapizza.com/[page]`
2. [Action 1: e.g., "Click on element X"]
3. [Action 2: e.g., "Wait for element Y to appear"]
4. [Action 3: e.g., "Verify Z is displayed correctly"]
5. Take screenshot for verification

### Expected Results:
- [What should happen after each step]
- [Final expected state]

### Verification Code (optional):
```javascript
// JavaScript to run in browser console for verification
console.log('Test result:', document.querySelector('.target-element').textContent);
```
```

---

## Quick Commands Reference

```bash
# Upload all modified files
cd ~/Dropbox/WooPizza && python3 ~/Dropbox/AutoUploadFTPbyGitStatus/auto_upload_ftp.py

# Download logs
python3 ~/Dropbox/AutoUploadFTPbyGitStatus/auto_download_file.py /wp-content/debug.log
python3 ~/Dropbox/AutoUploadFTPbyGitStatus/auto_download_file.py /wp-content/custom-debug.log

# View logs
cat ~/Dropbox/WooPizza/wp-content/debug.log
cat ~/Dropbox/WooPizza/wp-content/custom-debug.log

# Search logs
grep "keyword" ~/Dropbox/WooPizza/wp-content/debug.log
```

---

// turbo-all
