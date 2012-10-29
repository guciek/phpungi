<?php

# Copyright by Karol Guciek (http://guciek.github.com)
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, version 2 or 3.

function template($h) {
	$h = str_replace('</head>',
		'	<link rel="stylesheet" href="/file/template/site.css" />'."\n"
		.'</head>', $h);
	$h = str_replace('</body>',
		'	<div class="page">'.
				'<div class="rightcolumn">'.
					'<!--treemenu-->'.
				'</div>'.
				'<div class="leftcolumn">'.
					'<!--navpath-->'.
					'<h1><!--title--></h1>'.
					'<!--article-->'.
					'<div style="clear:left"> </div>'.
					'<!--attachments-->'.
					'<!--gallery-->'.
					'<!--subs-->'.
				'</div>'.
				'<div style="clear:both"> </div>'.
			'</div>'."\n".
		'	<div class="footer">'.
				'<p>'.
					'<!--login-link-->'.
				'</p>'.
				'<p>'.
					'<a href="http://github.com/guciek/phpungi">phpungi</a>'.
				'</p>'.
			'</div>'."\n".
		'</body>', $h);
	return $h;
}
$html = template($html);
