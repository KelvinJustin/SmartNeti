<?php
/**
 * Redirect from ngrok or any external URL to the actual server IP
 * This file handles URL normalization by redirecting users to the server's actual IP
 */

// Get the actual server IP
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ||
             (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
$server_ip = $_SERVER['SERVER_ADDR'] ?? $_SERVER['LOCAL_ADDR'] ?? 'localhost';
$base_path = dirname($_SERVER['SCRIPT_NAME']);

// Ensure base_path ends with a slash
$base_path = rtrim($base_path, '/\\') . '/';

// Get the current query string
$query_string = $_SERVER['QUERY_STRING'] ?? '';

// Build the redirect URL
$redirect_url = $protocol . $server_ip . $base_path;

// If there's a query string, append it
if (!empty($query_string)) {
    $redirect_url .= '?' . $query_string;
}

// Redirect to the actual server
header("Location: " . $redirect_url);
exit;
