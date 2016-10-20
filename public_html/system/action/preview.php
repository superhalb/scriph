<?php

	require_once '../core/post.php';
	require_once '../core/secure.php';
	
	header("Content-type: application/json");

	
	class PreviewContext {

		public $success;
		public $post;
		
		function __construct() {
			$json = '{"title":"", "content":""}';
			$references = "";
			$user = Secure::User();
			if ( $user && isset ( $_GET["postid"] ) ) {
				$pdata = Post::getPostById($_GET["postid"]);
				$pdata['refs_add'] = "";
				if($pdata['author'] !== $user['id'] ) {
					$this->notAllowed();
				}
				$folder = "../../images/" . $pdata["id"];
				if ( is_dir( $folder ) ) {
					$it = new RecursiveDirectoryIterator( $folder );
					foreach(new RecursiveIteratorIterator($it) as $file) { 
						if ( basename( $file ) === '.' || basename( $file )  === '..' ) continue;
						$f = Image::Generate( $_GET["postid"] , basename( $file ) );
						$references = $references . "\n[" . basename( $file ) ."]: ../" . $f;
					}
					$pdata['refs_add'] = "\n" . $references;
				}
			} else {
				$pdata = json_decode( $json , true );
			}
			
			$this->success = true;
			$this->post = $pdata;
		}
		
		function notAllowed(){
			echo '{"success":false}';
			exit;
		}
	};

	echo json_encode( new PreviewContext );
	
	
