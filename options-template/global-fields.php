<?php 
	$name = "fwpr_{$args['group']}_options[{$args['name']}]";
	$id = $args['label_for'];
	$value = $this->options[$args['group']][$args['name']];
	$value = htmlspecialchars($value);
 ?>
<input id="<?php echo $id; ?>" type="<?php echo $args['type']; ?>" name="<?php echo $name;?>" value="<?php echo $value; ?>">