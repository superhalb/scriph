<?php 
	ini_set('session.cookie_path', '/');
	session_set_cookie_params( 3600 , '/' );
	session_start();
	session_regenerate_id();
	
	require_once 'templates.php';
	require_once 'users.php';
	
	class Secure {
	
		public static $ErrorMessage;

		public static function HTTPSMandatory() {
			if( $_SERVER[ 'SERVER_PORT' ] != 443 )
			{
				header('Location: https://' . $_SERVER[ 'HTTP_HOST' ] . $_SERVER[ 'REQUEST_URI' ]);
				exit();
			}
		}

		public static function AuthOrLogIn() {
			if ( !Secure::User() ) {
				$tpl = Templates::$sys->loadTemplate('login');
				if( isset( Secure::$ErrorMessage ) ){
					echo $tpl->render( array( "ErrorMessage" => Secure::$ErrorMessage ) );
				} else {
					echo $tpl->render();
				}
				exit;
			}
		}
		
		public static function FormatSalt( $salt ) {
			return '$' . implode( '$' , array (
				'2y' , // most secure version of blowfish ( >= PHP 5.3.7 )
				'07' , // cost, two digits from 04 - 31
				$salt
			));
		}
		
		public static function GenerateCredentials( $pwd ) {
			$salt = Secure::FormatSalt( str_replace( "+" , "." , substr( base64_encode( openssl_random_pseudo_bytes( 17 ) ) , 0 , 22 ) ) );
			return array(
				's' => $salt ,
				'h' => crypt( $pwd , $salt )
			);
		} 
		
		public static function CheckCredentials( $pwd , $c ) {
			return crypt( $pwd , $c['s'] ) === $c['h'] ;
		}
		
		public static function User( $https = true ) {
			if ( $https ) { 
				Secure::HTTPSMandatory();
			}
			if ( isset( $_SESSION[ 'userdata' ] ) ) {
				return $_SESSION[ 'userdata' ];
			} else {
				if( isset( $_POST[ 'sysuseracc' ] ) && isset( $_POST[ 'sysuserpwd' ] ) ) {
					$acc = $_POST[ 'sysuseracc' ];
					$pwd = $_POST[ 'sysuserpwd' ];
					
					if ( isset ( Credentials::$data[ $acc ] ) ) {
						if ( Secure::CheckCredentials( $pwd, Credentials::$data[ $acc ] ) ) {
							$pfl = Profiles::$data[ $acc ];
							$pfl['id'] = $acc;
							$_SESSION[ 'userdata' ] = $pfl;
							return $pfl;
						}
					}
					Secure::$ErrorMessage = "Usuario o contraseña incorrecta";
				}
				return false;
			}
		}
		
	};
