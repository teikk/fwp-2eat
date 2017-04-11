<?php 
/**
Plugin Name: FWP 2Eat
Plugin URI: http://fabrykawp.pl/
Description: Simply order food from wordpress
Version: 2.0
Author: teik
Author URI: http://fabrykawp.pl/
*/



define( 'FWPR_DIR' , plugin_dir_path(__FILE__) );
define('FWPR_URI', plugin_dir_url( __FILE__ ));
define('FWPR_DEV',true);

function fwpr_template($slug,$name='',$data = ''){
	if(!empty($name)){
		$name = '-'.$name;
	}
	if ( $overridden_template = locate_template( 'fwpr/'.$slug.$name.'.php',false,false ) ) {
		$load = $overridden_template;
	} else {
		$load = FWPR_DIR . 'templates/'.$slug.$name.'.php';
	}
	include ( $load );
}

function fwpr_parse($string){
	$data = array();
	parse_str($string,$data);
	return $data;
}

add_action('plugins_loaded',array('FWPR_Food','init'));

class FWPR_Food {
	protected static $instance;
	public $test = __CLASS__;
	public $payment_types = array();
	function __construct(){	
	}

	public static function get_instance() {
		// create an object
		NULL === self::$instance and self::$instance = new self;

		return self::$instance; // return the object
	}

	public static function init(){
		$instance = self::get_instance();
		add_action( 'after_setup_theme', array($instance,'theme_setup') );
		add_action( 'wp_enqueue_scripts', array($instance,'scripts') );

	}
	public function scripts(){
		wp_register_script( 'fwpr-plugins', FWPR_URI . 'assets/plugins.js', array( 'jquery' ), false, true );
		wp_register_script( 'fwpr-app', FWPR_URI . 'assets/app.js', array( 'jquery' ), false, true );
		wp_register_script( 'fwpr-bd', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js', array( 'jquery' ), false, true );
		wp_register_script( 'fwpr-gmap','https://maps.googleapis.com/maps/api/js?v=3.exp&key=AIzaSyCKMkFobR97jdlA_UyomSRNWQH8cK1E7zQ&libraries=places,drawing,geometry', array( 'jquery' ), '1.0', false );
		wp_enqueue_script( 'fwpr-plugins' );
		wp_enqueue_script( 'fwpr-bd' );
		wp_enqueue_script( 'fwpr-app' );

		wp_enqueue_style( 'fwpr-bd-css', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker3.min.css' );
		$options = FWPR_Options::get_instance()->get_options();
		wp_localize_script( 'fwpr-plugins', 'fwpr', array(
			'ajaxurl' => admin_url('admin-ajax.php'),
			'maxForTomorrow' => $options['global']['max_for_tomorrow']
			) );	
	}
	public function theme_setup(){
		if( !current_theme_supports( 'posh-thumbnails' ) ) {
			add_theme_support( 'post-thumbnails' );
		}
	}

}

// 1. customize ACF path
add_filter('acf/settings/path', 'fwpr_acf_path');
 
function fwpr_acf_path( $path ) {
	// update path
	$path = FWPR_DIR . 'acf/';
	// return
	return $path;
}
 

// 2. customize ACF dir
add_filter('acf/settings/dir', 'fwpr_acf_dir');
 
function fwpr_acf_dir( $dir ) { 
	// update path
	$dir = FWPR_URI . 'acf/';
	// return
	return $dir;	 
}
// add_filter('acf/settings/show_admin', '__return_false');

require_once( FWPR_DIR . 'load.php' );

