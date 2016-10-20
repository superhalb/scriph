<?php
// Copyright (c) 2014, Alberto San Blas Cañabate. License details can be found in license/license.txt

final class Schemation {
	static function get( $file ) {
		$json = file_get_contents( $file . '.schema.json' );
		$json = str_replace(array("\n","\r"),"",$json); 
		$json = preg_replace('/([{,]+)(\s*)([^"]+?)\s*:/','$1"$3":',$json); 
		$schema = json_decode( $json , true );
		$json = file_get_contents( $file . '.json' );
		$json = str_replace(array("\n","\r"),"",$json); 
		$json = preg_replace('/([{,]+)(\s*)([^"]+?)\s*:/','$1"$3":',$json); 
		$values = json_decode( $json , true );
		foreach( $schema as $key => $val ) {
			if ( isset ( $values[ $schema[$key]['name'] ] ) ) {
				$schema[$key]['value'] = $values[ $schema[$key]['name'] ];
			}
		}
		return $schema;
	}
}
	