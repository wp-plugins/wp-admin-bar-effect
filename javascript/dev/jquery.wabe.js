(function($){
	var wab = $('#wpadminbar');
	var wab_sub = $('div.quicklinks');
	var wab_sub_sub = $('body.admin-bar #wpcontent, body.admin-bar #adminmenu');
	var wabe_active_link, wabe_url, wabe_domain, wabe_speed, wabe_sensitivity, wabe_interval, wabe_timeout = '';
	$.fn.wp_admin_bar_effect = function(options){
	var opts = $.extend({}, $.fn.wp_admin_bar_effect.defaults, options);
		add_link();
		wab.hoverIntent({
			sensitivity: wabe_sensitivity,
			interval: wabe_interval,
			over: wabe_start_stop,
			timeout: wabe_timeout,
			out: wabe_start_stop
		});
		function add_link(){
			if(opts['wabe_active_link'] === true){
				var wabe_site = opts['wabe_url'];
				$('ul#adminmenu').prepend('<li id="wabe-li">'+ wabe_site +'</li>');
				$('#wabe-li').addClass('menu-top');
			}else{
				return false;
				}
		
		}
		function wabe_stop() {
			wab.stop().animate({top: '-24px'}, wabe_speed, function(){
				wab_sub.hide();
			});
			wab_sub_sub.stop().animate({paddingTop: '4px'}, wabe_speed);
		}

		function wabe_start() {
			wab_sub.show();
			wab.stop().animate({top: '0'}, wabe_speed);
			wab_sub_sub.stop().animate({paddingTop: '28px'}, wabe_speed);
		}
	
		function wabe_start_stop() {
			if(wab_sub.is(':hidden')){
				wabe_start();
			} else {
				wabe_stop();
			}
		}
	};
	$.fn.wp_admin_bar_effect.defaults = {
		wabe_active_link: true,
		wabe_url : '',
		wabe_speed : 3000,
		wabe_sensitivity: 4,
		wabe_interval: 50,
		wabe_timeout: 200	
	};
})(jQuery);