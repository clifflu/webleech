<?php

/**
 * 送出適當的 request 並取回 html 內容
 * 如果函式需要預載資料, 只須實作 init() 函式, 便會在運作前自動調用
 */
abstract class leech {
	// ==============================================================
	// Overrides
	// ==============================================================

	/** @var string 檔案版本 * */
	static protected $VERSION = '111228';

	/** @var string 來源分頁資訊 */
	static protected $PAGINATION = null;

	/** @var string 抓取資料的來源頁面 */
	static protected $BASE_URI;

	/** @var array string=>string 預設送出的 POST 字串 */
	static protected $BASE_POST = array();

	/** @var array string=>string 預設送出的 GET 字串 */
	static protected $BASE_GET = array();

	// ==============================================================
	// Abstract Functions
	// ==============================================================

	/**
	 * 解析 html 結果
	 * 如果結果應包含多頁, 但本次 $target 只包含其中一頁的話, 
	 * 需定義 $meta['pages'] = array(
	 * 		'NAME' =>'分頁變數名稱',
	 * 		'METHOD' => 'GET|POST',
	 * 		'VALUE' => array(2,3,4,5),
	 * );
	 */
	protected abstract static function _extract($html);

	/**
	 * 解析 $meta['table'] 中的每個列，刪除重複值並輸出
	 */
	protected abstract static function _uniquify($meta);

	// ==============================================================
	// Public Functions
	// ==============================================================

	/**
	 * 送出 http request 並傳回結果
	 * 如果資料需要前處理, 實作 init() 方法
	 * 
	 * 可傳入任意數量的 $target 進行分析, 其中參數會蓋過預設值
	 * $target = array(
	 * 		'POST' => array(
	 * 			'varname' => 'varvalue',
	 * 		),
	 * 		'GET' => array(
	 * 			'varname' => 'varvalue',
	 * 		),
	 * );
	 */
	public static function bite() {

		if (function_exists("static::init()"))
			static::init();

		$meta_arr = array();
		foreach (func_get_args() as $target)
			$meta_arr[] = static::_digest($target);

		if (count($meta_arr) > 1)
			$meta = static::_merge($meta_arr);
		else
			$meta = $meta_arr[0];

		return static::_html($meta);
	}

	// ==============================================================
	// Workflow Functions
	// ==============================================================

	/**
	 * 處理單一 target, 呼叫 _fetch 與 _extract 以產生 $meta
	 * 若有多過一個頁面, 則針對每個頁面遞迴呼叫自身, 之後合併頁面結果
	 *
	 * @param $target array
	 * @param $recursive bool 是否進行遞迴呼叫
	 * @ return array $meta
	 */
	protected static function _digest($target, $recursive = true) {
		$html = static::_fetch($target);
		$meta = static::_extract($html);

		if ($recursive && isset($meta['pages'])) {
			$meta_arr = array($meta);

			foreach ($meta['pages']['VALUE'] as $pvalue) {
				if ($meta['pages']['METHOD'] == 'GET') {
					@$target['GET'][$meta['pages']['NAME']] = $pvalue;
				} else {
					@$target['POST'][$meta['pages']['NAME']] = $pvalue;
				}
				$meta_arr[] = static::_digest($target, false);
			}
			$meta = static::_merge($meta_arr);
		}

		return $meta;
	}

	/**
	 * 送出 http request, 取得 html
	 * @param array $target
	 * @return string html
	 */
	protected static function _fetch($target) {

		$poststr = self::requestify(static::$BASE_POST, @$target['POST']);
		$curl = curl_init(self::uri($target));

		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		if ($poststr) {
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $poststr);
		}

		$html = curl_exec($curl);
		curl_close($curl);

		return $html;
	}

	/**
	 * 將 $meta_arr[]['table'] 含有的所有資訊，整理為單一陣列 $meta
	 * 無視 $meta['table'] 以外的部份
	 * 
	 * @param $meta_arr array array of meta[]
	 * @return array meta
	 */
	protected static function _merge($meta_arr) {
		$out = array();
		foreach ($meta_arr as $meta) {
			$col = ''; // 每組資料先清除舊有 header
			foreach ($meta['table'] as $row) {
				// 只在第一行觸發
				if ($col == '') {
					if ($row && ($row == @$out[0]))
						continue;
					else
						$col = $row;
				}
				$out[] = $row;
			}
		}
		return array('table' => $out);
	}

	/**
	 * 解析 $meta 資料並顯示為 html
	 * 由於原表格可能包含 colspan / rowspan 屬性, 只能將標籤與屬性值保留在 meta 中，直接輸出
	 */
	protected static function _html($meta=null) {

		if (!$meta)
			return "";

		$str = "<table>\n";

		foreach ($meta['table'] as $row) {
			$str .= "\t<tr>\n";

			foreach ($row as $col) {
				$str .= "\t\t" . trim($col) . "\n";
			}
			$str .= "\t</tr>\n";
		}
		$str .= "</table>\n";
		return $str;
	}

	// ==============================================================
	// Utility Functions
	// ==============================================================

	/**
	 * 將陣列轉換成 post or get 格式的文字
	 * 接受任意數量的 陣列 輸入, 越右邊的變數會蓋過左邊的
	 * @param array varname => vardata
	 * @return string HTTP 格式的資料字串
	 */
	public static function requestify() {
		$srcarr = array();

		foreach (func_get_args() as $arg) {
			if (is_array($arg)) {
				$srcarr = array_merge($srcarr, $arg);
			}
		}

		$arr = array();
		foreach ($srcarr as $k => $p) {
			$arr[] = trim(urlencode($k) . "=" . urlencode($p));
		}
		return join('&', $arr);
	}

	/**
	 * 更新 DOM tree 中的 <a href> 屬性
	 * @param DOMDocument $doc
	 * @param DOMNode $node 
	 * @todo
	 */
	protected static function update_anchor(DOMDocument $doc) {
		$xpath = new DOMXPath($doc);
		$tags = $xpath->query('//a[@href]');
		
		foreach($tags as $tag) {
			print_r($tag->textContent);
		}
		
		die('update');
	}

	/**
	 * 解析 BASE_GET 與 $target 中的 GET 以產生 URI
	 * @param $target array
	 * @return string URI
	 */
	protected static function uri($target) {
		$getstr = self::requestify(static::$BASE_GET, @$target['GET']);
		return static::$BASE_URI . "?" . $getstr;
	}

	/**
	 * 傳回解析器的訊息
	 * @return string
	 */
	public static function class_info() {
		return "解析器 " . __CLASS__ . " (版本: " . static::$VERSION . ")";
	}

}
