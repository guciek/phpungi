<?php

# Copyright by Karol Guciek (http://guciek.github.com)
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, version 2 or 3.

class alphabetical {
	private static function alnum($t) {
		$t = str_replace(
			array('-','ą','ć','ę','ł','ń','ó','ś','ż','ź',
				' ','Ą','Ć','Ę','Ł','Ń','Ó','Ś','Ż','Ź'),
			array('_','a','c','e','l','n','o','s','z','z',
				'_','A','C','E','L','N','O','S','Z','Z'),
			(string)$t
		);
		$r = '';
		foreach (str_split((string)$t) as $c) {
			if ($c == '_') {
				if (($r != '') && ($r[strlen($r)-1] != '_'))
					$r .= '_';
				continue;
			}
			if (ctype_alnum($c)) $r .= $c;
		}
		return strtolower($r);
	}
	private static function file_read($path) {
		if (!is_file($path)) return '';
		$s = filesize($path);
		if ($s < 1) return '';
		if (!($h = @fopen($path, 'rb', true))) {
			self::$file_cache[$path] = '';
			return '';
		}
		$data = fread($h, $s);
		fclose($h);
		return $data;
	}
	private static function list_subdirs($path) {
		$r = array();
		$h = opendir($path);
		while (($n = readdir($h)) !== false) {
			if (strpos($n, '.') !== false) continue;
			if (is_dir($path.'/'.$n)) $r[] = $n;
		}
		closedir($h);
		sort($r);
		return $r;
	}
	private static function title($page, $lang) {
		$t = trim(self::file_read('../15-page/pages/'.$page.'/title-'.$lang));
		if (($page == 'index') && ($p = strpos($t, '-'))) {
			$t = trim(substr($t, 0, $p));
		}
		return str_replace('<!--', '', $t);
	}
	private static function allpages() {
		$ret = array();
		foreach (self::list_subdirs('../15-page/pages') as $line) {
			$line = trim($line);
			if ($line != self::alnum($line)) continue;
			if (strlen($line) < 1) continue;
			if (!is_dir('../15-page/pages/'.$line)) continue;
			if (is_file('../15-page/pages/'.$line.'/hidden')) {
				continue;
			}
			$ret[] = $line;
		}
		return $ret;
	}
	public static function html($h) {
		$req = substr($_SERVER['REQUEST_URI'], 1);
		if (!($p = strpos($req, '/'))) return $h;
		$lang = substr($req, $p+1);
		if (substr($req, 0, $p) != 'alphabetical') return $h;
		if ($lang != self::alnum($lang)) return $h;
		if (strlen($lang) != 2) return $h;

		$index = array();
		foreach (self::allpages() as $page) {
			$t = self::title($page, $lang);
			if (strlen($t) < 1) continue;
			$l = self::alnum($t);
			if (strlen($l) < 1) continue;
			$l = strtoupper(substr($l, 0, 1));
			$index[$l.$t] = '<a href="/'.$page.'/'.$lang.'">'.$t.'</a>';
		}
		uksort($index, 'strcasecmp');
		$list = '<div class="alphabetical"><ul>';
		$letter = '';
		$i = 0;
		foreach ($index as $key => $link) {
			$l = substr($key, 0, 1);
			if ($l != $letter) {
				if (($i >= 1) && ($i >= (count($index)+2)/3)) {
					$i = 0;
					$list .= '</ul><ul>';
				}
				$letter = $l;
				$list .= '<li><b>'.$letter.'</b></li>';
			}
			$list .= '<li>'.$link.'</li>';
			$i++;
		}
		$list .= '</ul></div>';

		$h = str_replace('<!--article-->', $list, $h);
		return $h;
	}
}
$html = alphabetical::html($html);
