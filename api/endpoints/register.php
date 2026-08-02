<?php

/**
 * POST /api/register
 * Customer registration endpoint
 */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(false, null, 'Method not allowed', 405);
}

$body = getRequestBody();
$username = sanitizeInput($body['username'] ?? '');
$password = sanitizeInput($body['password'] ?? '');
$cpassword = sanitizeInput($body['cpassword'] ?? '');
$fullname = sanitizeInput($body['fullname'] ?? '');
$email = sanitizeInput($body['email'] ?? '');
$address = sanitizeInput($body['address'] ?? '');
$phone_number = sanitizeInput($body['phone_number'] ?? '');
$otp_code = sanitizeInput($body['otp_code'] ?? '');

// Check if registration is disabled
global $_c;
if ($_c['disable_registration'] == 'noreg') {
    sendJsonResponse(false, null, 'Registration is disabled', 403);
}

// Validate required fields
if (empty($username) || empty($password) || empty($cpassword)) {
    sendJsonResponse(false, null, 'Username, password, and confirm password are required', 400);
}

// Validate username length
if (!Validator::Length($username, 35, 2)) {
    sendJsonResponse(false, null, 'Username should be between 3 to 35 characters', 400);
}

// Validate password length
if (!Validator::Length($password, 35, 2)) {
    sendJsonResponse(false, null, 'Password should be between 3 to 35 characters', 400);
}

// Validate password match
if ($password !== $cpassword) {
    sendJsonResponse(false, null, 'Passwords do not match', 400);
}

// Validate fullname if required
global $config;
if ($config['man_fields_fname'] == 'yes') {
    if (empty($fullname)) {
        sendJsonResponse(false, null, 'Full name is required', 400);
    }
    if (!Validator::Length($fullname, 36, 2)) {
        sendJsonResponse(false, null, 'Full name should be between 3 to 36 characters', 400);
    }
}

// Validate email if required
if ($config['man_fields_email'] == 'yes') {
    if (empty($email)) {
        sendJsonResponse(false, null, 'Email is required', 400);
    }
    if (!Validator::Email($email)) {
        sendJsonResponse(false, null, 'Email is not valid', 400);
    }
}

// Handle OTP verification if enabled
if ($_c['sms_otp_registration'] == 'yes') {
    if (empty($phone_number)) {
        sendJsonResponse(false, null, 'Phone number is required for OTP registration', 400);
    }
    if (empty($otp_code)) {
        sendJsonResponse(false, null, 'OTP code is required', 400);
    }
    
    global $db_pass, $CACHE_PATH;
    $otpPath = $CACHE_PATH . File::pathFixer('/sms/') . sha1("$phone_number$db_pass") . ".txt";
    
    run_hook('validate_otp');
    
    // Expire after 10 minutes
    if (file_exists($otpPath) && time() - filemtime($otpPath) > 1200) {
        unlink($otpPath);
        sendJsonResponse(false, null, 'Verification code expired', 400);
    } else if (file_exists($otpPath)) {
        $code = file_get_contents($otpPath);
        if ($code != $otp_code) {
            sendJsonResponse(false, null, 'Wrong verification code', 400);
        } else {
            unlink($otpPath);
        }
    } else {
        sendJsonResponse(false, null, 'No verification code found', 400);
    }
} else {
    // Use username as phone number if OTP is not enabled
    $phone_number = $username;
}

// Check if username already exists
$existing = ORM::for_table('tbl_customers')->where('username', $username)->find_one();
if ($existing) {
    sendJsonResponse(false, null, 'Account already exists', 409);
}

// Create customer
$customer = ORM::for_table('tbl_customers')->create();
$customer->username = alphanumeric($username, "+_.@-");
$customer->password = Password::_crypt($password);
$customer->fullname = $fullname;
$customer->address = $address;
$customer->email = $email;
$customer->phonenumber = $phone_number;

if ($customer->save()) {
    $userId = $customer->id();
    
    // Handle photo upload if enabled
    if ($config['photo_register'] == 'yes' && isset($_FILES['photo']) && !empty($_FILES['photo']['name']) && file_exists($_FILES['photo']['tmp_name'])) {
        if (function_exists('imagecreatetruecolor')) {
            global $UPLOAD_PATH;
            $hash = md5_file($_FILES['photo']['tmp_name']);
            $subfolder = substr($hash, 0, 2);
            $folder = $UPLOAD_PATH . DIRECTORY_SEPARATOR . 'photos' . DIRECTORY_SEPARATOR;
            if (!file_exists($folder)) {
                mkdir($folder);
            }
            $folder = $UPLOAD_PATH . DIRECTORY_SEPARATOR . 'photos' . DIRECTORY_SEPARATOR . $subfolder . DIRECTORY_SEPARATOR;
            if (!file_exists($folder)) {
                mkdir($folder);
            }
            $imgPath = $folder . $hash . '.jpg';
            File::resizeCropImage($_FILES['photo']['tmp_name'], $imgPath, 1600, 1600, 100);
            $customer->photo = '/photos/' . $subfolder . '/' . $hash . '.jpg';
            $customer->save();
        }
    }
    if (isset($_FILES['photo']) && file_exists($_FILES['photo']['tmp_name'])) {
        unlink($_FILES['photo']['tmp_name']);
    }
    
    // Set custom fields
    User::setFormCustomField($userId);
    
    // Run hooks
    run_hook('register_user');
    
    // Send admin notification if enabled
    if ($config['reg_nofify_admin'] == 'yes') {
        sendTelegram($config['CompanyName'] . ' - New User Registration' . "\n\nFull Name: " . $fullname . "\nUsername: " . $username . "\nEmail: " . $email . "\nPhone Number: " . $phone_number . "\nAddress: " . $address);
    }
    
    // Return success response
    $userData = [
        'id' => (int)$customer['id'],
        'username' => $customer['username'],
        'fullname' => $customer['fullname'],
        'email' => $customer['email'],
        'phone' => $customer['phonenumber'],
        'status' => $customer['status']
    ];
    
    sendJsonResponse(true, [
        'user' => $userData
    ], 'Registration successful');
} else {
    sendJsonResponse(false, null, 'Failed to register', 500);
}
