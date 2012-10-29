<?php

# Copyright by Karol Guciek (http://guciek.github.com)
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, version 2 or 3.

class attachments {
	private static function alnumdash($t) {
		$t = str_replace(
			array('ą','ć','ę','ł','ń','ó','ś','ż','ź',
				' ','Ą','Ć','Ę','Ł','Ń','Ó','Ś','Ż','Ź'),
			array('a','c','e','l','n','o','s','z','z',
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
			if (ctype_alnum($c) || ($c == '-')) $r .= $c;
		}
		return strtolower($r);
	}
	private static function list_files($path) {
		$r = array();
		$h = opendir($path);
		while (($n = readdir($h)) !== false) {
			if ($n[0] == '.') continue;
			if (is_file($path.'/'.$n)) $r[] = $n;
		}
		closedir($h);
		sort($r);
		return $r;
	}
	private static function admin($page) {
		if (!self::$admin) return;

		if (!is_dir('pages')) {
			if (!mkdir('pages', 0755)) return;
		}

		if (array_key_exists('attachments_add', $_FILES)) {
			$fa = $_FILES['attachments_add'];
			if (!array_key_exists('tmp_name', $fa)) return;
			$f = $fa['tmp_name'];
			if (!is_uploaded_file($f)) return;
			$size = filesize($f);
			if (($size < 1) || ($size > 1000*1024)) return;
			if (!is_dir('pages/'.$page)) {
				if (!mkdir('pages/'.$page, 0755)) return;
			}
			$name = '';
			$ext = '';
			if (array_key_exists('name', $fa)) {
				$name = $fa['name'];
				if ($p = strrpos($name, '.')) {
					$ext = substr($name, $p+1);
					$name = substr($name, 0, $p);
				}
			}
			$name = substr(self::alnumdash(trim($name)), 0, 50);
			$ext = substr(self::alnumdash(trim($ext)), 0, 8);
			if (strlen($ext) >= 1) $ext = '.'.$ext;
			while ((strlen($name) < 1) ||
				is_file('pages/'.$page.'/'.$name.$ext))
					$name .= mt_rand(0, 9);
			$newname = 'pages/'.$page.'/'.$name.$ext;
			if (rename($f, $newname)) {
				chmod($newname, 0644);
			}
		}

		if (array_key_exists('attachments_del', $_POST)) {
			$fn = $_POST['attachments_del'];
			if (strpos($fn, '/') !== false) return;
			if (strlen($fn) < 1) return;
			if ($fn[0] == '.') return;
			if (!is_file('pages/'.$page.'/'.$fn)) return;
			unlink('pages/'.$page.'/'.$fn);
			if (count(self::list_files('pages/'.$page)) < 1) {
				rmdir('pages/'.$page);
			}
		}
	}
	private static function admin_btn($label, $js_fun, $param = '') {
		return '<span style="cursor:pointer;text-decoration:underline;'.
				'font-size:11px;color:#822;font-family:serif;'.
				'font-weight:bold" onclick="attachments_admin.'.
				$js_fun.'(\''.$param.'\');">'.
				'['.$label.']'.
			'</span>';
	}
	private static function code() {
		if (array_key_exists('filecode', $_SESSION)) {
			return $_SESSION['filecode'];
		}
		$code = '';
		while (strlen($code) < 4) $code .= mt_rand(0, 9);
		$_SESSION['filecode'] = $code;
		return $code;
	}
	private static function sizeinfo($path) {
		$s = filesize($path);
		$t = 0;
		if ($s > 10*1024) { $t++; $s = round($s/1024); }
		if ($s > 10*1024) { $t++; $s = round($s/1024); }
		$ts = array('b', 'kB', 'MB');
		return $s.$ts[$t];
	}
	private static function display($page) {
		$code = self::code();
		$r = '';
		if (is_dir('pages/'.$page)) {
			foreach (self::list_files('pages/'.$page) as $fn) {
				$r .= '<li><a href="/file/attachments/'.$code.
					'/'.$page.'/'.$fn.'">'.$fn.'</a>';
				$r .= ' <span style="color:#888">('.self::sizeinfo(
					'pages/'.$page.'/'.$fn).')</span>';
				if (self::$admin) {
					$r .= ' '.self::admin_btn('usuń', 'del', $fn);
				}
				$r .= '</li>';
			}
		}
		if (self::$admin) {
			$r .= '<li>'.self::admin_btn('dodaj załącznik',
				'add').'</li>';
		}
		if (strlen($r) >= 1) return '<ul class="attachments">'.$r.'</ul>';
		return '';
	}
	private static $admin = false;
	public static function html($h) {
		$page = substr($_SERVER['REQUEST_URI'], 1);
		if (($p = strpos($page, '/')) !== false)
			$page = substr($page, 0, $p);
		if ($page != self::alnumdash($page)) return $h;
		if (strlen($page) < 1) return $h;

		if (strpos($h, '<!--allow-admin-->') !== false) {
			self::$admin = true;
			self::admin($page);
			$h = str_replace('</body>',
				"\t".'<script type="text/javascript" '.
				'src="/file/attachments/admin.js"></script>'."\n".
				'</body>', $h);
		}

		if (is_dir('pages/'.$page)) {
			$h .= '<!--referenced:/'.$page.'-->';
		}

		$h = str_replace('<!--attachments-->', self::display($page), $h);
		return $h;
	}
}
$html = attachments::html($html);
