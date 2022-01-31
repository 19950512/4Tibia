<?php

namespace Model;

use Core\De as de;

class Discord {
    
    static function send($data = []){

        $data = [
            'bot_id' => $data['bot_id'] ?? 1,
            'username' => $data['username'] ?? 'Crawler',
            'avatar_url' => $data['avatar_url'] ?? '',
            'mensagem' => $data['mensagem'] ?? '',
        ];

        $comando = 'node '.PATH_DISCORD_BOT.' --data '.base64_encode(json_encode($data)). ' 2>&1';
        $resposta = shell_exec($comando);
        
        return true;
    }
}