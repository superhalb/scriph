'use strict';

var Scriph = Scriph || {};

Scriph.Index = function ( ) {
	// [ private properties ]
	var current = null,
			writer = new commonmark.HtmlRenderer(),
			reader = new commonmark.Parser(),
			fade_speed = 50,
			display_settings = false,
			setting_id = $("#post-settings > ul > li > [name='id']") ,
			setting_url = $("#post-settings > ul > li > [name='url']") ,
			setting_url_val = "" ;

	writer.softbreak = "<br/>";
	
	// [ private methods ]
	function getPreview( ev ) {
		var el = $( ev.target ) ,
		    id = el.attr('id');
				
		if ( id !== undefined && current != id) {
			current = id;
			$( '.postitem-select' ).removeClass( 'postitem-select' );
			el.addClass( 'postitem-select' );
			$( "#postview" ).fadeOut( fade_speed , function() {
				$.post( "action/preview.php?postid=" + id , showPreview );
			});
		}
	}
	
	function showPreview( result ){
		if ( typeof result === "object" && result.success ) {

			var splited = result.post.content.split("\n\n");
			
			var title = splited[0];
			var excerpt = splited[1];
			
			
			var URLBASE = document.URL;
			var base_from = URLBASE.indexOf('://') + 1;
			var base_to = URLBASE.indexOf("system/") - base_from;
			URLBASE = URLBASE.substr( base_from ,  base_to );
			var references = "\n\r\n\r\n\r";
			var r = result.post.references;
			for( var f in r ) {
				references += "[" + r[f] + "]: " + URLBASE+"images/cache/" + result.post.id + "/" + r[f] + "\n";
			}
			
			var content = "---";
			if ( result.post.rendered !== undefined ) {
				content = result.post.rendered;
			} else {
				splited = splited.slice(2);
				var value = splited.join("\n\n");
				var source = value + references;
				var parsed = reader.parse( source );
				content = writer.render( parsed );	
				
				result.post.rendered = content;
				$.post( "action/save.php" , { post: result.post } , function( data ) {
					if ( data.success ) {
						shyMessage('GUARDADO', 1000 );
					} else {
						alert( "Error: documento no se ha podido salvar" );
					}
				});
				
			}
			var post_preview = '<div class="post-title">' + title 
							 + '</div><div class="post-excerpt">' + excerpt 
							 + '</div><div class="post-content">' + content + '</div>';

			$("#post-view").html( post_preview );
			$("#postview").fadeIn( fade_speed );
			display_settings = false;
			$("#post-settings").hide();
			setting_id.val( result.post.id );
			setting_url.val( result.post.url );
			setting_url_val = result.post.url;
		} else {
			alert( "Error: documento no se ha podido previsualizar" );
		}
	}
	
	function openEditor( ev ) {
		var el = $( ev.target ) ,
			  id = el.attr('id');
		if ( id !== undefined ) {
			current = id;
		}
		if ( current != null ) {
			window.location = ('editor.php?postid=' + current );
		}
	}
	
	function toggleSettings() {
		display_settings = !display_settings;
		if ( display_settings ) {
			$("#post-settings").slideDown( fade_speed * 2 );
		} else {
			$("#post-settings").slideUp( fade_speed * 2 );
		}
	}
	
	function rename( result ) {
		if ( typeof result === "string" ) {
			var s = $('.postitem[id="' + current + '"]');
			s.attr('id', result);
			current = result;
			setting_id.val( current );
		} else {
			alert( "Error: documento no se ha podido renombrar" );
		}
	}
	
		function setlink( result ) {
		if ( result.success ) {
			setting_url.val( result.url );
			setting_url_val = result.url;
		} else {
			alert( "Error: no se ha podido cambiar el link" );
		}
	}
	
	function saveSettings() {
		var new_id = setting_id.val();
		if ( new_id!== current ) {
			$.post( "action/rename.php" , { "old": current, "new": new_id} , rename );
		}
		var new_url = setting_url.val();
		if ( new_url !== setting_url_val ) {
			$.post( "action/setlink.php" , { "id": current, "url": new_url} , setlink );
		}
		display_settings = false;
		$("#post-settings").slideUp( fade_speed * 2 );
	}
	
	// [ public methods ]
	return {
		init: function(){
			var post_list = $('.postitem');
			post_list.click( getPreview );
			post_list.dblclick( openEditor );
			if ( post_list.length > 0 ) {
				$( post_list[ 0 ] ).click();
			}
			$('[action="edit"]').click( openEditor );
			$('[action="post settings"]').click( toggleSettings );
			$('[action="save settings"]').click( saveSettings );			
		}
	}
}();

Scriph.Index.init();