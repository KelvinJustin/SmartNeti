<?php

namespace TumaSend;

/**
 * Queue Manager
 * 
 * Handles message queuing and background processing
 */
class Queue
{
    private $config;
    private $api;
    private $sms;
    private $logger;
    
    public function __construct(Config $config, API $api, SMS $sms, Logger $logger)
    {
        $this->config = $config;
        $this->api = $api;
        $this->sms = $sms;
        $this->logger = $logger;
    }
    
    /**
     * Enqueue message for processing
     * 
     * @param string $recipient Phone number
     * @param string $message Message content
     * @param int|null $customerId Customer ID
     * @param string $priority Priority level (high, normal, low)
     * @param int|null $scheduledAt Unix timestamp for scheduled send
     * @return int Queue item ID
     */
    public function enqueue($recipient, $message, $customerId = null, $priority = 'normal', $scheduledAt = null)
    {
        $queue = ORM::for_table('tbl_tumasend_queue')->create();
        $queue->recipient = $recipient;
        $queue->message = $message;
        $queue->customer_id = $customerId;
        $queue->priority = $priority;
        $queue->scheduled_at = $scheduledAt ? date('Y-m-d H:i:s', $scheduledAt) : null;
        $queue->attempts = 0;
        $queue->max_attempts = $this->config->getRetryAttempts();
        $queue->next_attempt_at = $scheduledAt ? date('Y-m-d H:i:s', $scheduledAt) : date('Y-m-d H:i:s');
        $queue->status = 'pending';
        $queue->save();
        
        $this->logger->log('Queue', $recipient, 'Message enqueued (ID: ' . $queue->id . ')', 'Success');
        
        return $queue->id;
    }
    
    /**
     * Process pending queue items
     * 
     * @param int $limit Maximum number of items to process
     * @return array Processing results
     */
    public function process($limit = 50)
    {
        if (!$this->config->isQueueEnabled()) {
            return ['processed' => 0, 'failed' => 0, 'skipped' => 0];
        }
        
        $processed = 0;
        $failed = 0;
        $skipped = 0;
        
        // Get pending items that are due for processing
        $items = ORM::for_table('tbl_tumasend_queue')
            ->where('status', 'pending')
            ->where_raw('next_attempt_at <= NOW()')
            ->where_raw('(scheduled_at IS NULL OR scheduled_at <= NOW())')
            ->order_by_asc('priority')
            ->order_by_asc('next_attempt_at')
            ->limit($limit)
            ->find_many();
        
        foreach ($items as $item) {
            // Mark as processing
            $item->status = 'processing';
            $item->save();
            
            try {
                // Send SMS
                $this->sms->send($item->recipient, $item->message);
                
                // Mark as completed
                $item->status = 'completed';
                $item->save();
                
                $processed++;
                
                $this->logger->log('Queue', $item->recipient, 'Queue item processed (ID: ' . $item->id . ')', 'Success');
            } catch (\Exception $e) {
                // Increment attempt count
                $item->attempts++;
                
                // Check if max attempts reached
                if ($item->attempts >= $item->max_attempts) {
                    $item->status = 'failed';
                    $item->error_message = $e->getMessage();
                    $item->save();
                    
                    $failed++;
                    
                    $this->logger->log('Queue', $item->recipient, 'Queue item failed after ' . $item->attempts . ' attempts (ID: ' . $item->id . ')', 'Error', $e->getMessage());
                } else {
                    // Calculate next attempt time with exponential backoff
                    $delay = $this->config->getRetryDelay() * pow(2, $item->attempts - 1);
                    $nextAttempt = date('Y-m-d H:i:s', time() + $delay);
                    
                    $item->status = 'pending';
                    $item->next_attempt_at = $nextAttempt;
                    $item->error_message = $e->getMessage();
                    $item->save();
                    
                    $skipped++;
                    
                    $this->logger->log('Queue', $item->recipient, 'Queue item retry scheduled (ID: ' . $item->id . ', attempt ' . $item->attempts . ')', 'Warning', $e->getMessage());
                }
            }
        }
        
        return [
            'processed' => $processed,
            'failed' => $failed,
            'skipped' => $skipped
        ];
    }
    
    /**
     * Retry failed queue item
     * 
     * @param int $queueId Queue item ID
     * @return bool Success status
     */
    public function retry($queueId)
    {
        $item = ORM::for_table('tbl_tumasend_queue')
            ->where('id', $queueId)
            ->find_one();
        
        if (!$item) {
            return false;
        }
        
        // Reset for retry
        $item->status = 'pending';
        $item->attempts = 0;
        $item->next_attempt_at = date('Y-m-d H:i:s');
        $item->error_message = null;
        $item->save();
        
        $this->logger->log('Queue', $item->recipient, 'Queue item queued for retry (ID: ' . $queueId . ')', 'Success');
        
        return true;
    }
    
    /**
     * Get queue statistics
     * 
     * @return array Queue statistics
     */
    public function getStats()
    {
        $stats = [
            'pending' => ORM::for_table('tbl_tumasend_queue')->where('status', 'pending')->count(),
            'processing' => ORM::for_table('tbl_tumasend_queue')->where('status', 'processing')->count(),
            'completed' => ORM::for_table('tbl_tumasend_queue')->where('status', 'completed')->count(),
            'failed' => ORM::for_table('tbl_tumasend_queue')->where('status', 'failed')->count(),
        ];
        
        $stats['total'] = array_sum($stats);
        
        return $stats;
    }
    
    /**
     * Clean up old completed/failed items
     * 
     * @param int $days Number of days to keep
     * @return int Number of items deleted
     */
    public function cleanup($days = 30)
    {
        $cutoff = date('Y-m-d H:i:s', time() - ($days * 86400));
        
        $deleted = ORM::for_table('tbl_tumasend_queue')
            ->where_raw('created_at < ?', [$cutoff])
            ->where_in('status', ['completed', 'failed'])
            ->delete_many();
        
        return $deleted;
    }
}
