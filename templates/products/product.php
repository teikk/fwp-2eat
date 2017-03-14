<?php 
	$product_type = get_field( 'fwpr_product_type' );
 ?>
<div class="fwpr-product">
	<h1 class="fwpr-product__title"><?php the_title(); ?></h1>
	<div class="fwpr-product__description"><?php the_content(); ?></div>
	<form class="fwpr-product__cart fwpr-add-to-cart" method="POST">
		<?php if( $product_type == 'abonament' ): ?>
			<?php 
				$variants = get_field( 'fwpr_product_variants' );
			 ?>
			 <?php if( !empty($variants) ): ?>	
			 	<select name="variant">
				 <?php foreach ($variants as $key => $variant):?>
				 	<option value="<?php echo $key; ?>"><?php echo $variant['name']; ?> - <?php echo $variant['price']; ?> PLN, Posiłków: <?php echo $variant['dinners']; ?> </option>
				 <?php endforeach;?>
				 </select>
			<?php endif; ?>
		<?php else: ?>
			<span class="fwpr-product__price">
				<?php the_field( 'fwpr_product_price' ); ?> PLN
			</span>
			<input type="hidden" name="variant" value="false">
		<?php endif; ?>
		<input type="hidden" name="product_id" value="<?php the_ID(); ?>">
		<button type="submit" class="btn btn-primary">Do koszyka</button>
	</form>
</div>