<?php

namespace app\models\query;

/**
 * This is the ActiveQuery class for [[\app\models\ResidentEmail]].
 *
 * @see \app\models\ResidentEmail
 */
class ResidentEmailQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return \app\models\ResidentEmail[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return \app\models\ResidentEmail|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
