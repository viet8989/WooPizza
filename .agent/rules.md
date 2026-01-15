# Project Rules (Antigravity)

These rules apply to all tasks in this project.

## 1. Automated Testing First
- **Rule**: Before writing fix code, you must define how to test it automatically.
- **Implementation**:
  - Use `browser_subagent` for UI/Integration tests.
  - Create reusable test scripts (like `auto-test-mini-cart.js`) when possible.
  - **Exception**: Only use manual testing for final "feel" checks or when automation is impossible (e.g., physical device specific).

## 2. Server-Side Best Practices
- **Database**:
  - ❌ NEVER run direct SQL (`SELECT * FROM ...`).
  - ✅ USE WordPress/WooCommerce CRUD functions (`wc_get_order`, `update_post_meta`).
- **Logging**:
  - ✅ USE `error_log()` for all server-side debugging.
  - ❌ DO NOT use `print_r` or `echo` on frontend output.

## 3. Client-Side Best Practices
- **Logging**:
  - ✅ USE `writeLogServer()` (if available) or `console.log` directed to a captured object for the browser agent.
- **Selectors**:
  - ✅ USE strictly defined classes/IDs (e.g., `.woocommerce-mini-cart-item`).
  - ❌ AVOID fragile XPath or text-based selection if possible.

## 4. Workflow
- Always update `task.md` to reflect real-time progress.
- If a task seems complex, break it down into a specific `.agent/workflows/` file first.

## 5. Deployment
- **Rule**: Code changes MUST be uploaded to the server to take effect.
- **Action**: Run `python3 ~/Dropbox/AutoUploadFTPbyGitStatus/auto_upload_ftp.py` after editing files locally.
- **Verification**: Ensure the script returns a success status before testing.
