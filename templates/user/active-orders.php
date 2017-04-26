<?php 
	global $post;
	$current_date = current_time( 'd/m/Y' );	
	$nextDay = fwpr_setupReminderDate($current_date);
 ?>
<div class="fwpr-order-products">
	<h2 class="fwpr-order-products__title"><?php _e('Twoje aktywne diety','fwpr'); ?></h2>
	<div class="fwpr-order-products__wrap">
		<?php
		$rows = get_field('order_products',$post->ID);
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
					if( fwpr_getTimestamp($lastDate['date']) >= fwpr_getTimestamp($nextDay) ) {
					?>
						<div class="fwpr-order-products__item">
							<div class="fwpr-order-products__info">
								<span class="fwpr-order-products__label">
									<?php _e('Adres dostawy:','fwpr'); ?>
								</span>
								<?php the_field('order_address',$post->ID); ?>
							</div>
							<div class="fwpr-order-products__info">
								<span class="fwpr-order-products__label">
									<?php _e('Telefon:','fwpr'); ?>
								</span>
								<?php the_field('order_phone',$post->ID); ?>
							</div>
							<div class="fwpr-order-products__info">
								<span class="fwpr-order-products__label">
									<?php _e('Email:','fwpr'); ?>
								</span>
								<?php the_field('order_mail',$post->ID); ?>
							</div>
							<div class="fwpr-order-products__info">
								<?php $payment_type = get_field('order_payment_type',$post->ID); ?>
								<span class="fwpr-order-products__label">
									<?php _e('Sposób płatności:','fwpr'); ?>
								</span>
								<?php echo fwpr_payment_label($payment_type); ?>
							</div>
							<div class="fwpr-order-products__divider"></div>
							<div class="fwpr-order-products__info">
									<?php echo get_the_title( $row['product'] ).' '.$row['variant']; ?>
							</div>
							<div class="fwpr-order-products__info">
								<span class="fwpr-order-products__label">
									<?php _e('Data zakończenia:','fwpr'); ?>
								</span>
								<?php echo $lastDate['date']; ?>
							</div>
							<div class="fwpr-order-products__info">
								<?php the_field('order_info',$post->ID); ?>
							</div>
						</div>
					<?php
					}
				}
			}
		} else {
			_e('Nie masz aktywnych diet','fwpr');
		}
		?>
	</div>
</div>