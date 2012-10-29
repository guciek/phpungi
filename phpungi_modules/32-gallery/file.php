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

function thumb_size() {
	$dim = explode("x", trim(file_read('thumb_size')));
	if (count($dim) < 2) $dim[1] = 0;
	$w = intval($dim[0]);
	if ($w < 30) $w = 140;
	$h = intval($dim[1]);
	if ($h < 30) $h = 100;
	return array($w, $h);
}

function make_thumbnail($src, $dest) {
	$im = @imagecreatefromjpeg($src);
	if (!$im) return false;
	$tsize = thumb_size();
	$imsize = array(imagesx($im), imagesy($im));
	$ratio = min($tsize[0]/$imsize[0], $tsize[1]/$imsize[1]);
	if ($ratio >= 1) $ratio = 1;
	$tsize[0] = (int)round($imsize[0]*$ratio);
	$tsize[1] = (int)round($imsize[1]*$ratio);
	if (!($t = imagecreatetruecolor($tsize[0], $tsize[1]))) return false;
	if (!imagecopyresampled($t, $im, 0, 0, 0, 0, $tsize[0], $tsize[1],
		$imsize[0], $imsize[1])) return false;
	if (!imagejpeg($t, $dest, 70)) return false;
	chmod($dest, 0644);
	return true;
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

$code = '';
if (array_key_exists('filecode', $_SESSION)) $code = $_SESSION['filecode'];
$req = $_SERVER['REQUEST_URI'];

if (($req == '/file/gallery/gallery.js') || ($req == '/file/gallery/admin.js')) {
	response_file(substr($req, strrpos($req, '/')+1),
		'application/x-javascript');
}

if ((substr($req, 0, 19) == '/file/gallery/'.$code.'/') &&
		(strlen($req = substr($req, 19)) >= 6) &&
		(substr($req, strlen($req)-4) == '.jpg') &&
		(strpos($req = substr($req, 0, strlen($req)-4), '.') === false) &&
		(($thumb = (substr($req, strlen($req)-6) == '-thumb')) || true) &&
		is_file($file = 'pages/'.($thumb ? substr($req,
			0, strlen($req)-6) : $req).'.jpg')) {
	$thumb_file = substr($file, 0, strlen($file)-4).'-thumb.jpg';
	if (!is_file($thumb_file)) {
		make_thumbnail($file, $thumb_file);
	}
	response_file($thumb ? $thumb_file : $file, 'image/jpeg');
}

header('HTTP/1.1 404 Not Found');
