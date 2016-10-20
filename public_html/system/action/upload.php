<?php
	require_once '../core/secure.php';
	require_once '../core/post.php';
	require_once '../core/image.php';
	
	$user = Secure::User();
	
	function fail() {
		echo 'fail';
		exit;
	}
	
	if ( $user && isset( $_FILES['file'] ) && isset( $_GET['postid'] ) ) {
		if ( $_FILES["file"]["error"] > 0) fail();
		$id = $_GET['postid'];
		$post = PosT::getPostById( $id );
		if(!$post) fail();
		if($post['author'] !== $user['id'] ) fail();
		$folder = '../../images/' . $id . '/';
		if (!file_exists( $folder )) {
			mkdir( $folder , 0770 , true );
		}
		$target = $folder . str_replace( ' ', '.', $_FILES["file"]["name"] );
		move_uploaded_file( $_FILES["file"]["tmp_name"],  $target);
		Image::Generate( $_GET["postid"] , basename( $target ) );
		Image::Generate( $_GET["postid"] , basename( $target ) , 'thumb' );
		echo 'ok';
		exit;
	} 
	
	fail();
	
