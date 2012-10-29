<?php

# Copyright by Karol Guciek (http://guciek.github.com)
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, version 2 or 3.

class login {
	private static function alnum($t) {
		$t = str_replace(
			array('-','ą','ć','ę','ł','ń','ó','ś','ż','ź',
				' ','Ą','Ć','Ę','Ł','Ń','Ó','Ś','Ż','Ź'),
			array('_','a','c','e','l','n','o','s','z','z',
				'_','A','C','E','L','N','O','S','Z','Z'),
			(string)$t
		);
		$r = '';
		foreach (str_split((string)$t) as $c) {
			if ($c == '_') {
				if (($r != '') && ($r[strlen($r)-1] != '_'))
					$r .= '_';
				continue;
			}
			if (ctype_alnum($c)) $r .= $c;
		}
		return $r;
	}
	private static function file_read($path) {
		if (!is_file($path)) return '';
		$s = filesize($path);
		if ($s < 1) return '';
		if (!($h = @fopen($path, 'rb', true))) return '';
		$data = fread($h, $s);
		fclose($h);
		return $data;
	}
	private static function try_login($u, $p) {
		$u = strtolower(self::alnum($u));
		$p = self::alnum($p);
		if (strpos($u, '.') !== false) return false;
		if (strpos($p, '.') !== false) return false;
		if (strpos($u, '/') !== false) return false;
		if (strpos($p, '/') !== false) return false;
		if (!self::wait_lock()) {
			return;
		}
		$ok = trim(self::file_read('admins/'.$u));
		if ((strlen($ok) >= 3) && ($ok === $p)) {
			$_SESSION['user_logged'] = 'verified-admin';
		}
	}
	private static function logout() {
		if (array_key_exists('user_logged', $_SESSION))
			unset($_SESSION['user_logged']);
	}
	private static function wait_lock() {
		if (!($f = fopen("lock", "w")))
			return false;
		if (!flock($f, LOCK_EX))
			{ fclose($f); return false; }
		sleep(1);
		fclose($f);
		return true;
	}
	private static function form() {
		if (array_key_exists('edit_mode', $_POST)) {
			if ($_POST['edit_mode']) {
				$_SESSION['edit_mode'] = 1;
			} else {
				unset($_SESSION['edit_mode']);
			}
			return;
		}

		if (!array_key_exists('login_user', $_POST)) return;
		if (!array_key_exists('login_pass', $_POST)) return;
		self::try_login(trim($_POST['login_user']),
			trim($_POST['login_pass']));
	}
	public static function html($h) {
		self::form();
		if (array_key_exists('user_logged', $_SESSION) &&
				($_SESSION['user_logged'] === 'verified-admin')) {
			$editaction = 1;
			if (array_key_exists('edit_mode', $_SESSION)) {
				$h .= '<!--allow-admin-->';
				$editaction = 0;
			}
			$h = str_replace('<!--login-link-->', '<a href="#" onclick="'.
				'document.getElementById(\'edit_mode\').submit();'.
				'return false;">'.($editaction?'edit':'view').'</a>', $h);
			$h = str_replace('</body>',
				"\t".'<form id="edit_mode" method="post" '.
				'action="'.$_SERVER['REQUEST_URI'].'"><input '.
				'type="hidden" name="edit_mode" value="'.
				$editaction.'" /></form>'."\n".
				'</body>', $h);
		} else {
			$h = str_replace('<!--login-link-->', '<a href="#" onclick="'.
				'login_form();return false;">login</a>', $h);
			$h = str_replace('</body>',
				"\t".'<script type="text/javascript" '.
				'src="/file/login/form.js"></script>'."\n".
				'</body>', $h);
		}
		return $h;
	}
}
$html = login::html($html);
