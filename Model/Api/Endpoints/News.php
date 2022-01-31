<?php

namespace Model\Api\Endpoints;

use Model\Db\Connection as DB;
use Model\Api\Api;

class News {

    function total(){
        $result = DB::get([
            'tabela' => [
                'nome' => 'news', 
                'alias' => 'news'
            ],
            'colunas' => [
                'new_id',
            ],
            'where' => '',
            'order' => [
                'new_id DESC' // Ordenar pelas mais recentes primeiro.
            ]
        ]);

        return is_array($result) ? count($result) : 0;
    }

    function news($data = []){

        $pagina = Api::$pagina - 1;
        if(isset($data['page']) and is_numeric($data['page']) and $data['page'] > 1){
            $pagina = $data['page'] - 1;
        }

        $offset = $pagina * Api::$limit;

        if($offset < 0){
            $offset = 0;
        }

        return DB::get([
            'tabela' => [
                'nome' => 'news', 
                'alias' => 'news'
            ],
            'colunas' => [
                'new_id',
                'new_data',
                'new_title',
                'new_body',
            ],
            'where' => '',
            'order' => [
                'new_id DESC' // Ordenar pelas mais recentes primeiro.
            ],
            'limit' => Api::$limit,
            'offset' => $offset
        ]);
    }
}