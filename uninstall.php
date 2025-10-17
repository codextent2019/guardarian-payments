<?php
/**
 * Guardarian Uninstall
 *
 * Uninstalling Guardarian deletes tables and options.
 *
 * @package Guardarian
 * @since   1.0.0
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

// Delete options.
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'guardarian\_%';" );

// Delete database tables.
$transactions_table = $wpdb->prefix . 'guardarian_transactions';
$logs_table = $wpdb->prefix . 'guardarian_logs';
$wpdb->query( "DROP TABLE IF EXISTS {$transactions_table}" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
$wpdb->query( "DROP TABLE IF EXISTS {$logs_table}" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

// Clear any cached data that has been stored.
wp_cache_flush();

/**
 * Find all options that were created for the plugin.
 * This is a more robust way to delete options if you don't have a consistent prefix.
 * For this plugin, the LIKE query above is sufficient.
 *
 * $options = get_option( 'guardarian_options_list', array() ); // Assuming you store a list of your options.
 * if ( ! empty( $options ) ) {
 *  foreach ( $options as $option ) {
 *      delete_option( $option );
 *  }
 * }
 */