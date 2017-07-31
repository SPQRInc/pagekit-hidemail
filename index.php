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
	
	'settings' => 'hidemail-settings',
	
	'resources' => [
		'hidemail:' => ''
	],
	
	'config' => [
		'nodes' => []
	],
	
	'events' => [
		'boot' => function( $event, $app ) {
			$app->subscribe(
				new HidemailPlugin
			);
		},
		'view.scripts' => function( $event, $scripts ) use ( $app ) {
			$scripts->register(
				'hidemail-settings',
				'spqr/hidemail:app/bundle/hidemail-settings.js',
				[ '~extensions', 'input-tree', 'editor' ]
			);
		}
	]
];