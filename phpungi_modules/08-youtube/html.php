<?php

# Copyright by Karol Guciek (http://guciek.github.com)
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, version 2 or 3.

function youtube($h) {
	$h = str_replace('</body>',
		"\t".'<script type="text/javascript" '.
		'src="/file/youtube/links.js"></script>'."\n".
		'</body>', $h);
	return $h;
}
$html = youtube($html);
