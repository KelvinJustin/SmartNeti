<?php

/**
 * GET /api/banners
 * Get promotional banner images
 */

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendJsonResponse(false, null, 'Method not allowed', 405);
}

global $UI_PATH;

$banners = [
    [
        'id' => 1,
        'image' => '/ui/ui/images/promo-banner-1.png',
        'title' => 'Special Offer',
        'description' => 'Get amazing deals today'
    ],
    [
        'id' => 2,
        'image' => '/ui/ui/images/promo-banner-2.png',
        'title' => 'Fast Internet',
        'description' => 'Experience blazing speeds'
    ],
    [
        'id' => 3,
        'image' => '/ui/ui/images/promo-banner-3.jpg',
        'title' => 'Premium Plans',
        'description' => 'Choose the best for you'
    ]
];

sendJsonResponse(true, $banners, 'Banners retrieved successfully');
