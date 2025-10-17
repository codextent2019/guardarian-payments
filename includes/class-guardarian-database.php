<?php
/**
 * Database Handler for Guardarian Plugin
 * 
 * Manages all database operations including table creation,
 * transaction storage, and log management.
 */

if (!defined('ABSPATH')) {
    exit;
}

class Guardarian_Database {
    
    /**
     * Get transactions table name
     */
    public static function get_transactions_table() {
        global $wpdb;
        return $wpdb->prefix . 'guardarian_transactions';
    }
    
    /**
     * Get logs table name
     */
    public static function get_logs_table() {
        global $wpdb;
        return $wpdb->prefix . 'guardarian_logs';
    }
    
    /**
     * Create database tables
     */
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Transactions table
        $transactions_table = self::get_transactions_table();
        $transactions_sql = "CREATE TABLE IF NOT EXISTS $transactions_table (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            transaction_id varchar(100) NOT NULL,
            guardarian_id varchar(100) DEFAULT NULL,
            status varchar(50) NOT NULL DEFAULT 'pending',
            amount_from decimal(20,8) NOT NULL,
            amount_to decimal(20,8) DEFAULT NULL,
            currency_from varchar(10) NOT NULL,
            currency_to varchar(10) NOT NULL,
            exchange_rate decimal(20,8) DEFAULT NULL,
            wallet_address varchar(255) NOT NULL,
            payment_url text DEFAULT NULL,
            customer_email varchar(255) DEFAULT NULL,
            customer_name varchar(255) DEFAULT NULL,
            redirect_url text DEFAULT NULL,
            api_response longtext DEFAULT NULL,
            error_message text DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            completed_at datetime DEFAULT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY transaction_id (transaction_id),
            KEY guardarian_id (guardarian_id),
            KEY status (status),
            KEY created_at (created_at),
            KEY customer_email (customer_email)
        ) $charset_collate;";
        
        // Logs table
        $logs_table = self::get_logs_table();
        $logs_sql = "CREATE TABLE IF NOT EXISTS $logs_table (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            log_type varchar(50) NOT NULL,
            severity varchar(20) NOT NULL DEFAULT 'info',
            message text NOT NULL,
            context longtext DEFAULT NULL,
            transaction_id varchar(100) DEFAULT NULL,
            user_id bigint(20) UNSIGNED DEFAULT NULL,
            ip_address varchar(45) DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY log_type (log_type),
            KEY severity (severity),
            KEY transaction_id (transaction_id),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($transactions_sql);
        dbDelta($logs_sql);
    }
    
    /**
     * Insert new transaction
     */
    public static function insert_transaction($data) {
        global $wpdb;
        
        $table = self::get_transactions_table();
        
        $transaction_id = self::generate_transaction_id();
        $defaults = [
            'transaction_id' => $transaction_id,
            'status' => 'pending',
            'currency_from' => 'USD',
            'currency_to' => 'USDC_ETH',
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql'),
        ];
        
        $data = wp_parse_args($data, $defaults);
        
        // Serialize API response if it's an array
        if (isset($data['api_response']) && is_array($data['api_response'])) {
            $data['api_response'] = json_encode($data['api_response']);
        }
        
        $result = $wpdb->insert($table, $data);
        
        if ($result === false) {
            Guardarian_Logger::log(
                'Failed to insert transaction: ' . $wpdb->last_error,
                'error',
                ['data' => $data]
            );
            return false;
        }
        
        return $transaction_id;
    }
    
    /**
     * Update transaction
     */
    public static function update_transaction($transaction_id, $data) {
        global $wpdb;
        
        $table = self::get_transactions_table();
        
        $data['updated_at'] = current_time('mysql');
        
        // Mark as completed if status is success/completed
        if (isset($data['status']) && 
            in_array($data['status'], ['success', 'completed', 'finished'])) {
            $data['completed_at'] = current_time('mysql');
        }
        
        // Serialize API response if it's an array
        if (isset($data['api_response']) && is_array($data['api_response'])) {
            $data['api_response'] = json_encode($data['api_response']);
        }
        
        $result = $wpdb->update(
            $table,
            $data,
            ['transaction_id' => $transaction_id],
            null,
            ['%s']
        );
        
        if ($result === false) {
            Guardarian_Logger::log(
                'Failed to update transaction: ' . $wpdb->last_error,
                'error',
                ['transaction_id' => $transaction_id, 'data' => $data]
            );
        }
        
        return $result !== false;
    }
    
    /**
     * Get transaction by ID
     */
    public static function get_transaction($transaction_id) {
        global $wpdb;
        
        $table = self::get_transactions_table();
        
        $transaction = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE transaction_id = %s",
            $transaction_id
        ), ARRAY_A);
        
        if ($transaction && !empty($transaction['api_response'])) {
            $transaction['api_response'] = json_decode($transaction['api_response'], true);
        }
        
        return $transaction;
    }
    
    /**
     * Get transaction by Guardarian ID
     */
    public static function get_transaction_by_guardarian_id($guardarian_id) {
        global $wpdb;
        
        $table = self::get_transactions_table();
        
        $transaction = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE guardarian_id = %s",
            $guardarian_id
        ), ARRAY_A);
        
        if ($transaction && !empty($transaction['api_response'])) {
            $transaction['api_response'] = json_decode($transaction['api_response'], true);
        }
        
        return $transaction;
    }
    
    /**
     * Get transactions with filters
     */
    public static function get_transactions($args = []) {
        global $wpdb;
        
        $table = self::get_transactions_table();
        
        $defaults = [
            'status' => '',
            'date_from' => '',
            'date_to' => '',
            'search' => '',
            'orderby' => 'created_at',
            'order' => 'DESC',
            'limit' => 20,
            'offset' => 0,
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        $where = ['1=1'];
        $query_args = [];
        
        // Status filter
        if (!empty($args['status'])) {
            $where[] = 'status = %s';
            $query_args[] = $args['status'];
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
        
        // Search filter
        if (!empty($args['search'])) {
            $where[] = '(transaction_id LIKE %s OR guardarian_id LIKE %s OR customer_email LIKE %s)';
            $search_term = '%' . $wpdb->esc_like($args['search']) . '%';
            $query_args[] = $search_term;
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
     * Get transactions count
     */
    public static function get_transactions_count($args = []) {
        global $wpdb;
        
        $table = self::get_transactions_table();
        
        $where = ['1=1'];
        $query_args = [];
        
        if (!empty($args['status'])) {
            $where[] = 'status = %s';
            $query_args[] = $args['status'];
        }
        
        if (!empty($args['date_from'])) {
            $where[] = 'created_at >= %s';
            $query_args[] = $args['date_from'] . ' 00:00:00';
        }
        
        if (!empty($args['date_to'])) {
            $where[] = 'created_at <= %s';
            $query_args[] = $args['date_to'] . ' 23:59:59';
        }
        
        if (!empty($args['search'])) {
            $where[] = '(transaction_id LIKE %s OR guardarian_id LIKE %s OR customer_email LIKE %s)';
            $search_term = '%' . $wpdb->esc_like($args['search']) . '%';
            $query_args[] = $search_term;
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
     * Get dashboard statistics
     */
    public static function get_statistics() {
        global $wpdb;
        
        $table = self::get_transactions_table();
        
        return [
            'total_transactions' => (int) $wpdb->get_var("SELECT COUNT(*) FROM $table"),
            'successful_transactions' => (int) $wpdb->get_var(
                "SELECT COUNT(*) FROM $table WHERE status IN ('success', 'completed', 'finished')"
            ),
            'failed_transactions' => (int) $wpdb->get_var(
                "SELECT COUNT(*) FROM $table WHERE status IN ('failed', 'error', 'cancelled')"
            ),
            'pending_transactions' => (int) $wpdb->get_var(
                "SELECT COUNT(*) FROM $table WHERE status IN ('pending', 'processing')"
            ),
            'total_volume_usd' => (float) $wpdb->get_var(
                "SELECT SUM(amount_from) FROM $table WHERE status IN ('success', 'completed', 'finished')"
            ),
            'total_volume_usdc' => (float) $wpdb->get_var(
                "SELECT SUM(amount_to) FROM $table WHERE status IN ('success', 'completed', 'finished')"
            ),
        ];
    }
    
    /**
     * Generate unique transaction ID
     */
    private static function generate_transaction_id() {
        return 'GRD-' . strtoupper(wp_generate_password(12, false, false));
    }
    
    /**
     * Delete old transactions
     */
    public static function delete_old_transactions($days = 90) {
        global $wpdb;
        
        $table = self::get_transactions_table();
        $date = date('Y-m-d H:i:s', strtotime("-$days days"));
        
        return $wpdb->query($wpdb->prepare(
            "DELETE FROM $table WHERE created_at < %s AND status IN ('failed', 'cancelled', 'expired')",
            $date
        ));
    }
}