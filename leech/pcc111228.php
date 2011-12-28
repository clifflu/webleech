<?php

/**
 * 對應政府電子採購網 的 擷取與分析
 *
 * $target 格式:
 *	array(
 *		[POST] => array(
 *			[FIELDNAME] => [CONTENT],
 *		)
 *	);
 *
 * 常用 FIELDNAME :
 *		orgName (機關名稱), 
 *		tenderName (標案名稱)
 */
class pcc111228 extends leech {

	static $BASE_URI = "http://web.pcc.gov.tw/tps/pss/tender.do";

	static $BASE_GET = array(
		"searchMode" => "common",
		"searchType" => "advance",
		"pageIndex" => 1, // 頁數
	);

	static $BASE_POST = array(
		"method" => "search",
		"searchMethod" => "true",
		"searchTarget" => "TPAM",
		"orgName" => "", // 機關名稱
		"orgId" => "", // 機關代碼
		"tenderName" => "", // 標案名稱
		"tenderId" => "", // 標案案號
		"tenderWay" => "", // 招標方式
		// 招標公告日期, update to default value in script
		"tenderStartDate" => "",
		"tenderEndDate" => "",
		// 截止投標日期
		"spdtStartDate" => "",
		"spdtEndDate" => "",
		// 開標時間
		"opdtStartDate" => "",
		"opdtEndDate" => "",
		// 標的分類 ?
		"proctrgCate" => "",
		"radProctrgCate" => "",
		"tenderRange" => "", // 採購級距
		// 預算金額, 欄位不完整
		"minBudget" => "",
		"maxBudget" => "",
		"location" => "", //履約地點
		"priorityCate" => "", //優先採購分類
		// 災區重建工程
		"isReConstruct" => "",
		"radReConstruct" => "",
	);

	public static function parse($target) {
		self::$BASE_POST['tenderStartDate'] = '100/01/01';//(date("y")+89).date("/m/d") ;
		self::$BASE_POST['tenderEndDate'] =  (date("y")+89).date("/m/d") ;

		return parent::parse($target);
	}

	protected static function _extract($html) {
		print_r($html);
	}
}

?>
