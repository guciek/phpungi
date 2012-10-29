
// Copyright by Karol Guciek (http://guciek.github.com)
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, version 2 or 3.

function gallery() {
	var images = [];
	var current = 0;
	var container = document.createElement("div");
	var container_in_body = false;
	function close() {
		if (container_in_body) {
			document.body.removeChild(container);
			container_in_body = false;
		}
	}
	function background() {
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
		} catch (err) {}
		bg.style.cursor = "default";
		return bg;
	}
	function preloader() {
		var e = document.createElement("div");
		e.style.position = "fixed";
		e.style.left = Math.round(window.innerWidth/2)+"px";
		e.style.top = Math.round(window.innerHeight/2)+"px";
		function dot() {
			var e = document.createElement("div");
			e.style.position = "absolute";
			e.style.fontSize = e.style.lineHeight =
				e.style.width = e.style.height = "40px";
			e.textContent = "\u2022";
			e.style.fontFamily = "sans-serif";
			e.style.textAlign = "center";
			e.style.color = "#000";
			return e;
		}
		var visible = false;
		e.style.display = "none";
		var dots = [];
		var i;
		for (i = 0; i < 8; i++) {
			dots[i] = dot();
			dots[i].style.top = Math.round(20*Math.sin(Math.PI*i/4)-20)+"px";
			dots[i].style.left = Math.round(20*Math.cos(Math.PI*i/4)-20)+"px";
			e.appendChild(dots[i]);
		}
		var animationpos = 0;
		function animate() {
			if (!visible) { return; }
			var i;
			for (i = 0; i < 8; i++) {
				var c = "456789ab".charAt(i);
				dots[(i+animationpos)%8].style.color = "#"+c+c+c;
			}
			animationpos++;
		}
		setInterval(animate, 100);
		e.style.zIndex = 1030;
		e.show = function() {
			if (visible) { return; }
			visible = true;
			e.style.display = "block";
		};
		e.hide = function() {
			if (!visible) { return; }
			visible = false;
			e.style.display = "none";
		};
		return e;
	}
	container.appendChild(background());
	var prel = preloader();
	container.appendChild(prel);
	function btn(str, action, right, top) {
		var e = document.createElement("div");
		e.style.position = "fixed";
		e.textContent = str;
		e.style.top = (top ? 10 : Math.round(window.innerHeight/2-40))+"px";
		if (right) {
			e.style.right = "20px";
		} else {
			e.style.left = top ? "10px" : "20px";
		}
		e.style.fontSize = top ? "80px" : "60px";
		e.style.fontFamily = "sans-serif";
		e.style.textAlign = "center";
		e.style.zIndex = 1010;
		e.style.color = "#fff";
		e.style.cursor = "pointer";
		e.onmousedown = function(event) {
			try { event.preventDefault(); } catch(err) {}
			action();
			return false;
		};
		e.onmouseover = function() { e.style.color = "#bbb"; };
		e.onmouseout = function() { e.style.color = "#fff"; };
		return e;
	}
	var show_image;
	var btnleft = btn("\u25C0", function() {
		show_image(current-1, -1);
	}, false);
	var btnright = btn("\u25B6", function() {
		show_image(current+1, 1);
	}, true);
	container.appendChild(btnleft);
	container.appendChild(btnright);
	container.appendChild(btn("\u21ba", close, false, true));
	function onimgload() {
		btnleft.style.display = (current > 0) ?
			"block" : "none";
		btnright.style.display = (current+1 < images.length) ?
			"block" : "none";
		img.onmousedown = close;
		img.style.cursor = "pointer";
	}
	function elemimg(src, from) {
		prel.hide();
		var e = new Image();
		e.style.position = "fixed";
		e.style.top = "0";
		e.style.left = "0";
		e.style.border = "5px solid white";
		e.style.zIndex = 1010;
		e.style.display = "none";
		e.onload = function() {
			prel.hide();
			var w = e.width;
			var h = e.height;
			var sw = window.innerWidth;
			var sh = window.innerHeight;
			var ratio = Math.min((sw-230)/w, (sh-50)/h);
			if (ratio > 1) { ratio = 1; }
			w = Math.round(w*ratio);
			h = Math.round(h*ratio);
			e.style.width = w+"px";
			e.style.height = h+"px";
			var destleft = Math.round((sw-w)/2-5);
			var movelength = Math.round((sw+w+100)/2)*from;
			e.style.left = (destleft+movelength)+"px";
			e.style.top = Math.round((sh-h)/2-5)+"px";
			e.style.display = "block";
			if (movelength == 0) {
				onimgload();
				return;
			}
			var interval;
			var anim = 1;
			interval = setInterval(function() {
				anim -= 0.08;
				if (anim <= 0) {
					clearInterval(interval);
					e.style.left = destleft+"px";
					onimgload();
					return;
				}
				e.style.left = Math.round(destleft+
					movelength*anim*anim*anim)+"px";
			}, 33);
		}
		e.src = src;
		return e;
	}
	var img = null;
	show_image = function(n, from) {
		if (!container_in_body) {
			document.body.appendChild(container);
			container_in_body = true;
		}
		if (img) {
			img.onload = undefined;
			img.style.display = "none";
			container.removeChild(img);
		}
		img = elemimg(images[n], from);
		container.appendChild(img);
		current = n;
		btnleft.style.display = "none";
		btnright.style.display = "none";
		prel.show();
	};
	function set_action(a, n) {
		a.onclick = function(event) {
			try { event.preventDefault(); } catch(err) {}
			show_image(n, 0);
			return false;
		};
	}
	function init_ul(ul) {
		var es = ul.getElementsByTagName("a");
		var i;
		for (i = 0; i < es.length; i++) {
			var a = es[i];
			var file = a.href;
			if (file.match(/\.jpg$/)) {
				set_action(a, images.length);
				images[images.length] = file;
			}
		}
	}
	function init() {
		var es = document.getElementsByTagName("ul");
		var i;
		for (i = 0; i < es.length; i++) {
			if (es[i].className === "gallery") {
				init_ul(es[i]);
				return;
			}
		}
	}
	init();
}
try { gallery(); } catch(err) {}
