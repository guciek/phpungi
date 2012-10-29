<?php

# Copyright by Karol Guciek (http://guciek.github.com)
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, version 2 or 3.

function delcomments($h) {
	$a = explode('<!--', $h);
	for ($i = 1; $i < count($a); $i++) {
		$p = strpos($a[$i], '-->');
		if ($p === false) {
			$a[$i] = '';
		} else {
			$a[$i] = substr($a[$i], $p+3);
		}
	}
	return implode('', $a);
}
$html = delcomments($html);
