<header><?php echo esc_html(get_admin_page_title()); ?></header>
<div class="wrap">
	<h1><?php esc_html(get_admin_page_title()); ?></h1>
	<form action="options.php" method="post">
	<?php
	// output security fields for the registered setting "wporg"
	settings_fields('fwpr_settings');
	// output setting sections and their fields
	// (sections are registered for "wporg", each field is registered to a specific section)
	do_settings_sections('fwpr_settings');

	//fwpr_admin_scripts();
	/**
	$exceptions = $this->options['opening_exceptions'];
	if( is_array($exceptions) ) {
		$count_exceptions = sizeof($exceptions);
	} else {
		$count_exceptions = 0;
	}
	$has_exceptions = !empty($exceptions);
	$week_days = array(
		'Niedziela','Poniedziałek','Wtorek','Środa','Czwartek','Piatek','Sobota'
		);
	?>
	<h2>Godziny otwarcia</h2>
	<div class="opening-times">
		<?php foreach ($week_days as $key => $day):?>
			<?php 
				$opening_time = ( !empty($this->options['global']['opening_time'][$key]) ) ? $this->options['global']['opening_time'][$key] : '';
				$closing_time = ( !empty($this->options['global']['closing_time'][$key]) ) ? $this->options['global']['closing_time'][$key] : '';
			 ?>
			<div class="opening-time">
				<p><?php echo $day; ?></p>
				<input id="opening-time" type="text" name="fwpr_global_options[opening_time][<?php echo $key; ?>]" data-value="<?php echo $opening_time; ?>" data-type="time">
				<input id="closing-time" type="text" name="fwpr_global_options[closing_time][<?php echo $key; ?>]" data-value="<?php echo $closing_time; ?>" data-type="time">
			</div>
		<?php endforeach;?>
		<h3>Wyjatki</h3>
		<div class="opening opening--clonable" data-exceptions="<?php echo $count_exceptions; ?>">
			<label>
				Dzień <input type="text" data-name="fwpr_opening_exceptions[#exception#][date]" data-type="date">
			</label>
			<label>
				Od <input type="text" data-name="fwpr_opening_exceptions[#exception#][opened]" data-type="time">
			</label>
			<label>
				Do <input type="text" data-name="fwpr_opening_exceptions[#exception#][closed]" data-type="time">
			</label>
		</div>
		<?php if( $has_exceptions ): foreach ($exceptions as $key => $exception):?>
				<div class="opening">
					<label>
						Dzień <input type="text" name="fwpr_opening_exceptions[<?php echo $key; ?>][date]" data-value="<?php echo $exception['date']; ?>" data-type="date">
					</label>
					<label>
						Od <input type="text" name="fwpr_opening_exceptions[<?php echo $key; ?>][opened]" data-value="<?php echo $exception['opened']; ?>" data-type="time">
					</label>
					<label>
						Do <input type="text" name="fwpr_opening_exceptions[<?php echo $key; ?>][closed]" data-value="<?php echo $exception['closed']; ?>" data-type="time">
					</label>
				</div>
		<?php endforeach;endif; ?>
	</div>
	<p class="submit">
		<button type="button" class="fwpr-copy button-secondary">DODAJ Wyjatki</button>
	</p>
	*/ ?>
	<?php 
		// output save settings button
		submit_button( __('Zapisz zmiany','fwpr') );
	 ?>
	</form>
	<style type="text/css">
		header {
			background: #f2a000;
			margin-left: -20px;
			font-size: 36px;
			color:#fff;
			padding:10px 15px;
			line-height: 1.5;
		}
		.opening--clonable {
			display:none;
		}
	</style>
</div>