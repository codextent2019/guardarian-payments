<?php
/**
 * PART 1: includes/class-guardarian-api.php
 * Replace the entire file with this content
 */

if (!defined('ABSPATH')) {
    exit;
}

class Guardarian_API {
    
    private $api_key;
    private $environment;
    private $base_url;
    
    const PRODUCTION_URL = 'https://api-payments.guardarian.com/v1';
    const SANDBOX_URL = 'https://api-payments-sandbox.guardarian.com/v1';
    
    public function __construct() {
        $this->environment = get_option('guardarian_api_environment', 'production');
        $this->api_key = $this->get_api_key();
        $this->base_url = $this->environment === 'production' 
            ? self::PRODUCTION_URL 
            : self::SANDBOX_URL;
    }
    
    private function get_api_key() {
        if ($this->environment === 'production') {
            return get_option('guardarian_api_key_production', '');
        }
        return get_option('guardarian_api_key_sandbox', '');
    }
    
    private function request($endpoint, $method = 'GET', $data = null) {
        $url = $this->base_url . $endpoint;
        
        $args = [
            'method' => $method,
            'headers' => [
                'Content-Type' => 'application/json',
                'X-API-Key' => $this->api_key,
                'Accept' => 'application/json',
            ],
            'timeout' => 30,
        ];
        
        if ($method === 'POST' && $data !== null) {
            $args['body'] = json_encode($data);
        }
        
        if ($method === 'GET' && $data !== null) {
            $url = add_query_arg($data, $url);
        }
        
        Guardarian_Logger::log(
            "API Request: $method $endpoint",
            'debug',
            ['url' => $url, 'data' => $data]
        );
        
        $response = wp_remote_request($url, $args);

        $request_log_entry = [
            'timestamp' => current_time('mysql'),
            'endpoint' => $endpoint,
            'method' => $method,
            'data' => $data
        ];

        $response_log_entry = [
            'timestamp' => current_time('mysql'),
            'status_code' => wp_remote_retrieve_response_code($response),
            'body' => json_decode(wp_remote_retrieve_body($response), true)
        ];

        $request_log_file = GUARDARIAN_PLUGIN_DIR . 'request_log.json';
        $existing_request_logs = file_exists($request_log_file) ? json_decode(file_get_contents($request_log_file), true) : [];
        if (!is_array($existing_request_logs)) {
            $existing_request_logs = [];
        }
        $existing_request_logs[] = $request_log_entry;
        file_put_contents($request_log_file, json_encode($existing_request_logs, JSON_PRETTY_PRINT));

        $response_log_file = GUARDARIAN_PLUGIN_DIR . 'response_log.json';
        $existing_response_logs = file_exists($response_log_file) ? json_decode(file_get_contents($response_log_file), true) : [];
        if (!is_array($existing_response_logs)) {
            $existing_response_logs = [];
        }
        $existing_response_logs[] = $response_log_entry;
        file_put_contents($response_log_file, json_encode($existing_response_logs, JSON_PRETTY_PRINT));
        
        if (is_wp_error($response)) {
            Guardarian_Logger::log(
                'API Request Failed: ' . $response->get_error_message(),
                'error',
                ['endpoint' => $endpoint, 'error' => $response->get_error_message()]
            );
            
            return [
                'success' => false,
                'error' => $response->get_error_message(),
            ];
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        Guardarian_Logger::log(
            "API Response: $status_code",
            $status_code >= 400 ? 'error' : 'debug',
            ['status' => $status_code, 'response' => $data]
        );
        
        if ($status_code >= 400) {
            return [
                'success' => false,
                'error' => $data['message'] ?? 'API request failed',
                'status_code' => $status_code,
                'data' => $data,
            ];
        }
        
        return [
            'success' => true,
            'data' => $data,
            'status_code' => $status_code,
        ];
    }
    
    public function test_connection() {
        return $this->request('/currencies', 'GET');
    }
    
    public function get_currencies() {
        return $this->request('/currencies', 'GET');
    }
    
    public function get_estimate($from_currency, $to_currency, $from_amount) {
        $data = [
            'from_currency' => $from_currency,
            'to_currency' => $to_currency,
            'from_amount' => $from_amount,
        ];
        
        return $this->request('/estimate', 'GET', $data);
    }
    
    /**
     * Create transaction with proper API structure
     */
    public function create_transaction($transaction_data, $transaction_id) {
        $default_wallet = get_option('guardarian_default_wallet', '');
        $success_url = get_option('guardarian_success_url', home_url('/payment-success/'));
        $cancel_url = get_option('guardarian_cancel_url', home_url('/payment-cancelled/'));
        $failure_url = get_option('guardarian_failure_url', home_url('/payment-failed/'));
        
        $success_url = str_replace('$$', $transaction_id, $success_url);
        
        $data = [
            'from_amount' => (float) $transaction_data['from_amount'],
            'from_currency' => $transaction_data['from_currency'] ?? 'USD',
            'to_currency' => $transaction_data['to_currency'] ?? 'USDC_ETH',
            'from_network' => $transaction_data['from_network'] ?? null,
            'to_network' => $transaction_data['to_network'] ?? null,
        ];
        
        if (!empty($transaction_data['kyc_shared_token'])) {
            $data['kyc_shared_token'] = $transaction_data['kyc_shared_token'];
            $data['kyc_shared_token_provider'] = $transaction_data['kyc_shared_token_provider'] ?? 'sumsub';
        }
        
        $data['redirects'] = [
            'successful' => $success_url,
            'cancelled' => $cancel_url,
            'failed' => $failure_url,
        ];
        
        $payout_address = !empty($transaction_data['payout_address']) 
            ? $transaction_data['payout_address'] 
            : $default_wallet;
            
        $data['payout_info'] = [
            'payout_address' => $payout_address,
            'skip_choose_payout_address' => false,
        ];
        
        if (!empty($transaction_data['extra_id'])) {
            $data['payout_info']['extra_id'] = $transaction_data['extra_id'];
        }
        
        $data['customer'] = [
            'contact_info' => [],
        ];
        
        if (!empty($transaction_data['customer_email'])) {
            $data['customer']['contact_info']['email'] = $transaction_data['customer_email'];
        }
        
        if (!empty($transaction_data['billing_info']) && is_array($transaction_data['billing_info'])) {
            $billing_info = [];
            
            $billing_fields = [
                'country_alpha_2',
                'us_region_alpha_2',
                'region',
                'city',
                'street_address',
                'apt_number',
                'post_index',
                'first_name',
                'last_name',
                'date_of_birthday',
                'gender',
            ];
            
            foreach ($billing_fields as $field) {
                if (isset($transaction_data['billing_info'][$field]) && 
                    $transaction_data['billing_info'][$field] !== '') {
                    $billing_info[$field] = $transaction_data['billing_info'][$field];
                }
            }
            
            if (!empty($billing_info)) {
                $data['customer']['billing_info'] = $billing_info;
            }
        }
        
        $data['deposit'] = [
            'payment_category' => $transaction_data['payment_category'] ?? 'VISA_MC',
            'skip_choose_payment_category' => $transaction_data['skip_choose_payment_category'] ?? false,
        ];
        
        if (!empty($transaction_data['customer_country'])) {
            $data['customer_country'] = $transaction_data['customer_country'];
        }
        
        $data['external_partner_link_id'] = $transaction_id;
        $data['locale'] = $transaction_data['locale'] ?? 'en';
        
        $data = $this->remove_null_values($data);
        
        Guardarian_Logger::log(
            'Creating transaction',
            'debug',
            ['request_body' => $data]
        );

        return $this->request('/transaction', 'POST', $data);
    }
    
    private function remove_null_values($array) {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = $this->remove_null_values($value);
                if (empty($array[$key])) {
                    unset($array[$key]);
                }
            } elseif ($value === null || $value === '') {
                unset($array[$key]);
            }
        }
        return $array;
    }
    
    public function get_transaction($transaction_id) {
        return $this->request("/transactions/$transaction_id", 'GET');
    }
    
    public function get_payment_methods($currency_from, $currency_to) {
        $data = [
            'from_currency' => $currency_from,
            'to_currency' => $currency_to,
        ];
        
        return $this->request('/payment-methods', 'GET', $data);
    }
    
    public function validate_wallet($currency, $address) {
        $data = [
            'currency' => $currency,
            'address' => $address,
        ];
        
        return $this->request('/validate-address', 'GET', $data);
    }
    
    public function get_limits($from_currency, $to_currency) {
        $data = [
            'from_currency' => $from_currency,
            'to_currency' => $to_currency,
        ];
        
        return $this->request('/limits', 'GET', $data);
    }
}

/**
 * PART 2: Update ajax_create_transaction in includes/class-guardarian-widget.php
 * Replace the ajax_create_transaction method with this:
 */

public function ajax_create_transaction() {
    check_ajax_referer('guardarian-widget', 'nonce');
    
    $amount = floatval($_POST['amount'] ?? 0);
    $email = sanitize_email($_POST['email'] ?? '');
    $name = sanitize_text_field($_POST['name'] ?? '');
    
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
    
    // Create transaction in database first
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
    
    // Properly format API data
    $api_data = [
        'from_amount' => $amount,
        'from_currency' => get_option('guardarian_currency_from', 'USD'),
        'to_currency' => get_option('guardarian_currency_to', 'USDC_ETH'),
        'payout_address' => get_option('guardarian_default_wallet', ''),
        'customer_email' => $email,
    ];
    
    // Add customer name to billing info if provided
    if (!empty($name)) {
        $name_parts = explode(' ', $name, 2);
        $api_data['billing_info'] = [
            'first_name' => $name_parts[0] ?? '',
            'last_name' => $name_parts[1] ?? '',
        ];
    }
    
    $api = new Guardarian_API();
    $result = $api->create_transaction($api_data, $transaction['transaction_id']);
    
    if ($result['success']) {
        $response_data = $result['data'];
        
        // Extract values from the actual API response structure
        $guardarian_id = $response_data['id'] ?? '';
        $status = $response_data['status'] ?? 'pending';
        $redirect_url = $response_data['redirect_url'] ?? '';
        $expected_to_amount = $response_data['expected_to_amount'] ?? 0;
        
        // Get exchange rate from estimate_breakdown if available
        $exchange_rate = 0;
        if (isset($response_data['estimate_breakdown']['estimatedExchangeRate'])) {
            $exchange_rate = $response_data['estimate_breakdown']['estimatedExchangeRate'];
        }
        
        // Check for errors in response (API might return 200 but with errors)
        $has_errors = !empty($response_data['errors']);
        if ($has_errors) {
            $error_messages = [];
            foreach ($response_data['errors'] as $error) {
                $error_messages[] = $error['reason'] ?? $error['code'] ?? 'Unknown error';
            }
            $error_message = implode('; ', $error_messages);
            
            Guardarian_Logger::log(
                'Transaction created but has errors: ' . $error_message,
                'warning',
                [
                    'transaction_id' => $transaction['transaction_id'],
                    'guardarian_id' => $guardarian_id,
                    'errors' => $response_data['errors']
                ]
            );
        }
        
        // Update transaction with API response
        Guardarian_Database::update_transaction($transaction['transaction_id'], [
            'guardarian_id' => $guardarian_id,
            'payment_url' => $redirect_url,
            'amount_to' => $expected_to_amount,
            'exchange_rate' => $exchange_rate,
            'status' => $status,
            'error_message' => $has_errors ? $error_message : null,
            'api_response' => $response_data,
        ]);
        
        Guardarian_Logger::log(
            'Transaction created successfully',
            'info',
            [
                'transaction_id' => $transaction['transaction_id'],
                'guardarian_id' => $guardarian_id,
                'amount' => $amount,
                'status' => $status,
                'has_errors' => $has_errors
            ]
        );
        
        // Send notification emails if enabled
        if (get_option('guardarian_admin_email_enabled', true)) {
            Guardarian_Email::send_admin_notification($transaction['transaction_id']);
        }
        
        // If redirect_url is available, send success
        if (!empty($redirect_url)) {
            wp_send_json_success([
                'transaction_id' => $transaction['transaction_id'],
                'payment_url' => $redirect_url,
                'guardarian_id' => $guardarian_id,
                'status' => $status,
                'expected_amount' => $expected_to_amount,
                'has_errors' => $has_errors,
                'error_message' => $has_errors ? $error_message : null,
                'message' => __('Transaction created successfully. Redirecting to payment...', 'guardarian-payment')
            ]);
        } else {
            // No redirect URL - something went wrong
            wp_send_json_error([
                'message' => $has_errors 
                    ? $error_message 
                    : __('Transaction created but no payment URL received.', 'guardarian-payment')
            ]);
        }
    } else {
        // API request failed
        Guardarian_Database::update_transaction($transaction['transaction_id'], [
            'status' => 'failed',
            'error_message' => $result['error'] ?? 'Unknown error',
            'api_response' => $result['data'] ?? [],
        ]);
        
        Guardarian_Logger::log(
            'Failed to create transaction via API: ' . ($result['error'] ?? 'Unknown error'),
            'error',
            [
                'transaction_id' => $transaction['transaction_id'],
                'error' => $result['error'] ?? 'Unknown error',
                'amount' => $amount,
                'api_response' => $result['data'] ?? []
            ]
        );
        
        wp_send_json_error([
            'message' => $result['error'] ?? __('Failed to create transaction. Please try again.', 'guardarian-payment')
        ]);
    }
}

/**
 * PART 3: Update webhook handler in includes/class-guardarian-webhook.php
 * Update the process_webhook method to handle the proper response structure:
 */

private function process_webhook($data) {
    // Extract transaction information from actual API response structure
    $guardarian_id = $data['id'] ?? '';
    $status = $data['status'] ?? '';
    $external_id = $data['external_partner_link_id'] ?? '';
    
    if (empty($guardarian_id)) {
        Guardarian_Logger::log(
            'Webhook missing transaction ID',
            'error',
            ['webhook' => true, 'data' => $data]
        );
        return;
    }
    
    // Try to find transaction by external_partner_link_id first (our transaction ID)
    $transaction = null;
    if (!empty($external_id)) {
        $transaction = Guardarian_Database::get_transaction($external_id);
    }
    
    // If not found, try by Guardarian ID
    if (!$transaction) {
        $transaction = Guardarian_Database::get_transaction_by_guardarian_id($guardarian_id);
    }
    
    if (!$transaction) {
        Guardarian_Logger::log(
            'Webhook for unknown transaction',
            'warning',
            ['webhook' => true, 'guardarian_id' => $guardarian_id, 'external_id' => $external_id]
        );
        return;
    }
    
    // Extract values from webhook data
    $to_amount = $data['to_amount'] ?? $data['expected_to_amount'] ?? null;
    $exchange_rate = null;
    
    if (isset($data['estimate_breakdown']['estimatedExchangeRate'])) {
        $exchange_rate = $data['estimate_breakdown']['estimatedExchangeRate'];
    }
    
    // Build update data
    $update_data = [
        'status' => $status,
        'api_response' => $data,
    ];
    
    if ($to_amount !== null) {
        $update_data['amount_to'] = $to_amount;
    }
    
    if ($exchange_rate !== null) {
        $update_data['exchange_rate'] = $exchange_rate;
    }
    
    // Add output hash if available
    if (!empty($data['output_hash'])) {
        $update_data['error_message'] = 'TX Hash: ' . $data['output_hash'];
    }
    
    // Check for errors in webhook data
    if (!empty($data['errors'])) {
        $error_messages = [];
        foreach ($data['errors'] as $error) {
            $error_messages[] = $error['reason'] ?? $error['code'] ?? 'Unknown error';
        }
        $update_data['error_message'] = implode('; ', $error_messages);
    }
    
    // Mark as completed if status indicates success
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
            'status' => $status,
            'has_errors' => !empty($data['errors'])
        ]
    );
    
    // Send email notifications based on status
    $this->send_status_notifications($transaction['transaction_id'], $status);
}
