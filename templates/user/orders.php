<?php 
$orders = fwpr_getUserOrders();

?>
<?php if( $orders->have_posts() ): ?>
	<?php while( $orders->have_posts() ): $orders->the_post(); ?>
		<?php the_title();?>
		<?php echo fwpr_payment_label(get_field('order_payment_type')); ?>
	<?php endwhile; ?>
<?php endif; ?>