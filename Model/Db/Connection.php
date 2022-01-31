<?php

namespace Model\Db;

use Core\De as de;
use PDO;
use PDOException;

class Connection {

    public static $instance;

    function __construct(){
        self::getConnection();

    }

    public static function getConnection(){

        try {

           if(!isset(self::$instance)){

                self::$instance = new PDO('pgsql:host = ' . DB_HOST . ' dbname = ' . DB_NAME . ' user = ' . DB_USER . ' password = ' . DB_PASSWORD . ' port =' . DB_PORT);
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }

            return self::$instance;
            
        } catch (PDOException $e){

            return 'Error connection';
        }
    }

    static function update($tabela, $data = [], $where = ''){

        if($where == ''){
            return ['r' => 'no', 'data' => 'Ops, para fazer um update, precisa informar o Where (3° parametro).'];
        }
        try {

            $sql_sets = [];
            foreach($data as $coluna => $valor){
                $sql_sets[$coluna] = $coluna.' = :'.$coluna;
            }

            $sql_sets = implode(', ', $sql_sets);

            $sql = self::$instance->prepare("UPDATE $tabela SET $sql_sets WHERE $where");
            foreach($data as $coluna => &$valor){
                $sql->bindParam($coluna, $valor);
            }
            
            $sql->execute();
            $fetch = $sql->fetch(PDO::FETCH_ASSOC);
            $sql = null;


            if($fetch !== false){
                return ['r' => 'ok', 'data' => 'Pronto, dados guardados com sucesso.'];
            }
            
            return ['r' => 'no', 'data' => 'Ops, algo de errado não deu certo.'];

        }catch(\PDOException $e){

            // Duplicado
            if($e->errorInfo[0] == '23505'){
                $valor_existente = explode(') already exists.', explode(')=(', explode(PHP_EOL, $e->errorInfo[2])[1])[1])[0];
                return ['r' => 'no', 'data' => 'Já existe um valor "'.$valor_existente.'" no banco de dados.'];
            }

            return ['r' => 'no', 'data' => $e->errorInfo[2] ?? ''];
        }
    }

    static function insert($tabela, $data = []){

        try {

            $data_flip = array_flip($data);
            $valores = ':'.implode(', :', $data_flip);
            $colunas = implode(', ', $data_flip);

            $sql = self::$instance->prepare("INSERT INTO $tabela ($colunas) VALUES ($valores)");
            foreach($data as $coluna => &$valor){
                $sql->bindParam($coluna, $valor);
            }

            $sql->execute();
            $fetch = $sql->fetch(PDO::FETCH_ASSOC);
            $sql = null;

            if($fetch !== false){
                return ['r' => 'ok', 'data' => 'Pronto, dados guardados com sucesso.'];
            }
            
            return ['r' => 'no', 'data' => 'Ops, algo de errado não deu certo.'];

        }catch(\PDOException $e){

            // Duplicado
            if($e->errorInfo[0] == '23505'){
                $valor_existente = explode(') already exists.', explode(')=(', explode(PHP_EOL, $e->errorInfo[2])[1])[1])[0];
                return ['r' => 'no', 'data' => 'Já existe um valor "'.$valor_existente.'" no banco de dados.'];
            }

            return ['r' => 'no', 'data' => $e->errorInfo[2] ?? ''];
        }
    }

    static function get($data = []){

        if(!isset($data['tabela']) OR empty($data['tabela'])){
            return ['r' => 'no', 'data' => 'Ops, precisa informar qual é a tabela para utilizar o método GET.'];
        }
        
        if(!isset($data['colunas']) OR count($data['colunas']) == 0){
            return ['r' => 'no', 'data' => 'Ops, precisa informar quais colunas deseja consultar para utilizar o método GET.'];
        }
        
        if(!isset($data['where'])){
            return ['r' => 'no', 'data' => 'Ops, precisa informar qual o WHERE para consultar para utilizar o método GET.'];
        }

        $alias = $data['tabela']['alias'] ?? '';
        $alias_table = ($alias !== '') ? ' AS '.$alias : '';
        $colunas = implode(', '.$alias.'.', $data['colunas']);
        $tabela = $data['tabela']['nome'].$alias_table;
        $where = !empty($data['where']) ? 'WHERE '.$data['where'] : '';

        if(isset($data['order'])){
            foreach($data['order'] as $key => $valor){
                $data['order'][$key] = $alias.'.'.$valor;
            }
        }
        $order = isset($data['order']) ? 'ORDER BY '.implode(', ', $data['order']) : '';
        
        try {
            //code...
            $sql = self::$instance->prepare("SELECT $colunas FROM $tabela $where $order");
            $sql->execute();
            $fetch = $sql->fetchAll(PDO::FETCH_ASSOC);
            
            return $fetch;

        } catch (\PDOException $e) {

            return ['r' => 'no', 'data' => 
                (($e->errorInfo[0] ?? '') == '42P01') ? 'Ops, não existe essa tabela "'. $tabela .'"' : $e->errorInfo[2] ?? ''
            ];
        }
    }

    static function sql($data = []){

        if(!isset($data['query'])){
            return ['r' => 'no', 'data' => 'Informe a query do Select.'];
        }

        try {

            $data_flip = [];
            $num = 1;
            foreach(($data['bind'] ?? []) as $key => $valo){
                $num += 1;
                $data_flip[$num.'_'.$valo] = $key;
            }
            $valores = ':'.implode(', :', $data_flip);
            $colunas = implode(', ', $data_flip);

            
            //code...
            $sql = self::$instance->prepare($data['query']);
            foreach(($data['bind'] ?? []) as $coluna => &$valor){
                $sql->bindParam($coluna, $valor);
            }
            $sql->execute();
            $fetch = $sql->fetchAll(PDO::FETCH_ASSOC);
            
            if(is_array($fetch) and count($fetch) == 1){
                return $fetch[0];
            }

            return $fetch;
            
        } catch (\PDOException $e) {
            
            return ['r' => 'no', 'data' => 
                (($e->errorInfo[0] ?? '') == '42P01') ? 'Ops, não existe essa tabela' : $e->errorInfo[2] ?? ''
            ];
        }
    }
}