<?php

# Copyright by Karol Guciek (http://guciek.github.com)
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, version 2 or 3.

function page404() {
	header('HTTP/1.1 404 Not Found');
	return '<!DOCTYPE html>'."\n".
		'<html xmlns="http://www.w3.org/1999/xhtml" lang="en">'."\n".
		'<head>'."\n".
		'<title>404 Not Found</title>'."\n".
		'</head><body>'."\n".
		'<h1>Not Found</h1>'."\n".
		'<p>Requested URL "'.$_SERVER['REQUEST_URI'].
			'" could not be found on this server.</p>'.
		'<p>Please go to <a href="/">main page of this site</a>.</p>'."\n".
		'<hr />'."\n".
		'</body></html>'."\n";
}
if (strpos($html, '<!--title-->') !== false) {
	$html = page404();
}
