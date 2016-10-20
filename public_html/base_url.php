<?php
//require_once 'system/krumo/class.krumo.php';

class BaseUrlHelper {
	static public $url = "";
}
function getBaseUrl() {
	if ( BaseUrlHelper::$url !== "" ) {
		return BaseUrlHelper::$url;
	}
	//krumo( $_SERVER);
	if( isset( $_SERVER['HTTPS'] ) || isset( $_SERVER['HTTP'] ) ) {
		$url  = isset( $_SERVER['HTTPS'] ) && 'on' === $_SERVER['HTTPS'] ? 'https' : 'http';
	} else {
		$url  = $_SERVER[ 'SERVER_PORT' ] === '443' ? 'https' : 'http';
	}
	$url .= '://' . $_SERVER['HTTP_HOST'];
	$url .= in_array( $_SERVER['SERVER_PORT'], array('80', '443') ) ? '' : ':' . $_SERVER['SERVER_PORT'];
	$script = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME'] );
	$file = str_replace('\\', '/', __FILE__);
	$limite = strlen( $script ) < strlen( $file ) ? strlen( $script ) : strlen( $file );
	for( $i = 0, $j = 0 ; $i < $limite ; ++ $i ) {
					if ( $file[ $i ] === '/' ) $j = $i;
					if ( $file[ $i ] !== $script[ $i ] ) {
									$script2 = substr( $script , $j );
									$script_name = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] );
									$result = substr( $script_name , 0 , strpos( $script_name , $script2 ) );
									$url .= $result;
									break;
					}
	}
	
	//krumo($url);
	BaseUrlHelper::$url = $url;
	return $url;
}
