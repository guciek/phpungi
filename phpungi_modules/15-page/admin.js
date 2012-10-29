
// Copyright by Karol Guciek (http://guciek.github.com)
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, version 2 or 3.

var page_admin = function() {
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
	function form_input(name, value) {
		var e = document.createElement("input");
		e.type = "text";
		e.style.border = "1px solid black";
		e.style.padding = "3px";
		e.style.width = "292px";
		e.value = value;
		e.name = name;
		return form_p(e);
	}
	function form_submit(label, name) {
		var e = document.createElement("input");
		e.type = "submit";
		e.style.border = "1px solid black";
		e.style.padding = "3px 8px";
		e.value = label;
		if (name) { e.name = name; }
		return form_p(e);
	}
	function newsub_form(subject) {
		var e = document.createElement("form");
		e.method = "POST";
		e.action = window.location;
		e.appendChild(form_label("Nazwa podstrony:"));
		e.appendChild(form_hidden("page_adminsubject", subject));
		e.appendChild(form_input("page_newsub", ""));
		e.appendChild(form_submit("Utwórz"));
		return e;
	}
	function newlang_form(subject) {
		var e = document.createElement("form");
		e.method = "POST";
		e.action = window.location;
		var info = subject.split("/");
		e.appendChild(form_label("Tytuł strony \""+
			info[0]+"\" w języku \""+info[1]+"\":"));
		e.appendChild(form_hidden("page_adminsubject", subject));
		e.appendChild(form_input("page_newlang", ""));
		e.appendChild(form_submit("Utwórz"));
		return e;
	}
	function chtitle_form(subject, def) {
		var e = document.createElement("form");
		e.method = "POST";
		e.action = window.location;
		e.appendChild(form_label("Nowy tytuł:"));
		e.appendChild(form_hidden("page_adminsubject", subject));
		e.appendChild(form_input("page_chtitle", def));
		e.appendChild(form_submit("Zmień"));
		return e;
	}
	function delete_form(subject) {
		var e = document.createElement("form");
		e.method = "POST";
		e.action = window.location;
		e.appendChild(form_label("Czy na pewno chcesz usunąć tę stronę?"));
		e.appendChild(form_submit("Usuń", "page_delete"));
		return e;
	}
	return {
		newsub: function(subject) {
			popup(newsub_form(subject), 300, 85);
		},
		newlang: function(subject) {
			popup(newlang_form(subject), 300, 85);
		},
		chtitle: function(subject, def) {
			popup(chtitle_form(subject, def), 300, 85);
		},
		del: function() {
			popup(delete_form(), 300, 60);
		},
		moveend: function(subject) {
			var form = document.createElement("form");
			form.method = "POST";
			form.action = window.location;
			form.appendChild(form_hidden("page_adminsubject", subject));
			form.appendChild(form_hidden("page_movetoend", "1"));
			document.body.appendChild(form);
			form.submit();
		},
	};
};
page_admin = page_admin();
