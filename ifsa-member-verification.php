<?php

/**
 * @wordpress-plugin
 * Plugin Name:       IFSA Member Verification
 * Plugin URI:        #
 * Description:       IFSA member verification allows to manage the verification of IFSA members and give them access to the private features on the website.
 * Version:           1.0.0
 * Author:            Multidots
 * Author URI:        #
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       ifsa-member-verification
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
define( 'IFSA_MEMBER_VERIFICATION_VERSION', '1.0.4' );
define( 'IFSA_MEMBER_VERIFICATION_HOME', plugin_dir_url( __FILE__ ) );

// File, Path & Url.
define( 'IFSA_MEMBER_VERIFICATION_FILE', __FILE__ );
define( 'IFSA_MEMBER_VERIFICATION_PATH', plugin_dir_path( IFSA_MEMBER_VERIFICATION_FILE ) );
define( 'IFSA_MEMBER_VERIFICATION_URL', plugin_dir_url( IFSA_MEMBER_VERIFICATION_FILE ) );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-ifsa-member-verification-activator.php
 */
function activate_ifsa_member_verification() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-ifsa-member-verification-activator.php';
	Ifsa_Member_Verification_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-ifsa-member-verification-deactivator.php
 */
function deactivate_ifsa_member_verification() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-ifsa-member-verification-deactivator.php';
	Ifsa_Member_Verification_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_ifsa_member_verification' );
register_deactivation_hook( __FILE__, 'deactivate_ifsa_member_verification' );
add_filter( 'bp_core_signup_send_activation_key', '__return_false' );
function wpse27856_set_content_type(){
    return "text/html";
}
add_filter( 'wp_mail_content_type','wpse27856_set_content_type' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-ifsa-member-verification.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_ifsa_member_verification() {
	
	$plugin = new Ifsa_Member_Verification();
	$plugin->run();
	
}

run_ifsa_member_verification();
