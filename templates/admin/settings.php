<?php
/**
 * Template: Settings Page
 */

if (!defined('ABSPATH')) {
    exit;
}

$tabs = [
    'api' => __('API Configuration', 'guardarian-payment'),
    'payment' => __('Payment Settings', 'guardarian-payment'),
    'display' => __('Display Settings', 'guardarian-payment'),
    'redirect' => __('Redirect URLs', 'guardarian-payment'),
    'webhook' => __('Webhook', 'guardarian-payment'),
    'notification' => __('General', 'guardarian-payment'),
    'emails' => __('Emails', 'guardarian-payment'),
];
?>

<div class="wrap guardarian-settings">
    <h1><?php _e('Guardarian Settings', 'guardarian-payment'); ?></h1>
    
    <?php settings_errors('guardarian_messages'); ?>
    
    <nav class="nav-tab-wrapper">
        <?php foreach ($tabs as $tab_key => $tab_label): ?>
            <a href="?page=guardarian-settings&tab=<?php echo $tab_key; ?>" 
               class="nav-tab <?php echo $active_tab === $tab_key ? 'nav-tab-active' : ''; ?>">
                <?php echo esc_html($tab_label); ?>
            </a>
        <?php endforeach; ?>
    </nav>
    
    <form method="post" action="">
        <?php wp_nonce_field('guardarian_settings_nonce'); ?>
        
        <div class="guardarian-settings-content">
            <?php
            switch ($active_tab) {
                case 'api':
                    include GUARDARIAN_PLUGIN_DIR . 'templates/admin/settings/api.php';
                    break;
                case 'payment':
                    include GUARDARIAN_PLUGIN_DIR . 'templates/admin/settings/payment.php';
                    break;
                case 'display':
                    include GUARDARIAN_PLUGIN_DIR . 'templates/admin/settings/display.php';
                    break;
                case 'redirect':
                    include GUARDARIAN_PLUGIN_DIR . 'templates/admin/settings/redirect.php';
                    break;
                case 'webhook':
                    include GUARDARIAN_PLUGIN_DIR . 'templates/admin/settings/webhook.php';
                    break;
                case 'notification':
                    include GUARDARIAN_PLUGIN_DIR . 'templates/admin/settings/notification.php';
                    break;
                case 'emails':
                    include GUARDARIAN_PLUGIN_DIR . 'templates/admin/settings/emails.php';
                    break;
            }
            ?>
        </div>
        
        <p class="submit">
            <input type="submit" name="guardarian_save_settings" class="button button-primary" 
                   value="<?php _e('Save Settings', 'guardarian-payment'); ?>">
        </p>
    </form>
</div>

<style>
.guardarian-settings {
    margin-top: 20px;
}

.guardarian-settings-content {
    background: white;
    border: 1px solid #ddd;
    border-radius: 0 0 8px 8px;
    padding: 30px;
    margin-top: -1px;
}

.settings-section {
    margin-bottom: 30px;
    padding-bottom: 30px;
    border-bottom: 1px solid #eee;
}

.settings-section:last-child {
    border-bottom: none;
}

.settings-section h2 {
    margin-top: 0;
    font-size: 18px;
}

.setting-row {
    margin-bottom: 20px;
}

.setting-label {
    display: block;
    font-weight: 600;
    margin-bottom: 8px;
}

.setting-description {
    display: block;
    color: #666;
    font-size: 13px;
    margin-top: 5px;
    font-style: italic;
}

.setting-input {
    width: 100%;
    max-width: 500px;
}

.setting-input-short {
    width: 200px;
}

.setting-toggle {
    display: flex;
    align-items: center;
    gap: 10px;
}

.guardarian-info-box {
    background: #e7f3ff;
    border-left: 4px solid #0073aa;
    padding: 15px;
    margin: 15px 0;
}

.guardarian-warning-box {
    background: #fff3cd;
    border-left: 4px solid #ffc107;
    padding: 15px;
    margin: 15px 0;
}

.code-box {
    background: #f5f5f5;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 10px;
    font-family: monospace;
    margin: 10px 0;
    word-break: break-all;
}
</style>
