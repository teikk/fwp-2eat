<?php 
add_action( 'init', 'fwpr_register_cpt' );
function fwpr_register_cpt() {

	/**
	 * Post Type: Produkty.
	 */

	$labels = array(
		"name" => __( 'Produkty', '' ),
		"singular_name" => __( 'Produkt', '' ),
	);

	$args = array(
		"label" => __( 'Produkty', '' ),
		"labels" => $labels,
		"description" => "",
		"public" => true,
		"publicly_queryable" => true,
		"show_ui" => true,
		"show_in_rest" => false,
		"rest_base" => "",
		"has_archive" => false,
		"show_in_menu" => true,
		"exclude_from_search" => false,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"rewrite" => array( "slug" => "fwpr_product", "with_front" => true ),
		"query_var" => true,
		"menu_icon" => "dashicons-cart",
		"supports" => array( "title", "editor", "thumbnail" ),
	);

	register_post_type( "fwpr_product", $args );

	/**
	 * Post Type: Płatności.
	 */

	$labels = array(
		"name" => __( 'Płatności', '' ),
		"singular_name" => __( 'Płatność', '' ),
	);

	$args = array(
		"label" => __( 'Płatności', '' ),
		"labels" => $labels,
		"description" => "",
		"public" => false,
		"publicly_queryable" => false,
		"show_ui" => true,
		"show_in_rest" => false,
		"rest_base" => "",
		"has_archive" => false,
		"show_in_menu" => true,
		"exclude_from_search" => true,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"rewrite" => array( "slug" => "fwpr_payments", "with_front" => true ),
		"query_var" => true,
		"menu_icon" => "dashicons-clipboard",
		"supports" => array( "title" ),
	);

	register_post_type( "fwpr_payments", $args );

	/**
	 * Post Type: Zamówienia.
	 */

	$labels = array(
		"name" => __( 'Zamówienia', '' ),
		"singular_name" => __( 'Zamówienie', '' ),
	);

	$args = array(
		"label" => __( 'Zamówienia', '' ),
		"labels" => $labels,
		"description" => "",
		"public" => true,
		"publicly_queryable" => true,
		"show_ui" => true,
		"show_in_rest" => false,
		"rest_base" => "",
		"has_archive" => false,
		"show_in_menu" => true,
		"exclude_from_search" => false,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"rewrite" => array( "slug" => "fwpr_order", "with_front" => true ),
		"query_var" => true,
		"menu_icon" => "dashicons-archive",
		"supports" => array( "title" ),
	);

	register_post_type( "fwpr_order", $args );
}