<?php 
	$name = "fwpr_{$args['group']}_options[{$args['name']}]";
	$id = $args['label_for'];
	if( isset( $this->options[$args['group']][$args['name']] ) ) {
		$value = $this->options[$args['group']][$args['name']];
	} else {
		$value = '';
	}
	$value = htmlspecialchars($value);
	if( $args['type'] == 'number' ) {
		$min = 'min="'.$args['min'].'"';
		$max = 'max="'.$args['max'].'"';
	} else {
		$min = '';
		$max = '';
	}
 ?>
<input id="<?php echo $id; ?>" type="<?php echo $args['type']; ?>" name="<?php echo $name;?>" value="<?php echo $value; ?>" <?php echo $min; ?> <?php echo $max; ?>>