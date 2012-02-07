<?php

include 'pcc/120207.php';

/**
 * 政府電子採購網
 */
class pcc extends pcc_120207 {
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
				'manip'=>static::$MANIP_URI,
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
