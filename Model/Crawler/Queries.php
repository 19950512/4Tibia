<?php

namespace Model\Crawler;

use Core\De as de;
use Exception;

use Model\Discord;
use Model\Db\Connection as DB;

class Queries {

    static function newNews($data = []){
        return DB::insert('news', $data);
    }
}