<?php
require('common.php');

$leech = "pcc";

$info = $leech::info();

$form = array(
	'tenderStartDate' => array(
		'name' => '公告時間起始值',
		'code' => 'tenderStartDate',
		'default' => (date("y") + 89) . date("/m/d"),
		'exp' => '民國年(3位)/月(2位)/日(2位)',
	),
	'tenderEndDate' => array(
		'name' => '公告時間結束值',
		'code' => 'tenderEndDate',
		'default' => (date("y") + 89) . date("/m/d"),
		'exp' => '民國年(3位)/月(2位)/日(2位)',
	),
	'tenderName' => array(
		'name' => '標案名稱',
		'code' => 'tenderName',
		'default' => '',
		'exp' => '民國年(3位)/月(2位)/日(2位)',
		'mapper' => 'mapper::css',
	),
);
print_r($form);die();
/*
 * Load Templates
 */
tpl("header");
tpl("_form");
//require (TPLPATH."_form.php");
//tpl("index");
tpl("footer");

?>
