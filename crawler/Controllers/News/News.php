<?php

namespace crawler\Controllers\News;

use crawler\Controllers\Controller;

use Core\De as de;
use Core\Core;
use DateTime;

use Symfony\Component\DomCrawler\Crawler;


require '../vendor/autoload.php';

class News extends Controller {

	protected $controller = 'News';

	public function __construct(){

		parent::__construct();
	}

	public function index(){

        $this->viewName = __FUNCTION__;

		$this->view->setTitle('INÍCIO - '.SITE_NOME);
		$this->view->setHeader([
			['name' => 'description', 'content' => 'Início - '.SITE_NOME]
		]);
		
		$mustache = [];

		$base = 'https://www.tibia.com/community/?name=Joe+Zito';

		$html = Core::curl([
			'url' => $base,
			'headers' => [
				'Accept-Encoding:gzip, deflate, sdch',
            ]
		])['data'] ?? '';

		$crawler = new Crawler();
		$crawler->addHtmlContent($html);
		$info = $crawler->filterXPath('//div[@class="TableContentContainer"]/table[1]/tr[position() > 1]');

		$player = [];

		function contain($frase, $palavra){
			return strpos($frase, $palavra) !== false;
		};
		function time($data){
			$dataCompleta = explode(',', $data);

			$data = explode(' ', $dataCompleta[0]);
			$hora = explode(' ', $dataCompleta[1])[1];

			$dataFormatada = date('d/m/Y', strtotime($data[0].'-'.$data[1].'-'.$data[2]));
			return [
				'hora' => $hora,
				'data' => $dataFormatada
			];
		};
		foreach ($info as $row) {
			$key  = strtolower(str_replace(" ", "_", str_replace(":", "", trim($row->firstChild->nodeValue))));
			$value = trim($row->lastChild->nodeValue);

			if(contain($key, "world")) {
				$player["world"] = $value;
			}else if(contain($key, "vocation")) {
				$player["vocation"] = $value;
			}else if(contain($key, "comment")) {
				$player["comment"] = $value;
			}else if(contain($key, "account status")) {
				$player["premium"] = $value;
			}else if(contain($key, "title")) {
				$player["title"] = $value;
			}else if(contain($key, "sex")) {
				$player["sex"] = $value;
			}else if(contain($key, "level")) {
				$player["level"] = $value;
			}else if(contain($key, "achievement_points")) {
				$player["achievementPoints"] = $value;
			}else if(contain($key, "world")) {
				$player["world"] = $value;
			}else if(contain($key, "residence")) {
				$player["residence"] = $value;
			}else if(contain($key, "last_login")) {
				$player["lastLogin"] = time($value);
			}else if(contain($key, "account")) {
				$player["premium"] = $value == 'Free Account' ? false : true;
			}else if(contain($key, "created")) {
				$player["created"] = time($value);
			}else if(contain($key, "._")){
				$infoBuneco = explode('._', $key);
				$nomeBuneco = explode('_', $infoBuneco[1]);
				$nomeBuneco = ucwords(implode(' ', $nomeBuneco));
				$player["players"][] = $nomeBuneco;
			}
		}

		new de($player);

		// Render View
		$this->render($mustache, $this->controller, $this->viewName);
	}

}