<?php

namespace app\models\search;

use app\models\User;
use app\models\Users;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Petition;

/**
 * PetitionSearch represents the model behind the search form about `app\models\Petition`.
 */
class PetitionSearch extends Petition
{
    public $phone_number;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'status_id', 'specialist_id', 'manager_id', 'resident_id', 'relation_petition_id', 'created_by', 'closed_user_id'], 'integer'],
            [['header', 'text', 'where_type', 'execution_date', 'created_at', 'answer', 'petition_type', 'address'], 'safe'],
            [['company_id'], 'safe'],
            [['phone_number'], 'safe'],
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
        $query = Petition::find();

        if (!User::isSuperAdmin()){
            $query->joinWith(['company c'])
                ->andWhere(['c.id' => User::getCompanyIdForUser()]);
        } else {
            $query->joinWith(['company c']);
        }

        if (Users::isSpecialist()){
            $query->joinWith(['call']);
            $query->andWhere(['specialist_id' => Yii::$app->user->id]);
        }

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
            'status_id' => $this->status_id,
            'specialist_id' => $this->specialist_id,
            'manager_id' => $this->manager_id,
            'resident_id' => $this->resident_id,
            'relation_petition_id' => $this->relation_petition_id,
            'execution_date' => $this->execution_date,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'closed_user_id' => $this->closed_user_id,
            'c.id' => $this->company_id,
        ]);

        $query->andFilterWhere(['like', 'header', $this->header])
            ->andFilterWhere(['like', 'text', $this->text])
            ->andFilterWhere(['like', 'where_type', $this->where_type])
            ->andFilterWhere(['like', 'answer', $this->answer])
            ->andFilterWhere(['like', 'petition_type', $this->petition_type])
            ->andFilterWhere(['like', 'address', $this->address])
            ->andFilterWhere(['like', 'call.phone_number', $this->phone_number]);

        return $dataProvider;
    }
}
