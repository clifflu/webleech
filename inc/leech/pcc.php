<?php

include 'pcc/111229.php';

/**
 * 政府電子採購網
 */
class pcc extends pcc_111229 {
	// ==============================================================
	// Must Override
	// 衍生類別需覆蓋或實作下列參數或函式
	// ==============================================================

	/** @var string 物件名稱 */
	static protected $CLASSNAME = __CLASS__;

	static function info() {
		return array(
			'src'=>array(
				'name'=>'政府電子採購網',
				'uri'=>static::$BASE_URI,
				'comment'=>'',
			),
			'self'=>array(
				'name'=>static::$CLASSNAME,
				'version'=>static::$VERSION,
			),
		);
	}
}
?>
