'use strict';

var Scriph = Scriph || {};

Scriph.Editor_realTime = function () {
	// [ private properties ]
		var codeEditor ,
				wordCounter = $("#content-counter"),
				tags_input = $('#tags_input'),
				text_input =  $('#text_input'),
				title_preview = $("#title_preview"),
				excerpt_preview = $("#excerpt_preview"),
				text_preview =  $('#text_preview'),
				summary_counter = $("#summary-counter"),
				title_counter = $("#title-counter"),
				externalRefs = [],
				writer = new commonmark.HtmlRenderer(),
				reader = new commonmark.Parser();
		writer.softbreak = "<br/>";

	// [ private methods ]
	function insert( text ) {
		var cursorPos = codeEditor.prop('selectionStart'),
				v = codeEditor.val(),
				textBefore = v.substring(0,  cursorPos ),
				textAfter  = v.substring( cursorPos, v.length );
		
		codeEditor.val( textBefore + text + textAfter );
		codeEditor[0].setSelectionRange( cursorPos + text.length , cursorPos + text.length);
		contentChange();
	}
	
	function checkForExternalRefs( erefs ) {
		var youtube = [
			/(?:https?:\/\/)?(?:www\.)?youtube\.com\/watch\?(?=.*v=((\w|-){11}))(?:\S+)?/g,
			/(?:\/\/)?(?:www\.)?youtube\.com\/embed\/(?=((\w|-){11}))(?:\S+)?/g
			];
		for( var i = 0 ; i < youtube.length ; ++i ) {
			var m;
			while ( ( m = youtube[i].exec( erefs ) )  != null ) {
				var vid = m[1];
				if( externalRefs.indexOf( vid ) < 0 ) {
					externalRefs.push( vid );
					var status = Scriph.Editor_attachbox.create();
					status.set( m[1] , 1 , 'youtube' );
					status.cover();
				}
			}
		}
	}
	
	function contentChange() {
		var content = codeEditor.val();
		Scriph.Editor.Post.content = content;
		checkForExternalRefs ( content );

		var cursorPos = codeEditor.prop('selectionStart'),
				textBefore = content.substring(0,  cursorPos ),
				textAfter  = content.substring( cursorPos, content.length );

		update( content );
		resizeTextarea( codeEditor );
		countWords();
	}

	function countWords() {
		var s = text_preview.text();
		s = s.replace(/(^\s*)|(\s*$)/gi,"");//exclude  start and end white-space
		s = s.replace(/\n/g," "); // exclude newline with a start spacing
		s = s.replace(/[ ]{2,}/gi," ");//2 or more space to 1

		var ss = s.split(' ');
		var words = ( ss[0] === '' ) ?  "0 palabras" : ss.length + " palabras"; 
		wordCounter.attr('counter', words );
	}
	
	function replaceAll(find, replace, str) {
		return str.replace(new RegExp(find, 'g'), replace);
	}

	function resizeTextarea( obj ) {
		var sizer = $('<textarea id="sizer" class="edit-post-content"></textarea>');
		obj.after(sizer);
		var content = obj.val();
		var c = content.split("\n\n");

		sizer.val( c[0] );
		sizer.height( 0 );
		sizer.height( sizer[0].scrollHeight + 10 );
		var title_mark = $('#title-mark');
		title_mark.height( sizer.height() );

		sizer.val( c[0] + "\n");
		sizer.height( 0 );
		sizer.height( sizer[0].scrollHeight );
		var excerpt_offset = sizer.height() + 20;
		
		c = c.slice(1);
		
		if(c.length > 0 ) {
			sizer.val( c[0] );
			sizer.height( 0 );
			sizer.height( sizer[0].scrollHeight + 6 );
			$('#excerpt-mark').height( sizer.height() );
			$('#excerpt-mark').css( 'top' , ''+ excerpt_offset + 'px');
			$('#excerpt-mark').css('display','block');
		} else {
			$('#excerpt-mark').css('display','none');
		}
		
		sizer.val( content );
		sizer.height( 0 );
		sizer.height( sizer[0].scrollHeight );
//		sizer.height( sizer[0].scrollHeight - 40 );
//		sizer.height( sizer.height() + sizer[0].scrollHeight - sizer[0].clientHeight ); /* Firefox fix */
		var size = sizer.height() + 10;
		sizer.remove();
		obj.height( size );
	}

	
	function updatetags() {
		var tags = tags_input.val();
		tags = tags.toLowerCase().split(',');
		switch( tags.length ) {
			case 0: break;
			case 1: tags = tags[0]; break;
			default: {
				var result = "";
				var comma = false;
				for ( var i = 0 ; i < tags.length ; ++ i ) {
					tags[ i ] = tags[ i ].trim();
					if ( tags[ i ] === '' ) continue;
					if ( comma ) {
						result += ', ' + tags[ i ];
					} else {
						comma = true;
						result = tags[ i ];
					}
				}
				tags = result;
				break;
			}
		}
		tags_input.val( tags );
		Scriph.Editor.Post.tags = $("#tags_input").val();
	}

	function update ( value ) {
		var URLBASE = document.URL;
		var base_from = URLBASE.indexOf('://') + 1;
		var base_to = URLBASE.indexOf("system/editor.php") - base_from;
		URLBASE = URLBASE.substr( base_from ,  base_to );
		var references = "\n\r\n\r\n\r";
		var r = Scriph.Editor.Post.references;
		for( var f in r ) {
			references += "[" + r[f] + "]: " + URLBASE+"images/cache/" + Scriph.Editor.Post.id + "/" + r[f] + "\n";
		}
		
		var splited = value.split("\n\n");
		
		Scriph.Editor.Post.title = splited[0];
		Scriph.Editor.Post.excerpt = splited[1];

		title_preview.html( Scriph.Editor.Post.title );
		excerpt_preview.html( Scriph.Editor.Post.excerpt );


		splited = splited.slice(2);
		value = " \n" + splited.join("\n\n");

		var parsed = reader.parse( value + references );
		var mdp = writer.render( parsed );	
		Scriph.Editor.Post.rendered = mdp;

		mdp = mdp.replace( /<([^/][^>]*)>/g , "<$1 ((seb))) >" );

		var seb = 0;
		while( mdp.indexOf("((seb)))") > 0 ) {
			mdp = mdp.replace("((seb)))" , 'seb="' + seb + '"');
			seb++;
		}
			
		var source = text_preview.find('[seb]');
		var target = $('<div></div>').html( mdp );
		var from = source.length > seb ? source.length : seb;
		for ( var i  = from - 1 ; i >= seb ; -- i ) {
			text_preview.find('[seb="' + i + '"]').remove();
		}
		for ( var i  = seb - 1 ; i >= 0 ; -- i ) {
			var t0 = text_preview.find('[seb="' + i + '"]');
			if ( t0.length !== 1 ) {
				text_preview.html( mdp );
				break;
			}
			var t1 = target.find('[seb="' + i + '"]');
			if ( t0[0].outerHTML != t1[0].outerHTML ) {
				var replacement = t1.clone();
				t0.replaceWith( replacement );
				replacement[0].scrollIntoView();
			}
		}
	}

	// [ public methods ]
	return {
		init: function(){
				codeEditor = $("#content_input");
				codeEditor.val( Scriph.Editor.Post.content );

				update( Scriph.Editor.Post.content );
				
				countWords();
				checkForExternalRefs( Scriph.Editor.Post.content );

				codeEditor.bind('keyup paste cut mouseup', contentChange );
				
				$( window ).resize( function() {
					resizeTextarea( codeEditor );
				});
				$( document ).ready(function() {
					resizeTextarea( codeEditor );
				});
				tags_input.blur( updatetags );
			} ,
		insert: insert
	}
}();

Scriph.Editor_realTime.init();
