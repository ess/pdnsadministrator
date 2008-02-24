/* Suckerfish menu script to keep IE in line */

sfHover = function() {
	var sfEls = document.getElementById("hsmod").getElementsByTagName("LI");
	for (var i=0; i<sfEls.length; i++) {
		sfEls[i].onmouseover=function() {
			this.className+=" sfhover";
		};
		sfEls[i].onmouseout=function() {
			this.className=this.className.replace(new RegExp(" sfhover\\b"), "");
		};
	}
}

// Only run in IE
if (window.attachEvent) {
	window.attachEvent("onload", sfHover);
}


