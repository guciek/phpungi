
// Copyright by Karol Guciek (http://guciek.github.com)
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, version 2 or 3.

function login_form() {
	function popup(cf) {
		var bg = document.createElement("div");
		bg.style.position = "fixed";
		bg.style.top = "0";
		bg.style.left = "0";
		bg.style.width = (window.innerWidth+1000)+"px";
		bg.style.height = (window.innerHeight+1000)+"px";
		bg.style.zIndex = 1000;
		bg.style.background = "#000";
		try { bg.style.opacity = 0.8; } catch(e) {}
		cf.style.zIndex = 1001;
		cf.style.position = "fixed";
		cf.style.padding = "5px 15px";
		cf.style.top = Math.round((window.innerHeight-170)/2)+"px";
		cf.style.left = Math.round((window.innerWidth-330)/2)+"px";
		cf.style.width = "300px";
		cf.style.zIndex = 1001;
		cf.style.background = "#eee";
		var e = document.createElement("div");
		e.appendChild(bg);
		e.appendChild(cf);
		document.body.appendChild(e);
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
	function form_input(name, type) {
		var e = document.createElement("input");
		e.type = type;
		e.style.border = "1px solid black";
		e.style.padding = "3px";
		e.style.width = "292px";
		e.name = name;
		return form_p(e);
	}
	function form_submit(label) {
		var e = document.createElement("input");
		e.type = "submit";
		e.style.border = "1px solid black";
		e.style.padding = "3px 8px";
		e.style.marginTop = "6px";
		e.value = label;
		return form_p(e);
	}
	function make_form(subject) {
		var e = document.createElement("form");
		e.method = "POST";
		e.action = window.location;
		e.appendChild(form_label("Użytkownik:"));
		e.appendChild(form_input("login_user", "text"));
		e.appendChild(form_label("Hasło:"));
		e.appendChild(form_input("login_pass", "password"));
		e.appendChild(form_submit("Zaloguj"));
		return e;
	}
	popup(make_form());
};
