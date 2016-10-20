<?php
// Copyright (c) 2013, Alberto San Blas Cañabate. License details can be found in license/license.txt

require_once 'core/secure.php';
require_once 'core/config.php';
require_once 'core/post.php';
require_once 'core/templates.php';
require_once 'core/plugin.php';
require_once 'core/schemation.php';
require_once __DIR__ . '/../vendor/autoload.php';
date_default_timezone_set('Europe/Madrid');

Secure::AuthOrLogIn();

Templates::$sys->addHelper( '_select dir' , function( $a , Mustache_LambdaHelper $helper) {		
	$t = $helper->render($a);
	$t = explode('?', $t );
	$themes = glob( __DIR__ . '/' . $t[0] . '/*');
	$result = "";
	foreach ( $themes as $theme ) {
		$bn = basename( $theme );
		$result .= '<option value="' . $bn . '" ' . ( $t[1] === $bn ? 'selected="true"' : '' ) . '>' . $bn . '</option>';
	}
	return $result;
} );


class IndexContext {
	public $Menu;
	public $Profile;
	public $Blog;
	public $Theme;
	
	function __construct() {
		$this->Menu = array(
				array( 'action' => 'save','icon' => 'save','text' => 'Guardar'),
				array( 'action' => 'cancel','icon' => 'times','text' => 'Cancelar'),
		);
		$this->Profile = Secure::User(); 
		$this->Blog = Schemation::get( __DIR__ . '/json/settings' );
		$this->Theme = Schemation::get( __DIR__ . '/themes/' . Config::$settings->Theme .'/settings' );

		//krumo( $this);
		//exit;
	}	
};

$tpl = Templates::$sys->loadTemplate('settings');
echo $tpl->render( new IndexContext );
