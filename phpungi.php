<?php

# Copyright by Karol Guciek (http://guciek.github.com)
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, version 2 or 3.

ini_set("log_errors", "On");
ini_set("display_errors", "Off");
error_reporting(E_ALL);
date_default_timezone_set("Europe/Warsaw");

class phpungi {
	public static function required_file() {
		$url = $_SERVER['REQUEST_URI'];
		if ($url == '/favicon.ico') $url = '/file/template/favicon.ico';
		if (substr($url, 0, 6) !== '/file/') return '';
		$url = substr($url, 6);
		$p = strpos($url, '/');
		if ($p === false) return '';
		self::allmodules();
		$name = substr($url, 0, $p);
		if (!array_key_exists($name, self::$fullnames)) return '';
		$d = self::$mod_path.'/'.self::$fullnames[$name];
		if (is_file($d.'/file.php')) {
			return $d;
		} else {
			return '';
		}
	}
	public static function required_html() {
		$r = array();
		foreach (self::allmodules() as $m) {
			$d = self::$mod_path.'/'.$m;
			if (is_file($d.'/html.php')) {
				$r[] = $d;
			}
		}
		return $r;
	}
	public static function mod_dir_back() {
		return '../..';
	}
	private static function allmodules() {
		$r = array();
		$h = @opendir(self::$mod_path);
		if (!$h) { return $r; }
		while (($n = readdir($h)) !== false) {
			if (strlen($n) < 4) continue;
			if (strpos($n, '.') !== false) continue;
			if ($n[2] !== '-') continue;
			if (!is_dir(self::$mod_path.'/'.$n)) continue;
			$r[] = $n;
			$full = $n;
			$n = substr($n, 3);
			if (($p = strpos($n, '-')) !== false)
				$n = substr($n, 0, $p);
			self::$fullnames[$n] = $full;
		}
		closedir($h);
		sort($r);
		return $r;
	}
	private static $fullnames = array();
	private static $mod_path = 'phpungi_modules';
}

$dir = phpungi::required_file();
if ($dir != '') {
	if (chdir($dir)) {
		session_cache_limiter('public');
		session_start();
		require_once('file.php');
	}
	exit(0);
}

session_cache_limiter('nocache');
session_start();

$html = '';
foreach (phpungi::required_html() as $dir) {
	if (chdir($dir)) {
		require_once('html.php');
		if (!chdir(phpungi::mod_dir_back())) break;
	}
}

if (strtolower($_SERVER['REQUEST_METHOD']) != 'get') {
	$redir = $_SERVER['REQUEST_URI'];
	header('HTTP/1.1 303 See Other');
	header('Location: '.$redir);
	header('Content-Type: text/html');
	print('Your request has been processed, you can now <a href="'.
		$redir.'">view this page</a>.'."\n");
	exit(0);
}

if ($html == '') {
	header('HTTP/1.1 503 Service Unavailable');
	$html = '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">'."\n".
		'<html><head>'."\n".
		'<title>503 Service Unavailable</title>'."\n".
		'</head><body>'."\n".
		'<h1>Service Unavailable</h1>'."\n".
		'<p>This server is currently undergoing maintenance.</p>'.
		'<p>Please check back soon.</p>'."\n".
		'<hr>'."\n".
		'</body></html>'."\n";
}

header('Content-Length: '.strlen($html));
print($html);
