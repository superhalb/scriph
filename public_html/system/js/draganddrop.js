function setDragAndDropUploader( obj ) {
	$(document).on('dragenter', function (e) {
		e.stopPropagation();
		e.preventDefault();
		obj.css('background-color', 'rgba(0,255,0,0.1)');
	});
	$(document).on('dragleave', function (e) {
		e.stopPropagation();
		e.preventDefault();
		obj.css('background-color', '');
	});
	$(document).on('dragover', function (e) {
		e.stopPropagation();
		e.preventDefault();
	});
	$(document).on('drop', function (e) {
		obj.css('background-color', '');
		e.preventDefault();
		var files = e.originalEvent.dataTransfer.files;
		handleFileUpload(files,obj);
	});
}

function removeFile( filename , el ) {
	popup( 'Estas segura de que quieres BORRAR este adjunto?' , ['Si','No'] , function( reply ) {
		if( reply === '0' ) {
			$.post( "action/removeattachment.php" , { post: Scriph.Editor.Post , file: filename } , function( data ) {
				if ( data === "ok" ) {
					el.remove();
					shyMessage('ELIMINADO: ' + filename, 1000);
					Scriph.Editor_attachs.update();
				} else {
					alert( "Error: archivo adjunto no se ha podido borrar" );
				}
			});
		}
	});
}

function handleFileUpload(files,obj)
{
	if ( files ) {
		$.post( "action/save.php" , { post: Scriph.Editor.Post } , function( data ) {
			if ( data.success ) {
				shyMessage('GUARDADO', 1000 );
				for (var i = 0; i < files.length; i++) {
					var fd = new FormData();
					fd.append('file', files[i]);
					//var status = new createStatusbar(obj); //Using this we can set progress.
					var status = Scriph.Editor_attachbox.create();
					status.set(files[i].name,files[i].size);
					sendFileToServer(fd,status);
				}
			} else {
				alert( "Error: documento no se ha podido salvar" );
			}
		});
	}
}

function sendFileToServer(formData,status) {
	var uploadURL ="action/upload.php?postid=" + Scriph.Editor.Post.id;
	var extraData ={}; //Extra Data.
	var jqXHR=$.ajax({
		xhr: function() {
			var xhrobj = $.ajaxSettings.xhr();
			if (xhrobj.upload) {
				xhrobj.upload.addEventListener('progress', function(event) {
					var percent = 0;
					var position = event.loaded || event.position;
					var total = event.total;
					if (event.lengthComputable) {
						percent = Math.ceil(position / total * 100);
					}
					//Set progress
					status.progress(percent);
				}, false);
			}
			return xhrobj;
		},
		url: uploadURL,
		type: "POST",
		contentType:false,
		processData: false,
		cache: false,
		data: formData,
		success: function(data){
			status.progress( 100 );
			if ( data === 'ok' ) {
				status.cover();
				var attachs = $('.statusbox').length;
				if ( Scriph.Editor.Post.face === '' ) {
					status.pin();
				}
				Scriph.Editor.Post.references.push( status.filename() );
				Scriph.Editor_attachs.update();
			} else {
				status.abort();
			}
		}
	}); 
}

$( document ).ready(function() {
	setDragAndDropUploader( $( '#uploader' ) );
});