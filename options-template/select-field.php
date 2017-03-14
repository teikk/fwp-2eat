<?php
	$options = $args['options'];
	$name = "fwpr_{$args['group']}_options[{$args['name']}]";
	$id = $args['label_for'];
	$set_option = $this->options[$args['group']][$args['name']];
?>
<select name="<?php echo $name; ?>">
	<?php foreach ($options as $value => $label): ?>
		<?php $selected = ($value == $set_option) ? 'selected' : ''; ?>
		<option value="<?php echo $value; ?>" <?php echo $selected; ?>><?php echo $label; ?></option>
	<?php endforeach; ?>
</select>