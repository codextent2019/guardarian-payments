<?php
/**
 * Template: Settings - Notification
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="settings-section">
    <h2><?php _e('Notification Settings', 'guardarian-payment'); ?></h2>

    <div class="setting-row">
        <label class="setting-label">
            <?php _e('Admin Notifications', 'guardarian-payment'); ?>
        </label>
        <div class="setting-toggle">
            <input type="checkbox" 
                   id="guardarian_admin_email_enabled"
                   name="guardarian_admin_email_enabled" 
                   value="1" 
                   <?php checked(get_option('guardarian_admin_email_enabled'), 1); ?>>
            <label for="guardarian_admin_email_enabled"><?php _e('Send email notifications to the admin for new transactions.', 'guardarian-payment'); ?></label>
        </div>
    </div>

    <div class="setting-row">
        <label class="setting-label" for="guardarian_admin_email">
            <?php _e('Admin Email', 'guardarian-payment'); ?>
        </label>
        <input type="email" 
               id="guardarian_admin_email"
               name="guardarian_admin_email" 
               class="setting-input" 
               value="<?php echo esc_attr(get_option('guardarian_admin_email', get_option('admin_email'))); ?>">
        <span class="setting-description">
            <?php _e('The email address to send admin notifications to. Defaults to the site admin email.', 'guardarian-payment'); ?>
        </span>
    </div>

    <div class="setting-row">
        <label class="setting-label">
            <?php _e('Customer Notifications', 'guardarian-payment'); ?>
        </label>
        <div class="setting-toggle">
            <input type="checkbox" 
                   id="guardarian_customer_email_enabled"
                   name="guardarian_customer_email_enabled" 
                   value="1" 
                   <?php checked(get_option('guardarian_customer_email_enabled'), 1); ?>>
            <label for="guardarian_customer_email_enabled"><?php _e('Send email notifications to the customer after a successful payment.', 'guardarian-payment'); ?></label>
        </div>
        <span class="setting-description">
            <?php _e('Note: The customer must provide their email address in the payment widget.', 'guardarian-payment'); ?>
        </span>
    </div>
</div>