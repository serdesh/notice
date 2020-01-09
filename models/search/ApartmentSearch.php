<?php

namespace app\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Apartment;

/**
 * ApartmentSearch represents the model behind the search form about `app\models\Apartment`.
 */

class ApartmentSearch extends Apartment
{
    public $company_id;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'house_id'], 'integer'],
            [['number',  'room_number'], 'safe'],
            [['is_residential'], 'integer'],
            [['address'], 'safe'],
            [['rooms'], 'safe'],
            ['company_id', 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Apartment::find();

        $query->joinWith([
            'house',
//            'rooms r',
//            'street s',
        ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);


        $dataProvider->sort->attributes['address'] = [
            'asc' => [
                'house.address' => SORT_ASC,
//                'r.number' => SORT_ASC,
            ],
            'desc' => [
                'house.address' => SORT_DESC,
//                'r.number' => SORT_DESC,
            ],
        ];


        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'house_id' => $this->house_id,
            'is_residential' => $this->is_residential,
        ]);

        $query->andFilterWhere(['like', 'apartment.number', $this->number]);

        $query->andWhere(
            'house.address LIKE "%' . $this->address . '%" '
//            . 'AND r.number LIKE "%' . $this->rooms . '%" '

        );

        return $dataProvider;
    }
}
