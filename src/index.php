<?php

require_once('config.php');
require_once('leech.php');
require_once('leech/pcc.php');

echo pcc111228::bite(
	array(
		'POST' => array(
			'tenderName'=>'中文',
		),
	),
	array(
		'POST' => array(
			'tenderName' => '地理',
		),
	)
);
?>
