<?php 
$orders = fwpr_getUserOrders();
?>
<?php if( $orders->have_posts() && is_user_logged_in() ): ?>
	<div class="fwpr-user">
		<?php while( $orders->have_posts() ): $orders->the_post(); ?>
			<?php fwpr_template('user/single-order'); ?>
		<?php endwhile; ?>
	</div>
<?php endif; ?>