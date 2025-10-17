<?php
/**
 * Template: API Settings Tab
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="settings-section">
    <h2><?php _e('API Configuration', 'guardarian-payment'); ?></h2>
    
    <div class="guardarian-info-box">
        <p><strong><?php _e('Important:', 'guardarian-payment'); ?></strong> 
        <?php _e('You need a Guardarian API key to use this plugin. Visit the Guardarian Partner Portal to obtain your API credentials.', 'guardarian-payment'); ?></p>
        <p>
            <a href="https://guardarian.com/partner" target="_blank" class="button button-secondary">
                <?php _e('Get API Key', 'guardarian-payment'); ?>
            </a>
            <a href="https://api-payments.guardarian.com/v1/api-docs" target="_blank" class="button button-secondary">
                <?php _e('API Documentation', 'guardarian-payment'); ?>
            </a>
        </p>
    </div>
    
    <div class="setting-row">
        <label class="setting-label">
            <?php _e('API Environment', 'guardarian-payment'); ?>
        </label>
        <select name="guardarian_api_environment" class="setting-input-short">
            <option value="production" <?php selected(get_option('guardarian_api_environment'), 'production'); ?>>
                <?php _e('Production', 'guardarian-payment'); ?>
            </option>
            <option value="sandbox" <?php selected(get_option('guardarian_api_environment'), 'sandbox'); ?>>
                <?php _e('Sandbox (Testing)', 'guardarian-payment'); ?>
            </option>
        </select>
        <span class="setting-description">
            <?php _e('Select production for live transactions or sandbox for testing.', 'guardarian-payment'); ?>
        </span>
    </div>
    
    <div class="setting-row">
        <label class="setting-label" for="guardarian_api_key_production">
            <?php _e('Production API Key', 'guardarian-payment'); ?>
        </label>
        <input type="password" 
               id="guardarian_api_key_production"
               name="guardarian_api_key_production" 
               class="setting-input" 
               value="<?php echo esc_attr(get_option('guardarian_api_key_production', '')); ?>"
               placeholder="f3eaa23e-84e9-4b76-b981-e6c7bb02fe59">
        <span class="setting-description">
            <?php _e('Your production API key from Guardarian Partner Portal.', 'guardarian-payment'); ?>
        </span>
    </div>
    
    <div class="setting-row">
        <label class="setting-label" for="guardarian_api_key_sandbox">
            <?php _e('Sandbox API Key', 'guardarian-payment'); ?> 
            <em>(<?php _e('Optional', 'guardarian-payment'); ?>)</em>
        </label>
        <input type="password" 
               id="guardarian_api_key_sandbox"
               name="guardarian_api_key_sandbox" 
               class="setting-input" 
               value="<?php echo esc_attr(get_option('guardarian_api_key_sandbox', '')); ?>"
               placeholder="sandbox-key-here">
        <span class="setting-description">
            <?php _e('Your sandbox API key for testing purposes.', 'guardarian-payment'); ?>
        </span>
    </div>
    
    <div class="setting-row">
        <button type="button" id="test-api-connection" class="button button-secondary">
            <span class="dashicons dashicons-cloud"></span>
            <?php _e('Test API Connection', 'guardarian-payment'); ?>
        </button>
        <div id="api-test-result" style="margin-top: 10px;"></div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('#test-api-connection').on('click', function() {
        var $btn = $(this);
        var $result = $('#api-test-result');
        
        $btn.prop('disabled', true);
        $result.html('<span class="spinner is-active"></span> Testing connection...');
        
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'guardarian_test_api',
                nonce: guardarianAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    $result.html('<div class="notice notice-success inline"><p>✓ ' + response.data.message + '</p></div>');
                } else {
                    $result.html('<div class="notice notice-error inline"><p>✗ ' + response.data.message + '</p></div>');
                }
            },
            error: function() {
                $result.html('<div class="notice notice-error inline"><p>✗ Connection failed</p></div>');
            },
            complete: function() {
                $btn.prop('disabled', false);
            }
        });
    });
});
</script>
