<?php

/**
 * POST /api/voucher
 * Voucher redemption endpoint
 */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(false, null, 'Method not allowed', 405);
}

// Get authenticated user ID without status check
$userId = getAuthenticatedUserId();
if ($userId === null) {
    sendJsonResponse(false, null, 'Unauthorized or invalid token', 401);
}

$body = getRequestBody();
$code = sanitizeInput($body['code'] ?? '');

if (empty($code)) {
    sendJsonResponse(false, null, 'Voucher code is required', 400);
}

// Sanitize voucher code
$code = alphanumeric($code, "-_.,");

// Find unused voucher with matching code (case-sensitive)
$voucher = ORM::for_table('tbl_voucher')->whereRaw("BINARY code = '$code'")->where('status', 0)->find_one();

if (!$voucher) {
    sendJsonResponse(false, null, 'Voucher not valid or already used', 400);
}

// Get user info
$user = ORM::for_table('tbl_customers')->where('id', $userId)->find_one();
if (!$user) {
    sendJsonResponse(false, null, 'User not found', 404);
}

// Run hook
run_hook('customer_activate_voucher');

// Recharge user account
try {
    $result = Package::rechargeUser($userId, $voucher['routers'], $voucher['id_plan'], "Voucher", $code);
    if ($result) {
        // Mark voucher as used
        $voucher->status = "1";
        $voucher->used_date = date('Y-m-d H:i:s');
        $voucher->user = $user['username'];
        $voucher->save();
        
        // Run hook
        run_hook('view_activate_voucher');
        
        sendJsonResponse(true, [
            'voucher_code' => $code,
            'plan_id' => $voucher['id_plan'],
            'routers' => $voucher['routers'],
            'used_date' => $voucher->used_date
        ], 'Voucher activated successfully');
    } else {
        sendJsonResponse(false, null, 'Failed to activate voucher - recharge failed', 500);
    }
} catch (Exception $e) {
    error_log("Voucher activation error: " . $e->getMessage());
    sendJsonResponse(false, null, 'Failed to activate voucher: ' . $e->getMessage(), 500);
}
