<?php

require_once('common.php');

echo pcc::bite(
	array(
	'POST' => array(
		'tenderStartDate' => '100/12/28',
		'tenderEndDate' => '100/12/28',
		'tenderName' => '中文',
	),
	), array(
	'POST' => array(
		'tenderName' => '中文',
	),
	), array(
	'POST' => array(
		'tenderStartDate' => '100/12/28',
		'tenderEndDate' => '100/12/28',
		'tenderName' => '地理',
	),
	)
);

echo pcc::class_info();
?>
