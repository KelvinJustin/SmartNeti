<?php

/**
 * PHP Mikrotik Billing (https://github.com/hotspotbilling/phpnuxbill/)
 *
 * Payment Gateway PayChangu - https://paychangu.com
 *
 * created for SmartNeti
 *
 **/

function paychangu_validate_config()
{
  global $config;
  if (empty($config['paychangu_secret_key']) || empty($config['paychangu_callback_url']) || empty($config['paychangu_webhook_secret'])) {
    sendTelegram("PayChangu payment gateway not configured");
    r2(U . 'order/package', 'w', Lang::T("Admin has not yet setup PayChangu payment gateway, please tell admin"));
  }
}


function paychangu_show_config()
{
  global $ui, $config;
  $ui->assign('_title', 'PayChangu - Payment Gateway - ' . $config['CompanyName']);
  $ui->display('paychangu.tpl');
}


function paychangu_save_config()
{
  global $admin, $_L;
  $secret_key = _post('secret_key');
  $public_key = _post('public_key');
  $callback_url = _post('callback_url');
  $return_url = _post('return_url');
  $currency = _post('currency');
  $webhook_secret = _post('webhook_secret');

  $checkSecretKey = ORM::for_table('tbl_appconfig')->where('setting', 'paychangu_secret_key')->find_one();
  if ($checkSecretKey) {
    $checkSecretKey->value = $secret_key;
    $checkSecretKey->save();
  } else {
    $checkSecretKey = ORM::for_table('tbl_appconfig')->create();
    $checkSecretKey->setting = 'paychangu_secret_key';
    $checkSecretKey->value = $secret_key;
    $checkSecretKey->save();
  }

  $checkPublicKey = ORM::for_table('tbl_appconfig')->where('setting', 'paychangu_public_key')->find_one();
  if ($checkPublicKey) {
    $checkPublicKey->value = $public_key;
    $checkPublicKey->save();
  } else {
    $checkPublicKey = ORM::for_table('tbl_appconfig')->create();
    $checkPublicKey->setting = 'paychangu_public_key';
    $checkPublicKey->value = $public_key;
    $checkPublicKey->save();
  }

  $checkCallbackUrl = ORM::for_table('tbl_appconfig')->where('setting', 'paychangu_callback_url')->find_one();
  if ($checkCallbackUrl) {
    $checkCallbackUrl->value = $callback_url;
    $checkCallbackUrl->save();
  } else {
    $checkCallbackUrl = ORM::for_table('tbl_appconfig')->create();
    $checkCallbackUrl->setting = 'paychangu_callback_url';
    $checkCallbackUrl->value = $callback_url;
    $checkCallbackUrl->save();
  }

  $checkReturnUrl = ORM::for_table('tbl_appconfig')->where('setting', 'paychangu_return_url')->find_one();
  if ($checkReturnUrl) {
    $checkReturnUrl->value = $return_url;
    $checkReturnUrl->save();
  } else {
    $checkReturnUrl = ORM::for_table('tbl_appconfig')->create();
    $checkReturnUrl->setting = 'paychangu_return_url';
    $checkReturnUrl->value = $return_url;
    $checkReturnUrl->save();
  }

  $checkCurrency = ORM::for_table('tbl_appconfig')->where('setting', 'paychangu_currency')->find_one();
  if ($checkCurrency) {
    $checkCurrency->value = $currency;
    $checkCurrency->save();
  } else {
    $checkCurrency = ORM::for_table('tbl_appconfig')->create();
    $checkCurrency->setting = 'paychangu_currency';
    $checkCurrency->value = $currency;
    $checkCurrency->save();
  }

  $checkWebhookSecret = ORM::for_table('tbl_appconfig')->where('setting', 'paychangu_webhook_secret')->find_one();
  if ($checkWebhookSecret) {
    $checkWebhookSecret->value = $webhook_secret;
    $checkWebhookSecret->save();
  } else {
    $checkWebhookSecret = ORM::for_table('tbl_appconfig')->create();
    $checkWebhookSecret->setting = 'paychangu_webhook_secret';
    $checkWebhookSecret->value = $webhook_secret;
    $checkWebhookSecret->save();
  }

  _log('[' . $admin['username'] . ']: PayChangu ' . $_L['Settings_Saved_Successfully'], 'Admin', $admin['id']);

  r2(U . 'paymentgateway/paychangu', 's', $_L['Settings_Saved_Successfully']);
}


function paychangu_create_transaction($trx, $user)
{
  global $config, $routes;

  _log("PayChangu: Creating transaction for user " . $user['username'] . ", trx_id: " . $trx['id']);

  // Generate unique transaction reference
  $tx_ref = 'INV-' . $trx['id'] . '-' . time();

  // Use ngrok URL for webhook and return URLs
  // callback_url: Server-to-server webhook notification from PayChangu
  // return_url: User redirect after payment (success/failure) - goes to callback for polling
  $ngrok_url = 'https://imprecatory-unobligative-genna.ngrok-free.dev';

  $url = 'https://api.paychangu.com/payment';

  $fields = [
    'amount' => $trx['price'],
    'currency' => $config['paychangu_currency'] ?: 'MWK',
    'email' => $user['email'] ?: '',
    'first_name' => $user['fullname'] ? explode(' ', $user['fullname'])[0] : '',
    'last_name' => $user['fullname'] ? (count(explode(' ', $user['fullname'])) > 1 ? implode(' ', array_slice(explode(' ', $user['fullname']), 1)) : '') : '',
    'callback_url' => $ngrok_url . '/?_route=webhook/paychangu',
    'return_url' => $ngrok_url . '/?_route=callback/paychangu',
    'tx_ref' => $tx_ref,
    'customization' => [
      'title' => $config['CompanyName'] . ' - Payment',
      'description' => 'Payment for invoice #' . $trx['id']
    ],
    'meta' => [
      'invoice_id' => $trx['id'],
      'username' => $user['username']
    ]
  ];
  
  $payloadJson = json_encode($fields);
  
  _log("PayChangu: Calling API with tx_ref: " . $tx_ref . ", amount: " . $trx['price']);
  
  $curl = curl_init();
  curl_setopt_array($curl, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => $payloadJson,
    CURLOPT_HTTPHEADER => [
      'Content-Type: application/json',
      'Authorization: Bearer ' . $config['paychangu_secret_key']
    ]
  ]);
  $response = curl_exec($curl);
  $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
  $curlError = curl_error($curl);
  curl_close($curl);
  
  _log("PayChangu: API Response - HTTP Code: " . $httpCode . ", Response: " . $response . ", Curl Error: " . $curlError);
  
  $responseData = json_decode($response);
  
  if (isset($responseData->status) && $responseData->status == 'success' && isset($responseData->data->checkout_url)) {
    $checkout_url = $responseData->data->checkout_url;
    
    _log("PayChangu: Payment session created successfully, checkout_url: " . $checkout_url);
    
    // Update transaction record
    $d = ORM::for_table('tbl_payment_gateway')
      ->where('username', $user['username'])
      ->where('status', 1)
      ->find_one();
    
    if ($d) {
      $d->gateway_trx_id = $tx_ref;
      $d->pg_url_payment = $checkout_url;
      $d->pg_request = $user['id'];
      $d->expired_date = date('Y-m-d H:i:s', strtotime("+30 minutes"));
      $d->save();
      
      _log("PayChangu: Transaction record updated, redirecting to checkout");
      
      // Redirect to PayChangu checkout
      header("Location: " . $checkout_url);
      exit;
    } else {
      _log("PayChangu: ERROR - Transaction record not found for user: " . $user['username']);
      sendTelegram("PayChangu payment failed - Transaction record not found\n\n" . json_encode($responseData, JSON_PRETTY_PRINT));
      r2(U . 'order/package', 'e', Lang::T("Failed to create transaction. Transaction record not found."));
    }
  } else {
    _log("PayChangu: ERROR - Payment session creation failed");
    sendTelegram("PayChangu payment failed\n\nResponse: " . json_encode($responseData, JSON_PRETTY_PRINT) . "\nHTTP Code: " . $httpCode);
    $errorMessage = isset($responseData->message) ? (is_object($responseData->message) ? json_encode($responseData->message) : $responseData->message) : 'Unknown error';
    r2(U . 'order/package', 'e', Lang::T("Failed to create transaction. " . $errorMessage));
  }
}


function paychangu_payment_notification()
{
  global $config;
  header("Content-Type: application/json");

  $webhookData = file_get_contents('php://input');
  
  // Log webhook data to both file and system log
  $logFile = "PayChanguWebhook.json";
  $logEntry = date('Y-m-d H:i:s') . " - " . $webhookData . "\n";
  
  // Try to write to file with error handling
  try {
    $log = fopen($logFile, "a");
    if ($log) {
      fwrite($log, $logEntry);
      fclose($log);
    } else {
      _log("PayChangu Webhook: Failed to open log file for writing");
    }
  } catch (Exception $e) {
    _log("PayChangu Webhook: Exception writing to log file - " . $e->getMessage());
  }
  
  // Also log to system log for redundancy
  _log("PayChangu Webhook received: " . $webhookData);
  
  // Verify webhook signature
  $headers = getallheaders();
  $signature = isset($headers['Signature']) ? $headers['Signature'] : '';
  
  if (!empty($config['paychangu_webhook_secret'])) {
    $computedSignature = hash_hmac('sha256', $webhookData, $config['paychangu_webhook_secret']);
    if ($computedSignature !== $signature) {
      _log("PayChangu Webhook signature verification failed");
      http_response_code(403);
      echo json_encode(['status' => 'error', 'message' => 'Invalid signature']);
      exit;
    }
  }
  
  $webhookContent = json_decode($webhookData);
  
  // Check for tx_ref or reference field
  $tx_ref = $webhookContent->tx_ref ?? $webhookContent->reference ?? '';
  
  if (empty($tx_ref)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid webhook payload - missing tx_ref/reference']);
    exit;
  }
  
  $status = $webhookContent->status ?? '';
  
  // Find transaction by tx_ref
  $trx = ORM::for_table('tbl_payment_gateway')
    ->where('gateway_trx_id', $tx_ref)
    ->find_one();
  
  if (!$trx) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Transaction not found']);
    exit;
  }
  
  // Idempotency check - if already processed, return success
  if ($trx['status'] == 2) {
    _log("PayChangu Webhook: Transaction already processed - tx_ref: " . $tx_ref);
    http_response_code(200);
    echo json_encode(['status' => 'success', 'message' => 'Transaction already processed']);
    exit;
  }
  
  // Verify transaction with PayChangu API before processing (comprehensive verification)
  $verified = paychangu_verify_transaction($tx_ref, $trx['price'], $config['paychangu_currency'] ?: 'MWK');
  
  if ($verified && $status == 'success') {
    $user = ORM::for_table('tbl_customers')
      ->where('username', $trx['username'])
      ->find_one();
    
    if ($user) {
      if (!Package::rechargeUser($user['id'], $trx['routers'], $trx['plan_id'], $trx['gateway'], 'PayChangu')) {
        _log("PayChangu Payment Successful, But Failed to activate Package for user: " . $user['username']);
        sendTelegram("PayChangu Payment activation failed for user: " . $user['username']);
      } else {
        _log("PayChangu Payment Successful for user: " . $user['username']);
      }
    }
    
    $trx->pg_paid_response = json_encode($webhookContent);
    $trx->payment_method = 'PayChangu';
    $trx->payment_channel = $webhookContent->authorization->channel ?? 'PayChangu Checkout';
    $trx->paid_date = date('Y-m-d H:i:s');
    $trx->status = 2;
    $trx->webhook_received = 1;
    $trx->webhook_received_date = date('Y-m-d H:i:s');
    $trx->save();
    
    http_response_code(200);
    echo json_encode(['status' => 'success', 'message' => 'Payment processed successfully']);
  } else {
    $trx->status = 1;
    $trx->save();
    http_response_code(200);
    echo json_encode(['status' => 'pending', 'message' => 'Payment not successful']);
  }
}


function paychangu_callback()
{
  global $config, $ui;

  // Skip ngrok browser warning
  header("ngrok-skip-browser-warning: any-value");

  $tx_ref = _get('tx_ref');

  if (empty($tx_ref)) {
    r2(U . 'order/package', 'e', Lang::T("Invalid transaction reference"));
  }

  // Find transaction by tx_ref
  $trx = ORM::for_table('tbl_payment_gateway')
    ->where('gateway_trx_id', $tx_ref)
    ->find_one();

  if (!$trx) {
    r2(U . 'order/package', 'e', Lang::T("Transaction not found"));
  }

  // Poll for webhook receipt (up to 2 minutes)
  $max_attempts = 24; // 2 minutes with 5-second intervals
  $attempt = 0;
  $webhook_received = false;

  while ($attempt < $max_attempts && !$webhook_received) {
    // Reload transaction to check webhook status
    $trx = ORM::for_table('tbl_payment_gateway')
      ->where('gateway_trx_id', $tx_ref)
      ->find_one();

    if ($trx && $trx['webhook_received'] == 1) {
      $webhook_received = true;
      _log("PayChangu Callback: Webhook received for tx_ref: " . $tx_ref);
      break;
    }

    $attempt++;
    if ($attempt < $max_attempts) {
      sleep(5); // Wait 5 seconds before next poll
    }
  }

  // If webhook not received, verify payment directly with PayChangu API
  if (!$webhook_received) {
    _log("PayChangu Callback: Webhook not received, verifying directly with API for tx_ref: " . $tx_ref);
    
    $verified = paychangu_verify_transaction($tx_ref, $trx['price'], $config['paychangu_currency'] ?: 'MWK');
    
    if ($verified) {
      // Process the payment
      $user = ORM::for_table('tbl_customers')
        ->where('username', $trx['username'])
        ->find_one();
      
      if ($user) {
        if (!Package::rechargeUser($user['id'], $trx['routers'], $trx['plan_id'], $trx['gateway'], 'PayChangu')) {
          _log("PayChangu Callback: Payment verified but failed to activate package for user: " . $user['username']);
          sendTelegram("PayChangu Callback: Payment activation failed for user: " . $user['username']);
        } else {
          _log("PayChangu Callback: Payment verified and package activated for user: " . $user['username']);
        }
      }

      // Update transaction record
      $trx->payment_method = 'PayChangu';
      $trx->paid_date = date('Y-m-d H:i:s');
      $trx->status = 2;
      $trx->webhook_received = 1; // Mark as processed even though webhook didn't arrive
      $trx->webhook_received_date = date('Y-m-d H:i:s');
      $trx->save();
      
      _log("PayChangu Callback: Payment processed via direct verification");
    } else {
      _log("PayChangu Callback: Payment verification failed - tx_ref: " . $tx_ref);
    }
  }

  // Get local server IP for redirect
  $local_ip = '10.129.170.126';
  $invoice_id = $trx['id'];

  // Redirect to local IP with invoice ID as identifier
  $redirect_url = "http://" . $local_ip . "/?_route=order/view/" . $invoice_id . "&tx_ref=" . $tx_ref;

  _log("PayChangu Callback: Redirecting to local IP - webhook received: " . ($webhook_received ? 'yes' : 'no (verified directly)'));

  header("Location: " . $redirect_url);
  exit;
}


function paychangu_verify_transaction($tx_ref, $expected_amount = null, $expected_currency = 'MWK')
{
  global $config;

  $url = 'https://api.paychangu.com/verify-payment/' . $tx_ref;

  $curl = curl_init();
  curl_setopt_array($curl, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => [
      'Content-Type: application/json',
      'Authorization: Bearer ' . $config['paychangu_secret_key']
    ]
  ]);
  $response = curl_exec($curl);
  $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
  curl_close($curl);

  $responseData = json_decode($response);

  $logFile = "PayChanguVerify.json";
  $log = fopen($logFile, "a");
  fwrite($log, date('Y-m-d H:i:s') . " - TX_REF: " . $tx_ref . " - Response: " . $response . "\n");
  fclose($log);

  if (isset($responseData->status) && $responseData->status == 'success' && isset($responseData->data->status)) {
    // Comprehensive verification as per PayChangu recommendations
    $paymentData = $responseData->data;
    
    // 1. Verify status is success
    if ($paymentData->status != 'success') {
      _log("PayChangu Verification failed: Status not successful - " . $paymentData->status);
      return false;
    }
    
    // 2. Verify transaction reference matches (if we have the original)
    if (isset($paymentData->tx_ref) && $paymentData->tx_ref != $tx_ref) {
      _log("PayChangu Verification failed: TX_REF mismatch - Expected: " . $tx_ref . ", Got: " . $paymentData->tx_ref);
      return false;
    }
    
    // 3. Verify currency matches expected
    if ($expected_currency && isset($paymentData->currency) && $paymentData->currency != $expected_currency) {
      _log("PayChangu Verification failed: Currency mismatch - Expected: " . $expected_currency . ", Got: " . $paymentData->currency);
      return false;
    }
    
    // 4. Verify amount is greater than or equal to expected amount
    if ($expected_amount && isset($paymentData->amount)) {
      $paidAmount = floatval($paymentData->amount);
      $expectedAmount = floatval($expected_amount);
      
      if ($paidAmount < $expectedAmount) {
        _log("PayChangu Verification failed: Insufficient amount - Expected: " . $expected_amount . ", Got: " . $paidAmount);
        return false;
      }
      
      // Log if amount is greater (for potential refund)
      if ($paidAmount > $expectedAmount) {
        _log("PayChangu Verification: Amount greater than expected - Expected: " . $expected_amount . ", Got: " . $paidAmount . " (may need refund)");
      }
    }
    
    return true;
  }

  _log("PayChangu Verification failed: Invalid API response - HTTP Code: " . $httpCode);
  return false;
}


/**
 * Background job to check pending transactions (webhook fallback)
 * This should be called periodically (e.g., every 5-15 minutes) via cron job
 * to catch any payments where webhooks failed
 * 
 * Includes rate limiting to avoid API rate limits
 */
function paychangu_check_pending_transactions()
{
  global $config;
  
  // Find pending PayChangu transactions that are not expired
  // Limit to most recent 20 transactions to avoid rate limiting
  $pendingTransactions = ORM::for_table('tbl_payment_gateway')
    ->where('gateway', 'PayChangu')
    ->where('status', 1) // Pending status
    ->where_gt('expired_date', date('Y-m-d H:i:s'))
    ->order_by_desc('id')
    ->limit(20)
    ->find_many();
  
  $processed_count = 0;
  $total_count = count($pendingTransactions);
  
  foreach ($pendingTransactions as $index => $trx) {
    $tx_ref = $trx['gateway_trx_id'];
    
    if (empty($tx_ref)) {
      continue;
    }
    
    // Rate limiting: add delay between API calls if processing multiple transactions
    if ($index > 0) {
      usleep(500000); // 0.5 second delay between API calls
    }
    
    // Verify transaction status
    $verified = paychangu_verify_transaction($tx_ref, $trx['price'], $config['paychangu_currency'] ?: 'MWK');
    
    if ($verified) {
      // Process the payment
      $user = ORM::for_table('tbl_customers')
        ->where('username', $trx['username'])
        ->find_one();
      
      if ($user) {
        if (!Package::rechargeUser($user['id'], $trx['routers'], $trx['plan_id'], $trx['gateway'], 'PayChangu')) {
          _log("PayChangu Background Job: Payment verification successful, but failed to activate package for user: " . $user['username']);
          sendTelegram("PayChangu Background Job: Payment activation failed for user: " . $user['username']);
        } else {
          _log("PayChangu Background Job: Payment verification successful and package activated for user: " . $user['username']);
          $processed_count++;
          
          // Update transaction record
          $trx->status = 2;
          $trx->paid_date = date('Y-m-d H:i:s');
          $trx->payment_method = 'PayChangu';
          $trx->payment_channel = 'Background Verification';
          $trx->save();
        }
      }
    }
  }
  
  if ($processed_count > 0) {
    _log("PayChangu Background Job: Processed " . $processed_count . " out of " . $total_count . " pending transactions");
    sendTelegram("PayChangu Background Job: Successfully processed " . $processed_count . " pending transactions");
  }
  
  // If we hit the limit, there might be more pending transactions
  if ($total_count >= 20) {
    _log("PayChangu Background Job: Hit transaction limit (20). There may be more pending transactions.");
  }
  
  return $processed_count;
}


function paychangu_get_status($trx, $user)
{
  global $config;

  $tx_ref = $trx['gateway_trx_id'];
  $verified = paychangu_verify_transaction($tx_ref, $trx['price'], $config['paychangu_currency'] ?: 'MWK');

  if ($verified) {
    // Check if already processed
    if ($trx['status'] == 2) {
      r2(U . "order/view/" . $trx['id'], 's', Lang::T("Transaction successful."));
    } else {
      // Process the payment
      $userObj = ORM::for_table('tbl_customers')
        ->where('username', $trx['username'])
        ->find_one();

      if ($userObj) {
        if (!Package::rechargeUser($userObj['id'], $trx['routers'], $trx['plan_id'], $trx['gateway'], 'PayChangu')) {
          _log("PayChangu Payment Verification Successful, But Failed to activate Package");
        }
      }

      $trx->payment_method = 'PayChangu';
      $trx->paid_date = date('Y-m-d H:i:s');
      $trx->status = 2;
      $trx->save();

      r2(U . "order/view/" . $trx['id'], 's', Lang::T("Transaction successful."));
    }
  } else {
    r2(U . "order/view/" . $trx['id'], 'w', Lang::T("Transaction still unpaid."));
  }
}
