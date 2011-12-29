<?php
global $leech;
$info = $leech::info();
?>
<div class="fense">
	<h2>Leech Info:</h2>
	<?php echo $leech::class_info();?><br/>
	資料來源：<a href="<?php echo $info['src']['manip'];?>"><?php echo $info['src']['name'];?></a>
</div>
