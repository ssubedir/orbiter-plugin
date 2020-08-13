<?php 
/**
 * @package  AlecadddPlugin
 */
namespace Inc\Base;

use Inc\Base\BaseController;

/**
* 
*/
class Enqueue extends BaseController
{
	public function register() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
		//add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
	}
	
	function enqueue() {
		// enqueue all our scripts
		wp_enqueue_script( 'media-upload' );
		wp_enqueue_media();
		wp_enqueue_script('jquery');
		wp_enqueue_style( 'mypluginstyle', $this->plugin_url . 'assets/mystyle.css' );
		wp_enqueue_script( 'mypluginscript', $this->plugin_url . 'assets/myscript.js' );
		// wp_enqueue_script( 'iframeresizer', $this->plugin_url . 'assets/iframeResizer-contentWindow-min.js',array(), '1.0.0', true);
		// wp_enqueue_script( 'iframeresizer2', $this->plugin_url . 'assets/iframeResizer-min.js', array(), '1.0.0', true);
	}
}