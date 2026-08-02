<?php

/**
 * GET /api/announcement
 * Customer announcement endpoint
 */

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendJsonResponse(false, null, 'Method not allowed', 405);
}

global $PAGES_PATH;
$announcementFile = $PAGES_PATH . '/Announcement_Customer.html';

if (file_exists($announcementFile)) {
    $content = file_get_contents($announcementFile);
    sendJsonResponse(true, [
        'announcement' => $content
    ], 'Announcement retrieved successfully');
} else {
    sendJsonResponse(true, [
        'announcement' => ''
    ], 'No announcement configured');
}
