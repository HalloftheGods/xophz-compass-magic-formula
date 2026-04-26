<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://example.com
 * @since             1.0.0
 * @package           Xophz_Compass_Magic_Formula
 *
 * @wordpress-plugin
 * Category:          Command Deck
 * Plugin Name:       Xophz Magic Formulas
 * Description:       Proxy from YouMeOS/COMPASS to the Forminator PHP plugin.
 * Version:           26.4.26
 * Author:            Hall of the Gods, Inc.
 * Author URI:        https://hallofthegods.com		/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       xophz-compass-magic-formula
 * Domain Path:       /languages
 * Update URI:        https://github.com/HalloftheGods/xophz-compass-magic-formula
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
define( 'XOPHZ_COMPASS_MAGIC_FORMULA_VERSION', '26.4.26' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-xophz-compass-magic-formula.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_xophz_compass_magic_formula() {
	if ( ! class_exists( 'Xophz_Compass' ) ) {
		add_action( 'admin_init', 'shutoff_xophz_compass_magic_formula' );

		function shutoff_xophz_compass_magic_formula() {
			if ( ! function_exists( 'deactivate_plugins' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}
			deactivate_plugins( plugin_basename( __FILE__ ) );
		}

		function admin_notice_xophz_compass_magic_formula() {
			echo '<div class="error"><h2><strong>Xophz Magic Formulas</strong> requires COMPASS to run. It has self <strong>deactivated</strong>.</h2></div>';
			if ( isset( $_GET['activate'] ) )
				unset( $_GET['activate'] );
		}
	} else {
		$plugin = new Xophz_Compass_Magic_Formula();
		$plugin->run();
	}
}
add_action( 'plugins_loaded', 'run_xophz_compass_magic_formula', 99 );
