<?php

# Copyright by Karol Guciek (http://guciek.github.com)
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, version 2 or 3.

class gallery {
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
	private static function list_files($path) {
		$r = array();
		if (!is_dir($path)) return $r;
		$h = @opendir($path);
		while (($n = readdir($h)) !== false) {
			if ($n[0] == '.') continue;
			if (is_file($path.'/'.$n)) $r[] = $n;
		}
		closedir($h);
		sort($r);
		return $r;
	}
	private static function pad($name) {
		$name = ''.$name;
		while (strlen($name) < 8) $name = '0'.$name;
		return $name;
	}
	private static function highestname_jpg($path) {
		$newname = 1;
		foreach (self::list_files($path) as $f) {
			if (($p = strpos($f, '.')) === false) continue;
			$f = substr($f, 0, $p);
			if (!ctype_digit($f)) continue;
			$n = $f+1;
			if ($n > $newname) $newname = $n;
		}
		while (is_file($path.'/'.self::pad($newname).'.jpg')) $newname++;
		return self::pad($newname).'.jpg';
	}
	private static function fixnames($path) {
		foreach (self::list_files($path) as $f) {
			if (($p = strpos($f, '.')) === false) continue;
			$f = substr($f, 0, $p);
			if (!ctype_digit($f)) continue;
			if (!is_file($path.'/'.$f.'.jpg')) continue;
			if (strlen($f) < strlen(self::pad($f))) {
				@rename($path.'/'.$f.'.jpg',
					$path.'/'.self::pad($f).'.jpg');
				if (is_file($path.'/'.$f.'-thumb.jpg')) {
					unlink($path.'/'.$f.'-thumb.jpg');
				}
			}
		}
	}
	private static function admin($page) {
		if (!self::$admin) return;

		if (!is_dir('pages')) {
			if (!mkdir('pages', 0755)) return;
		}

		if (array_key_exists('gallery_addphoto', $_FILES)) {
			$f = $_FILES['gallery_addphoto'];
			if (!array_key_exists('tmp_name', $f)) return;
			$f = $f['tmp_name'];
			if (!is_uploaded_file($f)) return;
			$size = filesize($f);
			if (($size < 10) || ($size > 120*1024)) return;
			$im = @imagecreatefromjpeg($f);
			if (!$im) return;
			$w = imagesx($im);
			if (($w < 5) || ($w > 5000)) return;
			$h = imagesy($im);
			if (($h < 5) || ($h > 5000)) return;
			if (!is_dir('pages/'.$page)) {
				if (!mkdir('pages/'.$page, 0755)) return;
			}
			self::fixnames('pages/'.$page);
			$newname = 'pages/'.$page.
				'/'.self::highestname_jpg('pages/'.$page);
			if (rename($f, $newname)) {
				chmod($newname, 0644);
			}
		}

		if (array_key_exists('gallery_delphoto', $_POST)) {
			$id = $_POST['gallery_delphoto'];
			if (strpos($id, '-') !== false) return;
			if ($id !== self::alnum($id)) return;
			if (strlen($id) < 1) return;
			if (!is_file('pages/'.$page.'/'.$id.'.jpg')) return;
			if (!unlink('pages/'.$page.'/'.$id.'.jpg')) return;
			if (is_file('pages/'.$page.'/'.$id.'-thumb.jpg')) {
				unlink('pages/'.$page.'/'.$id.'-thumb.jpg');
			}
			if (count(self::list_files('pages/'.$page)) < 1) {
				rmdir('pages/'.$page);
			}
		}

		if (array_key_exists('gallery_movetoend', $_POST)) {
			$id = $_POST['gallery_movetoend'];
			if (strpos($id, '-') !== false) return;
			if ($id !== self::alnum($id)) return;
			if (strlen($id) < 1) return;
			$oldname = 'pages/'.$page.'/'.$id.'.jpg';
			if (!is_file($oldname)) return;
			$newname = 'pages/'.$page.'/'.
				self::highestname_jpg('pages/'.$page);
			if (!@rename($oldname, $newname)) return;
			@chmod($newname, 0644);
			if (is_file('pages/'.$page.'/'.$id.'-thumb.jpg')) {
				unlink('pages/'.$page.'/'.$id.'-thumb.jpg');
			}
			self::fixnames('pages/'.$page);
		}
	}
	private static function admin_btn($label, $js_fun, $param = '') {
		return '<span style="cursor:pointer;text-decoration:underline;'.
				'font-size:11px;color:#822;font-family:serif;'.
				'font-weight:bold" onclick="gallery_admin.'.
				$js_fun.'(\''.$param.'\');">['.$label.']'.
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
	private static function insertinline($page, &$h, $code, $id) {
		$p1 = strpos($h, '<div class="article">');
		if (!$p1) return false;
		$p2 = strpos($h, '</div>', $p1+10);
		if (!$p2) return false;
		$p = strpos($h, '[[FULLPHOTO]]', $p1+10);
		if (!$p) return false;
		if ($p >= $p2) return false;
		$h = substr($h, 0, $p).'<img src="/file/gallery/'.
			$code.'/'.$page.'/'.$id.'.jpg" alt="[photo'.$id.']" />'.
			substr($h, $p+13);
		return true;
	}
	private static function display($page, $h) {
		$code = self::code();
		$r = '';
		$inline = true;
		$fns = self::list_files('pages/'.$page);
		for ($k = 0; $k < count($fns); $k++) {
			$fn = $fns[$k];
			if (strpos($fn, '-') !== false) continue;
			if (($p = (strpos($fn, '.'))) === false) continue;
			$id = substr($fn, 0, $p);
			if ($inline && self::insertinline($page, $h,
				$code, $id)) continue;
			$inline = false;
			$r .= '<li><a href="/file/gallery/'.$code.'/'.$page.'/'.
				$id.'.jpg"><img src="/file/gallery/'.$code.'/'.$page.'/'.
				$id.'-thumb.jpg" alt="[photo'.$id.']" /></a>';
			if (self::$admin) {
				$r .= '<br />'.self::admin_btn(
					'usuń zdjęcie', 'delphoto', $id);
				if ($k+1 < count($fns))
					$r .= ' '.self::admin_btn('▼',
						'moveend', $id);
			}
			$r .= '</li>';
		}
		if (self::$admin) {
			$r .= '<li>'.self::admin_btn('dodaj zdjęcie',
				'addphoto').'</li>';
		}
		if (strlen($r) >= 1) {
			return str_replace('<!--gallery-->',
				'<ul class="gallery">'.$r.'</ul>', $h);
			}
		return $h;
	}
	private static $admin = false;
	public static function html($h) {
		$page = substr($_SERVER['REQUEST_URI'], 1);
		if (($p = strpos($page, '/')) !== false)
			$page = substr($page, 0, $p);
		if ($page != self::alnum($page)) return $h;
		if (strlen($page) < 1) return $h;

		if (strpos($h, '<!--allow-admin-->') !== false) {
			self::$admin = true;
			self::admin($page);
			$h = str_replace('</body>',
				"\t".'<script type="text/javascript" '.
				'src="/file/gallery/admin.js"></script>'."\n".
				'</body>', $h);
		}

		if (is_dir('pages/'.$page)) {
			$h .= '<!--referenced:/'.$page.'-->';
			$h = str_replace('</body>',
				"\t".'<script type="text/javascript" '.
				'src="/file/gallery/gallery.js"></script>'."\n".
				'</body>', $h);
		}

		return self::display($page, $h);
	}
}
$html = gallery::html($html);
