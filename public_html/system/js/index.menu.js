'use strict';

var Scriph = Scriph || {};

Scriph.Index_menu = function () {
	// [ private properties ]
	
	// [ private methods ]
	
	function frontend() {
		window.location = ('../');
	}
		
	function settings() {
		window.location = ('settings.php');
	}

	function newPost() {
		window.location = ('editor.php');
	}
	
	// [ public methods ]
	return {
		init: function(){
			$('.menu > [action="frontend"]').click( frontend );
			$('.menu > [action="settings"]').click( settings );
			$('.menu > [action="new post"]').click( newPost );
		}
	}
}();

Scriph.Index_menu.init();
