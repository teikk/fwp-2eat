<?php 
$orders = fwpr_getUserOrders();
?>
<?php if( $orders->have_posts() && is_user_logged_in() ): ?>
	<div class="fwpr-user">
		<?php while( $orders->have_posts() ): $orders->the_post(); ?>
			<?php //fwpr_template('user/single-order'); ?>
			<?php echo do_shortcode( '[fwpr_user_active_orders]' ); ?>
			<?php echo do_shortcode( '[fwpr_user_ended_orders]' ); ?>
		<?php endwhile; ?>
		<?php wp_reset_postdata(); ?>
	</div>
<?php else: ?>
	<div class="fwpr-user">
		<h3>Nie masz jeszcze zamówień</h3>
	</div>
<?php endif; ?>