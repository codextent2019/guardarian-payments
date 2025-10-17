<?php
/**
 * Guardarian Logger
 * 
 * Handles all logging operations for debugging and monitoring
 */

if (!defined('ABSPATH')) {
    exit;
}

class Guardarian_Logger {
    
    /**
     * Log types
     */
    const TYPE_API = 'api_call';
    const TYPE_DATABASE = 'database';
    const TYPE_WEBHOOK = 'webhook';
    const TYPE_ERROR = 'error';
    const TYPE_INFO = 'info';
    const TYPE_DEBUG = 'debug';
    
    /**
     * Severity levels
     */
    const SEVERITY_CRITICAL = 'critical';
    const SEVERITY_ERROR = 'error';
    const SEVERITY_WARNING = 'warning';
    const SEVERITY_INFO = 'info';
    const SEVERITY_DEBUG = 'debug';
    
    /**
     * Log a message
     */
    public static function log($message, $severity = self::SEVERITY_INFO, $context = []) {
        global $wpdb;
        
        // Check if debug logging is enabled for debug messages
        if ($severity === self::SEVERITY_DEBUG && 
            !get_option('guardarian_debug_logging', false)) {
            return;
        }
        
        $table = Guardarian_Database::get_logs_table();
        
        $log_type = self::determine_log_type($context);
        
        $data = [
            'log_type' => $log_type,
            'severity' => $severity,
            'message' => $message,
            'context' => !empty($context) ? json_encode($context) : null,
            'transaction_id' => $context['transaction_id'] ?? null,
            'user_id' => get_current_user_id() ?: null,
            'ip_address' => self::get_client_ip(),
            'created_at' => current_time('mysql'),
        ];
        
        $wpdb->insert($table, $data);
        
        // Also log to WordPress error log for critical errors
        if ($severity === self::SEVERITY_CRITICAL || $severity === self::SEVERITY_ERROR) {
            error_log("Guardarian Plugin [$severity]: $message");
        }
    }
    
    /**
     * Determine log type from context
     */
    private static function determine_log_type($context) {
        if (isset($context['webhook'])) {
            return self::TYPE_WEBHOOK;
        }
        if (isset($context['endpoint']) || isset($context['api'])) {
            return self::TYPE_API;
        }
        if (isset($context['database']) || isset($context['query'])) {
            return self::TYPE_DATABASE;
        }
        return self::TYPE_INFO;
    }
    
    /**
     * Get client IP address
     */
    private static function get_client_ip() {
        $ip = '';
        
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        }
        
        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '';
    }
    
    /**
     * Get logs with filters
     */
    public static function get_logs($args = []) {
        global $wpdb;
        
        $table = Guardarian_Database::get_logs_table();
        
        $defaults = [
            'log_type' => '',
            'severity' => '',
            'date_from' => '',
            'date_to' => '',
            'search' => '',
            'transaction_id' => '',
            'orderby' => 'created_at',
            'order' => 'DESC',
            'limit' => 50,
            'offset' => 0,
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        $where = ['1=1'];
        $query_args = [];
        
        // Log type filter
        if (!empty($args['log_type'])) {
            $where[] = 'log_type = %s';
            $query_args[] = $args['log_type'];
        }
        
        // Severity filter
        if (!empty($args['severity'])) {
            $where[] = 'severity = %s';
            $query_args[] = $args['severity'];
        }
        
        // Date range filter
        if (!empty($args['date_from'])) {
            $where[] = 'created_at >= %s';
            $query_args[] = $args['date_from'] . ' 00:00:00';
        }
        
        if (!empty($args['date_to'])) {
            $where[] = 'created_at <= %s';
            $query_args[] = $args['date_to'] . ' 23:59:59';
        }
        
        // Transaction ID filter
        if (!empty($args['transaction_id'])) {
            $where[] = 'transaction_id = %s';
            $query_args[] = $args['transaction_id'];
        }
        
        // Search filter
        if (!empty($args['search'])) {
            $where[] = '(message LIKE %s OR context LIKE %s)';
            $search_term = '%' . $wpdb->esc_like($args['search']) . '%';
            $query_args[] = $search_term;
            $query_args[] = $search_term;
        }
        
        $where_clause = implode(' AND ', $where);
        
        $orderby = sanitize_sql_orderby($args['orderby'] . ' ' . $args['order']);
        if (!$orderby) {
            $orderby = 'created_at DESC';
        }
        
        $query = "SELECT * FROM $table WHERE $where_clause ORDER BY $orderby LIMIT %d OFFSET %d";
        $query_args[] = $args['limit'];
        $query_args[] = $args['offset'];
        
        if (!empty($query_args)) {
            $query = $wpdb->prepare($query, $query_args);
        }
        
        return $wpdb->get_results($query, ARRAY_A);
    }
    
    /**
     * Get logs count
     */
    public static function get_logs_count($args = []) {
        global $wpdb;
        
        $table = Guardarian_Database::get_logs_table();
        
        $where = ['1=1'];
        $query_args = [];
        
        if (!empty($args['log_type'])) {
            $where[] = 'log_type = %s';
            $query_args[] = $args['log_type'];
        }
        
        if (!empty($args['severity'])) {
            $where[] = 'severity = %s';
            $query_args[] = $args['severity'];
        }
        
        if (!empty($args['date_from'])) {
            $where[] = 'created_at >= %s';
            $query_args[] = $args['date_from'] . ' 00:00:00';
        }
        
        if (!empty($args['date_to'])) {
            $where[] = 'created_at <= %s';
            $query_args[] = $args['date_to'] . ' 23:59:59';
        }
        
        if (!empty($args['transaction_id'])) {
            $where[] = 'transaction_id = %s';
            $query_args[] = $args['transaction_id'];
        }
        
        if (!empty($args['search'])) {
            $where[] = '(message LIKE %s OR context LIKE %s)';
            $search_term = '%' . $wpdb->esc_like($args['search']) . '%';
            $query_args[] = $search_term;
            $query_args[] = $search_term;
        }
        
        $where_clause = implode(' AND ', $where);
        $query = "SELECT COUNT(*) FROM $table WHERE $where_clause";
        
        if (!empty($query_args)) {
            $query = $wpdb->prepare($query, $query_args);
        }
        
        return (int) $wpdb->get_var($query);
    }
    
    /**
     * Delete old logs
     */
    public static function delete_old_logs($days = 30) {
        global $wpdb;
        
        $table = Guardarian_Database::get_logs_table();
        $date = date('Y-m-d H:i:s', strtotime("-$days days"));
        
        return $wpdb->query($wpdb->prepare(
            "DELETE FROM $table WHERE created_at < %s",
            $date
        ));
    }
    
    /**
     * Clear all logs
     */
    public static function clear_logs() {
        global $wpdb;
        
        $table = Guardarian_Database::get_logs_table();
        
        return $wpdb->query("TRUNCATE TABLE $table");
    }
    
    /**
     * Export logs to file
     */
    public static function export_logs($args = []) {
        $logs = self::get_logs(array_merge($args, ['limit' => 10000, 'offset' => 0]));
        
        $filename = 'guardarian-logs-' . date('Y-m-d-His') . '.csv';
        $filepath = wp_upload_dir()['path'] . '/' . $filename;
        
        $fp = fopen($filepath, 'w');
        
        // Write headers
        fputcsv($fp, ['ID', 'Type', 'Severity', 'Message', 'Transaction ID', 'Created At']);
        
        // Write data
        foreach ($logs as $log) {
            fputcsv($fp, [
                $log['id'],
                $log['log_type'],
                $log['severity'],
                $log['message'],
                $log['transaction_id'] ?? '',
                $log['created_at'],
            ]);
        }
        
        fclose($fp);
        
        return $filepath;
    }
}