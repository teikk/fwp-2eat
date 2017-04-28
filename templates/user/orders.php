<?php 
$orders = fwpr_getUserOrders();
?>
<?php if( $orders->have_posts() && is_user_logged_in() ): ?>
	<div class="fwpr-user">
		<div class="col-md-9">
			<h2 class="fwpr-user__title"><?php _e('Twoje zamówienia','fwpr'); ?></h2>
			<?php while( $orders->have_posts() ): $orders->the_post(); ?>
				<?php fwpr_template('user/single-order'); ?>
				<?php echo do_shortcode( '[fwpr_user_active_orders]' ); ?>

				<?php echo do_shortcode( '[fwpr_user_ended_orders]' ); ?>
			<?php endwhile; ?>
			<?php wp_reset_postdata(); ?>
		</div>
		<div class="col-md-3">
			<?php echo do_shortcode( '[fwpr_cart]' ); ?>
		</div>
	</div>
<?php endif; ?>