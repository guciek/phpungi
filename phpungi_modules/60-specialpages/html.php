<?php

# Copyright by Karol Guciek (http://guciek.github.com)
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, version 2 or 3.

class specialpages {
	private static function file_read($path) {
		if (!is_file($path)) return '';
		$s = filesize($path);
		if ($s < 1) return '';
		if (!($h = @fopen($path, 'rb', true))) return '';
		$data = fread($h, $s);
		fclose($h);
		return $data;
	}
	public static function html($h) {
		$req = substr($_SERVER['REQUEST_URI'], 1);
		if (strlen($req) < 4) return $h;
		if (strpos($req, '.') !== false) return $h;
		$file = 'pages/'.str_replace('/', '-', $req).'.html';
		if (is_file($file)) {
			header('Content-Type: text/html');
			$h = self::file_read($file);
		}
		return $h;
	}
}
$html = specialpages::html($html);
