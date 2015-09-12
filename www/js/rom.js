// http://stackoverflow.com/questions/2219924/idiomatic-jquery-delayed-event-only-after-a-short-pause-in-typing-e-g-timew
var typewatch = (function() {
	var timer = 0;
	return function(callback, ms) {
		clearTimeout(timer);
		timer = setTimeout(callback, ms);
	}
})();

function popup_show()
{
	$('html').addClass('overlay');
	$('#tab-popup-overlay').addClass('popup-overlay-visible');
	$('#tab-popup').addClass('popup-visible');
}
