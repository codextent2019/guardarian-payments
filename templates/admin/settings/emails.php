<?php
/**
 * Template: Settings - Emails
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="settings-section">
    <h2><?php _e('Email Templates', 'guardarian-payment'); ?></h2>
    <p><?php _e('Customize the emails sent to admins and customers.', 'guardarian-payment'); ?></p>

    <h3><?php _e('Admin Email', 'guardarian-payment'); ?></h3>

    <div class="setting-row">
        <label class="setting-label" for="guardarian_admin_email_subject">
            <?php _e('Subject', 'guardarian-payment'); ?>
        </label>
        <input type="text" 
               id="guardarian_admin_email_subject"
               name="guardarian_admin_email_subject" 
               class="setting-input" 
               value="<?php echo esc_attr(get_option('guardarian_admin_email_subject', 'New Transaction Notification')); ?>">
    </div>

    <div class="setting-row">
        <label class="setting-label" for="guardarian_admin_email_body">
            <?php _e('Body', 'guardarian-payment'); ?>
        </label>
        <?php
        $admin_email_body = get_option('guardarian_admin_email_body', 'A new transaction with ID {transaction_id} has been created.');
        wp_editor($admin_email_body, 'guardarian_admin_email_body', ['textarea_name' => 'guardarian_admin_email_body']);
        ?>
        <span class="setting-description">
            <?php _e('Available placeholders: {transaction_id}, {status}, {amount_from}, {currency_from}, {amount_to}, {currency_to}, {customer_email}, {customer_name}', 'guardarian-payment'); ?>
        </span>
    </div>

    <h3><?php _e('Customer Email', 'guardarian-payment'); ?></h3>

    <div class="setting-row">
        <label class="setting-label" for="guardarian_customer_email_subject">
            <?php _e('Subject', 'guardarian-payment'); ?>
        </label>
        <input type="text" 
               id="guardarian_customer_email_subject"
               name="guardarian_customer_email_subject" 
               class="setting-input" 
               value="<?php echo esc_attr(get_option('guardarian_customer_email_subject', 'Your Transaction Details')); ?>">
    </div>

    <div class="setting-row">
        <label class="setting-label" for="guardarian_customer_email_body">
            <?php _e('Body', 'guardarian-payment'); ?>
        </label>
        <?php
        $customer_email_body = get_option('guardarian_customer_email_body', 'Your transaction with ID {transaction_id} is now {status}.');
        wp_editor($customer_email_body, 'guardarian_customer_email_body', ['textarea_name' => 'guardarian_customer_email_body']);
        ?>
        <span class="setting-description">
            <?php _e('Available placeholders: {transaction_id}, {status}, {amount_from}, {currency_from}, {amount_to}, {currency_to}', 'guardarian-payment'); ?>
        </span>
    </div>
</div>
