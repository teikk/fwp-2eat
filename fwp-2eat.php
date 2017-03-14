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
	function __construct(){}

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
		wp_enqueue_script( 'fwpr-plugins' );
		wp_enqueue_script( 'fwpr-app' );
		wp_localize_script( 'fwpr-plugins', 'fwpr', array(
			'ajaxurl' => admin_url('admin-ajax.php')
			) );	
	}
	public function theme_setup(){
		if( !current_theme_supports( 'posh-thumbnails' ) ) {
			add_theme_support( 'post-thumbnails' );
		}
	}
		
}

require_once( FWPR_DIR . 'load.php' );