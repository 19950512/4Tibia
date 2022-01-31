<?php

namespace crawler\Controllers\News;

use crawler\Controllers\Controller;

use Model\Crawler\Crawler;
use Model\Api\Api;

use Core\De as de;

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

		$crawler = new Crawler();
		$api = new Api();
		new de($api->newsGet());

		/* $new = $crawler->news(830);

		new de($new);
		for($i = 829; $i <= 929; $i++){
			$new = $crawler->news($i);
		} */
		new de('cabo');

		// Render View
		$this->render($mustache, $this->controller, $this->viewName);
	}

}