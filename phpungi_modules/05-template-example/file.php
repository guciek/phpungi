<?php

# Copyright by Karol Guciek (http://guciek.github.com)
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, version 2 or 3.

function file_read($path) {
	if (!is_file($path)) return '';
	$s = filesize($path);
	if ($s < 1) return '';
	if (!($h = @fopen($path, 'rb', true))) return '';
	$data = fread($h, $s);
	fclose($h);
	return $data;
}

function if_modified_since($modtime) {
	header('Last-Modified: '.gmdate('D, d M Y H:i:s \G\M\T', $modtime));
	if (!array_key_exists("HTTP_IF_MODIFIED_SINCE", $_SERVER)) return;
	$d = $_SERVER["HTTP_IF_MODIFIED_SINCE"];
	if (($p = strpos($d, ';')) !== false) $d = substr($d, $p);
	$iftime = strtotime($d);
	if ($iftime < $modtime) return;
	header('HTTP/1.1 304 Not Modified');
	exit(0);
}

$req = $_SERVER['REQUEST_URI'];
$p = strrpos($req, '/');
if (($p !== false)
		&& (strlen($req = substr($req, $p+1)) >= 6)
		&& is_file('file/'.$req)) {
	$mimes = array(
		'jpg' => 'image/jpeg',
		'png' => 'image/png',
		'css' => 'text/css',
		'js' => 'application/x-javascript',
		'swf' => 'application/x-shockwave-flash',
		'ico' => 'image/x-icon',
		'appcache' => 'text/cache-manifest'
	);
	$ext = substr($req, strpos($req, '.')+1);
	if (array_key_exists($ext, $mimes)) {
		header('Content-Type: '.$mimes[$ext]);
		if_modified_since(filemtime('file/'.$req));
		print(file_read('file/'.$req));
	}
} else {
	header('HTTP/1.1 404 Not Found');
}
