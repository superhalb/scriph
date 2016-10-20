<?php
	require_once 'config.php';
	require_once __DIR__ . '/../../vendor/autoload.php';
	Mustache_Autoloader::register();
	
	class Templates {
		static public $sys;
		static public $themed;
	};
	
	Templates::$sys = new Mustache_Engine(
			array (
				'loader' => new Mustache_Loader_FilesystemLoader( __DIR__ .  '/../templates' ),
			)
		);

	Templates::$themed = new Mustache_Engine(
			array (
				'loader' => new Mustache_Loader_FilesystemLoader( __DIR__ .  '/../themes/' . Config::$settings->Theme . '/templates' ),
			)
		);
