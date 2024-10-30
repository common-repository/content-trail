<?php

/*
  Plugin Name: User Related Posts
  Plugin URI:  http://recosenselabs.com/Plugins/WordPress/
  Description: Our powerful algorithm uses Natural language processing and Artificial intelligence to understand the semantics of every post on any publisher\'s site which gets automatically collated by our plugin.We identify places, people, organisations, etc mentioned in the post and use our content profiling capabilities to generate categories around the post. Through this highly structured metadata, we find other posts that are similar and suggest them.We can also personalise content for every user based on his preferences such as favourite genres, personalities, etc. We can generate bands such as trending content, popular content, specific content around politics/sports (Yes, our algorithm can identify what the content is about).Our recommendation algorithm is language agnostic. We can understand content in any language to recommend related content in the same language or across languages.
  Version:     1.2.8
  Author:      RecoSense
  Author URI:  http://recosenselabs.com/
  License:     GPLv2
  License URI: https://www.gnu.org/licenses/gpl-2.0.html
  Text Domain: content_trail
  Domain Path: /languages
 */

// If this file is called directly, abort.
if ( !defined( 'ABSPATH' ) ) {
	die;
}

define( 'RCT_PREFIX', 'rct' );
define( 'RCT_VERSION', '1.2.8' );
define( 'RCT_PATH', trailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'RCT_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'RCT_SETTINGS_SLUG', RCT_PREFIX . '_my_setting' );
define( 'RCT_GUID', parse_url( get_site_url() )[ 'host' ] );
define( 'RCT_API_URL', 'https://wpplugin.recosenselabs.com' );

/**
 * The core plugin class
 */
require_once RCT_PATH . 'includes/class-content-trail.php';

/**
 * Load the admin class if its the admin dashboard
 */
if ( is_admin() ) {
	require_once RCT_PATH . 'admin/class-content-trail-admin.php';
}

function reco_plugin_activated()
{
    set_transient( 'fx-admin-notice-example', true, 5 );
     wp_remote_post( RCT_API_URL.'/wordpress_status', array(
		'method' => 'POST',
		'timeout' => 45,
		'body' => array( 'guid' => $_SERVER[ 'SERVER_NAME' ], 'status' => 'activated' )
		)
	); 
	
}
register_activation_hook( __FILE__, "reco_plugin_activated");
add_action( 'admin_notices', 'my_plugin_activation_notice' );

function my_plugin_activation_notice()
{
    /* Check transient, if available display notice */
    if( get_transient( 'fx-admin-notice-example' ) ){
        ?>
        <div class="updated notice is-dismissible">
            <p>Thankyou for using User Related Posts plugin. Please wait for few minutes before you can start seeing suggestions in our widgets <strong>(Recommendation, You may like, Trending and Tag Clouds)</strong>.</p>
        </div>
        <?php
        /* Delete transient, only display this notice once. */
        delete_transient( 'fx-admin-notice-example' );
    }
}
function reco_plugin_deactivated()
{
     wp_remote_post( RCT_API_URL.'/wordpress_status', array(
		'method' => 'POST',
		'timeout' => 45,
		'body' => array( 'guid' => $_SERVER[ 'SERVER_NAME' ], 'status' => 'deactivated' )
		)
	);
}
register_deactivation_hook( __FILE__, "reco_plugin_deactivated");

function reco_plugin_uninstalled(){
    wp_remote_post( RCT_API_URL.'/wordpress_status', array(
		'method' => 'POST',
		'timeout' => 45,
		'body' => array( 'guid' => $_SERVER[ 'SERVER_NAME' ], 'status' => 'uninstalled' )
		)
	);
}
register_uninstall_hook( __FILE__, 'reco_plugin_uninstalled' );
