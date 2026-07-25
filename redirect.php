<?php
/**
 * Redirect handler for PayChangu payment returns
 * Redirects from ngrok URL to local server IP
 */

// Get the current query string
$query_string = $_SERVER['QUERY_STRING'] ?? '';

// Use HTTP for local server (HTTPS to HTTP downgrade)
$protocol = "http://";

// Get the actual server IP
$server_ip = $_SERVER['SERVER_ADDR'] ?? $_SERVER['LOCAL_ADDR'] ?? '192.168.1.165';

// Build the redirect URL with explicit slash
$redirect_url = $protocol . $server_ip . '/';

// If there's a query string, append it
if (!empty($query_string)) {
    $redirect_url .= '?' . $query_string;
}

// Redirect
header("Location: " . $redirect_url);
exit;
