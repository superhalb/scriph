'use strict';

var Scriph = Scriph || {};

Scriph.Editor_menu = function () {
	// [ private properties ]
	var action_publish = $('.menu > [action="publish"]'), 
	    action_unpublish = $('.menu > [action="unpublish"]'), 
	    action_fixed = $('.menu > [action="fixed"]');
	
	// [ private methods ]

	function frontend() {
		$.post( "action/save.php" , { post: Scriph.Editor.Post } , function( data ) {
			if ( data.success ) {
				if( Scriph.Editor.Post.url ) {
					window.location = ('../content/' + Scriph.Editor.Post.url);
				} else {
					window.location = ('../view.php?id=' + Scriph.Editor.Post.id);
				}
			} else {
				alert( "Error: documento no se ha podido salvar" );
			}
		});
	}
	
	function content() {
		$.post( "action/save.php" , { post: Scriph.Editor.Post } , function( data ) {
			if ( data.success ) {
				window.location = ( "index.php" );
			} else {
				alert( "Error: documento no se ha podido salvar" );
			}
		});
	}
	
	function save() {
		$.post( "action/save.php" , { post: Scriph.Editor.Post } , function( data ) {
			if ( data.success ) {
				shyMessage('GUARDADO', 1000 );
			} else {
				alert( "Error: documento no se ha podido salvar" );
			}
		});
	}

	function publish() {
		popup( '¿PUBLICAR este post?' , ['Si','No'] , function( reply ) {
			if( reply === '0' ) {
				$.post( "action/publish.php" , { post: Scriph.Editor.Post } , function( data ) {
					if ( data === "ok" ) {
						action_unpublish.show();
						action_publish.hide();
						Scriph.Editor.Post.state = { 'published' : true };
						shyMessage('PUBLICADO', 1000 );
					} else {
						alert( "Error: documento no se ha podido salvar" );
					}
				});
			}
		});
	}

	function unpublish() {
		popup( '¿RETIRAR este post?' , ['Si','No'] , function( reply ) {
			if( reply === '0' ) {
				$.post( "action/unpublish.php" , { post: Scriph.Editor.Post } , function( data ) {
					if ( data === "ok" ) {
						action_publish.show();
						action_unpublish.hide();
						Scriph.Editor.Post.state = { 'draft' : true };
						shyMessage('PUBLICADO', 1000 );
					} else {
						alert( "Error: documento no se ha podido salvar" );
					}
				});
			}
		});
	}

	function discard() {
		popup( '¿BORRAR este post?' , ['Si','No'] , function( reply ) {
			if( reply === '0' ) {
				$.post( "action/discard.php" , { post: Scriph.Editor.Post } , function( data ) {
					if ( data === "ok" ) {
						window.location.replace("index.php" );
					} else {
						alert( "Error: documento no se ha podido borrar" );
					}
				});
			}
		});
	}
	
	// [ public methods ]
	return {
		init: function(){
			$('.menu > [action="frontend"]').click( frontend );
			$('.menu > [action="content"]').click( content );
			$('.menu > [action="save"]').click( save );
			$('.menu > [action="discard"]').click( discard );
			
			action_publish.click( publish );
			action_unpublish.click( unpublish );

			setInterval( save , 60000 );

			if ( Scriph.Editor.Post.state.published !== undefined ) {
				action_publish.hide();
				action_fixed.hide();
			}
			if ( Scriph.Editor.Post.state.draft !== undefined ) {
				action_unpublish.hide();
				action_fixed.hide();
			}
			if ( Scriph.Editor.Post.state.fixed !== undefined ) {
				action_publish.hide();
				action_unpublish.hide();
			}
		} , 
		save: save
	}
}();

Scriph.Editor_menu.init();
