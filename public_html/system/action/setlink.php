<?php
	require_once '../core/secure.php';
	require_once '../core/post.php';
	$user = Secure::User();

	header("Content-type: application/json");

	if ( $user && isset( $_POST[ 'id' ] ) && isset( $_POST[ 'url' ] ) ) {
		$user = Secure::User();
		$id = $_POST[ 'id' ];
		$post = Post::getPostById( $id );
		if( $post && $user && ( $post['author'] === $user['id'] ) ) {
			Post::UpdatePermalink( $id , $post , $post , $_POST[ 'url' ] );
			Post::Update( $id , $post , $post );
			echo '{"success": true , "url": "' . $post['url'] . '" }';
			exit;
		}
		echo '{"success":false , "message": "not owner"}';
	} 

		echo '{"success":false , "message": "wrong params"}';
	exit;
