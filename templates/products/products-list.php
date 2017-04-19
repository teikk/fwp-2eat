<?php 
	$args = array(
		'post_type' => 'fwpr_product',
		'posts_per_page' => -1
		);
	$query = new WP_Query($args);
?>
<style type="text/css">
	.fwpr-datepicker-container {
		position: relative;
	}
</style>
<h4>Lista produktów</h4>
<?php if( $query->have_posts() ): ?>
	<div class="fwpr-datepicker-container"></div>
	<?php while( $query->have_posts() ): $query->the_post(); ?>
		<?php fwpr_template( 'products/product' ); ?>
	<?php endwhile; ?>
	<?php wp_reset_postdata(); ?>
<?php endif; ?>