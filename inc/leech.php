<?php

/**
 * 送出適當的 request 並取回 html 內容
 * 如果函式需要預載資料, 須實作 init() 函式, 會在運作邏輯前自動調用
 * 呼叫衍生物件的 [obj]::bite() 或 bite_arr() 以進行分析
 * 
 * 常用資料結構定義：
 * 
 * 擷取參數 $target = array(
 * 		'POST' => array(
 * 			'varname' => 'varvalue',
 * 		),
 * 		'GET' => array(
 * 			'varname' => 'varvalue',
 * 		),
 * );
 * 
 * 網頁資料 $meta = array(
 * 		// 分頁設定, optional
 * 		'pages' => array(
 * 			'NAME' =>'分頁變數名稱',
 * 			'METHOD' => 'GET|POST',
 * 			'VALUE' => array(2,3,4,5),
 * 		),
 * 		// 實際內容
 * 		'table' => array(
 * 			[row (tr)] => array(
 * 				[col (td)],
 * 			),
 * 		),
 * );
 */
abstract class leech {
	// ==============================================================
	// Must Override
	// 衍生類別需覆蓋或實作下列參數或函式
	// ==============================================================

	/** @var string 物件名稱 */
	static protected $CLASSNAME = 'default';

	/** @var string 檔案版本 */
	static protected $VERSION = '';

	/** @var string 來源分頁資訊 */
	static protected $PAGINATION = null;

	/** @var string 來源網址 */
	static protected $BASE_URI;

	/** @var array string=>string 預設參數 - GET */
	static protected $BASE_GET = array();

	/** @var array string=>string 預設參數 - POST */
	static protected $BASE_POST = array();

	/**
	 * 解析 html 結果
	 * 如果結果應包含多頁, 但本次 $target 只包含其中一頁的話, 
	 * 需在回傳值中定義 $meta['pages']
	 * 
	 * @param string $html
	 * @return array $meta
	 */
	protected abstract static function _extract($html);

	/**
	 * 傳回解析器資訊陣列，其型態為
	 * array(
	 *		'src'=>array(
	 *			'name'=>'政府電子採購網',
	 *			'uri'=>static::$BASE_URI,
	 *			'comment'=>'',
	 *		),
	 *		'self'=>array(
	 *			'name'=>static::$CLASSNAME,
	 *			'version'=>static::$VERSION,
	 *		),
	 *	);
	 * @return array
	 */
	protected abstract static function info();

	// ==============================================================
	// May Override
	// 衍生類別可以透過覆蓋這些函式來設定行為
	// ==============================================================

	/**
	 * Bootstrap function, 實作此函式以觸發自動執行事件
	 * @todo 調整 init 機制, 讓每種物件只會被執行一次
	 */
	public static function init() {
		
	}

	/**
	 * 解析 $meta['table'] 中的每個列，刪除重複值並輸出
	 * 衍生物件若要自訂排序邏輯, 覆蓋此方法
	 * 
	 * @param array $meta
	 * @return array $meta
	 * @see self::_uniquify()
	 * @see self::pk_from_row()
	 */
	static protected function uniquify($meta) {
		// 不檢查
		// return $meta;
		// 利用 _uniquify() (調用 pk_from_row()) 檢查
		return static::_uniquify($meta);
	}

	/**
	 * 自 $meta 列中產生其 primary key
	 * 建議各衍生物件覆蓋此方法
	 * 
	 * @param array $row
	 * @return mixed primary key
	 */
	protected static function pk_from_row($row) {
		return md5(serialize($row));
	}

	// ==============================================================
	// Public Functions
	// 物件操作函式
	// ==============================================================

	/**
	 * 分析打包的任意數量 $target，並將結果擷取為一段 html 碼 (預設為表格) 回傳
	 * 如果解析器需要前處理, 實作 init() 方法
	 * 
	 * @param array $target_arr $target 形成的陣列, 其中資料會蓋過解析器的預設值
	 * @return string parsed html
	 */
	public static function bite_arr($target_arr) {
		if (function_exists("static::init()"))
			static::init();

		$meta_arr = array();
		foreach ($target_arr as $target)
			$meta_arr[] = static::_digest($target);

		if (count($meta_arr) > 1)
			$meta = static::_merge($meta_arr);
		else
			$meta = $meta_arr[0];

		return static::_html(static::uniquify($meta));
	}

	/**
	 * 分析打包的任意數量 $target，並將結果擷取為一段 html 碼 (預設為表格) 回傳
	 * 如果解析器需要前處理, 實作 init() 方法
	 * 
	 * @param array $target 其中資料會蓋過解析器的預設值
	 * @return string parsed html
	 * @see self::bite_arr()
	 */
	public static function bite() {
		return static::bite_arr(func_get_args());
	}

	// ==============================================================
	// Workflow Functions
	// 核心運作流程, 適合三秒鐘搞爛整套系統
	// ==============================================================

	/**
	 * 處理傳入的 target, 依次呼叫 _fetch 與 _extract 以產生 $meta
	 * 若 $meta 中包含分頁資訊, 則視情況遞迴呼叫，以抓取所有分頁結果並合併
	 *
	 * @param $target array
	 * @param $recursive bool 是否進行遞迴呼叫
	 * @return array $meta
	 * @see self::_fetch()
	 * @see self::_extract
	 * @see self::_merge
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

		return static::uniquify($meta);
	}

	/**
	 * 送出 http request, 取得 html
	 * @param array $target
	 * @return string html
	 * @todo 視情況改用 curl_multi
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
		$table = array();
		foreach ($meta_arr as $meta) {
			$col = ''; // 每組資料先清除舊有 header
			foreach ($meta['table'] as $row) {
				// 只在第一行觸發
				if ($col == '') {
					if ($row && ($row == @$table[0]))
						continue;
					else
						$col = $row;
				}
				$table[] = $row;
			}
		}
		return array('table' => $table);
	}

	/**
	 * 調用 static::pk_from_row() 產生每列的 primary key
	 * 並以此判斷是否重複，最終傳回不重複的表格 ($meta)
	 * 
	 * @param array $meta
	 * @return array $meta
	 * 
	 * @see self::uniquify()
	 * @see static::pk_from_row()
	 */
	protected static function _uniquify($meta) {

		$new_tbl = array();
		$key_arr = array();

		foreach ($meta['table'] as $row) {
			$pk = static::pk_from_row($row);
			if ($pk !== false) {
				if (isset($key_arr[$pk])) {
					continue;
				} else {
					$key_arr[$pk] = true;
				}
			}
			$new_tbl[] = $row;
		}
		$meta['table'] = $new_tbl;
		return $meta;
	}

	/**
	 * 解析 $meta 資料並顯示為 html
	 * 由於原表格可能包含 colspan / rowspan 屬性, 
	 * 只能將標籤 (td) 與屬性值保留在 meta 中，直接輸出
	 * 
	 * @param array $meta
	 * @return string html
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
	// 供自身與衍生類別使用的工具函式
	// ==============================================================

	/**
	 * 將陣列轉換成 post or get 格式的文字
	 * 接受任意數量的 陣列 輸入, 越右邊的變數會蓋過左邊的
	 * 
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
	 * 讀取 BASE_URI 並取出當前目錄名稱, 便於產生相對路徑
	 * @return string URI
	 */
	protected static function uri_path() {
		if (substr(static::$BASE_URI, -1) == '/')
			return static::$BASE_URI;
		return dirname(static::$BASE_URI) . '/';
	}

	/**
	 * 讀取 BASE_URI 並取出網址根目錄名稱, 便於產生決對路徑
	 * @return string URI
	 */
	protected static function uri_root() {
		$arr = parse_url(static::$BASE_URI);
		return $arr['scheme'] . '://' . $arr['host'];
	}

	/**
	 * 更新 DOM tree 中的 <a href> 屬性
	 * @param DOMDocument $doc
	 */
	protected static function update_anchor(DOMDocument $doc) {
		$xpath = new DOMXPath($doc);
		$tags = $xpath->query('a//@href');

		$uri_path = static::uri_path();
		$uri_root = static::uri_root();

		foreach ($tags as $tag) {
			$uri = $tag->textContent;

			if (preg_match("/^https?:\/\//", $uri)) {
				// complete uri, do nothing
				;
			} elseif (preg_match("/^\//", $uri)) {
				// absolute path
				$uri = $uri_root . $uri;
			} else {
				// relative path
				$uri = $uri_path . $uri;
			}

			$DN_uri = new DOMText($uri);
			$tag->removeChild($tag->firstChild);
			$tag->appendChild($DN_uri);
		}
	}

	/**
	 * 解析 BASE_GET 與 $target 中的 GET 以產生 URI
	 * 
	 * @param $target array
	 * @return string URI
	 */
	protected static function uri($target) {
		$getstr = self::requestify(static::$BASE_GET, @$target['GET']);
		return static::$BASE_URI . "?" . $getstr;
	}

	/**
	 * 傳回解析器版本訊息
	 * @return string
	 */
	public static function class_info() {
		return "解析器 " . static::$CLASSNAME . " ( 版本: " . static::$VERSION . " )";
	}

}
