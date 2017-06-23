<?php

use Pagekit\Application;
use Spqr\Hidemail\Plugin\HidemailPlugin;


return [
	'name' => 'spqr/hidemail',
	'type' => 'extension',
	'main' => function( Application $app ) {
	
	},
	
	'autoload' => [
		'Spqr\\Hidemail\\' => 'src'
	],
	
	'nodes' => [],
	
	'routes' => [],
	
	'widgets' => [],
	
	'menu' => [],
	
	'permissions' => [],
	
	'settings' => '',
	
	'resources' => [
		'hidemail:' => ''
	],
	
	'config' => [],
	
	'events' => [
		'boot' => function( $event, $app ) {
			$app->subscribe(
				new HidemailPlugin
			);
		}
	]
];