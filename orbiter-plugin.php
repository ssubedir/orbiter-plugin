<?php
/**
 * @package  OrbiterPlugin
 */
/*
Plugin Name: Orbiter Plugin
Plugin URI:  http://orbiter.ml
Description: Plugin for orbiter.
Version: 1.0.0
Author: Rajan Subedi
Author URI: http://orbiter.ml
License: GPLv2 or later
Text Domain: orbiter-plugin
*/

// If this file is called firectly, abort!!!
defined( 'ABSPATH' ) or die( 'Hey, what are you doing here? You silly human!' );

// Require once the Composer Autoload
if ( file_exists( dirname( __FILE__ ) . '/vendor/autoload.php' ) ) {
	require_once dirname( __FILE__ ) . '/vendor/autoload.php';
}

/**
 * The code that runs during plugin activation
 */
function activate_orbiter_plugin() {
	Inc\Base\Activate::activate();
}

register_activation_hook( __FILE__, 'activate_orbiter_plugin' );

/**
 * The code that runs during plugin deactivation
 */
function deactivate_orbiter_plugin() {
	Inc\Base\Deactivate::deactivate();
}
register_deactivation_hook( __FILE__, 'deactivate_orbiter_plugin' );

/**
 * Initialize all the core classes of the plugin
 */
if ( class_exists( 'Inc\\Init' ) ) {
	Inc\Init::registerServices();
}


function theme_scripts() {
	$plugin_url=plugin_dir_url( dirname( __FILE__ ) );
	//echo(plugin_dir_url( __FILE__ )	);
	wp_enqueue_script('jquery');
	//wp_enqueue_script( 'iframeresizer',  plugin_dir_url( __FILE__ ) . 'assets/iframeResizer-contentWindow-min.js',[], false, true);
	//wp_enqueue_script( 'iframeresizer2',  plugin_dir_url( __FILE__ ) . 'assets/iframeResizer-min.js',[], false, true);
}

  add_action('wp_enqueue_scripts', 'theme_scripts');

function so_post_40744782( $new_status, $old_status, $post ) {
    if ( $new_status == 'publish' && $old_status != 'publish' ) {
	$plugin_dir_path = dirname(__FILE__);
	$post_log = $plugin_dir_path.'/plugin_log.txt';
	$message = get_the_title($post).' was just published';
	
	$myoptions = get_option('orbiter_api_key');

	// make api request to orbiter

	/*

	{
		"api_key": "awdwd4485w4d5w1d5wd5",
		"thread_name": "Demo",
		"thread_content": "Demo",
		"thread_link":"http://127.0.0.1/wordpress/reddit/"
	}

	*/
	



	$link = preg_replace('#^https?://#', '', get_the_permalink($post,false));

	if(file_exists($post_log)){
		$file = fopen($post_log,'a');
		fwrite($file,$message."\n");
		fwrite($file,$myoptions."\n");
		fwrite($file,$link."\n");
	}
	else{
		$file = fopen($post_log,'w');
		fwrite($file,$message."\n");
		fwrite($file,$myoptions."\n");
		fwrite($file,$link."\n");
	}


	$url = 'https://api.orbiter.ml/api/community/v1/thread/new';
	
	$data = array(
		'api_key' => $myoptions, 
		'thread_name' => get_the_title($post), 
		'thread_content' => get_the_title($post),
		'thread_link' => $link
	);

	
	$resp = wp_remote_post($url, array(
		'method' => 'POST',
		'timeout'     => 60, // added
		'redirection' => 5,  // added
		'blocking'    => true, // added
		'httpversion' => '1.0',
		'sslverify' => true,
		'body' => json_encode($data))
	);

	if (is_wp_error($resp)) {
		$error = $resp->get_error_message();
			
			if(file_exists($post_log)){
				$file = fopen($post_log,'a');
				fwrite($file,$message."\n");
				fwrite($file,$myoptions."\n");
				fwrite($file,$link."\n");
				fwrite($file,'Response:'.$error,' \n');	
			}
			else{
				$file = fopen($post_log,'w');
				fwrite($file,$message."\n");
				fwrite($file,$myoptions."\n");
				fwrite($file,$link."\n");
				fwrite($file,'Response:'.$error,' \n');	
			}

	} else {
		$content = wp_remote_retrieve_body($response);

		if(file_exists($post_log)){
			$file = fopen($post_log,'a');
			fwrite($file,$message."\n");
					fwrite($file,$myoptions."\n");
					fwrite($file,$link."\n");
			fwrite($file,'Response:'.$content,' \n');	
		}
		else{
			$file = fopen($post_log,'w');
			fwrite($file,$message."\n");
					fwrite($file,$myoptions."\n");
					fwrite($file,$link."\n");
			fwrite($file,'Response:'.$content,' \n');	
		} 		
	}

	fclose($file);

	}
}



add_action('transition_post_status', 'so_post_40744782', 10, 3);


function orbiter_content( $content ) {
	if(check_post_type()){
		$cc =  '<iframe id="myIframe" src="https://embed.orbiter.ml/embed?thread_link='.preg_replace('#^https?://#', '', get_permalink(get_the_ID())).'" scrolling="no" horizontalscrolling="no" verticalscrolling="no" width="100%" frameborder="0"></iframe>';
		$custom_content = $content.$cc;
		return $custom_content;
	}
	else{
		return $content;
	}
	
	//return $content;
    
}

// add jq
add_filter( 'the_content', 'orbiter_content' );

function add_code_on_body_open() { // iframe

	echo '<style>iframe {width: 1px;min-width: 100%;}</style>';

	echo '<script src="https://cdn.jsdelivr.net/npm/iframe-resizer@4.2.11/js/iframeResizer.contentWindow.js" integrity="sha256-8iqXzZqircMm1m0IQjQQbGjbeIHEvwxZ7MGYbCnOTfg=" crossorigin="anonymous"></script>';
	echo '<script src="https://cdn.jsdelivr.net/npm/iframe-resizer@4.2.11/js/iframeResizer.min.js" integrity="sha256-GAWxQnl2DiqTOLcfQGScRf4328ODm7VzqXN83Ulel1I=" crossorigin="anonymous"></script>';
	echo '<script type="text/javascript">document.addEventListener("DOMContentLoaded", function(event) { console.log("loaded"); iFrameResize({ log: false }, "#myIframe");});</script>';

}

add_action('wp_body_open', 'add_code_on_body_open');

function is_blog () {
	global  $post;
	$posttype = get_post_type($post );
	return ( ((is_archive()) || (is_author()) || (is_category()) || (is_home()) || (is_single()) || (is_tag())) && ( $posttype == 'post')  ) ? true : false ;
}

function check_post_type(){

		// Error
		if ( is_404() ) {
			//echo '404';
			return false;
		}
		// Front page
		if ( is_front_page() ) {
			//echo 'frontpage';
			return false;
		}
		// Archive
		if ( is_archive() ) {
			//echo 'archive';
			return false;
		}
	
		// Search
		if ( is_search() ) {
			//echo 'search';
			return false;
		}
		// Singular
		if ( is_singular() ) {
			//echo 'singular';
			//echo get_permalink(get_the_ID());
			return true;
		}
		// Home - the blog page
		if (is_blog()){
			//echo 'blog';
			return true;
		}

		if ( is_home() ) {
			//echo 'home';
			return true;
		}
		
		return false;

  }


//   (function($) {
// 	jQuery(window).on("load", function({
// 		$("#odbFrame").iFrameResize({
// 		resizeFrom: "child",
// 		scrolling: false,
// 		log: true,
// 		});
// 	});
// }(jQuery));
	

