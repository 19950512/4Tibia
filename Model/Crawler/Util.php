<?php

namespace Model\Crawler;

class Util {

    static function time($data){
        
        $dataCompleta = explode(',', $data);

        $data = explode(' ', $dataCompleta[0]);
        $hora = explode(' ', $dataCompleta[1])[1];

        $dataFormatada = date('d/m/Y', strtotime($data[0].'-'.$data[1].'-'.$data[2]));
        return [
            'hora' => $hora,
            'data' => $dataFormatada
        ];
    }

    static function contain($frase, $palavra){
        return strpos($frase, $palavra) !== false;
    }

    static function soLetras($string){
        return preg_replace('/\d/', '', $string);
    }
}