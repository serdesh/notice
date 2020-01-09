<?php

namespace app\models\search;

use app\models\User;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Resident;

/**
 * ResidentSearch represents the model behind the search form about `app\models\Resident`.
 */
class ResidentSearch extends Resident
{
    public $address;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'owner', 'contact_id', 'apartment_id', 'room_id', 'created_by_company'], 'integer'],
            [
                [
                    'address',
                    'last_name',
                    'first_name',
                    'patronymic',
                    'birth_date',
                    'related_degree',
                    'additional_info',
                    'snils',
                    'fio'
                ],
                'safe'
            ],
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
        $query = Resident::find();

        $query->joinWith([
            'apartment a',
//            'house h' => function (ActiveQuery $q) {
//                $q->joinWith(['street s']);
//            }
            'house h'
        ]);

        if (User::isAdmin() || User::isManager() || User::isSpecialist()){
            $query->andWhere(['h.company_id' => User::getCompanyIdForUser()]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->sort->attributes['address'] = [
            'asc' => ['h.number' => SORT_ASC, 'a.number' => SORT_ASC],
            'desc' => ['h.number' => SORT_DESC, 'a.number' => SORT_DESC],
        ];
        $dataProvider->sort->attributes['fio'] = [
            'asc' => ['last_name' => SORT_ASC, 'first_name' => SORT_ASC, 'patronymic' => SORT_ASC],
            'desc' => ['last_name' => SORT_DESC, 'first_name' => SORT_DESC, 'patronymic' => SORT_DESC],
        ];

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'owner' => $this->owner,
            'birth_date' => $this->birth_date,
            'contact_id' => $this->contact_id,
            'apartment_id' => $this->apartment_id,
            'room_id' => $this->room_id,
            'created_by_company' => $this->created_by_company,
        ]);

        $query->andFilterWhere(['like', 'last_name', $this->last_name])
            ->andFilterWhere(['like', 'first_name', $this->first_name])
            ->andFilterWhere(['like', 'patronymic', $this->patronymic])
            ->andFilterWhere(['like', 'related_degree', $this->related_degree])
            ->andFilterWhere(['like', 'additional_info', $this->additional_info])
            ->andFilterWhere(['like', 'snils', $this->snils]);

//        $query->andWhere(
//            'a.number LIKE "%' . $this->address . '%" '
//            . 'OR h.number LIKE "%' . $this->address . '%"'
////            . 'OR s.name LIKE "%' . $this->address . '%"'
//        );
        $query->andWhere(
            'h.id LIKE "%' . $this->address . '%" '
        );

        $query->andWhere(
            'last_name LIKE "%' . $this->fio . '%" '
            . 'OR first_name LIKE "%' . $this->fio . '%"'
            . 'OR patronymic LIKE "%' . $this->fio . '%"'
        );

        return $dataProvider;
    }
}
