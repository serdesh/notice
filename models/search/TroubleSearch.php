<?php

namespace app\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\TroubleshootingPeriod;

/**
 * TroubleSearch represents the model behind the search form about `app\models\TroubleshootingPeriod`.
 */
class TroubleSearch extends TroubleshootingPeriod
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['trouble', 'description', 'group'], 'safe'],
            [['period'], 'number'],
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
        $query = TroubleshootingPeriod::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'period' => $this->period,
        ]);

        $query->andFilterWhere(['like', 'trouble', $this->trouble])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'group', $this->group]);

        return $dataProvider;
    }
}
