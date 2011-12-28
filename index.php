<?php

require_once('config.php');
require_once('leech.php');
require_once('leech/pcc111228.php');

echo pcc111228::bite(
	array(
		'POST' => array(
			'tenderStartDate' => '100/12/28',
			'tenderEndDate' => '100/12/28',
			'tenderName'=>'中文',
		),
	),
	array(
		'POST' => array(
			'tenderStartDate' => '100/12/28',
			'tenderEndDate' => '100/12/28',
			'tenderName' => '地理',
		),
	)
);
?>
