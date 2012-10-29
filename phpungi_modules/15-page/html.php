<?php

# Copyright by Karol Guciek (http://guciek.github.com)
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, version 2 or 3.

class page {
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
		return strtolower($r);
	}
	private static function safetext($t) {
		$t = trim((string)$t);
		$t = str_replace('&', '&amp;', $t);
		$t = str_replace('"', '&quot;', $t);
		$t = str_replace("'", '&#39;', $t);
		$t = str_replace("<", '&lt;', $t);
		$t = str_replace(">", '&gt;', $t);
		$t = str_replace("\\", '', $t);
		$t = str_replace("\n", '', $t);
		$t = str_replace("\r", '', $t);
		$t = str_replace("\t", '', $t);
		return $t;
	}
	private static $file_cache = array();
	private static function file_read($path) {
		if (!is_file($path)) return '';
		if (array_key_exists($path, self::$file_cache)) {
			return self::$file_cache[$path];
		}
		$s = filesize($path);
		if ($s < 1) return '';
		if (!($h = @fopen($path, 'rb', true))) {
			self::$file_cache[$path] = '';
			return '';
		}
		$data = fread($h, $s);
		fclose($h);
		self::$file_cache[$path] = $data;
		return $data;
	}
	private static function file_write($path, $data) {
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
		self::$file_cache[$path] = $data;
		return true;
	}
	private static function list_subdirs($path) {
		$r = array();
		$h = opendir($path);
		while (($n = readdir($h)) !== false) {
			if (strpos($n, '.') !== false) continue;
			if (is_dir($path.'/'.$n)) $r[] = $n;
		}
		closedir($h);
		sort($r);
		return $r;
	}
	private static function pagedelete($page) {
		if ($page == 'index') return '';
		if (!self::$admin) {
			return '';
		}
		foreach (self::list_subdirs('pages') as $c) {
			if (trim(self::file_read('pages/'.$c.'/parent')) === $page) {
				return '';
			}
		}
		return '<!--pagedelete-->';
	}
	private static function update_children($page, $ignore = '') {
		$children = array();
		if (self::file_read('pages/'.$page.'/autosort') == '') {
			foreach (explode("\n", self::file_read('pages/'.$page.'/children')) as $c) {
				$c = trim($c);
				if (trim(self::file_read('pages/'.$c.'/parent')) === $page) {
					if (array_search($c, $children) === false) {
						if ($ignore != $c) {
							$children[] = $c;
						}
					}
				}
			}
		}
		foreach (self::list_subdirs('pages') as $c) {
			if (trim(self::file_read('pages/'.$c.'/parent')) === $page) {
				if (array_search($c, $children) === false) {
					$children[] = $c;
				}
			}
		}
		if (self::file_read('pages/'.$page.'/autosort') != '') {
			sort($children);
		}
		self::file_write('pages/'.$page.'/children', implode("\n", $children));
	}
	private static function admin_btn($label, $js_fun, $subject, $def = '') {
		return '<span style="cursor:pointer;text-decoration:underline;'.
				'font-size:11px;color:#822;font-family:serif;'.
				'font-weight:bold" onclick="page_admin.'.
				$js_fun.'(\''.$subject.'\', \''.$def.'\');">'.
				'['.$label.']'.
			'</span>';
	}
	private static function title($page, $lang, $full = false) {
		$t = trim(self::file_read('pages/'.$page.'/title-'.$lang));
		if ((!$full) && ($page == 'index') && ($p = strpos($t, '-'))) {
			$t = trim(substr($t, 0, $p));
		}
		return str_replace('<!--', '', $t);
	}
	private static function linktopage($page, $lang) {
		$t = self::title($page, $lang);
		if (strlen($t) < 1) return '';
		return '<a href="/'.$page.'/'.$lang.'">'.$t.'</a>';
	}
	private static function up($page) {
		$page = trim(self::file_read('pages/'.$page.'/parent'));
		if ($page != self::alnum($page)) return '';
		if (strlen($page) < 1) return '';
		if (!is_dir('pages/'.$page)) return '';
		if (self::$admin) return $page;
		if (is_file('pages/'.$page.'/hidden')) return self::up($page);
		return $page;
	}
	private static function children($page) {
		$ret = array();
		$lines = explode("\n", self::file_read('pages/'.$page.'/children'));
		foreach ($lines as $line) {
			$line = trim($line);
			if ($line != self::alnum($line)) continue;
			if (strlen($line) < 1) continue;
			if (!is_dir('pages/'.$line)) continue;
			if (is_file('pages/'.$line.'/hidden') && (!self::$admin)) {
				continue;
			}
			$ret[] = $line;
		}
		return $ret;
	}
	private static function navpath($page, $lang) {
		$ret = '';
		while (($page = self::up($page)) != '') {
			$ret = '<li>'.self::linktopage($page, $lang).'</li>'.$ret;
			if (strlen($ret) > 1000) break;
		}
		if (strlen($ret) >= 1) return '<ul class="navpath">'.$ret.'</ul>';
		return '';
	}
	private static function subs($page, $lang) {
		$ret = '';
		$adm = '';
		$cs = self::children($page);
		for ($k = 0; $k < count($cs); $k++) {
			$c = $cs[$k];
			$l = self::linktopage($c, $lang);
			if (strlen($l) >= 1) {
				$ret .= '<li>'.$l;
				if (self::$admin && ($k+1 < count($cs)) && (self::file_read('pages/'.$page.'/autosort') == '')) {
					$ret .= ' '.self::admin_btn('▼',
						'moveend', $c.'/'.$lang);
				}
				$ret .= '</li>';
			} else if (self::$admin) {
				$adm .= '<li>'.self::admin_btn('przetłumacz '.$c,
					'newlang', $c.'/'.$lang).'</li>';
			}
		}
		if (self::$admin) {
			$adm .= '<li>'.self::admin_btn('dodaj podstronę',
				'newsub', $page.'/'.$lang).'</li>';
			$ret .= $adm;
		}
		if ($ret != '') return '<ul class="subs">'.$ret.'</ul>';
		return '';
	}
	private static function treemenu($page, $lang, $sub = '', $sub_ul = '') {
		if (strlen(self::title($page, $lang)) < 1) return $sub_ul;
		$cs = '';
		foreach (self::children($page) as $c) {
			if ($_SERVER['REQUEST_URI'] == '/'.$c.'/'.$lang) {
				$l = self::title($c, $lang);
			} else {
				$l = self::linktopage($c, $lang);
			}
			if (strlen($l) >= 1) {
				$cs .= '<li>'.$l.'</li>';
				if (($c == $sub) && ($sub_ul != ''))
					$cs .= '<li>'.$sub_ul.'</li>';
			}
		}
		if (strlen($cs) >= 1) $cs = '<ul>'.$cs.'</ul>';
		if (is_file('pages/'.$page.'/hidden')) return $cs;
		$parent = trim(self::file_read('pages/'.$page.'/parent'));
		if ($parent != self::alnum($parent)) return $cs;
		if (strlen($parent) < 1) return $cs;
		if (!is_dir('pages/'.$parent)) return $cs;
		return self::treemenu($parent, $lang, $page, $cs);
	}
	private static function admin() {
		if (!self::$admin) return;

		if (!array_key_exists('page_adminsubject', $_POST)) return;
		$req = $_POST['page_adminsubject'];
		if (!($p = strpos($req, '/'))) return;
		$page = substr($req, 0, $p);
		$lang = substr($req, $p+1);
		if ($page != self::alnum($page)) return;
		if ($lang != self::alnum($lang)) return;
		if (strlen($page) < 1) return;
		if (strlen($lang) != 2) return;

		if (array_key_exists('page_newsub', $_POST)) {
			if (strlen(self::title($page, $lang)) < 1) return;
			$name = trim(self::safetext($_POST['page_newsub']));
			if (strlen($name) < 1) return;
			$id = substr(self::alnum($name), 0, 40);
			if (strlen($id) < 1) return;
			if (file_exists('pages/'.$id) &&
				($page != 'index') &&
				(!file_exists('pages/'.$page.'_'.$id)) &&
				(substr($id, 0, strlen($page)) != $page)) {
				$id = $page.'_'.$id;
			} else {
				while (file_exists('pages/'.$id)) {
					$id .= mt_rand(0, 9);
				}
			}
			if (!mkdir('pages/'.$id, 0755)) return;
			self::file_write('pages/'.$id.'/title-'.$lang, $name);
			self::file_write('pages/'.$id.'/parent', $page);
			// (self::update_children is needed, but it is called
			// in self::html for any page anyway)
		}

		if (array_key_exists('page_newlang', $_POST)) {
			if ($page == 'index') return;
			$parent = trim(self::file_read('pages/'.$page.'/parent'));
			if (strlen(self::title($parent, $lang)) < 1) return;
			$name = trim(self::safetext($_POST['page_newlang']));
			if (strlen($name) < 1) return;
			self::file_write('pages/'.$page.'/title-'.$lang, $name);
		}

		if (array_key_exists('page_chtitle', $_POST)) {
			if (strlen(self::title($page, $lang)) < 1) return;
			$name = trim(self::safetext($_POST['page_chtitle']));
			if (strlen($name) < 1) return;
			self::file_write('pages/'.$page.'/title-'.$lang, $name);
		}

		if (array_key_exists('page_movetoend', $_POST)) {
			if ($page == 'index') return;
			$parent = trim(self::file_read('pages/'.$page.'/parent'));
			if (strlen(self::title($parent, $lang)) < 1) return;
			self::update_children($parent, $page);
		}
	}
	private static $admin = false;
	public static function html($h) {
		$req = substr($_SERVER['REQUEST_URI'], 1);
		if (!($p = strpos($req, '/'))) return $h;
		$page = substr($req, 0, $p);
		$lang = substr($req, $p+1);
		if ($page != self::alnum($page)) return $h;
		if ($lang != self::alnum($lang)) return $h;
		if (strlen($lang) != 2) return $h;
		if (!is_dir('pages')) {
			if (mkdir('pages', 0755) && mkdir('pages/index', 0755)) {
				self::file_write('pages/index/title-en', 'Main Page');
			}
		}
		if (strlen(self::title($page, $lang)) < 1) return $h;

		if (strpos($h, '<!--allow-admin-->') !== false) {
			self::$admin = true;
			self::admin($page);
			self::update_children($page);
			$h = str_replace('</h1>', ' '.self::pagedelete($page).
				'</h1>', $h);
			$h = str_replace('</h1>', ' '.
				self::admin_btn('zmień tytuł', 'chtitle',
				$page.'/'.$lang, self::title($page, $lang, true)).
				'</h1>', $h);

		} else {
			if (is_file('pages/'.$page.'/hidden')) return $h;
		}
		$h = str_replace(
			array(
				'<!--title-->',
				'<!--lang-->',
				'<!--navpath-->',
				'<!--subs-->'
			), array(
				self::title($page, $lang, true),
				$lang,
				self::navpath($page, $lang),
				self::subs($page, $lang)
			),
		$h);
		if (strpos($h, '<!--treemenu-->') !== false) {
			$h = str_replace(
				'<!--treemenu-->',
				'<div class="treemenu">'.
					self::treemenu($page, $lang).
				'</div>',
				$h
			);
		}
		if (strpos($h, '<!--subs_main-->') !== false) {
			$h = str_replace(
				'<!--subs_main-->',
				self::subs('index', $lang),
				$h
			);
		}
		if (self::$admin) {
			$h = str_replace('</body>',
				"\t".'<script type="text/javascript" '.
				'src="/file/page/admin.js"></script>'."\n".
				'</body>', $h);
		}
		return $h;
	}
}
$html = page::html($html);
