<?php

namespace TumaSend;

use ORM;

/**
 * Template Manager
 * 
 * Handles message template rendering with placeholder replacement
 */
class Template
{
    private $placeholders = [
        'customer_name' => 'Customer full name',
        'username' => 'Username',
        'voucher_code' => 'Voucher code',
        'plan_name' => 'Plan name',
        'expiry_date' => 'Expiration date',
        'amount' => 'Amount',
        'invoice_number' => 'Invoice number',
        'company_name' => 'Company name',
    ];
    
    /**
     * Get template by key
     * 
     * @param string $key Template key
     * @return array|false Template data or false if not found
     */
    public function get($key)
    {
        $template = ORM::for_table('tbl_tumasend_templates')
            ->where('template_key', $key)
            ->find_one();
        
        if ($template) {
            return [
                'id' => $template->id,
                'key' => $template->template_key,
                'name' => $template->template_name,
                'content' => $template->template_content
            ];
        }
        
        return false;
    }
    
    /**
     * Render template with data
     * 
     * @param string $templateKey Template key
     * @param array $data Data to replace placeholders
     * @return string Rendered message
     */
    public function render($templateKey, $data = [])
    {
        $template = $this->get($templateKey);
        
        if (!$template) {
            return '';
        }
        
        $message = $template['content'];
        
        // Replace placeholders
        foreach ($data as $key => $value) {
            $placeholder = '{' . $key . '}';
            $message = str_replace($placeholder, $value, $message);
        }
        
        return $message;
    }
    
    /**
     * Get available placeholders
     * 
     * @return array Placeholders with descriptions
     */
    public function getPlaceholders()
    {
        return $this->placeholders;
    }
    
    /**
     * Create or update template
     * 
     * @param string $key Template key
     * @param string $name Template name
     * @param string $content Template content
     * @return bool Success status
     */
    public function save($key, $name, $content)
    {
        $template = ORM::for_table('tbl_tumasend_templates')
            ->where('template_key', $key)
            ->find_one();
        
        if ($template) {
            $template->template_name = $name;
            $template->template_content = $content;
            $template->save();
        } else {
            $template = ORM::for_table('tbl_tumasend_templates')->create();
            $template->template_key = $key;
            $template->template_name = $name;
            $template->template_content = $content;
            $template->save();
        }
        
        return true;
    }
    
    /**
     * Get all templates
     * 
     * @return array All templates
     */
    public function getAll()
    {
        $templates = ORM::for_table('tbl_tumasend_templates')
            ->order_by_asc('template_name')
            ->find_many();
        
        $result = [];
        foreach ($templates as $template) {
            $result[] = [
                'id' => $template->id,
                'key' => $template->template_key,
                'name' => $template->template_name,
                'content' => $template->template_content
            ];
        }
        
        return $result;
    }
}

