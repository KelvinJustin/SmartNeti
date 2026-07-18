<?php

namespace TumaSend;

/**
 * Logger
 * 
 * Handles structured logging for plugin events
 */
class Logger
{
    /**
     * Log event to database
     * 
     * @param string $messageType Type of message (SMS, Webhook, API, etc.)
     * @param string $recipient Recipient identifier
     * @param string $messageContent Message content
     * @param string $status Status (Success, Error, Warning)
     * @param string|null $errorMessage Error message if any
     */
    public function log($messageType, $recipient, $messageContent, $status, $errorMessage = null)
    {
        $log = ORM::for_table('tbl_message_logs')->create();
        $log->message_type = 'TumaSend ' . $messageType;
        $log->recipient = $recipient;
        $log->message_content = $messageContent;
        $log->status = $status;
        $log->error_message = $errorMessage;
        $log->save();
    }
    
    /**
     * Get logs with filtering
     * 
     * @param array $filters Filter parameters
     * @param int $limit Limit results
     * @param int $offset Offset for pagination
     * @return array Log entries
     */
    public function getLogs($filters = [], $limit = 100, $offset = 0)
    {
        $query = ORM::for_table('tbl_message_logs')
            ->where_like('message_type', 'TumaSend%')
            ->order_by_desc('id')
            ->limit($limit)
            ->offset($offset);
        
        if (!empty($filters['message_type'])) {
            $query->where('message_type', 'TumaSend ' . $filters['message_type']);
        }
        
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (!empty($filters['recipient'])) {
            $query->where_like('recipient', '%' . $filters['recipient'] . '%');
        }
        
        return $query->find_many();
    }
}
