'use strict';

var Scriph = Scriph || {};

Scriph.Editor_attachs = function () {
	// [ private properties ]

	var counter = $('.attach-counter'),
	    uploader = $('#uploader'),
			options = $('#options'),
			editor = $('#editor'),
			visible = false;
	
	// [ private methods ]

	function show() {
		uploader.attr('style','height: 80px');
		options.attr('style','bottom: 80px');
		editor.attr('style','bottom: 120px');
	}
	
	function hide() {
		uploader.attr('style','height: 0px');
		options.attr('style','bottom: 0px');
		editor.attr('style','bottom: 40px');
	}

	function toggle() {
		var attachs = $('.statusbox').length;
		visible = ( ! visible ) && ( attachs !== 0 );
		if ( visible ) {
			show();
		} else {
			hide();
		}
	}
	
	function load() {
		var references = Scriph.Editor.Post.references;
		var id = Scriph.Editor.Post.id;
		for( var i in references ) {
			var s = Scriph.Editor_attachbox.create(); //createStatusbar( uploader );
			s.set( references[i] , 0 );
			s.cover();
		}
	}
	
	function update() {
		var attachs = $('.statusbox').length;
		if( attachs > 0 ) {
			counter.removeClass('hide');
			counter.text( attachs );
		} else {
			counter.addClass('hide');
			visible = false;
			hide();
		}
	}
	
	// [ public methods ]
	return {
		init: function(){
				$('.menu > [action="toggle attachs"]').click( toggle );
				load();
				update();
			} ,
		update: update
	}
}();

Scriph.Editor_attachs.init();
