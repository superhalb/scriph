'use strict';

var Scriph = Scriph || {};

Scriph.Editor_attachbox = function () {
	// [ private properties ]
	var updater = $( '#uploader' );

	// [ private methods ]
	function create() {	
		var el = $("<div class='statusbox'></div>") ,
		    filename = null , 
				fullname = "" , 
				provider = null,
				remover = null,
				face = null,
				filebox = null;
				
		updater.append( el );
		Scriph.Editor_attachs.update();
		
		function set( name , size , service ) {
			name = name.replace(/ /gi, ".");
			filename = name;

			remover = $( '<div class="remover hide"><i class="fa fa-times"></i></div>' );
			face = $( '<div class="face hide"><i class="fa fa-picture-o"></i></div>' );
			filebox = $( '<div class="filename"></div>' );
			
			el.append( remover );
			el.append( face );
			el.append( filebox );

			if( service !== undefined ) {
				provider = service;
				fullname = service + ":" + name;
				filebox.html( '<i class="fa fa-' + service + '"></i>' + name );
			} else {
				fullname = name;
				filebox.text( name );
			}

			if ( Scriph.Editor.Post.face === fullname ) {
				//face.removeClass('face');
				face.removeClass('hide');
				face.addClass('current-face');
			}
			
			el.hover( function() {
					remover.removeClass('hide');
					face.removeClass('hide');
				} , function() {
					remover.addClass('hide');
					if( ! face.hasClass('current-face') ) {
						face.addClass('hide');
					}
			});
			
			face.click( function() {
				var prev = $('.current-face');
				prev.addClass('hide');
				prev.removeClass('current-face');
				face.addClass('current-face');
				face.removeClass('hide');
				Scriph.Editor.Post.face = fullname;
				Scriph.Editor_menu.save();
			});
			
			remover.click( function() {
				if( provider === null ) {
					removeFile( fullname , el );
				} else {
					el.remove();
				}
			});
			
			switch( provider ) {
				case 'youtube': {
					el.dblclick( function() {
						var link = '\n\n[//]:# (Link to Youtube video)\n';
						link += '<iframe src="//www.youtube.com/embed/' + filename + '" frameborder="0" allowfullscreen></iframe>\n';
						Scriph.Editor_realTime.insert( link );
					});
					break;
				}
				default: {
					el.dblclick( function() {
						var link = "\n![][" + name + "]\n";
						Scriph.Editor_realTime.insert( link );
					});
					break;
				}
			}
		};
		
		function pin() {
			face.click();
		}
		
		function progress ( percent ) {
			if ( percent < 100 ) {
				filebox.text(  percent + "%" );
			} else {
				filebox.text(  filename );
			}	
		}
		
		function abort (query) {
			face.remove();
			el.css('background','#A44');
			el.off('dblclick');
			remover.off('click');
			remover.click( function() {
				el.remove();
			});
			filebox.html( '<i class="fa fa-exclamation-circle error"></i>' + filename );
		}

		function cover() {
			el.css('background-size','cover' );
			el.css('background-position','center center' );

			switch ( provider ) {
				case 'youtube': {
					el.css('background-image','url(//img.youtube.com/vi/' + filename +'/default.jpg)' );
					break;
				}
				default: {
					el.css('background-image','url(../images/cache/'+ Scriph.Editor.Post.id + '/thumb.' + filename + ')' );
					break;
				}
			}
		}

		return {
			create: create,
			set: set,
			progress: progress,
			abort: abort,
			cover: cover,
			filename: function(){ return filename; }
		};
	}
			
	// [ public methods ]
	return {
		create: create
	}
}();
