<?php

namespace app\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Call;

/**
 * CallSearch represents the model behind the search form about `app\models\Call`.
 */
class CallSearch extends Call
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'petition_id', 'specialist_id', 'company_id'], 'integer'],
            [['created_at', 'phone_number', 'petition_status'], 'safe'],
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

        $query = Call::find();
//        $query->
//        joinWith([
//            'petition' => function (ActiveQuery $query) {
//                $query->joinWith('company');
//            }
//        ])
//            ->andWhere(['company.id' => Users::getCompanyIdForUser()]);
        $query->andWhere(['company_id' => \Yii::$app->user->identity->company_id]);

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
            'created_at' => $this->created_at,
            'petition_id' => $this->petition_id,
            'company_id' => $this->company_id,
        ]);

        $query->andFilterWhere(['like', 'phone_number', $this->phone_number]);
        return $dataProvider;
    }
}
