<?php
	require_once '../core/secure.php';
	require_once '../core/post.php';
	
	$user = Secure::User();
	if ( $user && isset( $_POST[ 'post' ] ) ) {
		$user = Secure::User();
		$post = $_POST[ 'post' ];
		$id = $post['id'];
		$prev = Post::getPostById( $id );
		if( $prev && $user && ( $prev['author'] === $user['id'] ) ) {
			Post::Remove( $prev );
			echo 'ok';
			exit;
		}
	} 
	
	echo "fail\n";