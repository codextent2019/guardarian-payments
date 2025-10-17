<?php
/**
 * Template: Admin Logs
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap guardarian-admin">
    <h1><?php _e('Logs', 'guardarian-payment'); ?></h1>

    <div class="guardarian-box">
        <div class="box-header">
            <h2><?php _e('All Logs', 'guardarian-payment'); ?></h2>
            <form method="get">
                <input type="hidden" name="page" value="guardarian-logs">
                <?php
                // search box
                ?>
            </form>
        </div>
        <div class="box-content">
            <?php if (!empty($logs)): ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Log Type', 'guardarian-payment'); ?></th>
                            <th><?php _e('Severity', 'guardarian-payment'); ?></th>
                            <th><?php _e('Message', 'guardarian-payment'); ?></th>
                            <th><?php _e('Date', 'guardarian-payment'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?php echo esc_html($log['log_type']); ?></td>
                                <td>
                                    <span class="guardarian-severity-badge severity-<?php echo esc_attr($log['severity']); ?>">
                                        <?php echo esc_html(ucfirst($log['severity'])); ?>
                                    </span>
                                </td>
                                <td><?php echo esc_html($log['message']); ?></td>
                                <td><?php echo esc_html(date('M j, Y g:i A', strtotime($log['created_at']))); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="tablenav bottom">
                    <div class="tablenav-pages">
                        <span class="displaying-num"><?php echo $total; ?> items</span>
                        <span class="pagination-links">
                            <?php
                            echo paginate_links([
                                'base' => add_query_arg('paged', '%#%'),
                                'format' => '',
                                'prev_text' => __('&laquo;'),
                                'next_text' => __('&raquo;'),
                                'total' => $total_pages,
                                'current' => $paged,
                            ]);
                            ?>
                        </span>
                    </div>
                </div>
            <?php else: ?>
                <div class="guardarian-empty-state">
                    <span class="dashicons dashicons-media-text"></span>
                    <p><?php _e('No logs found', 'guardarian-payment'); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.guardarian-severity-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.severity-info {
    background: #d1ecf1;
    color: #0c5460;
}

.severity-warning {
    background: #fff3cd;
    color: #856404;
}

.severity-error {
    background: #f8d7da;
    color: #721c24;
}
</style>
