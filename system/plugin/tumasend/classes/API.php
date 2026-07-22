<?php

namespace TumaSend;

/**
 * TumaSend API Client
 * 
 * Handles all HTTP requests to the TumaSend API
 */
class API
{
    private $config;
    
    public function __construct(Config $config)
    {
        $this->config = $config;
    }
    
    /**
     * Send SMS via TumaSend API
     * 
     * @param string $from Sender ID
     * @param array $recipients Array of phone numbers in E.164 format
     * @param string $message Message content
     * @return array API response
     * @throws \Exception on API error
     */
    public function sendSMS($from, $recipients, $message)
    {
        $url = $this->config->getApiBaseUrl() . '/api/v1/send/sms';
        
        $data = [
            'from' => $from,
            'recipients' => is_array($recipients) ? $recipients : [$recipients],
            'message' => $message
        ];
        
        return $this->request('POST', $url, $data);
    }
    
    /**
     * Get account balance
     * 
     * @return array Balance information
     * @throws \Exception on API error
     */
    public function getBalance()
    {
        $url = $this->config->getApiBaseUrl() . '/api/v1/balance';
        return $this->request('GET', $url);
    }
    
    /**
     * Get batch information
     * 
     * @param string $batchId Batch ID
     * @return array Batch information
     * @throws \Exception on API error
     */
    public function getBatch($batchId)
    {
        $url = $this->config->getApiBaseUrl() . '/api/v1/batches/' . $batchId;
        return $this->request('GET', $url);
    }
    
    /**
     * List batches
     * 
     * @param array $params Query parameters (limit, offset, etc.)
     * @return array List of batches
     * @throws \Exception on API error
     */
    public function listBatches($params = [])
    {
        $url = $this->config->getApiBaseUrl() . '/api/v1/batches';
        
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        
        return $this->request('GET', $url);
    }
    
    /**
     * Make HTTP request to TumaSend API
     * 
     * @param string $method HTTP method
     * @param string $url Request URL
     * @param array $data Request data
     * @return array Response data
     * @throws \Exception on API error
     */
    private function request($method, $url, $data = null)
    {
        $apiKey = $this->config->getApiKey();
        
        if (empty($apiKey)) {
            throw new \Exception('API key not configured');
        }
        
        // Determine header based on endpoint
        // Balance endpoint requires x-system-key, SMS send requires x-api-key
        $headerKey = (strpos($url, '/balance') !== false) ? 'x-system-key' : 'x-api-key';
        
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->config->getConnectionTimeout(),
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                $headerKey . ': ' . $apiKey
            ]
        ]);
        
        if ($method === 'POST' && $data !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new \Exception('cURL error: ' . $error);
        }
        
        $decoded = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON response: ' . $response);
        }
        
        // Handle HTTP errors
        if ($httpCode >= 400) {
            $errorMessage = $decoded['error'] ?? $decoded['message'] ?? 'Unknown error';
            throw new \Exception('API error (' . $httpCode . '): ' . $errorMessage);
        }
        
        // Handle rate limiting
        if ($httpCode === 429) {
            throw new \Exception('Rate limit exceeded');
        }
        
        return $decoded;
    }
    
    /**
     * Send request with retry logic
     * 
     * @param string $method HTTP method
     * @param string $url Request URL
     * @param array $data Request data
     * @param int $maxAttempts Maximum retry attempts
     * @return array Response data
     * @throws \Exception on API error after retries
     */
    public function requestWithRetry($method, $url, $data = null, $maxAttempts = null)
    {
        if ($maxAttempts === null) {
            $maxAttempts = $this->config->getRetryAttempts();
        }
        
        $lastException = null;
        $attempt = 0;
        
        while ($attempt < $maxAttempts) {
            try {
                return $this->request($method, $url, $data);
            } catch (\Exception $e) {
                $lastException = $e;
                $attempt++;
                
                // Don't retry on client errors (4xx except 429)
                if (strpos($e->getMessage(), 'API error (4') === 0 && strpos($e->getMessage(), '429') === false) {
                    throw $e;
                }
                
                // Don't retry on authentication errors
                if (strpos($e->getMessage(), '401') !== false || strpos($e->getMessage(), '403') !== false) {
                    throw $e;
                }
                
                // Exponential backoff
                if ($attempt < $maxAttempts) {
                    $delay = $this->config->getRetryDelay() * pow(2, $attempt - 1);
                    sleep($delay);
                }
            }
        }
        
        throw $lastException;
    }
}
