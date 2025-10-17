<?php
/**
 * Guardarian Email Handler
 * 
 * Manages email notifications for transactions
 */

if (!defined('ABSPATH')) {
    exit;
}

class Guardarian_Email {
    
    /**
     * Send admin notification
     */
    public static function send_admin_notification($transaction_id, $status = null) {
        if (!get_option('guardarian_admin_email_enabled', true)) {
            return false;
        }
        
        $transaction = Guardarian_Database::get_transaction($transaction_id);
        
        if (!$transaction) {
            return false;
        }
        
        $status = $status ?? $transaction['status'];
        
        $admin_email = get_option('guardarian_admin_email', get_option('admin_email'));
        $site_name = get_bloginfo('name');
        
        $subject = get_option('guardarian_admin_email_subject', 'New Transaction Notification');
        $body = get_option('guardarian_admin_email_body', 'A new transaction with ID {transaction_id} has been created.');

        $subject = self::replace_placeholders($subject, $transaction);
        $message = self::replace_placeholders($body, $transaction);
        
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $site_name . ' <' . get_option('admin_email') . '>',
        ];
        
        return wp_mail($admin_email, $subject, $message, $headers);
    }
    
    /**
     * Send customer notification
     */
    public static function send_customer_notification($transaction_id, $status = null) {
        if (!get_option('guardarian_customer_email_enabled', false)) {
            return false;
        }
        
        $transaction = Guardarian_Database::get_transaction($transaction_id);
        
        if (!$transaction || empty($transaction['customer_email'])) {
            return false;
        }
        
        $status = $status ?? $transaction['status'];
        
        $site_name = get_bloginfo('name');
        
        $subject = get_option('guardarian_customer_email_subject', 'Your Transaction Details');
        $body = get_option('guardarian_customer_email_body', 'Your transaction with ID {transaction_id} is now {status}.');

        $subject = self::replace_placeholders($subject, $transaction);
        $message = self::replace_placeholders($body, $transaction);
        
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $site_name . ' <' . get_option('admin_email') . '>',
        ];
        
        return wp_mail($transaction['customer_email'], $subject, $message, $headers);
    }
    
    private static function replace_placeholders($string, $transaction) {
        $placeholders = [
            '{transaction_id}' => $transaction['transaction_id'],
            '{status}' => $transaction['status'],
            '{amount_from}' => $transaction['amount_from'],
            '{currency_from}' => $transaction['currency_from'],
            '{amount_to}' => $transaction['amount_to'],
            '{currency_to}' => $transaction['currency_to'],
            '{customer_email}' => $transaction['customer_email'],
            '{customer_name}' => $transaction['customer_name'],
        ];

        return str_replace(array_keys($placeholders), array_values($placeholders), $string);
    }
    
    /**
     * Get status color for badge
     */
    private static function get_status_color($status) {
        $colors = [
            'success' => '#28a745',
            'completed' => '#28a745',
            'finished' => '#28a745',
            'pending' => '#ffc107',
            'processing' => '#17a2b8',
            'failed' => '#dc3545',
            'error' => '#dc3545',
            'cancelled' => '#6c757d',
            'expired' => '#6c757d',
        ];
        
        return $colors[$status] ?? '#6c757d';
    }
}