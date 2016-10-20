<?php 
require_once __DIR__ . '/../../core/plugin.php';
require_once __DIR__ . '/../../core/templates.php';

final class LechuckPlugin implements SubscriberInterface {
	public static function GetSubscriptions() {
		return array(
			Hooks::ON_INDEX => 'OnIndex',
			Hooks::ON_VIEW => 'OnView'
		);
	}
	
	public function OnIndex( $context ) {
		$this->AddThemeContext( $context );
		$this->AddHelpers( $context );
	}	

	public function OnView( $context ) {
		$this->AddThemeContext( $context );
		$this->AddHelpers( $context );
	}

	public function AddThemeContext( $context ) {
		$context->Blog['cover'] = $context->Blog['assets'] . "/images/cover.jpg";
	}
	
	public function AddHelpers( $context ) {
		Templates::$themed->addHelper( '_time' , function( $a , Mustache_LambdaHelper $helper) use(&$context) {
			$t = intval( $helper->render($a) );
			return date( 'j M Y' , $t );
		} );
	}
}

PluginManager::Add( new LechuckPlugin );
