<?php
/**
 * Simple SMS Test - Hardcoded values for testing TumaSend API
 */

// Hardcoded test values
$recipient = '+265893233816';  // E.164 format for Malawi
$apiKey = 'lc44a0e0_57e84e00970fcdf6b6ef543050a1652e624abc981042a2bb367a5a6494f418df';
$sender = 'TumaSend';
$message = 'Test message from SmartNeti SMS test function';

// API endpoint
$url = 'https://gateway.tumasend.com/api/v1/send/sms';

// Prepare data
$data = [
    'from' => $sender,
    'recipients' => [$recipient],
    'message' => $message
];

// Initialize cURL
$ch = curl_init($url);

curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'x-api-key: ' . $apiKey
    ],
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false
]);

// Execute request
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

// Display results
?>
<!DOCTYPE html>
<html>
<head>
    <title>SMS Test</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        button:hover { background: #0056b3; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>SMS Test Function</h1>
    
    <div class="info">
        <strong>Test Configuration:</strong><br>
        Recipient: <?php echo htmlspecialchars($recipient); ?><br>
        Sender: <?php echo htmlspecialchars($sender); ?><br>
        Message: <?php echo htmlspecialchars($message); ?><br>
        API Key: <?php echo htmlspecialchars(substr($apiKey, 0, 20)) . '...'; ?>
    </div>

    <form method="POST">
        <button type="submit" name="send_sms">Send Test SMS</button>
    </form>

    <?php if (isset($_POST['send_sms'])): ?>
        <h2>Test Results</h2>
        
        <?php if ($error): ?>
            <div class="error">
                <strong>cURL Error:</strong> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="info">
            <strong>HTTP Status Code:</strong> <?php echo $httpCode; ?>
        </div>

        <div class="info">
            <strong>Raw Response:</strong>
            <pre><?php echo htmlspecialchars($response); ?></pre>
        </div>

        <?php
        $decoded = json_decode($response, true);
        if ($decoded):
            if ($httpCode == 201 && isset($decoded['success']) && $decoded['success']):
                ?>
                <div class="success">
                    <strong>SMS Sent Successfully!</strong><br>
                    Batch ID: <?php echo htmlspecialchars($decoded['batch_id'] ?? 'N/A'); ?><br>
                    Credits Used: <?php echo htmlspecialchars($decoded['credits_used'] ?? 'N/A'); ?><br>
                    Credits Remaining: <?php echo htmlspecialchars($decoded['credits_remaining'] ?? 'N/A'); ?>
                </div>
                <?php
            else:
                ?>
                <div class="error">
                    <strong>API Error:</strong><br>
                    <?php echo htmlspecialchars($decoded['error'] ?? $decoded['message'] ?? 'Unknown error'); ?>
                </div>
                <?php
            endif;
        endif;
        ?>
    <?php endif; ?>

</body>
</html>
