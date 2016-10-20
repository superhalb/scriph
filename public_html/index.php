<?php
// Copyright (c) 2013-2014, Alberto San Blas Cañabate. License details can be found in license/license.txt

require_once 'system/core/secure.php';
require_once 'system/core/config.php';
require_once 'system/core/post.php';
require_once 'system/core/templates.php';
require_once 'system/core/plugin.php';
require_once 'base_url.php';
date_default_timezone_set('Europe/Madrid');


class Context {
	public $Blog;
	public $Post;
	public $Page;
	public $Filter;
	public $PaginationLink;

	function __construct() {
		$this->Blog = array( 
			'title' => Config::$settings->Title ,
			'description' => Config::$settings->Description,
			'assets' => getBaseUrl() . '/system/themes/' . Config::$settings->Theme ,
			'baseUrl' => getBaseUrl()
		);
		
		$filter = '';
		if ( isset( $_GET['f'] ) ) {
			$filter = $_GET['f'];
		}
		$baseFilterUrl = $filter === '' ? getBaseUrl() . '/' : getBaseUrl() . '/tags/' . $filter . '/';
		$this->PostCount = Post::countPostList( 'published' , $filter );
		$this->Pagination = array(
			'First' => $baseFilterUrl
		);
		if ( isset( $_GET['p'] ) ) {
			$this->Pagination['Page'] = intval( $_GET['p'] );
		} else {
			$this->Pagination['Page'] = 1;
		}
		if(Config::$settings->PostPerPage > 0) {
			$this->Pagination['maxPage'] = 1 + floor( ($this->PostCount-1) / Config::$settings->PostPerPage );
		} else {
			$this->Pagination['maxPage'] = 1;
		}
		if ( $this->Pagination['Page'] < 1 ) {
			$this->Pagination['Page']  = 1;
		} else if ( $this->Pagination['Page'] > $this->Pagination['maxPage'] ) {
			$this->Pagination['Page'] = $this->Pagination['maxPage'];
		}
		
		if ( $this->Pagination['maxPage'] > 1 ) {
			$this->Pagination['Last'] = $baseFilterUrl . '?p=' . $this->Pagination['maxPage'];
		}
		if ( $this->Pagination['Page'] > 1 ) {
			$prev = $this->Pagination['Page'] - 1;
			$this->Pagination['Prev'] = $baseFilterUrl . '?p=' . $prev;
			
			$this->Pagination['PrevPages'] = array();
			for ( $i = $this->Pagination['Page'] - 1 , $j = Config::$settings->PaginationLinks; $i > 0 && $j > 0 ; --$i, --$j ) {
				array_unshift( $this->Pagination['PrevPages'] , array(
					'Text' => $i ,
					'Link' => $baseFilterUrl . '?p=' . $i 
				));
			}
			if($i>0) {
				array_unshift( $this->Pagination['PrevPages'] , array(
					'Text' => '...' ,
				));
			}
		}
		if ( $this->Pagination['Page'] < $this->Pagination['maxPage'] ) {
			$next = $this->Pagination['Page'] + 1;
			$this->Pagination['Next'] = $baseFilterUrl . '?p=' . $next;

			$this->Pagination['NextPages'] = array();
			for ( $i = $this->Pagination['Page'] + 1 , $j = Config::$settings->PaginationLinks; $i <= $this->Pagination['maxPage'] && $j > 0 ; ++$i, --$j ) {
				array_push( $this->Pagination['NextPages'] , array(
					'Text' => $i ,
					'Link' => $baseFilterUrl . '?p=' . $i
				));
			}
			if ( $i <= $this->Pagination['maxPage'] ) {
				array_push( $this->Pagination['NextPages'] , array(
					'Text' => '...' ,
				));
			}
		}
		
		$p0 = ( Config::$settings->PostPerPage * ( $this->Pagination['Page'] - 1 ) ) + 1;
		$p1 = ( Config::$settings->PostPerPage * $this->Pagination['Page'] );
		$this->Post = Post::getPostList( - $p0 ,  - $p1 , 'published' , $filter );
		$context = $this;
		Templates::$themed->addHelper( '_pagination' , function( $a , Mustache_LambdaHelper $helper) use(&$context) {
			$params = explode('|',$a);
			$length = count( $params );
			$result = file_get_contents( __DIR__ . '/system/templates/pagination.mustache' );
			if ( $length !== 1 || $params[0] !== '') {
				$context->Pagination['FirstReplacement'] = $params[0];
				if ( $length > 1 ) $context->Pagination['PrevReplacement'] = $params[1];
				if ( $length > 2 ) $context->Pagination['NextReplacement'] = $params[2];
				if ( $length > 3 ) $context->Pagination['LastReplacement'] = $params[3];
			}
			return $result;
		} );

		$this->Filter = $filter;
		$profile = Secure::User( false );
		if ( $profile ) {
			$this->User = $profile;
		}
		$plugin = __DIR__ . '/system/themes/' . Config::$settings->Theme . '/plugin.php';
		if ( file_exists( $plugin ) ) {
			require_once $plugin;
			PluginManager::Dispatch( Hooks::ON_INDEX , $this );
		}
		
		$headers = array();
		$headers[] = '<title>' . Config::$settings->Title . '</title>';
		$headers[] = Post::generateFacebookMetaTags();
		$headers[] = Post::generateTwitterMetaTags();

		$this->Blog['Headers'] = '';
		foreach( $headers as $header ) {
			$this->Blog['Headers'] .= $header;
		}

		$index_tpl = Templates::$themed->loadTemplate('index');
		$this->Blog['Body'] = $index_tpl->render( $this );

	}
};

$tpl = Templates::$themed->loadTemplate('default');

echo $tpl->render( new Context );
