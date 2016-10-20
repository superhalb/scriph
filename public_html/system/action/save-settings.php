<?php
	require_once '../core/secure.php';
	
	$user = Secure::User();
	if ( $user ) {
		$blog = $_POST['blog'];
		$theme = $_POST['theme'];
		updateSettingsFile( __DIR__ .'/../json/settings.json' , $blog );
		updateSettingsFile( __DIR__ . '/../themes/' . Config::$settings->Theme .'/settings.json' , $theme );
		echo 'ok';
		exit;
	} 

	function updateSettingsFile( $file , $fields ){
		$json = file_get_contents( $file );
		$json = str_replace(array("\n","\r"),"",$json); 
		$json = preg_replace('/([{,]+)(\s*)([^"]+?)\s*:/','$1"$3":',$json); 
		$settings = json_decode( $json , true );
		foreach( $fields as $f ) {
			$settings[ $f['name'] ] = $f['value'];
		}
		file_put_contents( $file , json_encode( $settings ) );
	}

	echo "fail\n";
