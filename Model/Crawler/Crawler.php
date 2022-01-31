<?php

namespace Model\Crawler;

use Symfony\Component\DomCrawler\Crawler as CrawlerDOM;
use Core\Core;
use Core\De as de;
use Core\View;
use Exception;

use Model\Discord;


require '../vendor/autoload.php';

class Crawler {

	static $base = 'https://www.tibia.com/';

	static $baseStatic = 'https://static.tibia.com/';

	private static $logs = [
		'news' => [
			'images' => false,
			'find' => true,
		]
	];

    function news($noticia_id){

		$base = 'https://www.tibia.com/news/?subtopic=newsarchive&id='.$noticia_id;

		$html = Core::curl([
			'url' => $base,
			'headers' => [
				"Cache-Control: no-cache",
				"Content-Type: application/json",
            ]
		])['data'] ?? '';

		$crawler = new CrawlerDOM();
		$crawler->addHtmlContent($html);
		$info = $crawler->filterXPath('//div[@class="BoxContent"]');
		
		$htmlArray = explode(PHP_EOL, $html);
		$news = [];

		$titulo = '';
		$data = '';
		$conteudo = '';

		$html_comprimido = View::comprimeHTML($html);
		foreach($htmlArray as $key => $lin){
			
			if(strpos($lin, 'NewsHeadline') !== false){

				$pattern = "/<div class=\"NewsHeadlineDate\">(.*?)<\/div>/";
				preg_match($pattern, $lin, $data);

				$data = strip_tags($data[1] ?? '');
				$data = str_replace([' ', '-'], '', $data);

				$data = urlencode($data);
				$data = str_replace('&#160;', '-', urldecode($data));
				$data = date('d/m/Y', strtotime($data));

				$pattern = "/<div class=\"NewsHeadlineText\">(.*?)<\/div>/";
				preg_match($pattern, $lin, $titulo);

				$titulo = strip_tags($titulo[1] ?? '');
			}
		}

		$pattern = "/<td class=\"NewsTableContainer\">(.*?)<\/td>/";
		preg_match($pattern, $html_comprimido, $conteudo_post);
		$conteudo_post = $conteudo_post[1] ?? '';

		$pattern = "/<img src=\"(.*?)\"/";
		preg_match_all($pattern, $html_comprimido, $imagens);

		unset($imagens[0]);

		foreach($imagens as $keyimg => $imagemss){

			foreach($imagemss as $keyimgs => $imagemURL){

				$imagemNome = '';
				if(strpos($imagemURL, self::$baseStatic.'images/') !== false){
					$imagemNome = explode(self::$baseStatic.'images/', $imagemURL)[1] ?? '';
				
					$imagemNome = 'images/'.$imagemNome;

					if(!is_file($imagemNome)){
						try {

							$extensaoIMG = explode('.', $imagemNome);

							$pastas = explode('/', $extensaoIMG[0]);
							unset($pastas[count($pastas) - 1]);
							$pastas = implode('/', $pastas);
							Core::mkdir('images/'.$pastas);


							if(self::$logs['news']['images']){
								Discord::send([
									'username' => 'Crawler [ News ]',
									'mensagem' => 'Vamos tentar pegar a imagem. URL: '.$imagemURL]
								);
							}

							$imagemdocao = Core::curl([
								'url' => $imagemURL,
								'headers' => [
									"Content-Type: application/json",
								]
							])['data'] ?? '';

							if(strlen($imagemdocao) >= 200){
								
								file_put_contents($imagemNome, $imagemdocao);
								
								if(self::$logs['news']['images']){
									Discord::send([
										'username' => 'Crawler [ News ]',
										'mensagem' => 'Salvamos a imagem. URL: '.$imagemURL]
									);
								}
			
							}else{

								if(self::$logs['news']['images']){
									Discord::send([
										'username' => 'Crawler [ News ]',
										'mensagem' => 'Parece que a imagem não existe mais. URL: '.$imagemURL]
									);
								}
							}


						} catch (Exception $th) {

							if(self::$logs['news']['images']){
								Discord::send([
									'username' => 'Crawler [ News ]',
									'mensagem' => 'Erro ao salvar a imagem. URL: '.$imagemURL]
								);
							}
						}
					
					}else{

						if(self::$logs['news']['images']){
							Discord::send([
								'username' => 'Crawler [ News ]',
								'mensagem' => 'Ja existe essa imagem. URL '.$imagemURL]
							);
						}
					}
				}
			}
		}

		if(empty($titulo)){

			if(self::$logs['news']['find']){
				Discord::send([
					'username' => 'Crawler [ News ]',
					'mensagem' => 'Não encontramos nenhuma noticia com o ID: '.$noticia_id]
				);
			}
			return [];
		}

		$conteudo_post = str_replace(self::$baseStatic, '', $conteudo_post);

		if(self::$logs['news']['find']){
			Discord::send([
				'username' => 'Crawler [ News ]',
				'mensagem' => 'Encontramos uma notícia '.
					PHP_EOL.'- **ID:** '.$noticia_id.
					PHP_EOL.'- **Titulo:** '.$titulo.
					PHP_EOL.'- **Data:** '.$data.
					PHP_EOL.'- **LINK:** '.self::$base.'news/?subtopic=newsarchive&id='.$noticia_id,
			]);
		}

		$dataNew = [
			'id' => $noticia_id,
			'data' => $data,
			'titulo' => $titulo,
			'mensagem' => strip_tags($conteudo_post),
			'html' => $conteudo_post
		];

		// Salva no DB
		$resposta = Queries::newNews([
			'new_id' => $dataNew['id'],
			'new_data' => $dataNew['data'],
			'new_title' => $dataNew['titulo'],
			'new_body' => $dataNew['mensagem'],
			'new_body_html' => $dataNew['html'],
		]);

		return $dataNew;
	}

    function player(String $nickname){

		$base = 'https://www.tibia.com/community/?name='.urlencode(urldecode($nickname));

		$html = Core::curl([
			'url' => $base,
			'headers' => [
				'Accept-Encoding:gzip, deflate, sdch',
            ]
		])['data'] ?? '';

		$crawler = new CrawlerDOM();
		$crawler->addHtmlContent($html);
		$info = $crawler->filterXPath('//div[@class="TableContentContainer"]/table[position() > 0]/tr[position() > 0]');

		$player = [];

		foreach ($info as $ii => $row) {
            $key  = trim($row->firstChild->nodeValue);
			$value = trim($row->lastChild->nodeValue);

            if($key == 'Name:') {
				$player["name"] = $value;
			}else if($key == 'Former Names:') {
				$player["formerName"] = $value;
			}else if($key == 'Title:') {
				$player["title"] = $value;
			}else if($key == 'Sex:') {
				$player["sex"] = $value;
			}else if($key == "Vocation:") {
				$player["vocation"] = $value;
			}else if($key == "Level:") {
				$player["level"] = $value;
			}else if($key == "Comment:") {
				$player["comment"] = $value;
			}else if($key == "Ahievement Points:") {
				$player["achievementPoints"] = $value;
			}else if($key == 'World:') {
				$player["world"] = $value;
			}else if($key == 'Former World:') {
				$player["formerWorld"] = $value;
			}else if($key == 'Residence:') {
				$player["residence"] = $value;
			}else if($key == 'House:') {
				$player["house"] = $value;
			}else if($key == 'Guild Membership:') {
				$player["guildMembership"] = $value;
			}else if($key == "Last Login:") {
				$player["lastLogin"] = Util::time($value);
			}else if($key == "Account Status:") {
				$player["premium"] = $value == 'Free Account' ? false : true;
			}else if(Util::contain($key, "created")) {
				$player["created"] = Util::time($value);
			}else if(Util::contain($key, "1. ")){

				$players = explode('. ', $row->parentNode->nodeValue);
				unset($players[0]);

				$listaPlayersAcc = [];

				foreach($players as $key => $playerName){

					$playerName = trim($playerName);

					if(!is_numeric(substr($playerName, strlen($playerName) - 1, strlen($playerName)))){
						$playerName .= $key;
					}

					$pattern = "/[A-Z][a-z]*\d(.?)/";
					preg_match($pattern, $playerName, $mundo);

					$world = Util::soLetras($mundo[0] ?? '');

					$listaPlayersAcc[] = [
						'name' => str_replace(($mundo[0] ?? ''), '', $playerName),
						'world' => $world
					];
				}

				$player["players"] = $listaPlayersAcc;
			}

            if(Util::contain($value, 'Account Achievements')){
                $player["achievements"] = $value;
            }
            if(Util::contain($value, 'Died at Level ')){
                $player['deaths'][] = [
                    'moment' => Util::time($key),
                    'description' => $value
                ];
            }
		}

		if(!isset($player['name'])){
			return [];
		}

		Discord::send([
			'username' => 'Crawler [ Player ]',
			'mensagem' => 'Encontramos um player '.
				PHP_EOL.'- **Name:** '.$player['name'].
				PHP_EOL.'- **Level:** '.$player['level'].
				PHP_EOL.'- **Vocation:** '.$player['vocation'].
				PHP_EOL.'- **LINK:** '.self::$base.'community/?name='.urlencode($player['name']),
		]);

        return $player;
    }
}