<?php
	require_once '../core/secure.php';
	require_once '../core/post.php';
	
	$user = Secure::User();
	
	function fail() {
		echo 'fail';
		exit;
	}

	if ( $user && isset( $_POST[ "post" ] ) ) {
		$post = $_POST[ "post" ];
		$file = $_POST[ "file" ];
		$id = $post["id"];
		$post = PosT::getPostById( $id );
		if(!$post) fail();
		if($post['author'] !== $user['id'] ) fail();

		$dirPath = '../../images/' . $id ;
		if( is_dir($dirPath) ) {
			foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dirPath, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $path) {
				
				if ( file_exists( $path ) && ( basename( $path ) === $file ) ) {
						unlink($path->getPathname());
					echo "ok";
					exit;
				}
			}
		}
	}
	
	fail();

