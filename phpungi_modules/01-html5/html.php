<?php

# Copyright by Karol Guciek (http://guciek.github.com)
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, version 2 or 3.

function html5() {
	$accept = array_key_exists('HTTP_ACCEPT', $_SERVER) ?
		$_SERVER['HTTP_ACCEPT'] : '';
	$use_xhtml = (stripos($accept, 'xhtml') !== false);
	header('Content-Type: '.($use_xhtml ? 'application/xhtml+xml' : 'text/html'));
	$h = ($use_xhtml ? '<?xml version="1.0" encoding="utf-8" ?>' : '');
	$h .= '<!DOCTYPE html>'."\n".
		'<html xmlns="http://www.w3.org/1999/xhtml" lang="<!--lang-->">'."\n".
		'<head>'."\n".
		'	<meta charset="utf-8" />'."\n".
		'	<title><!--title--></title>'."\n".
		'</head>'."\n".
		'<body>'."\n".
		'</body>'."\n".
		'</html>'."\n";
	return $h;
}

$html = html5();
