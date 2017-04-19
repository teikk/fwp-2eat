<h1><?php the_title(); ?></h1>
<div id="ajax-debug"></div>

<form id="fwpr-online-payment" class="fwpr-payment" action="" method="POST">
	<section class="payment">
		<header class="payment__header">
			<h3>Wybierz metodę płatności</h3>
		</header>
		<section class="payment__method-switch">
			<?php do_action( 'fwpr/payment-select' ); ?>
		</section>
	</section>
	<section class="delivery">
		<?php do_action( 'fwpr/delivery/method-select' ); ?>
	</section>
	<div>
		<label>
			<p>Imię</p>
			<input type="text" class="form-control" name="firstname" required="required">			
		</label>
	</div>
	<div>
		<label>
			<p>Nazwisko</p>
			<input type="text" class="form-control" name="lastname" required="required">
		</label>
	</div>
	<div>
		<label>
			<p>E-mail</p>
			<input type="email" class="form-control" name="email" required="required">
		</label>
	</div>
	<div>
		<label>
			<p>Telefon</p>
			<input type="tel" class="form-control" name="phone" required="required">
		</label>
	</div>
	<div>
		<label>
			<p>Miasto</p>
			<input type="text" class="form-control" name="city"  id="city" required="required">
		</label>
	</div>
	<div>
		<label>
			<p>Ulica</p>
			<input type="text" class="form-control" name="street"  id="street" required="required">
		</label>
	</div>
	<div>
		<label>
			<p>Numer budynku</p>
			<input type="text" class="form-control" name="block_number"  id="block_number" required="required">
		</label>
	</div>
	<div>
		<label>
			<p>Numer mieszkania</p>
			<input type="text" class="form-control" name="flat_number" id="flat_number">
		</label>
	</div>
	<div>
		<label>Informacje do zamówienia</label>
		<textarea class="form-control" name="info"></textarea>
	</div>
	<?php wp_nonce_field( 'fwpr_pay', '_fwpr_payment', true, true ); ?>
	<button type="submit" class="btn btn-success">ZAMÓW</button>
</form>