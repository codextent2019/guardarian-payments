<?php
/**
 * Template: Admin Transactions
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap guardarian-admin">
    <h1><?php _e('Transactions', 'guardarian-payment'); ?></h1>

    <div class="guardarian-box">
        <div class="box-header">
            <h2><?php _e('All Transactions', 'guardarian-payment'); ?></h2>
            <form method="get">
                <input type="hidden" name="page" value="guardarian-transactions">
                <?php
                // search box
                ?>
            </form>
        </div>
        <div class="box-content">
            <?php if (!empty($transactions)): ?>
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
                        <?php foreach ($transactions as $txn): ?>
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
                    <span class="dashicons dashicons-list-view"></span>
                    <p><?php _e('No transactions found', 'guardarian-payment'); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
