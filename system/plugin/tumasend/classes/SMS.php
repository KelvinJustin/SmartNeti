<?php

namespace TumaSend;

/**
 * SMS Service
 * 
 * Handles SMS sending, phone number normalization, and message processing
 */
class SMS
{
    private $config;
    private $api;
    
    public function __construct(Config $config, API $api)
    {
        $this->config = $config;
        $this->api = $api;
    }
    
    /**
     * Send SMS
     * 
     * @param string $phone Phone number
     * @param string $message Message content
     * @return bool Success status
     * @throws \Exception on error
     */
    public function send($phone, $message)
    {
        // Normalize phone number
        $normalizedPhone = $this->normalizePhone($phone);
        
        if (!$normalizedPhone) {
            throw new \Exception('Invalid phone number: ' . $phone);
        }
        
        // Validate sender ID
        $senderId = $this->config->getSenderId();
        if (empty($senderId)) {
            throw new \Exception('Sender ID not configured');
        }
        
        // Calculate message segments
        $segments = $this->calculateSegments($message);
        
        // Warn if message exceeds single segment
        if ($segments > 1 && $this->config->isDebugLoggingEnabled()) {
            error_log('TumaSend: Message exceeds single segment (' . $segments . ' segments)');
        }
        
        try {
            // Send via API
            $response = $this->api->sendSMS($senderId, [$normalizedPhone], $message);
            
            // Store in history
            $this->storeSMSHistory(
                $response['batch_id'] ?? null,
                $normalizedPhone,
                $message,
                $response
            );
            
            return true;
        } catch (\Exception $e) {
            // Log error
            $this->storeSMSHistory(
                null,
                $normalizedPhone,
                $message,
                null,
                'failed',
                $e->getMessage()
            );
            
            throw $e;
        }
    }
    
    /**
     * Send bulk SMS
     * 
     * @param array $recipients Array of phone numbers
     * @param string $message Message content
     * @return array Results with batch_id
     * @throws \Exception on error
     */
    public function sendBulk($recipients, $message)
    {
        $normalizedRecipients = [];
        $invalidRecipients = [];
        
        foreach ($recipients as $phone) {
            $normalized = $this->normalizePhone($phone);
            if ($normalized) {
                $normalizedRecipients[] = $normalized;
            } else {
                $invalidRecipients[] = $phone;
            }
        }
        
        if (empty($normalizedRecipients)) {
            throw new \Exception('No valid recipients');
        }
        
        $senderId = $this->config->getSenderId();
        if (empty($senderId)) {
            throw new \Exception('Sender ID not configured');
        }
        
        try {
            $response = $this->api->sendSMS($senderId, $normalizedRecipients, $message);
            
            // Store each recipient in history
            foreach ($normalizedRecipients as $recipient) {
                $this->storeSMSHistory(
                    $response['batch_id'] ?? null,
                    $recipient,
                    $message,
                    $response
                );
            }
            
            return [
                'batch_id' => $response['batch_id'] ?? null,
                'total_recipients' => count($normalizedRecipients),
                'queued' => $response['queued'] ?? count($normalizedRecipients),
                'invalid_recipients' => $invalidRecipients,
                'credits_used' => $response['credits_used'] ?? 0,
                'credits_remaining' => $response['credits_remaining'] ?? 0
            ];
        } catch (\Exception $e) {
            // Store failed attempts
            foreach ($normalizedRecipients as $recipient) {
                $this->storeSMSHistory(
                    null,
                    $recipient,
                    $message,
                    null,
                    'failed',
                    $e->getMessage()
                );
            }
            
            throw $e;
        }
    }
    
    /**
     * Normalize phone number to E.164 format
     * 
     * Supports Malawi formats:
     * - 0991234567 -> +265991234567
     * - 991234567 -> +265991234567
     * - 265991234567 -> +265991234567
     * - +265991234567 -> +265991234567
     * 
     * @param string $phone Phone number
     * @return string|false Normalized number or false if invalid
     */
    public function normalizePhone($phone)
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Check length
        $length = strlen($phone);
        
        // Malawi country code is 265, phone numbers are 9 digits (excluding country code)
        // Total length should be 12 with country code
        
        if ($length === 9) {
            // Assume local format: 991234567
            return '+265' . $phone;
        } elseif ($length === 10 && substr($phone, 0, 1) === '0') {
            // Format: 0991234567
            return '+265' . substr($phone, 1);
        } elseif ($length === 12 && substr($phone, 0, 3) === '265') {
            // Format: 265991234567
            return '+' . $phone;
        } elseif ($length === 13 && substr($phone, 0, 4) === '2650') {
            // Format: 2650991234567 (unlikely but handle it)
            return '+265' . substr($phone, 4);
        }
        
        // Try to validate as E.164 if it starts with +
        if (substr($phone, 0, 1) === '+' || $length >= 10) {
            // Basic validation - should be at least 10 digits
            if ($length >= 10) {
                return '+' . $phone;
            }
        }
        
        return false;
    }
    
    /**
     * Calculate SMS segments based on message encoding
     * 
     * @param string $message Message content
     * @return int Number of segments
     */
    public function calculateSegments($message)
    {
        $length = mb_strlen($message);
        
        // Check if message contains GSM-7 characters only
        if ($this->isGSM7($message)) {
            // GSM-7: 160 chars per segment, 153 for concatenated
            if ($length <= 160) {
                return 1;
            }
            return (int) ceil($length / 153);
        } else {
            // Unicode: 70 chars per segment, 67 for concatenated
            if ($length <= 70) {
                return 1;
            }
            return (int) ceil($length / 67);
        }
    }
    
    /**
     * Check if message is GSM-7 encoded
     * 
     * @param string $message Message content
     * @return bool True if GSM-7, false if Unicode
     */
    private function isGSM7($message)
    {
        $gsm7Chars = 
            '\$£¥èéùìòÇØøÅåΔ_ΦΓΛΩΠΨΣΘΞ\x1BÆæßÉ !"#¤%&\'()*+,-./0123456789:;<=>?' .
            '¡ABCDEFGHIJKLMNOPQRSTUVWXYZÄÖÑÜ§¿abcdefghijklmnopqrstuvwxyzäöñüà';
        
        // Check each character
        for ($i = 0; $i < mb_strlen($message); $i++) {
            $char = mb_substr($message, $i, 1);
            
            // Check if character is in GSM-7 charset
            if (strpos($gsm7Chars, $char) === false) {
                // Check for extended GSM-7 characters
                if (!in_array($char, ['^', '{', '}', '\\', '[', '~', ']', '|', '€'])) {
                    return false;
                }
            }
        }
        
        return true;
    }
    
    /**
     * Store SMS in history
     * 
     * @param string|null $batchId Batch ID
     * @param string $recipient Phone number
     * @param string $message Message content
     * @param array|null $apiResponse API response
     * @param string $status Status
     * @param string|null $errorMessage Error message
     */
    private function storeSMSHistory($batchId, $recipient, $message, $apiResponse = null, $status = 'sent', $errorMessage = null)
    {
        $history = ORM::for_table('tbl_tumasend_sms_history')->create();
        $history->batch_id = $batchId;
        $history->recipient = $recipient;
        $history->message = $message;
        $history->status = $status;
        $history->queued_at = date('Y-m-d H:i:s');
        
        if ($status === 'sent') {
            $history->sent_at = date('Y-m-d H:i:s');
        }
        
        if ($apiResponse) {
            $history->credits_used = $apiResponse['credits_used'] ?? null;
            $history->credits_remaining = $apiResponse['credits_remaining'] ?? null;
            $history->environment = $apiResponse['environment'] ?? null;
            $history->raw_response = json_encode($apiResponse);
        }
        
        if ($errorMessage) {
            $history->error_message = $errorMessage;
        }
        
        $history->save();
    }
}
