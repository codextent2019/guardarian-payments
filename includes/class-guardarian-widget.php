<?php
/**
 * Guardarian Frontend Widget
 * 
 * Handles frontend payment widget display and processing
 */

if (!defined('ABSPATH')) {
    exit;
}

class Guardarian_Widget {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_shortcode('guardarian_payment_widget', [$this, 'render_widget']);
        
        add_action('wp_ajax_guardarian_create_transaction', [$this, 'ajax_create_transaction']);
        add_action('wp_ajax_nopriv_guardarian_create_transaction', [$this, 'ajax_create_transaction']);
        add_action('wp_ajax_guardarian_get_estimate', [$this, 'ajax_get_estimate']);
        add_action('wp_ajax_nopriv_guardarian_get_estimate', [$this, 'ajax_get_estimate']);
    }
    
    /**
     * Enqueue frontend assets
     */
    private function enqueue_frontend_assets() {

        wp_enqueue_style(
            'guardarian-widget-css',
            GUARDARIAN_PLUGIN_URL . 'assets/css/widget.css',
            [],
            GUARDARIAN_VERSION
        );
        
        wp_enqueue_script(
            'guardarian-widget-js',
            GUARDARIAN_PLUGIN_URL . 'assets/js/widget.js',
            ['jquery'],
            GUARDARIAN_VERSION,
            true
        );
        
        wp_localize_script('guardarian-widget-js', 'guardarianWidget', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('guardarian-widget'),
            'currency_from' => get_option('guardarian_currency_from', 'USD'),
            'currency_to' => get_option('guardarian_currency_to', 'USDC_ETH'),
            'min_amount' => floatval(get_option('guardarian_min_amount', 50)),
            'max_amount' => floatval(get_option('guardarian_max_amount', 10000)),
            'wallet_address' => get_option('guardarian_default_wallet', ''),
            'i18n' => [
                'enter_amount' => __('Enter amount', 'guardarian-payment'),
                'min_amount_error' => sprintf(
                    __('Minimum amount is %s', 'guardarian-payment'),
                    get_option('guardarian_min_amount', 50)
                ),
                'max_amount_error' => sprintf(
                    __('Maximum amount is %s', 'guardarian-payment'),
                    get_option('guardarian_max_amount', 10000)
                ),
                'processing' => __('Processing...', 'guardarian-payment'),
                'error_occurred' => __('An error occurred. Please try again.', 'guardarian-payment'),
            ],
        ]);
    }
    
    /**
     * Render payment widget
     */
    public function render_widget($atts) {
        $this->enqueue_frontend_assets();

        $atts = shortcode_atts([
            'title' => get_option('guardarian_widget_title', 'Buy Cryptocurrency'),
            'button_text' => get_option('guardarian_button_text', 'Continue to Payment'),
            'default_amount' => '',
            'show_exchange_rate' => 'true',
            'theme' => get_option('guardarian_widget_theme', 'light'),
            'width' => '100%',
        ], $atts);
        
        ob_start();
        include GUARDARIAN_PLUGIN_DIR . 'templates/widget/payment-form.php';
        return ob_get_clean();
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
        
        if ($result['success']) {
            wp_send_json_success([
                'estimated_amount' => $result['data']['to_amount'] ?? 0,
                'exchange_rate' => $result['data']['rate'] ?? 0,
                'fee' => $result['data']['fee'] ?? 0,
            ]);
        } else {
            Guardarian_Logger::log(
                'Failed to get estimate: ' . $result['error'],
                'error',
                ['amount' => $amount, 'error' => $result['error']]
            );
            
            wp_send_json_error([
                'message' => __('Unable to get exchange rate. Please try again.', 'guardarian-payment')
            ]);
        }
    }
    
    /**
     * AJAX: Create transaction
     */
    public function ajax_create_transaction() {
        check_ajax_referer('guardarian-widget', 'nonce');
        
        $amount = floatval($_POST['amount'] ?? 0);
        $email = sanitize_email($_POST['email'] ?? '');
        $name = sanitize_text_field($_POST['name'] ?? '');
        
        // Validate amount
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
        
        // Create transaction in database
        $transaction_data = [
            'amount_from' => $amount,
            'currency_from' => get_option('guardarian_currency_from', 'USD'),
            'currency_to' => get_option('guardarian_currency_to', 'USDC_ETH'),
            'wallet_address' => get_option('guardarian_default_wallet', ''),
            'customer_email' => $email,
            'customer_name' => $name,
            'status' => 'pending',
        ];
        
        $transaction_id = Guardarian_Database::insert_transaction($transaction_data);
        
        if (!$transaction_id) {
            Guardarian_Logger::log(
                'Failed to create transaction in database',
                'error',
                ['data' => $transaction_data]
            );
            
            wp_send_json_error([
                'message' => __('Failed to create transaction. Please try again.', 'guardarian-payment')
            ]);
        }
        
        $transaction = Guardarian_Database::get_transaction($transaction_id);
        
        // Create transaction via Guardarian API
        $api = new Guardarian_API();
        $api_data = [
            'from_amount' => $amount,
            'from_currency' => get_option('guardarian_currency_from', 'USD'),
            'to_currency' => get_option('guardarian_currency_to', 'USDC_ETH'),
            'payout_address' => get_option('guardarian_default_wallet', ''),
            'customer_email' => $email,
        ];
        
        $result = $api->create_transaction($api_data, $transaction['transaction_id']);
        
        if ($result['success']) {
            // Update transaction with Guardarian response
            Guardarian_Database::update_transaction($transaction['transaction_id'], [
                'guardarian_id' => $result['data']['id'] ?? '',
                'payment_url' => $result['data']['payment_url'] ?? '',
                'amount_to' => $result['data']['to_amount'] ?? 0,
                'exchange_rate' => $result['data']['estimatedExchangeRate'] ?? 0,
                'status' => $result['data']['status'] ?? 'pending',
                'api_response' => $result['data'],
            ]);
            
            Guardarian_Logger::log(
                'Transaction created successfully',
                'info',
                [
                    'transaction_id' => $transaction['transaction_id'],
                    'guardarian_id' => $result['data']['id'] ?? '',
                    'amount' => $amount
                ]
            );
            
            // Send notification emails if enabled
            if (get_option('guardarian_admin_email_enabled', true)) {
                Guardarian_Email::send_admin_notification($transaction['transaction_id']);
            }
            
            wp_send_json_success([
                'transaction_id' => $transaction['transaction_id'],
                'payment_url' => $result['data']['payment_url'] ?? '',
                'guardarian_id' => $result['data']['id'] ?? '',
                'message' => __('Transaction created successfully. Redirecting to payment...', 'guardarian-payment')
            ]);
        } else {
            // Update transaction with error
            Guardarian_Database::update_transaction($transaction['transaction_id'], [
                'status' => 'failed',
                'error_message' => $result['error'] ?? 'Unknown error',
                'api_response' => $result['data'] ?? [],
            ]);
            
            Guardarian_Logger::log(
                'Failed to create transaction via API: ' . $result['error'],
                'error',
                [
                    'transaction_id' => $transaction['transaction_id'],
                    'error' => $result['error'],
                    'amount' => $amount
                ]
            );
            
            wp_send_json_error([
                'message' => $result['error'] ?? __('Failed to create transaction. Please try again.', 'guardarian-payment')
            ]);
        }
    }
}