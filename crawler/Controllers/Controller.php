<?php

namespace crawler\Controllers;

use Core\View as View;
use Core\De as de;
use Core\Core;
use Core\Router;

class Controller {

    /* Object VIEW / Layout */
    public $view;

    /* Name any action */
    public $viewName;

    public $pushHistory = false;

    public $Router;

    public $token;

    public $return_html = false;

    public function __construct($login = false){

        //header('Content-Type: application/json; charset=utf-8');
        
        if(isset($_POST['push']) and $_POST['push'] === 'push'){
            $this->pushHistory = true;
        }

        $this->view = new View();
        $this->Router = new Router();
    }

    static function response($data = []){
        echo json_encode($data);
        exit;
    }

    public function render($mustache = [], $controller = '', $viewName = '', $metas = [], $layout = 'Layout'){

        if($this->return_html){
            return Core::mustache($mustache, $this->view->getView($controller, $viewName));
        }

        /* Se for por F5 */
        if($this->pushHistory === false){

            echo $this->view->mustache($mustache, $this->view->getView($controller, $viewName), $layout);
            exit;

        }else{

            /* Se for por pushHistory */
            $result['html'] = $this->view->pushHistory($mustache, $this->view->getView($controller, $viewName), $layout);
            $result['metas'] = [
                'title' => $this->view->title,
            ];

            echo json_encode($result);
            exit;
        }
    }
}

?>