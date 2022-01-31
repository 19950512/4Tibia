<?php

// Esse Array serve somente para colocar a constante DEV true ou false. Nada mais.
$SERVER_NAME = [
	'4tibia.local' 						=> 'site',
	'api.4tibia.local' 					=> 'API',
];

define( 'DEV', false);//($SERVER_NAME[$_SERVER['SERVER_NAME'] ?? ''] ?? false) ? true : false);

define( 'DIR', __DIR__ );
define( 'DS', DIRECTORY_SEPARATOR );
define( 'CORE', DIR . DS . 'Core' );
define( 'CONTROLLERS', DIR . DS . 'Controllers' );
define( 'EXTENSAO_VIEW', '.html' );

define( 'API_CONFIG', [
	'limit' => 5 // Limitar os results em X. force pagination
]);

/* CONFIGURAÇÕES DO SITE */

define( 'SITE_PROTOCOLO', 'http://' );
define( 'SITE_DOMINIO', $_SERVER['SERVER_NAME'] ?? '' );
define( 'SITE_NOME', '4 Tibia - API' );
define( 'SITE_DOMINIO_SISTEMA', key($SERVER_NAME) );
define( 'SITE_IMAGEM', '/img/facebook.jpg' );

define( 'DOMINIO_CDN', 'https://cdn.'.SITE_DOMINIO);

define( 'JWT', [
	'key' => 'ONp+TMNeWOkyAnfdRy33sd2o99jXJnZisrkvk8kYaSCdFpE//Who4PZLvup8TBXV+aHUN5aNb',
	'alg' => 'HS256',
	'time_exp' => '+200 minutes' // Duração do Token,
] );

define( 'PATH_DADOS', '../dados/' );
define( 'PATH_TEMP', '../temp/' );

// Se for CDN True, envia as imagens ao CDN e exclui local,
// se for false, mantem as imagens local apenas.
define('IMAGENS_CONFIG', [
	'CDN' => false,
	'big' => ['height' => 1080, 'width' => 1920],
	'small' => ['height' => 720],
	'quality' => 70, # 70%
	'format' => 'jpg',
	'url' => 'https://imagems.dominio.com/',
	'format_allowed' => [
		'jpg' => 'jpg',
		'png' => 'png',
		'jpeg' => 'jpeg',
	]
]);

// DEFINE QUAL PROJETO VAI SER UTILIZADO.
$subdominio = explode('.', ($_SERVER['SERVER_NAME']) ?? '')[0] ?? 'api';
$subdominio = $subdominio == '4tibia' ? 'crawler' : $subdominio;
define( 'SUBPASTA', $subdominio );

/* AUTHOR - DEVELOPER */
define ( 'AUTHOR', 'Maydana' );

/* DB - arquivo Db.php 
	define ( 'DB_HOST', '127.0.0.1' );
	define ( 'DB_NAME', 'database_name' );
	define ( 'DB_USER', 'nome_user' );
	define ( 'DB_PASSWORD', 'senha_do_db' );
	define ( 'DB_PORT', '5432' );
	
	// SECRET
	define( 'API_KEY', 'SUA_API_KEY' );

	define( 'SMTP', [
		'host'	=> '',
		'user'	=> '',
		'pass'	=> '',
		'nome' 	=> 'Não responda',
		'from' 	=> '',
		'port'	=> '587',
		'crip'	=> 'tls',
		'debug'	=> 0,
		'para' 	=> array('email@email.com' => 'Pedro'),
		'icone' => '',
		'alt' => '',
		'comoCopiaOculta' => array('email@email.com' => 'Pedro')
	]);
*/
require_once 'Db.php';



/* Config.php - arquivo de configurações

*/
require_once 'Config.php';

define( 'AUTOLOAD_CLASSES', serialize(array(CORE, CONTROLLERS)));
