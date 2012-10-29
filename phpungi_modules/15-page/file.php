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

function response_file($file, $mimetype) {
	if (is_file($file)) {
		header('Content-Type: '.$mimetype);
		header('Content-Length: '.filesize($file));
		if_modified_since(filemtime($file));
		print(file_read($file));
		exit(0);
	}
}

response_file('admin.js', 'application/x-javascript');
