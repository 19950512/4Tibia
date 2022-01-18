<?php

namespace Core;

class De {

    function __construct($a = ''){

        header('Content-Type: text/html');

        if(is_array($a)){

            echo '<pre>';
            print_r($a);
            exit;

        }else{

            echo '<pre>';
            var_dump($a);
            exit;
        }
    }
}