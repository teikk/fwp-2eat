<?php 
	$args = array(
		'post_type' => 'fwpr_product',
		'posts_per_page' => -1
		);
	$query = new WP_Query($args);
?>
<h4>Lista produktów</h4>
<?php if( $query->have_posts() ): ?>
	<?php while( $query->have_posts() ): $query->the_post(); ?>
		<?php fwpr_template( 'products/product' ); ?>
	<?php endwhile; ?>
<?php endif; ?>