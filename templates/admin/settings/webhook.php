<?php
/**
 * Template: Settings - Webhook
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="settings-section">
    <h2><?php _e('Webhook Settings', 'guardarian-payment'); ?></h2>

    <div class="setting-row">
        <label class="setting-label">
            <?php _e('Enable Webhook', 'guardarian-payment'); ?>
        </label>
        <div class="setting-toggle">
            <input type="checkbox" 
                   id="guardarian_webhook_enabled"
                   name="guardarian_webhook_enabled" 
                   value="1" 
                   <?php checked(get_option('guardarian_webhook_enabled'), 1); ?>>
            <label for="guardarian_webhook_enabled"><?php _e('Enable webhook to receive real-time transaction updates', 'guardarian-payment'); ?></label>
        </div>
    </div>

    <div class="setting-row">
        <label class="setting-label" for="guardarian_webhook_secret">
            <?php _e('Webhook Secret', 'guardarian-payment'); ?>
        </label>
        <input type="text" 
               id="guardarian_webhook_secret"
               name="guardarian_webhook_secret" 
               class="setting-input" 
               value="<?php echo esc_attr(get_option('guardarian_webhook_secret', '')); ?>">
        <span class="setting-description">
            <?php _e('The secret key to verify webhook requests. This should match the secret in your Guardarian Partner Portal.', 'guardarian-payment'); ?>
        </span>
    </div>

    <div class="setting-row">
        <label class="setting-label">
            <?php _e('Webhook URL', 'guardarian-payment'); ?>
        </label>
        <div class="code-box">
            <?php echo esc_html(Guardarian_Webhook::get_webhook_url()); ?>
        </div>
        <span class="setting-description">
            <?php _e('Add this URL to your Guardarian Partner Portal to receive webhook notifications.', 'guardarian-payment'); ?>
        </span>
    </div>
</div>