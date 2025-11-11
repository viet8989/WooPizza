<?php
/**
 * Clear Debug Logs Utility
 *
 * This file clears both wp-content/debug.log and wp-content/custom-debug.log
 * NO SECURITY - For debugging only, DELETE after use
 *
 * Usage: curl http://your-site.com/wp-content/themes/flatsome-child/clear-logs.php
 */

// Load WordPress environment
require_once(__DIR__ . '/../../../../wp-load.php');

$results = array();
$log_files = array(
    'debug.log' => WP_CONTENT_DIR . '/debug.log',
    'custom-debug.log' => WP_CONTENT_DIR . '/custom-debug.log'
);

foreach ($log_files as $name => $path) {
    if (file_exists($path)) {
        if (is_writable($path)) {
            $success = file_put_contents($path, '');
            if ($success !== false) {
                $results[$name] = 'Cleared successfully';
            } else {
                $results[$name] = 'Failed to clear (write error)';
            }
        } else {
            $results[$name] = 'File not writable';
        }
    } else {
        $results[$name] = 'File does not exist';
    }
}

// Output JSON for command line
header('Content-Type: application/json');
echo json_encode(array(
    'success' => true,
    'results' => $results,
    'timestamp' => date('Y-m-d H:i:s')
), JSON_PRETTY_PRINT);
