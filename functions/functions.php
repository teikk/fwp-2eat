<?php 
function fwpr_returnUrl(){
	$options = FWPR_Options::get_instance()->get_options();
	return get_permalink( $options['global']['return_page'] );
}