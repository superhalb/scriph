<?php
require_once 'hooks.php';

interface SubscriberInterface {
	public static function GetSubscriptions();
}

final class PluginManager {
	static private $listeners = array();
	
	static private function Listen( $ev, $cb ) {
		PluginManager::$listeners[ $ev ][] = $cb;
	}
	
	static public function Dispatch( $ev, $p = NULL ) {
		if ( isset (  PluginManager::$listeners[ $ev ] ) ) {
			foreach ( PluginManager::$listeners[ $ev ] as $listener ) {
				call_user_func_array( $listener, array($p) );
			}
			return true;
		}
		return false;
	}
	
	static public function Add( SubscriberInterface $s ) {
		$listeners = $s->GetSubscriptions();
		foreach ( $listeners as $event => $listener ) {
			PluginManager::Listen( $event , array( $s , $listener ) );
		}
	}
}

/*// Test
final class TestEvents {
	const TEST = 'TestEvents.test';
}

class TestSubscriber implements SubscriberInterface {
	static $a = 0;
	public static function Test() {
		echo "Executing:\n";
		return TestSubscriber::$a ++;
	}
	public static function GetSubscriptions() {
		return array(
			TestEvents::TEST => 'Test'
		);
	}
}

$test = new TestSubscriber();
PluginManager::Add( $test );
PluginManager::Add( $test );
$r = PluginManager::Dispatch( TestEvents::TEST );
var_dump( $r );
exit;
// */