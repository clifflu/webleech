<?php

/**
 * 送出適當的 request 並取回 html 內容
 */

abstract class leech {

	/**
	 * @var string 抓取資料的來源頁面
	 */
	static protected $BASE_URI;

	/**
	 * @var array string=>string 預設送出的 POST 字串
	 */
	static protected $BASE_POST = array();

	/**
	 * @var array string=>string 預設送出的 GET 字串
	 */
	static protected $BASE_GET = array();

	/** 
	 * @var array string[] 偏好設定
	 */
	static protected $pref = array();

	public static function clear_pref() {
		self::$pref = array();
	}

	public static function set_pref($pref) {
		self::$pref = $pref;
	}

	/**
	 * 送出 http request 並傳回結果
	 * 如果資料需要前處理, 攔截此 function 並在處理後自行呼叫
	 * 
	 * @param $target mixed 若為 
	 */
	public static function parse($target) {

		// retrieve html
		$html = static::_html($target);

		return static::_extract($html);
	}

	/**
	 * 將陣列轉換成 post or get 格式的文字
	 * 接受任意數量的 陣列 輸入, 越右邊的變數會蓋過左邊的
	 */
	public static function requestify() {
		$srcarr = array();

		foreach(func_get_args() as $arg) {
			if (is_array($arg)) {
				$srcarr = array_merge($srcarr, $arg);
			}
		}
		
		$arr = array();
		foreach($srcarr as $k=>$p) {
			$arr[] = trim(urlencode($k)."=".urlencode($p));
		}
		return join('&',$arr);
	}

	/**
	 * 解析 BASE_GET 與 $target 中的 GET 以產生 URI
	 */
	protected static function _uri($target) {
		$getstr = self::requestify(static::$BASE_GET, @$target['GET']);
		return static::$BASE_URI."?".$getstr;
	}

	/**
	 * 送出 http request, 取得 html
	 */
	protected static function _html($target) {
		
		$poststr = self::requestify(static::$BASE_POST, @$target['POST']);
		$curl = curl_init(self::_uri($target));
		
		if ($poststr) {
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $poststr);
		}
		
		$html = curl_exec($curl);
		curl_close($curl);

		return $html;
	}


	/**
	 * 解析 html 結果
	 */
	protected abstract static function _extract($html); 

}

