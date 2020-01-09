<?php

namespace app\models\query;

use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[\app\models\Resident]].
 *
 * @see \app\models\Resident
 */
class ResidentQuery extends ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return \app\models\Resident[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return \app\models\Resident|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    public function owner()
    {
        return $this->andWhere(['owner' => 1]);
    }
}
