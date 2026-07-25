<?php
/**
 * Redirect handler for PayChangu payment returns
 * This file simply passes through the query string to the application routing
 */

// Get the current query string
$query_string = $_SERVER['QUERY_STRING'] ?? '';

// Build the redirect URL - use the same host and protocol, just remove redirect.php
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ||
             (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';

// Redirect to the root with the query string
$redirect_url = $protocol . $host . '/';

// If there's a query string, append it
if (!empty($query_string)) {
    $redirect_url .= '?' . $query_string;
}

// Redirect
header("Location: " . $redirect_url);
exit;
