<?php 
$date = current_time( 'd/m/Y' );
$orders = fwpr_sort_orders($date);
 ?>
<h1><?php the_title(); ?></h1>
<h3><?php echo $date; ?></h3>
<?php if( $orders ): ?>
	<?php foreach ($orders as $id => $order): ?>
		<h5><?php echo get_the_title($id); ?></h5>
		<?php if( $order ): ?>
			<div class="row">
				<div class="col-md-2">
					<strong>Imię i nazwisko</strong>
				</div>
				<div class="col-md-2">
					<strong>Wariant diety</strong>
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
				<div class="col-md-2">
					<strong>Dodatkowe info</strong>
				</div>
			</div>		
			<?php foreach ($order as $key => $order_data):?>
				<div class="row">
					<div class="col-md-2">
						<?php the_field('order_user',$order_data['order']); ?>
					</div>
					<div class="col-md-2">
						<?php echo $order_data['variant']; ?>
					</div>
					<div class="col-md-2">
						<?php the_field('order_address',$order_data['order']); ?>
					</div>
					<div class="col-md-1">
						<?php the_field('order_phone',$order_data['order']); ?>
					</div>
					<div class="col-md-2">
						<?php the_field('order_mail',$order_data['order']); ?>
					</div>
					<div class="col-md-1">
						<?php $payment_type = get_field('order_payment_type',$order_data['order']); ?>
						<?php echo fwpr_payment_label($payment_type); ?>
					</div>
					<div class="col-md-2">
						<?php the_field('order_info',$order_data['order']); ?>
					</div>
				</div>
			<?php endforeach; ?>			
		<?php endif; ?>
	<?php endforeach; ?>
<?php endif; ?>