<?php
/**
 * Redirect from ngrok or any external URL to the actual server IP
 * This file handles URL normalization by redirecting users to the server's actual IP
 */

// Get the actual server IP
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ||
             (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";

// Try multiple ways to get the server IP
$server_ip = $_SERVER['SERVER_ADDR'] ?? $_SERVER['LOCAL_ADDR'] ?? $_SERVER['SERVER_NAME'] ?? '192.168.1.165';

// Debug: Log the values
error_log("redirect.php debug - protocol: $protocol, server_ip: $server_ip");

// Get the current query string
$query_string = $_SERVER['QUERY_STRING'] ?? '';

// Build the redirect URL - redirect.php is in the root directory
$redirect_url = $protocol . $server_ip . '/';

// Debug: Log the redirect URL
error_log("redirect.php debug - redirect_url: $redirect_url");

// If there's a query string, append it
if (!empty($query_string)) {
    $redirect_url .= '?' . $query_string;
}

// Redirect to the actual server
header("Location: " . $redirect_url);
exit;
