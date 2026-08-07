<?php
/**
 * Redirect handler for PayChangu payment returns
 * Redirects from ngrok URL to local server IP
 */

// Get the current query string
$query_string = $_SERVER['QUERY_STRING'] ?? '';

// Check if this is a callback (has _route=callback/paychangu)
$is_callback = isset($_GET['_route']) && strpos($_GET['_route'], 'callback/paychangu') === 0;

// Use HTTP for local server (HTTPS to HTTP downgrade)
$protocol = "http://";

// Get the actual server IP - use 10.169.159.126 as it's the actual network IP
$server_ip = '10.169.159.126';

// Build the redirect URL with explicit slash
$redirect_url = $protocol . $server_ip . '/';

// If there's a query string, append it
if (!empty($query_string)) {
    $redirect_url .= '?' . $query_string;
}

// Redirect
header("Location: " . $redirect_url);
exit;
