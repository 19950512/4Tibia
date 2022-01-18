<?php

use Core\Router AS Router;
use Core\De AS de;

class Aplication {

	protected $router;

	function __construct(){
		
		try {

			$this->router = new Router();
			
			$controller = new $this->router->namespace();
			
			if(!method_exists($controller, $this->router->action)){
				$this->router->set404();
				$controller = new $this->router->namespace();
			}

			$url = $this->router->parseURL($_SERVER['REQUEST_URI']);

			$acion = strtolower($url[2] ?? 'index');
			$test_action_exists = method_exists($controller, $acion);
			$action = $test_action_exists ? $acion : 'index';
			
			$this->router->setAction($action);
			
			if(!$test_action_exists){
				$this->router->set404();
				$controller = new $this->router->namespace();
			}

			$controller->{$this->router->action}();
		
		}catch(\Exception $e) {
			echo $e->getMessage();
		}
	}
}