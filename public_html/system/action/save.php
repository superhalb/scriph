<?php
	require_once '../core/secure.php';
	require_once '../core/post.php';

	header("Content-type: application/json");
	
	$user = Secure::User();
	if ( $user && isset( $_POST[ 'post' ] ) ) {
		$user = Secure::User();
		$post = $_POST[ 'post' ];
		$id = $post['id'];
		$prev = Post::getPostById( $id );
		if( $prev && $user && ( $prev['author'] === $user['id'] ) ) {
			unset( $post['id'] );
			unset( $post['state'] );
			Post::Update( $id , $post , $prev );
			echo '{"success":true}';
			exit;
		}
	} 

	echo '{"success":false}';
