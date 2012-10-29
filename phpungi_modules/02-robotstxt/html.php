<?php

# Copyright by Karol Guciek (http://guciek.github.com)
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, version 2 or 3.

function robotstxt($h) {
	if ($_SERVER['REQUEST_URI'] !== '/robots.txt') return $h;
	header('Content-Type: text/plain');
	return 'User-agent: *'."\n".
		'Disallow: /file/gallery/'."\n".
		'Disallow: /file/attachments/'."\n";
}

$html = robotstxt($html);
