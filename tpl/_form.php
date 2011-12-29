<form id="frm_main" method="get">
<?php foreach($form as $fi): ?>
	<label for="<?php echo $fi['code'];?>"><?php echo $fi['name'];?></label>
	<input type="text" id="<?php echo $fi['code'];?>" name="<?php echo $fi['code'];?>" value="<?php echo $fi['default'];?>" />
<?php endforeach; ?>
</form>