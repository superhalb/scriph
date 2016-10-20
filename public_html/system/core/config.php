<?php
	// Copyright (c) 2013, Alberto San Blas Cañabate. License details can be found in license/license.txt
	final class Config {
		static $settings;
	}
	
	$json = file_get_contents( __DIR__ . '/../json/settings.json' );
	$json = str_replace(array("\n","\r"),"",$json); 
	$json = preg_replace('/([{,]+)(\s*)([^"]+?)\s*:/','$1"$3":',$json); 
	Config::$settings = json_decode( $json );