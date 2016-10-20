<?php
// Copyright (c) 2013-2014, Alberto San Blas Cañabate. License details can be found in license/license.txt

require_once __DIR__ . '/vendor/autoload.php';
require_once 'system/core/secure.php';
require_once 'system/core/config.php';
require_once 'system/core/post.php';
require_once 'system/core/image.php';
require_once 'system/core/templates.php';

date_default_timezone_set('Europe/Madrid');

$post_id = $_GET['id'];
$secure = ( $_SERVER[ 'SERVER_PORT' ] != 443 ) ? "" : "s";

$cached = __DIR__ . '/' . Config::$settings->Storage . '/cache/' . $post_id . $secure . '.html';
if ( file_exists( $cached ) && false ) {
	readfile( $cached );
	exit;
}

// TODO : Save cache result only if it is published

class Context {
	public $Blog;
	public $Post;

	function __construct() {
		$this->Blog = array( 
			'title' => Config::$settings->Title ,
			'assets' => getBaseUrl() . '/system/themes/' . Config::$settings->Theme ,
			'baseUrl' => getBaseUrl()
		);

		$this->Post = Post::getPostById( $_GET['id'] );

		$profile = Secure::User( false );
		if ( $profile ) {
			$this->User = $profile;
		} 
		
		if( (!$this->Post) || (( !$this->Post['state']['published'] ) && ( !$this->Post['state']['fixed'] ))) {
			if ( $this->Post['author'] !== $this->User['id'] ) {
				echo "Not allowed";
				exit;
			}
		}
		
		$splited = explode( "\n\n" , $this->Post['content'] );
		$this->Post['excerpt'] = $splited [1];
		
		$this->Post['content'] = $this->Post['rendered'];
		
		$plugin = __DIR__ . '/system/themes/' . Config::$settings->Theme . '/plugin.php';
		if ( file_exists( $plugin ) ) {
			require_once $plugin;
			PluginManager::Dispatch( Hooks::ON_VIEW , $this );
		}

		$headers = array();
		$headers[] = '<title>' . $this->Post['title']. ' - ' . Config::$settings->Title . '</title>';
		$headers[] = Post::generateFacebookMetaTags( $this->Post );
		$headers[] = Post::generateTwitterMetaTags( $this->Post );

		$this->Blog['Headers'] = '';
		foreach( $headers as $header ) {
			$this->Blog['Headers'] .= $header ."\n";
		}
		
		$index_tpl = Templates::$themed->loadTemplate('post');
		$this->Blog['Body'] = $index_tpl->render( $this );
	}
};

$tpl = Templates::$themed->loadTemplate('default');

$render = $tpl->render( new Context );

echo $render;

$cache_folder = __DIR__ . '/' . Config::$settings->Storage . '/cache/';

if ( !is_dir( $cache_folder ) ) {
    mkdir( $cache_folder );
}

file_put_contents( $cached , $render );