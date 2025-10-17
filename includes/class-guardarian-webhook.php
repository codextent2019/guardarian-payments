<?php
/**
 * Guardarian Webhook Handler
 * 
 * Processes webhook callbacks from Guardarian API
 */

if (!defined('ABSPATH')) {
    exit;
}

class Guardarian_Webhook {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', [$this, 'register_webhook_endpoint']);
        add_action('template_redirect', [$this, 'handle_webhook_request']);
    }
    
    /**
     * Register webhook endpoint
     */
    public function register_webhook_endpoint() {
        add_rewrite_rule(
            '^guardarian-webhook/?$',
            'index.php?guardarian_webhook=1',
            'top'
        );
        
        add_filter('query_vars', function($vars) {
            $vars[] = 'guardarian_webhook';
            return $vars;
        });
    }
    
    /**
     * Handle webhook request
     */
    public function handle_webhook_request() {
        if (!get_query_var('guardarian_webhook')) {
            return;
        }
        
        // Check if webhooks are enabled
        if (!get_option('guardarian_webhook_enabled', true)) {
            $this->send_response(['error' => 'Webhooks disabled'], 403);
        }
        
        // Get raw POST data
        $raw_body = file_get_contents('php://input');
        
        if (empty($raw_body)) {
            Guardarian_Logger::log(
                'Webhook received with empty body',
                'warning',
                ['webhook' => true]
            );
            $this->send_response(['error' => 'Empty request body'], 400);
        }
        
        // Verify webhook signature if secret is set
        $webhook_secret = get_option('guardarian_webhook_secret', '');
        if (!empty($webhook_secret)) {
            $signature = $_SERVER['HTTP_X_GUARDARIAN_SIGNATURE'] ?? '';
            
            if (!$this->verify_signature($raw_body, $signature, $webhook_secret)) {
                Guardarian_Logger::log(
                    'Webhook signature verification failed',
                    'error',
                    ['webhook' => true, 'signature' => $signature]
                );
                $this->send_response(['error' => 'Invalid signature'], 401);
            }
        }
        
        // Parse webhook data
        $webhook_data = json_decode($raw_body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            Guardarian_Logger::log(
                'Webhook JSON parsing failed: ' . json_last_error_msg(),
                'error',
                ['webhook' => true, 'raw_body' => $raw_body]
            );
            $this->send_response(['error' => 'Invalid JSON'], 400);
        }
        
        Guardarian_Logger::log(
            'Webhook received',
            'info',
            ['webhook' => true, 'data' => $webhook_data]
        );
        
        // Process webhook
        $this->process_webhook($webhook_data);
        
        // Send success response
        $this->send_response(['status' => 'success'], 200);
    }
    
    /**
     * Verify webhook signature
     */
    private function verify_signature($payload, $signature, $secret) {
        if (empty($signature)) {
            return false;
        }
        
        $expected_signature = hash_hmac('sha256', $payload, $secret);
        
        return hash_equals($expected_signature, $signature);
    }
    
    /**
     * Process webhook data
     */
    private function process_webhook($data) {
        // Extract transaction information
        $guardarian_id = $data['id'] ?? '';
        $status = $data['status'] ?? '';
        
        if (empty($guardarian_id)) {
            Guardarian_Logger::log(
                'Webhook missing transaction ID',
                'error',
                ['webhook' => true, 'data' => $data]
            );
            return;
        }
        
        // Find transaction by Guardarian ID
        $transaction = Guardarian_Database::get_transaction_by_guardarian_id($guardarian_id);
        
        if (!$transaction) {
            Guardarian_Logger::log(
                'Webhook for unknown transaction',
                'warning',
                ['webhook' => true, 'guardarian_id' => $guardarian_id]
            );
            return;
        }
        
        // Update transaction status
        $update_data = [
            'status' => $status,
            'api_response' => $data,
        ];
        
        // Add amount if provided
        if (isset($data['to_amount'])) {
            $update_data['amount_to'] = $data['to_amount'];
        }
        
        if (isset($data['rate'])) {
            $update_data['exchange_rate'] = $data['rate'];
        }
        
        // Mark as completed if status is success
        if (in_array($status, ['success', 'completed', 'finished'])) {
            $update_data['completed_at'] = current_time('mysql');
        }
        
        Guardarian_Database::update_transaction($transaction['transaction_id'], $update_data);
        
        Guardarian_Logger::log(
            'Transaction updated via webhook',
            'info',
            [
                'webhook' => true,
                'transaction_id' => $transaction['transaction_id'],
                'guardarian_id' => $guardarian_id,
                'status' => $status
            ]
        );
        
        // Send email notifications based on status
        $this->send_status_notifications($transaction['transaction_id'], $status);
    }
    
    /**
     * Send status notifications
     */
    private function send_status_notifications($transaction_id, $status) {
        $send_notifications = false;
        
        // Determine if we should send notifications for this status
        if (in_array($status, ['success', 'completed', 'finished'])) {
            $send_notifications = true;
        } elseif (in_array($status, ['failed', 'error', 'cancelled'])) {
            $send_notifications = true;
        }
        
        if (!$send_notifications) {
            return;
        }
        
        // Send admin notification
        if (get_option('guardarian_admin_email_enabled', true)) {
            Guardarian_Email::send_admin_notification($transaction_id, $status);
        }
        
        // Send customer notification if email is available
        if (get_option('guardarian_customer_email_enabled', false)) {
            $transaction = Guardarian_Database::get_transaction($transaction_id);
            if (!empty($transaction['customer_email'])) {
                Guardarian_Email::send_customer_notification($transaction_id, $status);
            }
        }
    }
    
    /**
     * Send HTTP response
     */
    private function send_response($data, $status_code = 200) {
        status_header($status_code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Get webhook URL
     */
    public static function get_webhook_url() {
        return home_url('/guardarian-webhook/');
    }
}