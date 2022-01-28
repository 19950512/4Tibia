<?php

namespace Model\Crawler;

use Symfony\Component\DomCrawler\Crawler as CrawlerDOM;
use Core\Core;
use Core\De as de;
use Core\View;
use Exception;


require '../vendor/autoload.php';

class Crawler {

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

		$pattern = "/ src=\"(.*?)\" /";
		preg_match_all($pattern, $html_comprimido, $imagens);

		unset($imagens[0]);
		foreach($imagens as $keyimg => $imagemss){

			foreach($imagemss as $keyimgs => $imagemURL){

				$imagemNome = '';
				if(strpos($imagemURL, 'static.tibia.com/images/') !== false){
					$imagemNome = explode('static.tibia.com/images/', $imagemURL)[1] ?? '';
					$extensaoIMG = explode('.', $imagemNome);

					$pastas = explode('/', $extensaoIMG[0]);
					unset($pastas[count($pastas) - 1]);
					$pastas = implode('/', $pastas);
					Core::mkdir('images/'.$pastas);

					$imagemdocao = Core::curl([
						'url' => $imagemURL,
						'headers' => [
							"Content-Type: application/json",
						]
					])['data'] ?? '';

					$imagemNome = 'images/'.$imagemNome;

					if(!is_file($imagemNome) and strlen($imagemdocao) >= 500){
						try {
							file_put_contents($imagemNome, $imagemdocao);
						} catch (Exception $th) {
						}
					}
				}
			}
		}

		$conteudo_post = str_replace('https://static.tibia.com/', '', $conteudo_post);

		return [
			'id' => $noticia_id,
			'data' => $data,
			'titulo' => $titulo,
			'mensagem' => strip_tags($conteudo_post),
			'html' => $conteudo_post
		];
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

        return $player;
    }
}