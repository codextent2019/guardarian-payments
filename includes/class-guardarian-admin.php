<?php
/**
 * Guardarian Admin Interface
 * 
 * Handles all admin dashboard pages and functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

class Guardarian_Admin {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', [$this, 'add_menu_pages']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('wp_ajax_guardarian_test_api', [$this, 'ajax_test_api']);
        add_action('wp_ajax_guardarian_get_estimate', [$this, 'ajax_get_estimate']);
        add_action('wp_ajax_guardarian_refresh_transaction', [$this, 'ajax_refresh_transaction']);
        add_action('wp_ajax_guardarian_export_transactions', [$this, 'ajax_export_transactions']);
        add_action('wp_ajax_guardarian_export_logs', [$this, 'ajax_export_logs']);
    }
    
    /**
     * Add admin menu pages
     */
    public function add_menu_pages() {
        // Main menu
        add_menu_page(
            __('Guardarian Payments', 'guardarian-payment'),
            __('Guardarian Payments', 'guardarian-payment'),
            'manage_options',
            'guardarian-payment',
            [$this, 'render_dashboard_page'],
            'dashicons-money-alt',
            58
        );
        
        // Dashboard submenu
        add_submenu_page(
            'guardarian-payment',
            __('Dashboard', 'guardarian-payment'),
            __('Dashboard', 'guardarian-payment'),
            'manage_options',
            'guardarian-payment',
            [$this, 'render_dashboard_page']
        );
        
        // Settings submenu
        add_submenu_page(
            'guardarian-payment',
            __('Settings', 'guardarian-payment'),
            __('Settings', 'guardarian-payment'),
            'manage_options',
            'guardarian-settings',
            [$this, 'render_settings_page']
        );
        
        // Transactions submenu
        add_submenu_page(
            'guardarian-payment',
            __('Transactions', 'guardarian-payment'),
            __('Transactions', 'guardarian-payment'),
            'manage_options',
            'guardarian-transactions',
            [$this, 'render_transactions_page']
        );
        
        // Logs submenu
        add_submenu_page(
            'guardarian-payment',
            __('Logs', 'guardarian-payment'),
            __('Logs', 'guardarian-payment'),
            'manage_options',
            'guardarian-logs',
            [$this, 'render_logs_page']
        );
    }
    
    /**
     * Register plugin settings
     */
    public function register_settings() {
        // API Settings
        register_setting('guardarian_api_settings', 'guardarian_api_environment');
        register_setting('guardarian_api_settings', 'guardarian_api_key_production');
        register_setting('guardarian_api_settings', 'guardarian_api_key_sandbox');
        
        // Payment Settings
        register_setting('guardarian_payment_settings', 'guardarian_default_wallet');
        register_setting('guardarian_payment_settings', 'guardarian_currency_from');
        register_setting('guardarian_payment_settings', 'guardarian_currency_to');
        register_setting('guardarian_payment_settings', 'guardarian_min_amount');
        register_setting('guardarian_payment_settings', 'guardarian_max_amount');
        register_setting('guardarian_payment_settings', 'guardarian_transaction_timeout');
        register_setting('guardarian_payment_settings', 'guardarian_enable_emails');
        
        // Display Settings
        register_setting('guardarian_display_settings', 'guardarian_widget_title');
        register_setting('guardarian_display_settings', 'guardarian_button_text');
        register_setting('guardarian_display_settings', 'guardarian_widget_theme');
        register_setting('guardarian_display_settings', 'guardarian_custom_css');
        
        // Redirect Settings
        register_setting('guardarian_redirect_settings', 'guardarian_success_url');
        register_setting('guardarian_redirect_settings', 'guardarian_failure_url');
        register_setting('guardarian_redirect_settings', 'guardarian_cancel_url');
        
        // Webhook Settings
        register_setting('guardarian_webhook_settings', 'guardarian_webhook_enabled');
        register_setting('guardarian_webhook_settings', 'guardarian_webhook_secret');
        
        // Notification Settings
        register_setting('guardarian_notification_settings', 'guardarian_admin_email_enabled');
        register_setting('guardarian_notification_settings', 'guardarian_customer_email_enabled');
        register_setting('guardarian_notification_settings', 'guardarian_admin_email');
        
        // Email Settings
        register_setting('guardarian_email_settings', 'guardarian_admin_email_subject');
        register_setting('guardarian_email_settings', 'guardarian_admin_email_body');
        register_setting('guardarian_email_settings', 'guardarian_customer_email_subject');
        register_setting('guardarian_email_settings', 'guardarian_customer_email_body');

        // Log Settings
        register_setting('guardarian_log_settings', 'guardarian_debug_logging');
        register_setting('guardarian_log_settings', 'guardarian_log_retention_days');
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'guardarian') === false) {
            return;
        }
        
        wp_enqueue_style(
            'guardarian-admin-css',
            GUARDARIAN_PLUGIN_URL . 'assets/css/admin.css',
            [],
            GUARDARIAN_VERSION
        );
        
        wp_enqueue_script(
            'guardarian-admin-js',
            GUARDARIAN_PLUGIN_URL . 'assets/js/admin.js',
            ['jquery'],
            GUARDARIAN_VERSION,
            true
        );
        
        wp_localize_script('guardarian-admin-js', 'guardarianAdmin', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('guardarian-admin'),
            'i18n' => [
                'confirm_delete' => __('Are you sure you want to delete this?', 'guardarian-payment'),
                'confirm_clear_logs' => __('Are you sure you want to clear all logs?', 'guardarian-payment'),
                'testing_connection' => __('Testing API connection...', 'guardarian-payment'),
                'connection_success' => __('API connection successful!', 'guardarian-payment'),
                'connection_failed' => __('API connection failed. Please check your credentials.', 'guardarian-payment'),
            ],
        ]);
    }
    
    /**
     * Render dashboard page
     */
    public function render_dashboard_page() {
        $stats = Guardarian_Database::get_statistics();
        $recent_transactions = Guardarian_Database::get_transactions(['limit' => 5]);
        
        include GUARDARIAN_PLUGIN_DIR . 'templates/admin/dashboard.php';
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'api';
        
        if (isset($_POST['guardarian_save_settings'])) {
            check_admin_referer('guardarian_settings_nonce');
            $this->save_settings($active_tab);
        }
        
        include GUARDARIAN_PLUGIN_DIR . 'templates/admin/settings.php';
    }
    
    /**
     * Save settings
     */
    private function save_settings($tab) {
        $settings_map = [
            'api' => [
                'guardarian_api_environment' => 'sanitize_text_field',
                'guardarian_api_key_production' => 'sanitize_text_field',
                'guardarian_api_key_sandbox' => 'sanitize_text_field',
            ],
            'payment' => [
                'guardarian_default_wallet' => 'sanitize_text_field',
                'guardarian_currency_from' => 'sanitize_text_field',
                'guardarian_currency_to' => 'sanitize_text_field',
                'guardarian_min_amount' => 'absint',
                'guardarian_max_amount' => 'absint',
                'guardarian_transaction_timeout' => 'absint',
                'guardarian_enable_emails' => 'absint',
            ],
            'display' => [
                'guardarian_widget_title' => 'sanitize_text_field',
                'guardarian_button_text' => 'sanitize_text_field',
                'guardarian_widget_theme' => 'sanitize_text_field',
                'guardarian_custom_css' => 'wp_strip_all_tags',
            ],
            'redirect' => [
                'guardarian_success_url' => 'esc_url_raw',
                'guardarian_failure_url' => 'esc_url_raw',
                'guardarian_cancel_url' => 'esc_url_raw',
            ],
            'webhook' => [
                'guardarian_webhook_enabled' => 'absint',
                'guardarian_webhook_secret' => 'sanitize_text_field',
            ],
            'notification' => [
                'guardarian_admin_email_enabled' => 'absint',
                'guardarian_customer_email_enabled' => 'absint',
                'guardarian_admin_email' => 'sanitize_email',
            ],
            'emails' => [
                'guardarian_admin_email_subject' => 'sanitize_text_field',
                'guardarian_admin_email_body' => 'wp_kses_post',
                'guardarian_customer_email_subject' => 'sanitize_text_field',
                'guardarian_customer_email_body' => 'wp_kses_post',
            ],
        ];

        if (!isset($settings_map[$tab])) {
            return;
        }

        foreach ($settings_map[$tab] as $option => $sanitize_callback) {
            if (isset($_POST[$option])) {
                update_option($option, call_user_func($sanitize_callback, $_POST[$option]));
            }
        }

        add_settings_error(
            'guardarian_messages',
            'guardarian_message',
            __('Settings saved successfully.', 'guardarian-payment'),
            'updated'
        );
    }
    
    /**
     * Render transactions page
     */
    public function render_transactions_page() {
        $paged = $_GET['paged'] ?? 1;
        $per_page = 20;
        $offset = ($paged - 1) * $per_page;
        
        $filters = [
            'status' => isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '',
            'date_from' => isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : '',
            'date_to' => isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : '',
            'search' => isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '',
            'limit' => $per_page,
            'offset' => $offset,
        ];
        
        $transactions = Guardarian_Database::get_transactions($filters);
        $total = Guardarian_Database::get_transactions_count($filters);
        $total_pages = ceil($total / $per_page);
        
        include GUARDARIAN_PLUGIN_DIR . 'templates/admin/transactions.php';
    }
    
    /**
     * Render logs page
     */
    public function render_logs_page() {
        $paged = $_GET['paged'] ?? 1;
        $per_page = 50;
        $offset = ($paged - 1) * $per_page;
        
        $filters = [
            'log_type' => isset($_GET['log_type']) ? sanitize_text_field($_GET['log_type']) : '',
            'severity' => isset($_GET['severity']) ? sanitize_text_field($_GET['severity']) : '',
            'date_from' => isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : '',
            'date_to' => isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : '',
            'search' => isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '',
            'limit' => $per_page,
            'offset' => $offset,
        ];
        
        $logs = Guardarian_Logger::get_logs($filters);
        $total = Guardarian_Logger::get_logs_count($filters);
        $total_pages = ceil($total / $per_page);
        
        include GUARDARIAN_PLUGIN_DIR . 'templates/admin/logs.php';
    }
    
    /**
     * AJAX: Test API connection
     */
    public function ajax_test_api() {
        check_ajax_referer('guardarian-admin', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }
        
        $api = new Guardarian_API();
        $result = $api->test_connection();
        
        if ($result['success']) {
            wp_send_json_success(['message' => 'API connection successful!']);
        } else {
            wp_send_json_error(['message' => $result['error'] ?? 'Connection failed']);
        }
    }

    /**
     * AJAX: Get exchange rate estimate
     */
    public function ajax_get_estimate() {
        check_ajax_referer('guardarian-widget', 'nonce');
        
        $amount = floatval($_POST['amount'] ?? 0);
        
        if ($amount <= 0) {
            wp_send_json_error([
                'message' => __('Please enter a valid amount', 'guardarian-payment')
            ]);
        }
        
        $min_amount = floatval(get_option('guardarian_min_amount', 50));
        $max_amount = floatval(get_option('guardarian_max_amount', 10000));
        
        if ($amount < $min_amount) {
            wp_send_json_error([
                'message' => sprintf(__('Minimum amount is %s USD', 'guardarian-payment'), $min_amount)
            ]);
        }
        
        if ($max_amount > 0 && $amount > $max_amount) {
            wp_send_json_error([
                'message' => sprintf(__('Maximum amount is %s USD', 'guardarian-payment'), $max_amount)
            ]);
        }
        
        $api = new Guardarian_API();
        $result = $api->get_estimate(
            get_option('guardarian_currency_from', 'USD'),
            get_option('guardarian_currency_to', 'USDC_ETH'),
            $amount
        );
        
        if ($result['success'] && isset($result['data']['value'])) {
            wp_send_json_success([
                'estimated_amount' => $result['data']['value'] ?? 0,
                'exchange_rate' => $result['data']['estimated_exchange_rate'] ?? 0,
                'fee' => $result['data']['service_fees'][0]['amount'] ?? 0,
            ]);
        } else {
            $error_message = $result['error'] ?? __('Unable to get exchange rate. Please try again.', 'guardarian-payment');
            if (isset($result['data']['message'])) {
                $error_message = $result['data']['message'];
            }
            Guardarian_Logger::log(
                'Failed to get estimate: ' . $error_message,
                'error',
                ['amount' => $amount, 'error' => $error_message]
            );
            
            wp_send_json_error([
                'message' => $error_message
            ]);
        }
    }
    
    
    /**
     * AJAX: Refresh transaction status
     */
    public function ajax_refresh_transaction() {
        check_ajax_referer('guardarian-admin', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }
        
        $transaction_id = sanitize_text_field($_POST['transaction_id'] ?? '');
        $transaction = Guardarian_Database::get_transaction($transaction_id);
        
        if (!$transaction) {
            wp_send_json_error(['message' => 'Transaction not found']);
        }
        
        if (empty($transaction['guardarian_id'])) {
            wp_send_json_error(['message' => 'No Guardarian ID associated']);
        }
        
        $api = new Guardarian_API();
        $result = $api->get_transaction($transaction['guardarian_id']);
        
        if ($result['success']) {
            Guardarian_Database::update_transaction($transaction_id, [
                'status' => $result['data']['status'] ?? 'unknown',
                'api_response' => $result['data'],
            ]);
            
            wp_send_json_success(['message' => 'Transaction updated']);
        } else {
            wp_send_json_error(['message' => $result['error']]);
        }
    }
    
    /**
     * AJAX: Export transactions
     */
    public function ajax_export_transactions() {
        check_ajax_referer('guardarian-admin', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }
        
        $filters = [
            'status' => isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '',
            'date_from' => isset($_POST['date_from']) ? sanitize_text_field($_POST['date_from']) : '',
            'date_to' => isset($_POST['date_to']) ? sanitize_text_field($_POST['date_to']) : '',
            'search' => isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '',
            'limit' => 10000,
            'offset' => 0,
        ];
        
        $transactions = Guardarian_Database::get_transactions($filters);
        
        $filename = 'guardarian-transactions-' . date('Y-m-d-His') . '.csv';
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        fputcsv($output, [
            'Transaction ID',
            'Guardarian ID',
            'Status',
            'Amount From',
            'Currency From',
            'Amount To',
            'Currency To',
            'Exchange Rate',
            'Wallet Address',
            'Created At',
            'Completed At',
        ]);
        
        foreach ($transactions as $txn) {
            fputcsv($output, [
                $txn['transaction_id'],
                $txn['guardarian_id'] ?? '',
                $txn['status'],
                $txn['amount_from'],
                $txn['currency_from'],
                $txn['amount_to'] ?? '',
                $txn['currency_to'],
                $txn['exchange_rate'] ?? '',
                $txn['wallet_address'],
                $txn['created_at'],
                $txn['completed_at'] ?? '',
            ]);
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * AJAX: Export logs
     */
    public function ajax_export_logs() {
        check_ajax_referer('guardarian-admin', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }
        
        $filepath = Guardarian_Logger::export_logs($_POST);
        
        if ($filepath && file_exists($filepath)) {
            wp_send_json_success(['file' => basename($filepath)]);
        } else {
            wp_send_json_error(['message' => 'Export failed']);
        }
    }
}