<?php

// Move de www para sem www
if(($_SERVER['SERVER_NAME'] ?? '') == 'www.'.SITE_DOMINIO){

	$uri = '';
	if(($_SERVER['REQUEST_URI'] ?? '') != '/'){
		$uri = $_SERVER['REQUEST_URI'] ?? '';
	}

	header("HTTP/1.1 301 Moved Permanently");
	header('Location:'.SITE_PROTOCOLO.SITE_DOMINIO.$uri);
	exit;
}

session_start();

// Seta Headers
$seconds_to_cache = 3600;
$ts = gmdate("D, d M Y H:i:s", time() + $seconds_to_cache) . " GMT";
header("Expires: $ts");
header("Pragma: cache");
header("Cache-Control: max-age=$seconds_to_cache");
header('X-Content-Type-Options: nosniff');
header('Content-type: text/html; charset=utf-8');
header('X-Frame-Options: SAMEORIGIN');
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: origin-when-cross-origin");

// Exibe os erros caso estiver Local
if(!DEV){
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
}

set_error_handler(
    function($a, $b, $c, $d){

       /*  if(!DEV){
            header('HTTP/1.1 503 Service Temporarily Unavailable');
            echo json_encode(['r' => 'no', 'data' => 'Ops, algo de errado não deu certo... Você informou os parâmetros corretos?']);
            exit;
        } */

        echo '<table border="1"><thead><tr><th style="text-align: left">Arquivo</th><th style="text-align: left">Linha</th><th style="text-align: left">Erro</th></tr></thead>';
        echo "<tbody><tr><td>$c</td><td>$d</td><td>$b</td></tr></tbody></table>";
        exit;
    }
);

/* set_error_handler(function(int $number, string $message, $a){
    
    // Se estiver em produção.. Enviar o erro para email
    if(!DEV){
        return ''; 
    }

    // Debuga Local.
    echo '<pre>';
    var_dump("Erro: $a, $number: '$message'" . PHP_EOL);
    exit;
 }); */