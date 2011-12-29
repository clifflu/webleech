<?php
require('common.php');

/*
 * Presets
 */
$leech = "pcc";

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
		'exp' => '使用半形逗點分隔, 逗點前後的空白字元會被忽略',
		'mapper' => 'mapper::css',
	),
);

/*
 * Parse Request
 */
if (count($_REQUEST) >= count($form)) {
	// got user input, extract query from request
	$query = array();

	foreach($_REQUEST as $key => $value) {
		if (!isset($form[$key])) continue;

		// 將使用者輸入值蓋過預設值, 才會自動帶入表單
		$form[$key]['default'] = $value;
		
		if (isset($form[$key]['mapper']))
			$value = call_user_func($form[$key]['mapper'],$value);
		
		$query[$key] = $value;
	}

	foreach($form as $key => $value) {
		if (!isset($query[$key]) && isset($value['default'])) 
			$query[$key] = $value['default'];
	}

	$target_arr = $leech::target_from_query($query);
	
	$html = $leech::bite_arr($target_arr);
}

/*
 * Load Templates
 */
tpl("header");
tpl("sidebar");
if (@$html) {
	tpl("result");
} else {
	tpl("usage");
}
tpl("footer");

?>
