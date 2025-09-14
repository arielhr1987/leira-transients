<?php

/**
 * The plugin bootstrap file
 *
 * WordPress reads this file to generate the plugin information in the plugin admin area.
 * This file also includes all the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function that starts the plugin.
 *
 * @link              https://github.com/arielhr1987
 * @since             1.0.0
 * @package           Leira_Transients
 *
 * @wordpress-plugin
 * Plugin Name: Leira Transients
 * Plugin URI: https://github.com/arielhr1987/leira-transients
 * Description: View, create, edit, and delete WordPress transients from the admin dashboard. Ideal for developers and advanced users who want better control over cached data.
 * Version: 1.0.0
 * Author: Ariel
 * Author URI: https://leira.dev
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: leira-transients
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

use Leira_Transients\Includes\Plugin;
use Leira_Transients\Includes\Activator;

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'LEIRA_TRANSIENTS_VERSION', '1.0.0' );

/**
 * Register the plugin's autoloader
 */
require_once plugin_dir_path( __FILE__ ) . 'includes' . DIRECTORY_SEPARATOR . 'autoload.php';

/**
 * The code that runs during plugin activation.
 */
register_activation_hook( __FILE__, array( Activator::class, 'activate' ) );

/**
 * The code that runs during plugin deactivation.
 */
register_deactivation_hook( __FILE__, array( Activator::class, 'deactivate' ) );

/**
 * Helper method to get the main instance of the plugin
 *
 * @return Leira_Transients\Includes\Plugin
 * @since    1.0.0
 * @access   global
 */
function leira_transients() {
	return Plugin::instance();
}

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
leira_transients()->run();
