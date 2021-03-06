<?php
	require_once '../core/secure.php';
	require_once '../core/post.php';
	require_once '../core/config.php';
	
	$user = Secure::User();
	if ( $user && isset( $_POST[ 'post' ] ) ) {
		$user = Secure::User();
		$post = $_POST[ 'post' ];
		$id = $post['id'];
		$prev = Post::getPostById( $id );
		if( $prev && $user && ( $prev['author'] === $user['id'] ) ) {
			reset( $prev['state'] );
			$state = key( $prev['state'] );
			$statefile = '../../' . Config::$settings->Storage . '/state/published/'. $prev['publish-time'] .'.' . $id ;
			if ( $state === 'published' && file_exists( $statefile ) ) {
				unlink( $statefile );
				unset( $post['id'] );
				unset( $post['state'] );
				Post::Update( $id , $post , $prev);
				file_put_contents( __DIR__ . "/../../" . Config::$settings->Storage . "/state/draft/" . $id , "" );
				echo 'ok';
				exit;
			} else {
				echo 'B-' . $prev['publish-time'] .'.' . $id ."-";
			}
		} else {
			echo 'A';
		}
	} 

	echo "fail\n";
