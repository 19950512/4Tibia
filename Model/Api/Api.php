<?php

namespace Model\Api;

use Model\Api\Endpoints\News;

class Api {
    
    public static $limit = API_CONFIG['limit'] ?? 50;

    public static $pagina = 1;

    private $NEWS;

    function __construct(){
        $this->NEWS = new News();
    }

    function newsGet($data = []){

        $data['page'] = $data['page'] ?? self::$pagina;
        $data['page'] = $data['page'] == 0 ? 1 : $data['page'];

        $results = $this->NEWS->news($data);
        $total = $this->NEWS->total();

        return $this->_return([
            'page' => $data['page'] ?? self::$pagina,
            'total' => $total,
            'results' => $results
        ]);
    }

    private function _return($data = []){

        $total = $data['total'] ?? 0;
        $results = $data['results'] ?? [];

        $total = ceil($total / self::$limit);

        return [
            'page' => $data['page'] ?? 1,
            'pageTotal' => $total,
            'results' => $results
        ];
    }
}



		// Salva no DB
		/*$resposta = Queries::newNews([
			'new_id' => $dataNew['id'],
			'new_data' => $dataNew['data'],
			'new_title' => $dataNew['titulo'],
			'new_body' => $dataNew['mensagem'],
			'new_body_html' => $dataNew['html'],
		]);*/