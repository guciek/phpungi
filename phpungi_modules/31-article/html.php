<?php

# Copyright by Karol Guciek (http://guciek.github.com)
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, version 2 or 3.

class article {
	private static $written_files = array();
	private static function file_read($path) {
		if (!is_file($path)) return '';
		if (array_key_exists($path, self::$written_files)) {
			return self::$written_files[$path];
		}
		$s = filesize($path);
		if ($s < 1) return '';
		if (!($h = @fopen($path, 'rb', true))) return '';
		$data = fread($h, $s);
		fclose($h);
		return $data;
	}
	private static function file_write($path, $data) {
		if ($data == '') {
			if (!is_file($path)) return false;
			if (!unlink($path)) return false;
			self::$written_files[$path] = $data;
			return true;
		}
		if (!($h = fopen($path, 'wb'))) {
			error_log("fopen($path, 'wb') returned $h");
			return false;
		}
		$l = fwrite($h, $data);
		fclose($h);
		if ($l !== strlen($data)) {
			error_log("fwrite($path) only wrote $l of ".strlen($data)." bytes");
			return false;
		}
		self::$written_files[$path] = $data;
		return true;
	}
	private static function admin() {
		if (!self::$admin) return;

		if (!is_dir('articles')) {
			if (!mkdir('articles', 0755)) return;
		}
		if (array_key_exists('article_save', $_POST)) {
			$art = trim($_POST['article_save']);
			self::file_write(self::$filename, $art);
		}
	}
	private static function get_param($h, $a, $b) {
		$p = strpos($h, $a);
		if ($p === false) return '';
		$h = substr($h, $p+strlen($a));
		$p = strpos($h, $b);
		if ($p === false) return '';
		return trim(substr($h, 0, $p));
	}
	private static $admin = false;
	private static $filename = '';
	public static function html($h) {
		$req = substr($_SERVER['REQUEST_URI'], 1);
		if (strpos($req, '.') !== false) return $h;
		if (strlen($req) < 4) return $h;
		self::$filename = 'articles/'.str_replace('/', '-', $req);

		if (strpos($h, '<!--allow-admin-->') !== false) {
			self::$admin = true;
			self::admin();
		}

		$text = '';
		$art = '';
		if (is_file(self::$filename)) {
			$text = str_replace('<!--', '',
				self::file_read(self::$filename));
			$text = str_replace(array("'","\r","\n","\\"),
				array('','','',''), $text);
			$text = trim($text);
			$art = '<div class="article">'.$text.'</div>';
			$h .= '<!--referenced:/'.$req.'-->';
		}

		if (self::$admin) {
			$art = '<p style="cursor:pointer;text-decoration:underline;'.
				'font-size:11px;color:#822;font-family:serif;'.
				'font-weight:bold;width:100px'.($art==''?'':
				';margin:0 0 10px 10px;float:right').'" onclick="'.
				'article_edit()">[zmień tekst]</p>'.$art;
			$js = str_replace(array("<", "&"),
				array("\\x3c", "\\x26"), $text);
			$h = str_replace('</body>',
				"\t".'<script type="text/javascript" '.
				'src="/file/article/admin.js"></script>'."\n".
				"\t".'<script type="text/javascript">'."\n".
					"\t\t".'function article_edit()'.
					'{article_admin.edit(\''.$js.'\');}'."\n".
				"\t".'</script>'."\n".
				'</body>', $h);
		}
		$h = str_replace('<!--article-->', $art, $h);
		return $h;
	}
}
$html = article::html($html);
