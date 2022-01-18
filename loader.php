<?php

function loader($class){
    $class = str_replace('\\', '/', $class);

    $class_file = DIR . DS . $class . '.php';

    if(is_file($class_file)){
        require_once($class_file);
    }else{
        foreach (unserialize(AUTOLOAD_CLASSES) as $path){

            $class_file = $path . DS . $class . DS . $class . '.php';
            if(file_exists($class_file)) require_once($class_file);
        }
    }
}

spl_autoload_register('loader');