<?php
/**
 * Template: Settings - Display
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="settings-section">
    <h2><?php _e('Display Settings', 'guardarian-payment'); ?></h2>

    <div class="setting-row">
        <label class="setting-label" for="guardarian_widget_title">
            <?php _e('Widget Title', 'guardarian-payment'); ?>
        </label>
        <input type="text" 
               id="guardarian_widget_title"
               name="guardarian_widget_title" 
               class="setting-input" 
               value="<?php echo esc_attr(get_option('guardarian_widget_title', 'Buy Cryptocurrency')); ?>">
        <span class="setting-description">
            <?php _e('The title displayed at the top of the payment widget.', 'guardarian-payment'); ?>
        </span>
    </div>

    <div class="setting-row">
        <label class="setting-label" for="guardarian_button_text">
            <?php _e('Button Text', 'guardarian-payment'); ?>
        </label>
        <input type="text" 
               id="guardarian_button_text"
               name="guardarian_button_text" 
               class="setting-input" 
               value="<?php echo esc_attr(get_option('guardarian_button_text', 'Continue to Payment')); ?>">
        <span class="setting-description">
            <?php _e('The text displayed on the payment button.', 'guardarian-payment'); ?>
        </span>
    </div>

    <div class="setting-row">
        <label class="setting-label">
            <?php _e('Widget Theme', 'guardarian-payment'); ?>
        </label>
        <select name="guardarian_widget_theme" class="setting-input-short">
            <option value="light" <?php selected(get_option('guardarian_widget_theme'), 'light'); ?>>
                <?php _e('Light', 'guardarian-payment'); ?>
            </option>
            <option value="dark" <?php selected(get_option('guardarian_widget_theme'), 'dark'); ?>>
                <?php _e('Dark', 'guardarian-payment'); ?>
            </option>
        </select>
        <span class="setting-description">
            <?php _e('The color theme for the payment widget.', 'guardarian-payment'); ?>
        </span>
    </div>

    <div class="setting-row">
        <label class="setting-label" for="guardarian_custom_css">
            <?php _e('Custom CSS', 'guardarian-payment'); ?>
        </label>
        <textarea id="guardarian_custom_css"
                  name="guardarian_custom_css"
                  class="setting-input"
                  rows="5"><?php echo esc_textarea(get_option('guardarian_custom_css', '')); ?></textarea>
        <span class="setting-description">
            <?php _e('Add custom CSS to style the payment widget.', 'guardarian-payment'); ?>
        </span>
    </div>
</div>