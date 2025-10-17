<?php
/**
 * Guardarian API Handler
 * 
 * Handles all communication with Guardarian API endpoints
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
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->environment = get_option('guardarian_api_environment', 'production');
        $this->api_key = $this->get_api_key();
        $this->base_url = $this->environment === 'production' 
            ? self::PRODUCTION_URL 
            : self::SANDBOX_URL;
    }
    
    /**
     * Get API key based on environment
     */
    private function get_api_key() {
        if ($this->environment === 'production') {
            return get_option('guardarian_api_key_production', '');
        }
        return get_option('guardarian_api_key_sandbox', '');
    }
    
    /**
     * Make API request
     */
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
    
    /**
     * Test API connection
     */
    public function test_connection() {
        return $this->request('/currencies', 'GET');
    }
    
    /**
     * Get available currencies
     */
    public function get_currencies() {
        return $this->request('/currencies', 'GET');
    }
    
    /**
     * Get exchange rate estimate
     */
    public function get_estimate($from_currency, $to_currency, $from_amount) {
        $data = [
            'from_currency' => $from_currency,
            'to_currency' => $to_currency,
            'from_amount' => $from_amount,
        ];
        
        return $this->request('/estimate', 'GET', $data);
    }
    
    /**
     * Create transaction
     */
    public function create_transaction($transaction_data, $transaction_id) {
        $data = [
            'from_amount' => (float) $transaction_data['from_amount'],
            'from_currency' => $transaction_data['from_currency'],
            'to_currency' => $transaction_data['to_currency'],
            'from_network' => $transaction_data['from_network'] ?? '',
            'to_network' => $transaction_data['to_network'] ?? '',
            'kyc_shared_token' => $transaction_data['kyc_shared_token'] ?? '',
            'kyc_shared_token_provider' => $transaction_data['kyc_shared_token'] ? 'sumsub' : '',
            'redirects' => [
                'successful' => get_option('guardarian_success_url', 'https://guardarian.com/finished/$$'),
                'cancelled' => get_option('guardarian_cancelled_url', 'https://guardarian.com/cancelled'),
                'failed' => get_option('guardarian_failure_url', 'https://guardarian.com/failed'),
            ],
            'payout_info' => [
                'payout_address' => $transaction_data['payout_address'],
                'extra_id' => $transaction_data['extra_id'] ?? '',
                'skip_choose_payout_address' => false,
            ],
            'customer' => [
                'contact_info' => [
                    'email' => $transaction_data['customer_email']
                ],
                'billing_info' => [
                    'country_alpha_2' => $transaction_data['billing_info']['country_alpha_2'] ?? '',
                    'us_region_alpha_2' => $transaction_data['billing_info']['us_region_alpha_2'] ?? '',
                    'region' => $transaction_data['billing_info']['region'] ?? '',
                    'city' => $transaction_data['billing_info']['city'] ?? '',
                    'street_address' => $transaction_data['billing_info']['street_address'] ?? '',
                    'apt_number' => $transaction_data['billing_info']['apt_number'] ?? '',
                    'post_index' => $transaction_data['billing_info']['post_index'] ?? '',
                    'first_name' => $transaction_data['billing_info']['first_name'] ?? '',
                    'last_name' => $transaction_data['billing_info']['last_name'] ?? '',
                    'date_of_birthday' => $transaction_data['billing_info']['date_of_birthday'] ?? '',
                    'gender' => $transaction_data['billing_info']['gender'] ?? '',
                ]
            ],
            'deposit' => [
                'payment_category' => 'VISA_MC',
                'skip_choose_payment_category' => false,
            ],
            'customer_country' => $transaction_data['customer_country'] ?? '',
            'external_partner_link_id' => $transaction_id,
            'locale' => $transaction_data['locale'] ?? 'en',
        ];

        Guardarian_Logger::log('Request body for create transaction', 'debug', $data);

        return $this->request('/transaction', 'POST', $data);
    }    
    /**
     * Get transaction status
     */
    public function get_transaction($transaction_id) {
        return $this->request("/transactions/$transaction_id", 'GET');
    }
    
    /**
     * Get payment methods
     */
    public function get_payment_methods($currency_from, $currency_to) {
        $data = [
            'from_currency' => $currency_from,
            'to_currency' => $currency_to,
        ];
        
        return $this->request('/payment-methods', 'GET', $data);
    }
    
    /**
     * Validate wallet address
     */
    public function validate_wallet($currency, $address) {
        $data = [
            'currency' => $currency,
            'address' => $address,
        ];
        
        return $this->request('/validate-address', 'GET', $data);
    }
    
    /**
     * Get transaction limits
     */
    public function get_limits($from_currency, $to_currency) {
        $data = [
            'from_currency' => $from_currency,
            'to_currency' => $to_currency,
        ];
        
        return $this->request('/limits', 'GET', $data);
    }
}