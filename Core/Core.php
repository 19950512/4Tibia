<?php

namespace Core;

use Exception;

use Core\De as de;

class Core {

	public static function mustache($mustache = [], $mask = ''){
		$view = View::comprimeHTML(str_replace(array_keys($mustache), array_values($mustache), $mask));
		return $view;
	}
	public static function lower($string = ''){
		return strtolower($string);
	}
	public static function strip_tags($strig = ''){
		return strip_tags($strig);
	}
	public static function ucwords($string = ''){
		return ucwords($string);
	}
	public static function ucfirst($string = ''){
		return ucfirst($string);
	}
	public static function strtotime($date = 'today'){
		$date = ($date == 'today') ? date('d-m-Y') : $date;
		return strtotime($date);
	}
	public static function strtolower($string = ''){
		return strtolower($string);
	}
	public static function trim($string = '', $mask = ' '){
		return trim($string, $mask);
	}
	public static function base64_encode($string = ''){
		return base64_encode($string);
	}
	public static function base64_decode($string = ''){
		return base64_decode($string);
	}
	public static function date($mask = ''){
		$mask = ($mask == '') ? 'd/m/Y' : $mask;
		return date($mask);
	}
	public static function mkdir($path){
		$mkdir = explode('/', $path);
		$atual = '';
		foreach ($mkdir as $nivel => $pasta){
			if($pasta === '..'){
				$atual .= $pasta.'/';
			}else{
				$atual .= $pasta.'/';
				if(!is_dir($atual)){
					mkdir($atual);
				}
			}
		}
	}
	public static function datemask($date = 'today', $mask = 'd/m/Y'){
		$date = ($date == 'today') ? date('d-m-Y') : $date;
		return self::date($mask, self::strtotime($date));
	}
	public static function number_format($number = 0, $decimals = 2, $dec_point = ',', $thousands_sep = '.'){
		return number_format($number, $decimals, $dec_point, $thousands_sep);
	}
	public static function moeda($number = 0, $moeda = 'R$'){
		return $moeda.' '.self::number_format($number);
	}
	public static function ip(){
		return $_SERVER['REMOTE_ADDR'] ?? '';
	}
	public static function getTrueFalse($variavel){
		if($variavel === true){
			return 'true';
		}elseif($variavel === 'true'){
			return 'true';
		}

		return 'false';
	}

	public static function getTokenClient($serial = ''){
		return substr(base64_encode(md5($serial)), 15, 4).'-'.$serial;
	}

	public static function semAcentos($string = ''){
		$comAcentos = array('à', 'á', 'â', 'ã', 'ä', 'å', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ù', 'ü', 'ú', 'ÿ', 'À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'O', 'Ù', 'Ü', 'Ú');
		$semAcentos = array('a', 'a', 'a', 'a', 'a', 'a', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'y', 'A', 'A', 'A', 'A', 'A', 'A', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U');

		return str_replace($comAcentos, $semAcentos, $string);
	}
	/*
		Função remove os NULL de um array.
	*/
	public static function arrayNotNull($array = []){
		foreach($array as $key => $subArray){
			foreach($subArray as $coluna => $valor){
				if(empty($valor)){
					$array[$key][$coluna] = '';
				}
			}
		}

		return $array;
	}

	/**
	 * Verifica se il valore è
	 * un'e-mail
	 *
	 * @param mixed $email
	 * @return boolean
	 */
	public static function is_email($email = ''){
		if(filter_var($email, FILTER_VALIDATE_EMAIL)) return true;
	}

	public static function CEP($cep){

		// retira espacos em branco
		$cep = trim($cep);
		
		// Tira os - se tiver
		$cep = str_replace('-', '', $cep);

		// Coloca o - agora.
		$cep = substr($cep, 0, 5).'-'.substr($cep, 5, 8);

		// expressao regular para avaliar o cep
		$avaliaCep = preg_match('/[0-9]{5,5}([-]?[0-9]{3})?$/', $cep);

		if(strlen($cep) < 9){
			return false;
		}

		// verifica o resultado
		return $avaliaCep;
	}

	public static function removeMascara($string = ''){
		return preg_replace('/\D/', '', $string);
	}

	public static function existNotEmpty($arr = [], $indice = ''){

		if(isset($arr[$indice]) and strlen($arr[$indice]) >= 1){
            return $arr[$indice];
        }

		return NULL;
	}

	public static function CNPJ($cnpj){

		$cnpj = preg_replace('/[^0-9]/', '', (string) $cnpj);

		// Valida tamanho
		if(strlen($cnpj) != 14){
			return false;
		}

		// Verifica se todos os digitos são iguais
		if(preg_match('/(\d)\1{13}/', $cnpj)){
			return false;	
		}

		// Valida primeiro dígito verificador
		for($i = 0, $j = 5, $soma = 0; $i < 12; $i++){
			$soma += $cnpj[$i] * $j;
			$j = ($j == 2) ? 9 : $j - 1;
		}

		$resto = $soma % 11;

		if($cnpj[12] != ($resto < 2 ? 0 : 11 - $resto)){
			return false;
		}

		// Valida segundo dígito verificador
		for($i = 0, $j = 6, $soma = 0; $i < 13; $i++){
			$soma += $cnpj[$i] * $j;
			$j = ($j == 2) ? 9 : $j - 1;
		}

		$resto = $soma % 11;

		return $cnpj[13] == ($resto < 2 ? 0 : 11 - $resto);
	}

	/*
		$data = [
			'url' => 'www.algumdominio.com/alguma-endpoin', ( obrigatório )
			'headers' => [ ( opcional )
				'x-teste: Javas Lixoz'
			],
			'post' => [ ( opcional, caso precise ser post )
				'id' => 'example_id',
				'senha' => '123456',
			]
		];
	*/
	static function curl($data = []){
		
		// Verifica se o Curl foi instalado.
		if(!function_exists('curl_init')){
			return ['r' => 'no', 'data' => 'Ops, é preciso instalar o curl.'];
		}

		if(!isset($data['url']) or empty($data['url'])){
			return ['r' => 'no', 'data' => 'Ops, informe a URL para o CURL.'];
		}

		// EXECUÇÃO INFINITA
		set_time_limit(0);

		// EVITA TRAVAMENTO DE NAVEGAÇÃO ENQUANTO BAIXA
		session_write_close();


		if(isset($data['headers']) and count($data['headers']) > 0){
			$headers = $data['headers'];	
		}

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_VERBOSE, true);
		curl_setopt($ch, CURLOPT_ENCODING , "gzip");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.45 Safari/537.36');
		curl_setopt($ch, CURLOPT_URL, $data['url']);
		curl_setopt($ch, CURLOPT_TIMEOUT, 600);

		// Se houver POSTS
		if(isset($data['post']) and is_array($data['post']) and count($data['post']) > 0){
			curl_setopt($ch, CURLOPT_POST, true); 
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data['post']));
		}

		// Se houver custon
		if(isset($data['custom'])){
   			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $data['custom']);
		}

		$resultado = curl_exec($ch);
		$err     = curl_errno($ch);
		$errmsg  = curl_error($ch);
		$header  = curl_getinfo($ch);
		
		curl_close($ch);

		$dataJson = json_decode($resultado, true);

		$dataResponse = $dataJson;
		
		if($dataJson === null && json_last_error() !== JSON_ERROR_NONE) {
			$dataResponse = $resultado;
		}

		$dataResponse = [
			'header' => $header,
			'erro' => $err,
			'message' => $errmsg,
			'data' => $dataResponse
		];
		
		return $dataResponse;
	}
	
	public static function CPF($cpf){

		// Extrai somente os números
		$cpf = preg_replace( '/[^0-9]/is', '', $cpf );

		// Verifica se foi informado todos os digitos corretamente
		if (strlen($cpf) != 11) {
			return false;
		}

		// Verifica se foi informada uma sequência de digitos repetidos. Ex: 111.111.111-11
		if (preg_match('/(\d)\1{10}/', $cpf)) {
			return false;
		}

		// Faz o calculo para validar o CPF
		for ($t = 9; $t < 11; $t++) {
			for ($d = 0, $c = 0; $c < $t; $c++) {
				$d += $cpf[$c] * (($t + 1) - $c);
			}
			$d = ((10 * $d) % 11) % 10;
			if ($cpf[$c] != $d) {
				return false;
			}
		}
		return true;
	}
	
	// Função que tenta pegar alguns dados.
	/*
	    [status] => success
		[country] => Brazil
		[countryCode] => BR
		[region] => RS
		[regionName] => Rio Grande do Sul
		[city] => Marau
		[zip] => 99150
		[lat] => -28.4422
		[lon] => -52.2741
		[timezone] => America/Sao_Paulo
		[isp] => Net Onze Provedor de Acesso a Internet Eireli
		[org] => Net Onze Provedor de Acesso a Internet Eireli
		[as] => AS53240 Net Onze Provedor de Acesso a Internet Eireli
		[query] => 190.15.59.69
	*/
	static function geo($atualizar = false){

		// Verifica se já não foi buscado antes essa informação, isso para evitar N requests. OR forçar atualização
		/* if(!isset($_SESSION[SESSION_LOGIN]['geo']) OR $atualizar == true){

			$ip = !DEV ? $_SERVER['REMOTE_ADDR'] : '190.15.59.69'; // IP Objetiva
			$resposta = unserialize(file_get_contents('http://ip-api.com/php/'.$ip));

			if(isset($resposta['status']) and $resposta['status'] == 'success'){
				$_SESSION[SESSION_LOGIN]['geo'] = $resposta;
				
				// Retorna os dados encontrados.
				return $resposta;
			}

			$_SESSION[SESSION_LOGIN]['geo'] = 'Parece estar Localhost ou em um mobile';
			
			// Retorna caso erro, Mobile ou Localhost
			return ['r' => 'no', 'data' => 'Ops, parece que o cliente está em um dispositivo móvel e esse metodo não funciona perfeitamente com mobile.'];
		}

		// Retorna os dados já requisitados anteriormente
		return $_SESSION[SESSION_LOGIN]['geo']; */
	}
}