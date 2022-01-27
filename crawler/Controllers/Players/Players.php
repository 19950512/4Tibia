<?php

namespace crawler\Controllers\Players;

use crawler\Controllers\Controller;

use Model\Crawler\Crawler;

use Core\De as de;

require '../vendor/autoload.php';

class Players extends Controller {

	protected $controller = 'Players';

	public function __construct(){

		parent::__construct();

		$crawler = new Crawler();

		$nickname = $this->Router->action;
		
		$player = $crawler->player($nickname);

		self::response($player);
	}

	public function index(){

        $this->viewName = __FUNCTION__;

		$this->view->setTitle('INÍCIO - '.SITE_NOME);
		$this->view->setHeader([
			['name' => 'description', 'content' => 'Início - '.SITE_NOME]
		]);
		
		$mustache = [];

		$crawler = new Crawler();
		$player = $crawler->player('Buli Nauta');
		new de($player);

		// Render View
		$this->render($mustache, $this->controller, $this->viewName);
	}

}