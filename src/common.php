<?php

define('WEBPATH', realpath( __DIR__).'/');
define('BASEPATH', realpath(dirname(WEBPATH)).'/');
define('INCPATH', realpath(BASEPATH.'inc').'/');
define('TPLPATH', realpath(BASEPATH.'tpl').'/');

require_once(INCPATH.'leech.php');
require_once(INCPATH.'leech/pcc.php');

function tpl($tpl_name) {
	require(TPLPATH.$tpl_name.'.php');
}