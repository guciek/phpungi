<?php

# Copyright by Karol Guciek (http://guciek.github.com)
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, version 2 or 3.

class icon {
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
		if (!($h = @fopen($path, 'rb', true))) return '';
		$data = fread($h, $s);
		fclose($h);
		return $data;
	}
	private static function thumb_size() {
		$dim = explode("x", trim(self::file_read('thumb_size')));
		if (count($dim) < 2) $dim[1] = 0;
		$w = intval($dim[0]);
		if ($w < 30) $w = 140;
		$h = intval($dim[1]);
		if ($h < 30) $h = 100;
		return array($w, $h);
	}
	private static function admin_btn($c, $a) {
		return '<span style="cursor:pointer;text-decoration:underline;'.
				'font-size:11px;color:#822;font-family:serif;'.
				'font-weight:bold" onclick="'.$a.'">['.$c.']'.
			'</span>';
	}
	private static function admin($page) {
		if (!self::$admin) return;

		if (!is_dir('icons')) {
			if (!mkdir('icons', 0755)) return;
		}

		if (array_key_exists('icon_png_base64', $_POST)) {
			$img = trim($_POST['icon_png_base64']);
			if (strlen($img) < 10) return;
			$img = base64_decode($img, true);
			if (!$img) return;
			$img = @imagecreatefromstring($img);
			if (!$img) return;
			imagejpeg($img, 'icons/'.$page.'.jpg', 70);
		}

		if (array_key_exists('icon_remove', $_POST)) {
			$f = 'icons/'.$page.'.jpg';
			if (is_file($f)) @unlink($f);
		}
	}
	private static function add_icons_subs($h) {
		$links = explode('<a href="/', $h);
		for ($i = 1; $i < count($links); $i++) {
			if (!($p = strpos($links[$i], '"'))) continue;
			$adr = substr($links[$i], 0, $p);
			if (($p = strpos($adr, '/')) !== false)
				$adr = substr($adr, 0, $p);
			if ($adr != self::alnum($adr)) continue;
			if (!is_file('icons/'.$adr.'.jpg')) continue;
			if (!($p = strpos($links[$i], '>'))) continue;
			$links[$i] = substr($links[$i], 0, $p+1).
				'<img src="/file/icon/'.$adr.'.jpg" alt="[icon]" /> '.
				substr($links[$i], $p+1);
		}
		return implode('<a href="/', $links);
	}
	private static function add_icons_page($h) {
		$h = explode('<ul class="subs">', $h);
		for ($i = 1; $i < count($h); $i++) {
			$end = strpos($h[$i], '</ul>');
			if (!$end) continue;
			$h[$i] = self::add_icons_subs(substr($h[$i], 0, $end)).
				substr($h[$i], $end);
		}
		return implode('<ul class="subs">', $h);
	}
	private static $admin = false;
	public static function html($h) {
		$h = self::add_icons_page($h);
		$page = substr($_SERVER['REQUEST_URI'], 1);
		if (($p = strpos($page, '/')) !== false)
			$page = substr($page, 0, $p);
		if ($page != self::alnum($page)) return $h;
		if (strlen($page) < 1) return $h;
		if ((strpos($h, '<!--allow-admin-->') !== false) &&
				($page != 'index')) {
			self::$admin = true;
			self::admin($page);
			$h = str_replace('</body>',
				"\t".'<script type="text/javascript" '.
				'src="/file/icon/admin.js"></script>'."\n".
				'</body>', $h);
			$size = self::thumb_size();
			$btns = self::admin_btn('zmień ikonę',
				'icon_admin('.$size[0].', '.$size[1].');');
			if (is_file('icons/'.$page.'.jpg')) {
				$btns .= ' '.self::admin_btn('usuń ikonę',
					'icon_admin_remove();');
			}
			$h = str_replace('</h1>', ' '.$btns.'</h1>', $h);
		}
		if (is_file('icons/'.$page.'.jpg')) {
			$h .= '<!--referenced:/'.$page.'-->';
		}
		return $h;
	}
}
$html = icon::html($html);
