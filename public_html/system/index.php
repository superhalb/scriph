<?php
// Copyright (c) 2013, Alberto San Blas Cañabate. License details can be found in license/license.txt

require_once 'core/secure.php';
require_once 'core/config.php';
require_once 'core/post.php';
require_once 'core/templates.php';
require_once 'core/plugin.php';
date_default_timezone_set('Europe/Madrid');

Secure::AuthOrLogIn();

Templates::$sys->addHelper( '_formatTime' , function( $a , Mustache_LambdaHelper $helper) {
	$t = intval( $helper->render($a) );
	$months = array(1 => 'Ene',2 => 'Feb',3 => 'Mar',4 => 'Abr',5 => 'May',6 => 'Jun',7 => 'Jul',8 => 'Ago',9 => 'Sep',10 => 'Oct',11 => 'Nov',12 => 'Dic');
	$strmonth = $months[ intval( date( "m" , $t ) ) ];
	$result = date( "H" , $t ) . ":" . date( "i" , $t ) . "&nbsp;&nbsp;&nbsp;" . date( "j" , $t ) . " " . $strmonth .  " " . date( "Y" , $t );
	return $result;
} );

class IndexContext {
	public $Post;
	public $themeCss;
	public $Menu;
	public $Profile;
	public $PostCount;

	function __construct() {
		$menu_json_filename = __DIR__ .'/json/index-menu.json';
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
		$author = $this->Profile['id'];
		$this->Post = array_values( array_filter( Post::getPostList() , function($v) use ( $author ) {
			return $v['author'] === $author;
		}));
		$this->PostCount = count( $this->Post );
	}
};

$tpl = Templates::$sys->loadTemplate('index');
echo $tpl->render( new IndexContext );
