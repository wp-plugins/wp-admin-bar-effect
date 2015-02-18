( function( $ ) {
	$.fn.wp_admin_bar_effect = function( options ) {
		var defaults = {
				speed: 3000,
				sensitivity: 4,
				interval: 50,
				timeout: 200
		},
		options = $.extend( {}, defaults, options ),
		wpwrap = $( '#wpwrap' ),
		wpadminbar = $( '#wpadminbar', wpwrap ),
		quicklinks = $( '.quicklinks', wpadminbar ),
		wabe = {
			open: function() {
				quicklinks.css( 'visibility','visible' );
				wpadminbar.stop().animate({ 'height':'32px' }, options.speed );
				wpwrap.stop().animate({ 'margin-top':'0','padding-bottom':'0' }, options.speed );
			},
			close: function(){
				quicklinks.css( 'visibility','hidden' );
				wpadminbar.stop().animate({ 'height':'4px' }, options.speed );
				wpwrap.stop().animate({ 'margin-top':'-32px', 'padding-bottom':'32px' }, options.speed );
			},
			toggle: function(){
				return ( 'hidden' === quicklinks.css('visibility') ) ? wabe.open() : wabe.close();
			},
			start: function(){
				wpadminbar.hoverIntent({
					sensitivity: options.sensitivity,
					interval: options.interval,
					timeout: options.timeout,
					over: wabe.toggle,
					out: wabe.toggle
				});

				if( 'visible' === quicklinks.css('visibility') ) {
					wabe.close();
				}
			},
			stop: function(){
				quicklinks.css( 'visibility','visible' );
				wpadminbar.css( 'height','46px' ).off();
				wpwrap.css({ 'margin-top':'0','padding-bottom':'0' });
			},
			init: function(){
				return ( 'absolute' === $('#adminmenuwrap').css('position') ) ? wabe.stop() : wabe.start();
			}
		};

		return this.each( function() {
			wabe.init();
			$( window ).on( 'resize', function() {
				wabe.init();
			});
		});
	};

	$.fn.wabe_check = function() {
		var self = this,
			toggle = self.find( '.wabe-toggle' ).closest( 'tr' ),
			check = {
				open: function() {
					toggle.removeClass( 'wabe-hidden' );
				},
				close: function() {
					toggle.addClass( 'wabe-hidden' );
				},
				init: function() {
					return ( self.find( '#wabe-disabled' ).is( ':checked' ) ) ? check.close() : check.open();
				}
			};

		return self.each(function() {
			check.init();
			self.on( 'change', 'input.wabe-radio', function() {
				check.init();
			});
		});
	};

}( jQuery ) );

jQuery( document ).ready( function( $ ) {
	$( document.body ).wp_admin_bar_effect({
		speed: wabe.speed,
		sensitivity: wabe.sensitivity,
		interval: wabe.interval,
		timeout: wabe.timeout
	});

	if ( '1' === wabe.target_link ) {
		$( '#wabe a' ).attr( 'target', '_blank' );
	}

	$( 'div.wabe-settings' ).wabe_check();

	$( document.body ).on( 'click', '#submit-img', function( e ) {
		e.preventDefault();

		if ( typeof wabe_media_frame != 'undefined' ) {
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

		wabe_media_frame.on( 'select', function() {
			var media_attachment = wabe_media_frame.state().get('selection').first().toJSON();
			$( '#wabe-icon' ).val( media_attachment.url );
		});

		wabe_media_frame.open();
	});
});
