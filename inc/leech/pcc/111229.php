<?php

/**
 * 政府電子採購網, 2011/12/29 版本
 */
abstract class pcc_111229 extends leech {
	// ==============================================================
	// Must Override
	// 衍生類別需覆蓋或實作下列參數或函式
	// ==============================================================

	/** @var string 檔案版本 */
	static protected $VERSION = '111229';

	/** @var string 來源分頁資訊 */
	static protected $PAGINATION = array(
		'METHOD' => 'GET',
		'NAME' => 'pageIndex',
	);

	/** @var string 來源網址 */
	static protected $BASE_URI = "http://web.pcc.gov.tw/tps/pss/tender.do";

	/** @var array string=>string 預設參數 - GET */
	static protected $BASE_GET = array(
		"searchMode" => "common",
		"searchType" => "advance",
		"pageIndex" => 1, // 頁數
	);

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

	/**
	 * 解析 html 結果
	 * 如果結果應包含多頁, 但本次 $target 只包含其中一頁的話, 
	 * 需在回傳值中定義 $meta['pages']
	 * 
	 * @param string $html
	 * @return array $meta
	 */
	protected static function _extract($html) {

		// 輸出參數
		$meta = array();

		$dom = new DOMDocument;
		$dom->preserveWhiteSpace = false;
		@$dom->loadHTML($html);

		$xpath = new DOMXPath($dom);
		$tags = $xpath->query('//div[@id = "print_area"]/table');
		$dom_table = $tags->item(0);

		if (!is_object($dom_table)) {
			return array('table' => array());
		}

		$table = array();
		foreach ($dom_table->childNodes as $tr) {
			if ($tr->nodeName != 'tr')
				continue;

			$row = array();

			foreach ($tr->childNodes as $td) {
				if ($td->nodeName != 'td')
					continue;

				if (trim($td->textContent) == '找不到任何資料') {
					// 本次查詢無結果, 先當作本行空白處理
					$row = null;
					break;
				}

				$doc = new DOMDocument;
				$doc->appendChild($doc->importNode($td, true));
				static::update_anchor($doc);
				$row[] = html_entity_decode($doc->saveHTML(), ENT_NOQUOTES, 'UTF-8');
			}
			if (@$row && is_array($row) && count($row) > 0)
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

	// ==============================================================
	// May Override
	// 衍生類別可以透過覆蓋這些函式來設定行為
	// ==============================================================

	/**
	 * Bootstrap function, 實作此函式以觸發自動執行事件
	 */
	public static function init() {
		// 預設值為 僅顯示當日上架標案
		static::$BASE_POST['tenderStartDate'] = (date("y") + 89) . date("/m/d");
		static::$BASE_POST['tenderEndDate'] = (date("y") + 89) . date("/m/d");
	}

	/**
	 * 自 $meta 列中產生其 primary key
	 * 建議各衍生物件覆蓋此方法
	 * 
	 * @param array $row
	 * @return mixed primary key
	 */
	protected static function pk_from_row($row) {
		if (is_array($row))
			$row = $row[2];
		$hit = preg_match('/primaryKey=(\d*)/', $row, $matches);
		if ($hit > 0) {
			return $matches[1];
		} else {
			return false;
		}
	}

}
?>
