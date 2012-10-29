
// Copyright by Karol Guciek (http://guciek.github.com)
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, version 2 or 3.

function icon_admin(icon_w, icon_h) {
	var utils = {};
	utils.set = function() {
		var es = [];
		return {
			add: function(e) {
				for (var i = 0; i < es.length; i++) {
					if (es[i] === e) { return false; }
				}
				es[es.length] = e;
				return true;
			},
			remove: function(e) {
				for (var i = 0; i < es.length; i++) {
					if (es[i] === e) {
						es.splice(i, 1);
						return true;
					}
				}
				return false;
			},
			removeall: function() {
				es = [];
			},
			contains: function(e) {
				for (var i = 0; i < es.length; i++) {
					if (es[i] === e) {
						return true;
					}
				}
				return false;
			},
			foreach: function(f) {
				for (var i = 0; i < es.length; i++) {
					f(es[i]);
				}
			}
		};
	};
	utils.event = function() {
		var callbacks = utils.set();
		return {
			add: callbacks.add,
			remove: callbacks.remove,
			removeall: callbacks.removeall,
			fire: function(info) {
				callbacks.foreach(function(callback) {
					try {
						callback(info);
					} catch(err) {}
				});
			}
		};
	};
	utils.drag = function(subject) {
		if (!subject) { subject = document; }
		var onstart = utils.event();
		var onmove = utils.event();
		var onend = utils.event();
		var is_dragging = false;
		var x = -1000;
		var y = -1000;
		function checkmoved(e, fireevent) {
			if (window.event) {
				e = window.event;
			}
			if (!e) { return false; }
			try { e.preventDefault(); } catch(err) {}
			if (e.changedTouches &&
				(e.changedTouches.length >= 1)) {
					e = e.changedTouches[0];
			}
			if (!e.pageX) { return; }
			if (!e.pageY) { return; }
			var dx = e.pageX-x;
			var dy = e.pageY-y;
			if ((dx === 0) && (dy === 0)) { return; }
			x = e.pageX;
			y = e.pageY;
			if (fireevent) {
				onmove.fire({x: x, y: y, dx: dx, dy: dy});
			}
		}
		function move(e) {
			checkmoved(e, true);
			return false;
		}
		function end(e) {
			checkmoved(e, true);
			if (is_dragging) {
				is_dragging = false;
				document.removeEventListener('mousemove', move, false);
				document.removeEventListener('mouseup', end, false);
				document.removeEventListener('touchmove', move, false);
				document.removeEventListener('touchend', end, false);
				onend.fire();
			}
			return false;
		}
		function start(e) {
			try { e.preventDefault(); } catch(err) {}
			if (!is_dragging) {
				checkmoved(e, false);
				is_dragging = true;
				document.addEventListener('mousemove', move, false);
				document.addEventListener('mouseup', end, false);
				document.addEventListener('touchmove', move, false);
				document.addEventListener('touchend', end, false);
				onstart.fire({x: x, y: y});
			}
			return false;
		}
		subject.addEventListener('mousedown', start, false);
		subject.addEventListener('touchstart', start, false);
		return {
			onstart: onstart,
			onmove: onmove,
			onend: onend,
			get_x: function() { if (!is_dragging) { return -1; } return x; },
			get_y: function() { if (!is_dragging) { return -1; } return y; },
			clean: function() {
				end();
				onstart.removeall();
				onmove.removeall();
				onend.removeall();
				subject.removeEventListener('mousedown', start, false);
				subject.removeEventListener('touchstart', start, false);
			}
		};
	};
	function popup(content, w, h) {
		function popup_back() {
			var e = document.createElement("div");
			e.style.position = "absolute";
			e.style.left = "10px";
			e.style.top = "10px";
			e.style.color = "#ccc";
			e.style.fontSize = "14px";
			e.style.cursor = "pointer";
			e.style.padding = "5px";
			e.style.border = "1px solid #888";
			e.textContent = "anuluj";
			e.onclick = function() {
				e.parentNode.cancel();
			}
			return e;
		}
		function fullscreen_div() {
			var e = document.createElement("div");
			e.style.position = "fixed";
			e.style.top = "0";
			e.style.left = "0";
			e.style.width = (window.innerWidth+1000)+"px";
			e.style.height = (window.innerHeight+1000)+"px";
			return e;
		}
		var e = document.createElement("div");
		var bg = fullscreen_div();
		bg.style.zIndex = 1000;
		bg.style.background = "#000";
		try {
			bg.style.opacity = 0.8;
		} catch(e) {}
		e.appendChild(bg);
		var c = fullscreen_div();
		c.style.zIndex = 1001;
		c.appendChild(popup_back());
		var bound = document.createElement("div");
		content.style.position = "absolute";
		if (w > window.innerWidth-42) { w = window.innerWidth-42; }
		if (h > window.innerHeight-90) { h = window.innerHeight-90; }
		content.style.left = Math.round((window.innerWidth-w-22)/2)+"px";
		var t = Math.round((window.innerHeight-h-40)/2);
		if (40 > t) { t = 50; }
		content.style.top = t+"px";
		content.style.width = w+"px";
		content.style.padding = "5px 10px";
		content.style.border = "1px solid #888";
		content.style.background = "#eee";
		content.style.overflow = "hidden";
		c.appendChild(content);
		e.appendChild(c);
		document.body.appendChild(e);
		content.cancel = c.cancel = function() {
			document.body.removeChild(e);
		}
	}
	function crop(w, h, url) {
		var e = document.createElement("div");
		e.get_data_url = function() { return ''; };
		var zoomin = function() {};
		var zoomout = function() {};
		var loading = document.createElement("div");
		loading.style.width = w+"px";
		loading.style.border = "1px solid black";
		loading.style.height = Math.abs(h-10)+"px";
		loading.textContent = "Ładowanie obrazka...";
		loading.style.overflow = "hidden";
		loading.style.marginTop = "10px";
		loading.style.textAlign = "center";
		e.appendChild(loading);
		function btns() {
			var bar = document.createElement('div');
			bar.style.textAlign = 'center';
			bar.style.width = (2+icon_w)+'px';
			bar.style.fontSize = '20px';
			bar.style.fontWeight = 'bold';
			function b(t, a) {
				var s = document.createElement('span');
				s.textContent = t;
				s.style.padding = '0 8px';
				s.onmousedown = function(ev) {
					try { ev.preventDefault(); } catch(err) {}
					return false;
				};
				s.onclick = a;
				s.style.cursor = "pointer";
				bar.appendChild(s);
			}
			b('+', function() { zoomin(); return false; });
			b('-', function() { zoomout(); return false; });
			return bar;
		};
		e.appendChild(btns());
		function crop_canvas(img) {
			var ce = document.createElement("canvas");
			ce.style.border = "1px solid black";
			ce.width = icon_w;
			ce.height = icon_h;
			ce.style.cursor = "move";
			var c = ce.getContext("2d");
			var tmpce = document.createElement("canvas");
			tmpce.width = icon_w*2;
			tmpce.height = icon_h*2;
			var tmpc = tmpce.getContext("2d");
			var drag = utils.drag(ce);
			var center = { x: Math.round(img.width/2),
				y: Math.round(img.height/2) };
			var zoom = Math.max(icon_w/img.width, icon_h/img.height);
			var minzoom = zoom;
			function redraw() {
				if (zoom > 1) { zoom = 1; }
				if (zoom < minzoom) { zoom = minzoom; }
				if (center.x < icon_w/(2*zoom)) {
					center.x = icon_w/(2*zoom);
				}
				if (center.x > img.width-icon_w/(2*zoom)) {
					center.x = img.width-icon_w/(2*zoom);
				}
				if (center.y < icon_h/(2*zoom)) {
					center.y = icon_h/(2*zoom);
				}
				if (center.y > img.height-icon_h/(2*zoom)) {
					center.y = img.height-icon_h/(2*zoom);
				}
				if (zoom == 1) {
					c.drawImage(
						img,
						Math.round(icon_w/2 - (img.width-center.x)),
						Math.round(icon_h/2 - (img.height-center.y))
					);
				} else {
					// (scaling in two steps increases
					// the quality of antialiasing,
					// but makes the image more blurry)
					tmpc.drawImage(
						img,
						Math.round(icon_w - 2*(img.width-center.x)*zoom),
						Math.round(icon_h - 2*(img.height-center.y)*zoom),
						Math.round(img.width*zoom*2),
						Math.round(img.height*zoom*2)
					);
					c.drawImage(tmpce, 0, 0, icon_w, icon_h);
				}
			}
			redraw();
			var dragstart = { dx: 0, dy: 0 };
			drag.onstart.add(function(ev) {
				dragstart.dx = center.x-ev.x/zoom;
				dragstart.dy = center.y-ev.y/zoom;
			});
			drag.onmove.add(function(ev) {
				center.x = ev.x/zoom + dragstart.dx;
				center.y = ev.y/zoom + dragstart.dy;
				redraw();
			});
			zoomin = function() {
				zoom *= 1.5;
				redraw();
			};
			zoomout = function() {
				zoom *= 0.7;
				redraw();
			};
			return ce;
		}
		function startloading() {
			var image = new Image();
			image.onload = function() {
				if (image.width < 10) { return; }
				if (image.height < 10) { return; }
				var canvas = crop_canvas(image);
				e.replaceChild(canvas, loading);
				e.get_data_url = function() {
					return canvas.toDataURL();
				};
			}
			image.src = url;
		}
		startloading();
		return e;
	}
	function form_p(e) {
		var p = document.createElement("p");
		p.style.margin = "5px 0";
		if (e) { p.appendChild(e); }
		return p;
	}
	function form_submit(label) {
		var e = document.createElement("input");
		e.type = "submit";
		e.style.border = "1px solid black";
		e.style.padding = "3px 8px";
		e.value = label;
		return form_p(e);
	}
	function form_label(text) {
		var p = form_p();
		p.textContent = text;
		return p;
	}
	function form_hidden(name) {
		var e = document.createElement("input");
		e.type = "hidden";
		e.name = name;
		return e;
	}
	var screen_crop, screen_choose;
	screen_choose = function() {
		function files() {
			var ret = [];
			var es = document.getElementsByTagName("a")
			var i;
			for (i = 0; i < es.length; i++) {
				var a = es[i];
				if (a.href && a.href.match(/\.jpg$/)) {
					ret[ret.length] = a.href;
				}
			}
			return ret;
		}
		var form = document.createElement("form");
		var f = files();
		function chooseimage_btn(url) {
			var im = document.createElement("img");
			im.src = url;
			im.style.maxWidth = "100px";
			im.style.maxHeight = "100px";
			im.style.margin = "5px";
			im.style.verticalAlign = "top";
			im.style.cursor = "pointer";
			im.onclick = function() {
				form.cancel();
				screen_crop(url);
			};
			return im;
		}
		function chooseimage() {
			var d = document.createElement("div");
			d.style.margin = "5px 0";
			d.style.width = "348px";
			d.style.height = "250px";
			d.style.border = "1px solid #444";
			d.style.overflowX = "hidden";
			d.style.overflowY = "scroll";
			var i;
			for (i = 0; i < f.length; i++) {
				d.appendChild(chooseimage_btn(f[i]));
			}
			return d;
		}
		if (f.length < 1) {
			form.appendChild(form_label("Nie można utworzyć ikony, gdyż na tej stronie nie ma żadnych obrazków."));
			popup(form, 300, 50);
		} else if (f.length < 2) {
			screen_crop(f[0]);
		} else {
			form.appendChild(form_label("Wybierz obrazek do utworzenia ikony:"));
			form.appendChild(chooseimage());
			popup(form, 350, 300);
		}
	};
	screen_crop = function(url) {
		var form = document.createElement("form");
		form.method = "POST";
		form.action = window.location;
		form.appendChild(form_label("Wykadruj ikonę:"));
		var c = crop(icon_w, icon_h, url);
		c.style.margin = "5px 0";
		form.appendChild(c);
		var form_data = form_hidden('icon_png_base64');
		form.appendChild(form_data);
		form.appendChild(form_submit("Zapisz"));
		form.onsubmit = function() {
			try {
				var s = c.get_data_url();
				var beg = 'data:image/png;base64,';
				if (s.substr(0, beg.length) !== beg) {
					alert('Could not export image data!');
				} else {
					form_data.value = s.substr(beg.length);
					return true;
				}
			} catch(err) {}
			return false;
		}
		popup(form, Math.max(300, icon_w+2), icon_h+100);
	};
	try {
		screen_choose();
	} catch (err) {
		alert(err);
	}
}

function icon_admin_remove() {
	var form = document.createElement("form");
	form.method = "POST";
	form.action = window.location;
	var i = document.createElement("input");
	i.type = "hidden";
	i.name = "icon_remove";
	i.value = "1";
	form.appendChild(i);
	document.body.appendChild(form);
	form.submit();
}
