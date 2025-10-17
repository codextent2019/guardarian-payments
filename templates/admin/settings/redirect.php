<?php
/**
 * Template: Settings - Redirect
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="settings-section">
    <h2><?php _e('Redirect Settings', 'guardarian-payment'); ?></h2>

    <div class="setting-row">
        <label class="setting-label" for="guardarian_success_url">
            <?php _e('Success URL', 'guardarian-payment'); ?>
        </label>
        <input type="url" 
               id="guardarian_success_url"
               name="guardarian_success_url" 
               class="setting-input" 
               value="<?php echo esc_attr(get_option('guardarian_success_url', '')); ?>">
        <span class="setting-description">
            <?php _e('The URL to redirect to after a successful payment.', 'guardarian-payment'); ?>
        </span>
    </div>

    <div class="setting-row">
        <label class="setting-label" for="guardarian_failure_url">
            <?php _e('Failure URL', 'guardarian-payment'); ?>
        </label>
        <input type="url" 
               id="guardarian_failure_url"
               name="guardarian_failure_url" 
               class="setting-input" 
               value="<?php echo esc_attr(get_option('guardarian_failure_url', '')); ?>">
        <span class="setting-description">
            <?php _e('The URL to redirect to after a failed payment.', 'guardarian-payment'); ?>
        </span>
    </div>

    <div class="setting-row">
        <label class="setting-label" for="guardarian_cancel_url">
            <?php _e('Cancel URL', 'guardarian-payment'); ?>
        </label>
        <input type="url" 
               id="guardarian_cancel_url"
               name="guardarian_cancel_url" 
               class="setting-input" 
               value="<?php echo esc_attr(get_option('guardarian_cancel_url', '')); ?>">
        <span class="setting-description">
            <?php _e('The URL to redirect to after a cancelled payment.', 'guardarian-payment'); ?>
        </span>
    </div>
</div>