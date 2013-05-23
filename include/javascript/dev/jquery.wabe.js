jQuery(document).ready(function($){
	var 
		wab = $('#wpadminbar'),
		wab_sub = $('div.quicklinks'),
		wab_sub_sub = $('#wpwrap'),
		wabe_speed, wabe_sensitivity, wabe_interval, wabe_timeout = '',
		wabe_media_frame;
	
	function check_input(){
		if($('#r-disable').is(':checked')){
			$('label[for^="wabe-icon"], label[for^="wabe-target-link"]').parent().parent().hide();
		} else {
			$('label[for^="wabe-icon"], label[for^="wabe-target-link"]').parent().parent().show();
		}
	};
	
	$.fn.wp_admin_bar_effect = function(options){
		var 
		opts = $.extend({}, $.fn.wp_admin_bar_effect.defaults, options);
		
		wab.hoverIntent({
			sensitivity: wabe_sensitivity,
			interval: wabe_interval,
			over: wabe_start_stop,
			timeout: wabe_timeout,
			out: wabe_start_stop
		});
		
		function wabe_stop(){
			wab.stop().animate({'height': '4px'}, wabe_speed, function(){
				wab_sub.hide();
			});
			wab_sub_sub.stop().animate({'margin-top': '-24px', 'padding-bottom': '24px'}, wabe_speed);
		};
		
		function wabe_start(){
			wab_sub.show();
			wab.stop().animate({'height': '28px'}, wabe_speed);
			wab_sub_sub.stop().animate({'margin-top': '0', 'padding-bottom': '0'}, wabe_speed);
		};
		
		function wabe_start_stop(){
			if(wab_sub.is(':hidden')){
				wabe_start();
			} else {
				wabe_stop();
			}
		};
	};
	
	$.fn.wp_admin_bar_effect.defaults =
	{
		wabe_speed : 3000,
		wabe_sensitivity: 4,
		wabe_interval: 50,
		wabe_timeout: 200	
	};

	if(wabe.target_link === '1'){
	$('#wabe a').attr('target', '_blank');
	}
	$('html, body').wp_admin_bar_effect({
		wabe_speed: wabe.speed,
		wabe_sensitivity: wabe.sensitivity,
		wabe_interval: wabe.interval,
		wabe_timeout: wabe.timeout
	});
	check_input();
	$('input.wabe-checked').click(function(){
		check_input();
	});
	$(document.body).on('click', '#submit-img', function(e){
		e.preventDefault();
		if(wabe_media_frame){
			wabe_media_frame.open();
			return;
		}
		wabe_media_frame = wp.media.frames.wabe_media_frame = wp.media({
			className: 'media-frame wabe-media-frame',
			frame: 'select',
			multiple: false,
			title: wabe.media_title,
			library: {
				type: 'image'
			},
			button: {
				text: wabe.media_button
			}
		});
		wabe_media_frame.on('select', function(){
			var
			media_attachment = wabe_media_frame.state().get('selection').first().toJSON();
			$('#wabe-icon').val(media_attachment.url);
		});
		wabe_media_frame.open();
	});
});