<?php 
$cart = FWPR_Cart::get_instance();
$options = FWPR_Options::get_instance()->get_options();

?>
<div id="fwpr-cart">
	<?php do_action( 'fwpr/cart/items/before',$cart ); ?>
	<?php if( !empty($cart->items) ): ?>
		<ul class="list-unstyled list-group">
		<?php foreach ($cart->items as $key => $item):?>
			<li class="list-group-item">
				<p><?php echo get_the_title($item['product']); ?></p>
				<?php if( $item['variant'] !== 'false' ): ?>
					<?php 
					$variants = get_field('fwpr_product_variants',$item['product']);
					?>
					<p class="small">Wariant: <?php echo $variants[ $item['variant'] ]['name']; ?>, posiłków: <?php echo $variants[ $item['variant'] ]['dinners']; ?></p>
					<p class="small">Cena: <?php echo $variants[ $item['variant'] ]['price']; ?> PLN</p>
				<?php else: ?>
					<?php 
					$isDiscounted = get_field('fwpr_product_discounted',$item['product']);
					$price = (!$isDiscounted) ? get_field( 'fwpr_product_price', $item['product']) : get_field( 'fwpr_product_price_discount', $item['product']);
					?>
					<p class="small">Cena: <?php echo $price; ?> PLN</p>
				<?php endif; ?>
				<form class="fwpr-remove-from-cart">
					<input type="hidden" name="cart_item" value="<?php echo $key; ?>">
					<button type="submit" class="btn btn-xs btn-danger">Usuń z koszyka</button>
				</form>
			</li>
		<?php endforeach;?>
		</ul>
		<a href="<?php echo get_permalink( $options['global']['order_page'] ); ?>" class="btn btn-success">Złóż zamówienie</a>
	<?php else: ?>
		<p><?php _e('Twój koszyk jest pusty.','fwpr'); ?></p>
	<?php endif; ?>
	<?php do_action( 'fwpr/cart/items/after',$cart ); ?>
</div>