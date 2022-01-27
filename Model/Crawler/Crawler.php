<?php

namespace Model\Crawler;

use Symfony\Component\DomCrawler\Crawler as CrawlerDOM;
use Core\Core;
use Core\De as de;


require '../vendor/autoload.php';

class Crawler {

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