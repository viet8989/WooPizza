---
description: Standard/Template workflow for any new feature or bug fix in this project
---

# Project Standard Workflow Template

Use this template for all new tasks. It enforces the "Automated Test & Fix" method.

## 1. Setup & Reproduction
- **Goal**: Confirm the issue or requirements without guessing.
- **Action**:
  - If a bug: create a reproduction case.
  - If a feature: define the expected behavior.
- **Tools**: `browser_subagent` (for initial observation), `read_url_content`.

## 2. Prerequisites & Tooling
**Python Scripts Setup**
Located at: `~/Dropbox/AutoUploadFTPbyGitStatus/`
- **auto_upload_ftp.py** - Upload modified files to production server
- **auto_download_file.py** - Download specific files from production server
- **ftp_config.ini** - FTP credentials configuration

## 3. Automated Test Creation
- **Goal**: Create a reliable, repeatable test *before* fixing/implementing.
- **Standard**: Do NOT rely on manual clicking for every iteration.
- **Method**:
  - **Option A (Browser Agent)**: Create a workflow that uses the `browser_subagent` to perform steps and return structured data (JSON) from the console.
  - **Option B (URL Param)**: If supported, implement `?autotest=feature_name` in JS to auto-run client-side logic.
- **Logging**:
  - Use `writeLogServer()` (JS) for client-side events.
  - Use `error_log()` (PHP) for server-side events.
  - Ensure logs are written to `wp-content/custom-debug.log` or `debug.log`.

## 4. Implementation / Fix
- **Goal**: Modify code to satisfy the test.
- **Rules**:
  - **No Database Access**: Use WordPress functions (`wc_get_product`, `update_post_meta`) instead of SQL.
  - **Safe Logging**: Add `error_log` markers to trace execution flow.
  - **Clean Code**: Follow existing patterns in `custom.js` or `functions.php`.

## 5. Verification Loop
- **Goal**: Prove the fix works using the automated test from Step 2.
- **Process**:
  1. Upload changes: `python3 ~/Dropbox/AutoUploadFTPbyGitStatus/auto_upload_ftp.py`
  2. Run Automated Test (Browser Agent or Script).
  3. Download/Read logs (if using server-side validation).
  4. Compare "Before" and "After" states.
  5. Repeat until Test passes.

## 6. Deployment & Cleanup
- **Action**:
  - Remove simple debug logs (keep critical error logging).
  - deploy changes.
  - Clear Transients/Cache (`WooCommerce > Status > Tools`).

## 7. Final Validation
- **Action**: Perform one manual walkthrough to ensure no UI regressions.
