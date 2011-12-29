<?php

require_once('config.php');

echo pcc::bite(
	array(
		'POST' => array(
			'tenderName'=>'中文',
		),
	),
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

echo pcc::class_info();
?>
