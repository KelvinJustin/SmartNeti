<?php

/**
 * GET /api/subscriptions - Get customer active subscriptions
 * POST /api/subscriptions - Perform actions on subscriptions (deactivate, sync)
 */

$userId = requireAuth();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get active subscriptions
    $subscriptions = ORM::for_table('tbl_user_recharges')
        ->where('customer_id', $userId)
        ->order_by_desc('expiration')
        ->find_many();

    $subscriptionsData = [];
    foreach ($subscriptions as $sub) {
        $plan = ORM::for_table('tbl_plans')->find_one($sub['plan_id']);
        if ($plan) {
            $subscriptionsData[] = [
                'id' => (int)$sub['id'],
                'plan_id' => (int)$sub['plan_id'],
                'plan_name' => $plan['name_plan'],
                'plan_type' => $plan['type'],
                'price' => $plan['price'],
                'routers' => $sub['routers'],
                'recharged_on' => $sub['recharged_on'],
                'expiration' => $sub['expiration'],
                'time' => $sub['time'],
                'status' => $sub['status'],
                'is_active' => $sub['status'] === 'on'
            ];
        }
    }

    sendJsonResponse(true, $subscriptionsData);

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['action'])) {
        sendJsonResponse(false, null, 'Action is required', 400);
    }

    $action = $input['action'];

    // Get user info
    $user = ORM::for_table('tbl_customers')->find_one($userId);
    if (!$user) {
        sendJsonResponse(false, null, 'User not found', 404);
    }

    if ($action === 'deactivate') {
        if (!isset($input['subscription_id'])) {
            sendJsonResponse(false, null, 'Subscription ID is required', 400);
        }

        $bill = ORM::for_table('tbl_user_recharges')
            ->where('id', $input['subscription_id'])
            ->where('customer_id', $userId)
            ->find_one();

        if (!$bill) {
            sendJsonResponse(false, null, 'Subscription not found', 404);
        }

        $p = ORM::for_table('tbl_plans')->where('id', $bill['plan_id'])->find_one();
        if ($p) {
            $dvc = Package::getDevice($p);
            if (file_exists($dvc)) {
                require_once $dvc;
                (new $p['device'])->remove_customer($user, $p);
            }
        }

        $bill->status = 'off';
        $bill->expiration = date('Y-m-d');
        $bill->time = date('H:i:s');
        $bill->save();

        sendJsonResponse(true, ['subscription_id' => (int)$bill['id'], 'status' => 'off'], 'Subscription deactivated successfully');

    } elseif ($action === 'sync') {
        if (!isset($input['subscription_id'])) {
            sendJsonResponse(false, null, 'Subscription ID is required', 400);
        }

        $bill = ORM::for_table('tbl_user_recharges')
            ->where('id', $input['subscription_id'])
            ->where('customer_id', $userId)
            ->find_one();

        if (!$bill) {
            sendJsonResponse(false, null, 'Subscription not found', 404);
        }

        if ($bill['status'] != 'on') {
            sendJsonResponse(false, null, 'Subscription is not active', 400);
        }

        $p = ORM::for_table('tbl_plans')->find_one($bill['plan_id']);
        if ($p) {
            $c = ORM::for_table('tbl_customers')->find_one($bill['customer_id']);
            if ($c) {
                $dvc = Package::getDevice($p);
                if (file_exists($dvc)) {
                    require_once $dvc;
                    if (method_exists($p['device'], 'sync_customer')) {
                        (new $p['device'])->sync_customer($c, $p);
                    } else {
                        (new $p['device'])->add_customer($c, $p);
                    }
                }
            }
        }

        sendJsonResponse(true, ['subscription_id' => (int)$bill['id']], 'Subscription synced successfully');

    } else {
        sendJsonResponse(false, null, 'Invalid action', 400);
    }

} else {
    sendJsonResponse(false, null, 'Method not allowed', 405);
}
