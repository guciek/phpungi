
// Copyright by Karol Guciek (http://guciek.github.com)
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, version 2 or 3.

function youtube_links() {
	function strafter(str, beg) {
		if (str.indexOf(beg) !== 0) {
			return "";
		}
		return str.substring(beg.length);
	}
	function youtubeplayer(id) {
		var e = document.createElement("iframe");
		e.style.border = "none";
		e.style.width = "640px";
		e.style.height = "400px";
		e.style.display = "block";
		e.src = "http://www.youtube.com/embed/"+id;
		return e;
	}
	function arraycopy(a) {
		var b = [];
		for (i in a) {
			b[i] = a[i];
		}
		return b;
	}
	function enrich() {
		var es = arraycopy(document.getElementsByTagName("a"));
		for (i in es) {
			var e = es[i];
			if (!e.href) continue;
			var id = strafter(e.href, "http://www.youtube.com/watch?v=");
			if ((id.length >= 5) && (id = id.match(/^[-_a-zA-Z0-9]+/g))) {
				id = id[0];
				try {
					e.parentNode.replaceChild(youtubeplayer(id), e);
				} catch (err) {}
			}
		}
	}
	setTimeout(enrich, 50);
}
youtube_links();
