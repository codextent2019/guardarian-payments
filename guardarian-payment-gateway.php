<?php
/**
 * Plugin Name: Guardarian Payment Gateway for WordPress
 * Plugin URI: https://github.com/yourusername/guardarian-payment-gateway
 * Description: Standalone cryptocurrency payment gateway integration for Guardarian API. Convert USD to USDC on Ethereum with complete transaction management.
 * Version: 1.0.0
 * Author: SnapyCode
 * Author URI: https://snapycode.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: guardarian-payment
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 8.1
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('GUARDARIAN_VERSION', '1.0.0');
define('GUARDARIAN_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('GUARDARIAN_PLUGIN_URL', plugin_dir_url(__FILE__));
define('GUARDARIAN_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main Plugin Class
 */
class Guardarian_Payment_Gateway {
    
    /**
     * Singleton instance
     */
    private static $instance = null;
    
    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }
    
    /**
     * Load required files
     */
    private function load_dependencies() {
        require_once GUARDARIAN_PLUGIN_DIR . 'includes/class-guardarian-database.php';
        require_once GUARDARIAN_PLUGIN_DIR . 'includes/class-guardarian-api.php';
        require_once GUARDARIAN_PLUGIN_DIR . 'includes/class-guardarian-logger.php';
        require_once GUARDARIAN_PLUGIN_DIR . 'includes/class-guardarian-admin.php';
        require_once GUARDARIAN_PLUGIN_DIR . 'includes/class-guardarian-widget.php';
        require_once GUARDARIAN_PLUGIN_DIR . 'includes/class-guardarian-webhook.php';
        require_once GUARDARIAN_PLUGIN_DIR . 'includes/class-guardarian-email.php';
    }
    
    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);
        
        add_action('plugins_loaded', [$this, 'load_textdomain']);
        add_action('init', [$this, 'init']);
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Create database tables
        Guardarian_Database::create_tables();
        
        // Set default options
        $default_options = [
            'api_environment' => 'production',
            'api_key_production' => '',
            'api_key_sandbox' => '',
            'default_wallet' => '0x8C848036496E6af33A22c4f59A4B442305c3f8BE',
            'currency_from' => 'USD',
            'currency_to' => 'USDC_ETH',
            'min_amount' => 50,
            'max_amount' => 10000,
            'transaction_timeout' => 30,
            'enable_emails' => true,
            'widget_title' => 'Buy Cryptocurrency',
            'button_text' => 'Continue to Payment',
            'success_url' => '',
            'failure_url' => '',
            'cancel_url' => '',
            'webhook_enabled' => true,
            'webhook_secret' => wp_generate_password(32, false),
            'admin_email_enabled' => true,
            'customer_email_enabled' => false,
        ];
        
        foreach ($default_options as $key => $value) {
            add_option('guardarian_' . $key, $value);
        }
        
        // Create webhook endpoint rewrite rule
        flush_rewrite_rules();
        
        // Log activation
        Guardarian_Logger::log('Plugin activated', 'info');
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        flush_rewrite_rules();
        Guardarian_Logger::log('Plugin deactivated', 'info');
    }
    
    /**
     * Load plugin text domain
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'guardarian-payment',
            false,
            dirname(GUARDARIAN_PLUGIN_BASENAME) . '/languages'
        );
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Initialize admin interface
        if (is_admin()) {
            new Guardarian_Admin();
        }
        
        // Initialize frontend widget
        new Guardarian_Widget();
        
        // Initialize webhook handler
        new Guardarian_Webhook();
        
        // Initialize email handler
        new Guardarian_Email();
    }
}

/**
 * Initialize the plugin
 */
function guardarian_payment_gateway() {
    return Guardarian_Payment_Gateway::get_instance();
}

// Start the plugin
guardarian_payment_gateway();