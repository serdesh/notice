<?php

namespace app\models\query;

/**
 * This is the ActiveQuery class for [[\app\models\House]].
 *
 * @see \app\models\House
 */
class HouseQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return \app\models\House[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return \app\models\House|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    public function byCompany($id)
    {
        return $this->andWhere(['company_id' => $id]);
    }
}
