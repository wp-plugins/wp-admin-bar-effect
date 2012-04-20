(function($){
	add_link();
	check_input();
	$('#wabe-actlink').click(function(){
		check_input();
	});
	var wab = $('#wpadminbar');
	var wab_sub = $('div.quicklinks');
	var wab_sub_sub = $('body.admin-bar #wpcontent, body.admin-bar #adminmenu');
	var wabe_speed, wabe_sensitivity, wabe_interval, wabe_timeout = '';
	$.fn.wp_admin_bar_effect = function(options){
	var opts = $.extend({}, $.fn.wp_admin_bar_effect.defaults, options);
		wab.hoverIntent({
			sensitivity: wabe_sensitivity,
			interval: wabe_interval,
			over: wabe_start_stop,
			timeout: wabe_timeout,
			out: wabe_start_stop
		});
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
		wabe_speed : 3000,
		wabe_sensitivity: 4,
		wabe_interval: 50,
		wabe_timeout: 200	
	};
	$('#wabe-img-button').click(function() {
		post_id = $('#post_ID').val();
		formfield = $('#wabe-ico').attr('name');
		tb_show('', 'media-upload.php?post_id='+post_id+'&amp;type=image&amp;TB_iframe=true');
		return false;
	});
	window.send_to_editor = function(html) {
		imgurl = $('img',html).attr('src');
		$('#wabe-ico').val(imgurl);
		tb_remove();
	}
	function check_input(){
		if(!$('#wabe-actlink').is(':checked')){
			$('label[for^="wabe-ico"]').parent().parent().hide();
		} else {
			$('label[for^="wabe-ico"]').parent().parent().show();
		}
	};
	function add_link(){
		$('#wabe a').attr('target', '_blank');		
	};
})(jQuery);