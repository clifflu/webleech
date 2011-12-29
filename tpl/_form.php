<?php
global $form;
?>
<div class="fense">
	<h2>解析器參數</h2>
	<form id="frm_main" method="get">
<?php foreach($form as $fi): ?>
		<div class="wrapper_input">
			<label for="<?php echo $fi['code'];?>"><?php echo $fi['name'];?></label>
			<input type="text" id="<?php echo $fi['code'];?>" name="<?php echo $fi['code'];?>" value="<?php echo $fi['default'];?>" />
			<div class="exp"><?php echo $fi['exp'];?></div>
		</div>
<?php endforeach; ?>
		<input type="submit" value="送出" />
	</form>
</div>
