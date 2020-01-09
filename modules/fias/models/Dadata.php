<?php

namespace app\modules\fias\models;

use app\models\Settings;
use app\modules\fias\models\SuggestClient as SuggestClient;


class Dadata
{

    public function suggestionAddress($address)
    {
        \Yii::info($address, 'test');

        $token = Settings::getValueByKey('dadata_token');
        $dadata = new SuggestClient($token);
        $query = '{' . $address . ', "count": 1}'  ;
        $data = array(
            'query' => $query
        );
        $resp = $dadata->suggest("address", $data);
//        print "Query: " . $query . "\n";
//        print "Suggestions: \n";
        $suggestion_address = '';
        foreach ($resp->suggestions as $suggestion) {
            $suggestion_address .= $suggestion->unrestricted_value . " ";
        }

        \Yii::info($suggestion_address, 'test');

        return $suggestion_address;
    }


}