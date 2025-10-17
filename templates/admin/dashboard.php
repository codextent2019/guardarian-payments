<?php
/**
 * Template: Admin Dashboard
 */

if (!defined('ABSPATH')) {
    exit;
}

$success_rate = $stats['total_transactions'] > 0 
    ? round(($stats['successful_transactions'] / $stats['total_transactions']) * 100, 1) 
    : 0;
?>

<div class="wrap guardarian-admin">
    <h1><?php _e('Guardarian Payment Dashboard', 'guardarian-payment'); ?></h1>
    
    <div class="guardarian-dashboard-stats">
        <div class="stat-card">
            <div class="stat-icon" style="background: #007bff;">
                <span class="dashicons dashicons-chart-line"></span>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($stats['total_transactions']); ?></h3>
                <p><?php _e('Total Transactions', 'guardarian-payment'); ?></p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: #28a745;">
                <span class="dashicons dashicons-yes-alt"></span>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($stats['successful_transactions']); ?></h3>
                <p><?php _e('Successful', 'guardarian-payment'); ?> (<?php echo $success_rate; ?>%)</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: #dc3545;">
                <span class="dashicons dashicons-dismiss"></span>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($stats['failed_transactions']); ?></h3>
                <p><?php _e('Failed', 'guardarian-payment'); ?></p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: #ffc107;">
                <span class="dashicons dashicons-clock"></span>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($stats['pending_transactions']); ?></h3>
                <p><?php _e('Pending', 'guardarian-payment'); ?></p>
            </div>
        </div>
    </div>
    
    <div class="guardarian-dashboard-row">
        <div class="guardarian-box guardarian-box-large">
            <div class="box-header">
                <h2><?php _e('Volume Statistics', 'guardarian-payment'); ?></h2>
            </div>
            <div class="box-content">
                <div class="volume-stats">
                    <div class="volume-item">
                        <div class="volume-label"><?php _e('Total Volume (USD)', 'guardarian-payment'); ?></div>
                        <div class="volume-value">$<?php echo number_format($stats['total_volume_usd'], 2); ?></div>
                    </div>
                    <div class="volume-item">
                        <div class="volume-label"><?php _e('Total Volume (USDC)', 'guardarian-payment'); ?></div>
                        <div class="volume-value"><?php echo number_format($stats['total_volume_usdc'], 6); ?> USDC</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="guardarian-box guardarian-box-small">
            <div class="box-header">
                <h2><?php _e('Quick Actions', 'guardarian-payment'); ?></h2>
            </div>
            <div class="box-content">
                <div class="quick-actions">
                    <button type="button" class="button button-primary button-large" id="test-api-connection">
                        <span class="dashicons dashicons-cloud"></span>
                        <?php _e('Test API Connection', 'guardarian-payment'); ?>
                    </button>
                    
                    <a href="<?php echo admin_url('admin.php?page=guardarian-settings'); ?>" class="button button-secondary button-large">
                        <span class="dashicons dashicons-admin-settings"></span>
                        <?php _e('Settings', 'guardarian-payment'); ?>
                    </a>
                    
                    <a href="<?php echo admin_url('admin.php?page=guardarian-transactions'); ?>" class="button button-secondary button-large">
                        <span class="dashicons dashicons-list-view"></span>
                        <?php _e('View All Transactions', 'guardarian-payment'); ?>
                    </a>
                    
                    <a href="<?php echo admin_url('admin.php?page=guardarian-logs'); ?>" class="button button-secondary button-large">
                        <span class="dashicons dashicons-media-text"></span>
                        <?php _e('View Logs', 'guardarian-payment'); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="guardarian-box">
        <div class="box-header">
            <h2><?php _e('Recent Transactions', 'guardarian-payment'); ?></h2>
            <a href="<?php echo admin_url('admin.php?page=guardarian-transactions'); ?>" class="button button-secondary">
                <?php _e('View All', 'guardarian-payment'); ?>
            </a>
        </div>
        <div class="box-content">
            <?php if (!empty($recent_transactions)): ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Transaction ID', 'guardarian-payment'); ?></th>
                            <th><?php _e('Amount', 'guardarian-payment'); ?></th>
                            <th><?php _e('Status', 'guardarian-payment'); ?></th>
                            <th><?php _e('Date', 'guardarian-payment'); ?></th>
                            <th><?php _e('Actions', 'guardarian-payment'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_transactions as $txn): ?>
                            <tr>
                                <td>
                                    <strong><?php echo esc_html($txn['transaction_id']); ?></strong>
                                    <?php if (!empty($txn['guardarian_id'])): ?>
                                        <br><small style="color: #666;"><?php echo esc_html($txn['guardarian_id']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo number_format($txn['amount_from'], 2); ?> <?php echo esc_html($txn['currency_from']); ?>
                                    <?php if (!empty($txn['amount_to'])): ?>
                                        <br><small style="color: #666;">
                                            → <?php echo number_format($txn['amount_to'], 6); ?> <?php echo esc_html($txn['currency_to']); ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="guardarian-status-badge status-<?php echo esc_attr($txn['status']); ?>">
                                        <?php echo esc_html(ucfirst($txn['status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo esc_html(date('M j, Y g:i A', strtotime($txn['created_at']))); ?>
                                </td>
                                <td>
                                    <button type="button" class="button button-small refresh-transaction" 
                                            data-transaction-id="<?php echo esc_attr($txn['transaction_id']); ?>">
                                        <span class="dashicons dashicons-update"></span>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="guardarian-empty-state">
                    <span class="dashicons dashicons-list-view"></span>
                    <p><?php _e('No transactions yet', 'guardarian-payment'); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="guardarian-box">
        <div class="box-header">
            <h2><?php _e('System Status', 'guardarian-payment'); ?></h2>
        </div>
        <div class="box-content">
            <table class="form-table">
                <tr>
                    <th><?php _e('Plugin Version', 'guardarian-payment'); ?></th>
                    <td><?php echo GUARDARIAN_VERSION; ?></td>
                </tr>
                <tr>
                    <th><?php _e('API Environment', 'guardarian-payment'); ?></th>
                    <td>
                        <span class="guardarian-badge">
                            <?php echo esc_html(ucfirst(get_option('guardarian_api_environment', 'production'))); ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <th><?php _e('API Key Configured', 'guardarian-payment'); ?></th>
                    <td>
                        <?php 
                        $api_key = get_option('guardarian_api_key_production', '');
                        echo !empty($api_key) ? '✓ Yes' : '✗ No';
                        ?>
                    </td>
                </tr>
                <tr>
                    <th><?php _e('Webhook URL', 'guardarian-payment'); ?></th>
                    <td>
                        <code><?php echo esc_html(Guardarian_Webhook::get_webhook_url()); ?></code>
                        <button type="button" class="button button-small copy-webhook-url">
                            <?php _e('Copy', 'guardarian-payment'); ?>
                        </button>
                    </td>
                </tr>
                <tr>
                    <th><?php _e('Webhook Status', 'guardarian-payment'); ?></th>
                    <td>
                        <?php 
                        $webhook_enabled = get_option('guardarian_webhook_enabled', true);
                        echo $webhook_enabled ? '✓ Enabled' : '✗ Disabled';
                        ?>
                    </td>
                </tr>
                <tr>
                    <th><?php _e('Default Wallet', 'guardarian-payment'); ?></th>
                    <td>
                        <code><?php echo esc_html(get_option('guardarian_default_wallet', '')); ?></code>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>

<style>
.guardarian-admin {
    margin-top: 20px;
}

.guardarian-dashboard-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.stat-card {
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    display: flex;
    align-items: center;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
}

.stat-icon .dashicons {
    color: white;
    font-size: 30px;
    width: 30px;
    height: 30px;
}

.stat-content h3 {
    margin: 0;
    font-size: 32px;
    font-weight: bold;
}

.stat-content p {
    margin: 5px 0 0;
    color: #666;
}

.guardarian-dashboard-row {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
    margin: 20px 0;
}

.guardarian-box {
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.box-header {
    padding: 15px 20px;
    border-bottom: 1px solid #ddd;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.box-header h2 {
    margin: 0;
    font-size: 18px;
}

.box-content {
    padding: 20px;
}

.volume-stats {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

.volume-item {
    text-align: center;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 5px;
}

.volume-label {
    font-size: 14px;
    color: #666;
    margin-bottom: 10px;
}

.volume-value {
    font-size: 24px;
    font-weight: bold;
    color: #333;
}

.quick-actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.quick-actions .button {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.guardarian-status-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-success, .status-completed, .status-finished {
    background: #d4edda;
    color: #155724;
}

.status-pending, .status-processing {
    background: #fff3cd;
    color: #856404;
}

.status-failed, .status-error, .status-cancelled {
    background: #f8d7da;
    color: #721c24;
}

.guardarian-empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #666;
}

.guardarian-empty-state .dashicons {
    font-size: 60px;
    width: 60px;
    height: 60px;
    opacity: 0.3;
}

.guardarian-badge {
    display: inline-block;
    padding: 4px 12px;
    background: #e9ecef;
    border-radius: 4px;
    font-weight: 600;
}

@media (max-width: 782px) {
    .guardarian-dashboard-row {
        grid-template-columns: 1fr;
    }
    
    .volume-stats {
        grid-template-columns: 1fr;
    }
}
</style>
