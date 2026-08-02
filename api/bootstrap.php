<?php

/**
 * API Bootstrap
 * Loads necessary dependencies and provides helper functions
 */

// Load the main application init file
$isApi = true;
require_once __DIR__ . '/../init.php';

// Helper function to send JSON response
function sendJsonResponse($success, $data = null, $message = '', $statusCode = 200) {
    http_response_code($statusCode);
    $response = ['success' => $success];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    if (!empty($message)) {
        $response['message'] = $message;
    }
    
    echo json_encode($response);
    exit;
}

// Helper function to get authenticated user ID from token
function getAuthenticatedUserId() {
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? '';
    
    error_log("Auth header: " . $authHeader);
    
    if (empty($authHeader) || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
        error_log("No valid auth header found");
        return null;
    }
    
    $token = $matches[1];
    error_log("Token: " . $token);
    
    // Validate token format: c.uid.time.sha1
    $parts = explode('.', $token);
    if (count($parts) !== 4) {
        error_log("Invalid token format, parts: " . count($parts));
        return null;
    }
    
    list($type, $uid, $time, $hash) = $parts;
    error_log("Token parts - type: $type, uid: $uid, time: $time");
    
    // Only customer tokens are allowed
    if ($type !== 'c') {
        error_log("Invalid token type: $type");
        return null;
    }
    
    // Verify token hash
    global $db_pass;
    $expectedHash = sha1($uid . '.' . $time . '.' . $db_pass);
    error_log("Expected hash: $expectedHash, Received hash: $hash");
    
    if ($hash !== $expectedHash) {
        error_log("Hash mismatch");
        return null;
    }
    
    // Check if customer exists
    $customer = ORM::for_table('tbl_customers')->where('id', $uid)->find_one();
    if (!$customer) {
        error_log("Customer not found for uid: $uid");
        return null;
    }
    
    error_log("Authentication successful for uid: $uid");
    return (int)$uid;
}

// Helper function to require authentication
function requireAuth() {
    $userId = getAuthenticatedUserId();
    if ($userId === null) {
        sendJsonResponse(false, null, 'Unauthorized or invalid token', 401);
    }
    return $userId;
}

// Helper function to get request body
function getRequestBody() {
    $input = file_get_contents('php://input');
    return json_decode($input, true) ?? [];
}

// Helper function to sanitize input
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}
