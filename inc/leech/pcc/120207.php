<?php

include '111229.php';

/**
 * 政府電子採購網, 2012/02/07 版本
 */
abstract class pcc_120207 extends pcc_111229 {
	// ==============================================================
	// Must Override
	// 衍生類別需覆蓋或實作下列參數或函式
	// ==============================================================

	/** @var string 檔案版本 */
	static protected $VERSION = '120207';


	/** @var array string=>string 預設參數 - POST */
	static protected $BASE_POST = array(
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
		// 勞務類,
		"radProctrgCate" => "3",
		"tenderRange" => "", // 採購級距
		// 預算金額, 濾除未輸入者
		"minBudget" => "1",
		"maxBudget" => "9999999999999",
		"location" => "", //履約地點
		"priorityCate" => "", //優先採購分類
		// 災區重建工程
		"isReConstruct" => "",
		"radReConstruct" => "",
	);

}
?>
