<?php
/**
 * Redirect handler for PayChangu payment returns
 * Redirects from ngrok URL to local server IP
 */

// Get the current query string
$query_string = $_SERVER['QUERY_STRING'] ?? '';

// Use HTTP for local server (HTTPS to HTTP downgrade)
$protocol = "http://";

// Get the actual server IP - use 192.168.1.164 as it's the actual network IP
// SERVER_ADDR returns 127.0.0.1 on local XAMPP
$server_ip = '192.168.1.164';

// Build the redirect URL with explicit slash
$redirect_url = $protocol . $server_ip . '/';

// If there's a query string, append it
if (!empty($query_string)) {
    $redirect_url .= '?' . $query_string;
}

// Redirect
header("Location: " . $redirect_url);
exit;
