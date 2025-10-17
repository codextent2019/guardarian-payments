<?php
/**
 * Template: Settings - Payment
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="settings-section">
    <h2><?php _e('Payment Settings', 'guardarian-payment'); ?></h2>

    <div class="setting-row">
        <label class="setting-label" for="guardarian_default_wallet">
            <?php _e('Default Wallet Address', 'guardarian-payment'); ?>
        </label>
        <input type="text" 
               id="guardarian_default_wallet"
               name="guardarian_default_wallet" 
               class="setting-input" 
               value="<?php echo esc_attr(get_option('guardarian_default_wallet', '')); ?>"
               placeholder="0x...">
        <span class="setting-description">
            <?php _e('The default wallet address to send cryptocurrency to.', 'guardarian-payment'); ?>
        </span>
    </div>

    <div class="setting-row">
        <label class="setting-label" for="guardarian_currency_from">
            <?php _e('Currency From', 'guardarian-payment'); ?>
        </label>
        <input type="text" 
               id="guardarian_currency_from"
               name="guardarian_currency_from" 
               class="setting-input-short" 
               value="<?php echo esc_attr(get_option('guardarian_currency_from', 'USD')); ?>"
               readonly>
        <span class="setting-description">
            <?php _e('The currency to convert from (currently fixed to USD).', 'guardarian-payment'); ?>
        </span>
    </div>

    <div class="setting-row">
        <label class="setting-label" for="guardarian_currency_to">
            <?php _e('Currency To', 'guardarian-payment'); ?>
        </label>
        <input type="text" 
               id="guardarian_currency_to"
               name="guardarian_currency_to" 
               class="setting-input-short" 
               value="<?php echo esc_attr(get_option('guardarian_currency_to', 'USDC_ETH')); ?>"
               readonly>
        <span class="setting-description">
            <?php _e('The currency to convert to (currently fixed to USDC on Ethereum).', 'guardarian-payment'); ?>
        </span>
    </div>

    <div class="setting-row">
        <label class="setting-label" for="guardarian_min_amount">
            <?php _e('Minimum Amount (USD)', 'guardarian-payment'); ?>
        </label>
        <input type="number" 
               id="guardarian_min_amount"
               name="guardarian_min_amount" 
               class="setting-input-short" 
               value="<?php echo esc_attr(get_option('guardarian_min_amount', 50)); ?>"
               min="0" 
               step="1">
        <span class="setting-description">
            <?php _e('The minimum transaction amount in USD.', 'guardarian-payment'); ?>
        </span>
    </div>

    <div class="setting-row">
        <label class="setting-label" for="guardarian_max_amount">
            <?php _e('Maximum Amount (USD)', 'guardarian-payment'); ?>
        </label>
        <input type="number" 
               id="guardarian_max_amount"
               name="guardarian_max_amount" 
               class="setting-input-short" 
               value="<?php echo esc_attr(get_option('guardarian_max_amount', 10000)); ?>"
               min="0" 
               step="1">
        <span class="setting-description">
            <?php _e('The maximum transaction amount in USD. Leave blank for no limit.', 'guardarian-payment'); ?>
        </span>
    </div>

    <div class="setting-row">
        <label class="setting-label" for="guardarian_transaction_timeout">
            <?php _e('Transaction Timeout (minutes)', 'guardarian-payment'); ?>
        </label>
        <input type="number" 
               id="guardarian_transaction_timeout"
               name="guardarian_transaction_timeout" 
               class="setting-input-short" 
               value="<?php echo esc_attr(get_option('guardarian_transaction_timeout', 30)); ?>"
               min="5" 
               step="1">
        <span class="setting-description">
            <?php _e('The number of minutes before a pending transaction is considered expired.', 'guardarian-payment'); ?>
        </span>
    </div>

    <div class="setting-row">
        <label class="setting-label">
            <?php _e('Enable Emails', 'guardarian-payment'); ?>
        </label>
        <div class="setting-toggle">
            <input type="checkbox" 
                   id="guardarian_enable_emails"
                   name="guardarian_enable_emails" 
                   value="1" 
                   <?php checked(get_option('guardarian_enable_emails'), 1); ?>>
            <label for="guardarian_enable_emails"><?php _e('Enable all email notifications', 'guardarian-payment'); ?></label>
        </div>
        <span class="setting-description">
            <?php _e('Master switch to enable or disable all emails sent by the plugin.', 'guardarian-payment'); ?>
        </span>
    </div>
</div>