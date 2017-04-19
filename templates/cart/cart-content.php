<?php 
$cart = FWPR_Cart::get_instance();
$options = FWPR_Options::get_instance()->get_options();
?>
<?php do_action( 'fwpr/cart/items/before',$cart ); ?>
<?php if( !empty($cart->items) ): ?>
	<ul class="list-unstyled list-group">
	<?php foreach ($cart->items as $key => $item):?>
		<li class="list-group-item">
			<p><?php echo get_the_title($item['product']); ?></p>
			<?php if( $item['variant'] !== 'false' ): ?>
				<?php 
				$variant = $cart->getVariant( $item['product'], $item['variant'] );
				$date = $item['date'];
				$date = explode(',', $date);
				$dates = sizeof($date);
				usort($date, 'fwpr_sortCartDates');
				?>
				<p class="small">Start diety: <?php echo $date[0]; ?>, koniec diety: <?php echo end($date); ?></p>
				<p class="small">
					Wariant: <?php echo $variant['name']; ?>
					<?php if( !empty( $variant['dinners'] ) ): ?>
					, posiłków: <?php echo $variant['dinners']; ?>
					<?php endif; ?>
				</p>
				<p class="small">Cena: <?php echo $dates * $variant['price']; ?> PLN</p>
			<?php else: ?>
				<?php 
				$isDiscounted = get_field('fwpr_product_discounted',$item['product']);
				$price = (!$isDiscounted) ? get_field( 'fwpr_product_price', $item['product']) : get_field( 'fwpr_product_price_discount', $item['product']);
				?>
				<p class="small">Cena: <?php echo $price; ?> PLN</p>
			<?php endif; ?>
			<form class="fwpr-remove-from-cart">
				<input type="hidden" name="cart_item" value="<?php echo $key; ?>">
				<button type="submit" class="btn btn-xs btn-danger"><i class="fa fa-times"></i></button>
			</form>
		</li>
	<?php endforeach;?>
	</ul>
	<a href="<?php echo get_permalink( $options['global']['order_page'] ); ?>" class="btn btn-success">Złóż zamówienie</a>
<?php else: ?>
	<p><?php _e('Twój koszyk jest pusty.','fwpr'); ?></p>
<?php endif; ?>
<?php do_action( 'fwpr/cart/items/after',$cart ); ?>