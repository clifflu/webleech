<?php

define('WEBPATH', realpath( __DIR__).'/');
define('BASEPATH', realpath(dirname(WEBPATH)).'/');
define('INCPATH', realpath(BASEPATH.'inc').'/');
define('TPLPATH', realpath(BASEPATH.'tpl').'/');

require_once(INCPATH.'leech.php');
require_once(INCPATH.'leech/pcc.php');

require_once(INCPATH.'mapper.php');

/**
 * 載入 template
 * @param string $tpl_name 
 */
function tpl($tpl_name) {
	require(TPLPATH.$tpl_name.'.php');
}