<?php

/**
 * POST /api/change-password - Change user password
 * 
 * Request body:
 * {
 *   "current_password": "current_password",
 *   "new_password": "new_password",
 *   "confirm_password": "new_password"
 * }
 */

$userId = requireAuth();

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

$currentPassword = sanitizeInput($input['current_password'] ?? '');
$newPassword = sanitizeInput($input['new_password'] ?? '');
$confirmPassword = sanitizeInput($input['confirm_password'] ?? '');

// Validate required fields
if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
    sendJsonResponse(false, null, 'Current password, new password, and confirm password are required', 400);
}

// Validate password length
if (!Validator::Length($newPassword, 35, 2)) {
    sendJsonResponse(false, null, 'New password should be between 3 to 35 characters', 400);
}

// Validate password match
if ($newPassword !== $confirmPassword) {
    sendJsonResponse(false, null, 'New password and confirm password do not match', 400);
}

// Check if new password is same as current
if ($currentPassword === $newPassword) {
    sendJsonResponse(false, null, 'New password cannot be the same as current password', 400);
}

// Get customer information
$customer = ORM::for_table('tbl_customers')->find_one($userId);

if (!$customer) {
    sendJsonResponse(false, null, 'Customer not found', 404);
}

// Verify current password
if (!Password::_uverify($currentPassword, $customer['password'])) {
    sendJsonResponse(false, null, 'Current password is incorrect', 401);
}

// Update password
$customer->password = $newPassword;
$customer->save();

sendJsonResponse(true, [
    'message' => 'Password changed successfully'
], 'Password changed successfully');
