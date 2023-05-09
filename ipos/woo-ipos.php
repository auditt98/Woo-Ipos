<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/auditt98/Woo-Ipos
 * @since             1.0.0
 * @package           Woo_Ipos
 *
 * @wordpress-plugin
 * Plugin Name:       Woo IPOS
 * Plugin URI:        https://github.com/auditt98/Woo-Ipos
 * Description:       Plugin tích hợp IPOS vào WooCommerce
 * Version:           1.0.0
 * Author:            auditt98
 * Author URI:        https://github.com/auditt98/Woo-Ipos
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woo-ipos
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WOO_IPOS_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-woo-ipos-activator.php
 */
function activate_woo_ipos() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woo-ipos-activator.php';
	Woo_Ipos_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-woo-ipos-deactivator.php
 */
function deactivate_woo_ipos() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woo-ipos-deactivator.php';
	Woo_Ipos_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_woo_ipos' );
register_deactivation_hook( __FILE__, 'deactivate_woo_ipos' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-woo-ipos.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_woo_ipos() {

	$plugin = new Woo_Ipos();
	$plugin->run();

}
run_woo_ipos();
