<select name="fwpr_global_options[<?php echo $args['name'] ?>]">
	<?php 
	$pages = get_pages();
	foreach ($pages as $key => $page){
		$selected = ($this->options['global'][$args['name']] == $page->ID ) ? 'selected' : '';
		echo "<option value='{$page->ID}' {$selected}>{$page->post_title}</option>";
	}
	?>
</select>