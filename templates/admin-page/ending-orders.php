<?php 
global $post;
$current_date = current_time( 'd/m/Y' );
$reminder = get_field('fwpr_reminder_panel','option');
if( empty($reminder) ) {
	$reminder = 1;
}
$current_date = DateTime::createFromFormat('d/m/Y', $current_date)->getTimestamp();
$nextDay = strtotime('+'.$reminder.' day',$current_date);
$nextDay = date( 'd/m/Y',$nextDay );
/**
 * Get orders that have desired date in their array
 * @var array
 */
$orders = get_posts(
	array(
		'post_type' => 'fwpr_order',
		'posts_per_page' => -1,
		'meta_query' => array(
				array(
					'key' => '_fwpr_order_dates',
					'value' => $nextDay,
					'compare' => 'LIKE'
				)
			)
		)
);
?>
<div class="fwpr-container">
	<header class="fwpr-orders__title">
		<?php the_title(); ?>
	</header>
	<div class="row">
		<div class="col-md-1">
			<strong>Imię i nazwisko</strong>
		</div>
		<div class="col-md-2">
			<strong>Dieta</strong>
		</div>
		<div class="col-md-2">
			<strong>Adres</strong>
		</div>
		<div class="col-md-1">
			<strong>Telefon</strong>
		</div>
		<div class="col-md-2">
			<strong>Adres e-mail</strong>
		</div>
		<div class="col-md-1">
			<strong>Typ płatności</strong>
		</div>
		<div class="col-md-1">
			<strong>Koniec diety</strong>
		</div>
		<div class="col-md-2">
			<strong>Dodatkowe informacje</strong>
		</div>
	</div>
	<?php 
	foreach ($orders as $key => $order) {
		$rows = get_field('order_products',$order->ID);
		/**
		 * Get products that have been notified already
		 * @var array
		 */

		if( $rows ) {
			foreach ($rows as $key => $row) {
				$dates = $row['dates'];
				if( $dates ) {
					usort( $dates,'fwpr_sortCartDates' );
					$lastDate = end( $dates );
					if( $lastDate['date'] == $nextDay ) {
						$post = $order;
						setup_postdata( $post );
					?>
						<div class="row">
							<div class="col-md-1">
								<?php the_field('order_user',$post->ID); ?>
							</div>
							<div class="col-md-2">
								<?php echo get_the_title( $row['product'] ).' '.$row['variant']; ?>
							</div>
							<div class="col-md-2">
								<?php the_field('order_address',$post->ID); ?>
							</div>
							<div class="col-md-1">
								<?php the_field('order_phone',$post->ID); ?>
							</div>
							<div class="col-md-2">
								<?php the_field('order_mail',$post->ID); ?>
							</div>
							<div class="col-md-1">
									<?php $payment_type = get_field('order_payment_type',$post->ID); ?>
									<?php echo fwpr_payment_label($payment_type); ?>
							</div>
							<div class="col-md-1">
								<?php echo $lastDate['date']; ?>
							</div>
							<div class="col-md-2">
								<?php the_field('order_info',$post->ID); ?>
							</div>
						</div>
					<?php
						wp_reset_postdata();
					}
				}
			}
		}
	}
	?>
</div>
<?php 
	$orders = get_posts(
		array(
			'post_type' => 'fwpr_order',
			'posts_per_page' => -1,
		)
	);
 ?>
<div class="fwpr-container">
	<header class="fwpr-orders__title">
		<?php _e('Zakończone diety','fwpr'); ?>
	</header>
	<div class="row">
		<div class="col-md-1">
			<strong>Imię i nazwisko</strong>
		</div>
		<div class="col-md-2">
			<strong>Dieta</strong>
		</div>
		<div class="col-md-2">
			<strong>Adres</strong>
		</div>
		<div class="col-md-1">
			<strong>Telefon</strong>
		</div>
		<div class="col-md-2">
			<strong>Adres e-mail</strong>
		</div>
		<div class="col-md-1">
			<strong>Typ płatności</strong>
		</div>
		<div class="col-md-1">
			<strong>Koniec diety</strong>
		</div>
		<div class="col-md-2">
			<strong>Dodatkowe informacje</strong>
		</div>
	</div>
	<?php 
	foreach ($orders as $key => $order) {
		$rows = get_field('order_products',$order->ID);
		/**
		 * Get products that have been notified already
		 * @var array
		 */

		if( $rows ) {
			foreach ($rows as $key => $row) {
				$dates = $row['dates'];
				if( $dates ) {
					usort( $dates,'fwpr_sortCartDates' );
					$lastDate = end( $dates );
					if( fwpr_getTimestamp($lastDate['date']) < $current_date ) {
						$post = $order;
						setup_postdata( $post );
					?>
						<div class="row">
							<div class="col-md-1">
								<?php the_field('order_user',$post->ID); ?>
							</div>
							<div class="col-md-2">
								<?php echo get_the_title( $row['product'] ).' '.$row['variant']; ?>
							</div>
							<div class="col-md-2">
								<?php the_field('order_address',$post->ID); ?>
							</div>
							<div class="col-md-1">
								<?php the_field('order_phone',$post->ID); ?>
							</div>
							<div class="col-md-2">
								<?php the_field('order_mail',$post->ID); ?>
							</div>
							<div class="col-md-1">
									<?php $payment_type = get_field('order_payment_type',$post->ID); ?>
									<?php echo fwpr_payment_label($payment_type); ?>
							</div>
							<div class="col-md-1">
								<?php echo $lastDate['date']; ?>
							</div>
							<div class="col-md-2">
								<?php the_field('order_info',$post->ID); ?>
							</div>
						</div>
					<?php
						wp_reset_postdata();
					}
				}
			}
		}
	}
	?>
</div>