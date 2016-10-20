<?php
	// Copyright (c) 2013, Alberto San Blas Cañabate. License details can be found in license/license.txt
	
	require_once 'core/secure.php';
	require_once 'core/config.php';
	require_once 'core/post.php';
	require_once 'core/templates.php';
	require_once 'core/image.php';
	
	Secure::AuthOrLogIn();
	
	class EditorContext {
		public $Post;
		public $themeCss;
		public $Menu;
		public $Profile;

		function __construct() {
			$menu_json_filename = __DIR__ .'/json/editor-menu.json';
			$menu_json = file_get_contents( $menu_json_filename );
			$this->Menu = json_decode( $menu_json );

			$this->Profile = Secure::User(); 
			$mandatoryLessFilename = 'themes/' . Config::$settings->Theme .'/mandatory.less';
			if(file_exists( $mandatoryLessFilename ) ) {
				$this->themeCss = '<link href="' . $mandatoryLessFilename . '"rel="stylesheet/less" type="text/css">' ;	
			} else {
				$mandatoryCssFilename = 'themes/' . Config::$settings->Theme .'/mandatory.css';
				$this->themeCss = '<link href="' . $mandatoryCssFilename . '" rel="stylesheet" type="text/css">' ;	
			}

			
			$json = '{"title":"","content":""}';
			$references = array();
			if ( isset ( $_GET["postid"] ) ) {
				$id = $_GET["postid"];
			} else {
				$id = Post::Create();
				header('Location: editor.php?postid=' . $id);
				exit;
			}
			$pdata = Post::getPostById( $id );
			if($pdata['author'] !== $this->Profile['id'] ) {
				// TODO
				// Not authorized
				exit;
			}
			$folder = "../images/" . $pdata["id"];
			if ( is_dir( $folder ) ) {
				$it = new RecursiveDirectoryIterator( $folder );
				foreach(new RecursiveIteratorIterator($it) as $file) { 
					if ( basename( $file ) === '.' || basename( $file )  === '..' ) continue;
					array_push( $references, basename( $file ) );
					Image::Generate( $pdata["id"] , basename( $file ) , 'thumb' );
				}
			}
			$jsonr = json_encode( $references );
			if(isset($pdata['intro'])) {
				$pdata['content'] = $pdata['title'] . "\n\n" . $pdata['intro'] . "\n\n" . $pdata['content'];
				unset($pdata['intro']);
			}
			$this->Post = array(
				'id' => $id,
				'tags' => $pdata['tags'],
				'json' => json_encode( $pdata ),
				'references' => $jsonr
			);
		}
	};
	
	$tpl = Templates::$sys->loadTemplate('editor');
	echo $tpl->render( new EditorContext );
