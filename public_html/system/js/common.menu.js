'use strict';

var Scriph = Scriph || {};

Scriph.Common_menu = function () {
	// [ private properties ]
	
	// [ private methods ]
	
	function profile() {
		// TBD
		// window.location = ('profile.php');
	}
	
	function signOut() {
		window.location = ('action/logout.php');
	}
	
	// [ public methods ]
	return {
		init: function(){
			$('.menu > [action="profile"]').click( profile );
			$('.menu > [action="sign out"]').click( signOut );
		}
	}
}();

Scriph.Common_menu.init();
