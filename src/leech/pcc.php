<?php

/**
 * 對應政府電子採購網 的 擷取與分析
 *
 * $target 格式:
 * 	array(
 * 		[POST] => array(
 * 			[FIELDNAME] => [CONTENT],
 * 		)
 * 	);
 *
 * 常用 FIELDNAME :
 * 		orgName (機關名稱), 
 * 		tenderName (標案名稱)
 */
class pcc111228 extends leech {
	// ==============================================================
	// Overrides
	// ==============================================================

	/** 檔案版本 * */
	static protected $VERSION = '111228';

	/** 來源分頁資訊 */
	static protected $PAGINATION = array(
		'METHOD' => 'GET',
		'NAME' => 'pageIndex',
	);

	/** 來源網址 */
	static protected $BASE_URI = "http://web.pcc.gov.tw/tps/pss/tender.do";

	/** 預設參數 - GET */
	static protected $BASE_GET = array(
		"searchMode" => "common",
		"searchType" => "advance",
		"pageIndex" => 1, // 頁數
	);

	/** 預設參數 - POST */
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
	
	// ==============================================================
	// Bootstrap
	// ==============================================================

	public static function init() {
		// 預設值為 僅顯示當日上架標案
		self::$BASE_POST['tenderStartDate'] = (date("y") + 89) . date("/m/d");
		self::$BASE_POST['tenderEndDate'] = (date("y") + 89) . date("/m/d");
	}
	
	// ==============================================================
	// Abstract Functions
	// ==============================================================

	protected static function _uniquify($meta) {
		
	}

	protected static function _extract($html) {

		// 輸出參數
		$meta = array();

		$dom = new DOMDocument;
		$dom->preserveWhiteSpace = false;
		@$dom->loadHTML($html);

		$xpath = new DOMXPath($dom);
		$tags = $xpath->query('//div[@id = "print_area"]/table');
		$dom_table = $tags->item(0);

		$table = array();
		foreach ($dom_table->childNodes as $tr) {
			if ($tr->nodeName != 'tr')
				continue;

			$row = array();

			foreach ($tr->childNodes as $td) {
				if ($td->nodeName != 'td')
					continue;
				$doc = new DOMDocument;
				$doc->appendChild($doc->importNode($td, true));
				$row[] = html_entity_decode($doc->saveHTML(), ENT_NOQUOTES, 'UTF-8');
			}
			$table[] = $row;
		}

		// parse page info on the last row
		$pageinfo = strip_tags($table[(count($table) - 1)][0]);
		$pageinfo = str_replace(array(' ', "\t", "\r", "\n"), '', $pageinfo);
		unset($table[(count($table) - 1)]);

		$meta['table'] = $table;

		// 偷懶, 用資料筆數 / 每頁 100 則, 計算還有幾頁
		$left = "共有";
		$right = "筆資料";
		$entries = mb_substr($pageinfo, (mb_strrpos($pageinfo, $left) + mb_strlen($left)));
		$entries = mb_substr($entries, 0, mb_strrpos($entries, $right));

		if ($entries > 100) {
			$parr = range(2, ceil($entries / 100), 1);

			$pages = self::$PAGINATION;
			$pages['VALUE'] = $parr;
			
			$meta['pages'] = $pages;
		}

		return $meta;
	}

}
?>
