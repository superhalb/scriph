<?php
	require_once '../core/secure.php';
	require_once '../core/post.php';
	$user = Secure::User();

	if ( $user && isset( $_POST[ 'old' ] ) && isset( $_POST[ 'new' ] ) ) {
		$user = Secure::User();
		$old_id = $_POST[ 'old' ];
		$old = Post::getPostById( $old_id );
		if( $old && $user && ( $old['author'] === $user['id'] ) ) {
			echo Post::Rename( $old_id , $_POST[ 'new' ] );
			exit;
		}
	} 

	header("Content-type: application/json");
	echo '{"success":false}';
	exit;
