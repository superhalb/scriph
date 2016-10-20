'use strict';

var Scriph = Scriph || {};

Scriph.Settings_menu = function () {
	// [ private properties ]
	
	// [ private methods ]
	
	function save() {
		var blog = $('#blog').serializeArray();
		var theme = $('#theme').serializeArray();
		var settings = { 
				blog: blog ,
				theme: theme
			};
		
		$.post( "action/save-settings.php" , settings , function( data ) {
			if ( data === "ok" ) {
				window.location = ( "index.php" );
			} else {
				alert( "Error: documento no se ha podido salvar" );
			}
		});
	}

	function cancel() {
		window.location = ( "index.php" );
	}

	// [ public methods ]
	return {
		init: function(){
			$('.menu > [action="save"]').click( save );
			$('.menu > [action="cancel"]').click( cancel );
		}
	}
}();

Scriph.Settings_menu.init();
