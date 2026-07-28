<?php

/**
 * GET /api/support
 * Get customer support information
 */

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendJsonResponse(false, null, 'Method not allowed', 405);
}

// Support information is typically configured in the app config
// This endpoint returns static support information that can be customized
global $config;

$supportData = [
    'company_name' => $config['CompanyName'] ?? 'SmartNeti',
    'support_email' => $config['support_email'] ?? 'support@smartneti.com',
    'support_phone' => $config['support_phone'] ?? '',
    'support_whatsapp' => $config['support_whatsapp'] ?? '',
    'business_hours' => $config['business_hours'] ?? '24/7',
    'website' => $config['website'] ?? '',
    'social_media' => [
        'facebook' => $config['facebook'] ?? '',
        'twitter' => $config['twitter'] ?? '',
        'instagram' => $config['instagram'] ?? ''
    ],
    'help_center_url' => $config['help_center_url'] ?? '',
    'faq_url' => $config['faq_url'] ?? ''
];

sendJsonResponse(true, $supportData);
