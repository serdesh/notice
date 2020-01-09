<?php

namespace app\modules\fias\models;

class SuggestClient {
    private $url,
        $token;

    public function __construct($token, $url = 'https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/') {
        $this->token = $token;
        $this->url = $url;
    }

    public function suggest($resource, $data) {
        $options = array(
            'http' => array(
                'method'  => 'POST',
                'header'  => array(
                    'Content-type: application/json',
                    'Authorization: Token ' . $this->token,
                ),
                'content' => json_encode($data),
            ),
        );
        $context = stream_context_create($options);
        $result = file_get_contents($this->url . $resource, false, $context);
        return json_decode($result);
    }
}
?>