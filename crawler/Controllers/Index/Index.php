<?php

namespace crawler\Controllers\Index;

use crawler\Controllers\Controller;


class Index extends Controller {

	protected $controller = 'Index';

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

		// Render View
		$this->render($mustache, $this->controller, $this->viewName);
	}

}