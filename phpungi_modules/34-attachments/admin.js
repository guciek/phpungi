
// Copyright by Karol Guciek (http://guciek.github.com)
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, version 2 or 3.

var attachments_admin = function() {
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
	function popup(content, w, h) {
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
		c.cancel = function() {
			document.body.removeChild(e);
		}
	}
	function form_p(e) {
		var p = document.createElement("p");
		p.style.margin = "5px 0";
		if (e) { p.appendChild(e); }
		return p;
	}
	function form_label(text) {
		var p = form_p();
		p.textContent = text;
		return p;
	}
	function form_hidden(name, value) {
		var e = document.createElement("input");
		e.type = "hidden";
		e.value = value;
		e.name = name;
		return e;
	}
	function form_fileinput(name) {
		var e = document.createElement("input");
		e.type = "file";
		e.style.border = "1px solid black";
		e.style.padding = "3px";
		e.style.width = "292px";
		e.name = name;
		e.onchange = function() {
			var form = e.parentNode.parentNode;
			form.submit();
			for (var i = 0; i < form.childNodes.length; i++) {
				form.childNodes[i].style.display = "none";
			}
			form.appendChild(form_label("Ładowanie..."));
		}
		return form_p(e);
	}
	function form_submit(label) {
		var e = document.createElement("input");
		e.type = "submit";
		e.style.border = "1px solid black";
		e.style.padding = "3px 8px";
		e.value = label;
		return form_p(e);
	}
	function add_form(subject) {
		var e = document.createElement("form");
		e.method = "POST";
		e.enctype = "multipart/form-data";
		e.action = window.location;
		e.appendChild(form_label("Dodaj załącznik (maks. 1000kB):"));
		e.appendChild(form_fileinput("attachments_add"));
		return e;
	}
	function del_form(name) {
		var e = document.createElement("form");
		e.method = "POST";
		e.action = window.location;
		e.appendChild(form_label("Czy na pewno chcesz usunąć ten załącznik?"));
		e.appendChild(form_hidden("attachments_del", name));
		e.appendChild(form_submit("Usuń"));
		return e;
	}
	return {
		add: function() {
			popup(add_form(), 300, 60);
		},
		del: function(name) {
			popup(del_form(name), 300, 60);
		}
	};
};
attachments_admin = attachments_admin();
