<?php
/**
 * Template: Payment Widget
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="guardarian-payment-widget" data-theme="<?php echo esc_attr($atts['theme']); ?>" 
     style="max-width: <?php echo esc_attr($atts['width']); ?>;">
    
    <div class="guardarian-widget-header">
        <h3><?php echo esc_html($atts['title']); ?></h3>
        <p class="widget-subtitle">
            <?php _e('Convert USD to USDC on Ethereum', 'guardarian-payment'); ?>
        </p>
    </div>
    
    <form class="guardarian-payment-form" method="post">
        <div class="form-group">
            <label for="guardarian-amount" class="form-label">
                <?php _e('Amount (USD)', 'guardarian-payment'); ?>
            </label>
            <div class="input-wrapper">
                <span class="currency-symbol">$</span>
                <input type="number" 
                       id="guardarian-amount" 
                       name="amount" 
                       class="form-input" 
                       placeholder="0.00" 
                       step="0.01" 
                       min="<?php echo esc_attr(get_option('guardarian_min_amount', 50)); ?>"
                       max="<?php echo esc_attr(get_option('guardarian_max_amount', 10000)); ?>"
                       value="<?php echo esc_attr($atts['default_amount']); ?>"
                       required>
            </div>
            <div class="amount-limits">
                <?php 
                printf(
                    __('Min: $%s | Max: $%s', 'guardarian-payment'),
                    number_format(get_option('guardarian_min_amount', 50)),
                    number_format(get_option('guardarian_max_amount', 10000))
                );
                ?>
            </div>
        </div>
        
        <?php if ($atts['show_exchange_rate'] === 'true'): ?>
        <div class="estimate-display" style="display: none;">
            <div class="estimate-box">
                <div class="estimate-label">
                    <?php _e('You will receive approximately:', 'guardarian-payment'); ?>
                </div>
                <div class="estimate-amount">0.00 USDC</div>
                <div class="exchange-rate">1 USD = 1.00 USDC</div>
                <div class="fetching-rates" style="display: none;"><?php _e('Fetching rates...', 'guardarian-payment'); ?></div>
                <div class="estimate-note">
                    <small><?php _e('* Rate updates every 30 seconds', 'guardarian-payment'); ?></small>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="form-group">
            <label for="guardarian-wallet" class="form-label">
                <?php _e('Destination Wallet', 'guardarian-payment'); ?>
            </label>
            <div class="wallet-display">
                <code><?php echo esc_html(get_option('guardarian_default_wallet', '')); ?></code>
                <button type="button" class="copy-wallet-btn" title="<?php _e('Copy', 'guardarian-payment'); ?>">
                    <span class="dashicons dashicons-clipboard"></span>
                </button>
            </div>
            <small class="form-help">
                <?php _e('USDC will be sent to this Ethereum wallet address', 'guardarian-payment'); ?>
            </small>
        </div>
        
        <div class="form-group">
            <label for="guardarian-email" class="form-label">
                <?php _e('Email (Optional)', 'guardarian-payment'); ?>
            </label>
            <input type="email" 
                   id="guardarian-email" 
                   name="email" 
                   class="form-input" 
                   placeholder="your@email.com">
            <small class="form-help">
                <?php _e('For transaction updates and receipt', 'guardarian-payment'); ?>
            </small>
        </div>
        
        <div class="form-group">
            <label for="guardarian-name" class="form-label">
                <?php _e('Name (Optional)', 'guardarian-payment'); ?>
            </label>
            <input type="text" 
                   id="guardarian-name" 
                   name="name" 
                   class="form-input" 
                   placeholder="<?php _e('Your name', 'guardarian-payment'); ?>">
        </div>
        
        <div class="guardarian-error" style="display: none;"></div>
        
        <div class="guardarian-loading" style="display: none;">
            <span class="spinner"></span>
            <span><?php echo esc_html($atts['button_text']); ?>...</span>
        </div>
        
        <button type="submit" class="guardarian-submit-btn">
            <?php echo esc_html($atts['button_text']); ?>
        </button>
    </form>
    
    <div class="guardarian-widget-footer">
        <small>
            <?php _e('Powered by', 'guardarian-payment'); ?> 
            <a href="https://guardarian.com" target="_blank" rel="noopener">Guardarian</a>
        </small>
    </div>
</div>

<style>
.guardarian-payment-widget {
    background: white;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    margin: 20px auto;
}

.guardarian-widget-header {
    text-align: center;
    margin-bottom: 30px;
}

.guardarian-widget-header h3 {
    margin: 0 0 10px;
    font-size: 24px;
    color: #333;
}

.widget-subtitle {
    color: #666;
    margin: 0;
    font-size: 14px;
}

.form-group {
    margin-bottom: 20px;
}

.form-label {
    display: block;
    font-weight: 600;
    margin-bottom: 8px;
    color: #333;
    font-size: 14px;
}

.input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.currency-symbol {
    position: absolute;
    left: 15px;
    font-weight: 600;
    color: #666;
    font-size: 18px;
}

.form-input {
    width: 100%;
    padding: 14px 15px 14px 35px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 16px;
    transition: all 0.3s ease;
}

.form-input:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
}

.amount-limits {
    margin-top: 8px;
    font-size: 12px;
    color: #666;
}

.estimate-display {
    margin: 20px 0;
}

.estimate-box {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 10px;
    text-align: center;
}

.estimate-label {
    font-size: 13px;
    opacity: 0.9;
    margin-bottom: 10px;
}

.estimate-amount {
    font-size: 28px;
    font-weight: bold;
    margin: 10px 0;
}

.exchange-rate {
    font-size: 14px;
    opacity: 0.9;
    margin-top: 10px;
}

.estimate-note {
    margin-top: 10px;
    opacity: 0.8;
}

.wallet-display {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #f8f9fa;
    padding: 12px;
    border-radius: 8px;
    border: 1px solid #e0e0e0;
}

.wallet-display code {
    flex: 1;
    font-size: 12px;
    word-break: break-all;
    color: #333;
}

.copy-wallet-btn {
    background: #007bff;
    color: white;
    border: none;
    padding: 8px 12px;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.copy-wallet-btn:hover {
    background: #0056b3;
}

.copy-wallet-btn .dashicons {
    width: 18px;
    height: 18px;
    font-size: 18px;
}

.form-help {
    display: block;
    margin-top: 6px;
    color: #666;
    font-size: 12px;
}

.guardarian-error {
    background: #f8d7da;
    color: #721c24;
    padding: 12px 15px;
    border-radius: 8px;
    margin-bottom: 15px;
    border: 1px solid #f5c6cb;
}

.guardarian-loading {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    margin-bottom: 15px;
}

.spinner {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 3px solid #f3f3f3;
    border-top: 3px solid #007bff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.guardarian-submit-btn {
    width: 100%;
    padding: 16px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.guardarian-submit-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.guardarian-submit-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.guardarian-widget-footer {
    text-align: center;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #e0e0e0;
}

.guardarian-widget-footer small {
    color: #666;
    font-size: 12px;
}

.guardarian-widget-footer a {
    color: #007bff;
    text-decoration: none;
}

.guardarian-widget-footer a:hover {
    text-decoration: underline;
}

/* Dark Theme */
.guardarian-payment-widget[data-theme="dark"] {
    background: #1a1a2e;
    color: #fff;
}

.guardarian-payment-widget[data-theme="dark"] .guardarian-widget-header h3,
.guardarian-payment-widget[data-theme="dark"] .form-label {
    color: #fff;
}

.guardarian-payment-widget[data-theme="dark"] .widget-subtitle,
.guardarian-payment-widget[data-theme="dark"] .form-help,
.guardarian-payment-widget[data-theme="dark"] .amount-limits {
    color: #aaa;
}

.guardarian-payment-widget[data-theme="dark"] .form-input {
    background: #16213e;
    border-color: #0f3460;
    color: #fff;
}

.guardarian-payment-widget[data-theme="dark"] .wallet-display {
    background: #16213e;
    border-color: #0f3460;
}

.guardarian-payment-widget[data-theme="dark"] .wallet-display code {
    color: #fff;
}

/* Responsive */
@media (max-width: 768px) {
    .guardarian-payment-widget {
        padding: 20px;
    }
    
    .guardarian-widget-header h3 {
        font-size: 20px;
    }
    
    .estimate-amount {
        font-size: 24px;
    }
}

/* High contrast for accessibility */
@media (prefers-contrast: high) {
    .form-input {
        border-width: 3px;
    }
    
    .form-input:focus {
        border-color: #000;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Copy wallet address
    $('.copy-wallet-btn').on('click', function() {
        var walletAddress = $(this).siblings('code').text();
        
        navigator.clipboard.writeText(walletAddress).then(function() {
            var $btn = $('.copy-wallet-btn');
            var originalHtml = $btn.html();
            $btn.html('<span class="dashicons dashicons-yes"></span>');
            
            setTimeout(function() {
                $btn.html(originalHtml);
            }, 2000);
        });
    });
});
</script>
