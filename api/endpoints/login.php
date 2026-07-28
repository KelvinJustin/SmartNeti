<?php

/**
 * POST /api/login
 * Customer login endpoint
 */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(false, null, 'Method not allowed', 405);
}

$body = getRequestBody();
$username = sanitizeInput($body['username'] ?? '');
$password = sanitizeInput($body['password'] ?? '');

if (empty($username) || empty($password)) {
    sendJsonResponse(false, null, 'Username and password are required', 400);
}

// Find customer by username
$customer = ORM::for_table('tbl_customers')->where('username', $username)->find_one();

if (!$customer) {
    sendJsonResponse(false, null, 'Invalid username or password', 401);
}

// Check account status
if ($customer['status'] !== 'Active') {
    sendJsonResponse(false, null, 'Account is ' . $customer['status'], 403);
}

// Verify password
if (!Password::_uverify($password, $customer['password'])) {
    sendJsonResponse(false, null, 'Invalid username or password', 401);
}

// Generate authentication token
global $db_pass;
$tokenData = User::generateToken($customer['id']);
$token = 'c.' . $tokenData['token'];

// Update last login
$customer->last_login = date('Y-m-d H:i:s');
$customer->save();

// Return success response
$userData = [
    'id' => (int)$customer['id'],
    'username' => $customer['username'],
    'fullname' => $customer['fullname'],
    'email' => $customer['email'],
    'phone' => $customer['phonenumber'],
    'status' => $customer['status'],
    'account_type' => $customer['account_type']
];

sendJsonResponse(true, [
    'token' => $token,
    'user' => $userData
], 'Login successful');
