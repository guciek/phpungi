
// Copyright by Karol Guciek (http://guciek.github.com)
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, version 2 or 3.

var article_admin = function() {
	var add_css = (function() {
		var done = false;
		return function() {
			if (done) { return; }
			done = true;
			var style = document.createElement('style');
			style.type = 'text/css';
			style.innerHTML = 'div.adminpopup img { max-width: 100px; max-height: 50px }';
			document.getElementsByTagName('head')[0].appendChild(style);
		};
	})();
	function clean_html(h) {
		h = h.split("\n").join("<br />");
		var r = "";
		var i = 0;
		var opened_block = "";
		var opened_inline = "";
		function close_inline() {
			if (opened_inline != "") {
				r += '</';
				for (var k = 0; k < opened_inline.length; k++) {
					var c = opened_inline.charAt(k);
					if (c == ' ') break;
					r += c;
				}
				r += '>';
				opened_inline = "";
			}
		}
		function open_inline(t) {
			close_inline();
			if (t != "") {
				r += '<'+t+'>';
				opened_inline = t;
			}
		}
		function close_block() {
			close_inline();
			if (opened_block != "") {
				r += '</'+opened_block+'>';
				opened_block = "";
			}
		}
		function open_block(t) {
			close_block();
			r += '<'+t+'>';
			opened_block = t;
		}
		var wanted_inline = "";
		var wanted_block = 'p';
		var whitespace = 0;
		while (i < h.length) {
			var c = h.charAt(i);
			i++;
			var tag = '', originaltag = '';
			if (c == '<') {
				var p = h.indexOf('>', i);
				if (p < 0) { break; }
				var tag = h.substring(i, p);
				var originaltag = tag;
				tag = tag.replace(/^\s+|\s+$/g, "");
				tag = tag.toLowerCase();
				tag = tag.split(' ')[0];
				tag = tag.split("\t")[0];
				i = p+1;
			}
			if ((tag !== '') && (tag !== 'img')) {
				if (tag == 'div') {
					tag = 'p';
				}
				if ((tag == 'h3') || (tag == 'h4') || (tag == 'h5')) {
					tag = 'h2';
				}
				if ((tag == 'p') || (tag == 'pre') ||
						(tag == 'h1') || (tag == 'h2')) {
					if (opened_block != 'pre') {
						close_block();
					}
					if ((opened_block == 'pre') && (tag == 'pre')) {
						if (!r.match(/<pre>(\s|(<br \/>))*$/)) {
							r += '<br />';
						}
					}
					wanted_block = tag;
					wanted_inline = "";
				}
				if ((tag == 'br') || (tag == 'br/')) {
					if (opened_block == 'pre') {
						if (!r.match(/<pre>(\s|(<br \/>))*$/)) {
							r += '<br />';
						}
					} else {
						close_block();
					}
				}
				if (tag.charAt(0) == '/') {
					wanted_inline = "";
				}
				if ((tag == 'b') || (tag == 'strong')) {
					wanted_inline = 'b';
				}
				if (tag == 'i') {
					wanted_inline = 'i';
				}
				if ((tag.substring(0, 4) == 'font') || (tag == 'code')) {
					wanted_inline = 'code';
				}
				if (tag == 'a') {
					var p = originaltag.indexOf('href="', 2);
					if (p >= 0) {
						p += 6;
						var p2 = originaltag.indexOf('"', p);
						if (p2 < 0) p2 = originaltag.length;
						var href = originaltag.substring(p, p2);
						if ((href.charAt(0) == '/')
							|| (href.substring(0, 7) == 'http://')
							|| (href.substring(0, 8) == 'https://')) {
							wanted_inline = 'a href="'+href+'"';
						}
					}
				}
			} else if (c == '>') {
			} else {
				if (opened_block != wanted_block) {
					r = r.replace(/(\s|(<br \/>))+$/, '');
					open_block(wanted_block);
					whitespace = 0;
				}
				if (c == '&') {
					if (h.substring(i, i+5).toLowerCase() == 'nbsp;') {
						c = ' ';
						i += 5;
					}
				} else if (c == '"') {
					c = '&quot;';
				} else if (c == "'") {
					c = '&#39;';
				} else if (c == "\\") {
					c = '&#92;';
				} else if (c == "\r") {
					c = ' ';
				} else if (c == "\b") {
					c = ' ';
				}
				if (c.match(/\s/)) {
					if (opened_block == 'pre') {
						if (!r.match(/<pre>(\s|(<br \/>))*$/)) {
							r += c;
						}
					} else {
						whitespace++;
					}
				} else if (opened_block == 'pre') {
					if (tag === '') {
						r += c;
					}
				} else {
					if (opened_block != 'p') {
						if (wanted_inline == 'b') {
							wanted_inline = "";
						}
					}
					if (opened_inline != wanted_inline) {
						if (opened_inline.match(/^a /) &&
							wanted_inline.match(/^a /)) {
							whitespace++;
						}
						close_inline();
					}
					if (whitespace > 0) {
						r += ' ';
						whitespace = 0;
					}
					if (opened_inline != wanted_inline) {
						open_inline(wanted_inline);
					}
					if (tag === '') {
						r += c;
					} else if (tag == 'img') {
						var p = originaltag.indexOf('src="data:', 2);
						if (p >= 0) {
							p += 10;
							var p2 = originaltag.indexOf('"', p);
							if (p2 >= 0) {
								var imgdata = originaltag.substring(p, p2);
								r += '<img src="data:'+imgdata+'" />';
							}
						}
					}
				}
			}
		}
		close_block();
		return r;
	}
	function editor(total_w, total_h, init_html) {
		function editable(w, h) {
			var e = document.createElement("div");
			e.style.border = "1px solid black";
			e.style.padding = "3px";
			e.style.width = (w-30)+"px";
			e.style.height = (h-8)+"px";
			e.contentEditable = "true";
			e.style.overflowX = "hidden";
			e.style.overflowY = "scroll";
			e.innerHTML = clean_html(init_html);
			return e;
		}
		function buttons(w, h, afterchange) {
			var bar = document.createElement("div");
			bar.style.width = w+"px";
			bar.style.height = h+"px";
			bar.style.overflow = "hidden";
			bar.style.lineHeight = "20px";
			function cap(text) {
				var b = document.createElement("span");
				b.textContent = text;
				b.style.fontSize = "11px";
				b.style.marginRight = "5px";
				bar.appendChild(b);
				return b;
			}
			function imgbtn(text) {
				cap(text);
				input = document.createElement("input");
				input.type = 'file';
				input.style.fontSize = "11px";
				input.style.marginRight = "5px";
				input.style.width = "150px";
				input.style.padding = "0";
				input.style.border = "0";
				input.style.background = "transparent";
				input.onchange = function() {
					var fr;
					try {
						fr = new FileReader();
					} catch (err) {
						alert("Przeglądarka nie obsługuje tej funkcji!");
						return;
					}
					if (input.files.length < 1) {
						return;
					}
					fr.onload = function(e) {
						var data = e.target.result;
						if ((data.substring(0, 14) !== 'data:image/png') &&
								(data.substring(0, 15) !== 'data:image/jpeg')) {
							return;
						}
						if (data.length > 20000) {
							alert("Plik jest zbyt duży!");
						} else {
							if (document.execCommand('inserthtml', false,
									'<img src="'+data+'" />')) {
								afterchange('');
							} else {
								afterchange('<br /><img src="'+data+'" />');
							}
						}
					};
					fr.readAsDataURL(input.files[0]);
				}
				bar.appendChild(input);
			}
			function btn(text, a, par) {
				var input;
				if (par === "[input]") {
					input = document.createElement("input");
					input.style.fontSize = "11px";
					input.style.marginRight = "5px";
					input.style.width = "150px";
					input.style.padding = "2px";
					input.style.border = "1px solid #222";
					input.style.background = "transparent";
					input.value = "http://";
					bar.appendChild(input);
				}
				var b = cap(text);
				b.style.border = "1px solid #222";
				b.style.padding = "2px 4px";
				b.style.cursor = "pointer";
				b.onmousedown = function() {
					if (par === "[input]") {
						if (document.execCommand(a, false, input.value)) {
							afterchange('');
						} else {
							afterchange('<br /><a href="'+input.value+
								'">'+input.value+'</a>');
						}
					} else {
						document.execCommand(a, false, par);
						afterchange('');
					}
				};
			}
			cap("akapit:");
			btn("zwykły", "formatBlock", "p");
			btn("kod", "formatBlock", "pre");
			btn("h1", "formatBlock", "h1");
			btn("h2", "formatBlock", "h2");
			cap(" ");
			imgbtn("wstaw obrazek:");
			bar.appendChild(document.createElement('br'));
			cap("tekst:");
			btn("zwykły", "removeFormat", false);
			btn("bold", "bold", false);
			btn("italic", "italic", false);
			btn("kod", "fontName", "code");
			cap(" ");
			cap("utwórz link:");
			btn("utwórz", "createlink", "[input]");
			return bar;
		}
		function editor(w, h) {
			var e = document.createElement("div");
			var edb = editable(w, h-55);
			e.appendChild(buttons(w-40, 50, function(add) {
				edb.innerHTML = clean_html(edb.innerHTML+add);
			}));
			e.appendChild(edb);
			e.get_html = function() {
				return clean_html(edb.innerHTML);
			};
			return e;
		}
		return editor(total_w, total_h);
	}
	function form_submit(label) {
		var e = document.createElement("input");
		e.type = "submit";
		e.style.border = "1px solid black";
		e.style.padding = "3px 8px";
		e.value = label;
		return e;
	}
	function form(get_html) {
		var e = document.createElement("form");
		e.method = "POST";
		e.action = window.location;
		var h = document.createElement("input");
		h.type = "hidden";
		e.appendChild(h);
		e.appendChild(form_submit("Zapisz"));
		e.onsubmit = function() {
			h.value = get_html();
			h.name = "article_save";
		};
		return e;
	}
	function popup_back(cancel) {
		var e = document.createElement("div");
		e.style.color = "#ccc";
		e.style.fontSize = "14px";
		e.style.cursor = "pointer";
		e.style.padding = "5px";
		e.style.border = "1px solid #888";
		e.textContent = "anuluj";
		e.onclick = cancel;
		return e;
	}
	function popup_content(w, h, init_html) {
		var div = document.createElement(div);
		div.style.border = "1px solid #888";
		div.style.background = "#eee";
		div.style.height = (h-2)+"px";

		var ed = editor(w-32, h-62, init_html);
		ed.style.margin = "10px 15px";
		div.appendChild(ed);

		var fm = form(ed.get_html);
		fm.style.margin = "0 15px 10px 15px";
		fm.style.position = "absolute";
		fm.style.top = (h-42)+"px";
		div.appendChild(fm);

		return div;
	}
	function popup(init_html) {
		var e = document.createElement("div");
		e.className = 'adminpopup';
		add_css();

		var bg = document.createElement("div");
		bg.style.position = "fixed";
		bg.style.top = "0";
		bg.style.left = "0";
		bg.style.width = (window.innerWidth+1000)+"px";
		bg.style.height = (window.innerHeight+1000)+"px";
		bg.style.zIndex = 1000;
		bg.style.background = "#000";
		try {
			bg.style.opacity = 0.8;
		} catch(e) {}
		e.appendChild(bg);

		var back = popup_back(function() {
			document.body.removeChild(e);
		});
		back.style.position = "fixed";
		back.style.left = "10px";
		back.style.top = "10px";
		back.style.zIndex = 1001;
		e.appendChild(back);

		var cw = Math.min(window.innerWidth-40, 600);
		var c = popup_content(cw, window.innerHeight-90, init_html);
		c.style.position = "fixed";
		c.style.top = "50px";
		c.style.left = Math.round((window.innerWidth-cw-20)/2)+"px";
		c.style.zIndex = 1002;
		c.style.overflow = "hidden";
		e.appendChild(c);

		document.body.appendChild(e);
	}
	return {
		edit: popup
	};
};
article_admin = article_admin();
