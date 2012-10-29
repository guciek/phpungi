<?php

# Copyright by Karol Guciek (http://guciek.github.com)
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, version 2 or 3.

class pagedelete {
	private static function file_read($path) {
		if (!is_file($path)) return '';
		$s = filesize($path);
		if ($s < 1) return '';
		if (!($h = @fopen($path, 'rb', true))) return '';
		$data = fread($h, $s);
		fclose($h);
		return $data;
	}
	private static function list_dir($path) {
		$r = array();
		$h = opendir($path);
		while (($n = readdir($h)) !== false) {
			if (strpos($n, '.') !== false) continue;
			$r[] = $n;
		}
		closedir($h);
		return $r;
	}
	private static function page_id() {
		$req = substr($_SERVER['REQUEST_URI'], 1);
		$p = strpos($req, '/');
		if (!$p) return '';
		return substr($req, 0, $p);

	}
	private static function anyother($dir, $lang) {
		$fs = self::list_dir($dir);
		$anyother = false;
		foreach ($fs as $f) {
			if (substr($f, 0, 6) != 'title-') continue;
			if ($f == 'title-'.$lang) continue;
			$anyother = true;
		}
		return $anyother;
	}
	private static function delete_form($page, $lang, $del_dir) {
		if (!array_key_exists('page_delete', $_POST)) return;
		$cmd = $_POST['page_delete'];
		if (($cmd[0] !== 'D') && ($cmd[0] !== 'U')) return;
		$parent = trim(self::file_read(self::$modpage_pages.'/'.$page.'/parent'));
		if (strlen($parent) < 1) return;
		if ($del_dir) {
			foreach (self::list_dir(self::$modpage_pages.'/'.$page) as $f) {
				unlink(self::$modpage_pages.'/'.$page.'/'.$f);
			}
			rmdir(self::$modpage_pages.'/'.$page);
		} else {
			unlink(self::$modpage_pages.'/'.$page.'/title-'.$lang);
		}
		header('HTTP/1.1 302 Found');
		$redir = '/'.$parent.'/'.$lang;
		header('Location: '.$redir);
		header('Content-Type: text/html');
		print('<a href="'.$redir.'">'.$redir.'</a>');
		exit(0);
	}
	private static function get_param($h, $a, $b) {
		$p = strpos($h, $a);
		if ($p === false) return '';
		$h = substr($h, $p+strlen($a));
		$p = strpos($h, $b);
		if ($p === false) return '';
		return trim(substr($h, 0, $p));
	}
	private static $modpage_pages = '../15-page/pages';
	public static function html($h) {
		if (strpos($h, '<!--pagedelete-->') === false) return $h;
		if (strpos($h, '<!--allow-admin-->') === false) return $h;

		if (!is_dir(self::$modpage_pages)) return;
		$page = self::page_id();
		if (strlen($page) < 1) return;
		if (!is_dir(self::$modpage_pages.'/'.$page)) return;
		if ($page == 'index') return;

		$lang = trim(self::get_param($h, 'lang="', '"'));
		if (strlen($lang) != 2) return;

		$del_dir = !self::anyother(self::$modpage_pages.'/'.$page, $lang);
		$deleted_path = '/'.$page.($del_dir ? '' : '/'.$lang);
		if (strpos($h, '<!--referenced:'.$deleted_path) !== false) {
			return $h;
		}

		self::delete_form($page, $lang, $del_dir);
		$h = str_replace('<!--pagedelete-->',
			'<span style="cursor:pointer;text-decoration:underline;'.
			'font-size:11px;color:#822;font-family:serif;font-weight:bold" '.
			'onclick="page_admin.del();">[usuń tę stronę]</span>', $h);
		return $h;
	}
}

$html = pagedelete::html($html);
