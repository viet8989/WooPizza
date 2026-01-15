---
description: Steps to test, debug, and fix a bug on a remote website
---

1. **Reproduction (Remote)**
   - Verification: Open the remote website URL and attempt to reproduce the reported issue.
   - Action: Note down specific steps, browser version, and user role used.

2. **Information Gathering**
   - Action: Check browser console for JavaScript errors (F12 > Console).
   - Action: Check network tab for failed requests (F12 > Network).
   - Action: If available, check server-side error logs (e.g., `error.log`, `debug.log`).

3. **Local Reproduction**
   - Action: Pull the latest code/content to your local environment.
   - Action: Attempt to reproduce the issue locally using the same steps.
   - Note: If reliable reproduction isn't possible locally, consider environment differences (PHP version, database data, config).

4. **Debugging & Fix**
   - Action: Use debugging tools (Xdebug, `console.log`, `error_log`) to pinpoint the failure.
   - Action: Implement a fix.
   - Verification: precise verification steps depend on the bug. Ensure the fix works locally.

5. **Deployment**
   - Action: Push the fix to a staging environment (if available) or production.
   - // turbo
   - Action: Clear caches (browser, server, CDN).

6. **Final Verification**
   - Verification: Visit the remote site and verify the bug is gone.
   - Verification: Check for regressions in related features.
