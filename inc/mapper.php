<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of mapper
 *
 * @author clifflu
 */
class mapper {
	/**
	 * Comma separated string
	 * @param string $string
	 * @return array
	 */
	public static function css($string) {
		$arr = explode(",", $string);
		$out = array();
		
		foreach($arr as $tmp) {
			$tmp = trim($tmp);
			if ($tmp)$out[] = $tmp;
		}
		if (count($out)==0) return '';
		if (count($out)==1) return $out[0];
		return $out;
	}
}
?>
