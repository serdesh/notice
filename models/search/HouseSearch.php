<?php

namespace app\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\House;

/**
 * HouseSearch represents the model behind the search form about `app\models\House`.
 */
class HouseSearch extends House
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'street_id', 'residential_number', 'non_residential_number', 'document_id', 'company_id'], 'integer'],
            [['fias_number', 'cadastral_number', 'additional_info', 'number', 'address'], 'safe'],
            [['street_name', 'import_address'], 'safe'],
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
        $query = House::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $query->joinWith(['street s']);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'street_id' => $this->street_id,
            'residential_number' => $this->residential_number,
            'non_residential_number' => $this->non_residential_number,
            'document_id' => $this->document_id,
            'company_id' => $this->company_id,
        ]);

        $query->andFilterWhere(['like', 'fias_number', $this->fias_number])
            ->andFilterWhere(['like', 'cadastral_number', $this->cadastral_number])
            ->andFilterWhere(['like', 'additional_info', $this->additional_info])
            ->andFilterWhere(['like', 'number', $this->number])
            ->andFilterWhere(['like', 's.name', $this->street_name])
            ->andFilterWhere(['like', 'address', $this->address])
            ->andFilterWhere(['like', 'import_address', $this->import_address]);

        return $dataProvider;
    }
}
