function popup( msg , r , callback ) {
	var pu = $('<div id="blocker"><div id="popup">' + msg + '<lu></lu></div></div>');
	for( var i in r ) {
		pu.find('lu').append('<li reply="' + i + '">' + r[i] + '</li>');
	}
	pu.find('li').click( function() {
		callback( $(this).attr('reply') );
		pu.remove();
	});
	$('body').append( pu );
}

function shyMessage( msg , wait , callback ) {
	var shymsg = $('<div id="shymsg">' + msg + '</div>');
	$('body').append( shymsg );
	setTimeout( function() {
		shymsg.addClass('showShyMsg');
	} , 50 );
	setTimeout( function() {
		shymsg.removeClass('showShyMsg');
	} , wait );
	setTimeout( function() {
		shymsg.remove();
		if( callback !== undefined ) {
			callback();
		}
	} , wait+ 500 );
}