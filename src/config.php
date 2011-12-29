<?php

define('WEBPATH', realpath( __DIR__).'/');
define('BASEPATH', realpath(dirname(WEBPATH)).'/');
define('INCPATH', realpath(BASEPATH.'inc').'/');

require_once(INCPATH.'leech.php');
require_once(INCPATH.'leech/pcc.php');
